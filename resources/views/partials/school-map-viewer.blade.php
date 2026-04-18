@php
    $viewerId = $viewerId ?? 'school-map-viewer';
    $mapLayers = $layers ?? collect();
    $mapAreasByLayer = $areas_by_layer ?? [];
    $mapTicketsByLayer = $tickets_by_layer ?? [];
    $unmappedTicketsList = $unmapped_tickets ?? collect();
    $mapLayersPayload = $mapLayers->values()->map(function ($layer) {
        return [
            'id' => $layer->id,
            'page_number' => $layer->page_number,
            'label' => $layer->label,
            'layer_scope' => $layer->layer_scope,
            'lantai_label' => $layer->lantai_label,
            'gedung_id' => $layer->gedung_id,
            'gedung_nama' => $layer->gedung?->nama,
        ];
    })->all();
@endphp

@include('partials.school-map-assets')

@if(!$map)
    <div class="sfcs-map-empty">
        <div class="fw-semibold mb-1">Peta digital belum aktif</div>
        <div class="small">Admin perlu mengunggah dan mengaktifkan denah sekolah terlebih dahulu. Sampai itu dilakukan, sistem tetap memakai lokasi teks biasa.</div>
    </div>
@else
    <div class="row g-4">
        <div class="col-xl-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <h4 class="mb-1">{{ $map->nama }}</h4>
                            <div class="text-muted small">Pilih halaman/lantai, lalu zoom untuk melihat titik laporan dan area yang sudah dipetakan.</div>
                        </div>
                        <div class="sfcs-map-pill">
                            <i class="fas fa-layer-group"></i>
                            {{ $map->layers->count() }} layer
                        </div>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="sfcs-map-toolbar mb-3" id="{{ $viewerId }}-tabs">
                        @foreach($mapLayers as $layer)
                            <button type="button" class="btn btn-sm {{ $loop->first ? 'btn-primary' : 'btn-outline-primary' }}" data-layer-id="{{ $layer->id }}">
                                {{ $layer->label }}
                            </button>
                        @endforeach
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-3" id="{{ $viewerId }}-meta"></div>
                    <div class="sfcs-map-frame">
                        <div id="{{ $viewerId }}-stage" class="sfcs-map-stage"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="sfcs-map-side-card mb-4">
                <div class="fw-semibold mb-2">Legenda</div>
                <div class="small text-muted mb-3">Area ruangan/gedung tetap terlihat, sementara marker tiket mengikuti prioritas.</div>
                <div class="d-flex flex-column gap-2 small">
                    <div><span class="badge bg-danger">URGENT</span> prioritas urgent</div>
                    <div><span class="badge bg-warning text-dark">TINGGI</span> prioritas tinggi</div>
                    <div><span class="badge bg-info text-dark">SEDANG</span> prioritas sedang</div>
                    <div><span class="badge bg-secondary">RENDAH</span> prioritas rendah</div>
                </div>
            </div>

            <div class="sfcs-map-side-card mb-4">
                <div class="fw-semibold mb-2">Tiket Layer Aktif</div>
                <div id="{{ $viewerId }}-tickets" class="sfcs-map-list">
                    <div class="text-muted small">Belum ada tiket di layer ini.</div>
                </div>
            </div>

            <div class="sfcs-map-side-card">
                <div class="fw-semibold mb-2">Belum Terpetakan</div>
                <div class="sfcs-map-list">
                    @forelse($unmappedTicketsList as $ticket)
                        <div class="sfcs-map-list-item">
                            <div class="fw-semibold">{{ $ticket['kode_pengaduan'] }}</div>
                            <div class="small text-muted">{{ $ticket['judul'] }}</div>
                            <div class="small mt-2">{{ $ticket['gedung_nama'] ?? '-' }} / {{ $ticket['ruangan_nama'] ?? '-' }}</div>
                        </div>
                    @empty
                        <div class="text-muted small">Semua tiket aktif sudah punya mapping area atau titik denah.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const viewerId = @json($viewerId);
                const fileUrl = @json($fileUrl);
                const fileType = @json($map->file_type);
                const layers = @json($mapLayersPayload);
                const areasByLayer = @json($mapAreasByLayer);
                const ticketsByLayer = @json($mapTicketsByLayer);

                const stage = document.getElementById(`${viewerId}-stage`);
                const meta = document.getElementById(`${viewerId}-meta`);
                const ticketsPanel = document.getElementById(`${viewerId}-tickets`);
                const tabButtons = Array.from(document.querySelectorAll(`#${viewerId}-tabs [data-layer-id]`));
                let currentLayerId = Number(layers[0]?.id ?? 0);

                tabButtons.forEach((button) => {
                    button.addEventListener('click', async () => {
                        currentLayerId = Number(button.dataset.layerId);
                        syncTabs();
                        await renderCurrentLayer();
                    });
                });

                syncTabs();
                renderCurrentLayer();

                async function renderCurrentLayer() {
                    const layer = layers.find((item) => Number(item.id) === currentLayerId);
                    if (!layer || !stage) {
                        return;
                    }

                    const layerTickets = ticketsByLayer[layer.id] ?? [];
                    const layerAreas = areasByLayer[layer.id] ?? [];
                    renderMeta(layer, layerTickets.length);
                    renderTickets(layerTickets);

                    try {
                        await window.SFCSMap.renderMap({
                            container: stage,
                            fileType,
                            fileUrl,
                            layer,
                            areas: layerAreas,
                            tickets: layerTickets,
                        });
                    } catch (error) {
                        stage.innerHTML = '<div class="sfcs-map-empty">Viewer denah tidak bisa dimuat. Pastikan file denah masih tersedia dan storage dapat diakses.</div>';
                        console.error(error);
                    }
                }

                function renderMeta(layer, ticketCount) {
                    const items = [
                        layer.gedung_nama ? `Gedung: ${layer.gedung_nama}` : 'Layer umum',
                        layer.lantai_label ? `Lantai: ${layer.lantai_label}` : 'Tanpa lantai spesifik',
                        `${ticketCount} tiket aktif`,
                    ];
                    meta.innerHTML = items.map((item) => `<span class="sfcs-map-pill">${escapeHtml(item)}</span>`).join('');
                }

                function renderTickets(items) {
                    if (!items.length) {
                        ticketsPanel.innerHTML = '<div class="text-muted small">Belum ada tiket di layer ini.</div>';
                        return;
                    }

                    ticketsPanel.innerHTML = items.map((ticket) => `
                        <div class="sfcs-map-list-item">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="fw-semibold">${escapeHtml(ticket.kode_pengaduan)}</div>
                                    <div class="small text-muted">${escapeHtml(ticket.judul)}</div>
                                </div>
                                <span class="badge" style="background:${priorityColor(ticket.prioritas)}">${escapeHtml(String(ticket.prioritas || '').toUpperCase())}</span>
                            </div>
                            <div class="small mt-2">${escapeHtml(ticket.gedung_nama || '-')} / ${escapeHtml(ticket.ruangan_nama || '-')}</div>
                        </div>
                    `).join('');
                }

                function syncTabs() {
                    tabButtons.forEach((button) => {
                        const isActive = Number(button.dataset.layerId) === currentLayerId;
                        button.classList.toggle('btn-primary', isActive);
                        button.classList.toggle('btn-outline-primary', !isActive);
                    });
                }

                function priorityColor(priority) {
                    switch (String(priority || '').toLowerCase()) {
                        case 'urgent':
                            return '#dc2626';
                        case 'tinggi':
                            return '#f59e0b';
                        case 'sedang':
                            return '#0891b2';
                        default:
                            return '#64748b';
                    }
                }

                function escapeHtml(text) {
                    return String(text ?? '')
                        .replaceAll('&', '&amp;')
                        .replaceAll('<', '&lt;')
                        .replaceAll('>', '&gt;')
                        .replaceAll('"', '&quot;')
                        .replaceAll("'", '&#039;');
                }
            })();
        </script>
    @endpush
@endif
