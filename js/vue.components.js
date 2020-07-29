var VueComponentParams = {
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