<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$buyQuantity = $frmBuyProduct->getField('quantity');
$buyQuantity->addFieldTagAttribute('class', 'qty-input cartQtyTextBox productQty-js');
$buyQuantity->addFieldTagAttribute('data-page', 'product-view');
?>
<div id="body" class="body detail-page" role="main">
    <section class="">
        <div class="container">
            <div class="section">
                <div class="breadcrumbs breadcrumbs--center">
                    <?php  $this->includeTemplate('_partial/custom/header-breadcrumb.php');  ?>
                </div>
            </div>
            <div class="detail-wrapper">
                <div class="detail-first-fold ">
                    <div class="row justify-content-between">
                        <div class="col-lg-7 relative">
                            <div id="img-static" class="product-detail-gallery">
                                <?php $data['product'] = $product;
                                    $data['productImagesArr'] = $productImagesArr;
                                    $data['imageGallery'] = true;
                                    /* $this->includeTemplate('products/product-gallery.php',$data,false); */ ?>
                                <div class="slider-for" dir="<?php echo CommonHelper::getLayoutDirection();?>" id="slider-for">
                                    <?php if ($productImagesArr) { ?>
                                    <?php foreach ($productImagesArr as $afile_id => $image) {
                                        $originalImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'product', array($product['product_id'], 'ORIGINAL', 0, $image['afile_id'] )), CONF_IMG_CACHE_TIME, '.jpg');
                                        $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'product', array($product['product_id'], 'MEDIUM', 0, $image['afile_id'] )), CONF_IMG_CACHE_TIME, '.jpg');
                                        $thumbImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'product', array($product['product_id'], 'THUMB', 0, $image['afile_id'] )), CONF_IMG_CACHE_TIME, '.jpg'); ?>
                                    <img class="xzoom active" id="xzoom-default" src="<?php echo $mainImgUrl; ?>" xoriginal="<?php echo $originalImgUrl; ?>">
                                    <?php break;
                                    } ?>
                                    <?php } else {
                                        $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'product', array(0, 'MEDIUM', 0 )), CONF_IMG_CACHE_TIME, '.jpg');
                                        $originalImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'product', array(0, 'ORIGINAL', 0 )), CONF_IMG_CACHE_TIME, '.jpg'); ?>
                                    <img class="xzoom" src="<?php echo $mainImgUrl; ?>" xoriginal="<?php echo $originalImgUrl; ?>">
                                    <?php
                                    } ?>
                                </div>
                                <?php if ($productImagesArr) { ?>
                                <div class="slider-nav xzoom-thumbs" dir="<?php echo CommonHelper::getLayoutDirection();?>" id="slider-nav">
                                    <?php foreach ($productImagesArr as $afile_id => $image) {
                                        $originalImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'product', array($product['product_id'], 'ORIGINAL', 0, $image['afile_id'] )), CONF_IMG_CACHE_TIME, '.jpg');
                                        $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'product', array($product['product_id'], 'MEDIUM', 0, $image['afile_id'] )), CONF_IMG_CACHE_TIME, '.jpg');
                                        /* $thumbImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'product', array($product['product_id'], 'THUMB', 0, $image['afile_id']) ), CONF_IMG_CACHE_TIME, '.jpg'); */ ?>
                                    <div class="thumb"><a href="<?php echo $originalImgUrl; ?>"><img class="xzoom-gallery" width="80" src="<?php echo $mainImgUrl; ?>"></a></div>
                                    <?php
                                    } ?>
                                </div>
                                <?php } ?>


                        </div>
						</div>
                        <div class="col-lg-5 col-details-right">
                            <div class="product-description">
                                <div class="product-description-inner">
                                    <div class="">
                                        <div class="products__title">
                                            <div clss="">
											<h2><?php echo $product['selprod_title'];?></h2>
											 <div class="favourite-wrapper favourite-wrapper-detail ">
                                <?php include(CONF_THEME_PATH.'_partial/collection-ui.php'); ?>
                                <div class="share-button">
                                    <a href="javascript:void(0)" class="social-toggle"><i class="icn">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share"></use>
                                            </svg>
                                        </i></a>
                                    <div class="social-networks">
                                        <ul>
                                            <li class="social-facebook">
                                                <a class="st-custom-button" data-network="facebook" data-url="<?php echo CommonHelper::generateFullUrl('Products', 'view', array($product['selprod_id'])); ?>/">
                                                    <i class="icn"><svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb"></use>
                                                        </svg></i>
                                                </a>
                                            </li>
                                            <li class="social-twitter">
                                                <a class="st-custom-button" data-network="twitter">
                                                    <i class="icn"><svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw"></use>
                                                        </svg></i>
                                                </a>
                                            </li>
                                            <li class="social-pintrest">
                                                <a class="st-custom-button" data-network="pinterest">
                                                    <i class="icn"><svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt"></use>
                                                        </svg></i>
                                                </a>
                                            </li>
                                            <li class="social-email">
                                                <a class="st-custom-button" data-network="email">
                                                    <i class="icn"><svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope"></use>
                                                        </svg></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
											</div>
                                            <?php if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?>
                                            <?php /*if (round($product['prod_rating']) > 0) {*/ ?>
                                            <?php $label = (round($product['prod_rating']) > 0) ? round($product['totReviews'], 1).' '.Labels::getLabel('LBL_Reviews', $siteLangId) : Labels::getLabel('LBL_No_Reviews', $siteLangId); ?>
                                            <div class="products-reviews">
											<div class="products__rating">
											<i class="icn"><svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
                                            </svg>
											</i>
                                            <span class="rate"><?php echo round($product['prod_rating'], 1);?></span>
											</div>
                                            <a href="#itemRatings" class="totals-review link nav-scroll-js"><?php echo $label; ?></a>
                                            </div>
                                            <?php /*}*/ ?>
                                            <?php /* if (round($product['prod_rating']) == 0) {  ?>
                                            <span class="be-first"> <a href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Be_the_first_to_review_this_product', $siteLangId); ?> </a> </span>
                                            <?php } */ ?>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="brand-data"><span class="txt-gray-light"><?php echo Labels::getLabel('LBL_Brand', $siteLangId); ?>:</span> <?php echo $product['brand_name'];?></div>
                                    <div class="col products__price"><?php echo CommonHelper::displayMoneyFormat($product['theprice']); ?>
                                        <?php if ($product['special_price_found']) { ?>
                                        <span class="products__price_old"><?php echo CommonHelper::displayMoneyFormat($product['selprod_price']); ?></span>
                                        <span class="product_off"><?php echo CommonHelper::showProductDiscountedText($product, $siteLangId); ?></span>
                                        <?php } ?>
                                    </div>

                                    <!--<div class="detail-grouping">
                                        <div class="products__category"><a href="<?php echo CommonHelper::generateUrl('Category', 'View', array($product['prodcat_id']));?>"><?php echo $product['prodcat_name'];?> </a></div>
                                    </div>-->

                                    <?php /* include(CONF_THEME_PATH.'_partial/product-listing-head-section.php'); */ ?>

                                    <?php  if ($shop['shop_free_ship_upto'] > 0 && Product::PRODUCT_TYPE_PHYSICAL == $product['product_type']) { ?>
                                    <?php $freeShipAmt = CommonHelper::displayMoneyFormat($shop['shop_free_ship_upto']); ?>
                                    <div class="note-messages"><?php echo str_replace('{amount}', $freeShipAmt, Labels::getLabel('LBL_Free_shipping_up_to_{amount}_purchase', $siteLangId));?></div>
                                    <?php }?>
                                    <div class="divider"></div>
                                    <?php if (!empty($optionRows)) { ?>
                                    <div class="gap"> </div>
                                    <div class="row">
                                        <?php $selectedOptionsArr = $product['selectedOptionValues'];
                                /*CommonHelper::printArray($selectedOptionsArr);
                                CommonHelper::printArray($optionRows);*/
                                $count = 0;
                                foreach ($optionRows as $key => $option) {
                                    $selectedOptionValue = $option['values'][$selectedOptionsArr[$key]]['optionvalue_name'];
                                    $selectedOptionColor = $option['values'][$selectedOptionsArr[$key]]['optionvalue_color_code']; ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="h6"><?php echo $option['option_name']; ?></div>
                                            <div class="js-wrap-drop wrap-drop" id="js-wrap-drop<?php echo $count; ?>">
                                                <span>
                                                    <?php if ($option['option_is_color']) { ?>
                                                    <span class="colors" style="background-color:#<?php echo $selectedOptionColor; ?>; ?>;"></span>
                                                    <?php } ?>
                                                    <?php echo $selectedOptionValue; ?></span>
                                                <?php if ($option['values']) { ?>
                                                <ul class="drop">
                                                    <?php foreach ($option['values'] as $opVal) {
                                                $isAvailable = true;
                                                if (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) {
                                                    $optionUrl = CommonHelper::generateUrl('Products', 'view', array($product['selprod_id']));
                                                } else {
                                                    $optionUrl = Product::generateProductOptionsUrl($product['selprod_id'], $selectedOptionsArr, $option['option_id'], $opVal['optionvalue_id'], $product['product_id']);
                                                    $optionUrlArr = explode("::", $optionUrl);
                                                    if (is_array($optionUrlArr) && count($optionUrlArr) == 2) {
                                                        $optionUrl = $optionUrlArr[0];
                                                        $isAvailable = false;
                                                    }
                                                } ?>
                                                    <li class="<?php echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? ' selected' : ' ';
                                            echo (!$optionUrl) ? ' is-disabled' : '';
                                            echo (!$isAvailable) ? 'not--available':''; ?>">
                                                        <?php if ($option['option_is_color'] && $opVal['optionvalue_color_code'] != '') { ?>
                                                        <a optionValueId="<?php echo $opVal['optionvalue_id']; ?>" selectedOptionValues="<?php echo implode("_", $selectedOptionsArr); ?>" title="<?php echo $opVal['optionvalue_name'];
                                                    echo (!$isAvailable) ? ' '.Labels::getLabel('LBL_Not_Available', $siteLangId) : ''; ?>" class="<?php echo (!$option['option_is_color']) ? 'selector__link' : '';
                                                    echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? ' ' : ' ';
                                                    echo (!$optionUrl) ? ' is-disabled' : '';  ?>" href="<?php echo ($optionUrl) ? $optionUrl : 'javascript:void(0)'; ?>"> <span class="colors"
                                                                style="background-color:#<?php echo $opVal['optionvalue_color_code']; ?>;"></span><?php echo $opVal['optionvalue_name'];?></a>
                                                        <?php } else { ?>
                                                        <a optionValueId="<?php echo $opVal['optionvalue_id']; ?>" selectedOptionValues="<?php echo implode("_", $selectedOptionsArr); ?>" title="<?php echo $opVal['optionvalue_name'];
                                                    echo (!$isAvailable) ? ' '.Labels::getLabel('LBL_Not_Available', $siteLangId) : ''; ?>"
                                                            class="<?php echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? '' : ' '; echo (!$optionUrl) ? ' is-disabled' : '' ?>"
                                                            href="<?php echo ($optionUrl) ? $optionUrl : 'javascript:void(0)'; ?>">
                                                            <?php echo $opVal['optionvalue_name'];  ?> </a>
                                                        <?php } ?>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <?php $count++;
                                }?>
                                    </div>
                                    <?php }?>

                                    <?php /*if (count($productSpecifications) > 0) { ?>
                                    <div class="gap"></div>
                                    <div class="box box--gray box--radius box--space">
                                        <div class="h6"><?php echo Labels::getLabel('LBL_Specifications', $siteLangId); ?>:</div>
                                        <div class="list list--specification">
                                            <ul>
                                                <?php $count=1;
                                    foreach ($productSpecifications as $key => $specification) {
                                        if ($count > 5) {
                                            continue;
                                        } ?>
                                                <li><?php echo '<span>'.$specification['prodspec_name']." :</span> ".$specification['prodspec_value']; ?></li>
                                                <?php $count++;
                                    } ?>
                                                <?php if (count($productSpecifications)>5) { ?>
                                                <li class="link_li"><a href="javascript:void()"><?php echo Labels::getLabel('LBL_View_All_Details', $siteLangId); ?></a></li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php }*/?>

                                    <!-- Add To Cart [ -->
                                    <?php if ($product['in_stock']) {
                                    echo $frmBuyProduct->getFormTag();
                                    $qtyField =  $frmBuyProduct->getField('quantity');
                                    $qtyFieldName =  $qtyField->getCaption();
                                if (strtotime($product['selprod_available_from'])<= strtotime(FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d'))) { ?>
                                    <div class="row align-items-end">
                                        <div class="col-xl-4 col-lg-5 col-md-5 mb-2">
                                            <div class="form__group form__group-select">
                                                <label class="h6"><?php echo $qtyFieldName; ?></label>
                                                <div class="qty-wrapper">
                                                    <div class="quantity" data-stock="<?php echo $product['selprod_stock']; ?>">
                                                        <span class="decrease decrease-js">-</span>
                                                        <div class="qty-input-wrapper" data-stock="<?php echo $product['selprod_stock']; ?>">
                                                            <?php echo $frmBuyProduct->getFieldHtml('quantity'); ?>
                                                        </div>
                                                        <span class="increase increase-js">+</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-8 col-lg-7 col-md-7 mb-2">
                                            <label class="h6">&nbsp;</label>
                                            <div class="buy-group">
                                                <?php if (strtotime($product['selprod_available_from']) <= strtotime(FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d'))) {
                                            echo $frmBuyProduct->getFieldHtml('btnProductBuy');
                                            echo $frmBuyProduct->getFieldHtml('btnAddToCart');
                                        }
                                        echo $frmBuyProduct->getFieldHtml('selprod_id'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <div class="gap"></div>

                                    </form>
                                    <?php echo $frmBuyProduct->getExternalJs();
                                } else { ?>
                                    <div class="sold">
                                        <h3 class="text--normal-secondary"><?php echo Labels::getLabel('LBL_Sold_Out', $siteLangId); ?></h3>
                                        <p class="text--normal-secondary"><?php echo Labels::getLabel('LBL_This_item_is_currently_out_of_stock', $siteLangId); ?></p>
                                    </div>
                                    <?php } ?>
                                    <?php if (strtotime($product['selprod_available_from'])> strtotime(FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d'))) { ?>
                                    <div class="sold">
                                        <h3 class="text--normal-secondary"><?php echo Labels::getLabel('LBL_Not_Available', $siteLangId); ?></h3>
                                        <p class="text--normal-secondary">
                                            <?php echo str_replace('{available-date}', FatDate::Format($product['selprod_available_from']), Labels::getLabel('LBL_This_item_will_be_available_from_{available-date}', $siteLangId)); ?>
                                        </p>
                                    </div>
                                    <?php }?>
                                    <!-- ] -->


                                    <?php /* if ($product['product_upc']!='') { ?>
                                    <div class="gap"></div>
                                    <div><?php echo Labels::getLabel('LBL_EAN/UPC_code', $siteLangId).' : '.$product['product_upc'];?></div>
                                    <?php } */ ?>

                                    <?php /* Volume Discounts[ */
                            if (isset($volumeDiscountRows) && !empty($volumeDiscountRows) && $product['in_stock']) { ?>
                                    <div class="gap"></div>
                                    <div class="h6"><?php echo Labels::getLabel('LBL_Wholesale_Price_(Piece)', $siteLangId); ?>:</div>
                                    <ul class="<?php echo (count($volumeDiscountRows) > 1) ? 'js--discount-slider' : ''; ?> discount-slider" dir="<?php echo CommonHelper::getLayoutDirection(); ?>">
                                        <?php foreach ($volumeDiscountRows as $volumeDiscountRow) {
                                $volumeDiscount = $product['theprice'] * ($volumeDiscountRow['voldiscount_percentage'] / 100);
                                $price = ($product['theprice'] - $volumeDiscount); ?>
                                        <li>
                                            <div class="qty__value"><?php echo($volumeDiscountRow['voldiscount_min_qty']); ?> <?php echo Labels::getLabel('LBL_Or_more', $siteLangId); ?>
                                                (<?php echo $volumeDiscountRow['voldiscount_percentage'].'%'; ?>) <span class="item__price"><?php echo CommonHelper::displayMoneyFormat($price); ?> /
                                                    <?php echo Labels::getLabel('LBL_Product', $siteLangId); ?></span></div>
                                        </li>
                                        <?php
                            } ?>
                                    </ul>
                                    <script type="text/javascript">
                                        $("document").ready(function() {
                                            $('.js--discount-slider').slick(getSlickSliderSettings(2, 1, langLbl.layoutDirection, false, {1199: 2,1023: 2,767: 1,480: 1}));
                                        });
                                    </script>
                                    <?php } /* ] */ ?>

                                    <!-- Upsell Products [ -->
                                    <?php if (count($upsellProducts)>0) { ?>
                                    <div class="gap"></div>
                                    <div class="h6"><?php echo Labels::getLabel('LBL_Product_Add-ons', $siteLangId); ?></div>
                                    <div class="addons-scrollbar" data-simplebar>
                                        <table class="table cart--full cart-tbl cart-tbl-addons">
                                            <tbody>
                                                <?php  foreach ($upsellProducts as $usproduct) {
                                $cancelClass ='';
                                $uncheckBoxClass='';
                                if ($usproduct['selprod_stock'] <= 0) {
                                    $cancelClass ='cancelled--js';
                                    $uncheckBoxClass ='remove-add-on';
                                } ?>
                                                <tr>
                                                    <td class="<?php echo $cancelClass; ?>">
                                                        <figure class="item__pic"><a title="<?php echo $usproduct['selprod_title']; ?>" href="<?php echo CommonHelper::generateUrl('products', 'view', array($usproduct['selprod_id']))?>"><img
                                                                    src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'product', array($usproduct['product_id'], 'MINI', $usproduct['selprod_id'] )), CONF_IMG_CACHE_TIME, '.jpg'); ?>"
                                                                    alt="<?php echo $usproduct['product_identifier']; ?>"> </a></figure>
                                                    </td>
                                                    <td class="<?php echo $cancelClass; ?>">
                                                        <div class="item__description">
                                                            <div class="item__title"><a href="<?php echo CommonHelper::generateUrl('products', 'view', array($usproduct['selprod_id']))?>"><?php echo $usproduct['selprod_title']?></a></div>
                                                        </div>
                                                        <?php if ($usproduct['selprod_stock'] <= 0) { ?>
                                                        <div class="addon--tag--soldout"><?php echo Labels::getLabel('LBL_SOLD_OUT', $siteLangId);?></div>
                                                        <?php  } ?><div class="item__price"><?php echo CommonHelper::displayMoneyFormat($usproduct['theprice']); ?></div>
                                                    </td>

                                                    <td class="<?php echo $cancelClass; ?>">
                                                        <div class="qty-wrapper">
                                                            <div class="quantity" data-stock="<?php echo $usproduct['selprod_stock']; ?>"><span class="decrease decrease-js">-</span>
                                                                <div class="qty-input-wrapper" data-stock="<?php echo $usproduct['selprod_stock']; ?>">
                                                                    <input type="text" value="1" data-page="product-view" placeholder="Qty" class="qty-input cartQtyTextBox productQty-js" lang="addons[<?php echo $usproduct['selprod_id']?>]"
                                                                        name="addons[<?php echo $usproduct['selprod_id']?>]">
                                                                </div>
                                                                <span class="increase increase-js">+</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="<?php echo $cancelClass; ?>"><label class="checkbox">
                                                            <input <?php if ($usproduct['selprod_stock'] > 0) { ?> checked="checked" <?php } ?> type="checkbox" class="cancel <?php echo $uncheckBoxClass; ?>" id="check_addons" name="check_addons"
                                                                title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?>">
                                                            <i class="input-helper"></i> </label>
                                                    </td>
                                                </tr>
                                                <?php
                            } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php } ?>
                                    <!-- ] -->

                                </div>
                                <div class="gap"></div>
                                <div class="sold-by bg-gray p-4 rounded">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col-xl-6 col-lg-6 col-md-5">
                                            <div class="h6 m-0 -color-light"><?php echo Labels::getLabel('LBL_Seller', $siteLangId);?></div>
                                            <h6 class="m-0">
                                                <a href="<?php echo CommonHelper::generateUrl('shops', 'View', array($shop['shop_id'])); ?>"><?php echo $shop['shop_name'];?></a>
                                                <div class="products__rating -display-inline m-0">
                                                    <?php if (0 < FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?>
                                                        - <i class="icn">
                                                            <svg class="svg">
                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
                                                            </svg>
                                                        </i> 
                                                        <span class="rate"><?php echo round($shop_rating,1),'','', '';  if($shopTotalReviews){ ?><?php } ?> </span>
                                                    <?php } ?>
                                                </div>

                                            </h6>


                                            <?php /*if ($shop_rating>0) { ?>
                                            <div class="products__rating"> <i class="icn"><svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
                                                    </svg></i> <span class="rate"><?php echo round($shop_rating, 1); ?><span></span></span>
                                            </div><br>
                                            <?php }*/?>

                                        </div>
                                        <div class="col-auto">
                                            <?php if (!UserAuthentication::isUserLogged() || (UserAuthentication::isUserLogged() && ((User::isBuyer()) || (User::isSeller() )) && (UserAuthentication::getLoggedUserId()!=$shop['shop_user_id']))) { ?>
                                            <a href="<?php echo CommonHelper::generateUrl('shops', 'sendMessage', array($shop['shop_id'],$product['selprod_id'])); ?>"
                                                class="btn btn--primary btn--secondary btn--primary-border  btn--sm"><?php echo Labels::getLabel('LBL_Ask_Question', $siteLangId); ?></a>
                                            <?php }?>
                                            <?php if (count($product['moreSellersArr'])>0) { ?>
                                            <a href="<?php echo CommonHelper::generateUrl('products', 'sellers', array($product['selprod_id']));?>" class="btn btn--primary btn--sm "><?php echo Labels::getLabel('LBL_All_Sellers', $siteLangId);?></a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <?php include(CONF_THEME_PATH.'_partial/product/shipping-rates.php');?>

                <?php $youtube_embed_code = CommonHelper::parseYoutubeUrl($product["product_youtube_video"]); ?>
                <div class="row justify-content-center">
                    <div class="col-md-7">
                        <div class="nav-detail nav-detail-js">
                            <ul>
                                <?php if (count($productSpecifications)>0) {?>
                                <li><a class="nav-scroll-js is-active" href="#specifications"><?php echo Labels::getLabel('LBL_Specifications', $siteLangId); ?></a></li>
                                <?php }?>
                                <?php if ($product['product_description']!='') { ?>
                                <li class=""><a class="nav-scroll-js" href="#description"><?php echo Labels::getLabel('LBL_Description', $siteLangId); ?> </a></li>
                                <?php }?>
                                <?php if ($youtube_embed_code) { ?>
                                <li class=""><a class="nav-scroll-js" href="#video"><?php echo Labels::getLabel('LBL_Video', $siteLangId); ?> </a></li>
                                <?php }?>
                                <?php if ($shop['shop_payment_policy'] != '' || !empty($shop["shop_delivery_policy"] != "") || !empty($shop["shop_delivery_policy"] != "")) { ?>
                                <li class=""><a class="nav-scroll-js" href="#shop-policies"><?php echo Labels::getLabel('LBL_Shop_Policies', $siteLangId); ?> </a></li>
                                <?php }?>
                                <?php if (!empty($product['selprodComments'])) { ?>
                                <li class=""><a class="nav-scroll-js" href="#extra-comments"><?php echo Labels::getLabel('LBL_Extra_comments', $siteLangId); ?> </a></li>
                                <?php }?>
                                <?php if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?>
                                <li class=""><a class="nav-scroll-js" href="#itemRatings"><?php echo Labels::getLabel('LBL_Ratings_and_Reviews', $siteLangId); ?> </a></li>
                                <?php }?>
                            </ul>
                        </div>
                    </div>
                </div>


            </div>
            <section class="section">
                <div class="row justify-content-center">
                    <div class="col-xl-7">
                        <?php if (count($productSpecifications)>0) {?>
                        <div class="section-head">
                            <div class="section__heading" id="specifications">
                                <h2><?php echo Labels::getLabel('LBL_Specifications', $siteLangId); ?></h2>
                            </div>
                        </div>
                        <div class="cms bg-gray p-4 mb-4">
                            <table>
                                <tbody>
                                    <?php foreach ($productSpecifications as $key => $specification) { ?>
                                    <tr>
                                        <th><?php echo $specification['prodspec_name']." :" ;?></th>
                                        <td><?php echo html_entity_decode($specification['prodspec_value'], ENT_QUOTES, 'utf-8') ; ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php }?>
                        <?php if ($product['product_description']!='') { ?>
                        <div class="section-head">
                            <div class="section__heading" id="description">
                                <h2><?php echo Labels::getLabel('LBL_Description', $siteLangId); ?></h2>
                            </div>
                        </div>
                        <div class="cms bg-gray p-4 mb-4">
                            <p><?php echo CommonHelper::renderHtml($product['product_description']);?></p>
                        </div>
                        <?php } ?>
                        <?php if ($youtube_embed_code) { ?>
                        <div class="section-head">
                            <div class="section__heading" id="video">
                                <h2><?php echo Labels::getLabel('LBL_Video', $siteLangId); ?></h2>
                            </div>
                        </div>
                        <?php if ($youtube_embed_code!="") : ?>
                        <div class="mb-4 video-wrapper">
                            <iframe width="100%" height="315" src="//www.youtube.com/embed/<?php echo $youtube_embed_code?>" allowfullscreen></iframe>
                        </div>
                        <span class="gap"></span>
                        <?php  endif;?>
                        <?php } ?>
                        <?php if ($shop['shop_payment_policy'] != '' || !empty($shop["shop_delivery_policy"] != "") || !empty($shop["shop_delivery_policy"] != "")) { ?>
                        <div class="section-head">
                            <div class="section__heading" id="shop-policies">
                                <h2><?php echo Labels::getLabel('LBL_Shop_Policies', $siteLangId); ?></h2>
                            </div>
                        </div>
                        <div class="cms bg-gray p-4 mb-4">
                            <?php if ($shop['shop_payment_policy'] != '') { ?>
                            <h6><?php echo Labels::getLabel('LBL_Payment', $siteLangId)?></h6>
                            <p><?php echo nl2br($shop['shop_payment_policy']); ?></p>
                            <br>
                            <?php } ?>
                            <?php if ($shop['shop_delivery_policy'] != '') { ?>
                            <h6><?php echo Labels::getLabel('LBL_Shipping', $siteLangId)?></h6>
                            <p><?php echo nl2br($shop['shop_delivery_policy']); ?></p>
                            <br>
                            <?php }?>
                            <?php if ($shop['shop_refund_policy'] != '') { ?>
                            <h6><?php echo Labels::getLabel('LBL_Shipping', $siteLangId)?></h6>
                            <p><?php echo nl2br($shop['shop_refund_policy']); ?></p>
                            <?php }?>
                        </div>
                        <?php } ?>
                        <?php if (!empty($product['selprodComments'])) { ?>
                        <div class="section-head">
                            <div class="section__heading" id="extra-comments">
                                <h2><?php echo Labels::getLabel('LBL_Extra_comments', $siteLangId); ?></h2>
                            </div>
                        </div>
                        <div class="cms bg-gray p-4 mb-4">
                            <p><?php echo CommonHelper::displayNotApplicable($siteLangId, nl2br($product['selprodComments'])); ?></p>
                        </div>
                        <?php } ?>

                        <div id="itemRatings">
                            <?php if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?>
                            <?php echo $frmReviewSearch->getFormHtml(); ?>
                            <?php $this->includeTemplate('_partial/product-reviews.php', array('reviews'=>$reviews,'siteLangId'=>$siteLangId,'product_id' => $product['product_id'],'canSubmitFeedback' => $canSubmitFeedback), false); ?>
                            <?php }?>
                        </div>

                    </div>
                </div>
            </section>
            <section class="">
                <?php if (isset($banners['Product_Detail_Page_Banner']) && $banners['Product_Detail_Page_Banner']['blocation_active'] && count($banners['Product_Detail_Page_Banner']['banners'])) { ?>
                <div class="gap"></div>
                <div class="row">
                    <?php foreach ($banners['Product_Detail_Page_Banner']['banners'] as $val) {
                         $desktop_url = '';
                         $tablet_url = '';
                         $mobile_url = '';
                        if (!AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BANNER, $val['banner_id'], 0, $siteLangId)) {
                             continue;
                        } else {
                            $slideArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BANNER, $val['banner_id'], 0, $siteLangId);
                            foreach ($slideArr as $slideScreen) {
                                switch ($slideScreen['afile_screen']) {
                                    case applicationConstants::SCREEN_MOBILE:
                                        $mobile_url = '<736:' .CommonHelper::generateUrl('Banner', 'productDetailPageBanner', array($val['banner_id'], $siteLangId, applicationConstants::SCREEN_MOBILE)).",";
                                        break;
                                    case applicationConstants::SCREEN_IPAD:
                                        $tablet_url = ' >768:' .CommonHelper::generateUrl('Banner', 'productDetailPageBanner', array($val['banner_id'], $siteLangId, applicationConstants::SCREEN_IPAD)).",";
                                        break;
                                    case applicationConstants::SCREEN_DESKTOP:
                                        $desktop_url = ' >1025:' .CommonHelper::generateUrl('Banner', 'productDetailPageBanner', array($val['banner_id'], $siteLangId, applicationConstants::SCREEN_DESKTOP)).",";
                                        break;
                                }
                            }
                        } ?>
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="banner-ppc"><a href="<?php echo CommonHelper::generateUrl('Banner', 'url', array($val['banner_id'])); ?>" target="<?php echo $val['banner_target']; ?>" title="<?php echo $val['banner_title']; ?>"
                                class="advertise__block"><img data-ratio="10:3" data-src-base="" data-src-base2x="" data-src="<?php echo $mobile_url  . $tablet_url  . $desktop_url; ?>"
                                    src="<?php echo CommonHelper::generateUrl('Banner', 'productDetailPageBanner', array($val['banner_id'],$siteLangId,applicationConstants::SCREEN_DESKTOP)); ?>" alt="<?php echo $val['banner_title']; ?>"
                                    class="img-responsive"></a></div>
                    </div>
                    <?php } ?>
                    </div>
                <?php } if (isset($val['banner_record_id']) && $val['banner_record_id'] > 0 && $val['banner_type'] == Banner::TYPE_PPC) {
                         Promotion::updateImpressionData($val['banner_record_id']);
                } ?>
            </section>
        </div>
    </section>
    <?php if ($recommendedProducts) { ?>
    <section class="section bg--second-color">
        <?php include(CONF_THEME_PATH.'products/recommended-products.php'); ?>
    </section>
    <?php } ?>
    <?php if ($relatedProductsRs) { ?>
    <section class="section">
        <?php include(CONF_THEME_PATH.'products/related-products.php'); ?>
    </section>
    <?php } ?>
    <div id="recentlyViewedProductsDiv"></div>
</div>
<script type="text/javascript">
    var mainSelprodId = <?php echo $product['selprod_id'];?>;
    var layout = '<?php echo CommonHelper::getLayoutDirection();?>';

    $("document").ready(function() {
        recentlyViewedProducts(<?php echo $product['selprod_id'];?>);
        /*zheight = $(window).height() - 180; */
        zwidth = $(window).width() / 3 - 15;

        if (layout == 'rtl') {
            $('.xzoom, .xzoom-gallery').xzoom({
                zoomWidth: zwidth,
                /*zoomHeight: zheight,*/
                title: true,
                tint: '#333',
                position: 'left'
            });
        } else {
            $('.xzoom, .xzoom-gallery').xzoom({
                zoomWidth: zwidth,
                /*zoomHeight: zheight,*/
                title: true,
                tint: '#333',
                Xoffset: 2
            });
        }

        window.setInterval(function() {
            var scrollPos = $(window).scrollTop();
            if (scrollPos > 0) {
                setProductWeightage('<?php echo $product['selprod_code']; ?>');
            }
        }, 5000);

    });

    <?php /* if( isset($banners['Product_Detail_Page_Banner']) && $banners['Product_Detail_Page_Banner']['blocation_active'] && count($banners['Product_Detail_Page_Banner']['banners']) ) { ?>
    $(function() {
        if ($(window).width() > 1050) {
            $(window).scroll(sticky_relocate);
            sticky_relocate();
        }
    });
    <?php } */ ?>
</script>
<script>
    $(document).ready(function() {
        $("#btnAddToCart").addClass("quickView");
        $('#slider-for').slick(getSlickGallerySettings(false));
        $('#slider-nav').slick(getSlickGallerySettings(true, '<?php echo CommonHelper::getLayoutDirection();?>'));

        /* for toggling of tab/list view[ */
        $('.list-js').hide();
        $('.view--link-js').on('click', function(e) {
            $('.view--link-js').removeClass("btn--active");
            $(this).addClass("btn--active");
            if ($(this).hasClass('list')) {
                $('.tab-js').hide();
                $('.list-js').show();
            } else if ($(this).hasClass('tab')) {
                $('.list-js').hide();
                $('.tab-js').show();
            }
        });
        /* ] */

        $(".nav-scroll-js").click(function(event) {
            event.preventDefault();
            var full_url = this.href;
            var parts = full_url.split("#");
            var trgt = parts[1];
            var target_offset = $("#" + trgt).offset();

            var target_top = target_offset.top - $('#header').height();
            $('html, body').animate({
                scrollTop: target_top
            }, 800);
        });
        $('.nav-detail-js li a').click(function() {
            $('.nav-detail-js li a').removeClass('is-active');
            $(this).addClass('is-active');
        });

    });
</script>
<!--Here is the facebook OG for this product  -->
<?php echo $this->includeTemplate('_partial/shareThisScript.php'); ?>
