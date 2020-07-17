{
    el: selector.calendar,
    data: {
        calendarShow: startCalendar,
        activityInstalled: startCalendar,
        bx24inited: BX24 !== null,
        activities: activities,
        days: <?=json_encode($days)?>,
        technics: <?=json_encode($technics)?>,
    },

    /**
     * Срабатывает после готовности Vue-приложения
     * 
     * @return void
     */
    mounted() {
        this.initCalendar();
    },
    methods: {

        /**
         * Обработчик нажатия кнопки "Удалить" для удаления установленных
         * действий БП
         * 
         * @return void
         */
        removeActivities() {
            deleteActivities();
        },

        /**
         * Обработчик нажатия кнопки "Установить" для установки тех действий, которые
         * не были установлены
         *
         * @return void
         */
        addActivities() {
            addActivities();
        },

        /**
         * Инициализация календаря
         * 
         * @return void
         */
        initCalendar() {
            if (this.filterDateInput) return;

            this.filterDateInput = datepicker(selector.filterDateInput, {
                showAllDates: true,
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

                formatter: (input, date, instance) => input.value = date.toLocaleDateString(),
                onSelect: () => this.showData()
            });
        },

        /**
         * Получает данные фильтра, отправляет на сервер, выводит данные согласно
         * параметрам фильтра
         * 
         * @return void
         */
        showData() {
            var data = {};
            $(selector.filterArea).find('[name]').each((paramNum, paramObj) => {
                data[$(paramObj).attr('name')] = paramObj.type == 'checkbox' ? paramObj.checked : paramObj.value;
            });
            $(selector.filterArea).addClass(classList.noReaction);
            $.get(ajaxURL + 'getcontents', data, answer => {
                $(selector.filterArea).removeClass(classList.noReaction);
                if (!answer.result) return;

                this.days = answer.data.days;
                this.technics = answer.data.technics;
            });
        },

        /**
         * Обработчик нажатия кнопки "Сегодня"
         * 
         * @param event - данные события
         * @return void
         */
        setToday(event) {
            this.filterDateInput.setDate(new Date());
            this.showData();
        },

        /**
         * Обработчик нажатия кнопки "Показать календарь"
         * 
         * @return void
         */
        showTable() {
            this.calendarShow = true;
            this.filterDateInput = null;
            this.initCalendar();
        },

        /**
         * Обработчик нажатия иконки в правом верхнем углу фильтра для перехода
         * к списку установленных действий
         * 
         * @return void
         */
        showActivities() {
            this.calendarShow = false;
        }
    }
}