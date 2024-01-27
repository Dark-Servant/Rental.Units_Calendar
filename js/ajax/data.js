;var InfoserviceAjaxDataType = (function() {

    let mapMainFieldCodes = new WeakMap();
    let mapAlternativeFieldCodes = new WeakMap();
    let mapNotImportantCodes = new WeakMap();
    let mapDefaultFieldValues = new WeakMap();
    let mapBeforeDefaultMethods = new WeakMap();
    let mapAfterDefaultMethods = new WeakMap();

    /**
     * 
     * @param {*} fieldCodes 
     * @returns 
     */
    let getPreparedMainFields = function(fieldCodes) {
        let result = [];
        fieldCodes.forEach(code => {
            if (typeof(code) != 'string') return;

            result.push(code);
        })
        return result;
    }

    /**
     * 
     * @param {*} firstUnit 
     * @param {*} secondUnit 
     * @returns 
     */
    let getCommonValues = function(firstUnit, secondUnit) {
        if (!(firstUnit instanceof Array) || !(secondUnit instanceof Array))
            return [];

        return firstUnit.filter(value => secondUnit.includes(value));
    }

    /**
     * 
     * @param {*} ajaxRequest 
     * @returns 
     */
    let initTypeUnitByAjaxRequest = function(ajaxRequest) {
        if (ajaxRequest) {
            return class extends AjaxDataClass {
                constructor() {
                    super(ajaxRequest);
                }
            };
            
        } else {
            return class extends AjaxDataClass {};
        }
    }

    /**
     *
     * @param {*} data
     * @returns
     */
    let getDefaultDataViaUnitAndData = function(unit, data) {
        let mainFields = getLastMapDataByImplements(mapMainFieldCodes, unit.constructor) || [];

        return {
            ...Object.fromEntries(mainFields.map(code => [code, data[code] || null])),
            ...(getLastMapDataByImplements(mapDefaultFieldValues, unit.constructor) || {})
        };
    }

    /**
     * 
     * @param {*} someObject 
     * @returns 
     */
    let getMethodsFromList = function(someObject) {
        if (!(someObject instanceof Array)) return {};

        let result = [];
        someObject.forEach(method => {
            if (typeof(method) != 'function') return;

            result.push(method);
        });
        return result;
    }

    /**
     * 
     * @param {*} someMap 
     * @param {*} typeUnit 
     */
    let runMethodsFromMapForTypeUnit = function(someMap, typeUnit)
    {
        let methods = getLastMapDataByImplements(someMap, typeUnit.constructor);
        if (!(methods instanceof Array) || !methods.length) return;

        methods.forEach(method => method(typeUnit));
    }

    /**
     * 
     * @param {*} classImplements 
     * @param {*} someMap 
     */
    let getLastMapDataByImplements = function(someMap, classImplements) {
        for (let classUnit = classImplements; classUnit.prototype; classUnit = Object.getPrototypeOf(classUnit)) {
            let data = someMap.get(classUnit);
            if (data) return data;
        }
        return false;
    }

    /**
     * 
     * @param {*} code 
     * @returns 
     */
    let defaultSetter = function(code) {
        return function(value) {
            return this.setPropertyValue(code, value);
        }
    }

    /**
     * 
     * @param {*} code 
     * @returns 
     */
    let defaultGetter = function(code) {
        return function() {
            return this.getPropertyValue(code);
        }
    }

    /**
     * 
     * @param {*} roleName 
     * @param {*} mainFields 
     * @param {*} codes 
     */
    let initSpecialRoleToMainFieldsForCodes = function(roleName, mainFields, codes) {
        let callBack = eval('default' + roleName);

        if (codes === false) {
            mainFields['set' + roleName + 'ForAll'](callBack);

        } else {
            mainFields['set' + roleName + 'ForSpecificFields'](callBack, codes);
        }
    }

    
    /**
     * 
     */
    let MainFields = (function() {

        /**
         * 
         * @param {*} callBack 
         * @param {*} unit 
         * @param {*} codes 
         * @returns 
         */
        let setCallBackResultToObjectForSpecificFields = function(callBack, unit, codes) {
            if ((typeof(callBack) != 'function') || !(codes instanceof Array) || !(unit instanceof Object))
                return;

            codes.forEach(code => {
                let result = callBack(code);
                if (typeof(result) != 'function') return;

                unit[code] = result;
            });
        }

        /**
         * 
         */
        return class {
            #fieldCodes = []
            #setters = {}
            #getters = {}

            /**
             * 
             * @param {*} fieldCodes 
             */
            constructor(fieldCodes) {
                this.#fieldCodes = getPreparedMainFields(fieldCodes);
            }

            /**
             * 
             * @param {*} callBack 
             * @returns 
             */
            setSetterForAll(callBack) {
                setCallBackResultToObjectForSpecificFields(callBack, this.#setters, this.#fieldCodes);
                return this;
            }

            /**
             * 
             * @param {*} callBack 
             * @param {*} codes 
             * @returns 
             */
            setSetterForSpecificFields(callBack, codes) {
                setCallBackResultToObjectForSpecificFields(callBack, this.#setters, getCommonValues(this.#fieldCodes, getPreparedMainFields(codes)));
                return this;
            }

            /**
             * 
             * @param {*} callBack 
             * @returns 
             */
            setGetterForAll(callBack) {
                setCallBackResultToObjectForSpecificFields(callBack, this.#getters, this.#fieldCodes);
                return this;
            }

            /**
             * 
             * @param {*} callBack 
             * @param {*} codes 
             * @returns 
             */
            setGetterForSpecificFields(callBack, codes) {
                setCallBackResultToObjectForSpecificFields(callBack, this.#getters, getCommonValues(this.#fieldCodes, getPreparedMainFields(codes)));
                return this;
            }

            /**
             * 
             * @param {*} unit 
             * @returns 
             */
            defineFieldsAtClass(unit) {
                if (!(unit instanceof Object)) return this;

                this.#fieldCodes.forEach(code => {
                    let options = {};

                    if (this.#setters[code]) options.set = this.#setters[code];
                    if (this.#getters[code]) options.get = this.#getters[code];

                    Object.defineProperty(unit.prototype, code, options);
                });
            }
        };
    })();

    /**
     *
     * @param {*} ajaxRequest
     * @returns
     */
    let AjaxDataClass = class {
        #ajaxRequest = false;
        #properties = {};
        #notReadyFields = [];

        /**
         *
         * @param {*} ajaxRequest
         * @returns
         */
        constructor(ajaxRequest) {
            this.setDefaultData();
            this.checkMainFieldReadiness();
            if (!(ajaxRequest instanceof InfoserviceAjax)) return;

            this.#ajaxRequest = ajaxRequest;
        }

        /**
         *
         * @returns
         */
        setDefaultData() {
            runMethodsFromMapForTypeUnit(mapBeforeDefaultMethods, this);
            this.#properties = getDefaultDataViaUnitAndData(this, this.#properties);
            runMethodsFromMapForTypeUnit(mapAfterDefaultMethods, this);
            return this;
        }

        /**
         *
         * @param {*} name
         * @param {*} value
         * @returns
         */
        setPropertyValue(name, value) {
            if (typeof(name) != 'string') return this;

            this.#properties[name] = value;
            return this;
        }

        /**
         *
         * @param {*} name
         * @returns
         */
        getPropertyValue(name) {
            if (typeof(name) != 'string') return null;

            return this.#properties[name];
        }

        /**
         * 
         * @returns 
         */
        getNotReadyFields() {
            return [...this.#notReadyFields];
        }

        /**
         * 
         * @returns 
         */
        getMainFields() {
            return Object.fromEntries((getLastMapDataByImplements(mapMainFieldCodes, this.constructor) || []).map(code => [code, this.#properties[code]]));
        }

        /**
         * 
         * @returns 
         */
        getPropertyValues() {
            return JSON.parse(JSON.stringify(this.#properties));
        }

        /**
         *
         * @returns
         */
        isReady() {
            if (this.#ajaxRequest === false) return false;

            return this.checkMainFieldReadiness().#notReadyFields.length < 1;
        }

        /**
         * 
         * @returns 
         */
        checkMainFieldReadiness() {
            this.#notReadyFields = [];
            let mainFields = getLastMapDataByImplements(mapMainFieldCodes, this.constructor);
            let notImportantCodes = getLastMapDataByImplements(mapNotImportantCodes, this.constructor);
            let alternativeFieldCodes = getLastMapDataByImplements(mapAlternativeFieldCodes, this.constructor);
            mainFields.forEach(code => {
                if (
                    notImportantCodes.includes(code)
                    || this.isCorrectValue(this.#properties[code])
                ) return;

                if (typeof(alternativeFieldCodes[code]) == 'undefined') {
                    this.#notReadyFields.push(code);
                    return;
                }

                let alternativeCodes = alternativeFieldCodes[code];
                if (!(alternativeCodes instanceof Array)) alternativeCodes = [alternativeCodes];

                if (this.getNotReadyFieldsForCodes(alternativeCodes).length)
                    this.#notReadyFields.push(code);
            });
            return this;
        }

        /**
         * 
         * @param {*} codes 
         * @returns 
         */
        getNotReadyFieldsForCodes(codes) {
            let result = [];
            getPreparedMainFields(codes).forEach(code => {
                if (this.isCorrectValue(this.#properties[code])) return;

                result.push(code);
            });
            return result;
        }

        /**
         *
         * @param {*} value
         * @returns
         */
        isCorrectValue(value) {
            return (typeof(value) != 'undefined') && (value !== null);
        }

        /**
         *
         * @returns
         */
        async sendData() {
            let result = false;
            if (!this.isReady()) return {result: false};

            await this.#ajaxRequest.sendPOST(this.#properties).then(answer => result = answer);
            return result;
        }
    };

    /**
     *
     */
    let resultClass = class {
        #typeUnit = false;

        /**
         * По этим полям надо учесть, что в созданном от AjaxDataClass классе они не только
         * могут заполняться через сеттеры и отдавать значения через геттеры, но и при вызове
         * метода setDefaultData по заполнению данных по-умолчанию будут учитываться их текущие
         * значения кроме тех, что были указаны среди полей в этом классе через метод setDefaultValues
         * как поля со значениями по-умолчанию. Значения других полей, не обозначнных как главные
         * или поля по-умолчанию, будут при вызове setDefaultData сбрасываться
         */
        #mainFields = false;
        #setters = false;
        #getters = false;

        #defaultAjaxRequest = false;
        #alternativeFieldCodes = {};
        #notImportantCodes = [];
        #defaultFieldValues = {};
        #beforeDefaultMethods = [];
        #afterDefaultMethods = [];

        /**
         *
         * @param {*} mainFields
         * @returns
         */
        constructor(mainFields) {
            this.setMainFields(mainFields);
        }

        /**
         * 
         * @param {*} mainFields 
         * @returns 
         */
        setMainFields(mainFields) {
            if (!(mainFields instanceof Array)) return;

            this.#mainFields = getPreparedMainFields(mainFields);
        }

        /**
         * 
         */
        get mainFields() {
            return JSON.parse(JSON.stringify(this.#mainFields));
        }

        /**
         * 
         * @param {*} fieldCodes 
         * @returns 
         */
        setSpecificCodesAsSetter(fieldCodes) {
            if (!(fieldCodes instanceof Array)) return this;

            this.#setters = getCommonValues(this.#mainFields, getPreparedMainFields(fieldCodes));
            return this;
        }

        /**
         * 
         */
        get setters() {
            return JSON.parse(JSON.stringify(this.#setters));
        }

        /**
         * 
         * @param {*} fieldCodes 
         * @returns 
         */
        setSpecificCodesAsGetter(fieldCodes) {
            if (!(fieldCodes instanceof Array)) return this;

            this.#getters = getCommonValues(this.#mainFields, getPreparedMainFields(fieldCodes));
            return this;
        }

        /**
         * 
         */
        get getters() {
            return JSON.parse(JSON.stringify(this.#getters));
        }

        /**
         * 
         * @param {*} request 
         * @returns 
         */
        setDefaultAjaxRequest(request) {
            if (!(request instanceof InfoserviceAjax)) return this;

            this.#defaultAjaxRequest = request;
            return this;
        }

        /**
         * 
         */
        get ajaxRequest() {
            return JSON.parse(JSON.stringify(this.#defaultAjaxRequest));
        }

        /**
         *
         * @param {*} names
         * @returns
         */
        setAlternativeNames(names) {
            if (!(names instanceof Object)) return this;

            let result = {};
            Object.keys(names).forEach(code => {
                if (
                    (typeof(names[code]) != 'string')
                    && !(names[code] instanceof Array)
                ) return;

                result[code] = names[code];
            });
            this.#alternativeFieldCodes = result;
            return this;
        }

        /**
         * 
         */
        get alternativeFieldCodes() {
            return JSON.parse(JSON.stringify(this.#alternativeFieldCodes));
        }

        /**
         * 
         * @param {*} fieldCodes 
         * @returns 
         */
        setNotImportantCodes(fieldCodes) {
            if (!(fieldCodes instanceof Array)) return this;

            this.#notImportantCodes = getCommonValues(this.#mainFields, getPreparedMainFields(fieldCodes));
            return this;
        }

        /**
         * 
         */
        get notImportantCodes() {
            return JSON.parse(JSON.stringify(this.#notImportantCodes));
        }

        /**
         *
         * @param {*} values
         * @returns
         */
        setDefaultValues(values) {
            if (!(values instanceof Object)) return this;

            this.#defaultFieldValues = values;
            return this;
        }

        /**
         * 
         */
        get defaultValues() {
            return JSON.parse(JSON.stringify(this.#defaultFieldValues));
        }

        /**
         * 
         * @param {*} methods 
         */
        setMethodsBeforeDefaultIniting(methods) {
            this.#beforeDefaultMethods = getMethodsFromList(methods);
        }

        /**
         * 
         */
        get beforeDefaultMethods() {
            return [...this.#beforeDefaultMethods];
        }

        /**
         * 
         * @param {*} methods 
         */
        setMethodsAfterDefaultIniting(methods) {
            this.#afterDefaultMethods = getMethodsFromList(methods);
        }

        /**
         * 
         */
        get afterDefaultMethods() {
            return [...this.#afterDefaultMethods];
        }

        /**
         *
         * @returns
         */
        getTypeUnit() {
            let typeUnit = initTypeUnitByAjaxRequest(this.#defaultAjaxRequest);
            let mainFields = new MainFields(this.#mainFields);
            initSpecialRoleToMainFieldsForCodes('Setter', mainFields, this.#setters);
            initSpecialRoleToMainFieldsForCodes('Getter', mainFields, this.#getters);
            mainFields.defineFieldsAtClass(typeUnit);

            mapMainFieldCodes.set(typeUnit, this.#mainFields);
            mapAlternativeFieldCodes.set(typeUnit, this.#alternativeFieldCodes);
            mapNotImportantCodes.set(typeUnit, this.#notImportantCodes);
            mapDefaultFieldValues.set(typeUnit, this.#defaultFieldValues);
            mapBeforeDefaultMethods.set(typeUnit, this.#beforeDefaultMethods);
            mapAfterDefaultMethods.set(typeUnit, this.#afterDefaultMethods);

            return typeUnit;
        }
    };

    return resultClass;
})();