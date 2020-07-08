<?php
class ShippingSettings
{
    const DB_SHIPPING_METHODS_TBL = 'tbl_shipping_apis';
    const DB_SHIPPING_METHODS_TBL_PREFIX = 'shippingapi_';
    const DB_SHIPPING_METHOD_SETTINGS_TBL = 'tbl_shippingapi_settings';
    const DB_SHIPPING_METHOD_SETTINGS_TBL_PREFIX = 'shipsetting_';

    private $db;
    private $error;
    private $shippingMethodKey = null;
    private $commonLangId;

    const RETURN_REQUEST_STATUS_PENDING = 0;
    const RETURN_REQUEST_STATUS_ESCALATED = 1;
    const RETURN_REQUEST_STATUS_REFUNDED = 2;
    const RETURN_REQUEST_STATUS_WITHDRAWN = 3;
    const RETURN_REQUEST_STATUS_CANCELLED = 4;

    public function __construct($methodIdentifier = '')
    {
        $this->db = FatApp::getDb();
        $this->shippingMethodKey = $methodIdentifier;
        $this->error = '';
        $this->commonLangId = CommonHelper::getLangId();
    }

    public function getError()
    {
        return $this->error;
    }

    public function saveSettings($arr)
    {
        if (empty($arr)) {
            $this->error = Labels::getLabel('ERR_Error:_Please_provide_data_to_save_settings.', $this->commonLangId);
            return false;
        }

        $shippingMethod = $this->getShippingMethodByCode($this->shippingMethodKey);
        if (!$shippingMethod) {
            $this->error = Labels::getLabel('ERR_Error:_Shipping_method_with_defined_shipping_key_does_not_exist.', $this->commonLangId);
            return false;
        }

        $shippingapi_id = $shippingMethod["shippingapi_id"];

        if (!$this->db->deleteRecords(
            static::DB_SHIPPING_METHOD_SETTINGS_TBL,
            array('smt' => static::DB_SHIPPING_METHOD_SETTINGS_TBL_PREFIX.'shippingapi_id = ?', 'vals' => array($shippingapi_id))
        )) {
            $this->error = $this->db->getError();
            return false;
        }

        foreach ($arr as $key => $val) {
            if ($key == "btn_submit") {
                continue;
            }

            $data = array(
            'shipsetting_shippingapi_id' => $shippingapi_id,
            'shipsetting_key' => $key
            );

            if (!is_array($val)) {
                $data['shipsetting_value'] = $val;
            } else {
                $data['shipsetting_value'] = serialize($val);
            }

            if (!$this->db->insertFromArray(static::DB_SHIPPING_METHOD_SETTINGS_TBL, $data, false, array('IGNORE'))) {
                $this->error = $this->db->getError();
                return false;
            }
        }
        return true    ;
    }

    public function getShippingSettings()
    {
        if (!isset($this->shippingMethodKey)) {
            $this->error = Labels::getLabel('ERR_Error:_Please_create_an_object_with_Shipping_Method_Key.', $this->commonLangId);
            return false;
        }

        $shippingMethod = $this->getShippingMethodByCode($this->shippingMethodKey);
        if (!$shippingMethod) {
            $this->error = Labels::getLabel('ERR_Error:_Shipping_method_with_this_shipping_key_does_not_exist.', $this->commonLangId);
            return false;
        }

        $shippingMethodSettings = $this->getShippingMethodFieldsById($shippingMethod["shippingapi_id"]);
        $shippingSettings = array();

        foreach ($shippingMethodSettings as $pkey => $pval) {
            $shippingSettings[$pval["shipsetting_key"]] = $pval["shipsetting_value"];
        }

        return array_merge($shippingSettings, $shippingMethod);
    }

    public function getShippingMethodByCode($code = '')
    {
        if (empty($code)) {
            return false;
        }
        $srch = new SearchBase(static::DB_SHIPPING_METHODS_TBL, 'tpm');
        $srch->addCondition('tpm.'.static::DB_SHIPPING_METHODS_TBL_PREFIX.'code', '=', $code);
        $rs = $srch->getResultSet();
        $shipping_method = $this->db->fetch($rs);
        return $shipping_method;
    }

