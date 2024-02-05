/**
 * 
 */
;var BXRestApi = (function() {

    /**
     * 
     */
    return function(action, params) {
        if (BX24_IS_INITED) {
            return new Promise(success => BX24.callMethod(action, params || {}, success));

        } else {
            return Promise.resolve({
                answer: {
                    result: false
                }
            });
        }
    };
})();