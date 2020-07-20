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
                <div class="rc-content-deal-addr" v-bind:class="{'rc-no-comment-addr': !deal.LAST_COMMENT}" v-bind:title="deal.WORK_ADDRESS">{{deal.WORK_ADDRESS}}</div>
                <div class="rc-content-deal-comment" v-bind:title="deal.LAST_COMMENT" v-if="deal.LAST_COMMENT">{{deal.LAST_COMMENT}}</div>
                <div class="rc-content-deal-responsible" v-bind:title="deal.RESPONSIBLE_NAME">{{deal.RESPONSIBLE_NAME}}</div>
            </template>
        </div>
    </div>
</script>

<script id="calendar-table-component" data-props="bx24inited, technics, days" type="text/vue-component">
    <table class="rc-table">
        <tr class="rc-header">
            <td class="rc-filter">
                <div class="rc-filter-my-technic-area">
                    <label>
                        <span class="rc-filter-my-technic-title"><?=$langValues['FILTER_MY_TECHNIC']?></span>
                        <input class="rc-filter-my-technic-checkbox"
                            name="my-technic" v-on:change="$emit('show-data')"
                            type="checkbox">
                    </label>
                </div>
                <div class="rc-filter-date-area">
                    <input class="rc-filter-date-input"
                        name="date" value="<?=date(Day::DAY_CALENDAR_FORMAT)?>"
                        v-on:change="$emit('show-data')"
                        type="text" readonly>
                    <div
                        class="rc-button rc-filter-button rc-filter-date-today"
                        v-on:click="$emit('set-today')"><?=$langValues['FILTER_TODAY_BUTTON']?></div>
                </div>
                <span class="rc-activity-list-back" v-on:click="$emit('show-activities')" v-if="bx24inited"></span>
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
                <div class="rc-technic-unit-area" v-bind:class="{'rc-chosen': technic.IS_CHOSEN}">
                    <span
                        class="rc-technic-name"
                        v-bind:title="technic.NAME">{{technic.NAME}}</span>
                    <span
                        class="rc-technic-state-number"
                        v-bind:title="technic.STATE_NUMBER"
                        v-if="!technic.IS_PARTNER">{{technic.STATE_NUMBER}}</span>
                </div>
            </td>
            <td class="rc-content" v-for="content in technic.CONTENTS">
                <content-cell v-bind:content="content"></content-cell>
            </td>
        </tr>

    </table>
</script>

<script id="activity-list-component" data-props="installed, activities" type="text/vue-component">
    <div class="rc-activity-list">
        <div class="rc-activity-list-title" v-if="installed"><?=$langValues['BP_ACTIVITIES_INSTALLED_TITLE']?></div>
        <div class="rc-activity-list-title" v-else><?=$langValues['BP_ACTIVITIES_EMPTY_TITLE']?></div>
        <div class="rc-activity-list-data">
            <div class="rc-activity-unit" v-for="activity in activities">
                <span>{{activity.data.NAME.<?=LANG?>}}</span>
            </div>
        </div>
        <div class="rc-activity-list-buttons">
            <template v-if="installed">
            <div class="rc-button rc-activity-list-button rc-activity-list-remove-button"
                v-on:click="$emit('remove-activities')"><?=$langValues['ACTIVITY_LIST_REMOVE_BUTTON']?></div><!--
            --><div class="rc-button rc-activity-list-button rc-activity-list-cancel-button"
                v-on:click="$emit('show-table')"><?=$langValues['ACTIVITY_LIST_CANCEL_BUTTON']?></div>
            </template><!--
            --><div class="rc-button rc-activity-list-button rc-activity-list-install-button"
                v-on:click="$emit('add-activities')"
                v-else><?=$langValues['ACTIVITY_LIST_INSTALL_BUTTON']?></div>
        </div>
    </div>
</script>

<div id="rental-calendar">
    <calendar-table
        v-on:show-data="showData"
        v-on:set-today="setToday"
        v-on:show-activities="showActivities"
        v-bind:bx24inited="bx24inited"
        v-bind:technics="technics"
        v-bind:days="days"
        v-if="calendarShow"></calendar-table>
    <activity-list
        v-on:remove-activities="removeActivities"
        v-on:show-table="showTable"
        v-on:add-activities="addActivities"
        v-bind:installed="activityInstalled"
        v-bind:activities="activities"
        v-else></activity-list>
</div>