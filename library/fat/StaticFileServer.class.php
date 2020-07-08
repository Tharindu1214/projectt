<?php
class StaticFileServer {
	public function serveAbsoluteFile($f) {
		$path = $f;
		if (!file_exists($path)) {
			FatUtility::exitWithErrorCode(404);
		}
		
		header('Content-Type: '.StaticFileServer::mimeType($path));
		
		$headers = FatApp::getApacheRequestHeaders();
		
		if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($path))) {
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($path)).' GMT', true, 304);
			exit;
		}
		
		header('Cache-Control: public');
		header("Pragma: public");
		
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($path)).' GMT', true, 200);
		header("Expires: " . date('r', strtotime("+30 Day")), true);
		
		readfile($path);
		
		exit();
	}
	
	public function serveFile($url) {
		$this->serveAbsoluteFile(CONF_THEME_PATH . $url);
	}
	
	public static function mimeType($path) {
		$ext = substr($path, strrpos($path, '.') + 1);
		switch (strtolower($ext)) {
			case 'js' :
				return 'application/x-javascript';
			case 'jpg' :
			case 'jpeg' :
			case 'jpe' :
				return 'image/jpeg';
			case 'png' :
			case 'gif' :
			case 'bmp' :
			case 'tiff' :
				return 'image/'.strtolower($ext);
			case 'css' :
				return 'text/css';
			case 'xml' :
				return 'application/xml';
			case 'html' :
			case 'htm':
				return 'text/html';
			case 'swf' :
				return 'application/x-shockwave-flash';
			case 'eot' :
				return 'application/vnd.ms-fontobject';
			case 'ttf' :
				return 'application/x-font-ttf';
			case 'woff' :
				return 'application/x-font-woff';
			case 'svg' :
				return 'image/svg+xml';
			default :
				die('Unknown file type.');
		}
	}
}