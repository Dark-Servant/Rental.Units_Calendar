<?
error_reporting(E_ERROR);

define('SESSION_CONTANTS', true);

$setting = require dirname(__DIR__) . '/configs/settings.php';
$dayPeriod = Day::getPeriod(date(DAY_FORMAT), 7);
$days = $dayPeriod['data'];
$technics = Technic::getWithContentsByDayPeriod($dayPeriod, [], TECHNIC_SORTING);

header('Content-Type: application/javascript; charset=utf-8');?>
;$(() => {
    var LANG_VALUES = <?=json_encode($langValues)?>;
    var SERVER_CONSTANTS = <?=json_encode($_SESSION['CONST_LIST'] ?? [])?>;<?

    include __DIR__ . '/components.js';
    include __DIR__ . '/script.js';?>
});