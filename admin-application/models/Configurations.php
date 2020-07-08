<?php
class Configurations extends FatModel
{
    const DB_TBL = 'tbl_configurations';
    const DB_TBL_PREFIX = 'conf_';
    private $db;

    const FORM_GENERAL = 1;
    const FORM_LOCAL = 2;
    const FORM_SEO = 3;
    const FORM_PRODUCT = 4;
    const FORM_AFFILIATE = 5;
    const FORM_REWARD_POINTS = 6;
    const FORM_REVIEWS = 7;
    const FORM_LIVE_CHAT = 8;
    const FORM_THIRD_PARTY_API = 9;
    const FORM_EMAIL = 10;
    const FORM_SERVER = 11;
    const FORM_SHARING = 12;
    const FORM_REFERAL = 13;
    const FORM_MEDIA = 14;
    const FORM_DISCOUNT = 15;
    const FORM_SUBSCRIPTION = 16;
    const FORM_SYSTEM = 17;
    const FORM_PPC = 18;
    const FORM_IMPORT_EXPORT = 19;
    const FORM_CHECKOUT_PROCESS = 20;
    const FORM_USER_ACCOUNT = 21;
    const FORM_CART_WISHLIST = 22;
    const FORM_COMMISSION = 23;

    public function __construct()
    {
        parent::__construct();
    }

    public static function getLangTypeFormArr()
    {
        return  array(
        Configurations::FORM_GENERAL,
        Configurations::FORM_EMAIL,
        Configurations::FORM_SHARING,
        Configurations::FORM_MEDIA,
        Configurations::FORM_PPC,
        Configurations::FORM_SERVER,
        );
    }

    public static function getTabsArr()
    {
        $adminLangId = CommonHelper::getLangId();
        $additionalArr  = array();
        if (FatApp::getConfig('CONF_ENABLE_IMPORT_EXPORT')) {
            $additionalArr = array(Configurations::FORM_IMPORT_EXPORT => Labels::getLabel('MSG_IMPORT_EXPORT', $adminLangId),);
        }
        $configurationArr =  array(
        Configurations::FORM_GENERAL =>Labels::getLabel('MSG_General', $adminLangId),
        Configurations::FORM_LOCAL =>Labels::getLabel('MSG_Local', $adminLangId),
        Configurations::FORM_SEO =>Labels::getLabel('MSG_Seo', $adminLangId),
        Configurations::FORM_USER_ACCOUNT =>Labels::getLabel('MSG_Account', $adminLangId),
        Configurations::FORM_PRODUCT =>Labels::getLabel('MSG_Product', $adminLangId),
        Configurations::FORM_CART_WISHLIST =>Labels::getLabel('MSG_Cart/Wishlist', $adminLangId),
        Configurations::FORM_CHECKOUT_PROCESS =>Labels::getLabel('MSG_Checkout', $adminLangId),
        Configurations::FORM_COMMISSION =>Labels::getLabel('MSG_Commission', $adminLangId),
        Configurations::FORM_DISCOUNT => Labels::getLabel('MSG_Discount', $adminLangId),
        Configurations::FORM_REWARD_POINTS => Labels::getLabel('MSG_REWARD_POINTS', $adminLangId),
        Configurations::FORM_AFFILIATE => Labels::getLabel('MSG_AFFILIATE', $adminLangId),
        Configurations::FORM_REVIEWS => Labels::getLabel('MSG_REVIEWS', $adminLangId),
        Configurations::FORM_THIRD_PARTY_API =>Labels::getLabel('MSG_Third_Party_API', $adminLangId),
        Configurations::FORM_EMAIL =>Labels::getLabel('MSG_Email', $adminLangId),
        Configurations::FORM_MEDIA => Labels::getLabel('MSG_Media', $adminLangId),
        Configurations::FORM_SUBSCRIPTION => Labels::getLabel('MSG_Subscription', $adminLangId),
        Configurations::FORM_REFERAL =>Labels::getLabel('MSG_Referal', $adminLangId),
        Configurations::FORM_SHARING =>Labels::getLabel('MSG_Sharing', $adminLangId),
        Configurations::FORM_SYSTEM => Labels::getLabel('MSG_System', $adminLangId),
        Configurations::FORM_LIVE_CHAT =>Labels::getLabel('MSG_Live_Chat', $adminLangId),
        Configurations::FORM_PPC => Labels::getLabel('MSG_PPC_Management', $adminLangId),
        Configurations::FORM_SERVER => Labels::getLabel('MSG_SERVER', $adminLangId),
        );
        return $configurationArr+$additionalArr;
    }


    public static function dateFormatPhpArr()
    {
        return array( 'Y-m-d' => 'Y-m-d', 'd/m/Y' => 'd/m/Y', 'm-d-Y' => 'm-d-Y', 'M d, Y' => 'M d, Y');
    }

    public static function dateFormatMysqlArr()
    {
        return array('%Y-%m-%d','%d/%m/%Y','%m-%d-%Y','%b %d, %Y');
    }

    public static function dateTimeZoneArr()
    {
        $arr = DateTimeZone::listIdentifiers();
        $arr=array_combine($arr, $arr);
        return $arr;
    }

    public static function getConfigurations()
    {
        $srch = new SearchBase(static::DB_TBL, 'conf');
        $rs = $srch->getResultSet();
        $record = array();
        while ($row = FatApp::getDb()->fetch($rs)) {
            $record [strtoupper($row['conf_name'])] = $row['conf_val'];
        }
        return $record;
    }

    public function update($data)
    {
        foreach ($data as $key => $val) {
            $assignValues = array('conf_name'=>$key,'conf_val'=>$val);
            FatApp::getDb()->insertFromArray(
                static::DB_TBL,
                $assignValues,
                false,
                array(),
                $assignValues
            );
        }
        return true;
    }
}
