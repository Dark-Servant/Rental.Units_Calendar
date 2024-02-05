;document.addEventListener('infoservicecalendar:started', function() {
    let InfoserviceCalendar = window.InfoserviceCalendar;
    let mainArea = InfoserviceCalendar.mainArea;
    let ajaxAddition = InfoserviceAddition.Ajax;

    /**
     * 
     */
    let resultClass = new InfoserviceAjaxDataType(['date', 'commentId', 'user']);
    resultClass.setDefaultAjaxRequest(new InfoserviceAjax('copycomment'))
               .setDefaultValues({user: InfoserviceCalendar.userData});
    ajaxAddition.addPeriodUpdatingToAjaxDataTypeUnit(resultClass);

    resultClass = resultClass.getTypeUnit();

    /**
     * 
     */
    resultClass.prototype.sendData = (function(sendData) {
        return async function() {
            let result = false;
            await sendData.call(this, ...arguments).then(answer => result = answer);
            if (!result.result) return result;

            InfoserviceCalendar.updateTechnicPeriodPropertyFromDataByIndex('CONTENTS', result.data[0], mainArea.contentDetail.TECHNIC_INDEX);
            InfoserviceCalendar.updateTechnicPeriodPropertyFromDataByIndex('COMMENTS', result.data[0], mainArea.contentDetail.TECHNIC_INDEX);
            
            return result;
        };

    })(resultClass.prototype.sendData);

    window.InfoserviceCalendarCopyComment = resultClass;
});