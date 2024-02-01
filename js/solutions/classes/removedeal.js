;document.addEventListener('infoservicecalendar:started', function() {

    let mainArea = InfoserviceCalendar.mainArea;
    let ajaxAddition = InfoserviceAddition.Ajax;

    /**
     * 
     */
    let resultClass = new InfoserviceAjaxDataType([
                                'dealID',
                                'technicID',
                                'isPartner',
                                'contentDate',
                                'user',
                            ]);
    resultClass.setDefaultAjaxRequest(new InfoserviceAjax('removedeal'))
               .setDefaultValues({user: InfoserviceCalendar.userData})
               .setMethodsAfterDefaultIniting([

                    /**
                     * 
                     */
                    unit => {
                        if (mainArea.contentDetail) {
                            unit.setPropertyValue('technicID', mainArea.contentDetail.ID);
                            unit.setPropertyValue('isPartner', mainArea.contentDetail.IS_PARTNER);

                        } else {
                            unit.setPropertyValue('technicID', null);
                            unit.setPropertyValue('isPartner', null);
                        }
                    },

                    /**
                     * 
                     */
                    unit => {
                        if (mainArea.contentDetail) {
                            unit.setPropertyValue('contentDate', mainArea.contentDetail.CONTENT_DAY);

                        } else {
                            unit.setPropertyValue('contentDate', null);
                        }
                    }
                ]);
    ajaxAddition.addPeriodUpdatingToAjaxDataTypeUnit(resultClass);
    resultClass = resultClass.getTypeUnit();

    /**
     * 
     */
    resultClass.prototype.sendData = (function(sendData) {
        return async function() {
            let result = false;
            await sendData.call(this, ...arguments).then(answer => result = answer);
            if (!result.result || !result.data.length) return result;

            Vue.set(mainArea.technics, mainArea.contentDetail.TECHNIC_INDEX, result.data[0]);
            mainArea.showContentDetails(mainArea.contentDetail.TECHNIC_INDEX, mainArea.contentDetail.CONTENT_DAY);
            
            return result;
        };

    })(resultClass.prototype.sendData);

    window.InfoserviceDealRemoving = resultClass;
});