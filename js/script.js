var selector = {
    calendar: '#rental-calendar',
    filterDateInput: '.rc-filter-date-input'
};

window.calendar = new Vue({
    el: selector.calendar,
    data: {
        days: <?=json_encode($days)?>,
        technics: <?=json_encode($technics)?>
    },
    mounted: () => {
        this.filterDateInput = datepicker(selector.filterDateInput, {
            showAllDates: true,
            startDay: 1,
            customDays: [
                LANG_VALUES.DATE_CHOOOSING.DAYS.SHORT.SUN,
                LANG_VALUES.DATE_CHOOOSING.DAYS.SHORT.MON,
                LANG_VALUES.DATE_CHOOOSING.DAYS.SHORT.TUE,
                LANG_VALUES.DATE_CHOOOSING.DAYS.SHORT.WED,
                LANG_VALUES.DATE_CHOOOSING.DAYS.SHORT.THU,
                LANG_VALUES.DATE_CHOOOSING.DAYS.SHORT.FRI,
                LANG_VALUES.DATE_CHOOOSING.DAYS.SHORT.SAT
            ],
            customMonths: [
                LANG_VALUES.DATE_CHOOOSING.MONTHS.JANUARY,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.FEBRUARY,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.MARCH,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.APRIL,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.MAY,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.JUNE,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.JULY,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.AUGUST,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.SEPTEMBER,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.OCTOBER,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.NOVEMBER,
                LANG_VALUES.DATE_CHOOOSING.MONTHS.DECEMBER
            ],
            overlayPlaceholder: LANG_VALUES.DATE_CHOOOSING_YEAR,

            formatter: (input, date, instance) => input.value = date.toLocaleDateString()
        });
    }
});
