(function() {
    
    // Компонент отрисовки комментария
    return {
        computed: {

            /**
             * Возвращает True, если комментарий находится в режиме редактирования
             * или создания
             * 
             * @return boolean
             */
            isEditing() {
                return this.isnothint && (InfoserviceCalendar.mainArea.editCommentIndex === this.commentindex);
            },

            /**
             * Возвращает True, если пользователь имеет право редактировать, удалять или
             * копировать комментарий
             * 
             * @return boolean
             */
            canEdit() {
                let area = InfoserviceCalendar.mainArea;
                return this.isnothint && BX24_IS_INITED && area.userData.ID
                       && (this.comment.USER_ID == area.userData.ID);
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
                Object.assign(InfoserviceCalendar.mainArea, {
                    newCommentDealIndex: false,
                    editCommentIndex: this.commentindex
                })
            },

            /**
             * Обработчик нажатия копирования комментария, показывает модальное
             * окно для копирования комментария
             * 
             * @return void
             */
            initCopyComment() {
                InfoserviceCalendar.mainArea.copyCommentIndex = this.commentindex;
            },

            /**
             * Обработчик удаления комментария при нажатии иконки с корзинкой
             * 
             * @return void
             */
            removeComment() {
                if (!confirm(LANG_VALUES.CONFIRM_MESSAGE_DELETING)) return;

                let contentDetail = InfoserviceCalendar.mainArea.contentDetail;
                let commentId = contentDetail.COMMENTS[this.commentindex].ID;
                InfoserviceCalendar.freezeContentDetailWindow();

                (new InfoserviceAjax('removecomment')).sendPOST({
                    commentId: commentId,
                    user: InfoserviceCalendar.userData
                }).then(answer => {
                    InfoserviceCalendar.unFreezeContentDetailWindow();
                    if (!answer.result) return;

                    InfoserviceCalendar.updateCommentsAtModalByTechnicPeriodData(answer.data[0]);
                });
            }
        }
    };
})