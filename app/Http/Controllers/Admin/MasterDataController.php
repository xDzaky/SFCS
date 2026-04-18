<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gedung;
use App\Models\Jurusan;
use App\Models\MasterDataImportBatch;
use App\Models\Ruangan;
use App\Services\SchoolMasterDataImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use ZipArchive;

class MasterDataController extends Controller
{
    public function __construct(private readonly SchoolMasterDataImportService $service)
    {
    }

    public function index(): View
    {
        $this->authorizeSuperAdmin();

        $batches = MasterDataImportBatch::query()
            ->with('actor:id,name')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.master-data.index', [
            'batches' => $batches,
            'scopes' => $this->service->scopes(),
        ]);
    }

    public function preview(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'file' => 'required|file|mimes:zip,xlsx,xls,csv,txt|max:10240',
            'mode' => 'nullable|in:replace_safe,upsert_only',
            'scope' => 'nullable|in:full,gedung_only,ruangan_only,mapping_only,kategori_only',
            'school_key' => 'nullable|string|max:100',
        ]);

        $uploaded = $request->file('file');
        $storedPath = $uploaded->store('master-data/uploads', 'local');
        $absolutePath = Storage::disk('local')->path($storedPath);

        try {
            $batch = $this->service->preview(
                $absolutePath,
                (string) $request->input('mode', 'replace_safe'),
                (string) $request->input('scope', 'full'),
                (int) Auth::id(),
                $uploaded->getClientOriginalName(),
                $request->input('school_key'),
                $storedPath
            );
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            return back()->with('error', 'Preview import gagal: '.$exception->getMessage());
        }

        if ($batch->status === 'failed') {
            return back()
                ->with('error', 'Import dibatalkan karena validasi gagal.')
                ->with('master_import_batch_id', $batch->id)
                ->with('master_import_result', $batch->summary_json)
                ->with('master_import_errors', $batch->errors_json)
                ->with('master_import_warnings', $batch->warnings_json);
        }

        return back()
            ->with('success', 'Preview import berhasil. Periksa hasil lalu klik Terapkan Perubahan.')
            ->with('master_import_batch_id', $batch->id)
            ->with('master_import_result', $batch->summary_json)
            ->with('master_import_errors', $batch->errors_json)
            ->with('master_import_warnings', $batch->warnings_json);
    }

    public function commit(MasterDataImportBatch $batch): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        try {
            $appliedBatch = $this->service->commitBatch($batch, (int) Auth::id());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            return back()->with('error', 'Commit import gagal: '.$exception->getMessage());
        }

        return back()
            ->with('success', 'Import master data selesai diterapkan.')
            ->with('master_import_batch_id', $appliedBatch->id)
            ->with('master_import_result', $appliedBatch->summary_json)
            ->with('master_import_errors', $appliedBatch->errors_json)
            ->with('master_import_warnings', $appliedBatch->warnings_json);
    }

    /**
     * Backward compatibility for existing route.
     */
    public function import(Request $request): RedirectResponse
    {
        return $this->preview($request);
    }

    public function errorReport(MasterDataImportBatch $batch)
    {
        $this->authorizeSuperAdmin();

        if (!$batch->error_report_path || !Storage::disk('local')->exists($batch->error_report_path)) {
            return back()->with('error', 'File error report tidak ditemukan.');
        }

        return Storage::disk('local')->download($batch->error_report_path, "master-data-errors-{$batch->id}.csv");
    }

    public function template(Request $request)
    {
        $this->authorizeSuperAdmin();

        $scope = (string) $request->input('scope', 'full');
        if (!in_array($scope, $this->service->scopes(), true)) {
            $scope = 'full';
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'master-template-');
        if ($tempPath === false) {
            abort(500, 'Gagal menyiapkan file template.');
        }

        $zipPath = $tempPath.'.zip';
        rename($tempPath, $zipPath);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat file ZIP template.');
        }

        $sheets = match ($scope) {
            'gedung_only' => ['gedung'],
            'ruangan_only' => ['ruangan'],
            'mapping_only' => ['mapping_jurusan_ruang'],
            'kategori_only' => ['kategori'],
            default => ['kategori', 'gedung', 'ruangan', 'mapping_jurusan_ruang'],
        };

        foreach ($sheets as $sheet) {
            $zip->addFromString($sheet.'.csv', implode(PHP_EOL, $this->service->templateRows($sheet)).PHP_EOL);
        }
        $zip->close();

        return response()->download(
            $zipPath,
            'template-master-data-'.date('Y-m-d')."-{$scope}.zip",
            ['Content-Type' => 'application/zip']
        )->deleteFileAfterSend(true);
    }

    public function validation(): JsonResponse
    {
        $this->authorizeSuperAdmin();

        $ruanganWithoutJurusan = Ruangan::query()->where('is_active', true)->doesntHave('jurusans')->count();
        $jurusanWithoutRuangan = Jurusan::query()->where('is_active', true)->doesntHave('ruangans')->count();
        $duplicateRoomCodes = Ruangan::query()
            ->select('kode')
            ->groupBy('kode')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        $duplicateGedungCodes = Gedung::query()
            ->select('kode')
            ->groupBy('kode')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        return response()->json([
            'counts' => [
                'gedung_total' => Gedung::count(),
                'gedung_active' => Gedung::where('is_active', true)->count(),
                'ruangan_total' => Ruangan::count(),
                'ruangan_active' => Ruangan::where('is_active', true)->count(),
                'jurusan_total' => Jurusan::count(),
                'jurusan_active' => Jurusan::where('is_active', true)->count(),
            ],
            'mismatch' => [
                'duplicate_kode_gedung' => $duplicateGedungCodes,
                'duplicate_kode_ruang' => $duplicateRoomCodes,
                'ruangan_tanpa_jurusan' => $ruanganWithoutJurusan,
                'jurusan_tanpa_ruangan' => $jurusanWithoutRuangan,
            ],
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    private function authorizeSuperAdmin(): void
    {
        if (!Auth::user()?->isSuperAdmin()) {
            abort(403, 'Hanya superadmin yang dapat mengakses fitur master data.');
        }
    }
}
