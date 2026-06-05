<?php
/**
 * Вспомогательные функции для работы с компонентом карты
 */

/**
 * Преобразование массива маркеров в JSON для передачи на фронтенд
 * 
 * @param array $markers - массив маркеров
 * @return string JSON строка
 * 
 * Пример использования:
 * $markers = [
 *     ['name' => 'Офис 1', 'lat' => 55.7558, 'lon' => 37.6173, 'description' => 'Описание'],
 *     ['name' => 'Офис 2', 'lat' => 55.7500, 'lon' => 37.6200, 'description' => 'Описание 2']
 * ];
 * $jsonMarkers = convertMarkersToJson($markers);
 */
function convertMarkersToJson($markers) {
    return json_encode($markers, JSON_UNESCAPED_UNICODE);
}

/**
 * Получение маркеров из массива PHP с валидацией
 * 
 * @param array $data - исходные данные
 * @param string $latField - имя поля широты
 * @param string $lonField - имя поля долготы
 * @param string $nameField - имя поля названия
 * @return array - отфильтрованные маркеры
 */
function getValidMarkers($data, $latField = 'lat', $lonField = 'lon', $nameField = 'name') {
    $markers = [];
    
    if (!is_array($data)) {
        return $markers;
    }
    
    foreach ($data as $item) {
        if (!is_array($item)) {
            continue;
        }
        
        if (!isset($item[$latField]) || !isset($item[$lonField])) {
            continue;
        }
        
        $lat = floatval($item[$latField]);
        $lon = floatval($item[$lonField]);
        
        // Проверяем корректность координат
        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            continue;
        }
        
        $markers[] = [
            'id' => isset($item['id']) ? $item['id'] : uniqid(),
            'name' => isset($item[$nameField]) ? $item[$nameField] : 'Маркер',
            'lat' => $lat,
            'lon' => $lon,
            'text' => isset($item['description']) ? $item['description'] : (isset($item['text']) ? $item['text'] : ''),
            'image' => isset($item['image']) ? $item['image'] : '',
            'url' => isset($item['url']) ? $item['url'] : '',
        ];
    }
    
    return $markers;
}

/**
 * Получение маркеров из инфоблока Bitrix
 * 
 * @param int $iblockId - ID инфоблока
 * @param int $limit - максимальное количество элементов
 * @param string $latProp - код свойства широты
 * @param string $lonProp - код свойства долготы
 * @param string $textProp - код свойства описания
 * @return array - массив маркеров
 * 
 * Пример:
 * $markers = getMarkersFromIblock(5, 50, 'LATITUDE', 'LONGITUDE', 'ADDRESS');
 */
function getMarkersFromIblock($iblockId, $limit = 50, $latProp = 'LATITUDE', $lonProp = 'LONGITUDE', $textProp = 'ADDRESS') {
    $markers = [];
    
    if (!defined('B_PROLOG_INCLUDED')) {
        return $markers;
    }
    
    if (!\Bitrix\Main\Loader::includeModule('iblock')) {
        return $markers;
    }
    
    $arFilter = [
        'IBLOCK_ID' => $iblockId,
        'ACTIVE' => 'Y',
    ];
    
    $res = CIBlockElement::GetList(
        ['ID' => 'ASC'],
        $arFilter,
        false,
        ['nPageSize' => $limit],
        ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'PREVIEW_TEXT']
    );
    
    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        $arProps = $ob->GetProperties();
        
        $lat = null;
        $lon = null;
        $text = '';
        
        // Получаем координаты
        if (isset($arProps[$latProp])) {
            $lat = $arProps[$latProp]['VALUE'];
        }
        if (isset($arProps[$lonProp])) {
            $lon = $arProps[$lonProp]['VALUE'];
        }
        if (isset($arProps[$textProp])) {
            $text = $arProps[$textProp]['VALUE'];
        }
        
        if ($lat && $lon) {
            $picture = '';
            if ($arFields['PREVIEW_PICTURE']) {
                $picture = CFile::GetPath($arFields['PREVIEW_PICTURE']);
            }
            
            $markers[] = [
                'id' => $arFields['ID'],
                'name' => $arFields['NAME'],
                'lat' => floatval($lat),
                'lon' => floatval($lon),
                'text' => $text ?: $arFields['PREVIEW_TEXT'],
                'image' => $picture,
                'url' => $arFields['DETAIL_PAGE_URL'],
            ];
        }
    }
    
    return $markers;
}

