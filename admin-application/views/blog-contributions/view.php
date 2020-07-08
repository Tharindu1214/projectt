<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
<div class="sectionhead">
   
    <h4><?php echo Labels::getLabel('LBL_Contribution_Detail',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
    <div class="border-box border-box--space">
<div class="repeatedrow">
	<div class="rowbody">
		<div class="listview">
			
			<dl class="list">
				<dt><?php echo Labels::getLabel('LBL_Full_Name',$adminLangId); ?></dt>
				<dd><?php echo CommonHelper::displayName($data['bcontributions_author_first_name'].' '.$data['bcontributions_author_last_name']);?></dd>
			</dl>
			<dl class="list">
				<dt><?php echo Labels::getLabel('LBL_Email',$adminLangId); ?></dt>
				<dd><?php echo $data['bcontributions_author_email'];?></dd>
			</dl>
			<dl class="list">
				<dt><?php echo Labels::getLabel('LBL_Phone',$adminLangId); ?></dt>
				<dd><?php echo $data['bcontributions_author_phone'];?></dd>
			</dl>
			<dl class="list">
				<dt><?php echo Labels::getLabel('LBL_Posted_On',$adminLangId); ?></dt>
				<dd><?php echo $data['bcontributions_added_on'];?></dd>
			</dl>
			<dl class="list">
				<dt><?php echo Labels::getLabel('LBL_Status',$adminLangId); ?></dt>
				<dd><?php echo $statusArr[$data['bcontributions_status']];?></dd>
			</dl>
			<?php if(!empty($attachedFile)){?>
			<dl class="list">
				<dt><?php echo Labels::getLabel('LBL_Attached_File',$adminLangId); ?></dt>
				<dd><a target="_new" href="<?php echo CommonHelper::generateUrl('BlogContributions','downloadAttachedFile',array($data['bcontributions_id']));?>" ><?php echo $attachedFile; ?></a></dd>
			</dl>			
			<?php } ?>
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
