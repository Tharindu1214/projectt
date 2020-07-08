<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
	$imgFrm->setFormTagAttribute('action', CommonHelper::generateUrl('Account','uploadProfileImage'));
?>
<div class="col-md-6">
    <div class="avtar avtar--large">
        <?php 
            $userId = UserAuthentication::getLoggedUserId();
            $userImgUpdatedOn = User::getAttributesById($userId, 'user_img_updated_on');
            $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);

            $profileImg = FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'user', array($userId,'thumb',true)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
        ?>
        <img src="<?php echo $profileImg;?>" alt="<?php echo Labels::getLabel('LBL_Profile_Image', $siteLangId);?>">
        <!--img src="<?php /* echo CommonHelper::generateUrl('Account','userProfileImage',array(UserAuthentication::getLoggedUserId(),'croped',true));?>" alt="<?php echo Labels::getLabel('LBL_Profile_Image', $siteLangId); */?>"-->
    </div>
</div>
<div class="col-md-6">
    <div class="btngroup--fix">
        <?php echo $imgFrm->getFormTag();	?>
        <span class="btn btn--primary btn--sm btn--fileupload">
        <?php echo $imgFrm->getFieldHtml('user_profile_image'); ?><?php echo ($mode == 'Edit') ? Labels::getLabel('LBL_Change',$siteLangId): Labels::getLabel('LBL_Upload',$siteLangId) ;?>
        </span>
        <?php echo $imgFrm->getFieldHtml('update_profile_img');
        echo $imgFrm->getFieldHtml('rotate_left');
        echo $imgFrm->getFieldHtml('rotate_right');
        echo $imgFrm->getFieldHtml('remove_profile_img');
        echo $imgFrm->getFieldHtml('action');
        echo $imgFrm->getFieldHtml('img_data');
        ?>
        <?php if($mode == 'Edit'){?>
            <a class="btn btn--primary btn--sm" href="javascript:void(0)" onClick="removeProfileImage()"><?php echo Labels::getLabel('LBL_Remove',$siteLangId);?></a>
         <?php }?>
        </form>
        <?php echo $imgFrm->getExternalJS();?>

        <div id="dispMessage"></div>
    </div>
</div>
