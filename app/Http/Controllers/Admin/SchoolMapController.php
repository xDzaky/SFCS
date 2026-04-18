<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gedung;
use App\Models\Ruangan;
use App\Models\SchoolMap;
use App\Models\SchoolMapArea;
use App\Models\SchoolMapLayer;
use App\Services\SchoolMapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SchoolMapController extends Controller
{
    public function __construct(private readonly SchoolMapService $schoolMapService)
    {
    }

    public function index(): View
    {
        $maps = SchoolMap::query()
            ->withCount('layers')
            ->latest()
            ->get();

        $uploadMax = (string) ini_get('upload_max_filesize');
        $postMax = (string) ini_get('post_max_size');
        $recommendedBytes = 20 * 1024 * 1024;

        return view('admin.denah.index', [
            'maps' => $maps,
            'phpUploadMax' => $uploadMax,
            'phpPostMax' => $postMax,
            'phpUploadReady' => $this->parseIniSize($uploadMax) >= $recommendedBytes
                && $this->parseIniSize($postMax) >= $recommendedBytes,
            'recommendedUploadLimit' => '20M',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama' => 'required|string|max:120',
            'file' => [
                'required',
                File::types(['pdf', 'png', 'jpg', 'jpeg'])->max(20 * 1024),
            ],
        ], [
            'file.required' => 'File denah wajib dipilih.',
            'file.uploaded' => sprintf(
                'File gagal diupload. Batas upload PHP saat ini %s, jadi naikkan `upload_max_filesize` dan `post_max_size` di PHP lalu restart server.',
                ini_get('upload_max_filesize')
            ),
            'file.max' => 'Ukuran file denah maksimal 20 MB.',
            'file.mimes' => 'Format file denah harus PDF, PNG, JPG, atau JPEG.',
        ]);

        $uploaded = $request->file('file');
        $fileType = $uploaded->getClientMimeType() === 'application/pdf' ? 'pdf' : 'image';
        $this->persistSchoolMapFromSource(
            (string) $request->input('nama'),
            $uploaded->getRealPath(),
            strtolower($uploaded->getClientOriginalExtension()),
            $fileType
        );

        return redirect()->route('admin.denah.index')->with('success', 'Denah sekolah berhasil diunggah.');
    }

    public function storeChunked(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:120',
            'upload_id' => 'required|string|max:120',
            'original_name' => 'required|string|max:255',
            'chunk_index' => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1|max:200',
            'total_size' => 'required|integer|min:1|max:20971520',
            'file_chunk' => 'required|file|max:1536',
        ], [
            'file_chunk.required' => 'Potongan file denah tidak ditemukan.',
            'file_chunk.max' => 'Setiap potongan upload maksimal 1.5 MB.',
            'total_size.max' => 'Ukuran file denah maksimal 20 MB.',
        ]);

        $uploadId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $validated['upload_id']) ?: Str::uuid()->toString();
        $chunkIndex = (int) $validated['chunk_index'];
        $totalChunks = (int) $validated['total_chunks'];
        $chunkDirectory = "chunked-map-uploads/{$uploadId}";
        $chunkName = sprintf('chunk_%05d.part', $chunkIndex);

        Storage::disk('local')->putFileAs(
            $chunkDirectory,
            $request->file('file_chunk'),
            $chunkName
        );

        if ($chunkIndex < $totalChunks - 1) {
            return response()->json([
                'uploaded' => true,
                'completed' => false,
                'received_chunk' => $chunkIndex + 1,
                'total_chunks' => $totalChunks,
            ]);
        }

        $extension = strtolower(pathinfo((string) $validated['original_name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['pdf', 'png', 'jpg', 'jpeg'], true)) {
            Storage::disk('local')->deleteDirectory($chunkDirectory);

            return response()->json([
                'message' => 'Format file denah harus PDF, PNG, JPG, atau JPEG.',
            ], 422);
        }

        $assembledRelativePath = "{$chunkDirectory}/assembled.{$extension}";
        $assembledAbsolutePath = Storage::disk('local')->path($assembledRelativePath);
        $output = fopen($assembledAbsolutePath, 'wb');

        if ($output === false) {
            Storage::disk('local')->deleteDirectory($chunkDirectory);

            return response()->json([
                'message' => 'Gagal menyiapkan file denah sementara di server.',
            ], 500);
        }

        try {
            for ($index = 0; $index < $totalChunks; $index++) {
                $partRelativePath = "{$chunkDirectory}/".sprintf('chunk_%05d.part', $index);
                if (!Storage::disk('local')->exists($partRelativePath)) {
                    throw ValidationException::withMessages([
                        'file' => 'Ada potongan file denah yang hilang. Silakan upload ulang.',
                    ]);
                }

                $partAbsolutePath = Storage::disk('local')->path($partRelativePath);
                $input = fopen($partAbsolutePath, 'rb');
                if ($input === false) {
                    throw ValidationException::withMessages([
                        'file' => 'Potongan file denah tidak bisa dibaca. Silakan upload ulang.',
                    ]);
                }

                stream_copy_to_stream($input, $output);
                fclose($input);
            }
        } finally {
            fclose($output);
        }

        $maxBytes = 20 * 1024 * 1024;
        $assembledSize = filesize($assembledAbsolutePath) ?: 0;

        if ($assembledSize <= 0 || $assembledSize > $maxBytes) {
            Storage::disk('local')->deleteDirectory($chunkDirectory);

            return response()->json([
                'message' => 'Ukuran file denah maksimal 20 MB.',
            ], 422);
        }

        $mimeType = (string) mime_content_type($assembledAbsolutePath);
        $fileType = $this->detectMapFileType($mimeType, $extension);

        if ($fileType === null) {
            Storage::disk('local')->deleteDirectory($chunkDirectory);

            return response()->json([
                'message' => 'Format file denah tidak dikenali. Gunakan PDF, PNG, JPG, atau JPEG.',
            ], 422);
        }

        $this->persistSchoolMapFromSource(
            (string) $validated['nama'],
            $assembledAbsolutePath,
            $extension,
            $fileType
        );

        Storage::disk('local')->deleteDirectory($chunkDirectory);

        return response()->json([
            'uploaded' => true,
            'completed' => true,
            'redirect_url' => route('admin.denah.index'),
            'message' => 'Denah sekolah berhasil diunggah.',
        ]);
    }

    public function update(Request $request, SchoolMap $map): RedirectResponse
    {
        $request->validate([
            'nama' => 'required|string|max:120',
        ]);

        $map->update([
            'nama' => (string) $request->input('nama'),
        ]);

        return back()->with('success', 'Nama denah berhasil diperbarui.');
    }

    public function destroy(SchoolMap $map): RedirectResponse
    {
        $wasActive = $map->is_active;
        $directory = dirname($map->file_path);

        DB::transaction(function () use ($map, $wasActive): void {
            $map->delete();

            if ($wasActive) {
                $nextMap = SchoolMap::query()->latest()->first();
                if ($nextMap) {
                    $nextMap->update(['is_active' => true]);
                }
            }
        });

        Storage::disk('public')->deleteDirectory($directory);

        return redirect()->route('admin.denah.index')->with('success', 'Denah berhasil dihapus.');
    }

    public function activate(SchoolMap $map): RedirectResponse
    {
        DB::transaction(function () use ($map): void {
            SchoolMap::query()->update(['is_active' => false]);
            $map->update(['is_active' => true]);
        });

        return back()->with('success', 'Denah aktif berhasil diganti.');
    }

    public function edit(SchoolMap $map): View
    {
        $map->load([
            'layers.gedung:id,nama',
            'layers.areas.gedung:id,nama',
            'layers.areas.ruangan:id,nama,gedung_id',
        ]);

        $gedungs = Gedung::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get(['id', 'nama']);

        $ruangans = Ruangan::query()
            ->with('gedung:id,nama')
            ->where('is_active', true)
            ->orderBy('nama')
            ->get(['id', 'gedung_id', 'nama', 'kode', 'lantai']);

        return view('admin.denah.edit', [
            'map' => $map,
            'fileUrl' => $this->schoolMapService->fileUrl($map),
            'gedungs' => $gedungs,
            'ruangans' => $ruangans,
        ]);
    }

    public function updateLayer(Request $request, SchoolMap $map, SchoolMapLayer $layer): RedirectResponse
    {
        abort_unless($layer->school_map_id === $map->id, 404);

        $validated = $request->validate([
            'label' => 'required|string|max:120',
            'layer_scope' => 'required|in:general,gedung_lantai',
            'gedung_id' => 'nullable|exists:gedungs,id',
            'lantai_label' => 'nullable|string|max:50',
        ]);

        if ($validated['layer_scope'] === 'gedung_lantai' && (empty($validated['gedung_id']) || empty($validated['lantai_label']))) {
            throw ValidationException::withMessages([
                'gedung_id' => 'Gedung dan lantai wajib diisi untuk layer gedung/lantai.',
            ]);
        }

        if ($validated['layer_scope'] === 'general') {
            $validated['gedung_id'] = null;
            $validated['lantai_label'] = null;
        }

        $layer->update([
            'label' => (string) $validated['label'],
            'layer_scope' => (string) $validated['layer_scope'],
            'gedung_id' => $validated['gedung_id'] ?? null,
            'lantai_label' => $validated['lantai_label'] ?? null,
        ]);

        return back()->with('success', 'Metadata layer berhasil diperbarui.');
    }

    public function storeArea(Request $request, SchoolMap $map, SchoolMapLayer $layer): RedirectResponse
    {
        abort_unless($layer->school_map_id === $map->id, 404);

        $payload = $this->validatedAreaPayload($request, $layer);
        $layer->areas()->create($payload);

        return back()->with('success', 'Area denah berhasil ditambahkan.');
    }

    public function updateArea(Request $request, SchoolMapArea $area): RedirectResponse
    {
        $payload = $this->validatedAreaPayload($request, $area->layer, $area);
        $area->update($payload);

        return back()->with('success', 'Area denah berhasil diperbarui.');
    }

    public function destroyArea(SchoolMapArea $area): RedirectResponse
    {
        $area->delete();

        return back()->with('success', 'Area denah berhasil dihapus.');
    }

    public function viewer(): View
    {
        $map = SchoolMap::query()->where('is_active', true)->first();
        $payload = $this->schoolMapService->buildViewerPayload($map);

        return view('admin.denah.viewer', array_merge($payload, [
            'fileUrl' => $map ? $this->schoolMapService->fileUrl($map) : null,
        ]));
    }

    public function activeMap(Request $request): JsonResponse
    {
        $payload = $this->schoolMapService->buildActiveMapApiPayload(
            $request->filled('gedung_id') ? (int) $request->input('gedung_id') : null,
            $request->input('lantai')
        );

        return response()->json($payload);
    }

    public function file(SchoolMap $map): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($map->file_path), 404);

        return Storage::disk('public')->response(
            $map->file_path,
            basename($map->file_path),
            [
                'Content-Disposition' => 'inline; filename="'.basename($map->file_path).'"',
                'Cache-Control' => 'no-store, max-age=0',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    private function validatedAreaPayload(Request $request, SchoolMapLayer $layer, ?SchoolMapArea $currentArea = null): array
    {
        $validated = $request->validate([
            'gedung_id' => 'nullable|exists:gedungs,id',
            'ruangan_id' => 'nullable|exists:ruangans,id',
            'label' => 'required|string|max:120',
            'shape_type' => 'required|in:marker,polygon,rect',
            'geometry_json' => 'required|string',
            'color' => 'required|string|max:20',
            'icon' => 'nullable|string|max:50',
            'is_clickable' => 'nullable|boolean',
        ]);

        $geometry = json_decode((string) $validated['geometry_json'], true);
        if (!is_array($geometry) || !$this->isValidGeometry($validated['shape_type'], $geometry)) {
            throw ValidationException::withMessages([
                'geometry_json' => 'Geometry area tidak valid.',
            ]);
        }

        if (!empty($validated['ruangan_id'])) {
            $ruangan = Ruangan::query()->findOrFail($validated['ruangan_id']);
            $validated['gedung_id'] = $validated['gedung_id'] ?? $ruangan->gedung_id;

            if ((int) $validated['gedung_id'] !== (int) $ruangan->gedung_id) {
                throw ValidationException::withMessages([
                    'gedung_id' => 'Gedung area harus sesuai dengan gedung ruangan yang dipilih.',
                ]);
            }

            $duplicateRoomArea = SchoolMapArea::query()
                ->where('school_map_layer_id', $layer->id)
                ->where('ruangan_id', $ruangan->id)
                ->when($currentArea, fn ($query) => $query->whereKeyNot($currentArea->id))
                ->exists();

            if ($duplicateRoomArea) {
                throw ValidationException::withMessages([
                    'ruangan_id' => 'Ruangan tersebut sudah terhubung ke area lain pada layer ini.',
                ]);
            }
        }

        if (empty($validated['gedung_id']) && empty($validated['ruangan_id'])) {
            throw ValidationException::withMessages([
                'gedung_id' => 'Area harus terhubung minimal ke gedung atau ruangan.',
            ]);
        }

        return [
            'gedung_id' => $validated['gedung_id'] ?? null,
            'ruangan_id' => $validated['ruangan_id'] ?? null,
            'label' => (string) $validated['label'],
            'shape_type' => (string) $validated['shape_type'],
            'geometry_json' => $geometry,
            'color' => (string) $validated['color'],
            'icon' => $validated['icon'] ?: 'fa-location-dot',
            'is_clickable' => (bool) ($validated['is_clickable'] ?? false),
        ];
    }

    private function isValidGeometry(string $shapeType, array $geometry): bool
    {
        return match ($shapeType) {
            'marker' => $this->hasNormalizedPoint($geometry),
            'rect' => $this->hasNormalizedRect($geometry),
            'polygon' => $this->hasNormalizedPolygon($geometry),
            default => false,
        };
    }

    private function hasNormalizedPoint(array $point): bool
    {
        return isset($point['x'], $point['y'])
            && $this->isRelativeCoordinate($point['x'])
            && $this->isRelativeCoordinate($point['y']);
    }

    private function hasNormalizedRect(array $rect): bool
    {
        return isset($rect['x'], $rect['y'], $rect['width'], $rect['height'])
            && $this->isRelativeCoordinate($rect['x'])
            && $this->isRelativeCoordinate($rect['y'])
            && $this->isRelativeCoordinate($rect['width'])
            && $this->isRelativeCoordinate($rect['height']);
    }

    private function hasNormalizedPolygon(array $polygon): bool
    {
        if (!isset($polygon['points']) || !is_array($polygon['points']) || count($polygon['points']) < 3) {
            return false;
        }

        foreach ($polygon['points'] as $point) {
            if (!is_array($point) || !$this->hasNormalizedPoint($point)) {
                return false;
            }
        }

        return true;
    }

    private function isRelativeCoordinate(mixed $value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        $number = (float) $value;
        return $number >= 0 && $number <= 1;
    }

    private function makeUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $seed = $baseSlug !== '' ? $baseSlug : 'denah-sekolah';
        $slug = $seed;
        $suffix = 1;

        while (SchoolMap::query()->where('slug', $slug)->exists()) {
            $slug = "{$seed}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function detectMapFileType(string $mimeType, string $extension): ?string
    {
        if ($extension === 'pdf' || str_contains($mimeType, 'pdf')) {
            return 'pdf';
        }

        if (in_array($extension, ['png', 'jpg', 'jpeg'], true) || str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        return null;
    }

    private function persistSchoolMapFromSource(string $name, string $sourcePath, string $extension, string $fileType): void
    {
        $slug = $this->makeUniqueSlug($name);
        $storedPath = "school-maps/{$slug}/source.{$extension}";

        $stream = fopen($sourcePath, 'rb');
        if ($stream === false) {
            throw ValidationException::withMessages([
                'file' => 'File denah tidak dapat dibaca dari server.',
            ]);
        }

        try {
            Storage::disk('public')->put($storedPath, $stream);
        } finally {
            fclose($stream);
        }

        $absolutePath = Storage::disk('public')->path($storedPath);
        $layerBlueprints = $this->schoolMapService->buildLayerBlueprints($absolutePath, $fileType);

        DB::transaction(function () use ($name, $slug, $storedPath, $fileType, $layerBlueprints): void {
            $isFirstMap = !SchoolMap::query()->exists();

            $map = SchoolMap::query()->create([
                'nama' => $name,
                'slug' => $slug,
                'file_path' => $storedPath,
                'file_type' => $fileType,
                'page_count' => count($layerBlueprints),
                'is_active' => $isFirstMap,
            ]);

            foreach ($layerBlueprints as $blueprint) {
                $map->layers()->create($blueprint);
            }
        });
    }

    private function parseIniSize(string $value): int
    {
        $normalized = trim($value);

        if ($normalized === '') {
            return 0;
        }

        $unit = strtolower(substr($normalized, -1));
        $number = (float) $normalized;

        return match ($unit) {
            'g' => (int) round($number * 1024 * 1024 * 1024),
            'm' => (int) round($number * 1024 * 1024),
            'k' => (int) round($number * 1024),
            default => (int) round($number),
        };
    }
}
