<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<?php
switch ($frameId) {
    case Banner::BANNER_HOME_PAGE_LAYOUT_1:
        $image =  'images/banner_layouts/layout-1.jpg';
        break;
    case Banner::BANNER_HOME_PAGE_LAYOUT_2:
        $image =  'images/banner_layouts/layout-2.jpg';
        break;
    case Banner::BANNER_PRODUCT_PAGE_LAYOUT_1:
        $image =  'images/banner_layouts/layout-3.jpg';
        break;
}
?>
<img src="<?php echo CONF_WEBROOT_URL.$image;?>">
