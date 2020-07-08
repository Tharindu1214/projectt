<?php
function fatAutoLoader($className) {
	if (strpos($className, '\\') !== false) {
		$arr = explode('\\', $className);
		$className = end($arr);
	}

	if (file_exists ( CONF_CORE_LIB_PATH . $className . '.class.php' )) {
		require_once CONF_CORE_LIB_PATH . $className . '.class.php';
	} else if (file_exists ( CONF_INSTALLATION_PATH . 'library/fat/' . $className . '.class.php' )) {
		require_once CONF_INSTALLATION_PATH . 'library/fat/' . $className . '.class.php';
	} else if (file_exists ( CONF_INSTALLATION_PATH . 'library/' . $className . '.class.php' )) {
		require_once CONF_INSTALLATION_PATH . 'library/' . $className . '.class.php';
	} else if (file_exists ( CONF_APPLICATION_PATH . 'utilities/' . $className . '.php' )) {
		require_once CONF_APPLICATION_PATH . 'utilities/' . $className . '.php';
	} else if (file_exists ( CONF_APPLICATION_PATH . 'controllers/' . $className . '.php' )) {
		require_once CONF_APPLICATION_PATH . 'controllers/' . $className . '.php';
	} else if (file_exists ( CONF_APPLICATION_PATH . 'models/' . $className . '.php' )) {
		require_once CONF_APPLICATION_PATH . 'models/' . $className . '.php';
	} else if (file_exists ( CONF_APPLICATION_PATH . 'utilities/' . $className . '.php' )) {
		require_once CONF_APPLICATION_PATH . 'utilities/' . $className . '.php';
	}	else {
		/*
		 * if current application path is not the application folder at installtion path
		 * let us try to look into application at root if that exists
		 */
		$root_application_path = CONF_INSTALLATION_PATH . 'application/';
		if ($root_application_path != CONF_APPLICATION_PATH) {
			if (file_exists ( $root_application_path . 'models/' . $className . '.php' )) {
				require_once $root_application_path . 'models/' . $className . '.php';
			}else if (file_exists ( $root_application_path . 'utilities/' . $className . '.php' )) {
				require_once $root_application_path . 'utilities/' . $className . '.php';
			}
		}
	}
}

spl_autoload_register ( 'fatAutoLoader' );
