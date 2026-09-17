@php
    $pickerId = $pickerId ?? 'pengaduan-map-picker';
    $selectedMapId = $selectedMapId ?? null;
    $selectedLayerId = $selectedLayerId ?? null;
    $selectedPointX = $selectedPointX ?? null;
    $selectedPointY = $selectedPointY ?? null;
    $selectedZoom = $selectedZoom ?? 2;
@endphp

@include('partials.school-map-assets')

<div class="col-12" id="{{ $pickerId }}-wrapper">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <label class="form-label fw-bold fs-6 mb-1">
                        <span class="badge bg-primary rounded-pill me-2">3A</span>
                        Pilih Titik Lokasi di Denah
                    </label>
                    <div class="text-muted small">Setelah memilih gedung dan lantai, klik titik kerusakan di denah. Ini lebih akurat daripada patokan teks saja.</div>
                </div>
                <span class="sfcs-map-pill" id="{{ $pickerId }}-status-pill">Menunggu lokasi</span>
            </div>

            <input type="hidden" name="school_map_id" id="{{ $pickerId }}-school-map-id" value="{{ old('school_map_id', $selectedMapId) }}">
            <input type="hidden" name="school_map_layer_id" id="{{ $pickerId }}-school-map-layer-id" value="{{ old('school_map_layer_id', $selectedLayerId) }}">
            <input type="hidden" name="map_point_x" id="{{ $pickerId }}-map-point-x" value="{{ old('map_point_x', $selectedPointX) }}">
            <input type="hidden" name="map_point_y" id="{{ $pickerId }}-map-point-y" value="{{ old('map_point_y', $selectedPointY) }}">
            <input type="hidden" name="map_zoom" id="{{ $pickerId }}-map-zoom" value="{{ old('map_zoom', $selectedZoom) }}">
            <input type="hidden" name="skip_map_point" id="{{ $pickerId }}-skip-map-point" value="{{ old('skip_map_point', '0') }}">

            <div id="{{ $pickerId }}-message" class="sfcs-picker-note mb-3">
                Langkah cepat:
                1) Pilih gedung.
                2) Pilih lantai.
                3) Klik titik kerusakan di denah.
            </div>

            <div id="{{ $pickerId }}-error" class="sfcs-picker-error mb-3 {{ $errors->has('map_point_x') || $errors->has('school_map_layer_id') ? '' : 'd-none' }}">
                {{ $errors->first('map_point_x') ?: $errors->first('school_map_layer_id') }}
            </div>

            <div id="{{ $pickerId }}-meta" class="d-flex flex-wrap gap-2 mb-3"></div>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="{{ $pickerId }}-reset-point-btn">
                    <i class="fas fa-rotate-left me-1"></i>Reset Titik
                </button>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="{{ $pickerId }}-skip-map-checkbox" {{ old('skip_map_point') ? 'checked' : '' }}>
                    <label class="form-check-label small text-muted" for="{{ $pickerId }}-skip-map-checkbox">
                        Saya kesulitan menentukan titik di denah, gunakan lokasi teks saja.
                    </label>
                </div>
            </div>

            <div id="{{ $pickerId }}-frame" class="sfcs-map-frame d-none">
                <div id="{{ $pickerId }}-stage" class="sfcs-map-stage"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (() => {
            const pickerId = @json($pickerId);
            const wrapper = document.getElementById(`${pickerId}-wrapper`);
            const stage = document.getElementById(`${pickerId}-stage`);
            const frame = document.getElementById(`${pickerId}-frame`);
            const message = document.getElementById(`${pickerId}-message`);
            const errorBox = document.getElementById(`${pickerId}-error`);
            const meta = document.getElementById(`${pickerId}-meta`);
            const statusPill = document.getElementById(`${pickerId}-status-pill`);
            const gedungSelect = document.getElementById('gedung_id');
            const lantaiSelect = document.getElementById('lantai');
            const form = document.getElementById(@json($formId ?? 'pengaduanForm'));

            const mapIdInput = document.getElementById(`${pickerId}-school-map-id`);
            const layerIdInput = document.getElementById(`${pickerId}-school-map-layer-id`);
            const pointXInput = document.getElementById(`${pickerId}-map-point-x`);
            const pointYInput = document.getElementById(`${pickerId}-map-point-y`);
            const zoomInput = document.getElementById(`${pickerId}-map-zoom`);
            const skipMapInput = document.getElementById(`${pickerId}-skip-map-point`);
            const skipMapCheckbox = document.getElementById(`${pickerId}-skip-map-checkbox`);
            const resetPointButton = document.getElementById(`${pickerId}-reset-point-btn`);

            const initialSelection = {
                mapId: mapIdInput.value || null,
                layerId: layerIdInput.value || null,
                x: pointXInput.value || null,
                y: pointYInput.value || null,
                zoom: zoomInput.value || 2,
            };

            let payload = null;
            let currentLayer = null;

            gedungSelect?.addEventListener('change', loadMapForLocation);
            lantaiSelect?.addEventListener('change', loadMapForLocation);
            skipMapCheckbox?.addEventListener('change', () => {
                skipMapInput.value = skipMapCheckbox.checked ? '1' : '0';
                if (skipMapCheckbox.checked) {
                    clearMapPoint();
                    clearPickerError();
                    setStatus('Pakai lokasi teks');
                } else {
                    setStatus('Klik titik kerusakan');
                }
            });
            resetPointButton?.addEventListener('click', () => {
                clearMapPoint();
                setStatus('Klik titik kerusakan');
            });

            form?.addEventListener('submit', (event) => {
                const requiresPoint = payload?.available && currentLayer;
                if (!requiresPoint) {
                    clearPickerError();
                    return;
                }

                if (!skipMapCheckbox.checked && (!pointXInput.value || !pointYInput.value)) {
                    event.preventDefault();
                    showPickerError('Titik lokasi di denah wajib dipilih untuk lokasi ini.');
                    document.getElementById(`${pickerId}-message`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });

            loadMapForLocation();

            async function loadMapForLocation() {
                clearPickerError();
                frame.classList.add('d-none');
                meta.innerHTML = '';
                currentLayer = null;

                const gedungId = gedungSelect?.value;
                const lantai = lantaiSelect?.value;

                if (!gedungId) {
                    showPickerWrapper();
                    applyEmptyState('Pilih gedung dulu. Setelah itu peta denah akan dimuat otomatis untuk membantu pilih titik kerusakan.', 'Menunggu gedung');
                    resetHiddenFields({ preserveOld: true });
                    return;
                }

                setStatus('Memuat denah...');

                try {
                    const params = new URLSearchParams({ gedung_id: gedungId });
                    if (lantai) {
                        params.set('lantai', lantai);
                    }
                    const response = await fetch(`{{ route('api.school-map.active') }}?${params.toString()}`, {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    });

                    payload = await response.json();
                    if (payload?.hide_picker) {
                        hidePickerWrapper();
                        resetHiddenFields();
                        payload = { available: false, hide_picker: true };
                        currentLayer = null;
                        return;
                    }

                    showPickerWrapper();
                    if (!response.ok || !payload?.available) {
                        applyEmptyState(payload?.message || 'Belum ada denah aktif. Sistem akan memakai lokasi teks biasa.', 'Denah nonaktif');
                        resetHiddenFields();
                        return;
                    }

                    currentLayer = payload.layers.find((layer) => Number(layer.id) === Number(payload.selected_layer_id)) || null;
                    mapIdInput.value = payload.map.id;

                    if (!currentLayer) {
                        applyEmptyState('Denah sekolah aktif, tetapi admin belum membuat layer untuk gedung dan lantai ini. Laporan tetap bisa dikirim memakai lokasi teks.', 'Belum dimapping');
                        resetHiddenFields({ keepMapId: true });
                        return;
                    }

                    layerIdInput.value = currentLayer.id;
                    frame.classList.remove('d-none');
                    setStatus('Klik titik kerusakan');
                    renderMeta(currentLayer);

                    const selectedPoint = shouldReuseInitialPoint(currentLayer.id)
                        ? { x: Number(initialSelection.x), y: Number(initialSelection.y) }
                        : (pointXInput.value && pointYInput.value ? { x: Number(pointXInput.value), y: Number(pointYInput.value) } : null);

                    await window.SFCSMap.renderMap({
                        container: stage,
                        fileType: payload.map.file_type,
                        fileUrl: payload.map.file_url,
                        layer: currentLayer,
                        areas: currentLayer.areas || [],
                        selectedPoint,
                        pointZoom: Number(zoomInput.value || initialSelection.zoom || 2),
                        allowPicking: true,
                        onPick: (point) => {
                            pointXInput.value = point.x;
                            pointYInput.value = point.y;
                            zoomInput.value = Math.max(1, Math.round(Number(point.zoom || 2)));
                            layerIdInput.value = currentLayer.id;
                            mapIdInput.value = payload.map.id;
                            skipMapCheckbox.checked = false;
                            skipMapInput.value = '0';
                            setStatus('Titik tersimpan');
                            clearPickerError();
                            syncResetButton();
                        },
                    });

                    message.textContent = 'Klik tepat di area fasilitas yang rusak. Marker bisa dipindah lagi dengan klik ulang atau drag.';
                    message.className = 'sfcs-picker-note mb-3';
                    syncResetButton();
                } catch (error) {
                    console.error(error);
                    applyEmptyState('Viewer denah gagal dimuat. Laporan tetap bisa dikirim dengan lokasi teks biasa.', 'Viewer gagal');
                    resetHiddenFields();
                    payload = { available: false };
                    currentLayer = null;
                    skipMapCheckbox.checked = true;
                    skipMapInput.value = '1';
                }
            }

            function applyEmptyState(text, status) {
                message.textContent = text;
                message.className = 'sfcs-picker-note mb-3';
                statusPill.textContent = status;
                statusPill.style.background = '#f8fafc';
                statusPill.style.color = '#475569';
                statusPill.style.borderColor = '#cbd5e1';
                stage.innerHTML = '';
                frame.classList.add('d-none');
                meta.innerHTML = '';
            }

            function hidePickerWrapper() {
                wrapper?.classList.add('d-none');
                if (skipMapCheckbox) {
                    skipMapCheckbox.checked = true;
                }
                if (skipMapInput) {
                    skipMapInput.value = '1';
                }
                clearMapPoint();
                clearPickerError();
            }

            function showPickerWrapper() {
                wrapper?.classList.remove('d-none');
            }

            function renderMeta(layer) {
                const items = [
                    layer.gedung_nama ? `Gedung: ${layer.gedung_nama}` : 'Layer umum',
                    layer.lantai_label ? `Lantai: ${layer.lantai_label}` : 'Tanpa lantai spesifik',
                    `Halaman: ${layer.page_number}`,
                ];
                meta.innerHTML = items.map((item) => `<span class="sfcs-map-pill">${escapeHtml(item)}</span>`).join('');
            }

            function resetHiddenFields(options = {}) {
                if (!options.keepMapId) {
                    mapIdInput.value = '';
                }
                layerIdInput.value = '';
                pointXInput.value = '';
                pointYInput.value = '';
                zoomInput.value = '';
                syncResetButton();
            }

            function setStatus(text) {
                statusPill.textContent = text;
                statusPill.style.background = '#eff6ff';
                statusPill.style.color = '#1d4ed8';
                statusPill.style.borderColor = '#bfdbfe';
            }

            function showPickerError(text) {
                errorBox.textContent = text;
                errorBox.classList.remove('d-none');
            }

            function clearPickerError() {
                errorBox.textContent = '';
                errorBox.classList.add('d-none');
            }

            function clearMapPoint() {
                pointXInput.value = '';
                pointYInput.value = '';
                zoomInput.value = '';
                syncResetButton();
            }

            function syncResetButton() {
                const hasPoint = Boolean(pointXInput.value && pointYInput.value);
                resetPointButton?.classList.toggle('d-none', !hasPoint);
            }

            function shouldReuseInitialPoint(layerId) {
                return initialSelection.layerId && Number(initialSelection.layerId) === Number(layerId) && initialSelection.x && initialSelection.y;
            }

            function escapeHtml(text) {
                return String(text ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            syncResetButton();
            if (skipMapCheckbox?.checked) {
                skipMapInput.value = '1';
                setStatus('Pakai lokasi teks');
            }
        })();
    </script>
@endpush
