<?
define('SESSION_CONTANTS', true);

$setting = require dirname(__DIR__) . '/configs/settings.php';

$calendarDate = time();
$userData = null;
if (defined('AUTH_ID')) {
    $userData = (new BX24RestAPI(['domain' => DOMAIN, 'access_token' => AUTH_ID]))->callUserCurrent();
    if ($userData) {
        $userData = $userData['result'];
        $responsible = Responsible::initialize($userData);
        if ($responsible->calendar_date)
            $calendarDate = $responsible->calendar_date->getTimestamp();
    }
}

$days = Day::getPeriod(date(Day::FORMAT, $calendarDate), 7);
$technics = Technic::getWithContentsByDayPeriod($userData ? $userData['ID'] : 0, $days, [], TECHNIC_SORTING);
$activities = BPActivity::getUnits();
header('Content-Type: application/javascript; charset=utf-8');?>
;$(() => {
    var LANG_VALUES = <?=json_encode($langValues)?>;
    var SERVER_CONSTANTS = <?=json_encode($_SESSION['CONST_LIST'] ?? [])?>;
    var selector = {
        calendar: '#rental-calendar',
        header: '.rc-header',
        filterArea: '.rc-filter',
        filterDateInput: '.rc-filter-date-input',
        activityList: '.rc-activity-list',
        technicUnit: '.rc-technic-unit',
        contentArea: '.rc-content-area',
        modalArea: '.rc-modal',
        contentDetailModal: '.rc-content-detail-modal',
        contentDetailWindow: '.rc-content-detail-window',
        copyCommentModalWindow: '.rc-copy-comment-modal-window',
        dealCommentInputArea: '.rc-deal-detail-comment-input-area',
        dealCommentTextarea: '.rc-deal-detail-comment-textarea',
        commentCopyDateInput: '.rc-comment-copy-date-input',
        hintWindow: '.rc-hint-window'
    };
    var classList = {
        noReaction: 'rc-no-reaction',
        noVisivility: 'rc-no-visivility'
    };
    var modalSelector = [
        selector.contentDetailWindow,
        selector.copyCommentModalWindow
    ];
    var ajaxURL = document.location.origin + SERVER_CONSTANTS.APPPATH + '?ajaxaction=#action#&' + SERVER_CONSTANTS.URL_SCRIPT_FINISH;
    var BX24Auth;
    var bx24inited = typeof SERVER_CONSTANTS.DOMAIN != 'undefined';
    var backtoactivities = false; // bx24inited;
    var currentUserData = <?=$userData ? json_encode($userData) : '{}'?>;
    
    var activities = <?=empty($activities) ? '{}' : json_encode($activities)?>;
    var notExistActivityCodes = [];
    var calendar = false;<?

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
     * Создает календарь выбора даты, используя решение datepicker
     *
     * @param inputSelector - селектор к input-объекту с типом text, для которого показывать
     * модальное окно
     * 
     * @param onSelectCallback - функция, которую надо вызывать, когда выбрана конкретная
     * дата
     * 
     * @return datepicker
     */
    var initDatePicker = function(inputSelector, onSelectCallback) {
        var modalPosition = false;
        var dpUnit = datepicker(inputSelector, {
            showAllDates: true,
            alwaysShow: false,
            startDay: 1,
            customDays: [
                LANG_VALUES.DATE_CHOOOSING.DAYS.SHORT.SUN,
                LANG_VALUES.DATE_CHOOOSING.DAYS.SHORT.MON,
                LANG_VALUES.DATE_CHOOOSING.DAYS.SHORT.TUE,
                LANG_VALUES.DATE_CHOOOSING.DAYS.SHORT.WED,
                LANG_VALUES.DATE_CHOOOSING.DAYS.SHORT.THU,
                LANG_VALUES.DATE_CHOOOSING.DAYS.SHORT.FRI,
                LANG_VALUES.DATE_CHOOOSING.DAYS.SHORT.SAT
            ],
            customMonths: [
                LANG_VALUES.DATE_CHOOOSING.MONTHS.JANUARY,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.FEBRUARY,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.MARCH,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.APRIL,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.MAY,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.JUNE,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.JULY,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.AUGUST,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.SEPTEMBER,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.OCTOBER,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.NOVEMBER,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.DECEMBER
            ],
            overlayPlaceholder: LANG_VALUES.DATE_CHOOOSING_YEAR,
            onShow(datePickerUnit) {
                var calendarUnit = $(datePickerUnit.calendar).parent();
                if (modalPosition) {
                    calendarUnit.css(modalPosition);
                    return;
                }

                var modalUnit = calendarUnit.closest(selector.modalArea);
                if (!modalUnit.length) return;

                var {left: leftValue, top: topValue} = calendarUnit.get(0).getBoundingClientRect();
                modalPosition = {left: leftValue, top: topValue};

                modalUnit.get(0).appendChild(calendarUnit.get(0));
                calendarUnit.css(modalPosition);
            },

            onSelect(unitParams, selectedDate) {
                setTimeout(() => dpUnit.hide(), 1);

                if (typeof(onSelectCallback) == 'function')
                    onSelectCallback(unitParams, selectedDate);
            }
        });
        return dpUnit;
    }

    /**
     * Согласно оставленному комментарию, если он относится к дежурным, устанавливает
     * класс для ячейки, которая принадлежит конкретной технике
     * 
     * @param data - данные комментария
     * @param contentDay - метка времени, указывающая на конкретный день
     * @param technic - объект с данными техники
     * @return void
     */
    var setContentDutyStatus = function(data, contentDay, technic) {
        if (!data.DUTY_STATUS_NAME) return;

        if (!technic.CONTENTS[contentDay])
            Vue.set(technic.CONTENTS, contentDay, {});

        technic.CONTENTS[contentDay].STATUS_CLASS = data.DUTY_STATUS_NAME;
    }

    /**
     * Добавление комментария в модальном окне для просмотра данных ячейки календаря
     * 
     * @param comment - объект с данными комментария, могут быть два поля:
     *     value - текст комментария (обязательный параметр)
     *     code - статус, если комментарий дежурный
     *     
     * @param commentIndex - порядковый номер комментария в модальном окне
     * @param dealIndex - порядковый номер сделки в модяльном окне
     * @param successCallBack - функция, которую надо вызвать после добавления комментария
     * @return void
     */
    var createComment = function(comment, commentIndex, dealIndex, successCallBack) {
        if (
            !(comment instanceof Object)
            || (typeof(comment.value) != 'string')
            || !comment.value.trim()
        ) return;

        var data = {
            ...comment,
            technicId: 0,
            contentId: 0,
            commentId: 0,
            isPartner: 0,
            contentDay: calendar.contentDetail.CONTENT_DAY,
            user: currentUserData
        };

        if (commentIndex !== false) {
            data.commentId = calendar.contentDetail.COMMENTS[commentIndex].ID;

        } else if (dealIndex !== false) {
            if (calendar.contentDetail.DEALS[dealIndex].TECHNIC_ID) {
                data.technicId = calendar.contentDetail.DEALS[dealIndex].TECHNIC_ID;

            } else {
                data.technicId = calendar.contentDetail.ID;
                data.isPartner = +calendar.contentDetail.IS_PARTNER;
            }
            data.contentId = calendar.contentDetail.DEALS[dealIndex].ID;

        } else {
            return;
        }

        var modalUnit = $(selector.contentDetailWindow);
        modalUnit.addClass(classList.noReaction);

        $.post(ajaxURL.replace(/#action#/i, 'addcomment'), data, answer => {
            modalUnit.removeClass(classList.noReaction);
            if (!answer.result) return;

            var technic = calendar.technics[calendar.contentDetail.TECHNIC_INDEX];
            if (data.technicId) {
                technic.COMMENTS[calendar.contentDetail.CONTENT_DAY].push(answer.data);

            } else {
                calendar.contentDetail.COMMENTS[commentIndex].VALUE = answer.data.VALUE;
            }

            setContentDutyStatus(answer.data, calendar.contentDetail.CONTENT_DAY, technic);
            if (typeof(successCallBack) == 'function') successCallBack();
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
            calendar.activities = activities;
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
        ).then(answer => addActivity());
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
    var deleteActivity = function(activityCodes, callBack) {
        if (!activityCodes.length) {
            $(selector.activityList).removeClass(classList.noReaction);
            if (calendar) calendar.activityInstalled = false;
            return typeof(callBack) == 'function' ? callBack() : true;
        }

        var activityCode = activityCodes.shift();
        /**
         * В случае, если некоторые действия были удалены из решения, но они остались на портале,
         * то эти действия удалятся с портала, и их не надо запоминать в списке неустановленных
         * действий
         */
        if (activities[activityCode] instanceof Object)
            notExistActivityCodes.push(activityCode);

        BXRestAPISend('bizproc.activity.delete', {code: activityCode})
            .then(() => deleteActivity(activityCodes, callBack));
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
                    deleteActivity(
                        answer.result.filter(code => activities[code] == undefined ),
                        () => showApplication(notExistActivityCodes.length < 1)
                    );
                }
            )
    }

    /**
     * По всем указанным в переменной modalselector селекторам к модальным окнам
     * проверяет наличие окна через объект по селектору, указанному в параметре selector.
     * Если окно существует, то оно центрируется по вертикали
     *
     * @param selector - селектор к фону, на котором расположены модальные окна
     * @return void
     */
    var verticalCenterWindow = function(selector) {
        /**
         * Делается с ожиданием, чтобы изменения успели отрисоваться и стали доступны
         * истинные размеры
         */
        setTimeout(() => {
            var bodyArea = $(selector).get(0).getBoundingClientRect();
            modalSelector.forEach(modalCode => {
                var modalUnit = $(selector).find(modalCode);
                if (!modalUnit.length) return;

                var modalCodeRect = modalUnit.get(0).getBoundingClientRect();
                var topvalue = modalCodeRect.height >= bodyArea.height ? 0
                             : Math.floor((bodyArea.height - modalCodeRect.height) / 2);
                modalUnit.css('top', topvalue + 'px');
                modalUnit.removeClass(classList.noVisivility);
            });
        }, 1);
    }

    /**
     * Обработчик скроллинга страницы. У шапки календаря устанавливает позицию, отвечающую
     * за левое смещение, чтобы элементы шапки всегда были над своими данными в календаре
     *
     * @return void
     */
    var setHeaderLeftPositionValue = function() {
        var calendarRect = $(selector.calendar).get(0).getBoundingClientRect();

        $(selector.header).css('left', calendarRect.left + 'px')
    }

    if (bx24inited) {
        BX24.init(() => {
            BX24Auth = BX24.getAuth();
            checkActivities();
        });

    } else {
        showApplication(true);
    }

    $(document)
        .on('scroll', setHeaderLeftPositionValue)
    ;
});