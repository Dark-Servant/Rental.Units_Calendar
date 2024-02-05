;var InfoserviceArea = InfoserviceArea || (function() {
    var lastIDValue = 0;
    var waitingAreas = [];

    /**
     * Базовые классы HTML-тегов, для некоторых реализованы методы
     */
    const BASE_CLASSES = {
        // значение класса для скрытия объекта из области видимости
        hidden: 'infs-hidden',

        /**
         * значение класса для блокирования элемента страницы к любым взаимодействиям, будь то
         * назначенные обработчики события, либо стили
         */
        noReaction: 'infs-no-reaction',

        /**
         * значение класса для обозначения, что он и все внутренние элементы не реагируют на
         * назначенные на него или его внутренние элементы обработчики события, но позволяет
         * работать css-стилям
         */
        disabled: 'infs-disabled',
    };

    /**
     * В экземпляр класса области компонента копируются все свойства другого объекта, кроме тех,
     * что уже имеются, для уже существующих свойсв будет вызвано исключение с ошибкой
     *
     * @param unit - экземпляр класса области компонента
     * @param behavior - какой-нибудь объект
     *
     * @return void
     */
    var initBehavior = function(unit, behavior) {
        for (let property in behavior) {
            if (typeof(unit[property]) != 'undefined')
                throw `ERROR: Property ${property} can't be replacing`

            unit[property] = behavior[property];
        }
    }

    /**
     * Сохраняет в экземпляре класса области компонента значения специальных классов HTML-тегов. Дополнительно
     * в экземпляре будут указаны значения классов, объявленные в константе BASE_CLASSES
     * 
     * @param unit - экземпляр класса области компонента
     * @param Object classList - список значений классов у HTML-тегов, которые указаны как значения свойств
     * объекта с имена свойств, через которые и надо потом работать с этими значениями классов, например,
     *      formError: 'test-error'
     * некий класс, который может служить для обозначения ошибки, для работы с ним далее желательно
     * пользоваться
     *      this._classList.formError
     *
     * @return void
     */
    var initClassList = function(unit, classList) {
        unit._classList = {};
        var data = {
            ...BASE_CLASSES,
            ...(classList instanceof Object ? classList : {})
        };
        for (var property in data) {
            unit._classList[property] = data[property].replace(/^.+ +/, '');
        }
    }

    /**
     * Для объекта, у которого под свойствами указаны строковые значения, делает обработку,
     * чтобы те значения свойств, которые включают значения других свойтсв через указанные в
     * фигурных скобках названия этих свойств, были замены на значения с уже вставленными
     * значениями других свойств. Например, некие свойства объекта имеют значения
     *      ...
     *      somePropertyOne: 'first value',
     *      somePropertyTwo: '{somePropertyThree} second value',
     *      somePropertyThree: '{somePropertyOne} third value',
     *      ...
     * После передачи методу объект в указанных свойствах будет иметь значения
     *      ...
     *      somePropertyOne: 'first value',
     *      somePropertyTwo: 'first value third value second value',
     *      somePropertyThree: 'first value third value',
     *      ...
     * ВНИМАНИЕ! Стоит следить за тем, что бы не случилось бесконечное зацикливание, когда значение
     * одного свойства ссылается на значение другого, а у другого на значение первого
     *
     * @param Object objectData - объект с разными свойствами, под значениями которых указаны
     * строковые значения
     *
     * @return void
     */
    var initRecurseValues = function(objectData) {
        const PARENT_PROPERTY_REGEX = /\{(\w+)\}/g;
        var nextStep = false;
        for (var property in objectData) {

            objectData[property] = objectData[property].replace(PARENT_PROPERTY_REGEX, (...data) => {
                return typeof(objectData[data[1]]) == 'string' ? objectData[data[1]] : '';
            })
            if (!nextStep) nextStep = objectData[property].match(PARENT_PROPERTY_REGEX) !== null;
        }
        if (nextStep) initRecurseValues(objectData);
    }

    /**
     * Переданное значение в аргументе selector поправляется так, что в начале каждой "части"
     * будет добавлено значение аргумента preValue, а в конце каждой части будет добавлено
     * значение аргумента endValue. Каждая "часть" в значении аргумента selector это части
     * значения, разделенные символом "запятая". Если "запятых" нет, значит, есть только одна
     * "часть"
     * 
     * @param selector - значение какого-то селектора
     * 
     * @param preValue - значение, которое надо добавить в значении аргумента selector в начале
     * каждой "части"
     * 
     * @param endValue - значение, которое надо добавить в значении аргумента selector в конце
     * каждой "части"
     * 
     * @param callBack - функция, которая при вызове получит
     *      - массив из частей значения аругумента selector
     * если функция вернет значение, отличное от null или undefined, то это значение и будет
     * результатом
     * 
     * @return array
     */
    var getMultiSelectorValue = function(selector, {preValue, endValue, callBack} = {preValue: '', endValue: '', callBack: null}) {
        let selectList = selector.split(/ *, */).map(selUnit => selUnit.trim());
        if (typeof(callBack) == 'function') {
            let result = callBack(selectList);
            if ((result!= null) && (result != undefined)) return result;
        }

        let preSelector = '';
        if ((typeof(preValue) == 'string') && (preValue.trim() > ''))
            preSelector = preValue.trimLeft();

        let endSelector = '';
        if ((typeof(endValue) == 'string') && (endValue.trim() > ''))
            endSelector = endValue.trimRight();

        return selectList.map(selUnit => preSelector + selUnit + endSelector).join(', ');
    }

    /**
     * Для объекта, у которого в свойствах указаны либо строковые значения, либо объекты со свойствами
     *      self - строковое значение, аналогичное значению, если б свойство основного объекта было
     *             не объектом, а строковым значением;
     *      pre - начальная часть селектора перед каждой "частью" значения из self, где "части" это разделенные
     *            друг от друга символом "запятая" части значения;
     *      end - конечная часть селектора после каждой "части" значения из self, где "части" это разделенные
     *            друг от друга символом "запятая" части значения;
     *      handles - объект с названиями событий как имена свойств и для каждого из свойств значением,
     *                указывающий на обработчик события - какой-то метод экземпляра класса области
     *                компонента
     * создает два объекта
     *      - объект со строковыми значениями под теми же ключами переданного объекта, где эти значения
     *        хранились либо сразу как строковые значения, либо в свойстве self с поправками из свойств
     *        pre и end;
     *      - объект, где под теми же свойствами, как и у переданного объекта, указаны данные объектов
     *        handles
     *
     * @param selector - объект с селекторами и, возможно, с названиями событий и обработчиков этих
     * событий
     *
     * @return array
     */
    var getSeparatedSelectorsAndHandlers = function(selector) {
        var _selector = {};
        var _handles = {};
        var _preAndEnd = {};
        for (let code in selector) {
            let value = selector[code];
            if (value instanceof Object) {
                if (typeof(value.self) != 'string')
                    continue;

                _selector[code] = value.self;
                if (typeof(value.pre) == 'string')
                    _preAndEnd[code] = {preValue: value.pre};

                if (typeof(value.end) == 'string')
                    _preAndEnd[code] = Object.assign(_preAndEnd[code] || {}, {endValue: value.end});

                if (!(value.handles instanceof Object))
                    continue;

                _handles[code] = value.handles;

            } else if (typeof(value) == 'string') {
                _selector[code] = value;
            }
        }

        initRecurseValues(_selector);
        for (let code in _preAndEnd) {
            _selector[code] = getMultiSelectorValue(_selector[code], _preAndEnd[code]);
        }
        /**
         * После внесения поправок с приставками и суфиксами в селекторы надо еще раз
         * проверить, нет ли использования встроенных селекторов
         */
        initRecurseValues(_selector);
        return [_selector, _handles];
    }

    /**
     * В данных переданного экземляра класса области компонента сохраняет информацию о событиях
     * и обработчиках событий, которые описаны в параметре handles, так же создавая на основе
     * переданной информации сами обработчики, но сами обработчики не устанавливаются на
     * указанные события у конкретных элементав. Создаваемые обработчики событий не вызывают напрямую
     * методы экземпляра класса, так как тогда в методах экземпляра класса будет потеряна связь с
     * самим экземпляром через специальное свойство this, ведь оно будет указывать на DOM-элемент.
     * Обработчичи станут отдельной функцией, которая будет вызывать нужный метод экземляра, передавая
     * первым аргументом ссылку на DOM-элемент, а, начиная со второго аргумента, другие параметры,
     * которые получает или может получить обработчик события. В обработчиках событий будет добавлен
     * контроль вызова методов экземпляра класса, который не позволит вызываться методу, если сам
     * DOM-элемент, или один из его родительских DOM-элементов будет иметь класс disabled из BASE_CLASSES
     *
     * @param unit - экземляр класса области компонента
     * @param handles - объект, свойства которого называются как события, а значения это название
     * методов экземляра класса области компонента, которые будут обрабатывать эти события
     *
     * @return void
     */
    var createHandles = function(unit, handles) {
        unit._handles = {};
        for (let code in handles) {
            let handleList = {};
            for (let handle in handles[code]) {
                handleList[handle] = function() {
                    if (
                        $(this).hasClass(unit._classList.disabled)
                        || $(this).closest(unit._selector.disabled).length
                    ) return;

                    unit[handles[code][handle]](this, ...arguments);
                };
            }
            unit._handles[code] = handleList;

            /**
             * Все указанные обработчики событий для селекторов будут созданы и сохранены в переменной _handles как
             *      <Символьный код селектора>: {
             *          <название события 1>: <обработчик события 1>,
             *          <название события 2>: <обработчик события 2>,
             *          ...
             *          <название события N>: <обработчик события N>,
             *      }
             * Если по какой-то причине для элементов с конкретным селектором не были назначены обработчики событий,
             * например, элементы не существовали, а могли быть частью какого-то шаблона, из-за чего появились уже в
             * процессе работы, то назначить нужные обработчики событий этим элементам можно
             *      this.initHandles(<символьный код селектора>, this._selector.<символьный код селектора>)
             * или
             *      this.init<символьный код селектора>Handles(<DOM-элемент или строковое значение с селектором>)
             * описание 1го варианта надо смотреть у метода initHandles ниже. Во 2м варианте, если не передавать
             * агрумент <DOM-элемент или ...>, то назначение обработчиков будет аналогично 1му варианту, иначе
             * назначение обработчиков будет для элементов внутри переданного(ых) DOM-элемента(ов) из параметра
             * <DOM-элемент или строковое значение с селектором>. Так же во 2м варианте значение <символьный код селектора>
             * должно начинатсья с заглавной буквы
             */
            let methodName = 'init' + code.replace(/^[a-z]/, word => word.toUpperCase()) + 'Handles';
            if (typeof(unit[methodName]) != 'undefined') continue;

            unit[methodName] = HTMLUnit => {
                let selector = (typeof(HTMLUnit) == 'string') || HTMLUnit?.jquery
                               || (HTMLUnit instanceof HTMLElement)
                             ? $(HTMLUnit).find(unit._selector[code])
                             : unit.select(unit._selector[code]);
                unit.initHandles(code, selector);
            }
        }
    }

    /**
     * Класс, через который в экземпляры класса InfoserviceArea добавляются метод(ы) для
     * конкретного символьного кода из свойства _selector, чтобы сократить написание
     * выполнения какого-то функционала по нужному селектору
     */
    var selectorMethod = (function() {
        return class {
            #unit = false;
            #selectorCode = false;
            #endingCode = false;

            /**
             * Инициализация
             * 
             * @param unit - экземпляр класса InfoserviceArea
             */
            constructor(unit) {
                this.#unit = unit;
            }

            /**
             * Возвращает экземпляр класса InfoserviceArea
             * 
             * @returns InfoserviceArea
             */
            getUnit() {
                return this.#unit;
            }

            /**
             * Устанавливает символьный код, который должен находиться в свойстве _selector
             * у переданного в конструкторе экземпляра класса InfoserviceArea
             * 
             * @param value - значение  символьного кода
             * @returns 
             */
            setSelectorCode(value) {
                this.#selectorCode = value;
                this.#endingCode = value.replace(/^[a-z]/, word => word.toUpperCase());
                return this;
            }

            /**
             * Возвращает символьный код, который был ранее установлен через метод setSelectorCode 
             * 
             * @returns string|false
             */
            getSelectorCode() {
                return this.#selectorCode;
            }

            /**
             * Возвращает символьный код, который используется в окончании названий методов и
             * который был сгенерирован на основе значения, ранее переданное через метод
             * setSelectorCode
             * 
             * @returns string|false
             */
            getEndingCode() {
                return this.#endingCode;
            }

            /**
             * Возвращает значение, полученное через соединение переданного символьного кода
             * приставки и символьного кода, сгенерированного на основе значения, переданного
             * через метод setSelectorCode
             * 
             * @param prefix - символьный код приставки в названии метода
             * @returns 
             */
            getMethodNameWithPrefix(prefix) {
                let methodName = prefix + this.#endingCode;
                return typeof(this.#unit[methodName]) == 'undefined' ? methodName : false;
            }

            /**
             * Для указанного в методе setSelectorCode символьного кода, который есть в свойстве
             * _selector у переданного в конструкторе экземпляра класса InfoserviceArea, создает
             * метод по правилу
             *      select<символьный код, который возвращает метод getEndingCode>
             * чтобы быстрее сократить написание кода для доступа к DOM-элементам.
             * Например, для селектора
             *      someData: '...'
             * будет создан метод
             *      selectSomeData
             * Теперь обращаться к элементам, имеющим css-селектор, скрывающийся под селектором
             * someData, можно не только как
             *      document.querySelectorAll(this._selector.main + ' ' + this._selector.someData)
             *      $(this._selector.main + ' ' + this._selector.someData) // для JQuery
             * но и как
             *      this.select(this._selector.someData)
             * или просто
             *      this.selectSomeData()
             * При этом в 2х последних вариантах допускается дополнительно указывать параметры
             *      - pre. начальное значение селектора перед каждой частью селектора из someData
             *      - end. конечное значение селектора после каждой части селектора из someData
             * Более подробно про pre и end читать у методов select и getMultiSelectorValue
             * 
             * @returns self
             */
            addSelector() {
                let methodName = this.getMethodNameWithPrefix('select');
                if (!methodName) return this;
        
                this.#unit[methodName] = (code => {
                        return function({pre, end} = {pre: '', end: ''}) {
                                    return this.select(this._selector[code], {pre: pre, end: end});
                                }
                    })(this.#selectorCode);
                return this;
            }

            /**
             * Для указанного в методе setSelectorCode символьного кода, который есть в свойстве
             * _selector у переданного в конструкторе экземпляра класса InfoserviceArea, создает
             * методы по правилу
             *      hide<символьный код, который возвращает метод getEndingCode>
             *      unHide<символьный код, который возвращает метод getEndingCode>
             * чтобы быстрее сократить написание кода для СКРЫТИЯ элементов через символьные коды
             * в свойстве _selector и методы hideElement и unHideElement.
             * Например, для селектора
             *      someData: '...'
             * будут созданы методы
             *      hideSomeData
             *      unHideSomeData
             * Теперь, чтобы СКРЫТЬ элементы через этот селектор, можно использовать варианты
             *      document.querySelectorAll(this._selector.main + ' ' + this._selector.someData)
             *              .classList.add(this._classList.hidden)
             * для JQuery
             *      $(this._selector.main + ' ' + this._selector.someData).addClass(this._classList.hidden)
             * 
             * а так же
             *      this.select(this._selector.someData).addClass(this._classList.hidden)
             *      this.hideElement(this._selector.someData)
             * и
             *      this.hideSomeData()
             * 
             * @returns self
             */
            addHiding() {
                let hideMethodName = this.getMethodNameWithPrefix('hide');
                let unHideMethodName = this.getMethodNameWithPrefix('unHide');
                if (!hideMethodName || !unHideMethodName) return this;
        
                this.#unit[hideMethodName] = (code => {
                        return function({pre, end} = {pre: '', end: ''}) {
                                    this.hideElement(this._selector[code], {pre: pre, end: end});
                                }
                    })(this.#selectorCode);
                this.#unit[unHideMethodName] = (code => {
                        return function({pre, end} = {pre: '', end: ''}) {
                                    this.unHideElement(this._selector[code], {pre: pre, end: end});
                                }
                    })(this.#selectorCode);
                return this;
            }

            /**
             * Для указанного в методе setSelectorCode символьного кода, который есть в свойстве
             * _selector у переданного в конструкторе экземпляра класса InfoserviceArea, создает
             * методы по правилу
             *      freeze<символьный код, который возвращает метод getEndingCode>
             *      unFreeze<символьный код, который возвращает метод getEndingCode>
             * чтобы быстрее сократить написание кода для БЛОКИРОВАНИЯ элементов через символьные
             * коды в свойстве _selector и методы freezeElement и unFreezeElement.
             * Например, для селектора
             *      someData: '...'
             * будут созданы методы
             *      freezeSomeData
             *      unFreezeSomeData
             * Теперь, чтобы ЗАБЛОКИРОВАТЬ элементы через этот селектор, можно использовать варианты
             *      document.querySelectorAll(this._selector.main + ' ' + this._selector.someData)
             *              .classList.add(this._classList.noReaction)
             * для JQuery
             *      $(this._selector.main + ' ' + this._selector.someData).addClass(this._classList.noReaction)
             * 
             * а так же
             *      this.select(this._selector.someData).addClass(this._classList.noReaction)
             *      this.freezeElement(this._selector.someData)
             * и
             *      this.freezeSomeData()
             * 
             * @returns self
             */
            addFreezing() {
                let freezeMethodName = this.getMethodNameWithPrefix('freeze');
                let unFreezeMethodName = this.getMethodNameWithPrefix('unFreeze');
                if (!freezeMethodName || !unFreezeMethodName) return this;

                this.#unit[freezeMethodName] = (code => {
                        return function({pre, end} = {pre: '', end: ''}) {
                                    this.freezeElement(this._selector[code], {pre: pre, end: end})
                                }
                    })(this.#selectorCode);
                this.#unit[unFreezeMethodName] = (code => {
                        return function({pre, end} = {pre: '', end: ''}) {
                                    this.unFreezeElement(this._selector[code], {pre: pre, end: end})
                                }
                    })(this.#selectorCode);
                return this;
            }
        }
    })();

    /**
     * По полученному описанию селектора сохраняет в экземпляре класса области компонента указанные
     * значения символьных кодов и css-селекторов, параллельно запоминая обработчики событий для
     * тех селекторов, где они указаны. Описание селектора должно быть оформлено следующим образом
     *      {
     *          ...
     *          <символьный код селектора-N>: <значение>
     *          ...
     *      }
     * где
     *      <значение> - значение, описание которого приведено в описании метода
     *                   getSeparatedSelectorsAndHandlers, где в описании значения указано, что
     *                   можно использовать объект, одним из параметров которых является
     *            handles - объект со списком обработчиков, описывается так
     *                  <название события>: '<название метода класса, который будет обработчиком>'
     * 
     *      <название метода класса, который будет обработчиком> - это объявленный внутри объекта метод,
     *      которому в виде аргументов передадутся сначала указатель на DOM-элемент, для которого сработало
     *      событие, а потом, начиная со второго аргумента, все остальные параметры, которые были переданы
     *      событию. Значение this внутри этих обработчиков указывает на экземпляр класса области компонента,
     *      а не на объект, у которого сработало событие
     * 
     *      Например, был создан объект
     *          var someObject = {
     *              done() {
     *                  ...
     *              }
     *          }
     *      Далее для него был создан экземпляр класса InfoserviceArea с описанием селекторов, где указан
     *      обработчик для какого-то DOM-элемента
     *          new InfoserviceArea(
     *              someObject,
     *              {
     *                  selector: {
     *                      main: '.some-area',
     *                      someButton: {
     *                          self: '.some-button',
     *                          handles: {
     *                              click: 'done'
     *                          }
     *                      }
     *                  }
     *              }
     *          )
     *      Теперь при нажатии по DOM-элементу с селектором .some-button будет создано событие onclick, и
     *      его обработка уйдет методу done внутри объекта someObject
     * 
     *      В <значении> селектора на месте css-селектора можно использовать ссылки на значения других
     *      css-селекторов через указание их <символьных кодов> в фигруных скобках. Более подробно
     *      описано у метода initRecurseValues
     *
     * @param unit - экземпляр класса области компонента
     * @param Object selector - список селекторов для HTML-тегов по правилам css, указываются так
     *      <символьный код селектора>: <значение>
     *
     * @return void
     */
    var initSelector = function(unit, selector) {
        var _selector = {}, _handles = {};
        if (selector instanceof Object)
            [_selector, _handles] = getSeparatedSelectorsAndHandlers(selector);

        var class_selector = {};
        for (let code in unit._classList) {
            class_selector[code] = '.' + unit._classList[code];
        }
        unit._selector = {...class_selector, ..._selector};
        
        /**
         * Для каждого селектора создается методы для сокращенного обращения к нему в зависимости
         * от целей, для этого используется выше написанный класс selectorMethod
         */
        let selectorName = new selectorMethod(unit);
        for (let code in unit._selector) {
            selectorName.setSelectorCode(code).addSelector();
            if (BASE_CLASSES[code] != BASE_CLASSES.hidden) selectorName.addHiding();
            if (BASE_CLASSES[code] != BASE_CLASSES.noReaction) selectorName.addFreezing();
        }

        createHandles(unit, _handles);
    }

    /**
     * Для конкретного метода у экземпляра класса области компонента создает и ругистрирует
     * обработчики ГЛОБАЛЬНЫХ (т.е для объекта document) событий.
     *
     * @param unit - экземпляр класса области компонента
     * @param methodName - название метода
     * @param handles - либо объект, где указаны
     *          {
     *              <название события>: <название метода>,
     *              ...
     *          }
     *      либо булевское значение, при равенстве которого значению true идет поиск
     *      названия события через проверку регулярным выражение в параметре
     *          regExValue
     * 
     * @param regExValue - регулярное выражение, необходимое для проверки названия
     * метода из параметра
     *      methodName
     * когда в параметре
     *      handles
     * указано значение true. Проверка укажет, подходит ли метод для обработчика
     * какого-то события. Если проверка прошла, то при условии, что
     *      - в регулярном выражении не используется ГРУППИРОВКА, значит, часть
     *        названия метода, что подошла для регулярного выражения, станет считаться
     *        названием события;
     *      - в регулярном выражении используется ГРУППИРОВКА, значит, та часть, что
     *        стала выделена 1й группой регулярного выражения, станет названием
     *        события.
     *
     * @return void
     */
    var initDocumentEventHandle = function(unit, methodName, handles, regExValue) {
        var globalEventNames = [];
        if (handles === true) {
            let eventNameChecking = methodName.match(regExValue)
            if (!eventNameChecking) return;

            globalEventNames.push(eventNameChecking[1] ? eventNameChecking[1] : eventNameChecking[0]);

        } else {
            let eventNames = Object.keys(handles);
            Object.values(handles).forEach((eventMethodName, number) => {
                if (eventMethodName != methodName) return;

                globalEventNames.push(eventNames[number]);
            });
        }

        globalEventNames.forEach(eventName => {
            $(document).on(eventName, function() {
                unit[methodName](...arguments);
            });
        });
    }

    /**
     * Перебирает все методы экземпляра класса области компонента и передает название каждого
     * функции
     *      initDocumentEventHandle
     * вместе с параметрами
     *      handles
     *      regExValue
     * в которых описано, какой метод считать обработчиком и для какого события
     * 
     * @param unit - экземпляр класса области компонента
     * @param handles - либо объект, либо булевское значение. Более подробно описано у функции
     *          initDocumentEventHandle
     * 
     * @param regExValue - регулярное выражение, необходимое для проверки названия
     * метода из параметра. Более подробно описано у функции
     *          initDocumentEventHandle
     * 
     * @return void
     */
    var initDocumentEventHandles = function(unit, handles, regExValue) {
        let methodNames = [];
        let initHandle = methodName => {
            if (
                (typeof(unit[methodName]) != 'function')
                || (methodNames.indexOf(methodName) >= 0)
            ) return;

            methodNames.push(methodName);

            initDocumentEventHandle(unit, methodName, handles, regExValue);
        }

        Object.keys(unit).forEach(methodName => initHandle(methodName));

        for (let classBase = unit.constructor; classBase.prototype; classBase = Object.getPrototypeOf(classBase)) {
            Object.getOwnPropertyNames(classBase.prototype).forEach(methodName => initHandle(methodName));
        }
    }

    /**
     * Проходится по свойствам переданного объекта и возвращает по-одному
     *      [<специальное название свойтсва>: <значение свойства, не являющееся объектом>]
     * где
     *      <специальное название свойтсва> - это значение, составленное из названий свойств
     *      переданного объекта и всех названий свойств внутренных объектов, соединенных символом
     *      разделителем, начиная от каждого свойства переданного объекта и затем добавленными
     *      названиями свойств вложенных объектов
     * 
     * Например, есть описанный объект
     *      {
     *          propertyA: 'ss',
     *          propertyB: {
     *              propertyBA: 0,
     *              ':propertyBB': '111,
     *              propertyBC: {
     *                  propertyBCA: 'test',
     *                  ':propertyBCB': 1000
     *              }
     *          }
     *      }
     * 
     * при передаче его методу будут возвращаться данные
     *      ['propertyA', 'ss']
     *      ['propertyB.propertyBA', 0]
     *      ['propertyB:propertyBB', 111]
     *      ['propertyB.propertyBC.propertyBCA', 'test']
     *      ['propertyB.propertyBC:propertyBCB', 1000]
     *
     * @param obj - некий объект
     * @param start - стартовое значение, которое будет указываться в начале каждого
     *      <специальное название свойтсва>
     * 
     * @param defaultSymbol - если название свойства внутреннего объекта не начинается с символов,
     * что указаны в параметре 
     *      joiningSymbols
     * то при соединении будет использоваться этот символ
     * 
     * @param joiningSymbols - список специальных символов, с которых может начинаться название
     * свойства внутреннего объекта. Если такое свойство будет найдено, то оно будет добавлено
     * в очердной
     *      <специальное название свойтсва>
     * без использования значения из параметра
     *      defaultSymbol
     *
     * @yield Array
     */
    function *objectPropertyPath(obj, start, defaultSymbol = '.', joiningSymbols = ['.', ':']) {
        let realStart = typeof(start) == 'string' ? start : '';

        if (obj instanceof Object) {
            for (let code in obj) {
                let codeStart = realStart;
                if (joiningSymbols.indexOf(code[0]) < 0) codeStart += defaultSymbol;

                codeStart += code;
                if (obj[code] instanceof Object) {
                    yield* objectPropertyPath(obj[code], codeStart);

                } else {
                    yield [codeStart, obj[code].toString()];
                }
            }
            
        } else {
            yield [realStart, obj];
        }
    }

    /**
     * Для экземпляра области компонента по описанию ГЛОБАЛЬНЫХ обработчиков событий
     * из параметра
     *      handles
     * создает и регистрирует обработчики событий. Описание выглядит как:
     *      {
     *          <категория обработчиков событий 1>: <описание событий>
     *          <категория обработчиков событий 2>: <описание событий>
     *          ...
     *          <категория обработчиков событий N>: <описание событий>
     *      }
     * 
     * В параметре handles по-умолчанию поддерживается категория
     *      document
     * для ГЛОБАЛЬНОГО объекта document, но со своими правилами:
     *      1. Если категория document равна true, то будет вызывана функция
     *          initDocumentEventHandles
     *         которой будут переданы:
     *              - экземпляр области компонента;
     *              - регулярное выражение для проверки на то, какие методы подходят
     *                для обработчиков событий. В этом случае оно будет равно
     *                  ^on(\w+)$
     *                т.е. метод экземпляра области компонента будет считаться
     *                обработчиком события, если название метода начинается с приставки
     *                  on
     *                а часть, идущая после этой приставки, будет считаться названием
     *                события;
     *      2. Если категория document равна какому-то регулярному выражению, то будет
     *         поведение аналогичное, как в правиле 1, только будет использоваться
     *         указанное регулярное выражение. Более подробно, как использовать регулярное
     *         выражение описано у метода initDocumentEventHandle;
     *      3. Категория document может быть представлена как объект, где
     *              <название события>: <обрабочик события>
     * 
     * Остальные категории описываются как (пример для события с названием из 3х частей)
     *      'categoryA.categoryAA.eventNameA': <название метода-обработчка события>
     * или
     *      'categoryA.categoryAA:eventNameA': <название метода-обработчка события>
     * или
     *      categoryA: {'categoryAA:eventNameA': <название метода-обработчка события>}
     * или
     *      categoryA: {
     *          categoryAA: {
     *              'eventNameA': <название метода-обработчка события 1>,
     *              ':eventNameB': <название метода-обработчка события 2>
     *          }
     *      }
     * 
     * НО! Чтобы события других категорий были приняты, надо создать метод
     *       InfoserviceArea.init<название самой первой части события>EventHandles
     * 
     * Этому методу будут передаваться параметры:
     *      - экземпляр области компонента;
     *      - название события, в котором будут склеены все части события, если оно
     *        было описано, как объект;
     *      - название метода у экземпляра области компонента;
     * 
     * и в самом методе решать, как и через что регистрировать событие. Если первую
     * часть события указать с каким-нибудь небуквенным и нечисловым символом в
     * начале названия, то полное название события будет передаваться без первой
     * части
     * 
     * Например:
     *      'categoryA.categoryAA': '<обработчик события 1>'
     *      '*categoryB:categoryBA': '<обработчик события 2>'
     *      'categoryC:categoryCA': '<обработчик события 3>'
     *      categoryD: {
     *          categoryDA: '<обработчик события 4>'
     *          categoryDB: '<обработчик события 5>'
     *      }
     *      *categoryE: {
     *          ':categoryEA': '<обработчик события 6>'
     *          categoryEB: '<обработчик события 7>'
     *      }
     * будут переданы названия событий
     *      categoryA.categoryAA
     *      :categoryBA
     *      categoryC:categoryCA
     *      categoryD:categoryDA
     *      categoryD:categoryDB
     *      :categoryEA
     *      categoryEB
     * 
     * @param unit - экземпляр области компонента
     * @param handles - список категорий событий
     *
     * @return void
     */
    var initEventHandleCategories = function(unit, handles) {
        if (!(handles instanceof Object)) return;

        if (typeof(handles.document) != 'undefined') {
            if (handles.document instanceof RegExp) {
                initDocumentEventHandles(unit, true, handles.document);

            } else if ((handles.document === true) || (handles.document instanceof Object)) {
                initDocumentEventHandles(unit, handles.document, /^on(\w+)$/i);
            }
        }
        Object.keys(handles).forEach(category => {
            if (category.toLowerCase() == 'document') return;
            
            let eventCategoryIniter = 'init' + category.replace(/^\W*/, '').replace(/\W[\W\w]*$/, '') + 'EventHandles';
            if (typeof(InfoserviceArea[eventCategoryIniter]) != 'function') return;
            
            for (let path of objectPropertyPath(handles[category], category)) {
                let [eventName, methodName] = path;
                if (typeof(unit[methodName]) != 'function') continue;

                InfoserviceArea[eventCategoryIniter](unit, eventName.replace(/^\W+\w+[^:\w]*/, ''), methodName);
            }
        });
    }

    /**
     * Этот метод вызывается после инициализации основных параметров экзепляра класса компонента:
     *      - копирование данных объекта;
     *      - запоминание переданного списка значений классов и их символьных кодов, через которые
     *        желательно обращение к значениям этих классов;
     *      - обработка переданного списка селекторов с описанием обработчиков событий для некоторых из
     *        них, НО без назначения созданных обработчиков событий DOM-элементам, которым эти обработчики
     *        предназначены;
     *      - созданние обработчиков событий объекта document и обработчиков ГЛОБАЛЬНЫХ событий других
     *        категорий.
     * 
     * Далее идет
     *      - вызыв метода finishInitialization у экземпляра, где происходит выполнение финальных шагов
     *        по инициализации экзепляра;
     *      - генерируется событие infoservicearea:ready, сообщающее, что экземпляр создан и готов к работе,
     *        в параметрах события передается ссылка на сам экземпляр;
     *      - ЕСЛИ экземпляр сохранен в свойстве глобального объекта window, т.е.
     *          * либо указан как
     *              window.<название переменной>;
     *          * либо указан как
     *              var <название переменной>
     *            в глобальном контексте;
     *        ТО так же генерируется событие
     *              infoservicearea:ready:<название переменной>
     *        сообщающее, что экземпляр в конкретной переменной (свойстве window) создан и готов к работе,
     *        в параметрах события передается ссылка на сам экземпляр;
     * 
     * @param unit - экземпляр области компонента
     * @return void
     */
    var initArea = function(unit) {
        unit.finishInitialization();
        document.dispatchEvent(new CustomEvent('infoservicearea:ready', {detail: {unit: unit}}));

        let windowProperties = Object.getOwnPropertyNames(window);
        for (let propetyNumber = 0; propetyNumber < windowProperties.length; ++propetyNumber) {
            if (window[windowProperties[propetyNumber]] !== unit) continue;

            var event = new CustomEvent('infoservicearea:ready:' + windowProperties[propetyNumber].toLowerCase(), {detail: {unit: unit}});
            document.dispatchEvent(event);
            break;
        }
    }

    /**
     * Добавляет стили, предначначенные для некоторых классов из константы BASE_CLASSES
     *
     * @return void
     */
    var addStyles = function() {
        var styleUnit = document.createElement('style');
        styleUnit.type = 'text/css';

        styleUnit.innerText = '.' + BASE_CLASSES.hidden + ' { display: none !important; } '
                            + '.' + BASE_CLASSES.noReaction + ' { pointer-events: none !important; } ';
        document.body.appendChild(styleUnit);
    }

    /**
     * Обработчик события ГОВНОСТИ страницы, вызывает метод initArea для тех экземпляров класса области
     * компонента, которые начали свою инициализацию до окончания создания объекта document.body
     *
     * @return void
     */
    var initWaitingAreas = function() {
        waitingAreas.forEach(areaUnit => initArea(areaUnit));
        waitingAreas = [];
    }

    if (document.body) {
        addStyles();
        
    } else {
        window.addEventListener('DOMContentLoaded', () => {
            addStyles();
            initWaitingAreas();
        });
    }

    return class {
        _id = 0;
        _classList = {};
        _selector = {};
        _handles = {};

        /**
         * Служит для сохранения информации о том, какому DOM-элементу уже были установлены
         * указанные при инициализации экземпляра класса InfoserviceArea обработчики конкретных
         * событий, чтобы при повторной установке этих обработчиков для новых DOM-элементов с
         * тем же селектором не устанавливать их снова тем DOM-элементам, у которых они уже
         * имеются
         */
        #eventHandlesDOMUnits = null;

        /**
         * Для списка DOM-элементов, переданного в параметре objects как список с конкретными элементами
         * или как строковое значение, под которым находится css-селектор на конкретные элементы, устаналивает
         * обработчики событий, сохраненные под конкретным символьным кодом, который использовался в параметре
         * selector при инициализации экземпляра класса для указания на конкретные элемент(ы) и под которым и
         * были указаны названия методов класса для обработки конкретных событий
         *
         * @param selectorCode - символьным код, который использовался в параметре selector при инициализации
         * экземпляра класса для указания на конкретные элемент(ы) и под которым были указаны названия методов
         * класса для обработки конкретных событий
         * @param objects - список DOM-элементов или селектор на получения этого списка DOM-элементов
         *
         * @return void
         */
        initHandles(selectorCode, objects) {
            if (this.#eventHandlesDOMUnits === null) this.#eventHandlesDOMUnits = new WeakMap();
            $(objects).each((number, unit) => {
                let handles = this.#eventHandlesDOMUnits.get(unit) || [];
                if (handles.indexOf(selectorCode) > -1) return;

                handles.push(selectorCode);
                Object.keys(this._handles[selectorCode]).forEach(code => $(unit).on(code, this._handles[selectorCode][code]));
                this.#eventHandlesDOMUnits.set(unit, handles);
            });
        }

        /**
         * Внутри переданного методу элемента(ов) ищет все элементы, селекторы на которые были
         * переданы в конструкторе через параметр selector, и устанавливает обработчики событий,
         * которые так же были указаны через параметр selector
         *
         * @param HTMLUnit - строковое значение с CSS-селектором, JQuery-объект или DOM-элемент
         *
         * @return void
         */
        initHandlesAtArea(HTMLUnit) {
            let selector = (typeof(HTMLUnit) == 'string') || HTMLUnit?.jquery
                           || (HTMLUnit instanceof HTMLElement)
                         ? HTMLUnit : this._selector.main;
            if (!selector) return;
            
            for (let selectorCode in this._handles) {
                this.initHandles(selectorCode, $(selector).find(this._selector[selectorCode]));
            }
        }

        /**
         * Метод, который при инициализации экземпляра класса вызывается последним после обработки переданных
         * селекторов и списка значений специальных классов, а так же регистрации обработчиков событий для
         * document и других ГЛОБАЛЬНЫХ событий. В методе идет установка созданных ранее обработчиков событий
         * для элементов, на которые при инициализации экземпляры были переданы селекторы
         *
         * @return void
         */
        finishInitialization() {
            Object.keys(this._handles).forEach(code => {
                this.initHandles(code, this._selector[code]);
            });
            if (typeof(this['_init_']) == 'function') {
                this._init_();
                delete this._init_;
            }
        }

        /**
         * Инициализация для работы с функционалом конкретной области компонента
         * Параметры передаются как объекты:
         *      - первый параметр хранит в себе методы и поля, которые используются для работы
         *        в конкретной DOM-области;
         *      - второй параметр хранит в себе описание, какие селекторы используются, какие
         *        HTML-классы используются и какие обработчики нужны для ГЛОБАЛЬНЫХ событий
         * При инициализации идут вызовы
         *      initBehavior - копирование данных первого параметра в экземпляр класса, включая и
         *                     методы;
         *      initClassList - обработка из 2го параметра поля classList с описанием специальных
         *                      классов (classList);
         *      initSelector - обработка из 2го параметра поля для описания селекторов (selector)
         *                     и создания обработчиков событий для конкретных селекторов;
         *      initEventHandleCategories - регистрация обработчиков ГЛОБАЛЬНЫХ событий для
         *                     всего документа или какой-то конкретной категории ГЛОБАЛЬНЫХ событий;
         *      initArea - вызов метода finishInitialization и генерации события infoservicearea:ready
         *                 о готовности экземпляра. Вызывается сразу, если событие о ГОТОВНОСТИ
         *                 СТРАНИЦЫ (document READY) уже прошло, иначе вызов метода будет прикреплен к
         *                 обработчику события ГОТОВНОСТИ СТРАНИЦЫ
         * 
         * @param Object behavior - параметр указывается всегда первым, не выделяется фигурными скобками
         * с указанием какого-то ключа, т.е.
         *      new initEventHandleCategories({<описание behavior>})
         * 
         * хранит описание обычных полей и методов для реализации конкретного поведения (фукционала),
         * в том числе обработчиков для событий конкретных элементов и ГЛОБАЛЬНЫХ событий других категорий.
         * Среди объявленных методов особую роль играют методы:
         *      _init_ - аналог конструктора. Этот метод будет вызван в конце инициализации экземпляра класса
         * 
         * @param Object classList - список специальных значений для аттрибута class у HTML-тегов, указываются как
         *      <символьный код класса>: '<значение без специальных пометок вроде точек, как принято в css>'
         * 
         * к указанному списку так же будут добавлены значения из константы BASE_CLASSES, для некоторых из
         * которых в текущем классе есть методы. Передается как
         *      new initEventHandleCategories({<описание behavior>}, {classList: {<описание classList>}})
         * 
         * @param Object selector - список селекторов для HTML-тегов по правилам css, более подобно смотреть в
         * описании метода initSelector. Передается как
         *      new initEventHandleCategories({<описание behavior>}, {selector: {<описание selector>}})
         *
         * @param Object handles - описание ГЛОБАЛЬНЫХ обработчиков событий. Более подробно описано у метода
         * initEventHandleCategories. Передается как
         *      new initEventHandleCategories({<описание behavior>}, {handles: {<описание handles>}})
         * 
         * @return void
         */
        constructor(behavior, {classList, selector, handles} = {}) {
            this._id = ++lastIDValue;
            initBehavior(this, behavior);
            initClassList(this, classList);
            initSelector(this, selector);
            initEventHandleCategories(this, handles);
            if (document.body) {
                /**
                 * Чтобы была возможность заменить метод finishInitialization в дочерних классах, так как пока работает конструктор
                 * родительского класса любые инициализации свойств экземпляров дочерних классов будут бесполезны
                 */
                setTimeout(() => initArea(this), 1);

            } else {
                waitingAreas.push(this);
            }
        }

        /**
         * Возвращает JQuery-элемент, который указывает на DOM-элемент(ы) по переданному при вызове метода
         * значению селектора с поправками из агрумента pre или end или значением, которое при инициализации
         * экземпляра класса было указано в агрументе selector под символьным кодом main.
         * Если при инициализации экземпляра класса был указан селектор с символьным кодом main или при вызове
         * этого метода передан(ы) аргумент(ы) pre и/или end, то значение агрумента selector поправляется так,
         * чтобы у всех частей этого значения, раздленных друг от друга символом "запятая", в начале будет
         * добавлено значение из pre или из селектора с символьным кодом main, а в конце будет добавлено
         * значение из end.
         * Например, получить элементы по селектору
         *      .somearea input
         * можно либо как
         *      $('.somearea input')
         * либо как
         *      this.select('.somearea input') // если нет селектора с символьным кодом main
         * либо как
         *      this.select('input', {pre: '.somearea '})
         * либо как
         *      this.select('input')
         * в последнем варианте значение '.somearea' должно быть указано при инициализации экземпляра класса
         * у аргумента
         *      selector
         * под символьным кодом main
         * Вариант с использованием pre не выглядит удобнее обычного способа, но pre можно использовать
         * в случаях, когда надо упростить код, например
         *      $('.somearea input, .somearea textarea')
         * или
         *      $(this._selector.main + ' input,' + this._selector.main + ' textarea')
         * можно заменить на
         *      this.select('input, textarea', {pre: '.somearea '})
         * или
         *      this.select('input, textarea') // если .somearea  указан в selector под символьным кодом main
         * Аргумент end нужен для случаев вроде
         *      $('input[name="first_name"], textarea[name="first_name"]')
         * где можно просто записать
         *      this.select('input, textarea', {pre: '', end: '[name="first_name"]'})
         * или
         *      this.select('input, textarea', {end: '[name="first_name"]'})
         * если у экземпляра класса нет селектора с символьным кодом main
         *
         * @param selector - селектор к какому-нибудь элементу класса
         * @param pre - начальное значение каждой части значения из агрумента selector
         * @param end - конечное значение каждой части значения из агрумента selector
         *
         * @return JQuery
         */
        select(selector, {pre, end} = {pre: '', end: ''}) {
            return $(
                typeof(selector) == 'string'
                ? getMultiSelectorValue(
                        selector.trim(),
                        {
                            preValue: pre || ((this._selector.main || '') + ' ').trimLeft(),
                            endValue: end,
                            callBack: parts => {
                                if (parts.indexOf(this._selector.main) >= 0)
                                    return this._selector.main;
                            }
                        }
                    )
                : selector
            );
        }

        /**
         * По переданному селектору получает DOM-объект и устанавливает у него из BASE_CLASSES значение
         * класса hidden для скрытия этого DOM-объекта
         *
         * @param selector - селектор к какому-нибудь элементу класса
         * @param pre - начальное значение каждой части значения из агрумента selector
         * @param end - конечное значение каждой части значения из агрумента selector
         *
         * @return void
         */
        hideElement(selector, {pre, end} = {pre: '', end: ''}) {
            this.select(selector, {pre: pre, end: end}).addClass(this._classList.hidden);
        }

        /**
         * По переданному селектору получает DOM-объект и удаляет у него значение класса под hidden,
         * определенное в константе BASE_CLASSES и служащее для скрытия DOM-объекта
         *
         * @param selector - селектор к какому-нибудь элементу класса
         * @param pre - начальное значение каждой части значения из агрумента selector
         * @param end - конечное значение каждой части значения из агрумента selector
         *
         * @return void
         */
        unHideElement(selector, {pre, end} = {pre: '', end: ''}) {
            this.select(selector, {pre: pre, end: end}).removeClass(this._classList.hidden);
        }

        /**
         * По переданному селектору получает DOM-объект и устанавливает у него из BASE_CLASSES
         * значение класса под noReaction для отсутствия реакции у этого DOM-объекта
         *
         * @param selector - селектор к какому-нибудь элементу класса
         * @param pre - начальное значение каждой части значения из агрумента selector
         * @param end - конечное значение каждой части значения из агрумента selector
         *
         * @return void
         */
        freezeElement(selector, {pre, end} = {pre: '', end: ''}) {
            this.select(selector, {pre: pre, end: end}).addClass(this._classList.noReaction);
        }

        /**
         * По переданному селектору получает DOM-объект и удаляет у него значение класса под noReaction,
         * определенное в константе BASE_CLASSES и служащее для установки отсутствия реакции у этого
         * DOM-объекта
         *
         * @param selector - селектор к какому-нибудь элементу класса
         * @param pre - начальное значение каждой части значения из агрумента selector
         * @param end - конечное значение каждой части значения из агрумента selector
         *
         * @return void
         */
        unFreezeElement(selector, {pre, end} = {pre: '', end: ''}) {
            this.select(selector, {pre: pre, end: end}).removeClass(this._classList.noReaction);
        }

        /**
         * Если при инициализации экземпляра класса в параметре selector было указано поле
         * с символьным кодом main, которое, желательно, указывает на область, за которую
         * отвечает экземпляр, то DOM-объекту с классом из значения под символьным кодом
         * main устанавливается значение класса под hidden, определенное в константе
         * BASE_CLASSES
         *
         * @return void
         */
        hide() {
            if (typeof(this._selector.main) != 'string') return;

            this.hideElement(this._selector.main);
        }

        /**
         * Если при инициализации экземпляра класса в параметре selector было указано поле
         * с символьным кодом main, которое, желательно, указывает на область, за которую
         * отвечает экземпляр, то у DOM-объекта с классом как значение под символьным кодом
         * main удаляется класс со значением под hidden, определенное в константе BASE_CLASSES
         *
         * @return void
         */
        unHide() {
            if (typeof(this._selector.main) != 'string') return;

            this.unHideElement(this._selector.main);
        }

        /**
         * Если при инициализации экземпляра класса в параметре selector было указано поле
         * с символьным кодом main, которое, желательно, указывает на область, за которую
         * отвечает экземпляр, то DOM-объекту с классом из значения под символьным кодом
         * main устанавливается значение класса под noReaction, определенное в константе
         * BASE_CLASSES
         *
         * @return void
         */
        freeze() {
            if (typeof(this._selector.main) != 'string') return;

            this.freezeElement(this._selector.main);
        }

        /**
         * Если при инициализации экземпляра класса в параметре selector было указано поле
         * с символьным кодом main, которое, желательно, указывает на область, за которую
         * отвечает экземпляр, то у DOM-объекта с классом как значение под символьным кодом
         * main удаляется класс со значением под noReaction, определенное в константе
         * BASE_CLASSES
         *
         * @return void
         */
        unFreeze() {
            if (typeof(this._selector.main) != 'string') return;

            this.unFreezeElement(this._selector.main);
        }
    };
})();