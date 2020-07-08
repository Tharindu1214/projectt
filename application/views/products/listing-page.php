<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (empty($products)) {
    $pSrchFrm = Common::getSiteSearchForm();
    $pSrchFrm->fill(array('btnSiteSrchSubmit' => Labels::getLabel('LBL_Submit', $siteLangId)));
    $pSrchFrm->setFormTagAttribute('onSubmit', 'submitSiteSearch(this); return(false);');

    $this->includeTemplate('_partial/no-product-found.php', array('pSrchFrm'=>$pSrchFrm,'siteLangId'=>$siteLangId,'postedData'=>$postedData), true);
    return;
}

$frmProductSearch->setFormTagAttribute('onSubmit', 'searchProducts(this); return(false);');
$keywordFld = $frmProductSearch->getField('keyword');
$keywordFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Search', $siteLangId));
$keywordFld = $frmProductSearch->getField('keyword');
$keywordFld->overrideFldType("hidden");
$desktop_url = '';
$tablet_url = '';
$mobile_url = '';
if (!empty($category['banner'])) {
    $catBannerArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_CATEGORY_BANNER, $category['prodcat_id'], 0, $siteLangId);
    foreach ($catBannerArr as $slideScreen) {
        $uploadedTime = AttachedFile::setTimeParam($slideScreen['afile_updated_at']);
        switch ($slideScreen['afile_screen']) {
            case applicationConstants::SCREEN_MOBILE:
                $mobile_url = '<736:' .FatCache::getCachedUrl(CommonHelper::generateUrl('Category', 'Banner', array($category['prodcat_id'], $siteLangId, 'MOBILE', applicationConstants::SCREEN_MOBILE)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg').",";
                break;
            case applicationConstants::SCREEN_IPAD:
                $tablet_url = ' >768:' .FatCache::getCachedUrl(CommonHelper::generateUrl('Category', 'Banner', array($category['prodcat_id'], $siteLangId, 'TABLET', applicationConstants::SCREEN_IPAD)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg').",";
                break;
            case applicationConstants::SCREEN_DESKTOP:
                $desktop_url = ' >1025:' .FatCache::getCachedUrl(CommonHelper::generateUrl('Category', 'Banner', array($category['prodcat_id'], $siteLangId, 'DESKTOP', applicationConstants::SCREEN_DESKTOP)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg').",";
                break;
            }
    } ?>
<section class="bg-shop">
    <div class="shop-banner"><img data-ratio="4:1" data-src-base="" data-src-base2x="" data-src="<?php echo $mobile_url . $tablet_url  . $desktop_url; ?>"
            src="<?php echo CommonHelper::generateUrl('Category', 'Banner', array($category['prodcat_id'],$siteLangId,'DESKTOP',applicationConstants::SCREEN_DESKTOP)); ?>"></div>
    <?php /* if (!empty($category['prodcat_description']) && array_key_exists('prodcat_description', $category)) { ?>
    <div class="page-category__content">
        <p><?php  echo FatUtility::decodeHtmlEntities($category['prodcat_description']); ?></p>
    </div>
    <?php } */ ?>
</section>
<?php }
if (array_key_exists('brand_id', $postedData) && $postedData['brand_id'] > 0) {
    $brandImgArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BRAND_IMAGE, $postedData['brand_id'], 0, $siteLangId);
    foreach ($brandImgArr as $slideScreen) {
        $uploadedTime = AttachedFile::setTimeParam($slideScreen['afile_updated_at']);
        switch ($slideScreen['afile_screen']) {
            case applicationConstants::SCREEN_MOBILE:
                $mobile_url = '<736:' .FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'BrandImage', array($postedData['brand_id'], $siteLangId, 'MOBILE', 0, applicationConstants::SCREEN_MOBILE)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg').",";
                break;
            case applicationConstants::SCREEN_IPAD:
                $tablet_url = ' >768:' .FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'BrandImage', array($postedData['brand_id'], $siteLangId, 'TABLET', 0, applicationConstants::SCREEN_IPAD)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg').",";
                break;
            case applicationConstants::SCREEN_DESKTOP:
                $desktop_url = ' >1025:' .FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'BrandImage', array($postedData['brand_id'], $siteLangId, 'DESKTOP', 0, applicationConstants::SCREEN_DESKTOP)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg').",";
                break;
            }
        } ?>
<section class="bg-shop">
    <div class="shop-banner"><img data-ratio="4:1" data-src-base="" data-src-base2x="" data-src="<?php echo $mobile_url . $tablet_url  . $desktop_url; ?>"
            src="<?php echo CommonHelper::generateUrl('Image', 'BrandImage', array($postedData['brand_id'],$siteLangId,'DESKTOP',0,applicationConstants::SCREEN_DESKTOP)); ?>"></div>
</section>
<?php } ?>

<?php if (isset($pageTitle)) { ?>
<section class="bg--second pt-3 pb-3">
    <div class="container">
        <div class="section-head section--white--head section--head--center mb-0">
            <div class="section__heading">
                <h2 class="mb-0">
                    <?php $keywordStr = '';
                if (isset($keyword) && !empty($keyword)) {
                    $short_keyword = (mb_strlen($keyword) > 20) ? mb_substr($keyword, 0, 20)."..." : $keyword;
                    $keywordStr = '<span title="'.$keyword.'" class="search-results">"'.$short_keyword.'"</span>';
                }
                echo $pageTitle; ?> <?php echo $keywordStr; ?></h2>
                <?php if (isset($showBreadcrumb) && $showBreadcrumb) { ?>
                <div class="breadcrumbs breadcrumbs--white breadcrumbs--center">
                    <?php $this->includeTemplate('_partial/custom/header-breadcrumb.php'); ?>
                </div>
                <?php }?>
            </div>
        </div>
    </div>
</section>
<?php } ?>
<?php $this->includeTemplate('_partial/productsSearchForm.php', array('frmProductSearch'=>$frmProductSearch,'siteLangId'=>$siteLangId,'recordCount'=>$recordCount,'pageTitle'=>(isset($pageTitle)) ? $pageTitle : 'Products'), false);  ?>
<section class="section">
    <div class="container">
        <div class="row">
            <div class="col-xl-3 col-lg-12">
                <?php if (isset($shop)) { ?>
                <div class="bg-gray rounded shop-information">
                    <div class="shop-logo"><img data-ratio="1:1 (150x150)" src="<?php echo CommonHelper::generateUrl('image', 'shopLogo', array($shop['shop_id'], $siteLangId, 'SMALL')); ?>" alt="<?php echo $shop['shop_name']; ?>"></div>
                    <div class="shop-info">
                        <div class="shop-name">
                            <h5>
                                <?php echo $shop['shop_name']; ?>
                                <span class="blk-txt"><?php echo Labels::getLabel('LBL_Shop_Opened_On', $siteLangId); ?> <strong> <?php $date = new DateTime($shop['user_regdate']);
                                echo $date->format('M d, Y'); ?> </strong></span>
                            </h5>
                        </div>
                        <?php if (0 < FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?>
                        <div class="products__rating"> <i class="icn"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
                                </svg></i> <span class="rate"><?php echo round($shopRating, 1),' ',Labels::getLabel('Lbl_Out_of', $siteLangId),' ', '5';
                                if ($shopTotalReviews) { ?>
                                - <a href="<?php echo CommonHelper::generateUrl('Reviews', 'shop', array($shop['shop_id'])); ?>"><?php echo $shopTotalReviews, ' ', Labels::getLabel('Lbl_Reviews', $siteLangId); ?></a>
                                <?php } ?> </span>
                        </div>
                        <?php } ?>
                        <div class="shop-btn-group">
                            <div class="share-button">
                                <a href="javascript:void(0)" class="social-toggle" title="<?php echo Labels::getLabel('Lbl_Share', $siteLangId); ?>"><i class="icn">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share"></use>
                                        </svg>
                                    </i></a>
                                <div class="social-networks">
                                    <ul>
                                        <li class="social-facebook">
                                            <a class="social-link st-custom-button" data-network="facebook" data-url="<?php echo CommonHelper::generateFullUrl('Shops', 'view', array($shop['shop_id'])); ?>/">
                                                <i class="icn"><svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb"></use>
                                                    </svg></i>
                                            </a>
                                        </li>
                                        <li class="social-twitter">
                                            <a class="social-link st-custom-button" data-network="twitter">
                                                <i class="icn"><svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw"></use>
                                                    </svg></i>
                                            </a>
                                        </li>
                                        <li class="social-pintrest">
                                            <a class="social-link st-custom-button" data-network="pinterest">
                                                <i class="icn"><svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt"></use>
                                                    </svg></i>
                                            </a>
                                        </li>
                                        <li class="social-email">
                                            <a class="social-link st-custom-button" data-network="email">
                                                <i class="icn"><svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope"></use>
                                                    </svg></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <?php $showAddToFavorite = true;
                            if (UserAuthentication::isUserLogged() && (!User::isBuyer())) {
                                $showAddToFavorite = false;
                            }
                            ?>
                            <?php if ($showAddToFavorite) { ?>
                            <a href="javascript:void(0)" title="<?php echo ($shop['is_favorite']) ? Labels::getLabel('Lbl_Unfavorite_Shop', $siteLangId) : Labels::getLabel('Lbl_Favorite_Shop', $siteLangId); ?>"
                                onclick="toggleShopFavorite(<?php echo $shop['shop_id']; ?>);" class="btn btn--primary btn--sm <?php echo ($shop['is_favorite']) ? 'is-active' : ''; ?>" id="shop_<?php echo $shop['shop_id']; ?>"><i class="icn"><svg
                                        class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#heart" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#heart"></use>
                                    </svg></i></a>
                            <?php }?>
                            <?php $showMoreButtons = true; if (UserAuthentication::isUserLogged() && UserAuthentication::getLoggedUserId(true) == $shop['shop_user_id']) {
                                $showMoreButtons = false;
                            } ?>
                            <?php if ($showMoreButtons) { ?>
                            <a href="<?php echo CommonHelper::generateUrl('Shops', 'ReportSpam', array($shop['shop_id'])); ?>" title="<?php echo Labels::getLabel('Lbl_Report_Spam', $siteLangId); ?>" class="btn btn--primary btn--sm"><i
                                    class="icn"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#report" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#report"></use>
                                    </svg></i></a>
                            <?php if (!UserAuthentication::isUserLogged() || (UserAuthentication::isUserLogged() && ((User::isBuyer()) || (User::isSeller() )))) { ?>
                            <a href="<?php echo CommonHelper::generateUrl('shops', 'sendMessage', array($shop['shop_id'])); ?>" title="<?php echo Labels::getLabel('Lbl_Send_Message', $siteLangId); ?>" class="btn btn--primary btn--sm"><i
                                    class="icn"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#send-msg" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#send-msg"></use>
                                    </svg></i></a>
                            <?php } ?>
                            <?php } ?>
                        </div>
                        <?php if ($socialPlatforms) { ?>
                        <div class="social-profiles mt-3">
                            <p><strong><?php echo Labels::getLabel('LBL_Follow_Us', $siteLangId); ?></strong> </p>
                            <ul class="social-icons">
                                <?php foreach ($socialPlatforms as $row) { ?>
                                <li><a <?php if ($row['splatform_url']!='') { ?> target="_blank" <?php } ?>
                                    href="<?php echo ($row['splatform_url']!='') ? $row['splatform_url']:'javascript:void(0)'; ?>"><i class="fa fa-<?php echo $row['splatform_icon_class']; ?>"></i></a></li>
                                <?php } ?>
                            </ul>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="gap"></div>
                <?php } ?>
                <?php if (array_key_exists('brand_id', $postedData) && $postedData['brand_id'] > 0) {
                    ?> <div class="brands-block-wrapper">
                    <div class="brands-block">
                        <img src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'brand', array($postedData['brand_id'] , $siteLangId, 'COLLECTION_PAGE')), CONF_IMG_CACHE_TIME, '.jpg'); ?>">
                    </div>
                </div> <?php
                } ?>
                <div class="filters bg-gray rounded">
                    <div class="filters__ele productFilters-js"></div>
                </div>
            </div>
            <div class="col-xl-9 col-lg-12">
                <div class="row align-items-center justify-content-between  flex-column flex-md-row mb-3">
                    <div class="col mb-3 mb-md-0">
                        <div class="total-products">
                            <span class="hide_on_no_product"><span id="total_records"><?php echo $recordCount;?></span> <?php echo Labels::getLabel('LBL_ITEM(S)', $siteLangId); ?></span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div id="top-filters" class="page-sort hide_on_no_product">
                            <ul>
                                <li class="list__item">
                                    <a href="javascript:void(0)" class="link__filter btn btn--secondary-border d-xl-none btn--filters-control"><i class="icn">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#filter" href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#filter"></use>
                                            </svg>
                                        </i><span class="txt"><?php echo Labels::getLabel('LBL_Filter', $siteLangId); ?></span></a>
                                    <?php if (!(UserAuthentication::isUserLogged()) || (UserAuthentication::isUserLogged() && (User::isBuyer()))) { ?>
                                    <a href="javascript:void(0)" onclick="saveProductSearch()" class="btn btn--primary-border btn--filters-control"><i class="icn">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#savesearch" href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#savesearch"></use>
                                            </svg>
                                        </i><span class="txt"><?php echo Labels::getLabel('LBL_Save_Search', $siteLangId); ?></span></a>
                                    <?php } ?>
                                </li>
                                <li>
                                    <?php echo $frmProductSearch->getFieldHtml('sortBy'); ?></li>
                                <li>
                                    <?php echo $frmProductSearch->getFieldHtml('pageSize'); ?></li>
                                <li class="d-none d-md-block">
                                    <div class="list-grid-toggle switch--link-js">
                                        <div class="icon">
                                            <div class="icon-bar"></div>
                                            <div class="icon-bar"></div>
                                            <div class="icon-bar"></div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="listing-products -listing-products ">
                    <div id="productsList" role="main-listing" class="row product-listing">
                        <?php
                        $productsData = array(
                                        'products'=> $products,
                                        'page'=> $page,
                                        'pageCount'=> $pageCount,
                                        'postedData'=> $postedData,
                                        'recordCount'=> $recordCount,
                                        'siteLangId'=> $siteLangId,
                                    );
                        $this->includeTemplate('products/products-list.php', $productsData, false);
                    ?> </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section>
    <div class="container">
        <div class="row">
            <div class="col-md-3 col--left col--left-adds">
                <div class="wrapper--adds">
                    <div class="grids" id="searchPageBanners">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="gap"></div>
<script type="text/javascript">
    $(document).ready(function() {
        $currentPageUrl = '<?php echo $canonicalUrl; ?>';
        $productSearchPageType = '<?php echo $productSearchPageType; ?>';
        $recordId = <?php echo $recordId; ?>;
        bannerAdds('<?php echo $bannerListigUrl;?>');
        loadProductListingfilters(document.frmProductSearch);
    });
</script>
