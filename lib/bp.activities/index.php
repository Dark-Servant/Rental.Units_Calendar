<?
$setting = require __DIR__ . '/../../configs/settings.php';

$answer = ['result' => true];

/**
 * Проверка на то, что действие БП было вызвано по REST, а не как обычный скрипт
 * из консоли. Далее для запросов по REST будут составляться логи по тому, какие
 * были запросы сделаны, какие команды BX24 Rest API вызваны и какие ответы получены
 * 
 * Для всех запросов из консоли потребуется заранее определить массивы $_SERVER и
 * $_REQUEST с заполненными в них данными
 */
define('FROM_HTTP_HOST', !empty($_SERVER['HTTP_HOST']));

try {

    $bpactivity = new BPActivity(strval($_REQUEST['code']));

    $dateLink = date('YmdHis');
    if (FROM_HTTP_HOST) {
        $logFolder = __DIR__ . '/.log/' . $_REQUEST['code'] . '/';
        if (!file_exists($logFolder)) mkdir($logFolder, 0777, true);

        file_put_contents($logFolder . '/' . $dateLink . '.request.txt', 
            print_r($_SERVER, true) . PHP_EOL .
            print_r($_REQUEST, true) . PHP_EOL
        );
        $restAPIUnit = new BX24RestAPI($_REQUEST['auth'], $logFolder . '/' . $dateLink . '.log.txt');

    } else {
        $restAPIUnit = new BX24RestAPI($_REQUEST['auth']);
    }

    $bpactivity->setParams($_REQUEST['properties'] ?? [])->run($restAPIUnit);

    if (FROM_HTTP_HOST)
        $restAPIUnit->callBizprocEventSend(['EVENT_TOKEN' => $_REQUEST['event_token']]);

} catch (Exception $error) {
    $answer = array_merge($answer, ['result' => false, 'message' => $error->GetMessage()]);
}

if (FROM_HTTP_HOST) {
    file_put_contents($logFolder . '/' . $dateLink . '.result.txt', 
        print_r($answer, true)
    );

} else {
    print_r($answer);
}