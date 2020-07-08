<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
	$imgFrm->addFormTagAttribute('class', 'web_form');
	$imgFrm->setFormTagAttribute('action', CommonHelper::generateUrl('Profile','uploadProfileImage'));
?>
<div class="sectionbody space">
	<span class="-gap"></span><span class="-gap"></span>
	<div class="avtar avtar--large"><img src="<?php echo CommonHelper::generateUrl('Image','profileImage',array(AdminAuthentication::getLoggedAdminId(),'croped',true));?>" alt=""></div>
	<span class="-gap"></span><span class="-gap"></span>
	<div class="btngroup--fix">
		<?php echo $imgFrm->getFormTag();	?>
		<span class="btn btn--primary btn--sm btn--fileupload">
		<?php echo $imgFrm->getFieldHtml('user_profile_image'); ?>
		</span>
		<?php echo $imgFrm->getFieldHtml('update_profile_img');
		echo $imgFrm->getFieldHtml('rotate_left');
		echo $imgFrm->getFieldHtml('rotate_right');
		echo $imgFrm->getFieldHtml('remove_profile_img');
		echo $imgFrm->getFieldHtml('action');
		echo $imgFrm->getFieldHtml('img_data');
		?>
		</form>
		<?php echo $imgFrm->getExternalJS();?>
		<p><br>
		<a class="themebtn btn-default btn-sm" href="javascript:void(0)" onClick="removeProfileImage()"><?php echo Labels::getLabel('LBL_Remove',$adminLangId);?></a></p>
		<div id="dispMessage"></div>
	</div>
	<span class="-gap"></span><span class="-gap"></span>
</div>
