<script id="content-cell-component" data-props="content, quarter, day, comments" type="text/vue-component">
    <div class="rc-content" v-bind:class="{'rc-content-quarter': quarter > 0, 'rc-read-content': readComment}">
        <div
            class="rc-content-area"
            v-bind:class="{
                    ['rc-content-' + content.STATUS_CLASS]: true,
                    'rc-content-very-many': content.VERY_MANY,
                    'rc-content-is-not-one': !content.VERY_MANY && !content.IS_ONE
                }"
            v-on:click="$emit('show-content-details')"
            v-on:mousemove="if (content.IS_ONE) $emit('start-waiting-hint-window', $event.target)"
            v-if="dealExists">
            <span class="rc-comment-exist-flag" v-if="commentSize"></span>
            <template v-if="!quarter">
                <span class="rc-content-many-deals" v-if="content.VERY_MANY"><?=$langValues['MANY_DEAL_STATUS']?></span>
                <div class="rc-content-deals" v-for="deal in content.DEALS" v-else>
                    <template v-if="deal.CELL_SHOWING">
                        <a
                            class="rc-content-deal-link"
                            v-bind:title="deal.CUSTOMER_NAME"
                            v-bind:href="deal.DEAL_URL ? deal.DEAL_URL : 'javascript:void();'"
                            v-bind:target="deal.DEAL_URL ? '_blank' : ''">{{deal.CUSTOMER_NAME}}</a>
                        <template v-if="content.IS_ONE">
                            <div class="rc-content-deal-addr" v-bind:class="{'rc-no-comment-addr': !commentSize}" v-bind:title="deal.WORK_ADDRESS">{{deal.WORK_ADDRESS}}</div>
                            <div class="rc-content-deal-comment" v-bind:title="lastComment.VALUE" v-if="commentSize">{{lastComment.VALUE}}</div>
                            <div class="rc-content-deal-responsible" v-bind:title="deal.RESPONSIBLE_NAME" v-if="deal.RESPONSIBLE_NAME">{{deal.RESPONSIBLE_NAME}}</div>
                        </template>
                    </template>
                </div>
            </template>
        </div>
        <div class="rc-content-area rc-content-empty"
            v-bind:class="{
                    ['rc-content-' + content.STATUS_CLASS]: content.STATUS_CLASS,
                }"
            v-on:click="$emit('show-content-details')"
            v-on:mousemove="$emit('start-waiting-hint-window', $event.target)"
            v-else>
            <template v-if="commentSize">
                <span class="rc-comment-exist-flag"></span>
                <template v-if="!quarter">
                    <div class="rc-content-deal-comment" v-bind:title="lastComment.VALUE">{{lastComment.VALUE}}</div>
                    <div class="rc-content-deal-responsible" v-bind:title="lastComment.USER_NAME">{{lastComment.USER_NAME}}</div>
                </template>
            </template>
        </div>
    </div>
</script>

<script id="calendar-filter-component" data-props="calendardate, quarter" type="text/vue-component">
    <div class="rc-filter">
        <template v-if="quarter"><!--
            --><select name="quarter-year" title="<?=$langValues['FILTER_YEAR_CHOSING']?>"
                v-model="chosenYear"
                v-on:change="changeQuarterParams">
                <option
                    v-bind:value="year"
                    v-for="year in yearList">{{year}}</option>
            </select><!--
            --><select name="quarter-number" title="<?=$langValues['FILTER_QUARTER_CHOSING']?>"
                v-model="quarter"
                v-on:change="changeQuarterParams">
                <option
                    v-bind:value="qIndex + 1"
                    v-for="(qValue, qIndex) in quarterList">{{qValue}}</option>
            </select><!--
        --></template><!--
        --><template v-else><!--
            --><label class="rc-date-area rc-filter-date-area">
                <input class="rc-date-input rc-filter-date-input"
                    name="date"
                    v-bind:value="calendarDateValue"
                    v-on:click="$emit('init-calendar')"
                    type="text" readonly>
            </label><!--
            --><div class="rc-filter-icon rc-filter-date-today"
                v-bind:title="'<?=$langValues['FILTER_TODAY_BUTTON']?>'"
                v-on:click="setToday()"></div><!--
        --></template><!--
        --><label
            class="rc-filter-icon rc-filter-my-technic"
            v-bind:title="'<?=$langValues['FILTER_MY_TECHNIC']?>'">
            <input type="checkbox" name="my-technic"
                v-on:click="$emit('show-data')">
            <span></span>
        </label><!--
        --><label
            class="rc-filter-icon rc-filter-quarter"
            v-bind:title="'<?=$langValues['FILTER_QUARTER_BUTTON']?>'">
            <input type="checkbox" v-on:change="showQuarters">
            <span></span>
        </label><!--
        --><span class="rc-activity-list-back"
                title="<?=$langValues['BIZ_PROC_ACTIVITY_LIST_TITLE']?>"
                v-on:click="$emit('show-activities')"
                v-if="backtoactivities"></span>
    </div>
