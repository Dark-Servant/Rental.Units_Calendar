;document.addEventListener('infoservicevuecomponentloader:finished', function() {

    window.InfoserviceCalendar = new InfoserviceArea(
        {
            userData: {},
            mainArea: {},

            /**
             * Основной метод приложения, с которого начинается работа в нем
             * 
             * @return void
             */
            showApplication() {
                alert(LANG_VALUES.ERROR_JS_SHOWAPPLICATION_REPLACING);
            },

            /**
             * 
             */
            _init_() {
                this.initUserData()
                    .then(() => this.getContentsByFilter())
                    .then(answer => {
                        if (!answer.result) return;

                        this.showApplication(answer.data);
                        delete this.showApplication;
                    });
            },

            /**
             * 
             */
            async initUserData() {
                await BXRestApi('user.current').then(answer => {
                            if (answer.answer.result) {
                                Object.assign(
                                    this.userData,
                                    answer.answer.result,
                                    {
                                        IS_ADMIN: BX24.isAdmin()
                                    }
                                );

                            } else {
                                Object.assign(
                                    this.userData,
                                    {
                                        ID: 1,
                                        IS_ADMIN: false
                                    }
                                );
                            }
                        });
            },

            /**
             * 
             */
            async getContentsByFilter(filter) {
                if (!(filter instanceof Object)) filter = {};

                filter.user = this.userData;

                let result;
                await (new InfoserviceAjax('getcontents')).sendGET(filter).then(answer => result = answer);
                return result;
            },

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
            initDatePicker(inputSelector, onSelectCallback) {
                let modalPosition = false;
                let self = this;

                let dpUnit = datepicker(inputSelector, {
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

                        var modalUnit = calendarUnit.closest(self._selector.modalArea);
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
            },

            /**
             * 
             */
            showContentDetailModal() {
                this.verticalCenterWindow(this._selector.contentDetailModal);
            },

            /**
             * По всем указанным в переменной modalselector селекторам к модальным окнам
             * проверяет наличие окна через объект по селектору, указанному в параметре selector.
             * Если окно существует, то оно центрируется по вертикали
             *
             * @param selector - селектор к фону, на котором расположены модальные окна
             * @return void
             */
            verticalCenterWindow(selector) {
                /**
                 * Делается с ожиданием, чтобы изменения успели отрисоваться и стали доступны
                 * истинные размеры
                 */
                setTimeout(() => {
                    var bodyArea = $(selector).get(0).getBoundingClientRect();
                    this.selectModalWindow().each((modalCode, modalUnit) => {
                        var modalCodeRect = modalUnit.getBoundingClientRect();
                        var topvalue = modalCodeRect.height >= bodyArea.height ? 0
                                     : Math.floor((bodyArea.height - modalCodeRect.height) / 2);
                        $(modalUnit).css('top', topvalue + 'px');
                        $(modalUnit).removeClass(this._classList.noVisivility);
                    });
                }, 1);
            },

            /**
             * Обработчик скроллинга страницы. У шапки календаря устанавливает позицию, отвечающую
             * за левое смещение, чтобы элементы шапки всегда были над своими данными в календаре
             *
             * @return void
             */
            setHeaderLeftPositionValue() {
                var calendarRect = this.selectCalendar().get(0).getBoundingClientRect();

                this.selectHeader().css('left', calendarRect.left + 'px')
            }
        },
        {
            classList: {
                noVisivility: 'rc-no-visivility'
            },

            selector: {
                calendar: '#rental-calendar',
                header: '.rc-header',
                filterArea: '.rc-filter',
                filterDateInput: '.rc-filter-date-input',
                activityList: '.rc-activity-list',
                technicUnit: '.rc-technic-unit',
                contentArea: '.rc-content-area',
                modalArea: '.rc-modal',
                modalWindow: '.rc-window',
                contentDetailModal: '.rc-content-detail-modal',
                contentDetailWindow: '.rc-content-detail-window',
                copyCommentModalWindow: '.rc-copy-comment-modal-window',
                dealCommentInputArea: '.rc-deal-detail-comment-input-area',
                dealCommentTextarea: '.rc-deal-detail-comment-textarea',
                commentCopyDateInput: '.rc-comment-copy-date-input',
                hintWindow: '.rc-hint-window'
            },

            handles: {
                document: {
                    scroll: 'setHeaderLeftPositionValue'
                }
            }
        }
    );
});