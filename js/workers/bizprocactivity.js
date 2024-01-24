;var InfoserviceBizProcActivity = (function() {

    return new InfoserviceArea(
        {
            isBitrix24Domain: false,
            activities: {},
            freeActivityCodes: [],

            /**
             *
             */
            _init_() {
                this.isBitrix24Domain = typeof SERVER_CONSTANTS.DOMAIN != 'undefined';

                this.initExistsActivities().then(() => {
                    let event = new CustomEvent('infoservicebizprocactivity:inited', {detail: {unit: this}});
                    document.dispatchEvent(event);
                });
            },

            /**
             * Запуск процесса Добавления действий Бизнес-процессов в систему
             *
             * @return void
             */
            addActivities() {
                return this.addActivity(this.freeActivityCodes);
            },

            /**
             * Добавление действия Бизнес-процессов в систему
             *
             * @param activityCodes - список специальных кодов действий Бизнес-процессов
             * @return void
             */
            async addActivity(activityCodes) {
                if (!activityCodes.length) return;

                var activityCode = activityCodes.shift();
                if (typeof(this.activities[activityCode]) == 'undefined') return;

                await BXRestApi('bizproc.activity.add',
                                {
                                    ...this.activities[activityCode],
                                    CODE: activityCode,
                                    HANDLER: document.location.origin + SERVER_CONSTANTS.APPPATH + '/lib/bp.activities/index.php',
                                    AUTH_USER_ID: 1,
                                    USE_SUBSCRIPTION: 'Y',
                                    DOCUMENT_TYPE: ['lists', 'BizprocDocument']
                                })
                        .then(answer => {
                            if (!answer.answer.result) return;

                            this.addActivity(activityCodes);
                        });
            },

            /**
             * Запуск процесса удаления действий Бизнес-процессов из системы
             *
             * @return void
             */
            deleteActivities() {
                return this.deleteActivity(
                            Object.keys(this.activities)
                                  .filter(code => this.freeActivityCodes.indexOf(code) < 0)
                        );
            },

            /**
             *
             */
            async initExistsActivities() {
                await (new InfoserviceAjax('getactivities'))
                        .sendGET()
                        .then(async answer => {
                            if (!answer.result) return;

                            this.activities = answer.data;
                            await this.checkActivities();
                        });
            },

            /**
             * Проверка какие действия Бизнес-процессов установлены в системе
             *
             * @return void
             */
            async checkActivities() {
                await BXRestApi('bizproc.activity.list')
                        .then(async answer => {
                            if (!answer.answer.result) return;

                            this.prepareFreeActivityCodesViaCheckingResult(answer.answer.result);
                            await this.deleteActivity(this.getBadActivitiesViaCheckingResult(answer.answer.result));
                        });
            },

            /**
             *
             * @param {*} answerData
             * @returns
             */
            prepareFreeActivityCodesViaCheckingResult(answerData) {
                this.freeActivityCodes = [];
                for (var code in this.activities) {
                    if (answerData.indexOf(code) > -1) continue;

                    this.freeActivityCodes.push(code);
                }
            },

            /**
             *
             * @param {*} answerData
             * @returns
             */
            getBadActivitiesViaCheckingResult(answerData) {
                return answerData.filter(code => this.activities[code] == undefined );
            },

            /**
             * Удаление действия Бизнес-процессов из системы
             *
             * @param activityCodes - список специальных кодов действий Бизнес-процессов
             * @return void
             */
            async deleteActivity(activityCodes) {
                if (!activityCodes.length) return;

                let activityCode = activityCodes.shift();
                /**
                 * В случае, если некоторые действия были удалены из решения, но они остались на портале,
                 * то эти действия удалятся с портала, и их не надо запоминать в списке неустановленных
                 * действий
                 */
                if (this.activities[activityCode] instanceof Object)
                    this.freeActivityCodes.push(activityCode);

                await BXRestApi('bizproc.activity.delete', {code: activityCode})
                        .then(answer => {
                            if (!answer.answer.result) return;

                            this.deleteActivity(activityCodes);
                        });
            }
        }
    );
})();