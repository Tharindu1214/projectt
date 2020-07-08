<?php
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
    ob_start("ob_gzhandler");
} else {
    ob_start();
}

ini_set('display_errors', (CONF_DEVELOPMENT_MODE)?1:0);

error_reporting((CONF_DEVELOPMENT_MODE)?E_ALL:E_ALL & ~E_NOTICE & ~E_WARNING);

require_once CONF_INSTALLATION_PATH . 'library/autoloader.php';

/* We must set it before initiating db connection. So that connection timezone is in sync with php */
date_default_timezone_set('America/New_York');

$timeZone = FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get());
date_default_timezone_set($timeZone);

/* setting Time Zone of Mysql Server with same as of PHP[ */
$now = new DateTime();
$mins = $now->getOffset() / 60;
$sgn = ($mins < 0 ? -1 : 1);
$mins = abs($mins);
$hrs = floor($mins / 60);
$mins -= $hrs * 60;
$offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
FatApp::getDb()->query("SET time_zone = '".$offset."'");
/* ] */

ini_set('session.cookie_httponly', true);
ini_set('session.cookie_path', CONF_WEBROOT_FRONT_URL);
session_start();
FatApp::getDb()->query("SET NAMES utf8mb4");
/*FatApp::getDb()->logQueries(true,CONF_UPLOADS_PATH.'logQuery.txt');*/

/* --- Redirect SSL --- */
$protocol = (FatApp::getConfig('CONF_USE_SSL', FatUtility::VAR_INT, 0)==1)?'https://':'http://';

if ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')  && (FatApp::getConfig('CONF_USE_SSL')==1)) {
    $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    FatApp::redirectUser($redirect);
}

/* USE when $_SERVER['HTTPS'] will not provided by server . Generally in AWS server when load balance used for SSL.
if ((!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] != 'https')  && (FatApp::getConfig('CONF_USE_SSL')==1)) {
    $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    FatApp::redirectUser($redirect);
} */
/* --- Redirect SSL --- */
$_SESSION['WYSIWYGFileManagerRequirements'] = CONF_INSTALLATION_PATH . 'public/WYSIWYGFileManagerRequirements.php';


define('SYSTEM_INIT', true);
define('CONF_WEB_APP_VERSION', 'RV-9.1.0');
