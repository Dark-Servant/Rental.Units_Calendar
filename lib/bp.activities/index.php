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

    $codeValue = strval($_REQUEST['code']) ?: '.empty';
    $dateLink = date('YmdHis');
    if (FROM_HTTP_HOST) {
        $logFolder = __DIR__ . '/.log/' . $codeValue . '/';
        if (!file_exists($logFolder)) mkdir($logFolder, 0777, true);

        file_put_contents($logFolder . '/' . $dateLink . '.request.txt',
            print_r($_SERVER, true) . PHP_EOL .
            print_r($_REQUEST, true) . PHP_EOL
        );
    }
    $bpactivity = new BPActivity($codeValue);
    if (FROM_HTTP_HOST) {
        $restAPIUnit = new BX24RestAPI($_REQUEST['auth'], $logFolder . '/' . $dateLink . '.log.txt');

    } else {
        $restAPIUnit = new BX24RestAPI($_REQUEST['auth']);
    }

    $answer['answer'] = $bpactivity->setParams($_REQUEST['properties'] ?? [])->run($restAPIUnit, FROM_HTTP_HOST ? $_REQUEST['event_token'] : null);

} catch (Exception $error) {
    $answer = array_merge($answer, ['result' => false, 'message' => $error->GetMessage()]);
}

if (FROM_HTTP_HOST) {
    file_put_contents($logFolder . '/' . $dateLink . ($answer['result'] ? '.result.txt' : '.error.txt'), 
        print_r($answer, true)
    );

} else {
    print_r($answer);
}
