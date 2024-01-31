;var InfoserviceAddition = InfoserviceAddition || {};
InfoserviceAddition.Ajax = InfoserviceAddition.Ajax || {};
InfoserviceAddition.Ajax.addPeriodUpdatingToAjaxDataTypeUnit = function(dataTypeUnit) {

    let mainArea = InfoserviceCalendar.mainArea;
    let mainFields = dataTypeUnit.mainFields || [];

    dataTypeUnit.setMainFields(mainFields.concat(['startDate', 'quarterNumber', 'quarterYear']));
    dataTypeUnit.setAlternativeNames(
        Object.assign(
            {},
            dataTypeUnit.alternativeFieldCodes || {},
            {
                quarterNumber: 'startDate',
                quarterYear: 'startDate',
            }
        )
    );
    dataTypeUnit.setMethodsAfterDefaultIniting([
        ...dataTypeUnit.afterDefaultMethods,

        /**
         * 
         * @param {*} unit 
         */
        unit => {
            unit.setPropertyValue('startDate', Math.floor(mainArea.calendarDate.getTime() / 1000));    
            if (mainArea.quarterNumber) {
                unit.setPropertyValue('quarterNumber', mainArea.quarterNumber);
                unit.setPropertyValue('quarterYear', mainArea.calendarDate.getFullYear());
                
            } else {
                unit.setPropertyValue('quarterNumber', null);
                unit.setPropertyValue('quarterYear', null);
            }
        }
    ]);
};