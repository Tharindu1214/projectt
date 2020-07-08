<?php
class ImageController extends FatController
{
    public function __construct()
    {
        CommonHelper::initCommonVariables();
    }

    public function user($recordId, $sizeType = '', $cropedImage = 0, $afile_id = 0)
    {
        $default_image = 'user_deafult_image.jpg';
        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $cropedImage = FatUtility::int($cropedImage);

        $fileType = ($cropedImage)?AttachedFile::FILETYPE_USER_PROFILE_CROPED_IMAGE:AttachedFile::FILETYPE_USER_PROFILE_IMAGE;

        if ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == $fileType) {
                $file_row = $res;
            }
        } else {
            //FILETYPE_USER_IMAGE
            //FILETYPE_f_PROFILE_IMAGE
            $file_row = AttachedFile::getAttachment($fileType, $recordId);
            if ($cropedImage && $file_row == false) {
                $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_USER_PROFILE_IMAGE, $recordId);
            }
        }

        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 150;
                $h = 150;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MINI':
                $w = 70;
                $h = 70;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'SMALL':
                $w = 200;
                $h = 200;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MEDIUM':
                $w = 500;
                $h = 500;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                /* $h = 100;
                $w = 100; */
                AttachedFile::displayOriginalImage($image_name);
                break;
        }
    }

    public function customProduct($recordId, $sizeType, $afile_id = 0, $lang_id = 0)
    {
        $default_image = 'product_default_image.jpg';
        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $lang_id = FatUtility::int($lang_id);

        if ($row) {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE, $row['afile_record_id'], $row['afile_record_subid'], $lang_id);
        } elseif ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE) {
                $file_row = $res;
            }
        }

        if ($file_row == false) {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE, $recordId, 0, $lang_id);
        }
        $image_name = isset($file_row['afile_physical_path']) ? AttachedFile::FILETYPE_PRODUCT_IMAGE_PATH . $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'SMALL':
                // image size required in product listing
                $w = 150;
                $h = 150;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MEDIUM':
                $w = 542;
                $h = 480;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    /*
    function product(){}
    ARG1-> $recordId -> required, (product_id) if passed only then will fetch default single main image
    ARG2-> $sizeType -> required, (SMALL, LARGE, THUMB) etc if passed then show image as per requested Size.
    ARG3-> $selprod_id -> selprod_id, optional, if passed, will show option value specific image if uploaded, caluclated by itself,
    ARG4-> $afile_id -> optional, if passed, will fetch direct file, but care, recordId and sizeType needs to passed, and pass selprod_id = 0
    */
    public function product($recordId, $sizeType, $selprod_id = 0, $afile_id = 0, $lang_id = 0)
    {
        $default_image = 'product_default_image.jpg';
        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $selprod_id = FatUtility::int($selprod_id);
        $lang_id = FatUtility::int($lang_id);

        /* code to fetch color specific images for a single product, and varies according to option value id, E.g: Color: White, Black, Grey[ */
        if ($selprod_id) {
            $srch = SellerProduct::getSearchObject();
            $srch->doNotCalculateRecords();
            $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'INNER JOIN', 'selprod_id = selprodoption_selprod_id', 'tspo');
            $srch->joinTable(OptionValue::DB_TBL, 'INNER JOIN', 'tspo.selprodoption_optionvalue_id = opval.optionvalue_id', 'opval');
            $srch->joinTable(Option::DB_TBL, 'INNER JOIN', 'opval.optionvalue_option_id = op.option_id', 'op');
            $srch->joinTable(AttachedFile::DB_TBL, 'INNER JOIN', 'sp.selprod_product_id = af.afile_record_id AND af.afile_record_subid =  tspo.selprodoption_optionvalue_id', 'af');
            $srch->addCondition('selprod_id', '=', $selprod_id);
            $srch->addCondition('af.afile_type', '=', AttachedFile::FILETYPE_PRODUCT_IMAGE);
            $srch->addOrder('af.afile_display_order');

            /* if( $lang_id > 0 ){ */
            $cnd = $srch->addCondition('af.afile_lang_id', '=', $lang_id);
            $cnd->attachCondition('af.afile_lang_id', '=', 0);
            $srch->addOrder('af.afile_lang_id');
            /* } */

            $srch->addDirectCondition('selprodoption_selprod_id IS NOT NULL', 'AND');
            $srch->addDirectCondition('af.afile_id IS NOT NULL', 'AND');
            $srch->setPageNumber(1);
            $srch->setPageSize(1);
            /* $srch->addMultipleFields(array('selprod_id', 'selprod_product_id', 'selprodoption_option_id', 'afile_id', 'afile_record_id', 'afile_record_subid')); */
            $srch->addMultipleFields(array( 'afile_id', 'afile_record_id', 'afile_record_subid'));
            $rs = $srch->getResultSet();
            $row = FatApp::getDb()->fetch($rs);
            /* CommonHelper::printArray($row); die(); */
        }
        /* ] */

        if ($selprod_id && $row) {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_IMAGE, $row['afile_record_id'], $row['afile_record_subid'], $lang_id);
        } elseif ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_PRODUCT_IMAGE) {
                $file_row = $res;
            }
        }

        if ($file_row == false) {
            //echo 'sds'; die("here");
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_IMAGE, $recordId, -1, $lang_id);
        }

        $image_name = isset($file_row['afile_physical_path']) ? AttachedFile::FILETYPE_PRODUCT_IMAGE_PATH . $file_row['afile_physical_path'] : '';
        /* CommonHelper::printArray($image_name); die();  */

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MINI':
                $w = 50;
                $h = 50;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false);
                break;
            case 'EXTRA-SMALL':
                $w = 60;
                $h = 60;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false);
                break;
            case 'SMALL':
                // image size required in product listing
                $w = 230;
                $h = 230;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'MEDIUM':
                $w = 500;
                $h = 500;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'CLAYOUT3':
                $w = 230;
                $h = 230;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'CLAYOUT2':
                $w = 398;
                $h = 398;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'ORIGINAL':
                $w = 2000;
                $h = 2000;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'FB_RECOMMEND':
                $w = 1200;
                $h = 630;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
        }

    }

    public function shopLogo($recordId, $lang_id = 0, $sizeType = '', $afile_id = 0, $displayUniversalImage = true)
    {
        $default_image = 'product_default_image.jpg';

        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $lang_id = FatUtility::int($lang_id);

        if ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_SHOP_LOGO) {
                $file_row = $res;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_LOGO, $recordId, 0, $lang_id, $displayUniversalImage);
        }

        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        AttachedFile::displayOriginalImage($image_name, $default_image);
    }

    public function promotion_banner($img = '', $type)
    {
        $default_image = 'product_default_image.jpg';
        switch (strtoupper($type)) {
            case 'MINI':
                return AttachedFile::displayImage($img, 50, 50, 'promotions/', 'shop_default.jpg');
            break;
            default:
                return AttachedFile::displayImage($img, 50, 50, $default_image);
        }
    }

    public function shopBanner($recordId, $lang_id = 0, $sizeType = '', $afile_id = 0, $screen = 0)
    {
        $default_image = 'product_default_image.jpg';

        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $lang_id = FatUtility::int($lang_id);

        if ($afile_id > 0) {
            $file_row = AttachedFile::getAttributesById($afile_id);
            if (false == $file_row || (!false == $file_row && $file_row['afile_type'] != AttachedFile::FILETYPE_SHOP_BANNER)) {
                return ;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_BANNER, $recordId, 0, $lang_id, true, $screen);
        }

        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'TEMP1':
                $w = 2000;
                $h = 500;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MOBILE':
                $w = 640;
                $h = 360;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            case 'TABLET':
                $w = 1024;
                $h = 360;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            case 'DESKTOP':
                $w = 2000;
                $h = 500;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function promotionMedia($recordId, $lang_id = 0, $sizeType = '', $afile_id = 0)
    {
        $default_image = 'product_default_image.jpg';

        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $lang_id = FatUtility::int($lang_id);

        if ($afile_id > 0) {
            $file_row = AttachedFile::getAttributesById($afile_id);
            if (false == $file_row || (!false == $file_row && $file_row['afile_type'] != AttachedFile::FILETYPE_PROMOTION_MEDIA)) {
                return ;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_PROMOTION_MEDIA, $recordId, 0, $lang_id);
        }

        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'TEMP2':
                $w = 1298;
                $h = 600;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'TEMP3':
                $w = 1583;
                $h = 475;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'TEMP4':
                $w = 1583;
                $h = 473;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'TEMP5':
                $w = 1440;
                $h = 600;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $w = 1298;
                $h = 600;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function shopBackgroundImage($recordId, $lang_id = 0, $sizeType = '', $afile_id = 0, $templateId = '')
    {
        switch ($templateId) {
            case Shop::TEMPLATE_ONE:
                $default_image='images/defaults/'.'logo-red.png';
                break;
            case Shop::TEMPLATE_TWO:
                $default_image='images/defaults/'.'transparent.png';
                break;
            case Shop::TEMPLATE_THREE:
                $default_image= 'images/defaults/'.'transparent.png';
                break;
            case Shop::TEMPLATE_FOUR:
                $default_image='images/defaults/'.'shop-bg.jpg';
                break;
            case Shop::TEMPLATE_FIVE:
                $default_image='images/defaults/'.'shop-5-bg.jpg';
                break;
            default:
                $h = '';
                $w = '';
                $default_image = '';
                break;
        }


        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $lang_id = FatUtility::int($lang_id);

        if ($afile_id > 0) {
            $file_row = AttachedFile::getAttributesById($afile_id);
            if (false == $file_row || (!false == $file_row && $file_row['afile_type'] != AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE)) {
                return ;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE, $recordId, 0, $lang_id);
        }

        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        if ($image_name=='' || empty($image_name)) {
            $image_name = $default_image;
        }
        switch (strtoupper($sizeType)) {
            default:
                $h = '';
                $w = '';
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function brandReal($recordId, $langId = 0, $sizeType = '', $afile_id = 0)
    {
        $this->displayBrandLogo($recordId, $langId, $sizeType, $afile_id, false);
    }

    public function brand($recordId, $langId = 0, $sizeType = '', $afile_id = 0)
    {
        $this->displayBrandLogo($recordId, $langId, $sizeType, $afile_id);
    }

    public function brandImage($recordId, $langId = 0, $sizeType = '', $afile_id = 0, $slide_screen = 0)
    {
        $this->displayBrandImage($recordId, $langId, $sizeType, $afile_id, $slide_screen);
    }

    public function displayBrandLogo($recordId, $langId = 0, $sizeType = '', $afile_id = 0, $displayUniversalImage = true)
    {
        $default_image = 'brand_deafult_image.jpg';
        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $langId = FatUtility::int($langId);

        if ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_BRAND_LOGO) {
                $file_row = $res;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BRAND_LOGO, $recordId, 0, $langId, $displayUniversalImage);
        }
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'MINITHUMB':
                $w = 42;
                $h = 52;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'THUMB':
                $w = 61;
                $h = 61;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'COLLECTION_PAGE':
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
            case 'LISTING_PAGE':
                $h = 530;
                $w = 530;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 500;
                $w = 500;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function displayBrandImage($recordId, $langId = 0, $sizeType = '', $afile_id = 0, $screen = 0, $displayUniversalImage = true)
    {
        $default_image = 'brand_deafult_image.jpg';
        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $langId = FatUtility::int($langId);

        if ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_BRAND_IMAGE) {
                $file_row = $res;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BRAND_IMAGE, $recordId, 0, $langId, $displayUniversalImage, $screen);
        }
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 61;
                $h = 61;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'COLLECTION_PAGE':
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
            case 'MOBILE':
                $w = 640;
                $h = 360;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            case 'TABLET':
                $w = 1024;
                $h = 360;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            case 'DESKTOP':
                $w = 2000;
                $h = 500;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function paymentMethod($recordId, $sizeType = '', $afile_id = 0)
    {
        $default_image = 'product_default_image.jpg';

        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);

        if ($afile_id > 0) {
            $file_row = AttachedFile::getAttributesById($afile_id);
            if (false == $file_row || (!false == $file_row && $file_row['afile_type'] != AttachedFile::FILETYPE_PAYMENT_METHOD)) {
                return ;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_PAYMENT_METHOD, $recordId);
        }

        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'SMALL':
                $w = 200;
                $h = 200;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MEDIUM':
                $w = 250;
                $h = 250;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function shopLayout($recordId, $sizeType = '')
    {
        $default_image = 'product_default_image.jpg';

        $recordId = FatUtility::int($recordId);
        $filePath = LayoutTemplate::LAYOUTTYPE_SHOP_IMAGE_PATH;

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 200;
                $h = 200;
                AttachedFile::displayImage($recordId, $w, $h, $default_image, $filePath);
                break;
            case 'SMALL':
                $w = 250;
                $h = 250;
                AttachedFile::displayImage($recordId, $w, $h, $default_image, $filePath);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($recordId, $w, $h, $default_image, $filePath);
                break;
        }
    }

    public function siteLogo($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_FRONT_LOGO, $recordId, 0, $lang_id, false);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';
        $default_image = '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 37;
                $w = 168;
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function emailLogo($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_EMAIL_LOGO, $recordId, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';
        $default_image = 'no_image.jpg';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $w = 100;
                $h = 100;
                if ($image_name=='' || empty($image_name)) {
                    AttachedFile::displayImage($image_name, $w, $h, $default_image);
                } else {
                    /* echo $image_name; die; */
                    AttachedFile::displayOriginalImage($image_name, $default_image);
                }
                break;
        }
    }

    public function socialFeed($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SOCIAL_FEED_IMAGE, $recordId, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';
        $default_image = '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 120;
                $h = 80;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 240;
                $w = 160;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function paymentPageLogo($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_PAYMENT_PAGE_LOGO, $recordId, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';
        $default_image = '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $w = 268;
                $h = 82;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function watermarkImage($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_WATERMARK_IMAGE, $recordId, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';
        $default_image = '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function appleTouchIcon($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_APPLE_TOUCH_ICON, $recordId, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';
        $default_image = '';

        switch (strtoupper($sizeType)) {
            case 'MINI':
                $w = 72;
                $h = 72;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'SMALL':
                $w = 114;
                $h = 114;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function mobileLogo($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_MOBILE_LOGO, $recordId, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';
        $default_image = '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 82;
                $w = 268;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function invoiceLogo($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_INVOICE_LOGO, $recordId, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';
        $default_image = '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 37;
                $w = 168;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function CategoryCollectionBgImage($langId = 0, $sizeType = '')
    {
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_CATEGORY_COLLECTION_BG_IMAGE, $recordId, 0, $langId);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name);
                break;
        }
    }

    public function BrandCollectionBgImage($langId = 0, $sizeType = '')
    {
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BRAND_COLLECTION_BG_IMAGE, $recordId, 0, $langId);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name);
                break;
        }
    }

    public function coupon($coupon_id, $lang_id = 0, $sizeType = '')
    {
        $coupon_id = FatUtility::int($coupon_id);

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_DISCOUNT_COUPON_IMAGE, $coupon_id, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'NORMAL':
                $w = 120;
                $h = 150;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $w = 600;
                $h = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function favicon($lang_id = 0, $sizeType = '')
    {
        /* $recordId = 0;
        $file_row = AttachedFile::getAttachment( AttachedFile::FILETYPE_FAVICON, $recordId );
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';
        $default_image = '';

        $uploadedFilePath = $file_row['afile_physical_path'];
        echo $uploadedFilePath; die();
        return $uploadedFilePath; */

        /* switch( strtoupper($sizeType) ){
        case 'THUMB':
        $w = 100;
        $h = 100;
        AttachedFile::displayImage( $image_name, $w, $h, $default_image );
        break;
        default:
        $h = 0;
        $w = 0;
        AttachedFile::displayImage( $image_name, $w, $h, $default_image );
        break;
        } */

        if ($file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_FAVICON, 0, 0, $lang_id, false)) {
            $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';
            AttachedFile::displayOriginalImage($image_name);
        }
    }

    public function slide($slide_id, $screen = 0, $lang_id, $sizeType = '', $displayUniversalImage = true)
    {
        $default_image = 'brand_deafult_image.jpg';
        $slide_id = FatUtility::int($slide_id);

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_HOME_PAGE_BANNER, $slide_id, 0, $lang_id, $displayUniversalImage, $screen);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        if ($sizeType) {
            switch (strtoupper($sizeType)) {
                case 'THUMB':
                    $w = 200;
                    $h = 100;
                    AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false, true, false);
                    break;
                case 'MOBILE':
                    $w = 640;
                    $h = 360;
                    AttachedFile::displayImage($image_name, $w, $h);
                    break;
                case 'TABLET':
                    $w = 1024;
                    $h = 360;
                    AttachedFile::displayImage($image_name, $w, $h);
                    break;
                case 'DESKTOP':
                    $w = 1350;
                    $h = 405;
                    AttachedFile::displayImage($image_name, $w, $h);
                    break;
                default:
                    $w = 1350;
                    $h = 405;
                    AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false, true, false);
                    break;
            }
        } else {
            AttachedFile::displayOriginalImage($image_name, $default_image);
        }
    }

    /* Moved in banner controller
    function banner( $banner_id, $sizeType = ''){
    $default_image = 'brand_deafult_image.jpg';
    $banner_id = FatUtility::int($banner_id);

    $file_row = AttachedFile::getAttachment( AttachedFile::FILETYPE_BANNER, $banner_id );
    $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

    switch( strtoupper( $sizeType ) ){
    case 'THUMB':
                $w = 200;
                $h = 100;
                AttachedFile::displayImage( $image_name, $w, $h, $default_image );
    break;
    default:
                $w = 1320;
                $h = 440;
                AttachedFile::displayImage( $image_name, $w, $h, $default_image );
    break;
    }
    } */

    public function SocialPlatform($splatform_id, $sizeType = '')
    {
        $default_image = 'brand_deafult_image.jpg';
        $splatform_id = FatUtility::int($splatform_id);

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SOCIAL_PLATFORM_IMAGE, $splatform_id);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 200;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $w = 30;
                $h = 30;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }


    public function collectionReal($recordId, $langId = 0, $sizeType = '', $fileType = '')
    {
        $this->displayCollectionImage($recordId, $langId, $sizeType, true, $fileType);
    }

    public function collection($recordId, $langId = 0, $sizeType = '')
    {
        $this->displayCollectionImage($recordId, $langId, $sizeType);
    }

    public function displayCollectionImage($collectionId, $langId = 0, $sizeType = '', $displayUniversalImage = true, $fileType = '')
    {
        $collectionId = FatUtility::int($collectionId);
        $fileType = empty($fileType) ? AttachedFile::FILETYPE_COLLECTION_IMAGE : $fileType;
        //$file_row = AttachedFile::getAttachment( AttachedFile::FILETYPE_COLLECTION_IMAGE, $collectionId );
        $file_row = AttachedFile::getAttachment($fileType, $collectionId, 0, $langId, $displayUniversalImage);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            case 'home':
                $w = 76;
                $h = 92;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name);
                break;
        }
    }

    public function collectionBgReal($recordId, $langId = 0, $sizeType = '')
    {
        $this->displayCollectionBgImage($recordId, $langId, $sizeType, false);
    }

    public function collectionBg($recordId, $langId = 0, $sizeType = '')
    {
        $this->displayCollectionBgImage($recordId, $langId, $sizeType);
    }

    public function displayCollectionBgImage($collectionId, $langId = 0, $sizeType = '', $displayUniversalImage = true)
    {
        $collectionId = FatUtility::int($collectionId);
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_COLLECTION_BG_IMAGE, $collectionId, 0, $langId, $displayUniversalImage);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name);
                break;
        }
    }

    public function blogPostAdmin($postId, $langId = 0, $size_type = '', $subRecordId = 0, $afile_id = 0)
    {
        $this->blogPost($postId, $langId, $size_type, $subRecordId, $afile_id, false);
    }

    public function blogPostFront($postId, $langId = 0, $size_type = '', $subRecordId = 0, $afile_id = 0)
    {
        $this->blogPost($postId, $langId, $size_type, $subRecordId, $afile_id);
    }

    public function blogPost($postId, $langId = 0, $size_type = '', $subRecordId = 0, $afile_id = 0, $displayUniversalImage = true)
    {
        $default_image = 'post_default_image.jpg';

        $langId = FatUtility::int($langId);
        $afile_id = FatUtility::int($afile_id);
        $postId = FatUtility::int($postId);
        $subRecordId = FatUtility::int($subRecordId);

        if ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_BLOG_POST_IMAGE) {
                $file_row = $res;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BLOG_POST_IMAGE, $postId, $subRecordId, $langId, $displayUniversalImage);
        }
        $image_name = isset($file_row['afile_physical_path']) ? AttachedFile::FILETYPE_BLOG_POST_IMAGE_PATH . $file_row['afile_physical_path'] : '';

        switch (strtoupper($size_type)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'SMALL':
                $w = 200;
                $h = 200;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'LAYOUT1':
                $w = 1350;
                $h = 759;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'LAYOUT2':
                $w = 645;
                $h = 363;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'FEATURED':
                $w = 446;
                $h = 251;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function BatchProduct($prodgroup_id, $lang_id, $sizeType = '')
    {
        $prodgroup_id = FatUtility::int($prodgroup_id);
        $lang_id = FatUtility::int($lang_id);
        $default_image = '';

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BATCH_IMAGE, $prodgroup_id, 0, $lang_id);

        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'SMALL':
                $w = 200;
                $h = 200;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function testimonial($recordId, $langId = 0, $sizeType = '', $afile_id = 0, $displayUniversalImage = true)
    {
        $default_image = 'user_deafult_image.jpg';
        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $langId = FatUtility::int($langId);

        if ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_TESTIMONIAL_IMAGE) {
                $file_row = $res;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_TESTIMONIAL_IMAGE, $recordId, 0, $langId, $displayUniversalImage);
        }
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'MINITHUMB':
                $w = 42;
                $h = 52;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;

            case 'THUMB':
                $w = 61;
                $h = 61;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 118;
                $w = 276;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function cpageBackgroundImage($cpageId, $langId = 0, $sizeType = '')
    {
        $cpageId = FatUtility::int($cpageId);
        $langId = FatUtility::int($langId);
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_CPAGE_BACKGROUND_IMAGE, $cpageId, 0, $langId);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            case 'COLLECTION_PAGE':
                $w = 45;
                $h = 41;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name);
                break;
        }
    }

    public function cblockBackgroundImage($cblockId, $langId = 0, $sizeType = '', $fileType)
    {
        $cblockId = FatUtility::int($cblockId);
        $langId = FatUtility::int($langId);
        $file_row = AttachedFile::getAttachment($fileType, $cblockId, 0, $langId);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            case 'DEFAULT':
                AttachedFile::displayOriginalImage($image_name);
                break;
        }
    }

    public function shopCollectionImage($recordId, $langId = 0, $sizeType = '', $displayUniversalImage = true)
    {
        $default_image = 'banner-default-image.png';
        $recordId = FatUtility::int($recordId);
        $langId = FatUtility::int($langId);
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_COLLECTION_IMAGE, $recordId, 0, $langId, $displayUniversalImage);
        $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'SHOP':
                $w = 610;
                $h = 343;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }
}
