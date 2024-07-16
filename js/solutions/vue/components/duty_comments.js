(function() {
    
    // Компонент работы с дежурными комментариями
    return {
        data() {
            return {
                isChoosing: false,
                comments: [
                    {CODE: SERVER_CONSTANTS.DUTY_COMMENT_REPAIR_STATUS, NAME: LANG_VALUES.DUTY_COMMENT_REPAIR_STATUS},
                    {CODE: SERVER_CONSTANTS.DUTY_COMMENT_ON_ROAD_STATUS, NAME: LANG_VALUES.DUTY_COMMENT_ON_ROAD_STATUS},
                    {CODE: SERVER_CONSTANTS.DUTY_COMMENT_BASED_ON_STATUS, NAME: LANG_VALUES.DUTY_COMMENT_BASED_ON_STATUS},
                    {CODE: SERVER_CONSTANTS.DUTY_COMMENT_WEEKEND_STATUS, NAME: LANG_VALUES.DUTY_COMMENT_WEEKEND_STATUS}
                ],
                selectedcomment: SERVER_CONSTANTS.DUTY_COMMENT_REPAIR_STATUS
            };
        },

        methods: {

            /**
             * Показывает выбор дежурных комментариев и кнопки для подтверждения и отмены
             * 
             * @return void
             */
            initDutyChoosing() {
                this.isChoosing = true;
            },

            /**
             * Скрывает выбор дежурных комментариев и кнопки для подтверждения и отмены
             * 
             * @return void
             */
            closeDutyChoosing() {
                this.isChoosing = false;
            },

            /**
             * Добавляет дежурный комментарий
             *
             * @return void
             */
            addDutyComment() {
                var {CODE: code, NAME: value} = this.comments.find(comment => comment.CODE == this.selectedcomment);

                let comment = new InfoserviceCalendarComment;
                comment.value = value;
                comment.code = code;
                comment.setDealIndex(this.dealindex);

                InfoserviceCalendar.freezeContentDetailWindow();
                comment.sendData().then(answer => {
                    InfoserviceCalendar.unFreezeContentDetailWindow();
                    if (!answer.result) return;

                    this.closeDutyChoosing();
                });
            }
        }
    
    };
})