@php
    $viewerId   = $viewerId ?? 'pengaduan-map-card';
    $title      = $title ?? 'Lokasi di Denah';
    $fullMapUrl = $fullMapUrl ?? null;
    $focusTicket      = $mapPayload['focus_ticket'] ?? null;
    $selectedLayerId  = $mapPayload['selected_layer_id'] ?? null;
    $layers           = collect($mapPayload['layers'] ?? []);
    $selectedLayer    = $layers->firstWhere('id', $selectedLayerId) ?? $layers->first();
    $areas            = $selectedLayer ? ($mapPayload['areas_by_layer'][$selectedLayer->id] ?? []) : [];
    $tickets          = $selectedLayer ? ($mapPayload['tickets_by_layer'][$selectedLayer->id] ?? []) : [];
    $selectedPoint    = ['x' => $focusTicket['map_point_x'] ?? null, 'y' => $focusTicket['map_point_y'] ?? null];
    $selectedLayerPayload = $selectedLayer ? [
        'id'          => $selectedLayer->id,
        'page_number' => $selectedLayer->page_number,
        'label'       => $selectedLayer->label,
    ] : null;
    $hasPoint   = $mapPayload['has_manual_point'] ?? false;
    $isEnabled  = $mapPayload['is_enabled'] ?? false;
    $showCoordinates = $showCoordinates ?? true;
    $showInfoDenah   = $showInfoDenah ?? true;
@endphp

@include('partials.school-map-assets')

