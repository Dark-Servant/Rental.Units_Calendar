<?
use Frontend\AutoLoader\{File, Path};
use Types\StringType;
use REST\Day as RestDay;

if (!isset($_REQUEST['ajaxaction'])) return;
error_reporting(E_ERROR);

$action = $_REQUEST['ajaxaction'];

$answer = ['result' => true];
set_time_limit(0);

try {
    switch ($action) {

        //
        case 'getactivities':
            $answer['data'] = $activities = BPActivity::getUnits();
            break;

        //
        case 'loadvuecomponents':
            $path = (new File('*.js'))->getFilePathValueViaTemplate(Path::getBaseTemplates()[Path::SOLUTION_VUE_COMPONENTS]);
            foreach (glob($_SERVER['DOCUMENT_ROOT'] . '/' . $path->getValue()) as $componentFile) {
                $answer['data'][StringType::getCamelCaseFileName($componentFile)] = file_get_contents($componentFile);
            }
            break;

        // Обработчик получения данных техники согласно фильтру в календаре
        case 'getcontents';
            $startDate = isset($_REQUEST['date'])
                       ? (new DateTime)->setTimestamp(intval($_REQUEST['date']))->setTime(0, 0)
                       : false;
            if (!empty($_REQUEST['user'])) {
                $responsible = Responsible::initialize($_REQUEST['user']);

                if (empty($_REQUEST['quarterNumber'])) {
                    if (!$startDate)
                        $startDate = ($responsible->calendar_date ?: new DateTime)->setTime(0, 0);

                    if (
                        !$responsible->calendar_date
                        || ($responsible->calendar_date->format(\Day::FORMAT) != $startDate->format(\Day::FORMAT))
                    ) {
                        $responsible->calendar_date = $startDate;
                        $responsible->save();
                    }
                }
            }
            if ($startDate === false) throw new Exception($langValues['ERROR_DATE_VALUE']);

            $filter = [];
            if ($_REQUEST['my-technic'] == 'true') $filter['IS_MY'] = 1;

            $user = $_REQUEST['user'];
            $restDay = new RestDay($startDate->getTimestamp());
            $answer['data'] = [
                'days' => $restDay->getIntervalWithDays(),
                'technics' => Technic::getWithContentsByDayPeriod(
                                    empty($user) ? 0 : intval($user['ID']),
                                    $restDay->getIntervalWithDayTimeStamps(),
                                    $filter,
                                    TECHNIC_SORTING
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
            if (empty($className::find_by_id($technicId)))
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

            $chosenTechnics = ChosenTechnic::find('first', ['conditions' => $data]);
            $data['is_active'] = $technic['IS_CHOSEN'] == 'true';
            if ($chosenTechnics) {
                $chosenTechnics->set_attributes($data);
                $chosenTechnics->save();

            } else {
                ChosenTechnic::create($data);
            }
            break;

        // обработчик удаления контента за конкретный день
        case 'removedeal':
            Content::find_by_id($_POST['dealID'])
                   ->cleanDataAtDay((new \DateTime)->setTimestamp($_POST['contentDate']));

            $answer['data'] = Technic::getWithContentsByDayPeriod(
                                    $_POST['user']['ID'],
                                    (new RestDay)->getIntervalWithDayTimeStamps(),
                                    [($_POST['isPartner'] == 'true' ? 'partner_id' : 'id') => $_POST['technicID']]
                                );
            break;

        // обработчик добавления/изменения комментариев
        case 'addcomment':
            $responsible = Responsible::initialize($_POST['user']);
            $commentValue = trim(strval($_POST['value']));
            if (empty($commentValue))
                throw new Exception($langValues['ERROR_EMPTY_COMMENT_VALUE']);

            $commentId = intval($_POST['commentId']);
            $contentsByDayFilter = [];
            if ($commentId) {
                $comment = Comment::find_by_id($commentId);
                if (empty($comment))
                    throw new Exception($langValues['ERROR_EMPTY_COMMENT_BY_ID']);

                if ($comment->user_id != $responsible->id)
                    throw new Exception($langValues['ERROR_COMMENT_AUTHOR_EDITING']);

                $technic = $comment->technic;
                $contentDay = $comment->content_date->getTimestamp();
                $contentsByDayFilter = $technic->partner_id
                                     ? ['partner_id' => $technic->partner_id]
                                     : ['id' => $technic->id];

            } else {
                $technicId = intval($_POST['technicId']);
                if (!$technicId) throw new Exception($langValues['ERROR_EMPTY_TECHNIC_AND_COMMENT_IDS']);

                if ($_POST['isPartner']) {
                    $technic = Technic::find_by_partner_id($technicId);
                    if (empty($technic)) throw new Exception($langValues['ERROR_EMPTY_PARTNER_TECHNIC_LIST']);

                    $contentsByDayFilter = ['partner_id' => $technicId];
                    $technicId = $technic->id;

                } else {
                    $contentsByDayFilter = ['id' => $technicId];
                }

                $contentDay = intval($_POST['contentDay']);
                $comment = new Comment;
                $comment->content_date = $contentDay;
                $comment->technic_id = $technicId;
                $comment->content_id = intval($_POST['contentId']);
                $comment->user_id = $responsible->id;
                if (isset($_POST['code'])) $comment->duty_status = intval($_POST['code']);
            }
            $comment->value = $commentValue;
            $comment->save();

            $answer['data'] = Technic::getWithContentsByDayPeriod($_POST['user']['ID'], [$contentDay], $contentsByDayFilter);
            break;

        // обработчик удаления комментария
        case 'removecomment':
            $user = $_POST['user'];
            if (
                empty($user['ID'])
                || empty($responsible = Responsible::find_by_external_id($user['ID']))
            ) throw new Exception($langValues['ERROR_EMPTY_USER_ID']);

            $commentId = intval($_POST['commentId']);
            if (!$commentId || empty($comment = Comment::find_by_id($commentId)))
                throw new Exception($langValues['ERROR_EMPTY_COMMENT_BY_ID']);
            
            if ($comment->user_id != $responsible->id)
                throw new Exception($langValues['ERROR_COMMENT_AUTHOR_EDITING']);
            
            $contentDay = $comment->content_date->getTimestamp();
            $technic = $comment->technic;
            $contentsByDayFilter = $technic->partner_id
                                 ? ['partner_id' => $technic->partner_id]
                                 : ['id' => $technic->id];
            $comment->delete();

            $answer['data'] = Technic::getWithContentsByDayPeriod($_POST['user']['ID'], [$contentDay], $contentsByDayFilter);
            break;

        // обработчик отметки, что комментарии были прочитаны пользователем
        case 'readcomments':
            $responsible = Responsible::initialize($_POST['user']);
            if (!is_array($_POST['comment_ids']))
                break;
                            
            ReadCommentMark::setMark($responsible->id, $_POST['comment_ids']);
            break;

        // обработчик копирования комментария
        case 'copycomment':
            $user = $_POST['user'];
            if (
                empty($user['ID'])
                || empty($responsible = Responsible::find_by_external_id($user['ID']))
            ) throw new Exception($langValues['ERROR_EMPTY_USER_ID']);

            $commentId = intval($_POST['commentId']);
            if (!$commentId || empty($comment = Comment::find_by_id($commentId)))
                throw new Exception($langValues['ERROR_BAD_COMMENT_ID']);

            $startDate = $comment->content_date->getTimestamp();
            $finishDate = intval($_POST['date']);
            /**
             * Вычитаем или добавляем сутки, так как нет смысла копировать комментарий в ту же
             * дату, откуда он копируется. Можно, конечно, просто проверять в следующем цикле не
             * совпадает ли новая дата с датой комментария, но тогда будут затраты времени на
             * проверки по каждой дате, когда ясно, что комментарий не надо копировать только для
             * одной даты
             */
            if ($finishDate < $startDate) {
                [$startDate, $finishDate] = [$finishDate, $startDate - DAY_SECOND_COUNT];

            } else {
                $startDate += DAY_SECOND_COUNT;
            }

            foreach (range($startDate, $finishDate, DAY_SECOND_COUNT) as $dayTime) {
                $commentUnit = Comment::create([
                    'technic_id' => $comment->technic_id,
                    'content_date' => $dayTime,
                    'content_id' => 0,
                    'user_id' => $comment->user_id,
                    'value' => $comment->value,
                    'duty_status' => $comment->duty_status,
                ]);
                $answer['data'][$dayTime] = $commentUnit->getData(true);
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