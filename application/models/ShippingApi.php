<?php
class ShippingApi extends MyAppModel
{
    const DB_TBL = 'tbl_shipping_apis';
    const DB_TBL_PREFIX = 'shippingapi_';
    const DB_TBL_LANG = 'tbl_shipping_apis_lang';
    const DB_TBL_LANG_PREFIX = 'shippingapilang_';
    const DB_TBL_PRODUCT_SHIPPING_RATES = 'tbl_product_shipping_rates';
    const DB_TBL_PRODUCT_SHIPPING_RATES_PREFIX = 'pship_';
    const DB_TBL_PRODUCT_SHIPPING_DURATION = 'tbl_shipping_durations';
    const DB_TBL_PRODUCT_SHIPPING_DURATION_PREFIX = 'sduration_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db=FatApp::getDb();
    }
    public static function getSearchObject($isActive = true, $langId = 0)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'c');

        if ($isActive==true) {
            $srch->addCondition('c.'.static::DB_TBL_PREFIX.'active', '=', applicationConstants::ACTIVE);
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'c_l.'.static::DB_TBL_LANG_PREFIX.'shippingapi_id = c.'.static::tblFld('id').' and
			c_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = '.$langId,
                'c_l'
            );
        }

        return $srch;
    }

    /* public static function getShippingDurations($product_id , $langId, &$msg){
    $product_id = FatUtility::int($product_id);
    if($product_id>0!=true) return array();
    $srch = new SearchBase(static::DB_TBL_PRODUCT_SHIPPING_RATES, 'tpsr');
    $srch->joinTable(Countries::DB_TBL, 'LEFT JOIN', 'tpsr.pship_country=tc.country_id', 'tc');
    $srch->joinTable(Countries::DB_TBL_LANG, 'LEFT JOIN', 'tpsr.pship_country=tcl.countrylang_country_id', 'tcl');
    $srch->joinTable(static::DB_TBL_PRODUCT_SHIPPING_DURATION, 'INNER JOIN', 'tpsr.pship_duration=tsd.sduration_id and tsd.sduration_deleted=0', 'tsd');
    $srch->addCondition('tpsr.pship_prod_id', '=', intval($product_id));
    $srch->addOrder('(`pship_country` = -1),tcl.country_name');
    $srch->addMultipleFields(array('tpsr.*','tc.*','tsd.*'));
    // echo $srch->getQuery(); die;
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs=$srch->getResultSet();
        $row = FatApp::getDb()->fetchAll($rs);
        if($row==false) return array();
        else return $row;
    } */
    public static function getShippingDurations($shippingApiId, $langId, &$msg)
    {
        $shippingApiId = FatUtility::int($shippingApiId);
        $data = array();

        if (1 > $shippingApiId) {
            $msg = Labels::getLabel('MSG_Invalid_Access', $langId);
            return false;
        }

        $row = ShippingMethods::getAttributesById($shippingApiId);
        if ($row == false) {
            $msg = Labels::getLabel('MSG_Invalid_Access', $langId);
            return false;
        }

        switch (strtoupper($row['shippingapi_code'])) {
            case 'EFALAK_SHIPPING':
                $srch = ShippingDurations::getListingObj($langId, array('sduration_id', 'sduration_from', 'sduration_to', 'sduration_days_or_weeks'));
                $srch->doNotCalculateRecords();
                $rs = $srch->getResultSet();
                $res = FatApp::getDb()->fetchAll($rs);
                return $res;
             break;
        }
        return $data;
    }
    /* weight accepted in grams, and lengths in centimeter. */
    public static function getShippingPrice($data = array(), $langId = 0, &$msg)
    {
        $langId = FatUtility::int($langId);
        $langId = ($langId == 0)?1:$langId;

        $mshipapi_sduration_id = isset($data['mshipapi_sduration_id']) ? FatUtility::int($data['mshipapi_sduration_id']) : 0;
        if (1 > $mshipapi_sduration_id) {
            $msg =  Labels::getLabel('MSG_Invalid_Access', $langId);
            return false;
        }

        if (isset($data['weight']) && $data['weight'] != '' && (!isset($data['weight_unit']) || $data['weight_unit'] == '')) {
            $msg =  Labels::getLabel('MSG_Invalid_Access', $langId);
            return false;
        }

        $weight        = isset($data['weight'])    ?    CommonHelper::getWeightInGrams($data['weight_unit'], $data['weight'])    : 0;
        $length     = isset($data['length'])    ?    $data['length']    : 0;
        $width        = isset($data['width'])        ?    $data['width']    : 0;
        $height        = isset($data['height'])    ?    $data['height']    : 0;
        $zipCode    = isset($data['zipCode'])    ?    $data['zipCode']: '';
        $state        = isset($data['state'])        ?    $data['state']    : 0;
        $country    = isset($data['country'])    ?    $data['country']: 0;

        $volume = ($length * $width * $height);
        $volume = CommonHelper::getVolumeInCC($data['product_dimension_unit'], $volume);
        if ($volume == 0 || $weight == 0) {
            $msg =  Labels::getLabel('MSG_Volume_and_weight_should_not_be_null', $langId);
            return false;
        }

        $srch = new SearchBase(ManualShippingApi::DB_TBL, 'msapi');
        $srch->joinTable(
            ManualShippingApi::DB_TBL_LANG,
            'LEFT OUTER JOIN',
            'msapi_l.mshipapilang_mshipapi_id = msapi.mshipapi_id AND msapi_l.mshipapilang_lang_id = ' . $langId,
            'msapi_l'
        );
        $srch->joinTable('tbl_shipping_durations', 'LEFT OUTER JOIN', 'msapi.mshipapi_sduration_id = sd.sduration_id', 'sd');
        $srch->addCondition('sd.sduration_deleted', '=', applicationConstants::NO);
        $srch->addMultipleFields(array('mshipapi_id','mshipapi_cost','mshipapi_comment'));
        $srch->addCondition('mshipapi_sduration_id', '=', $mshipapi_sduration_id);
        $srch->addCondition('mshipapi_volume_upto', '>=', $volume);
        $srch->addCondition('mshipapi_weight_upto', '>=', $weight);

        if ($zipCode != '') {
            $cnd = $srch->addCondition('mshipapi_zip', '=', '', 'AND');
            $cnd->attachCondition('mshipapi_zip', '=', $zipCode, 'OR');
        } else {
            $srch->addCondition('mshipapi_zip', '=', '');
        }

        if ($state != '') {
            $cnd = $srch->addCondition('mshipapi_state_id', '=', '-1', 'AND');
            $cnd->attachCondition('mshipapi_state_id', '=', $state, 'OR');
        } else {
            $srch->addCondition('mshipapi_state_id', '=', '-1');
        }

        if ($country != '') {
            $cnd = $srch->addCondition('mshipapi_country_id', '=', 0, 'AND');
            $cnd->attachCondition('mshipapi_country_id', '=', $country, 'OR');
        } else {
            $srch->addCondition('mshipapi_country_id', '=', 0);
        }

        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addOrder('mshipapi_volume_upto', 'asc');
        $srch->addOrder('mshipapi_weight_upto', 'asc');

        $rs = $srch->getResultSet();
        if (!$rs) {
            $msg =  Labels::getLabel('MSG_Invalid_Access', $langId);
            return false;
        }

        $res = FatApp::getDb()->fetch($rs);
        return $res;
    }


    public static function getAttributesById($recordId, $attr = null)
    {
        $recordId = FatUtility::convertToType($recordId, FatUtility::VAR_INT);
        if (1 > $recordId) {
            return false;
        }

        $db = FatApp::getDb();

        $srch = new SearchBase(static::DB_TBL);
        $srch->addCondition('shippingapi_id', '=', $recordId);

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

    public static function getAttributesByLangId($langId, $recordId, $attr = null)
    {
        $recordId = FatUtility::convertToType($recordId, FatUtility::VAR_INT);
        $langId = FatUtility::convertToType($langId, FatUtility::VAR_INT);

        $db = FatApp::getDb();
        $srch = new SearchBase(static::DB_TBL . '_lang', 'ln');
        $prefix = substr(static::DB_TBL_PREFIX, 0, -1);
        $srch->addCondition('ln.'.$prefix . 'lang_' . static::DB_TBL_PREFIX . 'id', '=', $recordId);
        $srch->addCondition('ln.'.$prefix . 'lang_lang_id', '=', FatUtility::int($langId));

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



    /* function getWeightInGrams($type,$val){
    $type = strtoupper($type);
    switch($type){
    case 'GM':
    case 'GRAMS':
    case 'ML':
    case 'MILI LITRES':
                $weight = $val;
    break;

    case 'PN':
    case 'POUNDS':
                $weight = $val * 453.592;
    break;

    case 'OU':
    case 'OUNCE':
                $weight = $val * 28.3495;
    break;

    case 'LTR':
    case 'LITRES':
    case 'KG':
    case 'KILOGRAM':
                $weight = $val * 0.001;
    break;
    }
    return $weight;
    } */
}
