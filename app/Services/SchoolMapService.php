<?php

namespace App\Services;

use App\Models\Pengaduan;
use App\Models\SchoolMapLayer;
use App\Models\SchoolMap;
use App\Models\SchoolMapArea;
use Illuminate\Support\Collection;

class SchoolMapService
{
    public function hasActiveMap(): bool
    {
        return SchoolMap::query()->where('is_active', true)->exists();
    }

    public function buildLayerBlueprints(string $absolutePath, string $fileType): array
    {
        return $fileType === 'pdf'
            ? $this->buildPdfLayers($absolutePath)
            : $this->buildImageLayers($absolutePath);
    }

    public function buildViewerPayload(?SchoolMap $map): array
    {
        if (!$map) {
            return [
                'map' => null,
                'layers' => collect(),
                'areas_by_layer' => [],
                'tickets_by_layer' => [],
                'unmapped_tickets' => collect(),
                'focus_ticket' => null,
            ];
        }

        $map->load([
            'layers.gedung:id,nama',
            'layers.areas.gedung:id,nama',
            'layers.areas.ruangan:id,nama,gedung_id',
        ]);

        $layers = $map->layers->values();
        $areasByLayer = $layers->mapWithKeys(function ($layer) {
            return [$layer->id => $layer->areas->map(fn (SchoolMapArea $area) => [
                'id' => $area->id,
                'label' => $area->label,
                'shape_type' => $area->shape_type,
                'geometry_json' => $area->geometry_json,
                'color' => $area->color,
                'icon' => $area->icon ?: 'fa-location-dot',
                'gedung_id' => $area->gedung_id,
                'gedung_nama' => $area->gedung?->nama,
                'ruangan_id' => $area->ruangan_id,
                'ruangan_nama' => $area->ruangan?->nama,
                'is_clickable' => $area->is_clickable,
            ])->values()->all()];
        })->all();

        $tickets = Pengaduan::query()
            ->with(['gedung:id,nama', 'ruangan:id,nama,gedung_id'])
            ->whereIn('status', [
                Pengaduan::STATUS_PENDING,
                Pengaduan::STATUS_DIVERIFIKASI,
                Pengaduan::STATUS_DIPROSES,
            ])
            ->latest()
            ->limit(200)
            ->get([
                'id',
                'kode_pengaduan',
                'judul',
                'status',
                'prioritas',
                'gedung_id',
                'ruangan_id',
                'created_at',
            ]);

        $areas = $layers->flatMap(fn ($layer) => $layer->areas)->values();
        $roomAreas = $areas->filter(fn (SchoolMapArea $area) => $area->ruangan_id !== null)->keyBy('ruangan_id');
        $buildingAreas = $areas->filter(fn (SchoolMapArea $area) => $area->ruangan_id === null && $area->gedung_id !== null)->keyBy('gedung_id');

        $ticketsByLayer = [];
        $unmappedTickets = collect();

        foreach ($tickets as $ticket) {
            $matchedArea = null;

            if ($ticket->ruangan_id && $roomAreas->has($ticket->ruangan_id)) {
                $matchedArea = $roomAreas->get($ticket->ruangan_id);
            } elseif ($ticket->gedung_id && $buildingAreas->has($ticket->gedung_id)) {
                $matchedArea = $buildingAreas->get($ticket->gedung_id);
            }

            $payload = [
                'id' => $ticket->id,
                'kode_pengaduan' => $ticket->kode_pengaduan,
                'judul' => $ticket->judul,
                'status' => $ticket->status,
                'prioritas' => $ticket->prioritas,
                'gedung_nama' => $ticket->gedung?->nama,
                'ruangan_nama' => $ticket->ruangan?->nama,
                'created_at' => optional($ticket->created_at)->format('d/m/Y H:i'),
            ];

            if ($ticket->school_map_layer_id && $ticket->map_point_x !== null && $ticket->map_point_y !== null) {
                $ticketsByLayer[$ticket->school_map_layer_id][] = array_merge($payload, [
                    'area_id' => null,
                    'shape_type' => 'marker',
                    'geometry_json' => [
                        'x' => (float) $ticket->map_point_x,
                        'y' => (float) $ticket->map_point_y,
                    ],
                    'color' => '#2563eb',
                    'icon' => 'fa-location-dot',
                    'is_manual_point' => true,
                ]);

                continue;
            }

            if (!$matchedArea) {
                $unmappedTickets->push($payload);
                continue;
            }

            $ticketsByLayer[$matchedArea->school_map_layer_id][] = array_merge($payload, [
                'area_id' => $matchedArea->id,
                'shape_type' => $matchedArea->shape_type,
                'geometry_json' => $matchedArea->geometry_json,
                'color' => $matchedArea->color,
                'icon' => $matchedArea->icon ?: 'fa-location-dot',
            ]);
        }

        return [
            'map' => $map,
            'layers' => $layers,
            'areas_by_layer' => $areasByLayer,
            'tickets_by_layer' => $ticketsByLayer,
            'unmapped_tickets' => $unmappedTickets->values(),
            'focus_ticket' => null,
        ];
    }

