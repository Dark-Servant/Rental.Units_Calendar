<script id="content-cell-component" data-props="content, day, comments" type="text/vue-component">
    <div class="rc-content-area"
        v-bind:class="{
                ['rc-content-' + content.STATUS_CLASS]: true,
                ['rc-content-very-many']: content.VERY_MANY,
                ['rc-content-is-not-one']: !content.VERY_MANY && !content.IS_ONE,
            }"
        v-on:click="$emit('show-content-details')"
        v-if="content">
        <span class="rc-content-many-deals" v-if="content.VERY_MANY"><?=$langValues['MANY_DEAL_STATUS']?></span>
        <div class="rc-content-deals" v-for="deal in content.DEALS" v-else>
            <a class="rc-content-deal-link" v-bind:title="deal.CUSTOMER_NAME" v-bind:href="deal.DEAL_URL" target=__bind>{{deal.CUSTOMER_NAME}}</a>
            <template v-if="content.IS_ONE">
                <div class="rc-content-deal-addr" v-bind:class="{'rc-no-comment-addr': !commentSize}" v-bind:title="deal.WORK_ADDRESS">{{deal.WORK_ADDRESS}}</div>
                <div class="rc-content-deal-comment" v-bind:title="lastComment.VALUE" v-if="commentSize">{{lastComment.VALUE}}</div>
                <div class="rc-content-deal-responsible" v-bind:title="deal.RESPONSIBLE_NAME">{{deal.RESPONSIBLE_NAME}}</div>
            </template>
        </div>
    </div>
    <div class="rc-content-area rc-content-empty"
        v-on:click="$emit('show-content-details')"
        v-else>
        <template v-if="commentSize">
            <div class="rc-content-deal-comment" v-bind:title="lastComment.VALUE">{{lastComment.VALUE}}</div>
            <div class="rc-content-deal-responsible" v-bind:title="lastComment.USER_NAME">{{lastComment.USER_NAME}}</div>
        </template>
    </div>
</script>

<script id="calendar-table-component" data-props="bx24inited, backtoactivities, technics, days" type="text/vue-component">
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
                        name="date" value="<?=date(Day::CALENDAR_FORMAT)?>"
                        v-on:change="$emit('show-data')"
                        type="text" readonly>
                    <div
                        class="rc-button rc-filter-button rc-filter-date-today"
                        v-on:click="$emit('set-today')"><?=$langValues['FILTER_TODAY_BUTTON']?></div>
                </div>
                <span class="rc-activity-list-back" v-on:click="$emit('show-activities')" v-if="backtoactivities"></span>
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
                        class="rc-technic-chosen"
                        v-on:click="$emit('set-chosen', technic.index, $event.target)"
                        v-if="bx24inited"></span>
                    <span
                        class="rc-technic-name"
                        v-bind:title="technic.NAME">{{technic.NAME}}</span>
                    <span
                        class="rc-technic-state-number"
                        v-bind:title="technic.STATE_NUMBER"
                        v-if="!technic.IS_PARTNER">{{technic.STATE_NUMBER}}</span>
                </div>
            </td>
            <td class="rc-content" v-for="(content, contentDay) in technic.CONTENTS">
                <content-cell
                    v-bind:content="content"
                    v-bind:day="contentDay"
                    v-bind:comments="technic.COMMENTS"
                    v-on:show-content-details="$emit('show-content-details', technic.index, contentDay)"></content-cell>
            </td>
        </tr>

    </table>
</script>

<script id="comment-unit-editor-component" data-props="value" type="text/vue-component">
    <div class="rc-deal-detail-comment-input-area">
        <textarea class="rc-textarea rc-deal-detail-comment-textarea">{{value}}</textarea>
        <div class="rc-deal-detail-comment-input-buttons">
            <span class="rc-comment-button rc-deal-detail-comment-input-button rc-deal-detail-comment-ok-button"
                v-on:click="$emit('comment-add', $event.target)"></span><!--
            --><span class="rc-comment-button rc-deal-detail-comment-input-button rc-deal-detail-comment-cancel-button"
                v-on:click="$emit('stop-comment-add')"></span>
        </div>
    </div>
</script>

