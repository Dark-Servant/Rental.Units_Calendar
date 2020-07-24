{
    el: selector.calendar,
    data: {
        calendarShow: startCalendar,
        activityInstalled: startCalendar,
        bx24inited: bx24inited,
        backtoactivities: backtoactivities,
        activities: activities,
        days: <?=json_encode($days)?>,
        technics: <?=json_encode($technics)?>,
        contentDetail: null
    },

    computed: {
        /**
         * Возвращает массив с техникой, где идут сначала вся техника и партнеры, отмеченные
         * как избранные, а потом обычные, т.е. перемещает все отмеченное как избранное вперед
         * списка
         * 
         * @return array
         */
        sortedTechnics() {
            var technics = this.technics.map((technic, index) => {
                                return {index: index, ...technic};
                            });
            var result = technics.filter(technic => technic.IS_CHOSEN);
            technics.forEach(technic => {
                if (!technic.IS_CHOSEN) result.push(technic);
            });
            return result;
        }
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
            data.user = {...currentUserData};
            $(selector.filterArea).addClass(classList.noReaction);
            $.get(ajaxURL.replace(/#action#/i, 'getcontents'), data, answer => {
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
        },

        /**
         * Устанавливает или снимает избранность пользователя с техники или партнера
         * 
         * @param index - порядковый номер техники или партнеры в списке technics
         * @param starObj - объект, указывающий на звезду для выбора избранноси
         *
         * @return void
         */
        setChosen(index, starObj) {
            var technic = this.technics[index];
            technic.IS_CHOSEN = !technic.IS_CHOSEN;
            
            var cellUnit = $(starObj).closest(selector.technicUnit);
            cellUnit.addClass(classList.noReaction);

            $.post(ajaxURL.replace(/#action#/i, 'setchosen'), {
                technic: {...technic},
                user: {...currentUserData}
            }, answer => {
                cellUnit.removeClass(classList.noReaction);
                if (!answer.result) return;
            });
        },

        /**
         * заполняет данными свойство contentDetail, что приводит к открытию
         * модального окна с описанием и комментариями для контента
         * 
         * @param technicIndex - порядковый номер техники или партнера в таблице
         * @param contentDay - timestamp-метка для даты выбранного контента
         * @return void
         */
        showContentDetails(technicIndex, contentDay) {
            this.contentDetail = {
                ...this.technics[technicIndex],
                CONTENTS: undefined,
                DATE: (new Date(contentDay * 1000)).toLocaleDateString(),
                DEAL_INDEX: 0,
                DEALS: this.technics[technicIndex].CONTENTS[contentDay].DEALS
            };

            setTimeout(() => verticalCenterWindow(), 1);
        },

        /**
         * Сбрасывает свойство contentDetail, что приводит к закрытию модального окна
         * с описанием контента для техники или партнера
         * 
         * @return void
         */
        closeDetailModal() {
            this.contentDetail = null;
        }
    }
}