(function() {
    
    // Компонент для модального окна
    return {
        computed: {

            /**
             * Возвращает True, если была нажата кнопка копирования комментария
             * 
             * @return boolean
             */
            isCopyProcess() {
                return InfoserviceCalendar.mainArea.copyCommentIndex !== false;
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
                InfoserviceCalendar.mainArea.contentDetail = null;
            },

            /**
             * Обработчик кнопки для удаления контента за конкретный день
             * 
             * @param dealIndex
             * @return void
             */
            initDealRemoving(dealIndex) {
                if (!confirm(LANG_VALUES.CONFIRM_DEAL_DELETING)) return;

                let removeDeal = new InfoserviceDealRemoving;
                removeDeal.dealID = InfoserviceCalendar.mainArea.contentDetail.DEALS[dealIndex].ID;

                InfoserviceCalendar.freezeContentDetailWindow();
                removeDeal.sendData().then(() => InfoserviceCalendar.unFreezeContentDetailWindow());
            }
        }
    };
})