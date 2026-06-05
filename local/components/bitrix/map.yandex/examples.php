<?php
/**
 * Пример 1: Карта 500x500 с маркерами из PHP массива
 */
?>

<!-- Пример 1: Простая карта с маркерами из кода -->
<h2>Карта с маркерами из PHP</h2>

<?php
$APPLICATION->IncludeComponent(
    "bitrix:map.yandex",
    "",
    array(
        "API_KEY" => "YOUR_YANDEX_API_KEY", // Замените на ваш API ключ
        "MAP_TYPE" => "simple",
        "MAP_CENTER_LAT" => "55.7558",
        "MAP_CENTER_LON" => "37.6173",
        "MAP_ZOOM" => 12,
        "MAP_HEIGHT" => "500px",
    )
);
?>

<script>
    // После загрузки компонента добавляем маркеры через JavaScript
    (function() {
        // Ждем загрузки OpenStreetMap Leaflet
        const checkInterval = setInterval(function() {
            if (typeof L !== 'undefined' && document.getElementById('map-map.yandex_*')) {
                clearInterval(checkInterval);
                
                // Получаем контейнер карты (самый последний добавленный)
                const mapContainers = document.querySelectorAll('[id^="map-map.yandex_"]');
                if (mapContainers.length > 0) {
                    const lastMapId = mapContainers[mapContainers.length - 1].id;
                    
                    // Инициализируем карту
                    const map = L.map(lastMapId).setView([55.7558, 37.6173], 12);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(map);
                    
                    // Ваши маркеры из PHP
                    const markers = [
                        {
                            name: 'Офис №1',
                            lat: 55.7558,
                            lon: 37.6173,
                            description: 'Главный офис на Арбате',
                            icon: '📍'
                        },
                        {
                            name: 'Офис №2',
                            lat: 55.7500,
                            lon: 37.6200,
                            description: 'Филиал на Тверской',
                            icon: '🏢'
                        },
                        {
                            name: 'Офис №3',
                            lat: 55.7600,
                            lon: 37.6150,
                            description: 'Представительство в ЦАО',
                            icon: '🏛️'
                        }
                    ];
                    
                    // Добавляем маркеры на карту
                    markers.forEach(function(marker) {
                        L.marker([marker.lat, marker.lon])
                            .addTo(map)
                            .bindPopup(
                                '<div style="min-width: 200px;">' +
                                '<h4 style="margin: 0 0 8px 0; color: #0066cc;">' + marker.name + '</h4>' +
                                '<p style="margin: 0; color: #666; font-size: 13px;">' + marker.description + '</p>' +
                                '</div>'
                            );
                    });
                }
            }
        }, 100);
    })();
</script>

<hr>

<!-- Пример 2: Карта со стилизацией -->
<h2>Карта 500x500 со стилизацией</h2>

<style>
    .custom-map-wrapper {
        border: 2px solid #0066cc;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 102, 204, 0.2);
        margin: 20px 0;
    }
</style>

<div class="custom-map-wrapper">
    <?php
    $APPLICATION->IncludeComponent(
        "bitrix:map.yandex",
        "",
        array(
            "API_KEY" => "YOUR_YANDEX_API_KEY",
            "MAP_TYPE" => "simple",
            "MAP_CENTER_LAT" => "55.7558",
            "MAP_CENTER_LON" => "37.6173",
            "MAP_ZOOM" => 11,
            "MAP_HEIGHT" => "500px",
        )
    );
    ?>
</div>

