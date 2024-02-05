;document.addEventListener('infoservicecalendar:started', function() {

    let InfoserviceCalendar = window.InfoserviceCalendar;
    let calendarMainArea = InfoserviceCalendar.mainArea;
    let isCanToChangeSpecialFields = new WeakMap();

    /**
     * 
     */
    let resultClass = new InfoserviceAjaxDataType(['code', 'value', 'contentDay', 'user', 'commentId', 'contentId']);
    resultClass.setAlternativeNames({
                        commentId: ['contentId', 'contentDay'],
                        contentId: 'commentId',
                        contentDay: 'commentId'
                    })
               .setNotImportantCodes(['code'])
               .setSpecificCodesAsSetter(['code', 'value'])
               .setDefaultAjaxRequest(new InfoserviceAjax('addcomment'))
               .setDefaultValues({
                        technicId: 0,
                        contentId: null,
                        commentId: null,
                        isPartner: 0,
                        user: InfoserviceCalendar.userData
                    });

    resultClass = resultClass.getTypeUnit();

    /**
     * 
     */
    resultClass.prototype.setPropertyValue = (function(setPropertyValue) {

        return function(name, value) {
            if (isCanToChangeSpecialFields.get(this) || (['contentDay', 'commentId', 'contentId'].indexOf(name) < 0))
                return setPropertyValue.call(this, name, value);

            return this;
        };
    })(resultClass.prototype.setPropertyValue);

    /**
     * 
     * @returns 
     */
    resultClass.prototype.initContentDayFromOpenedDetail = function() {
        this.setPropertyValue('contentDay', calendarMainArea.contentDetail?.CONTENT_DAY || null);

        return this;
    };

    /**
     * 
     * @param {*} index 
     * @returns 
     */
    resultClass.prototype.setCommentIndex = function(index) {
        if (!this.isCorrectValue(index)) return this;
        this.setDefaultData();
        isCanToChangeSpecialFields.set(this, true);
        this.setPropertyValue('commentId', calendarMainArea.contentDetail.COMMENTS[index].ID);
        this.setPropertyValue('contentDay', null);
        isCanToChangeSpecialFields.set(this, false);
        return this;
    };

    /**
     * 
     * @param {*} index 
     * @returns 
     */
    resultClass.prototype.setDealIndex = function(index) {
        if (!this.isCorrectValue(index)) return this;
        this.setDefaultData();
        isCanToChangeSpecialFields.set(this, true);

        this.initContentDayFromOpenedDetail();
        if (calendarMainArea.contentDetail.DEALS[index].TECHNIC_ID) {
            this.setPropertyValue('technicId', calendarMainArea.contentDetail.DEALS[index].TECHNIC_ID);

        } else {
            this.setPropertyValue('technicId', calendarMainArea.contentDetail.ID);
            this.setPropertyValue('isPartner', +calendarMainArea.contentDetail.IS_PARTNER);
        }
        this.setPropertyValue('contentId', calendarMainArea.contentDetail.DEALS[index].ID);
        isCanToChangeSpecialFields.set(this, false);
        return this;
    }

    /**
     * 
     */
    resultClass.prototype.sendData = (function(sendData) {
        return async function() {
            let result = false;
            await sendData.call(this, ...arguments).then(answer => result = answer);
            if (!result.result) return result;

            InfoserviceCalendar.updateCommentsAtModalByTechnicPeriodData(result.data[0]);
            return result;
        };

    })(resultClass.prototype.sendData);

    window.InfoserviceCalendarComment = resultClass;
});