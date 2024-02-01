(function() {

    // Компонент для создания ячейки с контентом
    return {
        computed: {
            /**
             * Возвращает True, если у контента есть сделки
             * 
             * @return void
             */
            dealExists() {
                return this.content && this.content.DEALS;
            },

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
    }
})