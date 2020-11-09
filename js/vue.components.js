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
        }
    },

    // Компонент для вывода всего календаря
    calendarTable: {

        methods: {

            /**
             * Обработчик событий нажатия стелочек для перехода начала недели на день вперед
             * или назаж
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

    // Компонент для модального окна
    contentDetailModal: {

        methods: {

            /**
             * Сбрасывает свойство contentDetail в календаре, что приводит к закрытию модального окна
             * с описанием контента для техники или партнера
             * 
             * @return void
             */
            closeDetailModal() {
                calendar.contentDetail = null;
            },
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
                calendar.newCommentDealIndex = false;
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

    var componentData = {
        ...params,
        props: props ? props.trim().split(/\s*,\s*/) : [],
        template: $(unitObj).html().trim().replace(/\s+/g, ' ')
    };

    Vue.component(componentSelector, componentData);
});