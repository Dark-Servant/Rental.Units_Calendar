<?
$setting = require __DIR__ . '/../../../configs/settings.php';

$answer = ['result' => true];
$folder = __DIR__ . '/requests';
$dateLink = date('YmdHis');
if (!is_dir($folder)) mkdir($folder);


define('FROM_CMD', empty($_SERVER['HTTP_HOST']));

if (FROM_CMD) {
    require __DIR__ . '/data.php';

} else {
    file_put_contents($folder . '/' . $dateLink . '.request.txt', 
        print_r($_SERVER, true) . PHP_EOL .
        print_r($_REQUEST, true) . PHP_EOL .
        print_r($_SESSION['CONST_LIST'], true) . PHP_EOL .
        '-------' . PHP_EOL .
        json_encode($_SERVER) . PHP_EOL .
        '-------' . PHP_EOL .
        json_encode($_REQUEST) . PHP_EOL
    );
}

try {
    $activityCode = preg_replace_callback(
                        '/(\w)\W(\w)/',
                        function($parts) { return $parts[1] . strtoupper($parts[2]); },
                        basename(__DIR__)
                    );
    if ($activityCode != $_REQUEST['code'])
        throw new Exception($langValues['ERROR_ACTIVITY_CODE']);
    
    $activitySetting = require __DIR__ . '/params.php';
    foreach ($activitySetting['PROPERTIES'] as $propertyCode => $propertyParams) {
        if (
            (strtolower($propertyParams['Required']) == 'y')
            && empty($_REQUEST['properties'][$propertyCode])
        ) throw new Exception(strtr($langValues['ERROR_EMPTY_ACTIVITY_PROPERTY'], ['#PROPERTY#' => $propertyCode]));
    }
    $technic = Technic::find('first', ['external_id' => $_REQUEST['properties']['TECHNIC_ID']]);
    if (FROM_CMD) var_dump($technic);

    if (!$technic) $technic = new Technic();
    $technic->external_id = $_REQUEST['properties']['TECHNIC_ID'];
    $technic->name = $_REQUEST['properties']['NAME'];
    $technic->state_number = $_REQUEST['properties']['STATE_NUMBER'];
    $technic->loading_capacity = $_REQUEST['properties']['LOADING_CAPACITY'];
    $technic->partner_id = $_REQUEST['properties']['PARTNER_ID'];
    $technic->partner_name = $_REQUEST['properties']['PARTNER_NAME'];
    $technic->is_my = $_REQUEST['properties']['IS_MY'];
    $technic->is_visibility = $_REQUEST['properties']['VISIBILITY'];
    $technic->save();

    if (!FROM_CMD) {
        $restAPIUnit = new BX24RestAPI($_REQUEST['auth'], $folder . '/' . $dateLink . '.log.txt');
        $restAPIUnit->callBizprocEventSend(['EVENT_TOKEN' => $_REQUEST['event_token']]);
    }

} catch (Exception $error) {
    $answer = array_merge($answer, ['result' => false, 'message' => $error->GetMessage()]);
}

file_put_contents($folder . '/' . $dateLink . '.result.txt', print_r($answer, true));

if (FROM_CMD) {
    echo print_r($answer, true) . PHP_EOL;

} else {
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode($answer));
}