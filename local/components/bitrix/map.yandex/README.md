# Компонент Карта с маркерами (Яндекс.Карты)

Универсальный компонент для отображения интерактивной карты Яндекса с маркерами из различных источников данных.

## Возможности

✅ **Три источника данных:**
1. **Просто отображение** - пустая карта (маркеры добавляются вручную через JS)
2. **Компонент ядра Bitrix** - данные из инфоблока
3. **Из JSON API** - загрузка маркеров с удаленного сервера

✅ **Функции:**
- 📍 Интерактивная карта Яндекса
- 🎯 Информационные балуны при клике на маркер
- 🖼️ Поддержка изображений в маркерах
- 📱 Адаптивный дизайн
- 🔗 Ссылки на подробные страницы
- 🎨 Легко кастомизируется
- 🆓 Fallback на OpenStreetMap если ключ не указан

## Установка

1. Скопируйте компонент в `/local/components/bitrix/map.yandex/`

2. Получите API ключ Яндекс.Карт:
   - Перейдите на https://developer.yandex.ru/services/maps-api/
   - Создайте приложение
   - Скопируйте ключ для API Maps

## Варианты использования

### 1. Просто отображение (пусто)

```php
<?php
$APPLICATION->IncludeComponent(
    "bitrix:map.yandex",
    "",
    array(
        "API_KEY" => "YOUR_YANDEX_API_KEY",
        "MAP_TYPE" => "simple",
        "MAP_CENTER_LAT" => "55.7558",
        "MAP_CENTER_LON" => "37.6173",
        "MAP_ZOOM" => 10,
        "MAP_HEIGHT" => "600px",
    )
);
?>
```

### 2. Компонент ядра Bitrix (из инфоблока)

```php
<?php
$APPLICATION->IncludeComponent(
    "bitrix:map.yandex",
    "",
    array(
        "API_KEY" => "YOUR_YANDEX_API_KEY",
        "MAP_TYPE" => "iblock",
        "IBLOCK_ID" => 5,  // ID инфоблока с маркерами
        "LIMIT" => 50,
        "MAP_CENTER_LAT" => "55.7558",
        "MAP_CENTER_LON" => "37.6173",
        "MAP_ZOOM" => 10,
    )
);
?>
```

### 3. Из JSON API

```php
<?php
$APPLICATION->IncludeComponent(
    "bitrix:map.yandex",
    "",
    array(
        "API_KEY" => "YOUR_YANDEX_API_KEY",
        "MAP_TYPE" => "json",
        "JSON_URL" => "https://example.com/api/markers.json",
        "LAT_FIELD" => "latitude",
        "LON_FIELD" => "longitude",
        "NAME_FIELD" => "title",
        "MAP_CENTER_LAT" => "55.7558",
        "MAP_CENTER_LON" => "37.6173",
        "MAP_ZOOM" => 10,
    )
);
?>
```

## Параметры компонента

| Параметр | Тип | Описание | По умолчанию |
|----------|-----|---------|-------------|
| **API_KEY** | строка | API ключ Яндекс.Карт | - |
| **MAP_TYPE** | список | Источник данных (simple, iblock, json) | simple |
| **MAP_CENTER_LAT** | число | Широта центра карты | 55.7558 |
| **MAP_CENTER_LON** | число | Долгота центра карты | 37.6173 |
| **MAP_ZOOM** | число | Начальный масштаб (0-20) | 10 |
| **MAP_HEIGHT** | строка | Высота карты (px, %, и т.д.) | 600px |
| **IBLOCK_ID** | число | ID инфоблока (для iblock) | - |
| **LIMIT** | число | Макс. маркеров (для iblock) | 50 |
| **JSON_URL** | строка | URL JSON API (для json) | - |
| **LAT_FIELD** | строка | Поле широты в JSON | lat |
| **LON_FIELD** | строка | Поле долготы в JSON | lon |
| **NAME_FIELD** | строка | Поле названия в JSON | name |

## Подготовка данных

### Для инфоблока

Создайте свойства инфоблока:

```php
// Широта (тип: Число)
$arProps[] = array(
    'NAME' => 'Широта',
    'CODE' => 'LATITUDE',
    'PROPERTY_TYPE' => 'N',
);

// Долгота (тип: Число)
$arProps[] = array(
    'NAME' => 'Долгота',
    'CODE' => 'LONGITUDE',
    'PROPERTY_TYPE' => 'N',
);

// Адрес (тип: Строка)
$arProps[] = array(
    'NAME' => 'Адрес',
    'CODE' => 'ADDRESS',
    'PROPERTY_TYPE' => 'S',
);
```

### Для JSON API

Формат ответа:

```json
[
  {
    "id": 1,
    "name": "Маркер 1",
    "latitude": 55.7558,
    "longitude": 37.6173,
    "description": "Описание маркера",
    "image": "https://example.com/image.jpg",
    "url": "https://example.com/details/1"
  },
  {
    "id": 2,
    "name": "Маркер 2",
    "latitude": 55.7500,
    "longitude": 37.6200,
    "description": "Еще один маркер",
    "image": "https://example.com/image2.jpg",
    "url": "https://example.com/details/2"
  }
]
```

## CSS классы для кастомизации

```css
.map-yandex-wrapper      /* Контейнер карты */
.map-yandex              /* Сама карта */
.map-yandex-balloon      /* Информационный балун */
.map-yandex-balloon h3   /* Название в балуне */
.map-yandex-balloon p    /* Описание в балуне */
.map-yandex-balloon img  /* Изображение в балуне */
.map-yandex-balloon a    /* Ссылка в балуне */
```

## Пример кастомной стилизации

```css
/* Уменьшение размера карты */
.map-yandex-wrapper {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Стилизация балуна */
.map-yandex-balloon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
}

.map-yandex-balloon h3 {
    color: white;
}

.map-yandex-balloon p {
    color: rgba(255, 255, 255, 0.9);
}
```

## Получение координат

### Использование Яндекс.Карт API

```php
function getCoordinatesByAddress($address, $apiKey) {
    $url = 'https://geocode-maps.yandex.ru/1.x/';
    $params = array(
        'apikey' => $apiKey,
        'geocode' => $address,
        'format' => 'json'
    );
    
    $ch = curl_init($url . '?' . http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
    
    if (!empty($response['response']['GeoObjectCollection']['featureMember'])) {
        $point = $response['response']['GeoObjectCollection']['featureMember'][0]
            ['GeoObject']['Point']['pos'];
        list($lon, $lat) = explode(' ', $point);
        return array('lat' => $lat, 'lon' => $lon);
    }
    
    return null;
}
```

## Браузеры

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Yandex Browser 21+

## Лицензия

MIT

## Автор

Shakirzhan

## Поддержка

Для вопросов и багрепортов создавайте Issues в репозитории.
