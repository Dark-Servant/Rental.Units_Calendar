;document.addEventListener('infoservicebizprocactivity:inited', function() {
    window.InfoserviceVueComponentLoader = new InfoserviceArea(
        {
            /**
             * 
             */
            _init_() {
                this.loadVueComponents().then(() => {
                    var event = new CustomEvent('infoservicevuecomponentloader:finished', {detail: {unit: this}});
                    document.dispatchEvent(event);
                });
            },

            /**
             * 
             */
            async loadVueComponents() {
                await (new InfoserviceAjax('loadvuecomponents'))
                    .sendGET()
                    .then(answer => {
                        if (!answer.result) return;

                        InfoserviceVueComponent.saveVueComponentParameters(answer.data);
                        this.initComponentAreas();
                    });
            },

            /**
             * 
             */
            initComponentAreas() {
                this.selectVueComponentArea().each((unitNum, unitObj) => {
                    (new InfoserviceVueComponent(unitObj)).add();
                });
            },
        },
        {
            selector: {
                vueComponentArea: '*[type="text/vue-component"]'
            }
        },
    );
});