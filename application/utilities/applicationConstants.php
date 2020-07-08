<?php
class applicationConstants
{
    const YES = 1;
    const NO = 0;

    const DAILY = 0;
    const WEEKLY = 1;
    const MONTHLY = 2;

    const ON = 1;
    const OFF = 0;

    const ACTIVE = 1;
    const INACTIVE = 0;

    const WEIGHT_GRAM = 1;
    const WEIGHT_KILOGRAM = 2;
    const WEIGHT_POUND = 3;

    const LENGTH_CENTIMETER = 1;
    const LENGTH_METER = 2;
    const LENGTH_INCH = 3;

    const NEWS_LETTER_SYSTEM_MAILCHIMP = 1;
    const NEWS_LETTER_SYSTEM_AWEBER = 2;

    const LINK_TARGET_CURRENT_WINDOW = "_self";
    const LINK_TARGET_BLANK_WINDOW = "_blank";

    const PERCENTAGE = 1;
    const FLAT = 2;

    const PUBLISHED = 1;
    const DRAFT = 0;

    const BLOG_CONTRIBUTION_PENDING = 0;
    const BLOG_CONTRIBUTION_APPROVED = 1;
    const BLOG_CONTRIBUTION_POSTED = 2;
    const BLOG_CONTRIBUTION_REJECTED = 3;

    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;
    const GENDER_OTHER = 3;

    const DISCOUNT_COUPON = 1;
    const DISCOUNT_REWARD_POINTS = 2;

    const SCREEN_DESKTOP = 1;
    const SCREEN_IPAD = 2;
    const SCREEN_MOBILE = 3;

    const CHECKOUT_PRODUCT = 1;
    const CHECKOUT_SUBSCRIPTION = 2;
    const CHECKOUT_PPC = 3;
    const CHECKOUT_ADD_MONEY_TO_WALLET = 4;

    const SMTP_TLS = 'tls';
    const SMTP_SSL = 'ssl';

    const LAYOUT_LTR = 'ltr';
    const LAYOUT_RTL = 'rtl';

    const SYSTEM_CATALOG = 0;
    const CUSTOM_CATALOG = 1;

    const DIGITAL_DOWNLOAD_FILE = 0;
    const DIGITAL_DOWNLOAD_LINK = 1;
    const DASHBOARD_PAGE_SIZE = 3;
    const PAGE_SIZE = 20;

    const ALLOWED_HTML_TAGS_FOR_APP = '<b><strong><i><u><small><br><p><h1><h2><h3><h4><h5><h6><div><a>';

    const MOBILE_SCREEN_WIDTH = 768;

    const URL_TYPE_EXTERNAL = 1;
    const URL_TYPE_SHOP = 2;
    const URL_TYPE_PRODUCT = 3;
    const URL_TYPE_CATEGORY = 4;
    const URL_TYPE_BRAND = 5;

