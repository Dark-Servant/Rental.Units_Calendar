<?
if (!isset($_REQUEST['ajaxaction'])) return;
$action = $_REQUEST['ajaxaction'];

$answer = ['result' => true];
set_time_limit(0);

try {
    switch ($action) {
        default:
            throw new Exception($langValues['ERROR_BAD_ACTION']);
    }

} catch (Exception $error) {
    $answer = array_merge($answer, ['result' => false, 'message' => $error->GetMessage()]);
}

header('Content-Type: application/json');
die(json_encode($answer));