<?php
class InnovaController extends AdminBaseController
{
    public function assetmanager() 
    {

        $calledFileArgsArr = func_get_args();
        $calledFileArgsArr = array_reverse($calledFileArgsArr);
        $fileName = $calledFileArgsArr[0];
        $ext = substr($fileName, strrpos($fileName, '.') + 1);

        $content_type = '';
        switch (strtolower($ext)) {
        case 'js' :
            $content_type = 'application/x-javascript';
            break;
        case 'jpg' :
        case 'jpeg' :
        case 'jpe' :
            //$content_type = 'image/jpeg';
            break;
        case 'png' :
        case 'gif' :
        case 'bmp' :
        case 'tiff' :
            //$content_type = 'image/'.strtolower($ext);
            break;
        case 'css' :
            $content_type = 'text/css';
            break;
        case 'xml' :
            $content_type = 'application/xml';
            break;
        case 'html' :
        case 'htm':
            $content_type = 'text/html';
            break;
        case 'swf' :
            $content_type = 'application/x-shockwave-flash';
            break;
        case 'eot' :
            //$content_type = 'application/vnd.ms-fontobject';
            break;
        case 'ttf' :
            //$content_type = 'application/x-font-ttf';
            break;
        case 'woff' :
            //$content_type = 'application/x-font-woff';
            break;
        case 'svg' :
            //$content_type = 'image/svg+xml';
            break;
          /* default :
          die('Unknown file type.'); */
        }

        if($content_type != "" ) {
            header('Content-Type: '.$content_type);
        }

        include_once CONF_THEME_PATH . 'assetmanager/' . implode('/', func_get_args());
    }
}