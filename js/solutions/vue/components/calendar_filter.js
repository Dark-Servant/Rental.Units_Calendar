(function() {

    // Компонент для вывода блока с фильтром
    return {
        data() {
            return {
                chosenYear: ''
            };
        },

        computed: {

            /**
             * Возвращает выбранную дату в календаре в формат вывода даты, установленного локально.
             * Используется для вывода в поле ввода даты календаря
             * 
             * @return void
             */
            calendarDateValue() {
                return this.calendardate.toLocaleDateString();
            },

            /**
             * Возвращает список с годами, начиная за пять лет до года, установленного в фильтре
             * календаря, и заканчивая через пять лет после этого года. 
             * Используется в фильтре, когда идет работа с кварталами
             * 
             * @return array
             */
            yearList() {
                var halfValue = 4;
                return Array.from(Array(10).keys()).map(number => {
                    return number - halfValue + this.chosenYear;
                });
            },

            /**
             * Возвращает список кварталов. Используется в фильтре, когда идет работа с кварталами
             * 
             * @return array
             */
            quarterList() {
                return ['I', 'II', 'III', 'IV'];
            }
        },

        methods: {

            /**
             * Устанавливает в переменной основного приложения календаря дату согласно выбранному
             * кварталу и году
             *
             * @return void
             */
            setCalendarDateByQuarter() {
                var month = (InfoserviceCalendar.mainArea.quarterNumber - 1) * 3 + 1;
                InfoserviceCalendar.mainArea.calendarDate = new Date(this.chosenYear + '-' + (month < 10 ? '0' : '') + month + '-01');
            },

            /**
             * Обработчик изменения года или квартала при работе с кваталами
             * 
             * @return void
             */
            changeQuarterParams() {
                InfoserviceCalendar.mainArea.quarterNumber = this.quarter;
                this.setCalendarDateByQuarter();
            },

            /**
             * Обработчик нажатия кнопки "Сегодня"
             * 
             * @param event - данные события
             * @return void
             */
            setToday(event) {
                InfoserviceCalendar.mainArea.calendarDate = new Date();
            },

            /**
             * Обработчик нажатия в фильтре кнопки для включения просмотра данных кварталами.
             * Включает просмотр кварталами, если не был включен, иначе выключает
             * 
             * @return void
             */
            showQuarters() {
                /**
                 * В этом месте есть возможность запутаться. Дело в том, что сначала идет изменение
                 * переменной quarterNumber из самого календаря, затем изменение переменной calendarDate
                 * снова из этого календаря. На каждую из этих переменных наложены свои watch-обработчики
                 * в календаре. Возмжно, ожидается, что после изменения одной из них сразу же сработает
                 * свой watch-обработчик, т.е. сначала для quarterNumber, а затем для calendarDate, и каждый
                 * раз после любого watch-обработчик код продолжил свою работу далее после той строчки, где
                 * произошло изменение.
                 * Но это не так. После изменения этих переменных watch-обработчики не срабатывают сразу,
                 * VueJS, кажется, создает отложеннный обработчик, который сработает после работы кода, в
                 * котором произошло изменение, включая и все вызванные методы, если только они не работают
                 * асинхронно. Все изменения Vue-переменных запоминаются где-то в какой-то очереди у VueJS,
                 * и, когда начнет работу обработчик VueJS, установленный на изменения этих пеерменных, то
                 * он, видимо, берет эту очередь подвегшихся изменению переменных и вызывает для них свои
                 * watch-обработчики, но не в порядке изменения этих переменных, а в порядке их следования
                 * в блоке watch. Поэтому watch-обработчик изменения переменной quarterNumber ранее срабатывал
                 * позже watch-обработчика переменной calendarDate, хотя переменная quarterNumber была изменена
                 * раньше, потому что watch-обработчик переменной quarterNumber в блоке watch у календаря раньше
                 * был описан позже watch-обработчик переменной calendarDate
                 */
                if (InfoserviceCalendar.mainArea.quarterNumber) {
                    InfoserviceCalendar.mainArea.quarterNumber = 0;
                    InfoserviceCalendar.mainArea.calendarDate = new Date(this.savedCalendarTimeStamp);

                } else {
                    this.savedCalendarTimeStamp = InfoserviceCalendar.mainArea.calendarDate.getTime();
                    this.chosenYear = this.calendardate.getFullYear();
                    InfoserviceCalendar.mainArea.quarterNumber = Math.floor(InfoserviceCalendar.mainArea.calendarDate.getMonth() / 3) + 1;
                    this.setCalendarDateByQuarter();
                }
            }
        }
    };
})