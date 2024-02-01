(function() {
    
    // Компонент формы добавления или редактирования комментария
    return {
        methods: {

            /**
             * Обработчик нажатия крестика в области добавления комментария, убирает
             * значение в переменной newCommentDealIndex, что приводит к скрытию всех полей
             * добавления комментария и появлению кнопки "+"
             * 
             * @return void
             */
            stopCommentAdd() {
                Object.assign(InfoserviceCalendar.mainArea, {
                    newCommentDealIndex: false,
                    editCommentIndex: false,
                });
            },

            /**
             * Обработчик нажатия галочки для подтверждения добавления или изменения комментария
             * к сделке
             * 
             * @return void
             */
            commentAdd() {
                let mainArea = InfoserviceCalendar.mainArea;
                let comment = new InfoserviceCalendarComment;
                comment.value = this.value.trim();

                if (mainArea.editCommentIndex !== false) {
                    comment.setCommentIndex(mainArea.editCommentIndex);

                } else if (mainArea.newCommentDealIndex !== false) {
                    comment.setDealIndex(mainArea.newCommentDealIndex);

                } else {
                    return;
                }
                InfoserviceCalendar.freezeContentDetailWindow();
                comment.sendData().then(answer => {
                    InfoserviceCalendar.unFreezeContentDetailWindow();
                    if (!answer.result) return;

                    this.stopCommentAdd();
                });
            }
        }
    };
})