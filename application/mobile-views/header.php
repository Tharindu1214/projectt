<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$status = applicationConstants::ON;
$commonData = array(
    'currencySymbol'=>$currencySymbol,
    'totalFavouriteItems'=>$totalFavouriteItems,
    'totalUnreadMessageCount'=>$totalUnreadMessageCount,
    'totalUnreadNotificationCount'=>$totalUnreadNotificationCount,
    'cartItemsCount'=>$cartItemsCount
);
