;var InfoserviceVueComponent = (function() {
    let vueComponentCodes = [];
    let vueComponentParameters = {};

    /**
     * 
     * @param {*} HTMLObj
     * @returns 
     */
    let getComponentCodeViaDOM = function(HTMLObj) {
        if (!(HTMLObj instanceof HTMLElement)) return false;

        return ($(HTMLObj).attr('id') || '').replace(/\-component$/i, '') || false;
    }

    /**
     * 
     * @param {*} code 
     * @returns 
     */
    let getSpecialCodeViaComponentCode = function(code) {
        return typeof(code) == 'string'
             ? code.replace(/\W(\w)/g, (...parts) => parts[1].toUpperCase())
             : false;
    }

    let result = class {
        #HTMLObj = false;
        #componentCode = false;
        #specialCode = false;

        /**
         * 
         * @param {*} DOMObj 
         */
        constructor(HTMLObj) {
            this.#HTMLObj = HTMLObj;
            this.#componentCode = getComponentCodeViaDOM(this.#HTMLObj);
            this.#specialCode = getSpecialCodeViaComponentCode(this.#componentCode);
        }

        /**
         * 
         * @returns 
         */
        getComponentCode() {
            return this.#componentCode;
        }

        /**
         * 
         * @returns 
         */
        getSpecialCode() {
            return this.#specialCode;
        }

        /**
         * 
         * @returns 
         */
        add() {
            if (vueComponentCodes.indexOf(this.#componentCode) > -1) return false;

            Vue.component(this.#componentCode, this.getParameters());
            vueComponentCodes.push(this.#componentCode);
        }

        /**
         * 
         * @returns 
         */
        getParameters = function() {
            let parameters = vueComponentParameters[this.#specialCode];
            if (parameters) {
                parameters = eval(parameters)();

            } else {
                parameters = {};
            }

            let properties;
            if (parameters.props instanceof Object) {
                properties = parameters.props;
        
            } else {
                properties = this.getProperties();
            }

            return {
                ...parameters,
                props: properties,
                template: $(this.#HTMLObj).html().trim().replace(/\s+/g, ' ')
            };
        }

        /**
         * 
         * @returns 
         */
        getProperties() {
            let properties = $(this.#HTMLObj).data('props');
            if (properties) {
                return properties.trim().split(/\s*,\s*/);
                
            } else {
                return [];
            }
        }
    };

    /**
     * 
     * @param {*} componentParameters 
     */
    result.saveVueComponentParameters = function(componentParameters) {
        for (let code in componentParameters) {
            if (typeof(vueComponentParameters[code]) != 'undefined') continue;

            vueComponentParameters[code] = componentParameters[code];
        }
    }

    return result;
})();