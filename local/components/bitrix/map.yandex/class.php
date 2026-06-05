<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;

class MapYandexComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        // Параметры по умолчанию
        $this->arParams['MAP_TYPE'] = $this->arParams['MAP_TYPE'] ?? 'iblock'; // iblock, json, file, simple
        $this->arParams['API_KEY'] = $this->arParams['API_KEY'] ?? '';
        $this->arParams['MAP_CENTER_LAT'] = $this->arParams['MAP_CENTER_LAT'] ?? '55.7558';
        $this->arParams['MAP_CENTER_LON'] = $this->arParams['MAP_CENTER_LON'] ?? '37.6173';
        $this->arParams['MAP_ZOOM'] = (int)$this->arParams['MAP_ZOOM'] ?? 10;
        $this->arParams['MAP_HEIGHT'] = $this->arParams['MAP_HEIGHT'] ?? '600px';
        $this->arParams['IBLOCK_ID'] = (int)$this->arParams['IBLOCK_ID'] ?? 0;
        $this->arParams['LIMIT'] = (int)$this->arParams['LIMIT'] ?? 50;
        $this->arParams['JSON_URL'] = $this->arParams['JSON_URL'] ?? '';
        $this->arParams['FILE_PATH'] = $this->arParams['FILE_PATH'] ?? '';
        $this->arParams['LAT_FIELD'] = $this->arParams['LAT_FIELD'] ?? 'lat';
        $this->arParams['LON_FIELD'] = $this->arParams['LON_FIELD'] ?? 'lon';
        $this->arParams['NAME_FIELD'] = $this->arParams['NAME_FIELD'] ?? 'name';

        $markers = array();

        switch ($this->arParams['MAP_TYPE']) {
            case 'file':
                $markers = $this->getMarkersFromFile();
                break;
            case 'json':
                $markers = $this->getMarkersFromJson();
                break;
            case 'iblock':
                if (Loader::includeModule('iblock')) {
                    $markers = $this->getMarkersFromIblock();
                }
                break;
            case 'simple':
            default:
                // Пустая карта, маркеры могут быть добавлены через JS
                break;
        }

        $this->arResult['MARKERS'] = json_encode($markers, JSON_UNESCAPED_UNICODE);
        $this->arResult['MAP_CENTER'] = array(
            'lat' => floatval($this->arParams['MAP_CENTER_LAT']),
            'lon' => floatval($this->arParams['MAP_CENTER_LON']),
        );
        $this->arResult['MAP_ZOOM'] = $this->arParams['MAP_ZOOM'];
        $this->arResult['MAP_HEIGHT'] = $this->arParams['MAP_HEIGHT'];
        $this->arResult['API_KEY'] = $this->arParams['API_KEY'];
        $this->arResult['MAP_TYPE'] = $this->arParams['MAP_TYPE'];

        $this->includeComponentTemplate();
    }

    protected function getMarkersFromFile()
    {
        if (!$this->arParams['FILE_PATH']) {
            return array();
        }

        // Обработка пути файла
        $filePath = $this->arParams['FILE_PATH'];
        
        // Если путь относительный, добавляем корень сайта
        if (strpos($filePath, '/') === 0) {
            // Абсолютный путь от корня сайта
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
        } elseif (strpos($filePath, 'http://') !== 0 && strpos($filePath, 'https://') !== 0) {
            // Если не URL, это относительный путь
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $filePath;
        }

        // Поддерживаемые форматы: JSON, CSV, XML
        $fileExt = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        try {
            if ($fileExt === 'json') {
                return $this->parseJsonFile($filePath);
            } elseif ($fileExt === 'csv') {
                return $this->parseCsvFile($filePath);
            } elseif ($fileExt === 'xml') {
                return $this->parseXmlFile($filePath);
            }
        } catch (Exception $e) {
            AddMessage2Log('Ошибка при чтении файла маркеров: ' . $e->getMessage(), 'map_yandex');
            return array();
        }

        return array();
    }

    protected function parseJsonFile($filePath)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception('Файл не найден или не читаем: ' . $filePath);
        }

        $jsonContent = file_get_contents($filePath);
        $data = json_decode($jsonContent, true);

        if (!is_array($data)) {
            throw new Exception('Некорректный формат JSON');
        }

        $markers = array();
        $latField = $this->arParams['LAT_FIELD'];
        $lonField = $this->arParams['LON_FIELD'];
        $nameField = $this->arParams['NAME_FIELD'];

        foreach ($data as $item) {
            if (is_array($item) && isset($item[$latField]) && isset($item[$lonField])) {
                $markers[] = array(
                    'id' => $item['id'] ?? uniqid(),
                    'name' => $item[$nameField] ?? 'Маркер',
                    'lat' => floatval($item[$latField]),
                    'lon' => floatval($item[$lonField]),
                    'text' => $item['description'] ?? $item['text'] ?? '',
                    'image' => $item['image'] ?? '',
                    'url' => $item['url'] ?? '',
                );
            }
        }

        return $markers;
    }

    protected function parseCsvFile($filePath)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception('Файл не найден или не читаем: ' . $filePath);
        }

        $markers = array();
        $file = fopen($filePath, 'r');
        $header = null;

        while (($row = fgetcsv($file, 1000, ',')) !== false) {
            if ($header === null) {
                // Первая строка - заголовок
                $header = $row;
                continue;
            }

            $item = array_combine($header, $row);
            
            $latField = $this->arParams['LAT_FIELD'];
            $lonField = $this->arParams['LON_FIELD'];
            $nameField = $this->arParams['NAME_FIELD'];

            if (isset($item[$latField]) && isset($item[$lonField])) {
                $markers[] = array(
                    'id' => $item['id'] ?? uniqid(),
                    'name' => $item[$nameField] ?? 'Маркер',
                    'lat' => floatval($item[$latField]),
                    'lon' => floatval($item[$lonField]),
                    'text' => $item['description'] ?? $item['text'] ?? '',
                    'image' => $item['image'] ?? '',
                    'url' => $item['url'] ?? '',
                );
            }
        }

        fclose($file);
        return $markers;
    }

    protected function parseXmlFile($filePath)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception('Файл не найден или не читаем: ' . $filePath);
        }

        $xmlContent = file_get_contents($filePath);
        $xml = simplexml_load_string($xmlContent);

        if ($xml === false) {
            throw new Exception('Некорректный формат XML');
        }

        $markers = array();
        $latField = $this->arParams['LAT_FIELD'];
        $lonField = $this->arParams['LON_FIELD'];
        $nameField = $this->arParams['NAME_FIELD'];

        // Ищем элементы маркеров
        foreach ($xml->marker as $marker) {
            $lat = (float)$marker->{$latField};
            $lon = (float)$marker->{$lonField};

            if ($lat && $lon) {
                $markers[] = array(
                    'id' => (string)$marker->id ?? uniqid(),
                    'name' => (string)$marker->{$nameField} ?? 'Маркер',
                    'lat' => $lat,
                    'lon' => $lon,
                    'text' => (string)$marker->description ?? (string)$marker->text ?? '',
                    'image' => (string)$marker->image ?? '',
                    'url' => (string)$marker->url ?? '',
                );
            }
        }

        return $markers;
    }

    protected function getMarkersFromIblock()
    {
        if (!$this->arParams['IBLOCK_ID']) {
            return array();
        }

        $markers = array();
        $arFilter = array(
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'ACTIVE' => 'Y',
        );

        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            $arFilter,
            false,
            array('nPageSize' => $this->arParams['LIMIT']),
            array('ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'PREVIEW_TEXT')
        );

        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $arProps = $ob->GetProperties();

            $lat = null;
            $lon = null;

            // Пытаемся найти координаты в свойствах
            foreach ($arProps as $propCode => $prop) {
                if (stripos($propCode, 'lat') !== false) {
                    $lat = $prop['VALUE'];
                }
                if (stripos($propCode, 'lon') !== false || stripos($propCode, 'lng') !== false) {
                    $lon = $prop['VALUE'];
                }
            }

            if ($lat && $lon) {
                $picture = '';
                if ($arFields['PREVIEW_PICTURE']) {
                    $picture = CFile::GetPath($arFields['PREVIEW_PICTURE']);
                }

                $markers[] = array(
                    'id' => $arFields['ID'],
                    'name' => $arFields['NAME'],
                    'lat' => floatval($lat),
                    'lon' => floatval($lon),
                    'text' => $arFields['PREVIEW_TEXT'] ?? '',
                    'image' => $picture,
                    'url' => $arFields['DETAIL_PAGE_URL'],
                );
            }
        }

        return $markers;
    }

    protected function getMarkersFromJson()
    {
        if (!$this->arParams['JSON_URL']) {
            return array();
        }

        try {
            $http = new HttpClient();
            $response = $http->get($this->arParams['JSON_URL']);
            $data = json_decode($response, true);

            if (!is_array($data)) {
                return array();
            }

            $markers = array();
            $latField = $this->arParams['LAT_FIELD'];
            $lonField = $this->arParams['LON_FIELD'];
            $nameField = $this->arParams['NAME_FIELD'];

            foreach ($data as $item) {
                if (isset($item[$latField]) && isset($item[$lonField])) {
                    $markers[] = array(
                        'id' => $item['id'] ?? uniqid(),
                        'name' => $item[$nameField] ?? 'Маркер',
                        'lat' => floatval($item[$latField]),
                        'lon' => floatval($item[$lonField]),
                        'text' => $item['description'] ?? $item['text'] ?? '',
                        'image' => $item['image'] ?? '',
                        'url' => $item['url'] ?? '',
                    );
                }
            }

            return $markers;
        } catch (Exception $e) {
            AddMessage2Log('Ошибка при загрузке JSON: ' . $e->getMessage(), 'map_yandex');
            return array();
        }
    }
}
?>