@extends('layouts.sfcs')

@section('title', 'Editor Denah')

@php
    $layersPayload = $map->layers->values()->map(function ($layer) {
        return [
            'id' => $layer->id,
            'page_number' => $layer->page_number,
            'label' => $layer->label,
            'layer_scope' => $layer->layer_scope,
            'lantai_label' => $layer->lantai_label,
            'gedung_id' => $layer->gedung_id,
            'areas' => $layer->areas->map(function ($area) {
                return [
                    'id' => $area->id,
                    'layer_id' => $area->school_map_layer_id,
                    'label' => $area->label,
                    'shape_type' => $area->shape_type,
                    'geometry_json' => $area->geometry_json,
                    'color' => $area->color,
                    'icon' => $area->icon,
                    'gedung_id' => $area->gedung_id,
                    'ruangan_id' => $area->ruangan_id,
                    'is_clickable' => $area->is_clickable,
                ];
            })->values()->all(),
        ];
    })->all();
@endphp

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.denah.index') }}">Kelola Denah</a></li>
    <li class="breadcrumb-item active">{{ $map->nama }}</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Editor Denah</h1>
        <p class="page-subtitle">Gambar area lalu hubungkan ke gedung atau ruangan yang sudah ada.</p>
    </div>
    <a href="{{ route('admin.peta-digital') }}" class="btn btn-outline-primary">
        <i class="fas fa-eye me-2"></i>Lihat Peta Aktif
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold">{{ $map->nama }}</div>
                    <div class="small text-muted">Klik area kerja untuk menggambar marker, kotak, atau polygon.</div>
                </div>
                <div class="d-flex flex-wrap gap-2" id="editor-layer-tabs">
                    @foreach($map->layers as $layer)
                        <button type="button" class="btn btn-sm {{ $loop->first ? 'btn-primary' : 'btn-outline-primary' }}" data-layer-id="{{ $layer->id }}">
                            {{ $layer->label }}
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="button" class="btn btn-outline-dark btn-sm draw-mode-btn" data-mode="marker">Marker</button>
                    <button type="button" class="btn btn-outline-dark btn-sm draw-mode-btn" data-mode="rect">Rectangle</button>
                    <button type="button" class="btn btn-outline-dark btn-sm draw-mode-btn" data-mode="polygon">Polygon</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="reset-draft-btn">Reset Draft</button>
                </div>
                <div class="alert alert-light border small">
                    Mode marker: klik 1x. Mode rectangle: klik-drag-lepas. Mode polygon: klik beberapa titik lalu klik dua kali untuk selesai.
                </div>
                <div class="map-stage-shell border rounded-3 bg-light p-2">
                    <div id="editor-stage" class="map-stage"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header">Konfigurasi Layer</div>
            <div class="card-body">
                <form id="layer-config-form" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Nama Layer</label>
                        <input type="text" name="label" id="layer_label" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Scope Layer</label>
                        <select name="layer_scope" id="layer_scope" class="form-select" required>
                            <option value="general">General</option>
                            <option value="gedung_lantai">Gedung + Lantai</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gedung Terkait</label>
                        <select name="gedung_id" id="layer_gedung_id" class="form-select">
                            <option value="">Tidak spesifik</option>
                            @foreach($gedungs as $gedung)
                                <option value="{{ $gedung->id }}">{{ $gedung->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Label Lantai</label>
                        <input type="text" name="lantai_label" id="layer_lantai_label" class="form-control" placeholder="Contoh: Lantai 1">
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100">Simpan Konteks Layer</button>
                </form>
                <div class="form-text mt-2">
                    Set `Gedung + Lantai` jika layer ini dipakai langsung di form laporan siswa.
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header">Form Area</div>
            <div class="card-body">
                <form id="area-form" method="POST" action="">
                    @csrf
                    <input type="hidden" name="_method" id="area-form-method" value="POST">
                    <input type="hidden" name="shape_type" id="shape_type" value="">
                    <input type="hidden" name="geometry_json" id="geometry_json" value="">

                    <div class="mb-3">
                        <label class="form-label">Label Area</label>
                        <input type="text" name="label" id="label" class="form-control" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Warna</label>
                            <input type="color" name="color" id="color" class="form-control form-control-color" value="#2563eb">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Icon</label>
                            <input type="text" name="icon" id="icon" class="form-control" value="fa-location-dot">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Gedung</label>
                        <select name="gedung_id" id="gedung_id" class="form-select">
                            <option value="">Pilih Gedung</option>
                            @foreach($gedungs as $gedung)
                                <option value="{{ $gedung->id }}">{{ $gedung->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Ruangan</label>
                        <select name="ruangan_id" id="ruangan_id" class="form-select">
                            <option value="">Pilih Ruangan</option>
                            @foreach($ruangans as $ruangan)
                                <option value="{{ $ruangan->id }}" data-gedung-id="{{ $ruangan->gedung_id }}">
                                    {{ $ruangan->kode }} - {{ $ruangan->nama }} (Lt. {{ $ruangan->lantai }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="is_clickable" name="is_clickable" checked>
                        <label class="form-check-label" for="is_clickable">Area bisa diklik di viewer</label>
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary" id="area-submit-btn">Simpan Area</button>
                        <button type="button" class="btn btn-outline-secondary" id="new-area-btn">Mode Area Baru</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">Area di Layer Aktif</div>
            <div class="card-body" id="area-list-panel">
                <div class="text-muted small">Belum ada area pada layer ini.</div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .map-stage-shell {
            overflow: auto;
            min-height: 520px;
        }

        .map-stage {
            position: relative;
            width: fit-content;
            margin: 0 auto;
            cursor: crosshair;
        }

        .map-stage canvas,
        .map-stage img {
            display: block;
            max-width: 100%;
            border-radius: 0.75rem;
        }

        .map-overlay-svg {
            position: absolute;
            inset: 0;
            overflow: visible;
        }

        .map-area-item {
            border: 1px solid #e5e7eb;
            border-radius: 0.8rem;
            padding: 0.75rem;
        }
    </style>
@endpush

@push('scripts')
    <script type="module">
        import * as pdfjsLib from 'https://cdn.jsdelivr.net/npm/pdfjs-dist@4.8.69/build/pdf.min.mjs';

        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@4.8.69/build/pdf.worker.min.mjs';

        const fileType = @json($map->file_type);
        const fileUrl = @json($fileUrl);
        const layers = @json($layersPayload);
        const storeAreaTemplate = @json(url("/admin/denah/{$map->id}/layers/__LAYER__/areas"));
        const updateAreaTemplate = @json(url('/admin/denah/areas/__AREA__'));
        const deleteAreaTemplate = @json(url('/admin/denah/areas/__AREA__'));
        const updateLayerTemplate = @json(url("/admin/denah/{$map->id}/layers/__LAYER__"));

        const stage = document.getElementById('editor-stage');
        const layerButtons = Array.from(document.querySelectorAll('#editor-layer-tabs [data-layer-id]'));
        const drawButtons = Array.from(document.querySelectorAll('.draw-mode-btn'));
        const form = document.getElementById('area-form');
        const formMethod = document.getElementById('area-form-method');
        const submitButton = document.getElementById('area-submit-btn');
        const newAreaButton = document.getElementById('new-area-btn');
        const resetDraftButton = document.getElementById('reset-draft-btn');
        const areaListPanel = document.getElementById('area-list-panel');
        const layerConfigForm = document.getElementById('layer-config-form');
        const shapeInput = document.getElementById('shape_type');
        const geometryInput = document.getElementById('geometry_json');
        const gedungInput = document.getElementById('gedung_id');
        const ruanganInput = document.getElementById('ruangan_id');
        const layerLabelInput = document.getElementById('layer_label');
        const layerScopeInput = document.getElementById('layer_scope');
        const layerGedungInput = document.getElementById('layer_gedung_id');
        const layerLantaiLabelInput = document.getElementById('layer_lantai_label');
        const pdfDocPromise = fileType === 'pdf' ? pdfjsLib.getDocument(fileUrl).promise : null;

        let currentLayerId = Number(layers[0]?.id ?? 0);
        let currentMode = null;
        let draftGeometry = null;
        let editingAreaId = null;
        let stageWidth = 0;
        let stageHeight = 0;

        layerButtons.forEach((button) => {
            button.addEventListener('click', () => {
                currentLayerId = Number(button.dataset.layerId);
                editingAreaId = null;
                draftGeometry = null;
                currentMode = null;
                syncLayerButtons();
                resetAreaForm();
                renderCurrentLayer();
            });
        });

        drawButtons.forEach((button) => {
            button.addEventListener('click', () => {
                currentMode = button.dataset.mode;
                if (currentMode !== 'polygon') {
                    draftGeometry = null;
                } else if (!draftGeometry || draftGeometry.shapeType !== 'polygon') {
                    draftGeometry = { points: [] };
                }
                syncDrawButtons();
                updateGeometryInputs();
                renderCurrentLayer();
            });
        });

        ruanganInput.addEventListener('change', () => {
            const option = ruanganInput.selectedOptions[0];
            if (option?.dataset.gedungId) {
                gedungInput.value = option.dataset.gedungId;
            }
        });

        newAreaButton.addEventListener('click', () => {
            editingAreaId = null;
            draftGeometry = null;
            currentMode = null;
            resetAreaForm();
            renderCurrentLayer();
        });

        resetDraftButton.addEventListener('click', () => {
            draftGeometry = null;
            currentMode = null;
            syncDrawButtons();
            updateGeometryInputs();
            renderCurrentLayer();
        });

        form.addEventListener('submit', (event) => {
            if (!draftGeometry || !currentMode) {
                event.preventDefault();
                alert('Gambar area dulu sebelum menyimpan.');
                return;
            }

            shapeInput.value = currentMode;
            geometryInput.value = JSON.stringify(currentMode === 'polygon' ? { points: draftGeometry.points } : draftGeometry);
        });

        renderCurrentLayer();

        async function renderCurrentLayer() {
            const layer = layers.find((item) => Number(item.id) === currentLayerId);
            if (!layer) {
                return;
            }

            stage.innerHTML = '';
            syncLayerButtons();
            syncLayerConfig(layer);
            renderAreaList(layer);

            if (fileType === 'pdf') {
                const pdfDoc = await pdfDocPromise;
                const page = await pdfDoc.getPage(layer.page_number);
                const viewport = page.getViewport({ scale: 1 });
                const availableWidth = Math.max(320, (stage.parentElement?.clientWidth ?? 996) - 16);
                const maxWidth = Math.min(980, availableWidth);
                const scale = maxWidth / viewport.width;
                const scaledViewport = page.getViewport({ scale });
                const canvas = document.createElement('canvas');
                canvas.width = scaledViewport.width;
                canvas.height = scaledViewport.height;
                stageWidth = scaledViewport.width;
                stageHeight = scaledViewport.height;
                stage.style.width = `${stageWidth}px`;
                stage.style.height = `${stageHeight}px`;
                stage.appendChild(canvas);
                await page.render({
                    canvasContext: canvas.getContext('2d'),
                    viewport: scaledViewport,
                }).promise;
                attachOverlay(layer);
                return;
            }

            const image = document.createElement('img');
            image.src = fileUrl;
            image.alt = layer.label;
            image.onload = () => {
                const availableWidth = Math.max(320, (stage.parentElement?.clientWidth ?? 996) - 16);
                const maxWidth = Math.min(980, availableWidth);
                stageWidth = Math.min(maxWidth, image.naturalWidth);
                stageHeight = image.naturalHeight * (stageWidth / image.naturalWidth);
                image.width = stageWidth;
                image.height = stageHeight;
                stage.style.width = `${stageWidth}px`;
                stage.style.height = `${stageHeight}px`;
                attachOverlay(layer);
            };
            stage.appendChild(image);
        }

        function attachOverlay(layer) {
            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('class', 'map-overlay-svg');
            svg.setAttribute('width', `${stageWidth}`);
            svg.setAttribute('height', `${stageHeight}`);
            svg.setAttribute('viewBox', `0 0 ${stageWidth} ${stageHeight}`);
            svg.style.cursor = currentMode ? 'crosshair' : 'default';

            layer.areas.forEach((area) => {
                const node = drawAreaShape(area.shape_type, area.geometry_json, area.color, false);
                if (!node) {
                    return;
                }
                node.style.cursor = 'pointer';
                node.addEventListener('click', (event) => {
                    event.stopPropagation();
                    loadAreaToForm(area);
                });
                svg.appendChild(node);
            });

            if (draftGeometry && currentMode) {
                const node = drawAreaShape(currentMode, currentMode === 'polygon' ? { points: draftGeometry.points } : draftGeometry, '#ef4444', true);
                if (node) {
                    svg.appendChild(node);
                }
            }

            let isDraggingRect = false;
            let rectStartPoint = null;

            svg.addEventListener('click', (event) => {
                if (!currentMode || currentMode === 'rect') {
                    return;
                }

                const point = getRelativePoint(event);
                if (currentMode === 'marker') {
                    draftGeometry = point;
                    updateGeometryInputs();
                    renderCurrentLayer();
                    return;
                }

                if (currentMode === 'polygon') {
                    draftGeometry = draftGeometry && Array.isArray(draftGeometry.points) ? draftGeometry : { points: [] };
                    draftGeometry.points.push(point);
                    updateGeometryInputs();
                    renderCurrentLayer();
                }
            });

            svg.addEventListener('dblclick', (event) => {
                if (currentMode === 'polygon') {
                    event.preventDefault();
                    updateGeometryInputs();
                    renderCurrentLayer();
                }
            });

            svg.addEventListener('mousedown', (event) => {
                if (currentMode !== 'rect') {
                    return;
                }
                isDraggingRect = true;
                rectStartPoint = getRelativePoint(event);
            });

            svg.addEventListener('mouseup', (event) => {
                if (currentMode !== 'rect') {
                    return;
                }
                if (!isDraggingRect || !rectStartPoint) {
                    return;
                }
                const endPoint = getRelativePoint(event);
                draftGeometry = normalizeRect(rectStartPoint, endPoint);
                updateGeometryInputs();
                isDraggingRect = false;
                rectStartPoint = null;
                renderCurrentLayer();
            });

            stage.appendChild(svg);
        }

        function loadAreaToForm(area) {
            currentLayerId = Number(area.layer_id ?? currentLayerId);
            currentMode = area.shape_type;
            editingAreaId = area.id;
            draftGeometry = area.shape_type === 'polygon'
                ? { points: area.geometry_json.points ?? [] }
                : area.geometry_json;

            form.action = updateAreaTemplate.replace('__AREA__', String(area.id));
            formMethod.value = 'PUT';
            submitButton.textContent = 'Update Area';
            document.getElementById('label').value = area.label;
            document.getElementById('color').value = area.color || '#2563eb';
            document.getElementById('icon').value = area.icon || 'fa-location-dot';
            gedungInput.value = area.gedung_id ?? '';
            ruanganInput.value = area.ruangan_id ?? '';
            document.getElementById('is_clickable').checked = Boolean(area.is_clickable);
            updateGeometryInputs();
            syncDrawButtons();
            renderCurrentLayer();
        }

        function resetAreaForm() {
            form.action = storeAreaTemplate.replace('__LAYER__', String(currentLayerId));
            formMethod.value = 'POST';
            submitButton.textContent = 'Simpan Area';
            form.reset();
            document.getElementById('color').value = '#2563eb';
            document.getElementById('icon').value = 'fa-location-dot';
            document.getElementById('is_clickable').checked = true;
            shapeInput.value = '';
            geometryInput.value = '';
        }

        function renderAreaList(layer) {
            if (!layer.areas.length) {
                areaListPanel.innerHTML = '<div class="text-muted small">Belum ada area pada layer ini.</div>';
                return;
            }

            areaListPanel.innerHTML = '';
            layer.areas.forEach((area) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'map-area-item mb-3';
                wrapper.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="fw-semibold">${escapeHtml(area.label)}</div>
                            <div class="small text-muted">${escapeHtml(area.shape_type)} / ${escapeHtml(area.icon || 'fa-location-dot')}</div>
                        </div>
                        <span class="badge" style="background:${area.color}">${area.ruangan_id ? 'Ruangan' : 'Gedung'}</span>
                    </div>
                    <div class="small mt-2">Gedung ID: ${area.gedung_id ?? '-'} | Ruangan ID: ${area.ruangan_id ?? '-'}</div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-sm btn-outline-primary area-edit-btn">Edit</button>
                        <form method="POST" action="${deleteAreaTemplate.replace('__AREA__', String(area.id))}" onsubmit="return confirm('Hapus area ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </div>
                `;
                wrapper.querySelector('.area-edit-btn').addEventListener('click', () => loadAreaToForm(area));
                areaListPanel.appendChild(wrapper);
            });
        }

        function syncLayerButtons() {
            layerButtons.forEach((button) => {
                const isActive = Number(button.dataset.layerId) === currentLayerId;
                button.classList.toggle('btn-primary', isActive);
                button.classList.toggle('btn-outline-primary', !isActive);
            });
            form.action = editingAreaId
                ? updateAreaTemplate.replace('__AREA__', String(editingAreaId))
                : storeAreaTemplate.replace('__LAYER__', String(currentLayerId));
        }

        function syncLayerConfig(layer) {
            if (!layerConfigForm || !layer) {
                return;
            }

            layerConfigForm.action = updateLayerTemplate.replace('__LAYER__', String(layer.id));
            layerLabelInput.value = layer.label || '';
            layerScopeInput.value = layer.layer_scope || 'general';
            layerGedungInput.value = layer.gedung_id || '';
            layerLantaiLabelInput.value = layer.lantai_label || '';
        }

        function syncDrawButtons() {
            drawButtons.forEach((button) => {
                const isActive = button.dataset.mode === currentMode;
                button.classList.toggle('btn-dark', isActive);
                button.classList.toggle('btn-outline-dark', !isActive);
            });
        }

        function updateGeometryInputs() {
            shapeInput.value = currentMode || '';
            if (!draftGeometry || !currentMode) {
                geometryInput.value = '';
                return;
            }

            geometryInput.value = JSON.stringify(currentMode === 'polygon' ? { points: draftGeometry.points } : draftGeometry);
        }

        function getRelativePoint(event) {
            const bounds = stage.getBoundingClientRect();
            const x = (event.clientX - bounds.left) / bounds.width;
            const y = (event.clientY - bounds.top) / bounds.height;
            return {
                x: clamp(x),
                y: clamp(y),
            };
        }

        function normalizeRect(start, end) {
            const x = Math.min(start.x, end.x);
            const y = Math.min(start.y, end.y);
            return {
                x,
                y,
                width: Math.abs(end.x - start.x),
                height: Math.abs(end.y - start.y),
            };
        }

        function clamp(value) {
            return Math.max(0, Math.min(1, value));
        }

        function drawAreaShape(shapeType, geometry, color, isDraft) {
            if (!geometry) {
                return null;
            }

            if (shapeType === 'marker') {
                const group = document.createElementNS('http://www.w3.org/2000/svg', 'g');
                const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                circle.setAttribute('cx', `${geometry.x * stageWidth}`);
                circle.setAttribute('cy', `${geometry.y * stageHeight}`);
                circle.setAttribute('r', isDraft ? '10' : '8');
                circle.setAttribute('fill', color);
                circle.setAttribute('stroke', '#fff');
                circle.setAttribute('stroke-width', '2');
                if (isDraft) {
                    circle.setAttribute('opacity', '0.8');
                }
                group.appendChild(circle);
                return group;
            }

            if (shapeType === 'rect') {
                const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                rect.setAttribute('x', `${geometry.x * stageWidth}`);
                rect.setAttribute('y', `${geometry.y * stageHeight}`);
                rect.setAttribute('width', `${geometry.width * stageWidth}`);
                rect.setAttribute('height', `${geometry.height * stageHeight}`);
                rect.setAttribute('fill', hexToRgba(color, isDraft ? 0.12 : 0.22));
                rect.setAttribute('stroke', color);
                rect.setAttribute('stroke-width', isDraft ? '3' : '2');
                return rect;
            }

            if (shapeType === 'polygon') {
                const polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
                const points = (geometry.points ?? []).map((point) => `${point.x * stageWidth},${point.y * stageHeight}`).join(' ');
                polygon.setAttribute('points', points);
                polygon.setAttribute('fill', hexToRgba(color, isDraft ? 0.12 : 0.22));
                polygon.setAttribute('stroke', color);
                polygon.setAttribute('stroke-width', isDraft ? '3' : '2');
                return polygon;
            }

            return null;
        }

        function hexToRgba(hex, alpha) {
            const normalized = String(hex || '#2563eb').replace('#', '');
            const value = normalized.length === 3
                ? normalized.split('').map((item) => item + item).join('')
                : normalized;
            const red = parseInt(value.substring(0, 2), 16);
            const green = parseInt(value.substring(2, 4), 16);
            const blue = parseInt(value.substring(4, 6), 16);
            return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
        }

        function escapeHtml(text) {
            return String(text ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        resetAreaForm();
        syncDrawButtons();
    </script>
@endpush
@endsection
