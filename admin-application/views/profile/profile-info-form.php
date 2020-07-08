<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
	$imgFrm->addFormTagAttribute('class', 'web_form');
	$imgFrm->setFormTagAttribute('action', CommonHelper::generateUrl('Profile','uploadProfileImage'));

	$userNameFld = $frm->getField('admin_username');
	$userNameFld->addFieldTagAttribute('id','admin_username');

	$emailFld = $frm->getField('admin_email');
	$emailFld->addFieldTagAttribute('id','admin_email');

	$frm->setFormTagAttribute('id', 'profileInfoFrm');
	$frm->setFormTagAttribute('class','web_form');
	$frm->developerTags['colClassPrefix'] = 'col-md-';
	$frm->developerTags['fld_default_col'] = 12;
	$frm->setFormTagAttribute('onsubmit', 'updateProfileInfo(this); return(false);');
?>
<div class="col-lg-4 col-md-4 col-sm-4">

	<div class="section section--profile-box -align-center" id="profileImageFrmBlock">
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
	</div>
</div>

<div class="col-lg-8 col-md-8 col-sm-8">
	<div class="section">
		<div class="sectionbody space">
			<div class="repeated-row">
			<h5><?php echo Labels::getLabel('LBL_My_Profile',$adminLangId);?></h5>
			<?php echo $frm->getFormHtml();?>
			</div>
		</div>
	</div>
</div>