</script>

<script id="calendar-table-component" data-props="calendardate, quarter, quartercontent, bx24inited, technics, days" type="text/vue-component">
    <div class="rc-calendar">
        <div class="rc-header" v-bind:class="{'rc-header-quarter': quarter > 0}" v-on:mousemove="$emit('hide-hint-window');">
            <calendar-filter
                v-bind:calendardate="calendardate"
                v-bind:quarter="quarter"
                v-on:init-calendar="$emit('init-calendar')"
                v-on:show-data="$emit('show-data')"
                v-on:show-activities="$emit('show-activities')"></calendar-filter><!--
            --><template v-if="quarter"><!--
                --><div class="rc-month" v-bind:data-day-count="month.dayCount" v-for="month in months">
                        <div class="rc-month-name">
                            <span>{{month.title}}</span>
                        </div>
                        <div
                            class="rc-month-day"
                            v-bind:class="{
                                'rc-hover-content-month-day': quartercontent
                                                              && (quartercontent.month == month.number)
                                                              && (quartercontent.day == day + 1)
                            }"
                            v-for="day in month.days">
                            <span>{{day + 1}}</span>
                        </div>
                    </div><!--
            --></template><!--
            --><template v-else><!--
                --><div class="rc-day" v-for="(day, dayIndex) in days">
                        <div class="rc-day-area">
                            <span class="rc-calendar-button rc-day-step" v-on:click="dayInc(dayIndex)"></span>
                            <span class="rc-day-value">{{day.VALUE}}</span>
                            <span class="rc-day-week-name">{{day.WEEK_DAY_NAME}}</span>
                        </div>
                    </div>
            </template>
        </div>
        <div class="rc-technic" v-bind:class="{'rc-technic-quarter': quarter}" v-for="technic in technics">
            <div class="rc-technic-unit"
                v-on:mousemove="$emit('hide-hint-window');"
                v-bind:class="{
                    'rc-chosen': technic.IS_CHOSEN,
                    'rc-hover-quarter-technic': quarter && quartercontent && (quartercontent.technicIndex == technic.index),

                }">
                <span class="rc-technic-chosen"
                    v-on:click="$emit('set-chosen', technic.index, $event.target)"
                    v-if="bx24inited"></span>
                <span class="rc-technic-name"
                    v-bind:title="technic.NAME">{{technic.NAME}}</span>
                <span class="rc-technic-state-number"
                    v-bind:title="technic.STATE_NUMBER"
                    v-if="!technic.IS_PARTNER">{{technic.STATE_NUMBER}}</span>
            </div><!--
            --><content-cell
                v-bind:content="content"
                v-bind:quarter="quarter"
                v-bind:day="contentDay"
                v-bind:comments="technic.COMMENTS"
                v-on:start-waiting-hint-window="$emit('start-waiting-hint-window', $event, technic.index, contentDay)"
                v-on:show-content-details="$emit('show-content-details', technic.index, contentDay)"
                v-for="(content, contentDay) in technic.CONTENTS"></content-cell><!--
        </div>
    </div>
</script>

<script id="comment-unit-editor-component" data-props="value" type="text/vue-component">
    <div class="rc-deal-detail-comment-input-area">
        <textarea class="rc-textarea rc-deal-detail-comment-textarea" v-model="value"></textarea>
        <div class="rc-deal-detail-comment-buttons">
            <span class="rc-calendar-button rc-comment-button rc-deal-detail-comment-button rc-deal-detail-comment-ok-button"
                v-on:click="commentAdd"></span><!--
            --><span class="rc-calendar-button rc-comment-button rc-deal-detail-comment-button rc-deal-detail-comment-cancel-button"
                v-on:click="stopCommentAdd"></span>
        </div>
    </div>
</script>

