<?php
class JsCssController{
	private function checkModifiedHeader() {
		$headers = FatApp::getApacheRequestHeaders();
    	if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == $_GET['sid'])) {
    		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $_GET['sid']).' GMT', true, 304);
    		exit;
    	}
	}

	private function setHeaders($contentType) {
		header('Content-Type: ' . $contentType);
		header('Cache-Control: public, max-age=2592000, stale-while-revalidate=604800');
        header("Pragma: public");
        header("Expires: " . date('r', strtotime("+30 days")));
		$this->checkModifiedHeader();
		if (isset($_GET['sid'])) {
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $_GET['sid']).' GMT', true, 200);
		}

		if (! in_array ( 'ob_gzhandler', ob_list_handlers () )) {
			if (substr_count ( $_SERVER ['HTTP_ACCEPT_ENCODING'], 'gzip' )) {
				ob_start ( "ob_gzhandler" );
			} else {
				ob_start ();
			}
		}
	}

    function css(){
    	$this->setHeaders('text/css');

        $arr = explode(',', $_GET['f']);

        $str = '';

        foreach ($arr as $fl){
            if (substr($fl, '-4') != '.css') continue;
        	$file = CONF_THEME_PATH . $fl;
            if (file_exists($file)) $str .= file_get_contents($file);
        }

        $str = str_replace('../', '', $str);

        if (FatApplication::getInstance()->getQueryStringVar('min', FatUtility::VAR_INT) == 1){
            $str = preg_replace('/([\n][\s]*)+/', " ", $str);
            $str = str_replace("\r", '', $str);
            $str = str_replace("\n", '', $str);
        }

		$cacheKey = $_SERVER['REQUEST_URI'];
		FatCache::set($cacheKey, $str, '.css');

        echo $str;
    }

    function cssCommon(){

	/*	if (empty($_SESSION['preview_theme']) && !isset($_SESSION['preview_theme']) ) {
			$this->checkModifiedHeader();
		}*/
    	$this->setHeaders('text/css');

        if ( isset($_GET['f']) ) {
        	$files = $_GET['f'];
        }
        else {
        	$pth = CONF_THEME_PATH . 'common-css';
        	$dir = opendir($pth);
        	$last_updated = 0;
        	$files = '';

        	$arrCommonfiles = scandir($pth, SCANDIR_SORT_ASCENDING);
        	foreach ($arrCommonfiles as $fl) {
        		if (!is_file($pth . DIRECTORY_SEPARATOR . $fl)) continue;
        		if ( '.css' != substr($fl, -4) ) continue;
        		if ( 'noinc-' == substr($fl, 0, 6) ) continue;

        		if ( '' != $files ) $files .= ',';
        		$files .= $fl;
        	}
        }


        $arr = explode(',', $files);

        $str = '';
        foreach ($arr as $fl){
            if (substr($fl, '-4') != '.css') continue;

			$file = CONF_THEME_PATH . 'common-css' . DIRECTORY_SEPARATOR . $fl;
			if (file_exists($file)) {
				$str .= file_get_contents($file);
			}
			/* if (!empty($_SESSION['preview_theme']) && isset($_SESSION['preview_theme']) ) {

				$Cfile = 'common-css' . DIRECTORY_SEPARATOR . $fl;
				$filesArr =  array(
					'common-css/1base.css'=>'css/css-templates/1base.css',
					'common-css/2nav.css'=>'css/css-templates/2nav.css',
					'common-css/3skeleton.css'=>'css/css-templates/3skeleton.css',
					'common-css/4phone.css'=>'css/css-templates/4phone.css'
				);
				$file = CONF_THEME_PATH . 'common-css' . DIRECTORY_SEPARATOR . $fl;

				if (file_exists($file) && !array_key_exists($Cfile,$filesArr)) {
					$str .= file_get_contents($file);
				}else if (file_exists($file) && array_key_exists($Cfile,$filesArr)) {
					$str .= $this->getPreviewThemeStr(CONF_THEME_PATH.$filesArr[$Cfile]);
				}
			}else{

				$file = CONF_THEME_PATH . 'common-css' . DIRECTORY_SEPARATOR . $fl;
				if (file_exists($file)) {
					$str .= file_get_contents($file);
				}
			} */
        }

		$str = str_replace('../', '', $str);



		if ( FatApplication::getInstance()->getQueryStringVar('min', FatUtility::VAR_INT, 0) == 1){
			$str = preg_replace('/([\n][\s]*)+/', " ", $str);
			$str = str_replace("\r", '', $str);
			$str = str_replace("\n", '', $str);
		}

		$cacheKey = $_SERVER['REQUEST_URI'];


		FatCache::set($cacheKey, $str, '.css');

        echo $str;
    }
	function getPreviewThemeStr($Cfile){

			$str= file_get_contents($Cfile);
			$selected_theme=$_SESSION['preview_theme'];
			$theme_detail = ThemeColor::getAttributesById($selected_theme);
			if(!$theme_detail){
				$selected_theme = 1;
			}
			$replace_arr=array(

					"var(--first-color)"=>$theme_detail['tcolor_first_color'],

					"var(--second-color)"=>$theme_detail['tcolor_second_color'],

					"var(--third-color)"=>$theme_detail['tcolor_third_color'],

					"var(--txt-color)"=>$theme_detail['tcolor_text_color'],

					"var(--txt-color-light)"=>$theme_detail['tcolor_text_light_color'],

					"var(--border-color)"=>$theme_detail['tcolor_border_first_color'],

					"var(--border-color-second)"=>$theme_detail['tcolor_border_second_color'],

					"var(--second-btn-color)"=>$theme_detail['tcolor_second_btn_color'],



					);
			foreach ($replace_arr as $key => $val) {

				$str = str_replace($key, "#".$val, $str);

			}
		return $str;

	}
    function js(){
    	$this->setHeaders('application/javascript');

        $arr = explode(',', $_GET['f']);

        $str = '';
        foreach ($arr as $fl){
            if (substr($fl, '-3') != '.js') continue;
            if (file_exists(CONF_THEME_PATH . $fl)) $str .= file_get_contents(CONF_THEME_PATH . $fl);
        }
		$cacheKey = $_SERVER['REQUEST_URI'];
		FatCache::set($cacheKey, $str, '.js');

        echo($str);
    }

    function jsCommon(){
    	$this->setHeaders('application/javascript');

        if ( isset($_GET['f']) ) {
        	$files = $_GET['f'];
        }
        else {
        	$pth = CONF_THEME_PATH . 'common-js';
        	$dir = opendir($pth);
        	$last_updated = 0;
        	$files = '';
        	$arrCommonfiles = scandir($pth, SCANDIR_SORT_ASCENDING);
        	foreach ($arrCommonfiles as $fl) {
        		if (!is_file($pth . DIRECTORY_SEPARATOR . $fl)) continue;
        		if ( '.js' != substr($fl, -3) ) continue;
        		if ( 'noinc-' == substr($fl, 0, 6) ) continue;

        		if ( '' != $files ) $files .= ',';
        		$files .= $fl;
        	}
        }

        $arr = explode(',', $files);

        $str = '';
        foreach ($arr as $fl){
            if (substr($fl, '-3') != '.js') continue;
            if (file_exists(CONF_THEME_PATH . 'common-js' . DIRECTORY_SEPARATOR . $fl)) $str .= file_get_contents(CONF_THEME_PATH . 'common-js' . DIRECTORY_SEPARATOR . $fl);
        }

		$cacheKey = $_SERVER['REQUEST_URI'];
		FatCache::set($cacheKey, $str, '.js');

        echo($str);
    }
}
