<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
    <?php if(!empty($messagesList)){ ?>
        <?php foreach($messagesList as $message){
        $shop_name = '';
        if($message['orrmsg_from_user_id']==$message['op_selprod_user_id'] && $message['shop_identifier']!='')
        {
            $shop_name =' - '.$message['shop_identifier'];
        } ?>
        <li class="is-read">
            <div class="msg_db">
                <div class="avtar">
                    <?php if($message['orrmsg_from_admin_id']){ ?>
                    <img src="<?php echo CommonHelper::generateUrl('Image', 'siteLogo', array( $siteLangId, 'THUMB' )); ?>" title="<?php echo $message['admin_name']; ?>" alt="<?php echo $message['admin_name']; ?>">
                    <?php } else { ?>
                    <img src="<?php echo CommonHelper::generateUrl('Image', 'user', array($message['orrmsg_from_user_id'], 'THUMB', 1)); ?>" title="<?php echo $message['msg_user_name']; ?>" alt="<?php echo $message['msg_user_name']; ?>">
                    <?php } ?>
                </div>
            </div>
            <div class="msg__desc">
                <span class="msg__title"><?php echo ($message['orrmsg_from_admin_id']) ? $message['admin_name']: $message['msg_user_name'].$shop_name; ?></span>
                <p class="msg__detail"><?php echo nl2br($message['orrmsg_msg']); ?></p>
                <span class="msg__date"><?php echo FatDate::format($message['orrmsg_date'], true); ?></span>
            </div>
        </li>
        <?php } ?>
    <?php
        $postedData['page'] = $page;
        echo FatUtility::createHiddenFormFromData ( $postedData, array ('name' => 'frmOrderReturnRequestMsgsSrchPaging') );

    } else {
        //echo Labels::getLabel('MSG_No_Record_Found', $siteLangId);
    } ?>