(function() {
    
    // Компонент модального окна для выбора даты копирования комментария
    return {
        data() {
            return {
                dateForCopyValue: new Date(InfoserviceCalendar.mainArea.contentDetail.CONTENT_DAY * 1000)
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
                () => InfoserviceCalendar.initDatePicker(
                            InfoserviceCalendar._selector.commentCopyDateInput,
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
                InfoserviceCalendar.mainArea.copyCommentIndex = false;
            },

            /**
             * Обработчик подтверждения копирования комментария
             * 
             * @return void
             */
            processCopyComment() {
                let mainArea = InfoserviceCalendar.mainArea;
                let comment = new InfoserviceCalendarCopyComment;

                comment.date = Math.floor(this.dateForCopyValue.getTime() / 1000);
                comment.commentId = mainArea.contentDetail.COMMENTS[mainArea.copyCommentIndex].ID;
                
                InfoserviceCalendar.freezeCopyCommentModalWindow();
                comment.sendData().then(answer => {
                    InfoserviceCalendar.unFreezeCopyCommentModalWindow();
                    if (!answer.result) return;

                    this.closeCopyComment();
                });
            }
        }
    };
})