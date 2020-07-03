<script id="content-cell-component" data-props="content" type="text/vue-component">
    <div class="rc-content-area" v-bind:class="['rc-content-' + content.STATUS_CLASS]" v-if="content">
        <span class="rc-content-many-deals" v-if="content.DEAL_COUNT > 3"><?=$langValues['MANY_DEAL_STATUS']?></span>
        <div class="rc-content-deals" v-for="deal in content.DEALS" v-else>
            <a class="rc-content-deal-link" v-bind:href="deal.DEAL_URL">{{deal.CUSTOMER_NAME}}</a>
            <template v-if="content.DEAL_IS_ONE">
                <div class="rc-content-deal-addr">{{deal.WORK_ADDRESS}}</div>
                <div class="rc-content-deal-comment">{{deal.LAST_COMMENT}}</div>
                <div class="rc-content-deal-responsible">{{deal.RESPONSIBLE_NAME}}</div>
            </template>
        </div>
    </div>
</script>

<div id="rental-calendar">
    <table class="rc-table">
        <tr class="rc-header">
            <td class="rc-filter">
                <div class="rc-filter-my-technic-area">
                    <span class="rc-filter-my-technic-title"><?=$langValues['FILTER_MY_TECHNIC']?></span>
                    <input class="rc-filter-my-technic-checkbox" type="checkbox">
                </div>
                <div class="rc-filter-date-area">
                    <label>
                        <input class="rc-filter-date-input" type="text" readonly>
                        <input
                            class="rc-button rc-filter-button rc-filter-date-today"
                            type="button"
                            value="<?=$langValues['FILTER_TODAY_BUTTON']?>">
                    </label>
                </div>
            </td>
            <td class="rc-day" v-for="day in days">
                <div class="rc-day-area">
                    <span class="rc-day-value">{{day.VALUE}}</span>
                    <span class="rc-day-week-name">{{day.WEEK_DAY_NAME}}</span>
                </div>
            </td>
        </tr>

        <tr class="rc-technic" v-for="technic in technics">
            <td class="rc-technic-unit">
                <div class="rc-technic-unit-area">
                    <span class="rc-technic-name">{{technic.NAME}}</span>
                </div>
            </td>
            <td class="rc-content" v-for="content in technic.CONTENTS">
                <content-cell v-bind:content="content"></content-cell>
            </td>
        </tr>

    </table>
</div>