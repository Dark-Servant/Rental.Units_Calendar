<?
if (!isset($_REQUEST['ajaxaction'])) return;
error_reporting(E_ERROR);

$action = $_REQUEST['ajaxaction'];

$answer = ['result' => true];
set_time_limit(0);

try {
    switch ($action) {
        case 'getcontents';
            $startDate = date_create_from_format(DAY_CALENDAR_FORMAT, $_REQUEST['date']);
            if ($startDate === false) throw new Exception($langValues['ERROR_DATE_VALUE']);

            $days = Day::getPeriod(date(DAY_FORMAT, $startDate->getTimestamp()), 7);
            $answer['data'] = [
                'days' => $days,
                'technics' => Technic::getWithContentsByDayPeriod(
                                    $days,
                                    ['IS_MY' => intval($_REQUEST['my-technic'] == 'true')],
                                    TECHNIC_SORTING
                                ),
            ];
            break;

        default:
            throw new Exception($langValues['ERROR_BAD_ACTION']);
    }

} catch (Exception $error) {
    $answer = array_merge($answer, ['result' => false, 'message' => $error->GetMessage()]);
}

header('Content-Type: application/json; charset=utf-8');
die(json_encode($answer));