/**
 * Генерация JavaScript для добавления маркеров на карту
 * 
 * @param array $markers - массив маркеров
 * @param string $mapId - ID контейнера карты
 * @return string - JavaScript код
 */
function generateMarkersScript($markers, $mapId = '') {
    $markersJson = json_encode($markers, JSON_UNESCAPED_UNICODE);
    
    $script = <<<JS
    (function() {
        const markers = $markersJson;
        
        // Ждем загрузки Leaflet
        const checkInterval = setInterval(function() {
            if (typeof L !== 'undefined') {
                clearInterval(checkInterval);
                
                // Находим карту
                let map = null;
                
                if ('$mapId' && document.getElementById('$mapId')) {
                    map = L.map('$mapId');
                } else {
                    const mapContainers = document.querySelectorAll('[id^="map-"]');
                    if (mapContainers.length > 0) {
                        const lastMapId = mapContainers[mapContainers.length - 1].id;
                        // Предполагаем, что карта уже инициализирована
                        // Получаем сс��лку из глобального объекта
                        console.warn('Map ID не найден, используем последний инициализированный');
                    }
                }
                
                // Добавляем маркеры
                if (markers && markers.length > 0) {
                    markers.forEach(function(marker) {
                        if (map && marker.lat && marker.lon) {
                            const popup = '<div style="min-width: 200px;">' +
                                '<h4 style="margin: 0 0 8px 0; color: #0066cc;">' + marker.name + '</h4>';
                            
                            if (marker.text) {
                                popup += '<p style="margin: 0 0 8px 0; color: #666; font-size: 13px;">' + marker.text + '</p>';
                            }
                            
                            if (marker.image) {
                                popup += '<img src="' + marker.image + '" style="width: 100%; height: 150px; object-fit: cover; border-radius: 4px; margin: 8px 0;">';
                            }
                            
                            if (marker.url) {
                                popup += '<a href="' + marker.url + '" style="display: inline-block; padding: 8px 12px; background: #0066cc; color: white; text-decoration: none; border-radius: 4px; font-size: 12px;">Подробнее →</a>';
                            }
                            
                            popup += '</div>';
                            
                            L.marker([marker.lat, marker.lon])
                                .addTo(map)
                                .bindPopup(popup);
                        }
                    });
                }
            }
        }, 100);
    })();
JS;
    
    return $script;
}

/**
 * Сохранение маркеров в JSON файл
 * 
 * @param array $markers - массив маркеров
 * @param string $filePath - путь к файлу (от корня сайта)
 * @return bool - успешно ли сохранено
 */
function saveMarkersToFile($markers, $filePath) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
    
    // Создаем директорию если её нет
    $dir = dirname($fullPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Сохраняем JSON
    $json = json_encode($markers, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    return file_put_contents($fullPath, $json) !== false;
}

/**
 * Загрузка маркеров из JSON файла
 * 
 * @param string $filePath - путь к файлу (от корня сайта)
 * @return array - массив маркеров или пустой массив
 */
function loadMarkersFromFile($filePath) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
    
    if (!file_exists($fullPath) || !is_readable($fullPath)) {
        return [];
    }
    
    $json = file_get_contents($fullPath);
    $markers = json_decode($json, true);
    
    return is_array($markers) ? $markers : [];
}

/**
 * Получение координат адреса через Яндекс Geocoder API
 * 
 * @param string $address - адрес
 * @param string $apiKey - API ключ Яндекс.Карт
 * @return array|null - массив с lat, lon или null если не найдено
 */
function getCoordinatesByAddress($address, $apiKey) {
    $url = 'https://geocode-maps.yandex.ru/1.x/';
    $params = [
        'apikey' => $apiKey,
        'geocode' => $address,
        'format' => 'json'
    ];
    
    $ch = curl_init($url . '?' . http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if (!$response) {
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (!empty($data['response']['GeoObjectCollection']['featureMember'])) {
        $point = $data['response']['GeoObjectCollection']['featureMember'][0]
            ['GeoObject']['Point']['pos'];
        list($lon, $lat) = explode(' ', $point);
        
        return [
            'lat' => floatval($lat),
            'lon' => floatval($lon)
        ];
    }
    
    return null;
}
?>