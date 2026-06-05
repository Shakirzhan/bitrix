<?php
/**
 * Компонент: Карта с маркерами (Яндекс.Карты)
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = array(
    'NAME' => 'Карта с маркерами (Яндекс.Карты)',
    'DESCRIPTION' => 'Компонент для отображения карты Яндекса с маркерами из инфоблока или JSON API',
    'ICON' => '',
    'PATH' => array(
        'ID' => 'content',
        'CHILD' => array(
            'ID' => 'maps',
            'NAME' => 'Карты и координаты'
        )
    ),
);
?>