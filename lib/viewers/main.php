<?
use Frontend\AutoLoader\{Base, Path};

$loader = new Base([
                Path::AJAX => [
                    'xmlhttprequest.event.js'
                ]
            ]);
?><!DOCTYPE html>
<html lang="ru">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?=$langValues['APPLICATION_TITLE']?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="Infoservice">
        <meta http-equiv="Cache-Control" content="no-cache">

        <meta itemprop="name" content="">
        <meta itemprop="description" content="">
        <meta itemprop="image" content="">

        <meta property="og:title" content="">
        <meta property="og:description" content="">
        <meta property="og:image" content="">
        <meta property="og:url" content=""> 

        <!--[if lt IE 9]>
            <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <link rel="icon" type="image/vnd.microsoft.icon" href="index.php">
        <link rel="shortcut icon" href="index.php">
        <link rel="apple-touch-icon" href="index.php">
        
        <link rel="stylesheet" href="<?=URL_SCRIPT_START?>css/style.css?<?=URL_SCRIPT_FINISH?>">
        <link rel="stylesheet" href="<?=URL_SCRIPT_START?>css/style.media.css?<?=URL_SCRIPT_FINISH?>">
        <link rel="stylesheet" href="<?=URL_SCRIPT_START?>node_modules/js-datepicker/dist/datepicker.min.css?<?=URL_SCRIPT_FINISH?>">

        <script src="<?=URL_SCRIPT_START?>node_modules/vue/dist/vue.min.js?<?=URL_SCRIPT_FINISH?>"></script>
        <script src="<?=URL_SCRIPT_START?>node_modules/jquery/dist/jquery.min.js?<?=URL_SCRIPT_FINISH?>"></script>
        <script src="<?=URL_SCRIPT_START?>node_modules/js-datepicker/dist/datepicker.min.js?<?=URL_SCRIPT_FINISH?>"></script><?
        if (defined('DOMAIN')):?>
        <script src="//api.bitrix24.com/api/v1/"></script><?
        endif;?>
        <?=$loader->prepareFiles()?>
        <script src="<?=URL_SCRIPT_START?>js/main.js.php?<?=URL_SCRIPT_FINISH?>"></script>
        <?require_once __DIR__ . '/xmlhttprequest.php';?>
    </head>
    <body><?
    include_once __DIR__ . '/' . SHOW_VIEW . '.php'; ?>
    </body>
</html>