    public static function getWeightUnitsArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::WEIGHT_GRAM        =>    Labels::getLabel('LBL_Gram', $langId),
        static::WEIGHT_KILOGRAM    =>    Labels::getLabel('LBL_Kilogram', $langId),
        static::WEIGHT_POUND    =>    Labels::getLabel('LBL_Pound', $langId),
        );
    }

    public static function bannerTypeArr()
    {
        $bannerTypeArr = Language::getAllNames();
        return array( 0 => Labels::getLabel('LBL_All_Languages', CommonHelper::getLangId()) ) + $bannerTypeArr;
    }

    public static function digitalDownloadTypeArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::DIGITAL_DOWNLOAD_FILE    =>    Labels::getLabel('LBL_Digital_download_file', $langId),
        static::DIGITAL_DOWNLOAD_LINK    =>    Labels::getLabel('LBL_Digital_download_link', $langId),
        );
    }

    public static function getLengthUnitsArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::LENGTH_CENTIMETER    =>    Labels::getLabel('LBL_CentiMeter', $langId),
        static::LENGTH_METER        =>    Labels::getLabel('LBL_Meter', $langId),
        static::LENGTH_INCH            =>    Labels::getLabel('LBL_Inch', $langId),
        );
    }

    public static function getYesNoArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::YES => Labels::getLabel('LBL_Yes', $langId),
        static::NO => Labels::getLabel('LBL_No', $langId)
        );
    }

    public static function getActiveInactiveArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::ACTIVE => Labels::getLabel('LBL_Active', $langId),
        static::INACTIVE => Labels::getLabel('LBL_In-active', $langId)
        );
    }

    public static function getBooleanArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        1=>Labels::getLabel('LBL_True', $langId),
        0=>Labels::getLabel('LBL_False', $langId)
        );
    }

    public static function getOnOffArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::ON => Labels::getLabel('LBL_On', $langId),
        static::OFF => Labels::getLabel('LBL_Off', $langId)
        );
    }

    public static function getNewsLetterSystemArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::NEWS_LETTER_SYSTEM_MAILCHIMP => Labels::getLabel('LBL_Mailchimp', $langId),
        static::NEWS_LETTER_SYSTEM_AWEBER => Labels::getLabel('LBL_Aweber', $langId),
        );
    }

    public static function getLinkTargetsArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::LINK_TARGET_CURRENT_WINDOW        =>    Labels::getLabel('LBL_Same_Window', $langId),
        static::LINK_TARGET_BLANK_WINDOW    =>    Labels::getLabel('LBL_New_Window', $langId)
        );
    }

    /* static function getUserTypesArr($langId){
    $langId = FatUtility::int($langId);
    if($langId < 1){
    $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
    }
    return array(
    1=>Labels::getLabel('LBL_Seller', $langId),
    2=>Labels::getLabel('LBL_Buyer', $langId)
    );
    } */

    public static function getPercentageFlatArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::PERCENTAGE    =>    Labels::getLabel('LBL_Percentage', $langId),
        static::FLAT    =>    Labels::getLabel('LBL_Flat', $langId)
        );
    }

    public static function allowedMimeTypes()
    {
        return array('text/plain','image/png','image/jpeg','image/jpg','image/gif','image/bmp','image/tiff','image/svg+xml','application/zip','application/x-zip','application/x-zip-compressed','application/rar','application/x-rar','application/x-rar-compressed','application/octet-stream','audio/mpeg','video/quicktime','application/pdf','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/msword','text/plain','image/x-icon','video/mp4');
    }

    public static function allowedFileExtensions()
    {
        return array('zip','txt','png','jpeg','jpg','gif','bmp','ico','tiff','tif','svg','svgz','rar','msi','cab','mp3','qt','mov','pdf','psd','ai','eps','ps','doc','docx','mp4');
    }

    public static function getBlogPostStatusArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::DRAFT => Labels::getLabel('LBL_Draft', $langId),
        static::PUBLISHED => Labels::getLabel('LBL_Published', $langId),
        );
    }

    public static function getBlogContributionStatusArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::BLOG_CONTRIBUTION_PENDING    => Labels::getLabel('LBL_Pending', $langId),
        static::BLOG_CONTRIBUTION_APPROVED     => Labels::getLabel('LBL_Approved', $langId),
        static::BLOG_CONTRIBUTION_POSTED     => Labels::getLabel('LBL_Posted', $langId),
        static::BLOG_CONTRIBUTION_REJECTED     => Labels::getLabel('LBL_Rejected', $langId),
        );
    }

    public static function getBlogCommentStatusArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::INACTIVE=> Labels::getLabel('LBL_Pending', $langId),
        static::ACTIVE    => Labels::getLabel('LBL_Approved', $langId)
        );
    }

    public static function getGenderArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::GENDER_MALE        =>    Labels::getLabel('LBL_Male', $langId),
        static::GENDER_FEMALE    =>    Labels::getLabel('LBL_Female', $langId),
        static::GENDER_OTHER    =>    Labels::getLabel('LBL_Other', $langId),
        );
    }

    public static function getDisplaysArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::SCREEN_DESKTOP => Labels::getLabel('LBL_Desktop', $langId),
        static::SCREEN_IPAD => Labels::getLabel('LBL_Ipad', $langId),
        static::SCREEN_MOBILE => Labels::getLabel('LBL_Mobile', $langId)
        );
    }

    public static function getExcludePaymentGatewayArr()
    {
        return array(
        static::CHECKOUT_PRODUCT => array(''),
        static::CHECKOUT_SUBSCRIPTION => array(
         'CashOnDelivery',
         'Transferbank'
        ),
        static::CHECKOUT_PPC => array(
                        'CashOnDelivery',
                        'Transferbank'
        ),
        static::CHECKOUT_ADD_MONEY_TO_WALLET => array(
                        'CashOnDelivery',
                        'Transferbank'
        )
        );
    }

    public static function getCatalogTypeArr($langId)
    {
        return array(
        static::CUSTOM_CATALOG => Labels::getLabel('LBL_Custom_Products', $langId),
        static::SYSTEM_CATALOG => Labels::getLabel('LBL_Catalog_Products', $langId)
        );
    }

    public static function getCatalogTypeArrForFrontEnd($langId)
    {
        return array(
        static::SYSTEM_CATALOG => Labels::getLabel('LBL_Marketplace_Products', $langId),
        static::CUSTOM_CATALOG => Labels::getLabel('LBL_My_Private_Products', $langId)
        );
    }

    public static function getShopBannerSize()
    {
        return array(
        Shop::TEMPLATE_ONE    =>   '2000*500',
        Shop::TEMPLATE_TWO    =>   '1300*600',
        Shop::TEMPLATE_THREE  =>   '1350*410',
        Shop::TEMPLATE_FOUR   =>   '1350*410',
        Shop::TEMPLATE_FIVE   =>   '1350*570'
        );
    }

    public static function getSmtpSecureArr($langId)
    {
        return array(
        static :: SMTP_TLS       =>  Labels::getLabel('LBL_tls', $langId),
        static :: SMTP_SSL       =>   Labels::getLabel('LBL_ssl', $langId),
        );
    }

    public static function getSmtpSecureSettingsArr()
    {
        return array(
        static :: SMTP_TLS       =>  'tls',
        static :: SMTP_SSL       =>  'ssl',
        );
    }

    public static function getLayoutDirections($langId)
    {
        return array(
        static::LAYOUT_LTR=>Labels::getLabel('LBL_Left_To_Right', $langId),
        static::LAYOUT_RTL=>Labels::getLabel('LBL_Right_To_Left', $langId),
        );
    }

    public static function getMonthsArr($langId)
    {
        return array(
        '01' => Labels::getLabel('LBL_January', $langId),
        '02' => Labels::getLabel('LBL_Februry', $langId),
        '03' => Labels::getLabel('LBL_March', $langId),
        '04' => Labels::getLabel('LBL_April', $langId),
        '05' => Labels::getLabel('LBL_May', $langId),
        '06' => Labels::getLabel('LBL_June', $langId),
        '07' => Labels::getLabel('LBL_July', $langId),
        '08' => Labels::getLabel('LBL_August', $langId),
        '09' => Labels::getLabel('LBL_September', $langId),
        '10' => Labels::getLabel('LBL_October', $langId),
        '11' => Labels::getLabel('LBL_November', $langId),
        '12' => Labels::getLabel('LBL_December', $langId),
        );
    }
}
