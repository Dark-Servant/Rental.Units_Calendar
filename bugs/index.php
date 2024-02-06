<?
use PHPMailer\PHPMailer\PHPMailer;

define('NOT_CHANGE_DUBLICATES', true);
define('NOT_CHANGE_COMMENT_HOST', true);

require_once __DIR__ . '/../configs/settings.php';

$logFiles = [];

echo '*************** Contents' . PHP_EOL;
require_once __DIR__ . '/contents/dublicates/index.php';

echo '*************** Comments' . PHP_EOL;
require_once __DIR__ . '/comments/badhosts/index.php';

if (empty($logFiles)) return;

$mail = new PHPMailer;
$mail->setFrom('bk@infoservice.ru', 'Rental Units calendar');
$mail->addAddress('bk@infoservice.ru');

$bodyText = '';
foreach ($logFiles as $logFileName => $logFileData) {
    $bodyText .= sprintf('%s (%s)<br>', $logFileData['title'], $logFileName);
    $mail->addAttachment($logFileData['path'], $logFileName);
}

$mail->CharSet = 'utf-8';
$mail->isHTML(true);
$mail->Subject = 'Появились логи по некоторым багам';
$mail->Body = $bodyText;

$mail->send();