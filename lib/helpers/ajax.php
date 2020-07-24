<?
if (!isset($_REQUEST['ajaxaction'])) return;
error_reporting(E_ERROR);

$action = $_REQUEST['ajaxaction'];

$answer = ['result' => true];
set_time_limit(0);

try {
    switch ($action) {

        // Обработчик получения данных техники согласно фильтру в календаре
        case 'getcontents';
            $startDate = date_create_from_format(Day::CALENDAR_FORMAT, $_REQUEST['date']);
            if ($startDate === false) throw new Exception($langValues['ERROR_DATE_VALUE']);

            $filter = [];
            if ($_REQUEST['my-technic'] == 'true') $filter['IS_MY'] = 1;

            $days = Day::getPeriod(date(Day::FORMAT, $startDate->getTimestamp()), 7);
            $user = $_REQUEST['user'];
            $answer['data'] = [
                'days' => $days,
                'technics' => Technic::getWithContentsByDayPeriod(
                                    empty($user) ? 0 : intval($user['ID']), $days, $filter, TECHNIC_SORTING
                                ),
            ];
            break;

        // Обработчик установки или снятия техники или партнера как избранных
        case 'setchosen':
            $technic = $_POST['technic'];
            if (
                !isset($technic['ID']) || !($technicId = intval($technic['ID']))
            ) throw new Exception($langValues['ERROR_EMPTY_TECHNIC_ID']);

            $isNotPartner = $technic['IS_PARTNER'] == 'false';
            $filter = $isNotPartner
                    ? ['ID' => $technicId]
                    : ['PARTNER_ID' => $technicId];
            if (empty(Technic::find('first', $filter)))
                 throw new Exception(
                            $isNotPartner ? $langValues['ERROR_BAD_TECHNIC_ID']
                                          : $langValues['ERROR_BAD_PARTNER_ID']
                        );

            $user = $_POST['user'];
            if (
                !isset($user['ID']) || !($userId = intval($user['ID']))
            ) throw new Exception($langValues['ERROR_EMPTY_USER_ID']);

            $responsible = Responsible::find_by_external_id($userId);
            if (!$responsible) $responsible = new Responsible;

            $responsible->external_id = $userId;
            $nameValue = '';
            foreach (['LAST_NAME', 'NAME', 'SECOND_NAME'] as $namePart) {
                $partvalue = trim($user[$namePart]);
                if (!$partvalue) continue;

                $nameValue .= ($nameValue ? ' ' : '') . $partvalue;
            }
            $responsible->name = trim($nameValue) ?: strval($user['EMAIL']);
            $responsible->save();

            $data = [
                'user_id' => $responsible->id,
                'entity_id' => $technicId,
                'is_partner' => !$isNotPartner
            ];

            $chosenTechnics = ChosenTechnics::find('first', ['conditions' => $data]);
            $data['is_active'] = $technic['IS_CHOSEN'] == 'true';
            if ($chosenTechnics) {
                $chosenTechnics->set_attributes($data);
                $chosenTechnics->save();

            } else {
                ChosenTechnics::create($data);
            }
            break;

        default:
            throw new Exception($langValues['ERROR_BAD_ACTION']);
    }

} catch (Exception $error) {
    $answer = array_merge($answer, ['result' => false, 'message' => $error->GetMessage()]);
}

header('Content-Type: application/json; charset=utf-8');
die(json_encode($answer));