<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <div class="cols--group">
    <div class="box__head">
        <h4><?php echo Labels::getLabel('LBL_Truncate_Request', $siteLangId); ?></h4>
    </div>
    <div class="box__body">
        <div class="form__subcontent">
            <form class="form form--horizontal">
                <div class=""><?php echo Labels::getLabel('LBL_Truncate_request_approval_will_delete_all_your_data._Truncate_anyway?', $siteLangId); ?></div>
                <div class="gap"></div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input class="btn btn--primary" type="button" name="btn_submit" onclick="sendTruncateRequest()" value="<?php echo Labels::getLabel('LBL_Yes', $siteLangId); ?>">
                        <input class="btn btn--primary-border" onclick="cancelTruncateRequest()" type="button" name="btn_cancel" value="<?php echo Labels::getLabel('LBL_Cancel', $siteLangId); ?>">
                        </div>
                </div>
            </form>
        </div>
    </div>
</div>
