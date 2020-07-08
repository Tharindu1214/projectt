<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<?php if( $layoutId == ContentPage::CONTENT_PAGE_LAYOUT1_TYPE ) { ?>
    <img src="<?php echo CommonHelper::generateFullUrl(); ?><?php echo CONF_WEBROOT_FRONT_URL; ?>images/cms_layouts/layout-1.jpg">
<?php } else if( $layoutId == ContentPage::CONTENT_PAGE_LAYOUT2_TYPE ) { ?>
    <img src="<?php echo CommonHelper::generateFullUrl(); ?><?php echo CONF_WEBROOT_FRONT_URL; ?>images/cms_layouts/layout-2.jpg">
<?php } ?>
