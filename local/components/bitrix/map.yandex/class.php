<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;

class MapYandexComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        // Параметры по умолчанию
        $this->arParams['MAP_TYPE'] = $this->arParams['MAP_TYPE'] ?? 'iblock'; // iblock, json, simple
        $this->arParams['API_KEY'] = $this->arParams['API_KEY'] ?? '';
        $this->arParams['MAP_CENTER_LAT'] = $this->arParams['MAP_CENTER_LAT'] ?? '55.7558';
        $this->arParams['MAP_CENTER_LON'] = $this->arParams['MAP_CENTER_LON'] ?? '37.6173';
        $this->arParams['MAP_ZOOM'] = (int)$this->arParams['MAP_ZOOM'] ?? 10;
        $this->arParams['MAP_HEIGHT'] = $this->arParams['MAP_HEIGHT'] ?? '600px';
        $this->arParams['IBLOCK_ID'] = (int)$this->arParams['IBLOCK_ID'] ?? 0;
        $this->arParams['LIMIT'] = (int)$this->arParams['LIMIT'] ?? 50;
        $this->arParams['JSON_URL'] = $this->arParams['JSON_URL'] ?? '';
        $this->arParams['LAT_FIELD'] = $this->arParams['LAT_FIELD'] ?? 'lat';
        $this->arParams['LON_FIELD'] = $this->arParams['LON_FIELD'] ?? 'lon';
        $this->arParams['NAME_FIELD'] = $this->arParams['NAME_FIELD'] ?? 'name';

        $markers = array();

        switch ($this->arParams['MAP_TYPE']) {
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
            return array();
        }
    }
}
?>