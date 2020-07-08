<?php
class Extrapage extends MyAppModel
{
    const DB_TBL = 'tbl_extra_pages';
    const DB_TBL_PREFIX = 'epage_';

    const DB_TBL_LANG = 'tbl_extra_pages_lang';
    const DB_TBL_LANG_PREFIX = 'epagelang_';

    const CONTACT_US_CONTENT_BLOCK = 1;
    const LOGIN_PAGE_RIGHT_BLOCK = 13;
    const REGISTRATION_PAGE_RIGHT_BLOCK = 14;
    const FORGOT_PAGE_RIGHT_BLOCK = 15;
    const SELLER_PAGE_BLOCK1 = 16;
    const SELLER_PAGE_BLOCK2 = 17;
    const SELLER_PAGE_BLOCK3 = 25;
    const SELLER_BANNER_SLOGAN = 18;
    const RESET_PAGE_RIGHT_BLOCK = 19;
    const SUBSCRIPTION_PAGE_BLOCK = 20;
    const ADVERTISER_BANNER_SLOGAN = 21;
    const AFFILIATE_BANNER_SLOGAN = 22;
    const CHECKOUT_PAGE_RIGHT_BLOCK = 23;
    const SELLER_PAGE_FORM_TEXT = 24;
    const FOOTER_TRUST_BANNERS = 26;
    const CHECKOUT_PAGE_HEADER_BLOCK = 27;

    const ADMIN_PRODUCTS_CATEGORIES_INSTRUCTIONS = 28;
    const GENERAL_SETTINGS_INSTRUCTIONS = 29;
    const ADMIN_BRANDS_INSTRUCTIONS = 30;
    const ADMIN_OPTIONS_INSTRUCTIONS = 31;
    const ADMIN_TAGS_INSTRUCTIONS = 32;
    const ADMIN_COUNTRIES_MANAGEMENT_INSTRUCTIONS = 33;
    const ADMIN_STATE_MANAGEMENT_INSTRUCTIONS = 34;
    const ADMIN_CATALOG_MANAGEMENT_INSTRUCTIONS = 35;
    const SELLER_CATALOG_MANAGEMENT_INSTRUCTIONS = 36;
    const SELLER_GENERAL_SETTINGS_INSTRUCTIONS = 37;
    const ADMIN_PRODUCT_INVENTORY_INSTRUCTIONS = 38;
    const SELLER_PRODUCT_INVENTORY_INSTRUCTIONS = 39;
    const PRODUCT_INVENTORY_UPDATE_INSTRUCTIONS = 40;
    const MARKETPLACE_PRODUCT_INSTRUCTIONS = 41;
    const SELLER_INVENTORY_INSTRUCTIONS = 42;
    const PRODUCT_REQUEST_INSTRUCTIONS = 43;
    const ADMIN_TYPE_POLICY_POINTS = 44;
    const ADMIN_CITY_MANAGEMENT_INSTRUCTIONS = 45;

    const CONTENT_PAGES = 0;
    const CONTENT_IMPORT_INSTRUCTION = 1;

    const REWRITE_URL_PREFIX = 'custom/view/';

    public function __construct($epageId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $epageId);
    }

    public static function getSearchObject($langId = 0, $isActive = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'ep');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'ep_l.'.static::DB_TBL_LANG_PREFIX.'epage_id = ep.'.static::tblFld('id').' and
			ep_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = '.$langId,
                'ep_l'
            );
        }

        if ($isActive) {
            $srch->addCondition('epage_active', '=', applicationConstants::ACTIVE);
        }

        return $srch;
    }

    public static function getContentBlockArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::CONTACT_US_CONTENT_BLOCK => Labels::getLabel('LBL_Contact_Us_Content_Block', $langId),
        static::LOGIN_PAGE_RIGHT_BLOCK => Labels::getLabel('LBL_Login_Page_Right_Block', $langId),
        static::REGISTRATION_PAGE_RIGHT_BLOCK => Labels::getLabel('LBL_Registration_Page_Right_Block', $langId),
        static::FORGOT_PAGE_RIGHT_BLOCK => Labels::getLabel('LBL_Forgot_Page_Right_Block', $langId),
        static::RESET_PAGE_RIGHT_BLOCK => Labels::getLabel('LBL_Reset_Page_Right_Block', $langId),
        static::SELLER_PAGE_BLOCK1 => Labels::getLabel('LBL_Seller_Page_Block1', $langId),
        static::SELLER_PAGE_BLOCK2 => Labels::getLabel('LBL_Seller_Page_Block2', $langId),
        static::SELLER_PAGE_BLOCK3 => Labels::getLabel('LBL_Seller_Page_Block3', $langId),
        static::SELLER_BANNER_SLOGAN => Labels::getLabel('LBL_Seller_Banner_Slogan', $langId),
        static::SUBSCRIPTION_PAGE_BLOCK => Labels::getLabel('LBL_Subscription_Page_Block', $langId),
        static::ADVERTISER_BANNER_SLOGAN => Labels::getLabel('LBL_Advertiser_Banner_Slogan', $langId),
        static::AFFILIATE_BANNER_SLOGAN => Labels::getLabel('LBL_Affiliate_Banner_Slogan', $langId),
        );
    }

    public function updatePageContent($data = array())
    {
        if (! ($this->mainTableRecordId > 0)) {
            $this->error = Labels::getLabel('MSG_Invalid_Request', $this->commonLangId);
            return false;
        }

        $epage_id = FatUtility::int($data['epage_id']);
        unset($data['btn_submit']);
        unset($data['epage_id']);

        $assignValues = $data;
        /* $assignValues = array(
        'epage_identifier'=>$data['epage_identifier'],
        ); */

        if (!FatApp::getDb()->updateFromArray(
            static::DB_TBL,
            $assignValues,
            array('smt' => static::DB_TBL_PREFIX . 'id = ? ', 'vals' => array((int)$epage_id))
        )) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }

        /* $assignValues = array(
        'epage_active'=>$data['epage_active'],
        );
        FatApp::getDb()->updateFromArray(static::DB_TBL, $assignValues,
        array('smt' => static::DB_TBL_PREFIX . 'id = ? and epage_default = ?', 'vals' => array((int)$epage_id,0))); */

        return true;
    }

    public function getContentByPageType($pageType = '', $langId = 0)
    {
        if ($pageType == '') {
            return;
        }
        $langId = FatUtility::int($langId);

        $srch = self::getSearchObject($langId);
        $srch->addCondition('ep.epage_type', '=', $pageType);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        return $pageData = FatApp::getDb()->fetch($rs);
    }

    public static function getContentBlockArrWithBg($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::SELLER_BANNER_SLOGAN => Labels::getLabel('LBL_Seller_Banner_Slogan', $langId),
        static::ADVERTISER_BANNER_SLOGAN => Labels::getLabel('LBL_Advertiser_Banner_Slogan', $langId),
        static::AFFILIATE_BANNER_SLOGAN => Labels::getLabel('LBL_Affiliate_Banner_Slogan', $langId),
        );
    }

    public function rewriteUrl($keyword)
    {
        if ($this->mainTableRecordId < 1) {
            return false;
        }

        $originalUrl = static::REWRITE_URL_PREFIX.$this->mainTableRecordId;

        $seoUrl =  CommonHelper::seoUrl($keyword);

        $customUrl = UrlRewrite::getValidSeoUrl($seoUrl, $originalUrl, $this->mainTableRecordId);

        return UrlRewrite::update($originalUrl, $customUrl);
    }
}
