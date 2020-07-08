<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <div class="after-header"></div>
<div id="body" class="body">
    <?php $haveBgImage =AttachedFile::getAttachment(AttachedFile::FILETYPE_SELLER_PAGE_SLOGAN_BG_IMAGE, $slogan['epage_id'], 0, $siteLangId);
    $bgImageUrl = ($haveBgImage) ? "background-image:url(" . CommonHelper::generateUrl('Image', 'cblockBackgroundImage', array($slogan['epage_id'], $siteLangId, 'DEFAULT', AttachedFile::FILETYPE_SELLER_PAGE_SLOGAN_BG_IMAGE)) . ")" : "background-image:url(".CONF_WEBROOT_URL."images/seller-bg.jpg);"; ?>
    <div class="banner" style="<?php echo $bgImageUrl; ?>">
        <div class="container">
            <div class="row justify-content-center">
               <div class="col-xl-6 col-xs-12">
                    <div class="seller-register-form" id="regFrmBlock"> <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?> </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if (!empty($postedData)) {
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSellerAccount'));
}