<div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">

    {{-- ── Card Header ─────────────────────────────────────────── --}}
    <div class="card-header bg-white border-bottom border-light-subtle px-4 py-3 d-flex justify-content-between align-items-center gap-3 flex-wrap">

        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary-subtle flex-shrink-0"
                 style="width:40px;height:40px;">
                <i class="fas fa-map-location-dot text-primary"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold text-dark">{{ $title }}</h6>
                <p class="text-muted small mb-0 mt-1">
                    @if($isEnabled && $hasPoint)
                        Titik kerusakan ditandai langsung di denah — zoom ke lokasi yang tepat.
                    @elseif($isEnabled)
                        Denah aktif. Lokasi saat ini berdasarkan teks gedung &amp; lantai.
                    @else
                        Fitur peta belum aktif. Lokasi dicatat dalam bentuk teks.
                    @endif
                </p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            @if($isEnabled)
                @if($hasPoint)
                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2"
                          style="font-size:.75rem;">
                        <i class="fas fa-crosshairs me-1"></i>Titik Tepat
                    </span>
                @else
                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2"
                          style="font-size:.75rem;">
                        <i class="fas fa-align-left me-1"></i>Lokasi Teks
                    </span>
                @endif
            @endif
            @if($fullMapUrl && $isEnabled)
                <a href="{{ $fullMapUrl }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fas fa-expand me-1"></i>Peta Penuh
                </a>
            @endif
        </div>
    </div>

    {{-- ── Card Body ────────────────────────────────────────────── --}}
    <div class="card-body p-0">

        {{-- ── Map Pane (full width) ──────────────────────────────── --}}
        @if(!$isEnabled)
            <div class="m-3 d-flex flex-column align-items-center justify-content-center text-center rounded-4 py-5 px-4"
                 style="background:#f8fafc;border:1.5px dashed #cbd5e1;min-height:160px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3"
                     style="width:44px;height:44px;background:#e2e8f0;">
                    <i class="fas fa-map text-secondary"></i>
                </div>
                <p class="fw-semibold text-secondary mb-1 small">Denah belum tersedia</p>
                <p class="text-muted mb-0" style="font-size:.8rem;">{{ $mapPayload['reason'] ?? 'Belum ada denah aktif.' }}</p>
            </div>

        @elseif(!$hasPoint)
            <div class="m-3 d-flex flex-column align-items-center justify-content-center text-center rounded-4 py-5 px-4"
                 style="background:linear-gradient(135deg,#fffbeb,#fef9c3);border:1.5px dashed #fde047;min-height:160px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3"
                     style="width:44px;height:44px;background:rgba(250,204,21,.2);">
                    <i class="fas fa-map-pin text-warning"></i>
                </div>
                <p class="fw-semibold text-dark mb-1 small">Titik lokasi belum dipilih</p>
                <p class="text-muted mb-0" style="font-size:.78rem;">
                    Laporan tetap diproses lewat gedung &amp; lantai. Edit laporan untuk titik presisi.
                </p>
            </div>

        @else
            {{-- Map with pin — full width, flush to card edges --}}
            <div class="sfcs-map-frame" style="height:220px;border-radius:0;border-left:none;border-right:none;border-top:none;">
                <div id="{{ $viewerId }}-stage" class="sfcs-map-stage is-mini" style="height:220px;min-height:unset;"></div>
            </div>
        @endif

        {{-- ── Info Denah strip (always below map) ──────────────── --}}
        @if($isEnabled && $showInfoDenah)
            <div class="px-3 pt-3 pb-3" style="background:#f8fafc;border-top:1px solid #e5e7eb;">
                <p class="text-uppercase fw-bold mb-2 d-flex align-items-center gap-1"
                   style="font-size:.6rem;letter-spacing:.07em;color:#9ca3af;">
                    <i class="fas fa-circle-info text-primary"></i>
                    Info Denah
                </p>

                <div class="row g-2">

                    {{-- Halaman Denah --}}
                    @if($selectedLayer)
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 px-2 py-2 rounded-3 h-100"
                                 style="background:#fff;border:1px solid #e5e7eb;">
                                <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:28px;height:28px;background:#ede9fe;">
                                    <i class="fas fa-map text-primary" style="font-size:.7rem;"></i>
                                </div>
                                <div style="min-width:0;">
                                    <div style="font-size:.58rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Halaman</div>
                                    <div class="fw-semibold text-truncate" style="font-size:.78rem;color:#111827;">{{ $selectedLayer->label }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Status Titik --}}
                    <div class="{{ $selectedLayer ? 'col-6' : 'col-12' }}">
                        <div class="d-flex align-items-center gap-2 px-2 py-2 rounded-3 h-100"
                             style="background:#fff;border:1px solid #e5e7eb;">
                            <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:28px;height:28px;background:{{ $hasPoint ? '#dcfce7' : '#fef9c3' }};">
                                <i class="fas fa-{{ $hasPoint ? 'crosshairs text-success' : 'align-left' }}"
                                   style="font-size:.7rem;{{ !$hasPoint ? 'color:#d97706;' : '' }}"></i>
                            </div>
                            <div style="min-width:0;">
                                <div style="font-size:.58rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Status</div>
                                @if($hasPoint)
                                    <div class="fw-semibold text-success text-truncate" style="font-size:.78rem;">Tepat di denah</div>
                                @else
                                    <div class="fw-semibold text-truncate" style="font-size:.78rem;color:#d97706;">Dari teks</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Koordinat --}}
                    @if($hasPoint && $showCoordinates)
                        <div class="col-12">
                            <div class="d-flex align-items-center gap-2 px-2 py-2 rounded-3"
                                 style="background:#fff;border:1px solid #e5e7eb;">
                                <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:28px;height:28px;background:#ede9fe;">
                                    <i class="fas fa-location-crosshairs text-primary" style="font-size:.7rem;"></i>
                                </div>
                                <div>
                                    <div style="font-size:.58rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Koordinat</div>
                                    <div class="fw-semibold font-monospace" style="font-size:.8rem;color:#111827;">
                                        X {{ number_format($selectedPoint['x'] ?? 0, 2) }}
                                        &nbsp;<span style="color:#9ca3af;">&middot;</span>&nbsp;
                                        Y {{ number_format($selectedPoint['y'] ?? 0, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        @endif

    </div>
</div>

@if($isEnabled && $hasPoint && $selectedLayer)
    @push('scripts')
        <script>
            (() => {
                const container = document.getElementById(@json($viewerId . '-stage'));
                if (!container) return;

                window.SFCSMap.renderMap({
                    container,
                    fileType:      @json($mapPayload['map']->file_type),
                    fileUrl:       @json($mapPayload['file_url'] ?? null),
                    layer:         @json($selectedLayerPayload),
                    areas:         @json($areas),
                    tickets:       @json($tickets),
                    selectedPoint: @json($selectedPoint),
                    pointZoom:     @json($focusTicket['map_zoom'] ?? 2),
                }).catch((error) => {
                    container.innerHTML = '<div class="sfcs-map-empty text-center py-4"><i class="fas fa-circle-exclamation text-warning mb-2 fs-4 d-block"></i>Mini map tidak dapat dimuat. Pastikan file denah tersedia di storage.</div>';
                    console.error(error);
                });
            })();
        </script>
    @endpush
@endif
