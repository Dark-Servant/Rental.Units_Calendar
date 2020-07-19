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

    if (!$technic) throw new Exception(strtr(
                        $langValues['ERROR_PARENT_TECHNIC_OF_CONTENT'],
                        ['#ID#' => $_REQUEST['properties']['TECHNIC_ID']]
                    ));

    $content = Content::find('first', ['specification_id' => $_REQUEST['properties']['SPECIFICATION_ID']]);
    if (!$content) $content = new Content();
    
    $content->specification_id = $_REQUEST['properties']['SPECIFICATION_ID'];
    $content->technic_id = $technic->id;
    $content->begin_date = $_REQUEST['properties']['BEGIN_DATE'];
    $content->finish_date = $_REQUEST['properties']['FINISH_DATE'];
    $content->deal_url = $_REQUEST['properties']['DEAL_URL'];
    $responsible = Responsible::find('first', ['name' => $_REQUEST['properties']['RESPONSIBLE_NAME']]);
    if (!$responsible) {
        $responsible = new Responsible(['name' => $_REQUEST['properties']['RESPONSIBLE_NAME']]);
        $responsible->save();
    }
    $customer = Customer::find('first', ['name' => $_REQUEST['properties']['CUSTOMER_NAME']]);
    if (!$customer) {
        $customer = new Customer(['name' => $_REQUEST['properties']['CUSTOMER_NAME']]);
        $customer->save();
    }
    $content->responsible_id = $responsible->id;
    $content->customer_id = $customer->id;
    $content->work_address = $_REQUEST['properties']['WORK_ADDRESS'];
    $content->status = $_REQUEST['properties']['STATUS'];
    $content->is_closed = $_REQUEST['properties']['IS_CLOSED'];
    $content->save();

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