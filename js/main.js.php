<?
error_reporting(E_ERROR);

define('SESSION_CONTANTS', true);

$setting = require dirname(__DIR__) . '/configs/settings.php';
$dayPeriod = Day::getPeriod(date(DAY_FORMAT), 7);
$days = $dayPeriod['data'];
$technics = Technic::getWithContentsByDayPeriod($dayPeriod, [], TECHNIC_SORTING);

$activities = [];
foreach (glob(dirname(__DIR__) . '/lib/bp.activities/*') as $activityPath) {
    if (!is_dir($activityPath) || !file_exists($activityPath . '/params.php'))
        continue;

    $activityFolder = basename($activityPath);
    $activityCode = preg_replace_callback(
                        '/(\w)\W(\w)/',
                        function($parts) { return $parts[1] . strtoupper($parts[2]); },
                        $activityFolder
                    );
    $activities[$activityCode] = [
        'path' => $activityFolder,
        'data' => require $activityPath . '/params.php'
    ];
}
header('Content-Type: application/javascript; charset=utf-8');?>
;$(() => {
    var LANG_VALUES = <?=json_encode($langValues)?>;
    var SERVER_CONSTANTS = <?=json_encode($_SESSION['CONST_LIST'] ?? [])?>;
    var selector = {
        calendar: '#rental-calendar',
        filterArea: '.rc-filter',
        filterDateInput: '.rc-filter-date-input',
        activityList: '.rc-activity-list'
    };
    var classList = {
        noReaction: 'rc-no-reaction'
    };
    var ajaxURL = document.location.origin + SERVER_CONSTANTS.APPPATH + '?ajaxaction=';
    var BX24Auth;
    var bxLinks = [];
    var bxLinkCount = bxLinks.length;
    
    var activities = <?=json_encode($activities)?>;
    var notExistActivityCodes = [];
    var calendar;<?

    include __DIR__ . '/vue.components.js';?>

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
        BX24.callMethod(
            'bizproc.activity.add',
            Object.assign(
                {}, activities[activityCode].data,
                {
                    CODE: activityCode,
                    HANDLER: document.location.origin + SERVER_CONSTANTS.APPPATH + '/lib/bp.activities/' + activities[activityCode].path + '/index.php',
                    AUTH_USER_ID: 1,
                    USE_SUBSCRIPTION: 'Y',
                    DOCUMENT_TYPE: ['lists', 'BizprocDocument']
                }
            ),
            result => {
                console.log(result);
                addActivity();
            }
        );
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

        BX24.callMethod('bizproc.activity.delete', {code: activityCode}, result => deleteActivity(activityCodes));
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
        BX24.callMethod('bizproc.activity.list', {}, result => {
            for (var code in activities) {
                if (result.answer.result.indexOf(code) > -1) continue;

                notExistActivityCodes.push(code);
            }
            showApplication(notExistActivityCodes.length < 1);
       });
    }

    /**
     * Поверяет все ли сторонние библитеки из Битрикса подключены. Если ничего
     * не надо было подключать или все загрузилось успешно, то запускает
     * основной метод
     * 
     * @return void
     */
    var startApplicationWork = function() {
        if (--bxLinkCount > 0) return;

        checkActivities();
    }

    /**
     * Проверяет нужно ли подключить js или css - файлы из коробки. Если ничего не нужно
     * подключать, то сразу переходит к методу startApplicationWork, иначе запускает подключение
     * файлов и устанавливает startApplicationWork как обратчик события onload при их успешном
     * подключении
     * 
     * @return void
     */
    var prepareBXLibs = function() {
        if (!bxLinkCount) {
            startApplicationWork();
            return;
        }
        var currentTime = (new Date()).getTime();
        bxLinks.forEach(link => {
            if (/\.css$/i.test(link)) {
                var linkUnit = $('<link href="https://' + authData.domain + link + '?v=' + currentTime + '" rel="stylesheet" media="screen">');

            } else if (/\.js$/i.test(link)) {
                var linkUnit = $('<script>');
                linkUnit.attr({src: 'https://' + authData.domain + link + '?v=' + currentTime, 'async': false});

            } else {
                return;
            }
            linkUnit.on('load', startApplicationWork);
            document.body.appendChild(linkUnit.get(0));
        });
    }

    if (BX24) {
        BX24.init(() => {
            BX24Auth = BX24.getAuth();
            prepareBXLibs();
        });

    } else {
        showApplication(true);
    }
});