    private function getShippingMethodFieldsById($shippingapi_id)
    {
        $srch = new SearchBase(static::DB_SHIPPING_METHOD_SETTINGS_TBL, 'tsms');
        $srch->addCondition('tsms.'.static::DB_SHIPPING_METHOD_SETTINGS_TBL_PREFIX.'shippingapi_id', '=', (int)$shippingapi_id);
        $rs = $srch->getResultSet();
        $shippingMethodSettings = $this->db->fetchAll($rs);
        return $shippingMethodSettings;
    }

    public static function getCityLists($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        $srch = new SearchBase('tbl_cities', 'ct');
        if ($langId > 0) {
            $srch->joinTable(
                'tbl_cities_lang',
                'LEFT OUTER JOIN',
                'ct_l.citylang_city_id = ct.city_id','ct_l'
                );
            $srch->addCondition('ct_l.citylang_lang_id', '=', $langId);
        }
        $srch->addCondition('city_active', '=', 1);
        $srch->addOrder('city_code', 'ASC');
       
        $rs = $srch->getResultSet();
        $cities = FatApp::getDb()->fetchAll($rs);
        $cityCollection = array();
         foreach($cities as $city){
            $cityCollection[$city['city_id']] = $city['city_name'];
        }
       /*  echo '<pre>';
        print_r($cityCollection);
        exit(); */
        return $cityCollection;
    }

    public static function getShippingMethods($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        $srch = new SearchBase('tbl_shipping_company', 'sc');
        if ($langId > 0) {
            $srch->joinTable(
                'tbl_shipping_company_lang',
                'LEFT OUTER JOIN',
                'sc_l.scompanylang_scompany_id = sc.scompany_id','sc_l'
                );
            $srch->addCondition('sc_l.scompanylang_lang_id', '=', $langId);
        }
        $srch->addCondition('scompany_active', '=', 1);
        $srch->addOrder('scompany_display_order', 'ASC');
       
        $rs = $srch->getResultSet();
        $shippingCompanies = FatApp::getDb()->fetchAll($rs);
        $shipCompanies = array();
        foreach($shippingCompanies as $shippingCompany){
            $shipCompanies[$shippingCompany['scompany_id']] = $shippingCompany['scompany_name'];
        }
        return $shipCompanies;
    }

    public static function getBusinessDays($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        $srch = new SearchBase('tbl_shipping_durations', 'sd');
        if ($langId > 0) {
            $srch->joinTable(
                'tbl_shipping_durations_lang',
                'LEFT OUTER JOIN',
                'sd_l.sdurationlang_sduration_id = sd.sduration_id','sd_l'
                );
            $srch->addCondition('sd_l.sdurationlang_lang_id', '=', $langId);
        }
        $srch->addCondition('sduration_deleted', '=', 0);
        $srch->addOrder('sduration_from', 'ASC');
        $srch->addOrder('sduration_to', 'ASC');
       
        $rs = $srch->getResultSet();
        $shippingDurations = FatApp::getDb()->fetchAll($rs);
        $shpDurations = array();
         foreach($shippingDurations as $sd){

             if($sd['sduration_from'] == $sd['sduration_to']){
                 $cond = '';
                 if($sd['sduration_from'] > 1){
                    $cond = 's';
                 }
                $shpDurations[$sd['sduration_id']] = $sd['sduration_from'].' Business Day'.$cond;
             }else{
                $shpDurations[$sd['sduration_id']] = $sd['sduration_from'].' to '.$sd['sduration_to'].' Business Days';
             }
        }
        return $shpDurations;
    }

    public function form($countryId)
    {
        $this->objPrivilege->canEditCountries();

        $countryId =  FatUtility::int($countryId);

        $frm = $this->getForm($countryId);

        if (0 < $countryId) {
            $data = Countries::getAttributesById($countryId, array('country_id','country_code','country_active','country_currency_id','country_language_id'));

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('country_id', $countryId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public static function requiredFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                               
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'ship_set_city',
                'ship_set_company',
                'ship_set_duration',
                'cost_for_1st_kg',
                'each_additional_kg',
            ),
        );
    }

    public static function validateFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

}
