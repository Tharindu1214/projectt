<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
define('HTTP_YOKART_PUBLIC', $protocol . $_SERVER['HTTP_HOST'] . rtrim(rtrim(dirname($_SERVER['SCRIPT_NAME']), 'install'), '/.\\') . '/');
define('HTTP_YOKART', preg_replace('~/[^/]*/([^/]*)$~', '/\1', HTTP_YOKART_PUBLIC));

if (is_file('settings.php')) {
    require_once('settings.php');
}

if (!defined('CONF_WEBROOT_FRONTEND') || is_file('install/install_step.php')) {
    require_once('install/install.php');
    die();
}
require_once dirname(__DIR__) . '/conf/conf.php';

/*$filename = CONF_UPLOADS_PATH.'database-restore-progress.txt';
if (file_exists($filename)) {
    $filelastmodified = filemtime($filename);
    if ((time() - $filelastmodified) < 1*60) {
        if (!strpos($_SERVER['REQUEST_URI'], 'app-api')=== false) {
            $arr = array('status'=>0,'msg'=>'We are restoring database as a scheduled process. Please try in about a minute.');
            die(json_encode($arr));
        }
        die('We are restoring database as a scheduled process. Please try in about a minute.');
    }
    @unlink(CONF_UPLOADS_PATH.'database-restore-progress.txt');
}*/
$filename = CONF_UPLOADS_PATH.'database-restore-progress.txt';
if (file_exists($filename)) {
    $filelastmodified = filemtime($filename);
    if ((time() - $filelastmodified) < 8*60) {
        if (!strpos($_SERVER['REQUEST_URI'], 'app-api')=== false) {
            $arr = array('status'=>0,'msg'=>'We are restoring database as a scheduled process. Please try in about a minute.');
            die(json_encode($arr));
        }
        require_once('maintenance.php');
        exit;
    }
    @unlink(CONF_UPLOADS_PATH.'database-restore-progress.txt');
}

require_once dirname(__FILE__) . '/application-top.php';

FatApp::unregisterGlobals();

if (file_exists(CONF_APPLICATION_PATH . 'utilities/prehook.php')) {
    require_once CONF_APPLICATION_PATH . 'utilities/prehook.php';
}

FatApplication::getInstance()->callHook();
