<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
<div class="sectionhead">

    <h4><?php echo Labels::getLabel('LBL_Comment_Details',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
    <div class="border-box border-box--space"><div class="repeatedrow">
    <div class="rowbody">
        <div class="listview">
            <dl class="list">
                <dt><?php echo Labels::getLabel('LBL_Full_Name',$adminLangId); ?></dt>
                <dd><?php echo CommonHelper::displayName($data['bpcomment_author_name']);?></dd>
            </dl>
            <dl class="list">
                <dt><?php echo Labels::getLabel('LBL_Email',$adminLangId); ?></dt>
                <dd><?php echo $data['bpcomment_author_email'];?></dd>
            </dl>
            <dl class="list">
                <dt><?php echo Labels::getLabel('LBL_Posted_On',$adminLangId); ?></dt>
                <dd><?php echo FatDate::format($data['bpcomment_added_on']);?></dd>
            </dl>
            <dl class="list">
                <dt><?php echo Labels::getLabel('LBL_Blog_Post_Title',$adminLangId); ?></dt>
                <dd><?php echo $data['post_title'];?></dd>
            </dl>
            <dl class="list">
                <dt><?php echo Labels::getLabel('LBL_Comment',$adminLangId); ?></dt>
                <dd><?php echo nl2br($data['bpcomment_content']);?></dd>
            </dl>
            <dl class="list">
                <dt><?php echo Labels::getLabel('LBL_User_IP',$adminLangId); ?></dt>
                <dd><?php echo $data['bpcomment_user_ip'];?></dd>
            </dl>
            <dl class="list">
                <dt><?php echo Labels::getLabel('LBL_User_Agent',$adminLangId); ?></dt>
                <dd><?php echo $data['bpcomment_user_agent'];?></dd>
            </dl>
        </div>
    </div>
</div>
<div class="repeatedrow">
    <div class="form_horizontal">

    <h3><i class="ion-person icon"></i><?php echo Labels::getLabel('LBL_Update_Status',$adminLangId); ?></h3>
</div>
    <div class="rowbody">
        <div class="listview">
        <?php
        $frm->setFormTagAttribute('class', 'web_form form_horizontal');
        $frm->developerTags['colClassPrefix'] = 'col-sm-';
        $frm->developerTags['fld_default_col'] = '12';
        $frm->setFormTagAttribute('onsubmit', 'updateStatus(this); return(false);');
        echo $frm->getFormHtml(); ?>
        </div>
    </div>
</div>
</div>
</div>
</section>
