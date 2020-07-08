<?php
class EditorController extends FatController
{
    private $common;
    private $task;

    public function demoPhoto($image ="", $w = 0, $h = 0)
    {
        self::displayImage($image, 5, 5, true);
    }

    public function editorImage($dir ='', $img ='')
    {
        ob_end_clean();
        if ($img == '') {
            $pth = CONF_INSTALLATION_PATH . 'user-uploads/editor/' . ltrim($dir, '/');
        } else {
            $pth = CONF_INSTALLATION_PATH . 'user-uploads/editor/' . ltrim($dir, '/'). '/' . ltrim($img, '/');
        }


        if (!is_file($pth)) {
            $pth = 'images/defaults/no_image.jpg';
        }
        $fileMimeType = mime_content_type($pth);
        /*  if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($pth))) {
          header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($pth)).' GMT', true, 304);
          exit;
        }

        header('Cache-Control: public');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($pth)).' GMT', true, 200);
        */

        $ext = pathinfo($pth, PATHINFO_EXTENSION);
        if ($ext == "svg") {
            CommonHelper::editorSvg($pth);
            exit;
        }

        $size = getimagesize($pth);

        if ($size) {
            list($w, $h) = getimagesize($pth);
        } else {
            /* $obj = new imageResize($pth);
            $obj->setMaxDimensions($w, $h);
            $obj->setResizeMethod(imageResize::IMG_RESIZE_EXTRA_ADDSPACE); */
        }
        $obj = new ImageResize($pth);
        $obj->setMaxDimensions($w, $h);
        $obj->setResizeMethod(imageResize::IMG_RESIZE_EXTRA_ADDSPACE);

        if ($fileMimeType != '') {
            header("content-type: ".$fileMimeType);
        } else {
            header("Content-Type: ".$size['mime']);
        }
        $obj->displayImage(80, false);
    }
}
