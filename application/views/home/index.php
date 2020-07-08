<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<div id="body" class="body" role="main">
  <!--slider[-->
<?php if (isset($slides) && count($slides)) {
    $this->includeTemplate('_partial/homePageSlides.php', array( 'slides' =>$slides, 'siteLangId' => $siteLangId ), false);
} ?>
  <!--]-->
<?php
/* Product Layout1[ */
if (count($sponsoredProds)>0) {
    $this->includeTemplate('_partial/collection/sponsored-products.php', array( 'products' => $sponsoredProds, 'siteLangId' => $siteLangId ), false);
}

echo FatUtility::decodeHtmlEntities($homePageFirstLayout);
// echo FatUtility::decodeHtmlEntities($homePageProdLayout1);
/*if (isset($collections[Collections::TYPE_PRODUCT_LAYOUT1])) {
    $this->includeTemplate('_partial/collection/product-layout-1.php', array( 'collections' => $collections[Collections::TYPE_PRODUCT_LAYOUT1], 'siteLangId' => $siteLangId ), false);
}*/
/* ] */

/* category Layout2[ */
// echo FatUtility::decodeHtmlEntities($homePageCatLayout1);
/* if (isset($collections[Collections::TYPE_CATEGORY_LAYOUT1])) {
   $this->includeTemplate('_partial/collection/category-layout-1.php', array( 'collections' => $collections[Collections::TYPE_CATEGORY_LAYOUT1], 'siteLangId' => $siteLangId,'action'=>$action ), false);
}*/
/* ] */

/* Top Banner Layout[ */
if (isset($banners['Home_Page_Top_Banner'])) {
    $this->includeTemplate('_partial/banners/home-banner-first-layout.php', array( 'bannerLayout1' => $banners['Home_Page_Top_Banner'], 'siteLangId' => $siteLangId ), false);
}
/* ] */

/* Product Layout2[ */
echo FatUtility::decodeHtmlEntities($homePageProdLayout2);
/*if (isset($collections[Collections::TYPE_PRODUCT_LAYOUT2])) {
    $this->includeTemplate('_partial/collection/product-layout-2.php', array( 'collections' => $collections[Collections::TYPE_PRODUCT_LAYOUT2], 'siteLangId' => $siteLangId ), false);
}*/
/* ] */

/* Bottom Banner Layout[ */
if (isset($banners['Home_Page_Bottom_Banner'])) {
    $this->includeTemplate('_partial/banners/home-banner-second-layout.php', array( 'bannerLayout1' => $banners['Home_Page_Bottom_Banner'], 'siteLangId' => $siteLangId ), false);
}
/* ] */

/* Shop Layout1[ */
echo FatUtility::decodeHtmlEntities($homePageShopLayout1);
/*if (isset($collections[Collections::TYPE_SHOP_LAYOUT1])) {
    $this->includeTemplate('_partial/collection/shop-layout-1.php', array( 'collections' => $collections[Collections::TYPE_SHOP_LAYOUT1], 'siteLangId' => $siteLangId ,'action'=>$action), false);
}*/

if (count($sponsoredShops) > 0) {
    $this->includeTemplate('_partial/collection/sponsored-shops.php', array( 'sponsoredShops' => $sponsoredShops, 'siteLangId' => $siteLangId ,'action'=>$action), false);
}
/* ] */

echo FatUtility::decodeHtmlEntities($homePageFooterLayout);
/* Category Layout2[ */
// echo FatUtility::decodeHtmlEntities($homePageCatLayout2);
/*if (isset($collections[Collections::TYPE_CATEGORY_LAYOUT2])) {
    $this->includeTemplate('_partial/collection/category-layout-2.php', array( 'collections' => $collections[Collections::TYPE_CATEGORY_LAYOUT2], 'siteLangId' => $siteLangId,'action'=>$action ), false);
}*/
/* ] */

/* Product Layout3[ */
// echo FatUtility::decodeHtmlEntities($homePageProdLayout3);
/*if (isset($collections[Collections::TYPE_PRODUCT_LAYOUT3])) {
    $this->includeTemplate('_partial/collection/product-layout-3.php', array( 'collections' => $collections[Collections::TYPE_PRODUCT_LAYOUT3], 'siteLangId' => $siteLangId ), false);
}*/
/* ] */

/* Brand Layout1[ */
// echo FatUtility::decodeHtmlEntities($homePageBrandLayout1);
/*if (isset($collections[Collections::TYPE_BRAND_LAYOUT1])) {
    $this->includeTemplate('_partial/collection/brand-layout-1.php', array( 'collections' => $collections[Collections::TYPE_BRAND_LAYOUT1], 'siteLangId' => $siteLangId ), false);
}*/
/* ] */

?>
</div>
