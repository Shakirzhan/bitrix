<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = array(
    'GROUPS' => array(
        'BASIC' => array(
            'NAME' => 'Основные параметры',
        ),
        'MAP_SETTINGS' => array(
            'NAME' => 'Параметры карты',
        ),
        'IBLOCK_SETTINGS' => array(
            'NAME' => 'Параметры инфоблока',
        ),
        'JSON_SETTINGS' => array(
            'NAME' => 'Параметры JSON API',
        ),
        'FILE_SETTINGS' => array(
            'NAME' => 'Параметры файла',
        ),
    ),
    'PARAMETERS' => array(
        'API_KEY' => array(
            'NAME' => 'API ключ Яндекс.Карт',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
            'PARENT' => 'BASIC',
            'DESCRIPTION' => 'Получите ключ на https://developer.yandex.ru/services/maps-api/',
        ),
        'MAP_TYPE' => array(
            'NAME' => 'Источник данных маркеров',
            'TYPE' => 'LIST',
            'VALUES' => array(
                'simple' => 'Просто отображение (пусто)',
                'file' => 'Из файла (JSON, CSV, XML)',
                'iblock' => 'Компонент ядра Bitrix (инфоблок)',
                'json' => 'Из JSON API',
            ),
            'DEFAULT' => 'simple',
            'PARENT' => 'BASIC',
        ),
        'MAP_CENTER_LAT' => array(
            'NAME' => 'Широта центра карты',
            'TYPE' => 'STRING',
            'DEFAULT' => '55.7558',
            'PARENT' => 'MAP_SETTINGS',
            'DESCRIPTION' => 'Москва по умолчанию',
        ),
        'MAP_CENTER_LON' => array(
            'NAME' => 'Долгота центра карты',
            'TYPE' => 'STRING',
            'DEFAULT' => '37.6173',
            'PARENT' => 'MAP_SETTINGS',
            'DESCRIPTION' => 'Москва по умолчанию',
        ),
        'MAP_ZOOM' => array(
            'NAME' => 'Масштаб карты',
            'TYPE' => 'STRING',
            'DEFAULT' => '10',
            'PARENT' => 'MAP_SETTINGS',
            'DESCRIPTION' => 'От 0 до 20',
        ),
        'MAP_HEIGHT' => array(
            'NAME' => 'Высота карты',
            'TYPE' => 'STRING',
            'DEFAULT' => '600px',
            'PARENT' => 'MAP_SETTINGS',
            'DESCRIPTION' => 'Например: 600px, 100%, и т.д.',
        ),
        'IBLOCK_ID' => array(
            'NAME' => 'ID инфоблока',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
            'PARENT' => 'IBLOCK_SETTINGS',
            'DESCRIPTION' => 'Используется если MAP_TYPE = "iblock"',
        ),
        'LIMIT' => array(
            'NAME' => 'Максимальное количество маркеров',
            'TYPE' => 'STRING',
            'DEFAULT' => '50',
            'PARENT' => 'IBLOCK_SETTINGS',
        ),
        'JSON_URL' => array(
            'NAME' => 'URL для JSON API',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
            'PARENT' => 'JSON_SETTINGS',
            'DESCRIPTION' => 'Используется если MAP_TYPE = "json". Пример: https://example.com/api/markers.json',
        ),
        'LAT_FIELD' => array(
            'NAME' => 'Поле для широты',
            'TYPE' => 'STRING',
            'DEFAULT' => 'lat',
            'PARENT' => 'JSON_SETTINGS',
            'DESCRIPTION' => 'Используется для JSON API и файлов',
        ),
        'LON_FIELD' => array(
            'NAME' => 'Поле для долготы',
            'TYPE' => 'STRING',
            'DEFAULT' => 'lon',
            'PARENT' => 'JSON_SETTINGS',
            'DESCRIPTION' => 'Используется для JSON API и файлов',
        ),
        'NAME_FIELD' => array(
            'NAME' => 'Поле для названия',
            'TYPE' => 'STRING',
            'DEFAULT' => 'name',
            'PARENT' => 'JSON_SETTINGS',
            'DESCRIPTION' => 'Используется для JSON API и файлов',
        ),
        'FILE_PATH' => array(
            'NAME' => 'Путь к файлу маркеров',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
            'PARENT' => 'FILE_SETTINGS',
            'DESCRIPTION' => 'Используется если MAP_TYPE = "file". Поддерживаемые форматы: JSON, CSV, XML. Пример: /uploads/markers.json или uploads/markers.csv',
        ),
    ),
);
?>