;var InfoserviceAjax = InfoserviceAjax || (function() {

    return class {
        _url = null;

        /**
         * 
         * @param actionName - название действия
         */
        constructor(actionName) {
            this._url = document.location.origin + SERVER_CONSTANTS.APPPATH + '?ajaxaction=' + actionName;
        }

        /**
         * Создание ajax-запроса и возвращение созданного объекта запроса, чтобы можно
         * было прикрепить callback-функцию для обработки ответа
         * Параметры передаются как объект, в котором под свойствами указываются конкретные
         * входные параметры
         *
         * @param methodType - тип запроса (GET (по-умочланию), POST, PUT, DELETE)
         * @param params - дополнительные настройки запроса
         * @param data - данные запроса
         *
         * @return Object
         */
        sendAjax({methodType, params, data} = {methodType: 'GET'}) {
            return $.ajax({
                        type: methodType,
                        url: this._url,
                        ...(params instanceof Object ? params : {}),
                        data: (data instanceof Object ? data : {})
                    });
        }

        /**
         * Создание ajax-запроса с типом GET и возвращение созданного объекта запроса, чтобы
         * можно было прикрепить callback-функцию для обработки ответа
         *
         * @param data - данные запроса
         * @param params - дополнительные настройки запроса
         *
         * @return Object
         */
        sendGET(data = {}, params = {}) {
            return this.sendAjax({params: params, data: data});
        }

        /**
         * Создание ajax-запроса с типом POST и возвращение созданного объекта запроса, чтобы
         * можно было прикрепить callback-функцию для обработки ответа
         *
         * @param data - данные запроса
         * @param params - дополнительные настройки запроса
         *
         * @return Object
         */
        sendPOST(data = {}, params = {}) {
            return this.sendAjax({methodType: 'POST', params: params, data: data});
        }

        /**
         * Создание ajax-запроса с типом PUT и возвращение созданного объекта запроса, чтобы
         * можно было прикрепить callback-функцию для обработки ответа
         *
         * @param data - данные запроса
         * @param params - дополнительные настройки запроса
         *
         * @return Object
         */
        sendPUT(data = {}, params = {}) {
            return this.sendAjax({methodType: 'PUT', params: params, data: data});
        }
        
        /**
         * Создание ajax-запроса с типом DELETE и возвращение созданного объекта запроса, чтобы
         * можно было прикрепить callback-функцию для обработки ответа
         *
         * @param data - данные запроса
         * @param params - дополнительные настройки запроса
         *
         * @return Object
         */
        sendDELETE(data = {}, params = {}) {
            return this.sendAjax({methodType: 'DELETE', params: params, data: data});
        }
    };
})();