<?
define('SESSION_CONTANTS', true);

$setting = require dirname(__DIR__) . '/configs/settings.php';

$userData = null;
if (defined('AUTH_ID')) {
    $userData = (new BX24RestAPI(['domain' => DOMAIN, 'access_token' => AUTH_ID]))->callUserCurrent();
    if ($userData) $userData = $userData['result'];
}

$days = Day::getPeriod(date(Day::FORMAT), 7);
$technics = Technic::getWithContentsByDayPeriod($userData ? $userData['ID'] : 0, $days, [], TECHNIC_SORTING);
$activities = BPActivity::getParams();
header('Content-Type: application/javascript; charset=utf-8');?>
;$(() => {
    var LANG_VALUES = <?=json_encode($langValues)?>;
    var SERVER_CONSTANTS = <?=json_encode($_SESSION['CONST_LIST'] ?? [])?>;
    var selector = {
        calendar: '#rental-calendar',
        filterArea: '.rc-filter',
        filterDateInput: '.rc-filter-date-input',
        activityList: '.rc-activity-list',
        technicUnit: '.rc-technic-unit',
        contentDetailWindow: '.rc-content-detail-window',
        dealCommentInputArea: '.rc-deal-detail-comment-input-area',
        dealCommentTextarea: '.rc-deal-detail-comment-textarea',
    };
    var classList = {
        noReaction: 'rc-no-reaction',
        noVisivility: 'rc-no-visivility'
    };
    var modalSelector = {
        [selector.contentDetailWindow]: selector.contentDetailWindow
    };
    var ajaxURL = document.location.origin + SERVER_CONSTANTS.APPPATH + '?ajaxaction=#action#&' + SERVER_CONSTANTS.URL_SCRIPT_FINISH;
    var BX24Auth;
    var bx24inited = typeof SERVER_CONSTANTS.DOMAIN != 'undefined';
    var backtoactivities = bx24inited;
    var currentUserData = <?=$userData ? json_encode($userData) : '{}'?>;
    
    var activities = <?=json_encode($activities)?>;
    var notExistActivityCodes = [];
    var calendar;<?

    include __DIR__ . '/vue.components.js';?>

    /**
     * Возвращает правильно описанные параметры для запроса
     * 
     * @param params - параметры
     * @param parentCode - название родительского параметра (использовать не стоит,
     * он нужен только для случаев, если в params будет параметр, чье значение тоже
     * объект)
     * 
     * @return string
     */
    var getPreparedParams = function(params, parentCode) {
        var result = '';
        for (var code in params) {
            if (params[code] === null) continue;

            var paramName = code;
            if (parentCode) paramName = parentCode + '[' + paramName + ']';

            result += (result ? '&' : '') + (params[code] instanceof Object
                    ? getPreparedParams(params[code], paramName)
                    : paramName + '=' + encodeURI(params[code]));
        }
        return result;
    }

    /**
     * Обработчик Bitrix RestAPI запроса
     * 
     * @param name - название метода
     * @param params - параметры метода
     *
     * @return Promise
     */
    var BXRestAPISend = function(name, params) {
        var parameters = params instanceof Object ? params : {};
        return new Promise(success => {
            var readyParams = getPreparedParams({auth: BX24Auth.access_token, ...parameters});
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'https://' + BX24Auth.domain + '/rest/' + name);
            xhr.onload = () => success(JSON.parse(xhr.response));
            xhr.send(readyParams);
        });
    }

    /**
     * Основной метод приложения, с которого начинается работа в нем
     * 
     * @return void
     */
    var showApplication = function(startCalendar) {
        calendar = new Vue(<?include __DIR__ . '/vue.main.js';?>);
    }

    /**
     * Добавление действия Бизнес-процессов в систему
     * 
     * @return void
     */
    var addActivity = function() {
        if (!notExistActivityCodes.length) {
            $(selector.activityList).removeClass(classList.noReaction);
            calendar.activityInstalled = true;
            return calendar.showTable();
        }

        var activityCode = notExistActivityCodes.shift();

        BXRestAPISend(
            'bizproc.activity.add',
            {
                ...activities[activityCode],
                CODE: activityCode,
                HANDLER: document.location.origin + SERVER_CONSTANTS.APPPATH + '/lib/bp.activities/index.php',
                AUTH_USER_ID: 1,
                USE_SUBSCRIPTION: 'Y',
                DOCUMENT_TYPE: ['lists', 'BizprocDocument']
            }
        ).then(answer => {
            console.log(answer);
            addActivity();
        });
    }

    /**
     * Запуск процесса Добавления действий Бизнес-процессов в систему
     * 
     * @return void
     */
    var addActivities = function() {
        $(selector.activityList).addClass(classList.noReaction);
        addActivity();
    }

    /**
     * Удаление действия Бизнес-процессов из системы
     * 
     * @param activityCodes - список специальных кодов действий Бизнес-процессов
     * @return void
     */
    var deleteActivity = function(activityCodes) {
        if (!activityCodes.length) {
            $(selector.activityList).removeClass(classList.noReaction);
            calendar.activityInstalled = false;
            return;
        }

        var activityCode = activityCodes.shift();
        notExistActivityCodes.push(activityCode);

        BXRestAPISend('bizproc.activity.delete', {code: activityCode})
            .then(() => deleteActivity(activityCodes));
    }

    /**
     * Запуск процесса удаления действий Бизнес-процессов из системы
     * 
     * @return void
     */
    var deleteActivities = function() {
        $(selector.activityList).addClass(classList.noReaction);
        deleteActivity(Object.keys(activities));
    }

    /**
     * Проверка какие действия Бизнес-процессов установлены в системе
     * 
     * @return void
     */
    var checkActivities = function() {
        BXRestAPISend('bizproc.activity.list')
            .then(
                answer => {
                    if (answer.error) {
                        backtoactivities = false;
                        showApplication(true);
                        return;
                    }

                    for (var code in activities) {
                        if (answer.result.indexOf(code) > -1) continue;

                        notExistActivityCodes.push(code);
                    }
                    showApplication(notExistActivityCodes.length < 1);
                }
            )
    }

    /**
     * По всем указанным в переменной modalselector селекторам к модальным окнам
     * берет каждое окно, проверяет его на существование. Если окно существует, то
     * оно центрируется по вертикали
     * 
     * @return void
     */
    var verticalCenterWindow = function() {
        var bodyArea = document.body.getBoundingClientRect();
        for (var modalCode in modalSelector) {
            var modalUnit = $(modalSelector[modalCode]);
            if (!modalUnit.length) continue;

            var modalCodeRect = modalUnit.get(0).getBoundingClientRect();
            var topvalue = modalCodeRect.height >= bodyArea.height ? 0
                         : Math.floor((bodyArea.height - modalCodeRect.height) / 2);
            modalUnit.css('top', topvalue + 'px');
            modalUnit.removeClass(classList.noVisivility);
        }
    }

    if (bx24inited) {
        BX24.init(() => {
            BX24Auth = BX24.getAuth();
            BX24.resizeWindow(screen.width, screen.height);
            checkActivities();
        });

    } else {
        showApplication(true);
    }
});