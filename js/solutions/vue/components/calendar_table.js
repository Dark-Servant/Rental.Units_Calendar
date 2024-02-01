(function() {

    // Компонент для вывода всего календаря
    return {
        computed: {

            /**
             * Возвращает список месяцев для просмотра календаря в режиме кварталов. Согласно дате,
             * установленной в календаре, возвращает те три месяца, т.е. кватал, где эта дата находится
             * 
             * @return array
             */
            months() {
                var monthTitles = LANG_VALUES.DATE_CHOOOSING.MONTHS;
                return Array.from(Array(3).keys()).map(monthDiff => {
                            var monthNumber = (this.quarter - 1) * 3 + monthDiff;
                            var dayCount = [0, 2, 4, 6, 7, 9, 11].indexOf(monthNumber) < 0 ? 30 : 31;
                            if (monthNumber == 1)
                                dayCount = InfoserviceCalendar.mainArea.calendarDate.getFullYear() & 3 ? 28 : 29;

                            return {
                                title: monthTitles[monthNumber],
                                number: monthNumber + 1,
                                dayCount: dayCount,
                                days: Array.from(Array(dayCount).keys())
                            };
                        });
            }
        },

        methods: {

            /**
             * Обработчик событий нажатия стрелочек для перехода начала недели на день вперед
             * или назад
             * 
             * @param dayTimeStamp - временная метка даты, рядом с которой нажата стрелка.
             * Если нажата рядом с первой датой недели, то переход идет на день назад, иначе
             * вперед
             * 
             * @return void
             */
            dayInc(dayTimeStamp) {
                var firstDayTimeStamp = Object.keys(InfoserviceCalendar.mainArea.days)[0];
                var daySecondCount = SERVER_CONSTANTS.DAY_SECOND_COUNT;
                if (firstDayTimeStamp  == dayTimeStamp)
                    daySecondCount = -daySecondCount;

                InfoserviceCalendar.mainArea.calendarDate = new Date((parseInt(firstDayTimeStamp) + daySecondCount) * 1000);
            }
        }
    };
})