<script id="comment-unit-component" data-props="comment, isediting, canedit" type="text/vue-component">
    <comment-unit-editor
        v-bind:value="comment.VALUE"
        v-on:comment-add="$emit('comment-add', $event)"
        v-on:stop-comment-add="$emit('stop-comment-add')"
        v-if="isediting && canedit"></comment-unit-editor>
    <div class="rc-comment-unit" v-else>
        <div class="rc-comment-unit-value">{{comment.VALUE}}</div>
        <div class="rc-comment-unit-buttons" v-if="canedit">
            <span class="rc-comment-button rc-comment-unit-button rc-comment-unit-edit-button"
                v-on:click="$emit('init-edit-comment')"></span><!--
            --><span class="rc-comment-button rc-comment-unit-button rc-comment-unit-remove-button"
                v-on:click="$emit('remove-comment')"></span>
        </div>
        <div class="rc-comment-unit-author">
            <span class="rc-comment-unit-author-value">{{comment.USER_NAME}}</span><!--
            --><span class="rc-comment-unit-author-date">{{authorDate}}</span>
        </div>
    </div>
</script>

<script id="deal-detail-modal-component" data-props="deal, newcomment, bx24inited, comments, editcommentindex, user" type="text/vue-component">
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
        </template>
        <div class="rc-deal-detail-comments">
            <comment-unit
                v-bind:comment="comment"
                v-bind:isediting="editcommentindex === commentIndex"
                v-bind:canedit="bx24inited && user.ID && (comment.USER_ID == user.ID)"
                v-on:comment-add="$emit('comment-add', $event)"
                v-on:stop-comment-add="$emit('stop-comment-add')"
                v-on:init-edit-comment="$emit('init-edit-comment', commentIndex)"
                v-on:remove-comment="$emit('remove-comment', commentIndex)"
                v-for="(comment, commentIndex) in comments"
                v-if="deal.ID == comment.CONTENT_ID"></comment-unit>
            <template v-if="bx24inited">
                <comment-unit-editor
                    v-bind:value="''"
                    v-on:comment-add="$emit('comment-add', $event)"
                    v-on:stop-comment-add="$emit('stop-comment-add')"
                    v-if="newcomment"></comment-unit-editor>
                <span class="rc-deal-detail-add-comment"
                    v-on:click="$emit('init-comment-add')" v-else></span>
            <template>
        </div>
    </div>
</script>

<script id="content-detail-modal-component" data-props="content, bx24inited, newcommentdealindex, editcommentindex, user" type="text/vue-component">
    <div class="rc-content-detail-modal">
        <div class="rc-content-detail-window rc-no-visivility">
            <span class="rc-content-detail-close" v-on:click="$emit('close-detail-modal')"></span>
            <div class="rc-content-detail-title">
                <span class="rc-content-detail-title-date">{{content.DATE}}</span><!--
                --><span class="rc-content-detail-title-value">{{content.NAME}}</span>
            </div>
            <div class="rc-deal-details">           
                <deal-detail-modal
                    v-bind:deal="deal"
                    v-bind:newcomment="newcommentdealindex === dealIndex"
                    v-bind:bx24inited="bx24inited"
                    v-bind:comments="content.COMMENTS"
                    v-bind:editcommentindex="editcommentindex"
                    v-bind:user="user"
                    v-on:init-comment-add="$emit('init-comment-add', dealIndex)"
                    v-on:comment-add="$emit('comment-add', $event)"
                    v-on:stop-comment-add="$emit('stop-comment-add')"
                    v-on:init-edit-comment="$emit('init-edit-comment', $event)"
                    v-on:remove-comment="$emit('remove-comment', $event)"
                    v-for="(deal, dealIndex) in content.DEALS"></deal-detail-modal>
            </div>               
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
            v-on:show-data="showData"
            v-on:set-today="setToday"
            v-on:show-activities="showActivities"
            v-on:set-chosen="setChosen"
            v-on:show-content-details="showContentDetails"
            v-bind:bx24inited="bx24inited"
            v-bind:backtoactivities="backtoactivities"
            v-bind:technics="sortedTechnics"
            v-bind:days="days"></calendar-table>

        <content-detail-modal
            v-on:close-detail-modal="closeDetailModal"
            v-on:init-comment-add="initCommentAdd"
            v-on:comment-add="commentAdd"
            v-on:stop-comment-add="stopCommentAdd"
            v-on:init-edit-comment="initEditComment"
            v-on:remove-comment="removeComment"
            v-bind:content="contentDetail"
            v-bind:bx24inited="bx24inited"
            v-bind:newcommentdealindex="newCommentDealIndex"
            v-bind:editcommentindex="editCommentIndex"
            v-bind:user="userData"
            v-if="contentDetail"></content-detail-modal>
    </template>

    <activity-list
        v-on:remove-activities="removeActivities"
        v-on:show-table="showTable"
        v-on:add-activities="addActivities"
        v-bind:installed="activityInstalled"
        v-bind:activities="activities"
        v-else></activity-list>
</div>