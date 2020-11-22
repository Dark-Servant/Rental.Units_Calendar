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

            if (!empty($_REQUEST['user'])) {
                $responsible = Responsible::initialize($_REQUEST['user']);

                if (empty($_REQUEST['quarter-number']))
                    $responsible->calendar_date = $_REQUEST['date'];

                $responsible->save();
            }
            $filter = [];
            if ($_REQUEST['my-technic'] == 'true') $filter['IS_MY'] = 1;

            $dayCount = 7;
            if (!empty($_REQUEST['quarter-number'])) {
                /**
                 * везде берется на один день меньше, чем есть в квартале, так как первый день уже учтен
                 * в $startDate
                 *
                 * В 3м и 4м кварталах одинаковое количество дней
                 */
                if ($_REQUEST['quarter-number'] > 2) {
                    $dayCount = 91;

                // Во 2м квартале столько же дней, как и в 1м, если год высокосный
                } elseif (($_REQUEST['quarter-number'] > 1) || !(intval($_REQUEST['quarter-year']) & 3)) {
                    $dayCount = 90;

                } else {
                    $dayCount = 89;
                }
            }
            $days = Day::getPeriod(date(Day::FORMAT, $startDate->getTimestamp()), $dayCount);
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
            $className = $isNotPartner ? 'Technic' : 'Partner';
            if (empty($className::find($technicId)))
                 throw new Exception(
                            $isNotPartner ? $langValues['ERROR_BAD_TECHNIC_ID']
                                          : $langValues['ERROR_BAD_PARTNER_ID']
                        );

            $responsible = Responsible::initialize($_POST['user']);
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

        // обработчик добавления/изменения комментариев
        case 'addcomment':
            $responsible = Responsible::initialize($_POST['user']);
            $commentValue = trim(strval($_POST['value']));
            if (empty($commentValue))
                throw new Exception($langValues['ERROR_EMPTY_COMMENT_VALUE']);

            $commentId = intval($_POST['commentId']);
            if ($commentId) {
                $comment = Comment::find($commentId);
                if (empty($comment))
                    throw new Exception($langValues['ERROR_EMPTY_COMMENT_BY_ID']);

                if ($comment->user_id != $responsible->id)
                    throw new Exception($langValues['ERROR_COMMENT_AUTHOR_EDITING']);

            } else {
                $technicId = intval($_POST['technicId']);
                if (!$technicId) throw new Exception($langValues['ERROR_EMPTY_TECHNIC_AND_COMMENT_IDS']);

                if ($_POST['isPartner']) {
                    $technic = Technic::find_by_partner_id($technicId);
                    if (empty($technic)) throw new Exception($langValues['ERROR_EMPTY_PARTNER_TECHNIC_LIST']);

                    $technicId = $technic->id;
                }

                $day = date(Day::FORMAT, intval($_POST['contentDay']));
                $comment = new Comment;
                $comment->technic_id = $technicId;
                $comment->content_date = $day;
                $comment->content_id = intval($_POST['contentId']);
                $comment->user_id = $responsible->id;
            }

            $comment->value = $commentValue;
            $comment->save();

            $answer['data'] = $comment->getData();
            break;

        // обработчик удаления комментария
        case 'removecomment':
            $user = $_POST['user'];
            if (
                empty($user['ID'])
                || empty($responsible = Responsible::find_by_external_id($user['ID']))
            ) throw new Exception($langValues['ERROR_EMPTY_USER_ID']);

            $commentId = intval($_POST['commentId']);
            if (!$commentId || empty($comment = Comment::find($commentId)))
                throw new Exception($langValues['ERROR_EMPTY_COMMENT_BY_ID']);
            
            if ($comment->user_id != $responsible->id)
                throw new Exception($langValues['ERROR_COMMENT_AUTHOR_EDITING']);
            
            $comment->delete($commentId);
            break;

        default:
            throw new Exception($langValues['ERROR_BAD_ACTION']);
    }

} catch (Exception $error) {
    $answer = array_merge($answer, ['result' => false, 'message' => $error->GetMessage()]);
}

header('Content-Type: application/json; charset=utf-8');
die(json_encode($answer));