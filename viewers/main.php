<!DOCTYPE html>
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

        <link rel="stylesheet" href="css/style.css?<?=VERSION?>">

        <script src="<?=APPPATH?>/node_modules/vue/dist/vue.min.js?<?=VERSION?>"></script>
        <script src="<?=APPPATH?>/js/main.js.php?<?=VERSION?>"></script>
    </head>
    <body><?
    require_once __DIR__ . '/' . SHOW_VIEW . '.php'; ?>
    </body>
</html>