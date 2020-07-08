<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
require_once(CONF_THEME_PATH.'cart/price-detail.php');
if (empty($products)) {
    $status = applicationConstants::OFF;
}