<script id="comment-unit-component" data-props="comment, commentindex, isnothint" type="text/vue-component">
    <comment-unit-editor
        v-bind:value="comment.VALUE"
        v-if="isEditing && canEdit"></comment-unit-editor>
    <div class="rc-comment-unit" v-else>
        <div class="rc-comment-unit-value">{{comment.VALUE}}</div>
        <div class="rc-comment-unit-buttons" v-if="canEdit">
            <span class="rc-calendar-button rc-comment-button rc-comment-unit-button rc-comment-unit-copy-button"
                title="<?=$langValues['COMMENT_COPY_BUTTON_TITLE']?>"
                v-on:click="initCopyComment"></span><!--
            --><span class="rc-calendar-button rc-comment-button rc-comment-unit-button rc-comment-unit-edit-button"
                title="<?=$langValues['COMMENT_EDIT_BUTTON_TITLE']?>"
                v-on:click="initEditComment"></span><!--
            --><span class="rc-calendar-button rc-comment-button rc-comment-unit-button rc-comment-unit-remove-button"
                title="<?=$langValues['COMMENT_REMOVE_BUTTON_TITLE']?>"
                v-on:click="removeComment"></span>
        </div>
        <div class="rc-comment-unit-author">
            <span class="rc-comment-unit-author-value">{{comment.USER_NAME}}</span><!--
            --><span class="rc-comment-unit-author-date">{{authorDate}}</span>
        </div>
    </div>
</script>

<script id="duty-comments-component" data-props="dealindex" type="text/vue-component">
    <template>
        <div class="rc-duty-comment-area" v-if="isChoosing">
            <select class="rc-duty-comment-select" v-model="selectedcomment">
                <option v-bind:value="dutycomment.CODE" v-for="dutycomment in comments">{{dutycomment.NAME}}</option>
            </select>
            <span class="rc-calendar-button rc-comment-button rc-deal-detail-comment-button rc-deal-detail-comment-ok-button"
                v-on:click="addDutyComment"></span><!--
            --><span class="rc-calendar-button rc-comment-button rc-deal-detail-comment-button rc-deal-detail-comment-cancel-button"
                v-on:click="closeDutyChoosing"></span>
        </div>
        <span class="rc-deal-detail-button rc-deal-duty-comment"
            title="<?=$langValues['BEGIN_DUTY_COMMENT_ADD_BUTTON_TITLE']?>"
            v-on:click="initDutyChoosing"
            v-else></span>
    </template>
</script>

<script id="deal-detail-modal-component" data-props="deal, dealindex, newcomment, bx24inited, comments" type="text/vue-component">
    <div class="rc-deal-detail">
        <template v-if="!deal.IS_EMPTY">
            <a class="rc-deal-detail-customer-url" v-bind:href="deal.DEAL_URL">{{deal.CUSTOMER_NAME}}</a>
            <div class="rc-deal-detail-work-address">{{deal.WORK_ADDRESS}}</div>
            <div class="rc-deal-detail-technic" v-if="deal.TECHNIC_NAME">
                <span class="rc-deal-detail-technic-caption"><?=$langValues['MODAL_CONTENT_TECHNIC_CAPTION']?></span>
                <span class="rc-deal-detail-technic-value">{{deal.TECHNIC_NAME}}</span>
            </div>
            <div class="rc-deal-detail-responsible">
                <span class="rc-deal-detail-responsible-caption"><?=$langValues['MODAL_CONTENT_RESPONSIBLE_CAPTION']?></span>
                <span class="rc-deal-detail-responsible-value">{{deal.RESPONSIBLE_NAME}}</span>
            </div>
            <span class="rc-calendar-button rc-calendar-remove-deal-button"
                title="<?=$langValues['OPEN_URL_WITH_DEAL_REMOVE_TITLE']?>"
                v-on:click="$emit('init-deal-removing', dealindex)"
                v-if="bx24inited"></span>
        </template>
        <span class="rc-calendar-button rc-calendar-add-deal-button"
            title="<?=$langValues['OPEN_URL_WITH_DEAL_ADD_TITLE']?>"
            v-on:click="openDealCreating"
            v-else-if="bx24inited"></span>
        <div class="rc-deal-detail-comments">
            <comment-unit
                v-bind:comment="comment"
                v-bind:commentindex="commentIndex"
                v-bind:isnothint="true"
                v-for="(comment, commentIndex) in comments"
                v-if="deal.ID == comment.CONTENT_ID"></comment-unit>
            <template v-if="bx24inited">
                <comment-unit-editor
                    v-bind:value="''"
                    v-if="newcomment"></comment-unit-editor>
                <template v-else>
                    <span class="rc-deal-detail-button rc-deal-detail-add-comment"
                        title="<?=$langValues['BEGIN_COMMENT_ADD_BUTTON_TITLE']?>"
                        v-on:click="initCommentAdd"></span>
                    <duty-comments v-bind:dealindex="dealindex"></duty-comments>
                </template>
            <template>
        </div>
    </div>
