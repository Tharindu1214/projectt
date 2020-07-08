<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$tplFile = str_replace( CONF_APPLICATION_PATH, CONF_INSTALLATION_PATH.CONF_FRONT_END_APPLICATION_DIR, CONF_THEME_PATH );
$tplFile .= '_partial/child-order-detail-email.php';

include_once $tplFile;