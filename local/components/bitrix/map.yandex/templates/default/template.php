<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$componentId = $this->getComponent()->getName() . '_' . rand(10000, 99999);
?>

<div class="map-yandex-wrapper" id="<?php echo htmlspecialchars($componentId); ?>">
    <div id="map-<?php echo htmlspecialchars($componentId); ?>" class="map-yandex" style="width: 100%; height: <?php echo htmlspecialchars($this->arResult['MAP_HEIGHT']); ?>;"></div>
</div>

<style>
    .map-yandex-wrapper {
        position: relative;
        width: 100%;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .map-yandex {
        background: #f0f0f0;
    }

    .map-yandex-balloon {
        padding: 10px;
        min-width: 200px;
        max-width: 300px;
    }

    .map-yandex-balloon h3 {
        margin: 0 0 8px 0;
        font-size: 16px;
        font-weight: bold;
        color: #333;
    }

    .map-yandex-balloon p {
        margin: 0 0 8px 0;
        font-size: 13px;
        color: #666;
    }

    .map-yandex-balloon img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 4px;
        margin: 8px 0;
    }

    .map-yandex-balloon a {
        display: inline-block;
        padding: 8px 12px;
        background: #0066cc;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 12px;
        transition: background 0.2s;
    }

    .map-yandex-balloon a:hover {
        background: #0052a3;
    }
</style>

<script>
    (function() {
        const mapId = '<?php echo addslashes($componentId); ?>';
        const mapContainer = 'map-' + mapId;
        const markers = <?php echo isset($this->arResult['MARKERS']) && !empty($this->arResult['MARKERS']) ? $this->arResult['MARKERS'] : '[]'; ?>;
        const mapCenterLat = parseFloat(<?php echo isset($this->arResult['MAP_CENTER']['lat']) ? (float)$this->arResult['MAP_CENTER']['lat'] : 55.7558; ?>);
        const mapCenterLon = parseFloat(<?php echo isset($this->arResult['MAP_CENTER']['lon']) ? (float)$this->arResult['MAP_CENTER']['lon'] : 37.6173; ?>);
        const mapZoom = parseInt(<?php echo isset($this->arResult['MAP_ZOOM']) ? (int)$this->arResult['MAP_ZOOM'] : 10; ?>);
        const apiKey = '<?php echo addslashes($this->arResult['API_KEY']); ?>';

        console.log('Map Init:', {
            mapId: mapId,
            mapCenterLat: mapCenterLat,
            mapCenterLon: mapCenterLon,
            mapZoom: mapZoom,
            markersCount: markers.length,
            apiKey: apiKey ? 'Set' : 'Not set'
        });

        // Функция для инициализации карты Яндекса
        function initYandexMap() {
            if (typeof ymaps === 'undefined') {
                console.error('Яндекс.Карты API не загружена');
                loadOpenStreetMap();
                return;
            }

            ymaps.ready(function() {
                const map = new ymaps.Map(mapContainer, {
                    center: [mapCenterLat, mapCenterLon],
                    zoom: mapZoom,
                    controls: ['zoomControl', 'fullscreenControl']
                });

                // Добавляем маркеры
                if (markers && markers.length > 0) {
                    const collection = new ymaps.GeoObjectCollection();

                    markers.forEach(function(marker) {
                        const placemark = new ymaps.Placemark(
                            [marker.lat, marker.lon],
                            {
                                balloonContent: createBalloonContent(marker),
                            },
                            {
                                preset: 'islands#dotIcon',
                                iconColor: '#0066cc'
                            }
                        );

                        collection.add(placemark);
                    });

                    map.geoObjects.add(collection);

                    // Если несколько маркеров, подгоняем границы
                    if (markers.length > 1) {
                        map.setBounds(collection.getBounds());
                    }
                }
            });
        }

        function createBalloonContent(marker) {
            let html = '<div class="map-yandex-balloon">';
            html += '<h3>' + escapeHtml(marker.name) + '</h3>';

            if (marker.text) {
                html += '<p>' + escapeHtml(marker.text) + '</p>';
            }

            if (marker.image) {
                html += '<img src="' + escapeHtml(marker.image) + '" alt="' + escapeHtml(marker.name) + '" />';
            }

            if (marker.url) {
                html += '<a href="' + escapeHtml(marker.url) + '" target="_blank">Подробнее →</a>';
            }

            html += '</div>';
            return html;
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) {
                return map[m];
            });
        }

        // Загружаем API Яндекс.Карт
        if (apiKey && apiKey.length > 0) {
            const script = document.createElement('script');
            script.src = 'https://api-maps.yandex.ru/2.1/?apikey=' + apiKey + '&lang=ru_RU';
            script.onload = initYandexMap;
            script.onerror = function() {
                console.warn('Ошибка загрузки Яндекс.Карт, используется OpenStreetMap');
                loadOpenStreetMap();
            };
            document.head.appendChild(script);
        } else {
            console.warn('API ключ Яндекс.Карт не указан, используется OpenStreetMap');
            loadOpenStreetMap();
        }

        function loadOpenStreetMap() {
            const leafletCss = document.createElement('link');
            leafletCss.rel = 'stylesheet';
            leafletCss.href = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css';
            document.head.appendChild(leafletCss);

            const leafletJs = document.createElement('script');
            leafletJs.src = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js';
            leafletJs.onload = function() {
                console.log('Leaflet loaded. Initializing map with:', {
                    lat: mapCenterLat,
                    lon: mapCenterLon,
                    zoom: mapZoom
                });

                try {
                    const map = L.map(mapContainer).setView(
                        [mapCenterLat, mapCenterLon],
                        mapZoom
                    );

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(map);

                    if (markers && markers.length > 0) {
                        const markerGroup = [];
                        
                        markers.forEach(function(marker) {
                            const popup = createBalloonContent(marker);
                            const m = L.marker([marker.lat, marker.lon])
                                .addTo(map)
                                .bindPopup(popup);
                            markerGroup.push(m);
                        });

                        if (markerGroup.length > 1) {
                            const group = new L.featureGroup(markerGroup);
                            map.fitBounds(group.getBounds());
                        }
                    }
                } catch (e) {
                    console.error('Ошибка при инициализации карты:', e);
                }
            };
            leafletJs.onerror = function() {
                console.error('Ошибка загрузки Leaflet');
            };
            document.head.appendChild(leafletJs);
        }
    })();
</script>
