(function() {
    
    // Компонент отрисовки сделки в модальном окне
    return {
        methods: {

            /**
             * Обработчик нажатия синей кнопки "+", чтобы открыть отдельное окно в браузере с формой
             * создания новой CRM-сделки, где уже заранее заполненны конкретные поля
             * 
             * @return void
             */
            openDealCreating() {
                var dateValue = (new Date(InfoserviceCalendar.mainArea.contentDetail.CONTENT_DAY * 1000)).toLocaleDateString();
                $(
                    '<a target="_blank" '
                        + 'href="' + SERVER_CONSTANTS.MAIN_SERVER_URL + 'crm/deal/details/0/?category_id=0&'
                        + SERVER_CONSTANTS.CRM_USER_FIELD_TECHNIC_ID + '=' + InfoserviceCalendar.mainArea.contentDetail.EXTERNAL_ID + '&'
                        + 'COMMENTS=' +
                            encodeURI(
                                this.comments.map(comment => {
                                    var dateValue = new Date(comment['CREATED_AT'] * 1000);
                                    return comment['VALUE'] + "BR" + comment['USER_NAME']
                                         + ' (' + dateValue.toLocaleDateString() + ' '
                                                + dateValue.toLocaleTimeString() + ')';
                                }).join("BR")
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
                Object.assign(InfoserviceCalendar.mainArea, {
                    newCommentDealIndex: this.dealindex,
                    editCommentIndex: false,
                });
            },
        }
    };
})