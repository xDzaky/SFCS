@once
    @push('styles')
        <link
            rel="stylesheet"
            href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
            crossorigin=""
        >
        <style>
            .sfcs-map-frame {
                position: relative;
                border: 1px solid #e2e8f0;
                border-radius: 1rem;
                overflow: hidden;
                background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
            }

            .sfcs-map-stage {
                width: 100%;
                min-height: 320px;
            }

            .sfcs-map-stage.is-mini {
                min-height: 240px;
            }

            .sfcs-map-empty {
                padding: 1.25rem;
                border: 1px dashed #cbd5e1;
                border-radius: 1rem;
                background: #f8fafc;
                color: #64748b;
            }

            .sfcs-map-toolbar {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .sfcs-map-pill {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                border-radius: 999px;
                padding: 0.35rem 0.8rem;
                font-size: 0.82rem;
                background: #eff6ff;
                color: #1d4ed8;
                border: 1px solid #bfdbfe;
            }

            .sfcs-map-side-card {
                border: 1px solid #e2e8f0;
                border-radius: 1rem;
                background: #fff;
                padding: 1rem;
                box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            }

            .sfcs-map-list {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
                max-height: 420px;
                overflow: auto;
            }

            .sfcs-map-list-item {
                border: 1px solid #e2e8f0;
                border-radius: 0.9rem;
                padding: 0.8rem;
                background: #fff;
            }

            .sfcs-map-list-item.is-active {
                border-color: #93c5fd;
                background: #eff6ff;
            }

            .sfcs-picker-note {
                border: 1px solid #bfdbfe;
                background: #eff6ff;
                color: #1d4ed8;
                border-radius: 0.9rem;
                padding: 0.85rem 1rem;
            }

            .sfcs-picker-error {
                border: 1px solid #fecaca;
                background: #fef2f2;
                color: #b91c1c;
                border-radius: 0.9rem;
                padding: 0.75rem 1rem;
            }
        </style>
    @endpush

    @push('scripts')
        <script
            src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""
        ></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" referrerpolicy="no-referrer"></script>
        <script>
            (() => {
                if (window.SFCSMap) {
                    return;
                }

                if (window.pdfjsLib) {
                    window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                }

                const backgroundCache = new Map();

                async function getBackground(fileType, fileUrl, pageNumber) {
                    const cacheKey = `${fileType}:${fileUrl}:${pageNumber}`;
                    if (backgroundCache.has(cacheKey)) {
                        return backgroundCache.get(cacheKey);
                    }

                    let payload;
                    if (fileType === 'pdf') {
                        const pdf = await window.pdfjsLib.getDocument(fileUrl).promise;
                        const page = await pdf.getPage(pageNumber);
                        const viewport = page.getViewport({ scale: 1.6 });
                        const canvas = document.createElement('canvas');
                        const context = canvas.getContext('2d');
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        await page.render({ canvasContext: context, viewport }).promise;
                        payload = {
                            imageUrl: canvas.toDataURL('image/png'),
                            width: viewport.width,
                            height: viewport.height,
                        };
                    } else {
                        payload = await new Promise((resolve, reject) => {
                            const image = new Image();
                            image.onload = () => resolve({
                                imageUrl: fileUrl,
                                width: image.naturalWidth || image.width,
                                height: image.naturalHeight || image.height,
                            });
                            image.onerror = reject;
                            image.src = fileUrl;
                        });
                    }

                    backgroundCache.set(cacheKey, payload);
                    return payload;
                }

                function normalizedToLatLng(geometry, width, height) {
                    return [geometry.y * height, geometry.x * width];
                }

                function boundsForSize(width, height) {
                    return [[0, 0], [height, width]];
                }

                function getAnchorPoint(item, width, height) {
                    const geometry = item.geometry_json || {};
                    if (item.shape_type === 'marker') {
                        return normalizedToLatLng(geometry, width, height);
                    }

                    if (item.shape_type === 'rect') {
                        return [
                            (geometry.y + (geometry.height / 2)) * height,
                            (geometry.x + (geometry.width / 2)) * width,
                        ];
                    }

                    if (item.shape_type === 'polygon' && Array.isArray(geometry.points) && geometry.points.length > 0) {
                        const total = geometry.points.reduce((carry, point) => {
                            carry.x += point.x;
                            carry.y += point.y;
                            return carry;
                        }, { x: 0, y: 0 });

                        return [
                            (total.y / geometry.points.length) * height,
                            (total.x / geometry.points.length) * width,
                        ];
                    }

                    return null;
                }

                function destroyExistingMap(container) {
                    if (container._leaflet_map_instance) {
                        container._leaflet_map_instance.remove();
                        container._leaflet_map_instance = null;
                    }

                    // Leaflet can leave internal marker on the container during rapid re-renders.
                    // Ensure we always reset it before creating a new map instance.
                    if (container._leaflet_id) {
                        delete container._leaflet_id;
                    }
                    container.innerHTML = '';
                }

                function colorByPriority(priority) {
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

                async function renderMap(config) {
                    const {
                        container,
                        fileType,
                        fileUrl,
                        layer,
                        areas = [],
                        tickets = [],
                        selectedPoint = null,
                        pointZoom = 2,
                        allowPicking = false,
                        onPick = null,
                        focusAreaId = null,
                    } = config;

                    if (!container || !layer) {
                        return null;
                    }

                    const renderToken = Date.now() + Math.random();
                    container.__sfcs_render_token = renderToken;
                    destroyExistingMap(container);
                    const background = await getBackground(fileType, fileUrl, layer.page_number);

                    // If a newer render request already started, stop this one.
                    if (container.__sfcs_render_token !== renderToken) {
                        return null;
                    }

                    destroyExistingMap(container);
                    const bounds = boundsForSize(background.width, background.height);
                    const map = L.map(container, {
                        crs: L.CRS.Simple,
                        zoomSnap: 0.25,
                        minZoom: -2,
                        maxZoom: 5,
                        attributionControl: false,
                    });
                    container._leaflet_map_instance = map;

                    L.imageOverlay(background.imageUrl, bounds).addTo(map);

                    areas.forEach((area) => {
                        let overlay = null;
                        if (area.shape_type === 'marker') {
                            overlay = L.circleMarker(normalizedToLatLng(area.geometry_json, background.width, background.height), {
                                radius: focusAreaId === area.id ? 9 : 7,
                                color: area.color || '#2563eb',
                                fillColor: area.color || '#2563eb',
                                fillOpacity: 0.7,
                                weight: focusAreaId === area.id ? 3 : 2,
                            });
                        }

                        if (area.shape_type === 'rect') {
                            const rectBounds = [
                                [area.geometry_json.y * background.height, area.geometry_json.x * background.width],
                                [(area.geometry_json.y + area.geometry_json.height) * background.height, (area.geometry_json.x + area.geometry_json.width) * background.width],
                            ];
                            overlay = L.rectangle(rectBounds, {
                                color: area.color || '#2563eb',
                                weight: focusAreaId === area.id ? 3 : 2,
                                fillOpacity: 0.15,
                            });
                        }

                        if (area.shape_type === 'polygon') {
                            const latlngs = (area.geometry_json.points || []).map((point) => normalizedToLatLng(point, background.width, background.height));
                            overlay = L.polygon(latlngs, {
                                color: area.color || '#2563eb',
                                weight: focusAreaId === area.id ? 3 : 2,
                                fillOpacity: 0.15,
                            });
                        }

                        if (overlay) {
                            if (area.label) {
                                overlay.bindTooltip(area.label, { sticky: true });
                            }
                            overlay.addTo(map);
                        }
                    });

                    tickets.forEach((ticket) => {
                        const anchor = getAnchorPoint(ticket, background.width, background.height);
                        if (!anchor) {
                            return;
                        }

                        const marker = L.circleMarker(anchor, {
                            radius: ticket.is_manual_point ? 9 : 8,
                            color: '#ffffff',
                            weight: 2,
                            fillColor: colorByPriority(ticket.prioritas),
                            fillOpacity: 1,
                        }).addTo(map);

                        marker.bindPopup(`
                            <div style="min-width: 180px;">
                                <strong>${escapeHtml(ticket.kode_pengaduan || '')}</strong><br>
                                <span>${escapeHtml(ticket.judul || '')}</span><br>
                                <small>${escapeHtml(ticket.gedung_nama || '-')} / ${escapeHtml(ticket.ruangan_nama || '-')}</small>
                            </div>
                        `);
                    });

                    let pointMarker = null;
                    if (selectedPoint && selectedPoint.x !== null && selectedPoint.y !== null) {
                        pointMarker = L.marker(normalizedToLatLng(selectedPoint, background.width, background.height), {
                            draggable: Boolean(allowPicking),
                        }).addTo(map);
                    }

                    function updateSelectedPoint(latlng) {
                        const point = {
                            x: Number((latlng.lng / background.width).toFixed(6)),
                            y: Number((latlng.lat / background.height).toFixed(6)),
                            zoom: Math.max(1, Math.round(map.getZoom())),
                        };

                        if (!pointMarker) {
                            pointMarker = L.marker(latlng, { draggable: Boolean(allowPicking) }).addTo(map);
                        } else {
                            pointMarker.setLatLng(latlng);
                        }

                        if (allowPicking && pointMarker) {
                            pointMarker.on('dragend', () => {
                                const current = pointMarker.getLatLng();
                                updateSelectedPoint(current);
                            });
                        }

                        if (typeof onPick === 'function') {
                            onPick(point);
                        }
                    }

                    if (allowPicking) {
                        map.on('click', (event) => updateSelectedPoint(event.latlng));
                    }

                    if (selectedPoint && selectedPoint.x !== null && selectedPoint.y !== null) {
                        map.setView(normalizedToLatLng(selectedPoint, background.width, background.height), pointZoom || 2);
                    } else {
                        map.fitBounds(bounds, { padding: [16, 16] });
                    }

                    setTimeout(() => map.invalidateSize(), 150);

                    return map;
                }

                function escapeHtml(text) {
                    return String(text ?? '')
                        .replaceAll('&', '&amp;')
                        .replaceAll('<', '&lt;')
                        .replaceAll('>', '&gt;')
                        .replaceAll('"', '&quot;')
                        .replaceAll("'", '&#039;');
                }

                window.SFCSMap = {
                    renderMap,
                };
            })();
        </script>
    @endpush
@endonce
