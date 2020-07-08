<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<ul>
    <li class="<?php echo $action == 'view' ? 'is--active' : '' ?>"><a href="<?php echo CommonHelper::generateUrl('shops', 'view', array($shop_id));?>" class="ripplelink"><?php echo Labels::getLabel('LBL_SHOP_STORE_HOME', $siteLangId); ?></a></li>
    <li class="<?php echo $action == 'topProducts' ? 'is--active' : '' ?>"><a href="<?php echo CommonHelper::generateUrl('shops', 'topProducts', array($shop_id));?>" class="ripplelink"><?php echo Labels::getLabel('LBL_SHOP_TOP_PRODUCTS', $siteLangId); ?></a></li>
    <?php /* if (!empty($collectionData)) { ?>
    <li class="<?php echo $action == 'collection' ? 'is--active' : '' ?>"><a href="<?php echo CommonHelper::generateUrl('shops', 'collections', array($shop_id));?>" class="ripplelink"><?php echo $collectionData['collectionName']; ?></a></li>
    <?php } */ ?>
    <?php if (0 < FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?>
    <li class="<?php echo $action == 'shop' ? 'is--active' : '' ?>"><a href="<?php echo CommonHelper::generateUrl('reviews', 'shop', array($shop_id));?>" class="ripplelink"><?php echo Labels::getLabel('LBL_SHOP_REVIEW', $siteLangId); ?></a></li>
    <?php } ?>
    <?php if (!UserAuthentication::isUserLogged() || (UserAuthentication::isUserLogged() && ((User::isBuyer()) || (User::isSeller() )) && (UserAuthentication::getLoggedUserId() != $shop_user_id))) { ?>
    <li class="<?php echo $action == 'sendMessage' ? 'is--active' : '' ?>"><a href="<?php echo CommonHelper::generateUrl('shops', 'sendMessage', array($shop_id));?>" class="ripplelink"><?php echo Labels::getLabel('LBL_SHOP_CONTACT', $siteLangId); ?></a></li>
    <?php } ?>
    <li class="<?php echo $action == 'policy' ? 'is--active' : '' ?>"><a href="<?php echo CommonHelper::generateUrl('shops', 'policy', array($shop_id));?>" class="ripplelink"><?php echo Labels::getLabel('LBL_SHOP_POLICY', $siteLangId); ?></a></li>
</ul>
