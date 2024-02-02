<?
use Frontend\AutoLoader\{Base, Path};

$loader = new Base([
                Path::AJAX => [
                    'main.js',
                    'xmlhttprequest.event.js',
                    'bxrestapi.js',
                    'data.js',
                ],
                Path::NODEJS => [
                    'vue/dist/vue.min.js',
                    'jquery/dist/jquery.min.js',
                    'js-datepicker/dist/datepicker.min.css',
                    'js-datepicker/dist/datepicker.min.js',
                ],
                Path::CLASSES => 'area.js',
                Path::CLASSES_VUE => 'component.js',
                Path::WORKERS => 'bizprocactivity.js',
                Path::WORKERS_VUE => 'componentloader.js',
                Path::SOLUTION => [
                    'addition/ajax/periodupdating.js',
                    'main.js'
                ],
                Path::SOLUTION_CLASSES => [
                    'comment.js',
                    'copycomment.js',
                    'removedeal.js'
                ],
                Path::SOLUTION_VUE => 'main.js',
                Path::SIMPLE => [
                    'style.css',
                    'style.media.css'
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
        <link rel="apple-touch-icon" href="index.php"><?        
        if (defined('DOMAIN')):?>
        <script src="//api.bitrix24.com/api/v1/"></script><?
        endif;?>
        <?require_once __DIR__ . '/constants.php';?>
        <?=$loader->prepareFiles()?>
        <?require_once __DIR__ . '/xmlhttprequest.php';?>
    </head>
    <body><?
    include_once __DIR__ . '/' . SHOW_VIEW . '.php'; ?>
    </body>
</html>