    public function buildPengaduanMapPayload(Pengaduan $pengaduan): array
    {
        $map = $pengaduan->schoolMap;
        if (!$map && $pengaduan->school_map_id) {
            $map = SchoolMap::query()->find($pengaduan->school_map_id);
        }

        if (!$map) {
            $map = SchoolMap::query()->where('is_active', true)->first();
        }

        if (!$map) {
            return [
                'is_enabled' => false,
                'reason' => 'Belum ada denah aktif.',
            ];
        }

        $payload = $this->buildViewerPayload($map);
        $selectedLayerId = $pengaduan->school_map_layer_id;

        if (!$selectedLayerId) {
            $matchedLayer = $this->resolveLayerForLocation($map, $pengaduan->gedung_id, $pengaduan->lantai);
            $selectedLayerId = $matchedLayer?->id;
        }

        return [
            'is_enabled' => true,
            'map' => $map,
            'file_url' => $this->fileUrl($map),
            'layers' => $payload['layers'],
            'areas_by_layer' => $payload['areas_by_layer'],
            'tickets_by_layer' => $payload['tickets_by_layer'],
            'selected_layer_id' => $selectedLayerId,
            'focus_ticket' => [
                'id' => $pengaduan->id,
                'kode_pengaduan' => $pengaduan->kode_pengaduan,
                'judul' => $pengaduan->judul,
                'gedung_nama' => $pengaduan->gedung?->nama,
                'ruangan_nama' => $pengaduan->ruangan?->nama,
                'lantai' => $pengaduan->lantai,
                'map_point_x' => $pengaduan->map_point_x,
                'map_point_y' => $pengaduan->map_point_y,
                'map_zoom' => $pengaduan->map_zoom,
            ],
            'has_manual_point' => $pengaduan->map_point_x !== null && $pengaduan->map_point_y !== null,
        ];
    }

    public function buildActiveMapApiPayload(?int $gedungId = null, ?string $lantai = null): array
    {
        $map = SchoolMap::query()->where('is_active', true)->first();
        if (!$map) {
            return [
                'available' => false,
                'hide_picker' => true,
                'reason_code' => 'no_active_map',
                'message' => 'Belum ada denah aktif.',
            ];
        }

        $map->load([
            'layers.gedung:id,nama',
            'layers.areas.gedung:id,nama',
            'layers.areas.ruangan:id,nama,gedung_id',
        ]);

        $layers = $map->layers->values();
        $selectedLayer = $this->resolveLayerForLocation($map, $gedungId, $lantai);

        return [
            'available' => true,
            'hide_picker' => false,
            'reason_code' => $selectedLayer ? null : 'no_matching_layer',
            'map' => [
                'id' => $map->id,
                'nama' => $map->nama,
                'file_type' => $map->file_type,
                'file_url' => $this->fileUrl($map),
            ],
            'selected_layer_id' => $selectedLayer?->id,
            'layers' => $layers->map(fn (SchoolMapLayer $layer) => [
                'id' => $layer->id,
                'page_number' => $layer->page_number,
                'label' => $layer->label,
                'layer_scope' => $layer->layer_scope,
                'lantai_label' => $layer->lantai_label,
                'gedung_id' => $layer->gedung_id,
                'gedung_nama' => $layer->gedung?->nama,
                'areas' => $layer->areas->map(fn (SchoolMapArea $area) => [
                    'id' => $area->id,
                    'label' => $area->label,
                    'shape_type' => $area->shape_type,
                    'geometry_json' => $area->geometry_json,
                    'color' => $area->color,
                    'icon' => $area->icon ?: 'fa-location-dot',
                    'gedung_id' => $area->gedung_id,
                    'ruangan_id' => $area->ruangan_id,
                ])->values()->all(),
            ])->all(),
        ];
    }

    public function resolveLayerForLocation(SchoolMap $map, ?int $gedungId, ?string $lantai): ?SchoolMapLayer
    {
        $map->loadMissing('layers');

        $normalizedLantai = $this->normalizeFloorLabel($lantai);

        if ($gedungId) {
            $specific = $map->layers->first(function (SchoolMapLayer $layer) use ($gedungId, $normalizedLantai) {
                if ($layer->layer_scope !== 'gedung_lantai' || (int) $layer->gedung_id !== (int) $gedungId) {
                    return false;
                }

                if ($normalizedLantai !== null) {
                    return $this->normalizeFloorLabel($layer->lantai_label) === $normalizedLantai;
                }

                // If lantai is not chosen yet, allow the first layer that matches gedung.
                return true;
            });

            if ($specific) {
                return $specific;
            }
        }

        return $map->layers->firstWhere('layer_scope', 'general') ?? $map->layers->first();
    }

    public function fileUrl(SchoolMap $map): string
    {
        return route('school-map.file', $map);
    }

    public function normalizeFloorLabel(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return preg_replace('/[^0-9a-z]+/i', '', strtolower((string) $value));
    }

    private function buildPdfLayers(string $absolutePath): array
    {
        $pageCount = 1;
        $width = 842;
        $height = 595;
        $output = '';

        if (function_exists('shell_exec')) {
            $output = (string) @shell_exec('pdfinfo '.escapeshellarg($absolutePath).' 2>/dev/null');
        }

        if (preg_match('/Pages:\s+(\d+)/i', $output, $matches) === 1) {
            $pageCount = max(1, (int) $matches[1]);
        }

        if (preg_match('/Page size:\s+([\d.]+)\s+x\s+([\d.]+)/i', $output, $matches) === 1) {
            $width = max(1, (int) round((float) $matches[1]));
            $height = max(1, (int) round((float) $matches[2]));
        }

        $layers = [];
        for ($page = 1; $page <= $pageCount; $page++) {
            $layers[] = [
                'page_number' => $page,
                'label' => 'Halaman '.$page,
                'width' => $width,
                'height' => $height,
                'sort_order' => $page,
            ];
        }

        return $layers;
    }

    private function buildImageLayers(string $absolutePath): array
    {
        $width = 1000;
        $height = 700;

        $dimensions = @getimagesize($absolutePath);
        if (is_array($dimensions)) {
            $width = max(1, (int) $dimensions[0]);
            $height = max(1, (int) $dimensions[1]);
        }

        return [[
            'page_number' => 1,
            'label' => 'Layer 1',
            'width' => $width,
            'height' => $height,
            'sort_order' => 1,
        ]];
    }
}
