<?php
class ShippingMethods extends MyAppModel
{
    const DB_TBL = 'tbl_shipping_apis';
    const DB_LANG_TBL = 'tbl_shipping_apis_lang';
    const DB_TBL_PREFIX = 'shippingapi_';
    const DB_LANG_TBL_PREFIX = 'shippingapilang_';
    const DB_SETTING_TBL = 'tbl_shippingapi_settings';
    const DB_SETTING_TBL_PREFIX = 'shippingapi_';

    const MANUAL_SHIPPING =1;
    const SHIPSTATION_SHIPPING =2;

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db=FatApp::getDb();
    }

    public static function getSearchObject($isActive = true, $langId = 0)
    {
        $langId = FatUtility::int($langId);

        $srch = new SearchBase(static::DB_TBL, 'sm');
        if ($isActive == true) {
            $srch->addCondition('sm.'.static::DB_TBL_PREFIX.'active', '=', applicationConstants::ACTIVE);
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'sm_l.shippingapilang_'.static::DB_TBL_PREFIX.'id = sm.'.static::DB_TBL_PREFIX.'id and sm_l.shippingapilang_lang_id = '.$langId,
                'sm_l'
            );
        }

        $srch->addOrder('sm.'.static::DB_TBL_PREFIX.'display_order', 'ASC');
        return $srch;
    }

    public static function getListingObj($langId, $attr = null)
    {
        $srch = self::getSearchObject(true, $langId);

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $srch->addMultipleFields(
            array(
            'IFNULL(sm_l.shippingapi_name,sm.shippingapi_identifier) as shippingapi_name'
            )
        );

        return $srch;
    }

    public static function getShipStationApiKeys($recordId)
    {
        $recordId = FatUtility::convertToType($recordId, FatUtility::VAR_INT);
        if (1 > $recordId) {
            return false;
        }
        $db = FatApp::getDb();

        $srch = new SearchBase(static::DB_SETTING_TBL);
        $srch->addCondition('shipsetting_shippingapi_id', '=', $recordId);
        $rs = $srch->getResultSet();
        $row = $db->fetchAll($rs);
        if ($row == false || empty($row)) {
            return false;
        }

        $data = array();
        foreach ($row as $val) {
            $data[$val['shipsetting_key']]=$val['shipsetting_value'];
        }
        return $data;
    }

    public static function getShipstationApiAttributesById($recordId, $attr = null)
    {
        $recordId = FatUtility::convertToType($recordId, FatUtility::VAR_INT);
        if (1 > $recordId) {
            return false;
        }

        $db = FatApp::getDb();

        $srch = new SearchBase(static::DB_SETTING_TBL);
        $srch->addCondition('shipsetting_shippingapi_id', '=', $recordId);

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);

        if (!is_array($row)) {
            return false;
        }

        if (is_string($attr)) {
            return $row[$attr];
        }

        return $row;
    }
}