<script>
    (function() {
        const checkInterval = setInterval(function() {
            if (typeof L !== 'undefined') {
                clearInterval(checkInterval);
                
                const mapContainers = document.querySelectorAll('.map-yandex');
                const lastMap = mapContainers[mapContainers.length - 1];
                
                if (lastMap && lastMap.id) {
                    const map = L.map(lastMap.id).setView([55.7558, 37.6173], 11);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap',
                        maxZoom: 19
                    }).addTo(map);
                    
                    // Маркеры компаний/офисов из PHP
                    const companies = [
                        {
                            name: 'IT Company Alpha',
                            lat: 55.7558,
                            lon: 37.6173,
                            type: 'IT',
                            phone: '+7 (495) 123-45-67'
                        },
                        {
                            name: 'Design Studio Beta',
                            lat: 55.7500,
                            lon: 37.6200,
                            type: 'Design',
                            phone: '+7 (495) 987-65-43'
                        },
                        {
                            name: 'Marketing Agency Gamma',
                            lat: 55.7600,
                            lon: 37.6150,
                            type: 'Marketing',
                            phone: '+7 (495) 555-66-77'
                        },
                        {
                            name: 'Startup Delta',
                            lat: 55.7480,
                            lon: 37.6250,
                            type: 'Startup',
                            phone: '+7 (495) 111-22-33'
                        }
                    ];
                    
                    companies.forEach(function(company) {
                        const color = company.type === 'IT' ? '#FF6B6B' : 
                                    company.type === 'Design' ? '#4ECDC4' :
                                    company.type === 'Marketing' ? '#FFE66D' : '#95E1D3';
                        
                        const html = '<div style="font-family: Arial; min-width: 220px;">' +
                            '<h4 style="margin: 0 0 6px 0; color: ' + color + '; font-size: 14px; font-weight: bold;">' + company.name + '</h4>' +
                            '<p style="margin: 4px 0; color: #666; font-size: 12px;"><strong>Тип:</strong> ' + company.type + '</p>' +
                            '<p style="margin: 4px 0; color: #666; font-size: 12px;"><strong>Тел:</strong> ' + company.phone + '</p>' +
                            '</div>';
                        
                        L.circleMarker([company.lat, company.lon], {
                            radius: 8,
                            fillColor: color,
                            color: '#fff',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.8
                        }).addTo(map).bindPopup(html);
                    });
                    
                    // Автоматически подгоняем границы
                    const group = new L.featureGroup(
                        companies.map(c => L.marker([c.lat, c.lon]))
                    );
                    if (companies.length > 1) {
                        map.fitBounds(group.getBounds().pad(0.1));
                    }
                }
            }
        }, 100);
    })();
</script>

<hr>

<!-- Пример 3: Карта с поиском по маркерам -->
<h2>Интерактивная карта с фильтром</h2>

<input type="text" id="markerFilter" placeholder="Поиск по названию офиса..." 
    style="padding: 10px; width: 100%; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px;">

<?php
$APPLICATION->IncludeComponent(
    "bitrix:map.yandex",
    "",
    array(
        "API_KEY" => "YOUR_YANDEX_API_KEY",
        "MAP_TYPE" => "simple",
        "MAP_CENTER_LAT" => "55.7558",
        "MAP_CENTER_LON" => "37.6173",
        "MAP_ZOOM" => 12,
        "MAP_HEIGHT" => "500px",
    )
);
?>

<script>
    (function() {
        const checkInterval = setInterval(function() {
            if (typeof L !== 'undefined') {
                clearInterval(checkInterval);
                
                const mapContainers = document.querySelectorAll('.map-yandex');
                const lastMap = mapContainers[mapContainers.length - 1];
                
                if (lastMap && lastMap.id) {
                    const map = L.map(lastMap.id).setView([55.7558, 37.6173], 12);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap',
                        maxZoom: 19
                    }).addTo(map);
                    
                    // Маркеры филиалов
                    const branches = [
                        { name: 'Москва - Центр', lat: 55.7558, lon: 37.6173, region: 'Москва' },
                        { name: 'Москва - Юго-Запад', lat: 55.7300, lon: 37.5500, region: 'Москва' },
                        { name: 'Москва - Север', lat: 55.8100, lon: 37.6300, region: 'Москва' },
                        { name: 'Подмосковье', lat: 55.6500, lon: 37.7000, region: 'МО' }
                    ];
                    
                    let markers = [];
                    let currentMarkers = [];
                    
                    // Добавляем маркеры
                    branches.forEach(function(branch) {
                        const marker = L.marker([branch.lat, branch.lon])
                            .addTo(map)
                            .bindPopup(
                                '<strong>' + branch.name + '</strong><br>' +
                                'Регион: ' + branch.region
                            );
                        
                        marker.data = branch;
                        markers.push(marker);
                        currentMarkers.push(marker);
                    });
                    
                    // Фильтр поиска
                    document.getElementById('markerFilter').addEventListener('keyup', function(e) {
                        const query = e.target.value.toLowerCase();
                        
                        markers.forEach(function(marker) {
                            const name = marker.data.name.toLowerCase();
                            if (name.includes(query)) {
                                marker.addTo(map);
                            } else {
                                map.removeLayer(marker);
                            }
                        });
                    });
                }
            }
        }, 100);
    })();
</script>
