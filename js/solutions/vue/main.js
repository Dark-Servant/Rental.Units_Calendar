;document.addEventListener('infoservicearea:ready:infoservicecalendar', function() {
   
    /**
     * Основной метод приложения, с которого начинается работа в нем
     * 
     * @return void
     */
    window.InfoserviceCalendar.showApplication = function(startLoadedData) {
        let data = initData(startLoadedData);

        this.mainArea = new Vue(Object.assign(
                                calendarImplementation,
                                {
                                    el: this._selector.calendar,
                                    data: data
                                }
                            ));
        var event = new CustomEvent('infoservicecalendar:started', {detail: {unit: this}});
        document.dispatchEvent(event);
    }

    /**
     * 
     * @param {*} propertyCode 
     * @param {*} data 
     * @param {*} technicIndex 
     */
    window.InfoserviceCalendar.updateTechnicPeriodPropertyFromDataByIndex = function(propertyCode, data, technicIndex) {
        if (data[propertyCode] instanceof Object) {
            if (typeof(this.mainArea.technics[technicIndex]) == 'undefined') return;
            if (typeof(this.mainArea.technics[technicIndex][propertyCode]) == 'undefined')
                Vue.set(this.mainArea.technics[technicIndex], propertyCode, {});

            Object.keys(data[propertyCode]).forEach(dayTimestamp => {
                Vue.set(this.mainArea.technics[technicIndex][propertyCode], dayTimestamp, data[propertyCode][dayTimestamp]);
            });
            
        } else {
            Vue.del(this.mainArea.technics[technicIndex], propertyCode);
        }
    }

    /**
     * 
     * @param {*} periodData 
     */
    window.InfoserviceCalendar.updateCommentsAtModalByTechnicPeriodData = function(periodData) {
        let technicIndex = this.mainArea.contentDetail.TECHNIC_INDEX;
        this.updateTechnicPeriodPropertyFromDataByIndex('COMMENTS', periodData, technicIndex);
        this.updateTechnicPeriodPropertyFromDataByIndex('CONTENTS', periodData, technicIndex);

        if (periodData.COMMENTS instanceof Object) {
            Vue.set(this.mainArea.contentDetail, 'COMMENTS', periodData.COMMENTS[this.mainArea.contentDetail.CONTENT_DAY]);

        } else {
            Vue.del(this.mainArea.contentDetail, 'COMMENTS');
        }
    }

    /**
     * 
     * @returns 
     */
    let initData = function(startLoadedData) {
        let noFreeActivities = InfoserviceBizProcActivity.freeActivityCodes.length < 1;
        let result = {
            calendarShow: noFreeActivities,
            activityInstalled: noFreeActivities,
            userData: InfoserviceCalendar.userData,
            bx24inited: BX24_IS_INITED
        };
        if (noFreeActivities) {
            result.activities = InfoserviceBizProcActivity.activities;

        } else {
            result.activities = {};
            InfoserviceBizProcActivity.freeActivityCodes.forEach(code => {
                result.activities[code] = InfoserviceBizProcActivity.activities[code];
            });
        }
        Object.assign(result, {
            calendarDate: new Date(Object.keys(startLoadedData.days)[0] * 1000),
            ...startLoadedData,
            contentDetail: null,
            newCommentDealIndex: false,
            editCommentIndex: false,
            copyCommentIndex: false,
            hintShowingData: false,
            quarterNumber: 0,
            quarterContent: null
        });
        return result;
    }

    /**
     * 
     */
    let calendarImplementation = {

        watch: {
            /**
             * Следит за изменением переменной contentDetail. Когда она инициализирована как объект, то
             * появляется модальное окно с информацией о контенте, иначе окно исчезает. В случае сброса
             * переменной contentDetail этот метод сбрасывает значение в переменных, наличие в которых
             * значения отличного от false приводит к открытию поля ввода комментария
             * 
             * @return void
             */
            contentDetail() {
                if (this.contentDetail) {
                    this.hideHintWindow();
                    InfoserviceCalendar.showContentDetailModal();
    
                    var commentIds = [];
                    if (this.contentDetail.COMMENTS)
                        commentIds = this.contentDetail.COMMENTS.map(comment => comment.ID);
    
                    (new InfoserviceAjax('readcomments')).sendPOST({
                            commentIDs: commentIds,
                            user: InfoserviceCalendar.userData
                        }).then(answer => {
                            if (!answer.result) return;
    
                            this.contentDetail.COMMENTS.forEach(comment => comment.READ = true );
                        }
                    );
    
                } else {            
                    this.newCommentDealIndex = 
                    this.copyCommentIndex = 
                    this.editCommentIndex = false;
                }
            },
    
            /**
             * Следит за изменением переменной newCommentDealIndex, наличие в которой значения отличного
             * от false приводит к появлению поля добавления комментария
             * 
             * @return void
             */
            newCommentDealIndex() {
                if (this.contentDetail) InfoserviceCalendar.showContentDetailModal();
                if (this.newCommentDealIndex === false) return;
    
                this.copyCommentIndex = false;
            },
    
            /**
             * Следит за изменением переменной editCommentIndex, наличие в которой значения отличного
             * от false приводит к появлению поля редактирования комментария
             * 
             * @return void
             */
            editCommentIndex() {
                if (this.contentDetail) InfoserviceCalendar.showContentDetailModal();
                if (this.editCommentIndex === false) return;
    
                this.copyCommentIndex = false;
            },
    
            /**
             * Следит за изменением переменной copyCommentIndex, наличие в которой значения отличного
             * от false приводит к появлению модального окна для копирования комментария
             * 
             * @return void
             */
            copyCommentIndex() {
                InfoserviceCalendar.showContentDetailModal();
                if (this.copyCommentIndex === false) return;
    
                this.newCommentDealIndex =
                this.editCommentIndex = false;
            },
    
            /**
             * Следит за изменением значения переменной quarterNumber. Если значение изменилось на нуль,
             * то вызывает таблицу просмотра контента в течении недели
             * 
             * @return void
             */
            quarterNumber() {
                this.technics = [];
                if (this.quarterNumber) return;
    
                this.days = []; 
                this.showTable();
            },
    
            /**
             * Следит за изменением значения переменной calendarDate, вызывает метод showData для вывода
             * данных согласно установленной в этой переменной дате
             * 
             * @return void
             */
            calendarDate() {
                if (this.filterDateInput)
                    this.filterDateInput.setDate(this.calendarDate);
    
                setTimeout(() => this.showData(), 1);
            }
        },
        computed: {
    
            /**
             * Возвращает массив с техникой, где идут сначала вся техника и партнеры, отмеченные
             * как избранные, а потом обычные, т.е. перемещает все отмеченное как избранное вперед
             * списка
             * 
             * @return array
             */
            sortedTechnics() {
                var technics = this.technics.map((technic, index) => {
                                    return {index: index, ...technic};
                                });
                var result = technics.filter(technic => technic.IS_CHOSEN);
                technics.forEach(technic => {
                    if (!technic.IS_CHOSEN) result.push(technic);
                });
                return result;
            },
        },
    
        /**
         * Срабатывает после готовности Vue-приложения
         * 
         * @return void
         */
        mounted() {
            if (!BX24_IS_INITED) return;
            BX24.fitWindow(() => BX24.resizeWindow(document.body.clientWidth + 50, screen.height - 120));

            if (!this.calendarShow) return;
            this.initCalendar();
        },
    
        methods: {
    
            /**
             * Обработчик нажатия кнопки "Удалить" для удаления установленных действий БП
             * 
             * @return void
             */
            removeActivities() {
                InfoserviceCalendar.freezeActivityList();
                InfoserviceBizProcActivity.deleteActivities().then(() => {
                    InfoserviceCalendar.unFreezeActivityList();
                    InfoserviceCalendar.mainArea.activityInstalled = false;
                });
            },
    
            /**
             * Обработчик нажатия кнопки "Установить" для установки тех действий, которые
             * не были установлены
             *
             * @return void
             */
            addActivities() {
                InfoserviceCalendar.freezeActivityList();
                InfoserviceBizProcActivity.addActivities().then(() => {
                    InfoserviceCalendar.unFreezeActivityList();

                    let mainArea = InfoserviceCalendar.mainArea;
                    mainArea.activityInstalled = true;
                    mainArea.activities = InfoserviceBizProcActivity.activities;
                    return mainArea.showTable();
                });
            },
    
            /**
             * Инициализация календаря
             * 
             * @return void
             */
            initCalendar() {
                if (this.filterDateInput) {
                    this.filterDateInput.show();
                    return;
                }
    
                this.filterDateInput = InfoserviceCalendar.initDatePicker(
                                            InfoserviceCalendar._selector.filterDateInput,
                                            (unitParams, selectedDate) => this.calendarDate = selectedDate
                                        );
            },
    
            /**
             * Получает данные фильтра, отправляет на сервер, выводит данные согласно
             * параметрам фильтра
             * 
             * @return void
             */
            showData() {
                var data = {date: this.calendarDate.getTime() / 1000};
                InfoserviceCalendar.selectFilterArea().find('[name]:not([name="date"])').each((paramNum, paramObj) => {
                    data[$(paramObj).attr('name')] = paramObj.type == 'checkbox' ? paramObj.checked : paramObj.value;
                });
                InfoserviceCalendar.freezeFilterArea();

                InfoserviceCalendar.getContentsByFilter(data).then(answer => {
                    InfoserviceCalendar.unFreezeFilterArea();
                    if (!answer.result) return;
    
                    this.days = answer.data.days;
                    this.technics = answer.data.technics;
                });
            },
    
            /**
             * Обработчик нажатия кнопки "Показать календарь"
             * 
             * @return void
             */
            showTable() {
                this.calendarShow = true;
                this.filterDateInput = null;
                setTimeout(() => this.initCalendar(), 1);
            },
    
            /**
             * Обработчик нажатия иконки в правом верхнем углу фильтра для перехода
             * к списку установленных действий
             * 
             * @return void
             */
            showActivities() {
                this.calendarShow = false;
            },
    
            /**
             * Устанавливает или снимает избранность пользователя с техники или партнера
             * 
             * @param index - порядковый номер техники или партнеры в списке technics
             * @param starObj - объект, указывающий на звезду для выбора избранноси
             *
             * @return void
             */
            setChosen(index, starObj) {
                var technic = this.technics[index];
                technic.IS_CHOSEN = !technic.IS_CHOSEN;
                
                var cellUnit = $(starObj).closest(InfoserviceCalendar._selector.technicUnit);
                InfoserviceCalendar.freezeElement(cellUnit);
    
                (new InfoserviceAjax('setchosen')).sendPOST({
                    technic: {...technic},
                    user: InfoserviceCalendar.userData
                }).then(answer => {
                    InfoserviceCalendar.unFreezeElement(cellUnit);
                    if (!answer.result) return;
                });
            },
    
            /**
             * Заполняет данными свойство contentDetail, что приводит к открытию
             * модального окна с описанием и комментариями для контента
             * 
             * @param technicIndex - порядковый номер техники или партнера в таблице
             * @param contentDay - timestamp-метка для даты выбранного контента
             * @return void
             */
            showContentDetails(technicIndex, contentDay) {
                var technic = this.technics[technicIndex];
                if (!technic.COMMENTS) Vue.set(technic, 'COMMENTS', {});
                if (!technic.COMMENTS[contentDay]) Vue.set(technic.COMMENTS, contentDay, []);
    
                var contents = this.technics[technicIndex].CONTENTS;
                this.contentDetail = {
                    ...this.technics[technicIndex],
                    CONTENTS: undefined,
                    DATE: (new Date(contentDay * 1000)).toLocaleDateString(),
                    TECHNIC_INDEX: technicIndex,
                    CONTENT_DAY: contentDay,
                    DEALS: contents && contents[contentDay] && contents[contentDay].DEALS
                         ? contents[contentDay].DEALS
                         : [{ID: 0, IS_EMPTY: true}],
                    COMMENTS: technic.COMMENTS[contentDay]
                };
            },
    
            /**
             * Обработчик события, которое срабатывает спустя мгновение после того, как было установлено, что
             * надо показать окно с подсказками, а само окно отрисовалось, получив данные о комментариях.
             * В обработчике идет выравнивание окна с комментариями относительно ячейки, на которую был наведен
             * курсор мышки
             * 
             * @param cellObj - DOM-объект на объект с ячейкой контента
             * @return void
             */
            changeHintWindowPosition(cellObj) {
                var hintWindow = InfoserviceCalendar.selectHintWindow();
                if (!hintWindow.length) return;
    
                var cellObjRect = (
                                  $(cellObj).is(InfoserviceCalendar._selector.contentArea)
                                ? cellObj
                                : $(cellObj).closest(InfoserviceCalendar._selector.contentArea).get(0)
                            ).getBoundingClientRect();
    
                var {top: bodyTop, left: bodyLeft} = document.body.getBoundingClientRect();
                var hintWindowRect = hintWindow.get(0).getBoundingClientRect();
                var cssSettings = {};
                var height = document.body.clientHeight < hintWindowRect.height
                           ? document.body.clientHeight
                           : hintWindowRect.height;
    
                cssSettings['max-height'] = height + 'px';
    
                if (cellObjRect.top < 0) {
                    cssSettings.top = -bodyTop;
                
                } else if (cellObjRect.bottom > document.body.clientHeight) {
                    cssSettings.top = (-bodyTop + document.body.clientHeight - height) + 'px';
    
                } else if (document.body.clientHeight - cellObjRect.top >= height) {
                    cssSettings.top = -bodyTop + cellObjRect.top + 'px';
                
                } else {
                    cssSettings.top = (-bodyTop + document.body.clientHeight - height) + 'px';
                }
                
                if (document.body.clientWidth - cellObjRect.right < cellObjRect.left) {
                    cssSettings.left = (-bodyLeft + cellObjRect.left - hintWindowRect.width) + 'px';
                
                } else {
                    cssSettings.left = (-bodyLeft + cellObjRect.right) + 'px';
                }
    
                InfoserviceCalendar.selectHintWindow().css(cssSettings);
            },
    
            /**
             * Скрывает окно с подсказкой о контенте
             * 
             * @return void
             */
            hideHintWindow() {
                this.hintShowingData = false;
                this.quarterContent = null;
            },
    
            /**
             * Обработчик события наведения курсора мышки на ячейку, предназначенную для контента
             * 
             * @param cellObj - DOM-объект на объект с ячейкой контента
             * @param technicIndex - порядковый номер техники или партнера
             * @param contentDay - timestamp-метка для даты выбранного контента
             * @return void
             */
            startWaitingHintWindow(cellObj, technicIndex, contentDay) {
                var windowIndex = technicIndex + '.' + contentDay;
                if (this.windowIndex && (this.windowIndex == windowIndex)) return;
                this.hideHintWindow();
                this.windowIndex = windowIndex;
    
                var date = new Date(contentDay * 1000);
                this.quarterContent = this.quarterNumber ? {
                                            technicIndex: technicIndex,
                                            month: date.getMonth() + 1,
                                            day: date.getDate()
                                        } : null;
    
                var technic = this.technics[technicIndex];
                if (!technic.COMMENTS || !technic.COMMENTS[contentDay] || !technic.COMMENTS[contentDay].length)
                    return;
    
                setTimeout(() => {
                    if (
                        (this.windowIndex != windowIndex)
                        || this.contentDetail
                    ) return;
    
                    this.hintShowingData = technic.COMMENTS[contentDay];
                    setTimeout(() => this.changeHintWindowPosition(cellObj), 1);
                }, 500);
            }
        }
    };
});