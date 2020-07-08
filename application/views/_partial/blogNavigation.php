<div class="main-bar-blog">
    <div class="container">
        <a class="navs_toggle" href="javascript:void(0)"><span></span></a>
        <div class="header-blog-inner">

            <div class="logo">
                <a href="<?php echo CommonHelper::generateUrl('Blog'); ?>">
                    <img src="<?php echo CommonHelper::generateFullUrl('Image', 'siteLogo', array($siteLangId), CONF_WEBROOT_FRONT_URL); ?>"
                        alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId); ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>">
                </a>
            </div>
            <div class="main-search">
                <a href="javascript:void(0)" class="toggle--search toggle--search-js"> <span class="icn"></span></a>
                <div class="form--search form--search-popup">
                    <a id="close-search-popup-js" class="close-layer d-xl-none d-lg-none" href="javascript:void(0)"></a>
                    <?php $srchFrm->setFormTagAttribute('onSubmit', 'submitBlogSearch(this); return(false);');
                    $srchFrm->setFormTagAttribute('class', 'main-search-form');
                    $srchFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
                    $srchFrm->developerTags['fld_default_col'] = 12;
                    $keywordFld = $srchFrm->getField('keyword');
                    $keywordFld->setFieldTagAttribute('class', 'search--keyword search--keyword--js no--focus');
                    $keywordFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Search_In_Blogs...', $siteLangId));
                    $submitFld = $srchFrm->getField('btnProductSrchSubmit');
                    $submitFld->setFieldTagAttribute('class', 'search--btn submit--js');
                    echo $srchFrm->getFormTag();
                    echo $srchFrm->getFieldHTML('keyword');
                    echo $srchFrm->getFieldHTML('btnProductSrchSubmit');
                    echo $srchFrm->getExternalJS(); ?>
                    </form>
                </div>
            </div>
            <div class="backto"><a href="<?php echo CommonHelper::generateUrl(); ?>"><?php echo Labels::getLabel('LBL_Shop', $siteLangId).' '. FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId);  ?> <svg class="icn-arrow" x="0px" y="0px" viewBox="0 0 31.49 31.49" style="enable-background:new 0 0 31.49 31.49;" xml:space="preserve" width="512px" height="512px">
                <path
                    d="M21.205,5.007c-0.429-0.444-1.143-0.444-1.587,0c-0.429,0.429-0.429,1.143,0,1.571l8.047,8.047H1.111  C0.492,14.626,0,15.118,0,15.737c0,0.619,0.492,1.127,1.111,1.127h26.554l-8.047,8.032c-0.429,0.444-0.429,1.159,0,1.587  c0.444,0.444,1.159,0.444,1.587,0l9.952-9.952c0.444-0.429,0.444-1.143,0-1.571L21.205,5.007z" />
            </svg></a></div>
        </div>
    </div>
</div>
<div class="last-bar">
    <div class="container">
        <?php if (!empty($categoriesArr)) {
            $noOfCharAllowedInNav = 60;
            $navLinkCount = 0;
            foreach ($categoriesArr as $cat) {
                if (!$cat) {
                    break;
                }
                $noOfCharAllowedInNav = $noOfCharAllowedInNav - mb_strlen($cat);
                if ($noOfCharAllowedInNav < 0) {
                    break;
                }
                $navLinkCount++;
            } ?>
        <div class="navigations__overlayx"></div>
        <div class="navigation-wrapper">
            <ul class="navigations <?php echo ($navLinkCount > 4) ? 'justify-content-between' : '' ; ?>">
                <!--<li><a href="<?php /*echo CommonHelper::generateUrl('Blog'); ?>"><?php echo Labels::getLabel('LBL_Blog_Home', $siteLangId);*/ ?></a> </li>-->
                <?php $mainNavigation = array_slice($categoriesArr, 0, $navLinkCount, true);
                foreach ($mainNavigation as $id => $cat) { ?>
                <li><a href="<?php echo CommonHelper::generateUrl('Blog', 'category', array($id)); ?>"><?php echo $cat; ?></a> </li>
                <?php }?>
            </ul>
        </div>
        <?php } ?>
    </div>
</div>
