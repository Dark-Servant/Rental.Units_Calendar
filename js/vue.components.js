var VueComponentParams = {
    // Компонент для создания ячейки с контентом
    contentCell: {

        computed: {
            /**
             * Возвращает количество комментариев
             * 
             * @return int
             */
            commentSize() {
                return this.comments && this.comments[this.day] && Object.values(this.comments[this.day]).length;
            },

            /**
             * Возвращает последний комментарий в ячейке календаря
             * 
             * @return array
             */
            lastComment() {
                if (!this.commentSize) return;
                
                var comments = this.comments[this.day];
                return comments[comments.length - 1];
            },

            /**
             * Возвращает True, если в ячейке с контентом нет ни одного непрочитанного
             * текущим пользователем комментария, иначе будет возвращено False.
             * Возвращаемое значение влияет на цвет треугольника, указывающего на наличие
             * комментариев в ячейке с контентом
             * 
             * @return boolean
             */
            readComment() {
                return this.comments && this.comments[this.day]
                       && !this.comments[this.day].find(comment => !comment.READ );
            }
        }
    },

    // Компонент для вывода блока с фильтром
    calendarFilter: {
        data() {
            return {
                backtoactivities: backtoactivities,
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
                var month = (calendar.quarterNumber - 1) * 3 + 1;
                calendar.calendarDate = new Date(this.chosenYear + '-' + (month < 10 ? '0' : '') + month + '-01');
            },

            /**
             * Обработчик изменения года или квартала при работе с кваталами
             * 
             * @return void
             */
            changeQuarterParams() {
                calendar.quarterNumber = this.quarter;
                this.setCalendarDateByQuarter();
            },

            /**
             * Обработчик нажатия кнопки "Сегодня"
             * 
             * @param event - данные события
             * @return void
             */
            setToday(event) {
                calendar.calendarDate = new Date();
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
                if (calendar.quarterNumber) {
                    calendar.quarterNumber = 0;
                    calendar.calendarDate = new Date(this.savedCalendarTimeStamp);

                } else {
                    this.savedCalendarTimeStamp = calendar.calendarDate.getTime();
                    this.chosenYear = this.calendardate.getFullYear();
                    calendar.quarterNumber = Math.floor(calendar.calendarDate.getMonth() / 3) + 1;
                    this.setCalendarDateByQuarter();
                }
            }
        }
    },

    // Компонент для вывода всего календаря
    calendarTable: {

        computed: {

            /**
             * Возвращает список месяцев для просмотра календаря в режиме кварталов. Согласно дате,
             * установленной в календаре, возвращает те три месяца, т.е. кватал, где эта дата находится
             * 
             * @return array
             */
            months() {
                var monthTitles = <?=json_encode(array_values($langValues['DATE_CHOOOSING']['MONTHS']))?>;
                return Array.from(Array(3).keys()).map(monthDiff => {
                            var monthNumber = (this.quarter - 1) * 3 + monthDiff;
                            var dayCount = [0, 2, 4, 6, 7, 9, 11].indexOf(monthNumber) < 0 ? 30 : 31;
                            if (monthNumber == 1)
                                dayCount = calendar.calendarDate.getFullYear() & 3 ? 28 : 29;

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
                var firstDayTimeStamp = Object.keys(calendar.days)[0];
                var daySecondCount = SERVER_CONSTANTS.DAY_SECOND_COUNT;
                if (firstDayTimeStamp  == dayTimeStamp)
                    daySecondCount = -daySecondCount;

                calendar.calendarDate = new Date((parseInt(firstDayTimeStamp) + daySecondCount) * 1000);
            }
        }
    },

    // Компонент модального окна для выбора даты копирования комментария
    copyCommentModal: {
        data() {
            return {
                dateForCopyValue: new Date(calendar.contentDetail.CONTENT_DAY * 1000)
            };
        },

        computed: {

            /**
             * Возвращает дату, которая отображается в поле ввода даты,
             * до которой надо копировать
             * 
             * @return string
             */
            copyToDateValue() {
                return this.dateForCopyValue.toLocaleDateString();
            },
        },

        mounted() {
            setTimeout(
                () => initDatePicker(
                            selector.commentCopyDateInput,
                            (unitParams, selectedDate) => this.dateForCopyValue = selectedDate
                        ),
                500
            );
        },

        methods: {

            /**
             * Обработчик нажатия закрытия окна копирования комментария
             * 
             * @return void
             */
            closeCopyComment() {
                calendar.copyCommentIndex = false;
            },

            /**
             * Обработчик подтверждения копирования комментария
             * 
             * @return void
             */
            processCopyComment() {
                var data = {
                    date: Math.floor(this.dateForCopyValue.getTime() / 1000),
                    commentId: calendar.contentDetail.COMMENTS[calendar.copyCommentIndex].ID,
                    user: {...currentUserData}
                };

                var modalUnit = $(selector.copyCommentModalWindow);
                modalUnit.addClass(classList.noReaction);

                $.post(ajaxURL.replace(/#action#/i, 'copycomment'), data, answer => {
                    modalUnit.removeClass(classList.noReaction);
                    if (!answer.result) return;

                    var technic = calendar.technics[calendar.contentDetail.TECHNIC_INDEX];
                    Object.keys(technic.CONTENTS).forEach(contentDay => {
                        if (!answer.data[contentDay]) return;
                        if (!technic.COMMENTS[contentDay]) Vue.set(technic.COMMENTS, contentDay, []);

                        technic.COMMENTS[contentDay].push(answer.data[contentDay]);
                    });
                    this.closeCopyComment();
                });
            }
        }
    },

    // Компонент для модального окна
    contentDetailModal: {

        computed: {

            /**
             * Возвращает True, если была нажата кнопка копирования комментария
             * 
             * @return boolean
             */
            isCopyProcess() {
                return calendar.copyCommentIndex !== false;
            }
        },

        methods: {

            /**
             * Сбрасывает свойство contentDetail в календаре, что приводит к закрытию модального окна
             * с описанием контента для техники или партнера
             * 
             * @return void
             */
            closeDetailModal() {
                calendar.contentDetail = null;
            }
        }
    },

    // Компонент отрисовки сделки в модальном окне
    dealDetailModal: {

        methods: {

            /**
             * Обработчик нажатия синей кнопки "+", чтобы открыть отдельное окно в браузере с формой
             * создания новой CRM-сделки, где уже заранее заполненны конкретные поля
             * 
             * @return void
             */
            openDealCreating() {
                var dateValue = (new Date(calendar.contentDetail.CONTENT_DAY * 1000)).toLocaleDateString();
                $(
                    '<a target="_blank" '
                        + 'href="' + SERVER_CONSTANTS.MAIN_SERVER_URL + 'crm/deal/details/0/?category_id=0&'
                        + SERVER_CONSTANTS.CRM_USER_FIELD_TECHNIC_ID + '=' + calendar.contentDetail.EXTERNAL_ID + '&'
                        + 'COMMENTS=' +
                            encodeURI(
                                this.comments.map(comment => {
                                    var dateValue = new Date(comment['CREATED_AT'] * 1000);
                                    return comment['VALUE'] + "\n" + comment['USER_NAME']
                                         + ' (' + dateValue.toLocaleDateString() + ' '
                                                + dateValue.toLocaleTimeString() + ')';
                                }).join("\n\n")
                            ) + '&'
                        + SERVER_CONSTANTS.CRM_USER_FIELD_START_DATE + '=' + dateValue + '&'
                        + SERVER_CONSTANTS.CRM_USER_FIELD_COMPLETION_DATE + '=' + dateValue + '">'
                ).get(0).click();
            },

            /**
             * После нажатия желтой кнопки "+" у каждой сделки запоминает порядковый номер сделки в переменной
             * newCommentDealIndex, что приводит к скрытию кнопки "+" и появлению поля ввода комментария
             * 
             * @return void
             */
            initCommentAdd() {
                calendar.newCommentDealIndex = this.dealindex;
                calendar.editCommentIndex = false;
            },
        }
    },

    // Компонент отрисовки комментария
    commentUnit: {

        computed: {

            /**
             * Возвращает True, если комментарий находится в режиме редактирования
             * или создания
             * 
             * @return boolean
             */
            isEditing() {
                return this.isnothint && (calendar.editCommentIndex === this.commentindex);
            },

            /**
             * Возвращает True, если пользователь имеет право редактировать, удалять или
             * копировать комментарий
             * 
             * @return boolean
             */
            canEdit() {
                return this.isnothint && bx24inited && calendar.userData.ID
                       && (this.comment.USER_ID == calendar.userData.ID);
            },

            /**
             * Возвращает дату и время добавления комментария
             * 
             * @return string
             */
            authorDate() {
                var date = new Date(this.comment.CREATED_AT * 1000);
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
            }
        },

        methods: {

            /**
             * Обработчик включения редактирования комментария
             * 
             * @return void
             */
            initEditComment() {
                calendar.newCommentDealIndex = false;
                calendar.editCommentIndex = this.commentindex;
            },

            /**
             * Обработчик нажатия копирования комментария, показывает модальное
             * окно для копирования комментария
             * 
             * @return void
             */
            initCopyComment() {
                calendar.copyCommentIndex = this.commentindex;
            },

            /**
             * Обработчик удаления комментария при нажатии иконки с корзинкой
             * 
             * @return void
             */
            removeComment() {
                if (!confirm(LANG_VALUES.CONFIRM_MESSAGE_DELETING)) return;

                var commentId = calendar.contentDetail.COMMENTS[this.commentindex].ID;
                var modalUnit = $(selector.contentDetailWindow);
                modalUnit.addClass(classList.noReaction);

                $.post(ajaxURL.replace(/#action#/i, 'removecomment'), {
                    commentId: commentId,
                    user: {...currentUserData}
                }, answer => {
                    modalUnit.removeClass(classList.noReaction);
                    if (!answer.result) return;

                    calendar.contentDetail.COMMENTS.splice(this.commentindex, 1);
                });
            }
        }
    },

    // Компонент формы добавления или редактирования комментария
    commentUnitEditor: {

        methods: {

            /**
             * Обработчик нажатия крестика в области добавления комментария, убирает
             * значение в переменной newCommentDealIndex, что приводит к скрытию всех полей
             * добавления комментария и появлению кнопки "+"
             * 
             * @return void
             */
            stopCommentAdd() {
                calendar.newCommentDealIndex =
                calendar.editCommentIndex = false;
            },

            /**
             * Обработчик нажатия галочки для подтверждения добавления или изменения комментария
             * к сделке
             * 
             * @return void
             */
            commentAdd() {
                var commentValue = this.value.trim();
                if (!commentValue) return;

                var data = {
                    technicId: 0,
                    contentId: 0,
                    commentId: 0,
                    isPartner: 0,
                    contentDay: calendar.contentDetail.CONTENT_DAY,
                    value: commentValue,
                    user: {...currentUserData}
                };

                if (calendar.editCommentIndex !== false) {
                    data.commentId = calendar.contentDetail.COMMENTS[calendar.editCommentIndex].ID;

                } else if (calendar.newCommentDealIndex !== false) {
                    if (calendar.contentDetail.DEALS[calendar.newCommentDealIndex].TECHNIC_ID) {
                        data.technicId = calendar.contentDetail.DEALS[calendar.newCommentDealIndex].TECHNIC_ID;

                    } else {
                        data.technicId = calendar.contentDetail.ID;
                        data.isPartner = +calendar.contentDetail.IS_PARTNER;
                    }
                    data.contentId = calendar.contentDetail.DEALS[calendar.newCommentDealIndex].ID;

                } else {
                    return;
                }

                var modalUnit = $(selector.contentDetailWindow);
                modalUnit.addClass(classList.noReaction);

                $.post(ajaxURL.replace(/#action#/i, 'addcomment'), data, answer => {
                    modalUnit.removeClass(classList.noReaction);
                    if (!answer.result) return;

                    if (data.technicId) {
                        calendar.technics[calendar.contentDetail.TECHNIC_INDEX].COMMENTS[calendar.contentDetail.CONTENT_DAY].push(answer.data);

                    } else {
                        calendar.contentDetail.COMMENTS[calendar.editCommentIndex].VALUE = answer.data.VALUE;
                    }
                    this.stopCommentAdd();
                });
            }
        }
    }
};

$('*[type="text/vue-component"]').each((unitNum, unitObj) => {
    var componentSelector = ($(unitObj).attr('id') || '').replace(/\-component$/i, '');
    if (!componentSelector) return;

    var paramSelector = componentSelector.replace(/\W(\w)/g, (...parts) => parts[1].toUpperCase() );
    var params = VueComponentParams[paramSelector] ? VueComponentParams[paramSelector] : {};
    var props = $(unitObj).data('props');
    if (params.props instanceof Object) {
        props = params.props;

    } else {
        props = props ? props.trim().split(/\s*,\s*/) : [];
    }

    var componentData = {
        ...params,
        props: props,
        template: $(unitObj).html().trim().replace(/\s+/g, ' ')
    };

    Vue.component(componentSelector, componentData);
});