</script>

<script id="copy-comment-modal-component" data-props="" type="text/vue-component">
    <div class="rc-window rc-copy-comment-modal-window rc-no-visivility">
        <div class="rc-modal-title rc-copy-comment-title"><?=$langValues['COMMENT_COPY_TITLE']?></div>
        <div class="rc-modal-details rc-comment-copy-details">
            <label class="rc-date-area rc-comment-copy-date-area">
                <input class="rc-date-input rc-comment-copy-date-input"
                    name="date"
                    v-bind:value="copyToDateValue"
                    type="text" readonly>
            </label>
            <span class="rc-calendar-button rc-comment-button rc-comment-unit-button rc-copy-comment-ok-button"
                v-on:click="processCopyComment"></span><!--
            --><span class="rc-calendar-button rc-comment-button rc-comment-unit-button rc-copy-comment-cancel-button"
                v-on:click="closeCopyComment"></span>
        </div>
    </div>
</script>

<script id="content-detail-modal-component" data-props="content, bx24inited, newcommentdealindex" type="text/vue-component">
    <div class="rc-modal rc-content-detail-modal">
        <div class="rc-window rc-content-detail-window rc-no-visivility" v-bind:class="{'rc-no-reaction': isCopyProcess}">
            <span class="rc-content-detail-close" v-on:click="closeDetailModal"></span>
            <div class="rc-modal-title rc-content-detail-title">
                <span class="rc-content-detail-title-date">{{content.DATE}}</span><!--
                --><span class="rc-content-detail-title-value">{{content.NAME}}</span>
            </div>
            <div class="rc-modal-details rc-deal-details">
                <deal-detail-modal
                    v-bind:deal="deal"
                    v-bind:dealindex="dealIndex"
                    v-bind:newcomment="newcommentdealindex === dealIndex"
                    v-bind:bx24inited="bx24inited"
                    v-bind:comments="content.COMMENTS"
                    v-on:init-deal-removing="initDealRemoving"
                    v-for="(deal, dealIndex) in content.DEALS"></deal-detail-modal>
            </div>
        </div>
        <copy-comment-modal v-if="isCopyProcess"></copy-comment-modal>
    </div>
</script>

<script id="hint-window-component" data-props="comments" type="text/vue-component">
    <div class="rc-hint-window">
        <div class="rc-hint-comments">
            <comment-unit
                v-bind:comment="comment"
                v-bind:commentindex="false"
                v-bind:isnothint="false"
                v-for="comment in comments"></comment-unit>
        </div>
    </div>
</script>

<script id="activity-list-component" data-props="installed, activities" type="text/vue-component">
    <div class="rc-activity-list">
        <div class="rc-activity-list-title" v-if="installed"><?=$langValues['BP_ACTIVITIES_INSTALLED_TITLE']?></div>
        <div class="rc-activity-list-title" v-else><?=$langValues['BP_ACTIVITIES_EMPTY_TITLE']?></div>
        <div class="rc-activity-list-data">
            <div class="rc-activity-unit" v-for="activity in activities">
                <span>{{activity.NAME.<?=LANG?>}}</span>
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
    <template v-if="calendarShow">
        <calendar-table
            v-on:init-calendar="initCalendar"
            v-on:show-data="showData"
            v-on:show-activities="showActivities"
            v-on:set-chosen="setChosen"
            v-on:show-content-details="showContentDetails"
            v-on:start-waiting-hint-window="startWaitingHintWindow"
            v-on:hide-hint-window="hideHintWindow"
            v-bind:calendardate="calendarDate"
            v-bind:quarter="quarterNumber"
            v-bind:quartercontent="quarterContent"
            v-bind:bx24inited="bx24inited"
            v-bind:technics="sortedTechnics"
            v-bind:days="days"></calendar-table>

        <content-detail-modal
            v-bind:content="contentDetail"
            v-bind:bx24inited="bx24inited"
            v-bind:newcommentdealindex="newCommentDealIndex"
            v-if="contentDetail"></content-detail-modal>

        <hint-window
            v-bind:comments="hintShowingData"
            v-if="hintShowingData"></hint-window>
    </template>

    <activity-list
        v-on:remove-activities="removeActivities"
        v-on:show-table="showTable"
        v-on:add-activities="addActivities"
        v-bind:installed="activityInstalled"
        v-bind:activities="activities"
        v-else></activity-list>
</div>