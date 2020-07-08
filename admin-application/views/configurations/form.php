<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = '12';

if($lang_id > 0){
    $frm->setFormTagAttribute('onsubmit', 'setupLang(this); return(false);');
}
else{
    $frm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
}
    $tbid = isset($tabId)?$tabId:'tabs_'.$frmType;
    switch ($frmType){
    case Configurations::FORM_GENERAL:
        /* if( $lang_id == 0 ){
            $adminLogoFld = $frm->getField('admin_logo');
            $desktopLogoFld = $frm->getField('front_logo');
            $emailLogoFld = $frm->getField('email_logo');
            $faviconFld = $frm->getField('favicon');

            $adminLogoFld->htmlAfterField =  '<span class = "uploadimage--info" >Dimensions 142*45</span>';
            $desktopLogoFld->htmlAfterField = '<span class = "uploadimage--info" >Dimensions 168*37</span>';
            $emailLogoFld->htmlAfterField = '<span class = "uploadimage--info" >Dimensions 168*37</span>';
            if( isset($adminLogo) && !empty($adminLogo) ){
                $adminLogoFld->htmlAfterField .= '<div class="uploaded--image"><img src="'.CommonHelper::generateFullUrl('Image','siteAdminLogo',array('THUMB')).'"> <a  class="remove--img" href="javascript:void(0);" onclick="removeSiteAdminLogo()" ><i class="ion-close-round"></i></a></div>';
            }

            if( isset($desktopLogo) && !empty($desktopLogo) ){
                $desktopLogoFld->htmlAfterField .= '<div class="uploaded--image"><img src="'.CommonHelper::generateFullUrl('Image','siteLogo',array(''), CONF_WEBROOT_FRONT_URL).'"> <a  class="remove--img" href="javascript:void(0);" onclick="removeDesktopLogo()" ><i class="ion-close-round"></i></a></div>';
            }

            if( isset($emailLogo) && !empty($emailLogo) ){
                $emailLogoFld->htmlAfterField .= '<div class="uploaded--image"><img src="'.CommonHelper::generateFullUrl('Image','emailLogo',array(''), CONF_WEBROOT_FRONT_URL).'"><a  class="remove--img" href="javascript:void(0);" onclick="removeEmailLogo()" ><i class="ion-close-round"></i></a></div>';
            }

            if( isset($favicon) && !empty($favicon) ){
                $faviconFld->htmlAfterField = '<div class="uploaded--image"><img src="'.CommonHelper::generateFullUrl('Image','favicon',array(''), CONF_WEBROOT_FRONT_URL).'"> <a  class="remove--img" href="javascript:void(0);" onclick="removeFavicon()" ><i class="ion-close-round"></i></a></div>';
            }
        } */
    break;
    case Configurations::FORM_MEDIA:

    break;

    case Configurations::FORM_DISCOUNT:
    $discountValue = $frm->getField('CONF_FIRST_TIME_BUYER_COUPON_DISCOUNT_VALUE');
    $discountValue->requirements()->setRange(0, $record['CONF_FIRST_TIME_BUYER_COUPON_MIN_ORDER_VALUE']);
    break;
}

?>
<ul class="tabs_nav innerul abc">
    <?php if( $frmType == Configurations::FORM_IMPORT_EXPORT){ ?>
        <li><a href="javascript:void(0);" onclick="generalInstructions(<?php echo $frmType;?>);"><?php echo Labels::getLabel('LBL_Instructions',$adminLangId); ?></a></li>
    <?php } ?>
    <?php if( $frmType != Configurations::FORM_MEDIA && $frmType != Configurations::FORM_SHARING ){ ?>
        <li><a class="<?php echo ($lang_id == 0) ? 'active' : ''; ?>" href="javascript:void(0)" onClick="getForm(<?php echo $frmType;?>,'<?php echo $tbid;?>')">Basic</a></li>
    <?php } ?>
    <?php
    if( $dispLangTab ){
        foreach( $languages as $langId => $langName ){ ?>
            <li><a href="javascript:void(0);" class="<?php echo ($lang_id == $langId) ? 'active' : '' ; ?>" onClick="getLangForm(<?php echo $frmType;?>,<?php echo $langId;?>,'<?php echo $tbid; ?>')"><?php echo $langName; ?></a></li>
        <?php }
    } ?>
</ul>
<div class="tabs_panel_wrap">
    <?php echo $frm->getFormHtml();?>
</div>
