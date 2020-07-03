<?
error_reporting(E_ERROR);

define('SESSION_CONTANTS', true);

$setting = require $_SERVER['DOCUMENT_ROOT'] . '/configs/settings.php';

header('Content-Type: application/javascript');?>
;$(() => {
    var LANG_VALUES = <?=json_encode($langValues)?>;
    var SERVER_CONSTANTS = <?=json_encode($_SESSION['CONST_LIST'] ?? [])?>;<?

    require __DIR__ . '/components.js';
    require __DIR__ . '/script.js';?>
});