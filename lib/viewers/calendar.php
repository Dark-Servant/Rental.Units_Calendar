<script id="content-cell-component" data-props="content" type="text/vue-component">
    <div class="rc-content-area"
        v-bind:class="{
            ['rc-content-' + content.STATUS_CLASS]: true,
            ['rc-content-very-many']: content.VERY_MANY,
            ['rc-content-is-not-one']: !content.VERY_MANY && !content.IS_ONE,
        }" v-if="content">
        <span class="rc-content-many-deals" v-if="content.VERY_MANY"><?=$langValues['MANY_DEAL_STATUS']?></span>
        <div class="rc-content-deals" v-for="deal in content.DEALS" v-else>
            <a class="rc-content-deal-link" v-bind:title="deal.CUSTOMER_NAME" v-bind:href="deal.DEAL_URL" target=__bind>{{deal.CUSTOMER_NAME}}</a>
            <template v-if="content.IS_ONE">
                <div class="rc-content-deal-addr" v-bind:title="deal.WORK_ADDRESS">{{deal.WORK_ADDRESS}}</div>
                <div class="rc-content-deal-comment" v-bind:title="deal.LAST_COMMENT">{{deal.LAST_COMMENT}}</div>
                <div class="rc-content-deal-responsible" v-bind:title="deal.RESPONSIBLE_NAME">{{deal.RESPONSIBLE_NAME}}</div>
            </template>
        </div>
    </div>
</script>

<div id="rental-calendar">
    <table class="rc-table">
        <tr class="rc-header">
            <td class="rc-filter">
                <div class="rc-filter-my-technic-area">
                    <label>
                        <span class="rc-filter-my-technic-title"><?=$langValues['FILTER_MY_TECHNIC']?></span>
                        <input class="rc-filter-my-technic-checkbox"
                            name="my-technic" v-on:change="showData"
                            type="checkbox">
                    </label>
                </div>
                <div class="rc-filter-date-area">
                    <input class="rc-filter-date-input"
                        name="date" value="<?=date(DAY_CALENDAR_FORMAT)?>"
                        v-on:change="showData"
                        type="text" readonly>
                    <input
                        class="rc-button rc-filter-button rc-filter-date-today"
                        type="button" v-on:click="setToday"
                        value="<?=$langValues['FILTER_TODAY_BUTTON']?>">
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
                <div class="rc-technic-unit-area" v-bind:class="{'rc-my': technic.IS_MY}">
                    <span class="rc-technic-name" v-bind:title="technic.NAME">{{technic.NAME}}</span>
                </div>
            </td>
            <td class="rc-content" v-for="content in technic.CONTENTS">
                <content-cell v-bind:content="content"></content-cell>
            </td>
        </tr>

    </table>
</div>