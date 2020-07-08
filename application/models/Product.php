<?php
class Product extends MyAppModel
{
    const DB_TBL = 'tbl_products';
    const DB_LANG_TBL ='tbl_products_lang';
    const DB_TBL_PREFIX = 'product_';

    const DB_NUMERIC_ATTRIBUTES_TBL = 'tbl_product_numeric_attributes';
    const DB_NUMERIC_ATTRIBUTES_PREFIX = 'prodnumattr_';

    const DB_TEXT_ATTRIBUTES_TBL = 'tbl_product_text_attributes';
    const DB_TEXT_ATTRIBUTES_PREFIX = 'prodtxtattr_';

    const DB_TBL_PRODUCT_TO_CATEGORY = 'tbl_product_to_category';
    const DB_TBL_PRODUCT_TO_CATEGORY_PREFIX = 'ptc_';

    const DB_PRODUCT_TO_OPTION = 'tbl_product_to_options';
    const DB_PRODUCT_TO_OPTION_PREFIX = 'prodoption_';

    const DB_PRODUCT_TO_SHIP = 'tbl_product_shipping_rates';
    const DB_PRODUCT_TO_SHIP_PREFIX = 'pship_';

    const DB_PRODUCT_TO_TAG = 'tbl_product_to_tags';
    const DB_PRODUCT_TO_TAG_PREFIX = 'ptt_';

    const DB_TBL_PRODUCT_FAVORITE = 'tbl_user_favourite_products';

    const DB_PRODUCT_SPECIFICATION = 'tbl_product_specifications';
    const DB_PRODUCT_SPECIFICATION_PREFIX = 'prodspec_';

    const DB_PRODUCT_LANG_SPECIFICATION = 'tbl_product_specifications_lang';
    const DB_PRODUCT_LANG_SPECIFICATION_PREFIX = 'prodspeclang_';

    const DB_TBL_PRODUCT_SHIPPING = 'tbl_products_shipping';
    const DB_TBL_PRODUCT_SHIPPING_PREFIX = 'ps_';

    const DB_PRODUCT_SHIPPED_BY_SELLER = 'tbl_products_shipped_by_seller';
    const DB_PRODUCT_SHIPPED_BY_SELLER_PREFIX = 'psbs_';

    const DB_PRODUCT_MIN_PRICE = 'tbl_products_min_price';
    const DB_PRODUCT_MIN_PRICE_PREFIX = 'pmp_';

    const PRODUCT_TYPE_PHYSICAL = 1;
    const PRODUCT_TYPE_DIGITAL = 2;

    const APPROVED = 1;
    const UNAPPROVED = 0;

    const INVENTORY_TRACK = 1;
    const INVENTORY_NOT_TRACK = 0;

    const CONDITION_NEW = 1;
    const CONDITION_USED = 2;
    const CONDITION_REFURBISH = 3;

    const PRODUCT_VIEW_ORGINAL_URL ='products/view/';
    const PRODUCT_REVIEWS_ORGINAL_URL ='reviews/product/';
    const PRODUCT_MORE_SELLERS_ORGINAL_URL ='products/sellers/';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($langId = 0, $isDeleted = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'tp');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'productlang_product_id = tp.product_id	AND productlang_lang_id = ' . $langId,
                'tp_l'
            );
        }

        if ($isDeleted) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'deleted', '=', applicationConstants::NO);
        }

        $srch->addOrder(static::DB_TBL_PREFIX . 'active', 'DESC');
        return $srch;
    }

    public static function requiredFields($prodType = PRODUCT::PRODUCT_TYPE_PHYSICAL)
    {
        $arr = array(
        ImportexportCommon::VALIDATE_POSITIVE_INT => array(
        'product_id',
        'product_brand_id',
        'category_Id',
        'tax_category_id',
        'product_min_selling_price',
        ),
        ImportexportCommon::VALIDATE_NOT_NULL => array(
        'product_name',
        'product_identifier',
        'credential_username',
        'category_indentifier',
        'brand_identifier',
        'product_type_identifier',
        'tax_category_identifier'
        ),
        ImportexportCommon::VALIDATE_INT => array(
        'product_seller_id',
        'product_type',
        'product_ship_free',
        ),
        );

        if (FatApp::getConfig('CONF_PRODUCT_DIMENSIONS_ENABLE', FatUtility::VAR_INT, 0) && $prodType == PRODUCT::PRODUCT_TYPE_PHYSICAL) {
            $physical = array(
                'product_dimension_unit_identifier',
                'product_weight_unit_identifier',
                'product_length',
                'product_width',
                'product_height',
                'product_weight',
                );
            $arr[ImportexportCommon::VALIDATE_NOT_NULL] = array_merge($arr[ImportexportCommon::VALIDATE_NOT_NULL], $physical);
        }

        if (FatApp::getConfig('CONF_PRODUCT_MODEL_MANDATORY', FatUtility::VAR_INT, 0)) {
            $physical = array(
                'product_model',
                );
            $arr[ImportexportCommon::VALIDATE_NOT_NULL] = array_merge($arr[ImportexportCommon::VALIDATE_NOT_NULL], $physical);
        }

        return $arr;
    }

    public static function validateFields($columnIndex, $columnTitle, $columnValue, $langId, $prodType = PRODUCT::PRODUCT_TYPE_PHYSICAL)
    {
        $requiredFields = static::requiredFields($prodType);
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredMediaFields()
    {
        return array(
        ImportexportCommon::VALIDATE_POSITIVE_INT => array(
        'product_id',
        ),
        ImportexportCommon::VALIDATE_NOT_NULL => array(
        'product_identifier',
        'afile_physical_path',
        'afile_name',
        ),
        );
    }

    public static function validateMediaFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredMediaFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredShippingFields()
    {
        return array(
        ImportexportCommon::VALIDATE_POSITIVE_INT => array(
        'product_id',
        'country_id',
        'scompany_id',
        'sduration_id',
        'pship_charges',
        ),
        ImportexportCommon::VALIDATE_NOT_NULL => array(
        'product_identifier',
        'credential_username',
        'scompany_identifier',
        'sduration_identifier',
        'user_id',
        ),
        );
    }

    public static function validateShippingFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredShippingFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function getApproveUnApproveArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::UNAPPROVED => Labels::getLabel('LBL_Un-Approved', $langId),
        static::APPROVED => Labels::getLabel('LBL_Approved', $langId),
        );
    }

    public static function getInventoryTrackArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::INVENTORY_TRACK => Labels::getLabel('LBL_Track', $langId),
        static::INVENTORY_NOT_TRACK => Labels::getLabel('LBL_Do_not_track', $langId)
        );
    }

    public static function getConditionArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::CONDITION_NEW => Labels::getLabel('LBL_New', $langId),
        static::CONDITION_USED => Labels::getLabel('LBL_Used', $langId),
        static::CONDITION_REFURBISH => Labels::getLabel('LBL_Refurbished', $langId)
        );
    }

    public static function getProductTypes($langId = 0)
    {
        $langId = FatUtility::convertToType($langId, FatUtility::VAR_INT);
        if (!$langId) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $langId), E_USER_ERROR);
            return false;
        }
        return array(
        self::PRODUCT_TYPE_PHYSICAL => Labels::getLabel('LBL_Physical', $langId),
        self::PRODUCT_TYPE_DIGITAL => Labels::getLabel('LBL_Digital', $langId)
        );
    }

    public static function getAttributesById($recordId, $attr = null)
    {
        $row = parent::getAttributesById($recordId, $attr);

        /* get Numeric attributes data[ */
        if (!$attr) {
            $num_attr_row = static::getProductNumericAttributes($recordId);
            if (!empty($num_attr_row)) {
                $row = array_merge($row, $num_attr_row);
            }
        }
        /* ] */
        return $row;
    }

    public static function getProductDataById($langId = 0, $productId = 0, $attr = array())
    {
        $srch  = self::getSearchObject($langId);
        $srch->addCondition('product_id', '=', $productId);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }
        $rs = $srch->getResultSet();

        $row = FatApp::getDb()->fetch($rs);
        if ($row == false) {
            return array();
        } else {
            return $row;
        }
    }

    public function deleteProductImage($product_id, $image_id)
    {
        $product_id = FatUtility :: int($product_id);
        $image_id = FatUtility :: int($image_id);
        if ($product_id < 1 || $image_id < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product_id, $image_id)) {
            $this->error = $fileHandlerObj->getError();
            return false;
        }
        return true;
    }

    public function updateProdImagesOrder($product_id, $order)
    {
        $product_id = FatUtility :: int($product_id);
        if (is_array($order) && sizeof($order) > 0) {
            foreach ($order as $i => $id) {
                if (FatUtility::int($id) < 1) {
                    continue;
                }
                FatApp::getDb()->updateFromArray('tbl_attached_files', array('afile_display_order' => $i), array('smt' => 'afile_type = ? AND afile_record_id = ? AND afile_id = ?','vals' => array(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product_id, $id)));
            }
            return true;
        }
        return false;
    }

    public function addUpdateProductCategories($product_id, $categories = array())
    {
        if (!$product_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        FatApp::getDb()->deleteRecords(static::DB_TBL_PRODUCT_TO_CATEGORY, array('smt'=> static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX.'product_id = ?','vals' => array($product_id) ));
        if (empty($categories)) {
            return true;
        }

        $record = new TableRecord(static::DB_TBL_PRODUCT_TO_CATEGORY);
        foreach ($categories as $category_id) {
            $to_save_arr = array();
            $to_save_arr['ptc_product_id'] = $product_id;
            $to_save_arr['ptc_prodcat_id'] = $category_id;
            $record->assignValues($to_save_arr);
            if (!$record->addNew(array(), $to_save_arr)) {
                $this->error = $record->getError();
                return false;
            }
        }
        return true;
    }

    public function addUpdateProductOption($product_id, $option_id)
    {
        $product_id = FatUtility::int($product_id);
        $option_id = FatUtility::int($option_id);
        if (!$product_id || !$option_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        $record = new TableRecord(static::DB_PRODUCT_TO_OPTION);
        $to_save_arr = array();
        $to_save_arr[static::DB_PRODUCT_TO_OPTION_PREFIX.'product_id'] = $product_id;
        $to_save_arr[static::DB_PRODUCT_TO_OPTION_PREFIX.'option_id'] = $option_id;
        $record->assignValues($to_save_arr);
        if (!$record->addNew(array(), $to_save_arr)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function removeProductOption($product_id, $option_id)
    {
        $db = FatApp::getDb();
        $product_id = FatUtility::int($product_id);
        $option_id = FatUtility::int($option_id);
        if (!$product_id || !$option_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        if (!$db->deleteRecords(static::DB_PRODUCT_TO_OPTION, array('smt'=> static::DB_PRODUCT_TO_OPTION_PREFIX.'product_id = ? AND '.static::DB_PRODUCT_TO_OPTION_PREFIX . 'option_id = ?','vals' => array($product_id, $option_id)))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function addUpdateProductTag($product_id, $tag_id)
    {
        $product_id = FatUtility::int($product_id);
        $tag_id = FatUtility::int($tag_id);
        if (!$product_id || !$tag_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        $record = new TableRecord(static::DB_PRODUCT_TO_TAG);
        $to_save_arr = array();
        $to_save_arr[static::DB_PRODUCT_TO_TAG_PREFIX.'product_id'] = $product_id;
        $to_save_arr[static::DB_PRODUCT_TO_TAG_PREFIX.'tag_id'] = $tag_id;
        $record->assignValues($to_save_arr);
        if (!$record->addNew(array(), $to_save_arr)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function addUpdateProductTags($product_id, $tags = array())
    {
        if (!$product_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        FatApp::getDb()->deleteRecords(static::DB_PRODUCT_TO_TAG, array('smt'=> static::DB_PRODUCT_TO_TAG_PREFIX.'product_id = ?','vals' => array($product_id)));
        if (empty($tags)) {
            return true;
        }

        $record = new TableRecord(static::DB_PRODUCT_TO_TAG);
        foreach ($tags as $tag_id) {
            $to_save_arr = array();
            $to_save_arr['ptt_product_id'] = $product_id;
            $to_save_arr['ptt_tag_id'] = $tag_id;
            $record->assignValues($to_save_arr);
            if (!$record->addNew(array(), $to_save_arr)) {
                $this->error = $record->getError();
                return false;
            }
        }
        return true;
    }

    public function removeProductTag($product_id, $tag_id)
    {
        $db = FatApp::getDb();
        $product_id = FatUtility::int($product_id);
        $tag_id = FatUtility::int($tag_id);
        if (!$product_id || !$tag_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        if (!$db->deleteRecords(static::DB_PRODUCT_TO_TAG, array('smt'=> static::DB_PRODUCT_TO_TAG_PREFIX.'product_id = ? AND '.static::DB_PRODUCT_TO_TAG_PREFIX . 'tag_id = ?','vals' => array($product_id, $tag_id)))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public static function getProductShippingRates($product_id, $lang_id, $city_id = 0, $sellerId = 0, $limit = 0, $page = 'product')
    {
        $product_id = FatUtility::convertToType($product_id, FatUtility::VAR_INT);
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        if (!$product_id || !$lang_id) {
            //trigger_error(Labels::getLabel("ERR_Arguments_not_specified.",$this->commonLangId), E_USER_ERROR);
            return false;
        }
        $srch = new SearchBase(static::DB_PRODUCT_TO_SHIP, 'tpsr');
        
        //$srch->joinTable(Countries::DB_TBL_LANG, 'LEFT JOIN', 'tpsr.'.static::DB_PRODUCT_TO_SHIP_PREFIX.'country=tc.'.Countries::DB_TBL_LANG_PREFIX.'country_id and tc.'.Countries::DB_TBL_LANG_PREFIX.'lang_id='.$lang_id, 'tc');

        $srch->joinTable('tbl_cities_lang', 'LEFT JOIN', 'tpsr.'.static::DB_PRODUCT_TO_SHIP_PREFIX.'city=ct.citylang_city_id and ct.citylang_lang_id='.$lang_id, 'ct');

        $srch->joinTable(ShippingCompanies::DB_TBL, 'LEFT JOIN', 'tpsr.pship_company=sc.scompany_id ', 'sc');
        $srch->joinTable(ShippingCompanies::DB_LANG_TBL, 'LEFT JOIN', 'tpsr.pship_company=tsc.scompanylang_scompany_id and tsc.'.ShippingCompanies::DB_LANG_TBL_PREFIX.'lang_id='.$lang_id, 'tsc');
        
        $srch->joinTable(ShippingDurations::DB_TBL_LANG, 'LEFT JOIN', 'tpsr.pship_duration=tsd.sdurationlang_sduration_id  and tsd.'.ShippingDurations::DB_TBL_PREFIX_LANG.'lang_id='.$lang_id, 'tsd');
        $srch->joinTable(ShippingDurations::DB_TBL, 'LEFT JOIN', 'tpsr.pship_duration=ts.sduration_id and sduration_deleted =0 ', 'ts');
        $srch->addCondition('tpsr.'.static::DB_PRODUCT_TO_SHIP_PREFIX.'prod_id', '=', intval($product_id));
        if ($city_id > 0){
            $srch->addDirectCondition('( tpsr.'.static::DB_PRODUCT_TO_SHIP_PREFIX.'city ='. intval($city_id) .' )');
        }
        $srch ->addCondition('tpsr.'.static::DB_PRODUCT_TO_SHIP_PREFIX.'user_id', '=', $sellerId);

        $srch->addOrder('pship_id');
        $srch->addMultipleFields(
            array(
                static::DB_PRODUCT_TO_SHIP_PREFIX.'id',
                static::DB_PRODUCT_TO_SHIP_PREFIX.'city',
                static::DB_PRODUCT_TO_SHIP_PREFIX.'user_id',
                static::DB_PRODUCT_TO_SHIP_PREFIX.'company',
                static::DB_PRODUCT_TO_SHIP_PREFIX.'duration',
                static::DB_PRODUCT_TO_SHIP_PREFIX.'charges',
                static::DB_PRODUCT_TO_SHIP_PREFIX.'additional_charges',
                'IFNULL(city_name','\''.Labels::getLabel('LBL_Everywhere_Else', $lang_id).'\') as city_name',
                'ifNull('.ShippingCompanies::DB_TBL_PREFIX.'name',ShippingCompanies::DB_TBL_PREFIX.'identifier) as '.ShippingCompanies::DB_TBL_PREFIX.'name',
                ShippingCompanies::DB_TBL_PREFIX.'id',
                ShippingCompanies::DB_LANG_TBL_PREFIX.'scompany_id',
                ShippingDurations::DB_TBL_PREFIX.'name',
                ShippingDurations::DB_TBL_PREFIX.'id',
                ShippingDurations::DB_TBL_PREFIX.'from',
                ShippingDurations::DB_TBL_PREFIX.'identifier ',
                ShippingDurations::DB_TBL_PREFIX.'to',
                ShippingDurations::DB_TBL_PREFIX.'days_or_weeks',
            )
        );

        if ($limit > 0) {
            $srch->setPageSize($limit);
        } else {
            $srch->doNotLimitRecords(true);
            $srch->doNotCalculateRecords(true);
        }
        $rs = $srch->getResultSet();
        /* echo $srch->getQuery();die; */
        $row = FatApp::getDb()->fetchAll($rs);
        if(empty($row)){
            $srch = new SearchBase(static::DB_PRODUCT_TO_SHIP, 'tpsr');
            $srch->joinTable(ShippingCompanies::DB_TBL, 'LEFT JOIN', 'tpsr.pship_company=sc.scompany_id ', 'sc');
            $srch->joinTable(ShippingCompanies::DB_LANG_TBL, 'LEFT JOIN', 'tpsr.pship_company=tsc.scompanylang_scompany_id and tsc.'.ShippingCompanies::DB_LANG_TBL_PREFIX.'lang_id='.$lang_id, 'tsc');

            // For get cityname
            $srch->joinTable('tbl_cities_lang', 'LEFT JOIN', 'tpsr.'.static::DB_PRODUCT_TO_SHIP_PREFIX.'city=ct.citylang_city_id and ct.citylang_lang_id='.$lang_id, 'ct');

            // For get duration
            $srch->joinTable(ShippingDurations::DB_TBL_LANG, 'LEFT JOIN', 'tpsr.pship_duration=tsd.sdurationlang_sduration_id  and tsd.'.ShippingDurations::DB_TBL_PREFIX_LANG.'lang_id='.$lang_id, 'tsd');
            $srch->joinTable(ShippingDurations::DB_TBL, 'LEFT JOIN', 'tpsr.pship_duration=ts.sduration_id and sduration_deleted =0 ', 'ts');

            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addCondition('tpsr.'.static::DB_PRODUCT_TO_SHIP_PREFIX.'prod_id', '=', intval($product_id));
            $srch ->addCondition('tpsr.'.static::DB_PRODUCT_TO_SHIP_PREFIX.'user_id', '=', $sellerId);
            $rs = $srch->getResultSet();
            $row = FatApp::getDb()->fetchAll($rs);
        }
       
        if(empty($row) && $page == "checkout"){
            $srch = new SearchBase('tbl_shipping_settings', 'ss');
        
            $srch->joinTable('tbl_cities_lang', 'LEFT JOIN', 'ss.ship_set_city=ct.citylang_city_id and ct.citylang_lang_id='.$lang_id, 'ct');

            $srch->joinTable('tbl_shipping_company', 'LEFT JOIN', 'ss.ship_set_company=sc.scompany_id ', 'sc');
            $srch->joinTable('tbl_shipping_company_lang', 'LEFT JOIN', 'ss.ship_set_company=tsc.scompanylang_scompany_id and tsc.scompanylang_lang_id='.$lang_id, 'tsc');
            
            $srch->joinTable('tbl_shipping_durations_lang', 'LEFT JOIN', 'ss.ship_set_duration=tsd.sdurationlang_sduration_id  and tsd.sdurationlang_lang_id='.$lang_id, 'tsd');
            $srch->joinTable('tbl_shipping_durations', 'LEFT JOIN', 'ss.ship_set_duration=ts.sduration_id and sduration_deleted =0 ', 'ts');

           // $srch->addCondition('tpsr.'.static::DB_PRODUCT_TO_SHIP_PREFIX.'prod_id', '=', intval($product_id));
            if ($city_id > 0) {
                $srch->addDirectCondition('( ss.ship_set_city ='. intval($city_id) .' )');
            }
            $srch ->addCondition('ss.ship_set_user_id', '=', $sellerId);

            $srch->addOrder('city_name');
            $srch->addMultipleFields(
                array(
                    'ship_set_id as pship_id',
                    'ship_set_city',
                    'ship_set_user_id',
                    'ship_set_company',
                    'ship_set_duration',
                    'cost_for_1st_kg as pship_charges',
                    'each_additional_kg as pship_additional_charges',
                    'IFNULL(city_name','\''.Labels::getLabel('LBL_Everywhere_Else', $lang_id).'\') as city_name',
                    'ifNull('.ShippingCompanies::DB_TBL_PREFIX.'name',ShippingCompanies::DB_TBL_PREFIX.'identifier) as '.ShippingCompanies::DB_TBL_PREFIX.'name',
                    ShippingCompanies::DB_TBL_PREFIX.'id',
                    ShippingCompanies::DB_LANG_TBL_PREFIX.'scompany_id',
                    ShippingDurations::DB_TBL_PREFIX.'name',
                    ShippingDurations::DB_TBL_PREFIX.'id',
                    ShippingDurations::DB_TBL_PREFIX.'from',
                    ShippingDurations::DB_TBL_PREFIX.'identifier ',
                    ShippingDurations::DB_TBL_PREFIX.'to',
                    ShippingDurations::DB_TBL_PREFIX.'days_or_weeks',
                )
            );

            if ($limit > 0) {
                $srch->setPageSize($limit);
            } else {
                $srch->doNotLimitRecords(true);
                $srch->doNotCalculateRecords(true);
            }
            $rs = $srch->getResultSet();
            // echo $srch->getQuery();die;
            $row = FatApp::getDb()->fetchAll($rs);
        }

        if($row == false) {
            return array();
        } else {
            return $row;
        }
    }

    public static function getProductFreeShippingAvailabilty($product_id, $lang_id, $country_id = 0, $sellerId = 0)
    {
        $product_id = FatUtility::convertToType($product_id, FatUtility::VAR_INT);
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        $sellerId = FatUtility::convertToType($sellerId, FatUtility::VAR_INT);
        if (!$product_id || !$lang_id || !$sellerId) {
            //trigger_error(Labels::getLabel("ERR_Arguments_not_specified.",$this->commonLangId), E_USER_ERROR);
            return false;
        }
        $srch = new SearchBase(static::DB_TBL_PRODUCT_SHIPPING, 'tps');
        $srch->joinTable(Countries::DB_TBL_LANG, 'LEFT JOIN', 'tps.'.static::DB_TBL_PRODUCT_SHIPPING_PREFIX.'from_country_id=tc.'.Countries::DB_TBL_LANG_PREFIX.'country_id and tc.'.Countries::DB_TBL_LANG_PREFIX.'lang_id='.$lang_id, 'tc');
        $srch->addCondition('tps.'.static::DB_TBL_PRODUCT_SHIPPING_PREFIX.'product_id', '=', intval($product_id));

        $srch ->addCondition('tps.'.static::DB_TBL_PRODUCT_SHIPPING_PREFIX.'user_id', '=', $sellerId);
        $srch->addFld(
            array(
                static::DB_TBL_PRODUCT_SHIPPING_PREFIX.'free'
            )
        );

        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if ($row) {
            return $row[static::DB_TBL_PRODUCT_SHIPPING_PREFIX.'free'];
        }
        return 0;
    }

    public static function getProductShippingDetails($productId, $langId, $userId = 0)
    {
        $productId = FatUtility::convertToType($productId, FatUtility::VAR_INT);
        if (!$productId || !$langId) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", CommonHelper::getLangId()), E_USER_ERROR);
            return false;
        }
        $srch = new SearchBase(static::DB_TBL_PRODUCT_SHIPPING);
        $srch->addCondition(static::DB_TBL_PRODUCT_SHIPPING_PREFIX . 'product_id', '=', $productId);
        $srch->addCondition(static::DB_TBL_PRODUCT_SHIPPING_PREFIX . 'user_id', '=', $userId);

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $row = $db->fetch($rs);
        return $row;
    }

    public static function getProductOptions($product_id, $lang_id, $includeOptionValues = false, $option_is_separate_images = 0)
    {
        $product_id = FatUtility::convertToType($product_id, FatUtility::VAR_INT);
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        if (!$product_id || !$lang_id) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", CommonHelper::getLangId()), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_PRODUCT_TO_OPTION);
        $srch->addCondition(static::DB_PRODUCT_TO_OPTION_PREFIX . 'product_id', '=', $product_id);
        $srch->joinTable(Option::DB_TBL, 'INNER JOIN', Option::DB_TBL_PREFIX.'id = '.static::DB_PRODUCT_TO_OPTION_PREFIX.'option_id');

        $srch->joinTable(Option::DB_TBL.'_lang', 'LEFT JOIN', 'lang.optionlang_option_id = ' . Option::DB_TBL_PREFIX.'id AND optionlang_lang_id = '.$lang_id, 'lang');

        $srch->addMultipleFields(array('option_id','option_name','option_identifier'));

        if ($option_is_separate_images) {
            $srch->addCondition('option_is_separate_images', '=', applicationConstants::YES);
        }

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $data = array();
        while ($row = $db->fetch($rs)) {
            if ($includeOptionValues) {
                $row['optionValues'] = static::getOptionValues($row['option_id'], $lang_id);
            }
            $data[] = $row;
        }
        return $data;
    }

    public static function getProductSpecifications($product_id, $lang_id)
    {
        $product_id = FatUtility::convertToType($product_id, FatUtility::VAR_INT);
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        if (!$product_id || !$lang_id) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", CommonHelper::getLangId()), E_USER_ERROR);
            return false;
        }
        $data = array();
        $languages = Language::getAllNames();

        foreach ($languages as $langId => $langName) {
            $srch = new SearchBase(static::DB_PRODUCT_SPECIFICATION);
            $srch->addCondition(static::DB_PRODUCT_SPECIFICATION_PREFIX . 'product_id', '=', $product_id);
            $srch->joinTable(static::DB_PRODUCT_LANG_SPECIFICATION, 'LEFT JOIN', static::DB_PRODUCT_SPECIFICATION_PREFIX.'id = '.static::DB_PRODUCT_LANG_SPECIFICATION_PREFIX.'prodspec_id and '.static::DB_PRODUCT_LANG_SPECIFICATION_PREFIX.'lang_id ='.$langId);
            $srch->addMultipleFields(
                array(
                static::DB_PRODUCT_SPECIFICATION_PREFIX.'id',
                static::DB_PRODUCT_SPECIFICATION_PREFIX.'name',
                static::DB_PRODUCT_SPECIFICATION_PREFIX.'value'
                )
            );
            $rs = $srch->getResultSet();
            $row = FatApp::getDb()->fetchAll($rs);
            foreach ($row as $resRow) {
                $data[$resRow[static::DB_PRODUCT_SPECIFICATION_PREFIX.'id']][$langId]=$resRow ;
            }
        }

        return $data;
    }

    public static function getProductTags($product_id, $lang_id = 0)
    {
        $product_id = FatUtility::convertToType($product_id, FatUtility::VAR_INT);
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        if (!$product_id) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $lang_id), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_PRODUCT_TO_TAG);
        $srch->addCondition(static::DB_PRODUCT_TO_TAG_PREFIX . 'product_id', '=', $product_id);
        $srch->joinTable(Tag::DB_TBL, 'INNER JOIN', Tag::DB_TBL_PREFIX.'id = '.static::DB_PRODUCT_TO_TAG_PREFIX.'tag_id');
        $srch->addMultipleFields(array('tag_id', 'tag_identifier'));

        if ($lang_id) {
            $srch->joinTable(Tag::DB_TBL.'_lang', 'LEFT JOIN', 'lang.taglang_tag_id = ' . Tag::DB_TBL_PREFIX.'id AND taglang_lang_id = '.$lang_id, 'lang');
            $srch->addFld('tag_name');
        }

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $data = array();
        while ($row = $db->fetch($rs)) {
            $data[] = $row;
        }
        return $data;
    }

    public static function getProductIdsByTagId($tagId)
    {
        $tagId = FatUtility::int($tagId);
        if (!$tagId) {
            return array();
        }

        $srch = new SearchBase(static::DB_PRODUCT_TO_TAG);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition(static::DB_PRODUCT_TO_TAG_PREFIX.'tag_id', '=', $tagId);
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    public static function getOptionValues($option_id, $lang_id)
    {
        $option_id = FatUtility::int($option_id);
        $lang_id = FatUtility::int($lang_id);
        if (!$option_id || !$lang_id) {
            trigger_error(Labels::getLabel('ERR_Invalid_Arguments!', $lang_id), E_USER_ERROR);
        }
        $srch = new SearchBase(OptionValue::DB_TBL);
        $srch->joinTable(OptionValue::DB_TBL.'_lang', 'LEFT JOIN', 'lang.optionvaluelang_optionvalue_id = ' . OptionValue::DB_TBL_PREFIX.'id AND optionvaluelang_lang_id = '.$lang_id, 'lang');
        $srch->addCondition(OptionValue::DB_TBL_PREFIX.'option_id', '=', $option_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('optionvalue_display_order');
        $srch->addOrder('optionvalue_option_id');
        $srch->addMultipleFields(array('optionvalue_id', 'optionvalue_name' ));
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        return $db->fetchAllAssoc($rs);
    }

    public function getProductCategories($product_id)
    {
        $srch = new SearchBase(static::DB_TBL_PRODUCT_TO_CATEGORY, 'ptc');
        $srch->addCondition(static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'product_id', '=', $product_id);
        $srch->joinTable(ProductCategory::DB_TBL, 'INNER JOIN', ProductCategory::DB_TBL_PREFIX.'id = ptc.'.static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX.'prodcat_id', 'cat');
        $srch->addMultipleFields(array('prodcat_id'));
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'prodcat_id');
        if (!$records) {
            return false;
        }
        return $records;
    }

    public function addUpdateNumericAttributes($data)
    {
        $record = new TableRecord(self::DB_NUMERIC_ATTRIBUTES_TBL);
        $record->assignValues($data);
        if (!$record->addNew(array(), $data)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function addUpdateTextualAttributes($data)
    {
        $record = new TableRecord(self::DB_TEXT_ATTRIBUTES_TBL);
        $record->assignValues($data);
        if (!$record->addNew(array(), $data)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public static function getProductNumericAttributes($product_id)
    {
        if (!$product_id) {
            trigger_error(Labels::getLabel('ERR_Invalid_Arguments!', CommonHelper::getLangId()), E_USER_ERROR);
        }
        $record = new TableRecord(static::DB_NUMERIC_ATTRIBUTES_TBL);
        $record->loadFromDb(array('smt' => static::DB_NUMERIC_ATTRIBUTES_PREFIX . 'product_id = ?', 'vals' => array($product_id)));
        return $record->getFlds();
    }

    public static function getProductTextualAttributes($langId, $product_id)
    {
        $product_id = FatUtility::int($product_id);
        $langId = FatUtility::int($langId);
        if (!$product_id || !$langId) {
            trigger_error(Labels::getLabel('ERR_Invalid_Arguments!', $langId), E_USER_ERROR);
        }
        $record = new TableRecord(static::DB_TEXT_ATTRIBUTES_TBL);
        $record->loadFromDb(array('smt' => static::DB_TEXT_ATTRIBUTES_PREFIX . 'product_id = ? AND ' . static::DB_TEXT_ATTRIBUTES_PREFIX . 'lang_id = ?', 'vals' => array($product_id, $langId)));
        return $record->getFlds();
    }

    public static function generateProductOptionsUrl($selprod_id, $selectedOptions, $option_id, $optionvalue_id, $product_id, $returnId = false)
    {
        $selectedOptions[$option_id] = $optionvalue_id;
        sort($selectedOptions);

        $selprod_code = $product_id.'_'.implode('_', $selectedOptions);

        $prodSrchObj = new ProductSearch();
        $prodSrchObj->setDefinedCriteria();
        $prodSrchObj->joinProductToCategory();
        $prodSrchObj->doNotCalculateRecords();
        $prodSrchObj->addCondition('selprod_id', '!=', $selprod_id);
        $prodSrchObj->addMultipleFields(array('product_id','selprod_id','theprice'));
        $prodSrchObj->addCondition('product_id', '=', $product_id);

        $prodSrch = clone $prodSrchObj;

        $prodSrch->addCondition('selprod_code', '=', $selprod_code);
        $prodSrch->doNotLimitRecords();
        $prodSrch->addOrder('theprice', 'ASC');
        $productRs = $prodSrch->getResultSet();
        //echo $prodSrch->getQuery();
        $product = FatApp::getDb()->fetch($productRs);
        if ($product) {
            if ($returnId) {
                return $product['selprod_id'];
            }
            return CommonHelper::generateUrl('Products', 'view', array($product['selprod_id']));
        } else {
            $prodSrch2 =  new ProductSearch(CommonHelper::getLangId());
            $prodSrch2->doNotCalculateRecords();
            $prodSrch2->setDefinedCriteria();
            $prodSrch2->addCondition('selprod_id', '!=', $selprod_id);
            $prodSrch2->addCondition('product_id', '=', $product_id);
            $prodSrch2->addCondition('selprod_code', 'LIKE', '%_'.$optionvalue_id.'%');
            $prodSrch2->addMultipleFields(array('selprod_id', 'special_price_found', 'theprice'));
            $prodSrch2->setPageSize(1);
            $prodSrch2->addOrder('theprice', 'ASC');
            $productRs = $prodSrch2->getResultSet();
            $product = FatApp::getDb()->fetch($productRs);

            if ($product) {
                if ($returnId) {
                    return $product['selprod_id'];
                }
                return CommonHelper::generateUrl('Products', 'view', array($product['selprod_id']))."::";
            } else {
                return false;
            }
            return false;
        }
    }

    public static function uniqueProductAction($selprodCode, $weightageKey)
    {
        /* $ipAddress = $_SERVER['REMOTE_ADDR'];
        list($product_id) = explode('_',$selprodCode);
        $product_id = FatUtility::int($product_id);

        $srch = new SearchBase('tbl_smart_log_actions');

        $date = date('Y-m-d H:i:s');
        $currentDate = strtotime($date);
        $futureDate = $currentDate - (60*5);
        $formatDate = date("Y-m-d H:i:s", $futureDate);

        $srch->addDirectCondition("slog_ip = '".$ipAddress."' and '".$formatDate."' < slog_datetime and      slog_swsetting_key = '".$weightageKey."' and slog_record_code = '".$selprodCode."' and slog_record_id = '".$product_id."' and slog_type = '".SmartUserActivityBrowsing::TYPE_PRODUCT."'");
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('slog_ip'));
        $rs = $srch->getResultSet();
        $row =  FatApp::getDb()->fetch($rs);
        return ($row == false)?true:false; */
    }

    public static function recordProductWeightage($selprodCode, $weightageKey, $eventWeightage = 0)
    {
        list($productId) = explode('_', $selprodCode);
        $productId = FatUtility::int($productId);

        if (1 > $productId) {
            return false;
        }

        if ($eventWeightage == 0) {
            $weightageArr = SmartWeightageSettings::getWeightageAssoc();
            $eventWeightage = !empty($weightageArr[$weightageKey]) ? $weightageArr[$weightageKey] : 0;
        }

        if (!UserAuthentication::isUserLogged()) {
            $userId = CommonHelper::getUserIdFromCookies();
        } else {
            $userId = UserAuthentication::getLoggedUserId();
        }

        $record = new TableRecord('tbl_recommendation_activity_browsing');

        $assignFields = array();
        $assignFields['rab_session_id'] = session_id();
        $assignFields['rab_user_id'] = $userId;
        $assignFields['rab_record_id'] = $productId;
        $assignFields['rab_record_type'] = SmartUserActivityBrowsing::TYPE_PRODUCT;
        $assignFields['rab_weightage_key'] = $weightageKey;
        $assignFields['rab_weightage'] = $eventWeightage;
        $assignFields['rab_last_action_datetime'] = date('Y-m-d H:i:s');

        $onDuplicateKeyUpdate = array_merge($assignFields, array('rab_weightage'=>'mysql_func_rab_weightage + '.$eventWeightage));

        FatApp::getDb()->insertFromArray('tbl_recommendation_activity_browsing', $assignFields, true, array(), $onDuplicateKeyUpdate);
    }

    public static function addUpdateProductBrowsingHistory($selprodCode, $weightageKey, $weightageVal = 1)
    {
        /* list($productId) = explode('_',$selprodCode);
        $productId = FatUtility::int($productId);
        $weightageVal = FatUtility::int($weightageVal);

        $weightageKey = FatUtility::int($weightageKey);
        $weightageKey = 1 ;

        if(1 > $weightageKey || 1 > $weightageVal) { return false;}

        if(!static::uniqueProductAction($selprodCode,$weightageKey)){ return false ;}

        if (!UserAuthentication::isUserLogged()) {
        $userId = CommonHelper::getUserIdFromCookies();
        }else{
        $userId = UserAuthentication::getLoggedUserId();
        }

        $record = new TableRecord('tbl_products_browsing_history');

        $assignFields = array();
        $assignFields['pbhistory_sessionid'] = session_id();
        $assignFields['pbhistory_selprod_code'] = $selprodCode;
        $assignFields['pbhistory_swsetting_key'] = $weightageKey;
        $assignFields['pbhistory_user_id'] = $userId;
        $assignFields['pbhistory_product_id'] = $productId;
        $assignFields['pbhistory_count'] = $weightageVal;
        $assignFields['pbhistory_datetime'] = date('Y-m-d H:i:s');

        $onDuplicateKeyUpdate = array_merge($assignFields,array('pbhistory_count'=>'mysql_func_pbhistory_count + '.$weightageVal));

        FatApp::getDb()->insertFromArray('tbl_products_browsing_history',$assignFields,true,array(),$onDuplicateKeyUpdate);  */
    }

    public static function tempHoldStockCount($selprod_id = 0, $userId = 0, $pshold_prodgroup_id = 0, $useProductGroup = false)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $pshold_prodgroup_id = FatUtility::int($pshold_prodgroup_id);
        $intervalInMinutes = FatApp::getConfig('cart_stock_hold_minutes', FatUtility::VAR_INT, 15);

        $srch = new SearchBase('tbl_product_stock_hold');
        $srch->doNotCalculateRecords();
        $srch->addOrder('pshold_id', 'ASC');
        $srch->addCondition('pshold_added_on', '>=', 'mysql_func_DATE_SUB( NOW(), INTERVAL ' . $intervalInMinutes . ' MINUTE )', 'AND', true);
        $srch->addCondition('pshold_selprod_id', '=', $selprod_id);

        if ($useProductGroup == true) {
            $srch->addCondition('pshold_prodgroup_id', '=', $pshold_prodgroup_id);
        }

        if ($userId > 0) {
            $srch->addCondition('pshold_user_id', '=', $userId);
        }
        $srch->addMultipleFields(array('sum(pshold_selprod_stock) as stockHold'));
        $srch->setPageNumber(1);
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $stockHoldRow = FatApp::getDb()->fetch($rs);
        if ($stockHoldRow == false) {
            return 0;
        }
        return $stockHoldRow['stockHold'];
    }

    public function addUpdateUserFavoriteProduct($user_id, $product_id)
    {
        $user_id = FatUtility::int($user_id);
        $product_id = FatUtility::int($product_id);

        $data_to_save = array( 'ufp_user_id' => $user_id, 'ufp_selprod_id' => $product_id );
        $data_to_save_on_duplicate = array( 'ufp_selprod_id' => $product_id );
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL_PRODUCT_FAVORITE, $data_to_save, false, array(), $data_to_save_on_duplicate)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public static function getUserFavouriteProducts($user_id, $langId)
    {
        $user_id = FatUtility::int($user_id);
        $srch = new UserFavoriteProductSearch();
        $srch->setDefinedCriteria($langId);
        $srch->joinBrands();
        $srch->joinSellers();
        $srch->joinShops();
        $srch->joinProductToCategory();
        $srch->joinSellerSubscription($langId, true);
        $srch->addSubscriptionValidCondition();
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addCondition('ufp_user_id', '=', $user_id);
        $srch->addMultipleFields(array( 'selprod_id', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'product_id', 'IFNULL(product_name, product_identifier) as product_name', 'IF(selprod_stock > 0, 1, 0) AS in_stock'));
        $srch->setPageNumber(1);
        $srch->setPageSize(4);
        $srch->addGroupBy('selprod_id');

        /* die($srch->getQuery());  */
        $rs = $srch->getResultSet();
        $result['uwlist_id'] = 0;
        $result['uwlist_title'] = Labels::getLabel('LBL_Products_That_I_Love', $langId);
        $result['uwlist_type'] = UserWishList::TYPE_FAVOURITE;

        $result['totalProducts'] = $srch->recordCount();
        $result['products'] = FatApp::getDb()->fetchAll($rs);
        return $result;
    }

    public static function getProductMetaData($selProductId = 0)
    {
        if ($selProductId <= 0) {
            return false;
        }
        $srch = MetaTag::getSearchObject();
        $srch->addCondition(MetaTag::DB_TBL_PREFIX.'record_id', '=', $selProductId);
        $srch->addCondition(MetaTag::DB_TBL_PREFIX.'controller', '=', 'Products');
        $srch->addCondition(MetaTag::DB_TBL_PREFIX.'action', '=', 'view');
        $srch->addMultipleFields(array('meta_id','meta_identifier'));
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetch($rs);
        return $records;
    }

    public static function isProductShippedBySeller($productId, $productAddedBySellerId, $selProdSellerId)
    {
        $productId = FatUtility::int($productId);
        $productAddedBySellerId = FatUtility::int($productAddedBySellerId);
        $selProdSellerId = FatUtility::int($selProdSellerId);
        if ($productAddedBySellerId && $productAddedBySellerId == $selProdSellerId) {
            return true;
        }
        $srch = new SearchBase(static::DB_PRODUCT_SHIPPED_BY_SELLER, 'psbs');
        $srch->addCondition('psbs_product_id', '=', $productId);
        $srch->addCondition('psbs_user_id', '=', $selProdSellerId);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row['psbs_user_id'] == $selProdSellerId) {
            return true;
        }
        return false;
    }

    public function getTotalProductsAddedByUser($user_id)
    {
        $srch = SellerProduct::getSearchObject(CommonHelper::getLangId());
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_LANG_TBL, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = '.CommonHelper::getLangId(), 'p_l');
        $srch->addOrder('product_name');
        $srch->addCondition('selprod_user_id', '=', $user_id);
        $srch->addCondition('selprod_deleted', '=', 0);
        $srch->addMultipleFields(
            array(
            'count(selprod_id) as totProducts')
        );
        $srch->addOrder('selprod_active', 'DESC');

        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $produtcCountList = $db->fetch($rs);
        $totalProduct = $produtcCountList['totProducts'];
        return $totalProduct;
    }

    public static function getProductShippingTitle($langId, $shippingDetails = array())
    {
        $langId = FatUtility::int($langId);
        if (1 > $langId) {
            return;
        }
        if (empty($shippingDetails)) {
            return;
        } else {
            return FatUtility::decodeHtmlEntities('<em><strong>'.$shippingDetails['country_name'].'</em></strong> '.Labels::getLabel('LBL_by', $langId).' <strong>'.$shippingDetails['scompany_name'].'</strong> '.Labels::getLabel('LBL_in', $langId).' '.ShippingDurations::getShippingDurationTitle($shippingDetails, $langId));
        }
    }

    public static function isSellProdAvailableForUser($selProdCode, $langId, $userId = 0, $selprod_id = 0)
    {
        $userId = FatUtility::int($userId);
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        if (1 > $userId) {
            return false;
        }

        $srch = SellerProduct::getSearchObject($langId);
        $srch->addCondition('selprod_code', '=', $selProdCode);
        $srch->addCondition('selprod_user_id', '=', $userId);
        /* $srch->addCondition('selprod_deleted','=',applicationConstants::NO); */
        if ($selprod_id) {
            $srch->addCondition('selprod_id', '!=', $selprod_id);
        }
        $db = FatApp::getDb();

        $srch->addMultipleFields(array('selprod_id','selprod_deleted'));
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);

        if ($row == false) {
            return array();
        }

        return $row;
    }

    public static function availableForAddToStore($productId, $userId)
    {
        $productId = FatUtility::int($productId);
        $userId = FatUtility::int($userId);

        $srch = SellerProduct::getSearchObject();
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT JOIN', 'selprod_id = selprodoption_selprod_id', 'tspo');
        $srch->addCondition('selprod_product_id', '=', $productId);
        $srch->addCondition('selprod_user_id', '=', $userId);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        // $srch->addCondition('tspo.selprodoption_optionvalue_id', 'is', 'mysql_func_null', 'and', true);
        $srch->addFld('selprodoption_optionvalue_id');
        $rs = $srch->getResultSet();
        $alreadyAdded = FatApp::getDb()->fetchAll($rs, 'selprodoption_optionvalue_id');
        if ($alreadyAdded == false) {
            return true;
        }

        $srch = new SearchBase(static::DB_PRODUCT_TO_OPTION);
        $srch->addCondition(static::DB_PRODUCT_TO_OPTION_PREFIX . 'product_id', '=', $productId);
        $srch->joinTable(OptionValue::DB_TBL, 'LEFT JOIN', 'prodoption_option_id = opval.optionvalue_option_id', 'opval');
        $srch->addFld('optionvalue_id');
        $rs = $srch->getResultSet();
        $allOptions = FatApp::getDb()->fetchAll($rs, 'optionvalue_id');
        $result = array_diff_key($allOptions, $alreadyAdded);
        if (empty($result)) {
            return false;
        }
        return true;
    }

    public static function addUpdateProductSellerShipping($product_id, $data_to_be_save, $userId)
    {
        $productSellerShiping = array();
        $productSellerShiping['ps_product_id']= $product_id;
        $productSellerShiping['ps_user_id']= $userId;
        $productSellerShiping['ps_from_country_id']= $data_to_be_save['ps_from_country_id'];
        $productSellerShiping['ps_free']=  $data_to_be_save['ps_free'];
        if (!FatApp::getDb()->insertFromArray(PRODUCT::DB_TBL_PRODUCT_SHIPPING, $productSellerShiping, false, array(), $productSellerShiping)) {
            return false;
        }
        return true;
    }

    public static function addUpdateProductShippingRates($product_id, $data, $userId = 0)
    {
        static::removeProductShippingRates($product_id, $userId);

        if (empty($data) || count($data) == 0) {
            // $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId);
            return false;
        }

        foreach ($data as $key => $val) {
            if (isset($val["country_id"]) && ($val["country_id"] > 0 || $val["country_id"] == -1) && $val["company_id"] > 0 && $val["processing_time_id"] > 0) {
                $prodShipData = array(
                'pship_prod_id'=>$product_id,
                'pship_country'=>(isset($val["country_id"]) && FatUtility::int($val["country_id"]))?FatUtility::int($val["country_id"]):0,
                'pship_user_id'=>$userId,
                'pship_company'=>(isset($val["company_id"]) && FatUtility::int($val["company_id"]))?FatUtility::int($val["company_id"]):0,
                'pship_duration'=>(isset($val["processing_time_id"]) && FatUtility::int($val["processing_time_id"]))?FatUtility::int($val["processing_time_id"]):0,
                'pship_charges'=>(1 > FatUtility::float($val["cost"]) ? 0 : FatUtility::float($val["cost"])),
                'pship_additional_charges'=>FatUtility::float($val["additional_cost"]),
                );

                if (!FatApp::getDb()->insertFromArray(ShippingApi::DB_TBL_PRODUCT_SHIPPING_RATES, $prodShipData, false, array(), $prodShipData)) {
                    // $this->error = FatApp::getDb()->getError();
                    return false;
                }
            }
        }

        return true;
    }

    public static function removeProductShippingRates($product_id, $userId)
    {
        $db = FatApp::getDb();
        $product_id = FatUtility::int($product_id);
        $userId = FatUtility::int($userId);

        if (!$db->deleteRecords(ShippingApi::DB_TBL_PRODUCT_SHIPPING_RATES, array('smt'=> ShippingApi::DB_TBL_PRODUCT_SHIPPING_RATES_PREFIX.'prod_id = ? and '.ShippingApi::DB_TBL_PRODUCT_SHIPPING_RATES_PREFIX.'user_id = ?','vals' => array($product_id,$userId)))) {
            // $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function removeProductCategory($product_id, $option_id)
    {
        $db = FatApp::getDb();
        $product_id = FatUtility::int($product_id);
        $option_id = FatUtility::int($option_id);
        if (!$product_id || !$option_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        if (!$db->deleteRecords(static::DB_TBL_PRODUCT_TO_CATEGORY, array('smt'=> static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX.'product_id = ? AND '.static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'prodcat_id = ?','vals' => array($product_id, $option_id)))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function addUpdateProductCategory($product_id, $option_id)
    {
        $product_id = FatUtility::int($product_id);
        $option_id = FatUtility::int($option_id);
        if (!$product_id || !$option_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        $record = new TableRecord(static::DB_TBL_PRODUCT_TO_CATEGORY);
        $to_save_arr = array();
        $to_save_arr[static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX.'product_id'] = $product_id;
        $to_save_arr[static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX.'prodcat_id'] = $option_id;
        $record->assignValues($to_save_arr);
        if (!$record->addNew(array(), $to_save_arr)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function deleteProduct()
    {
        $productId = FatUtility::int($this->mainTableRecordId);
        if (0 >= $productId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $db = FatApp::getDb();
        if (!$db->updateFromArray(static::DB_TBL, array(static::DB_TBL_PREFIX.'deleted' => applicationConstants::YES), array('smt' => static::DB_TBL_PREFIX.'id = ?','vals' => array($this->mainTableRecordId)))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public static function verifyProductIsValid($selprod_id)
    {
        $prodSrch = new ProductSearch();
        $prodSrch->setDefinedCriteria();
        $prodSrch->joinProductToCategory();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->addMultipleFields(array('selprod_id','product_id'));
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $prodSrch->doNotLimitRecords();
        $productRs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($productRs);

        if ($product == false) {
            return false;
        }
        return true;
    }

    public static function convertArrToSrchFiltersAssocArr($arr)
    {
        return SearchItem::convertArrToSrchFiltersAssocArr($arr);
    }

    public static function getListingObj($criteria, $langId = 0, $userId = 0)
    {
        $srch = new ProductSearch($langId);
        $join_price = 0;
        if (array_key_exists('join_price', $criteria)) {
            $join_price = FatUtility::int($criteria['join_price']);
        }

        $keyword = '';
        if (array_key_exists('keyword', $criteria)) {
            $keyword = $criteria['keyword'];
        }

        if (true ===  MOBILE_APP_API_CALL) {
            $criteria['optionvalue'] = !empty($criteria['optionvalue']) ? json_decode($criteria['optionvalue'], true) : '';
        }

        $shop_id = 0;   
        if (array_key_exists('shop_id', $criteria)) {
            $shop_id =  FatUtility::int($criteria['shop_id']);           
        }

        //$srch->setDefinedCriteria($join_price, 0, $criteria, true);
        $srch->joinForPrice('', $criteria, true);
        $srch->unsetDefaultLangForJoins();
        $srch->joinSellers();
        $srch->joinShops($langId, true, true, $shop_id);
        $srch->joinShopCountry();
        $srch->joinShopState();
        $srch->joinBrands($langId);
        $srch->joinProductToCategory($langId);
        $srch->joinSellerSubscription(0, false, true);
        $srch->addSubscriptionValidCondition();

        /* to check current product is in wish list or not[ */
        if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
            $srch->joinFavouriteProducts($userId);
            $srch->addFld('ufp_id');
        } else {
            $srch->joinUserWishListProducts($userId);
            $srch->addFld('COALESCE(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
        }
        /*substring_index(group_concat(IFNULL(prodcat_name, prodcat_identifier) ORDER BY IFNULL(prodcat_name, prodcat_identifier) ASC SEPARATOR "," ) , ",", 1) as prodcat_name*/
        $srch->addMultipleFields(
            array('prodcat_code','product_id', 'prodcat_id', 'COALESCE(product_name, product_identifier) as product_name', 'product_model',  'product_image_updated_on','COALESCE(prodcat_name, prodcat_identifier) as prodcat_name',
            'selprod_id', 'selprod_user_id',  'selprod_code', 'selprod_stock', 'selprod_condition', 'selprod_price', 'COALESCE(selprod_title  ,COALESCE(product_name, product_identifier)) as selprod_title',
            'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type', 'splprice_start_date', 'splprice_end_date',
            'brand_id', 'COALESCE(brand_name, brand_identifier) as brand_name', 'user_name', 'IF(selprod_stock > 0, 1, 0) AS in_stock',
            'selprod_sold_count','selprod_return_policy',/* 'ifnull(sq_sprating.totReviews,0) totReviews','IF(ufp_id > 0, 1, 0) as isfavorite', */'selprod_min_order_qty'
            )
        );

        $includeRating = false;

        if (true ===  MOBILE_APP_API_CALL) {
            $includeRating = true;
        }

        if (array_key_exists('top_products', $criteria)) {
            $includeRating = true;
            $srch->addHaving('prod_rating', '>=', 3);
        }

        /*if (!empty($keyword)) {
            $includeRating = true;
        }*/

        if (array_key_exists('sortBy', $criteria)) {
            $sortBy = $criteria['sortBy'];
            $sortByArr = explode("_", $sortBy);
            $sortBy = isset($sortByArr[0]) ? $sortByArr[0] : $sortBy;
            if ($sortBy == 'rating') {
                $includeRating = true;
            }
        }

        if (true === $includeRating) {
            $selProdReviewObj = new SelProdReviewSearch();
            $selProdReviewObj->joinSelProdRating();
            $selProdReviewObj->addCondition('sprating_rating_type', '=', SelProdRating::TYPE_PRODUCT);
            $selProdReviewObj->doNotCalculateRecords();
            $selProdReviewObj->doNotLimitRecords();
            $selProdReviewObj->addGroupBy('spr.spreview_product_id');
            $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
            $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id','spr.spreview_product_id',"ROUND(AVG(sprating_rating),2) as prod_rating"));
            $selProdRviewSubQuery = $selProdReviewObj->getQuery();
            /*$srch->joinTable('(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_selprod_id = selprod_id', 'sq_sprating');*/
            $srch->joinTable('(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_product_id = product_id', 'sq_sprating');
            $srch->addFld('coalesce(prod_rating,0) prod_rating');
        }


        if (array_key_exists('category', $criteria)) {
            $srch->addCategoryCondition($criteria['category']);
        }

        if (array_key_exists('prodcat', $criteria)) {
            if (true ===  MOBILE_APP_API_CALL) {
                $criteria['prodcat'] = json_decode($criteria['prodcat'], true);
            }
            $srch->addCategoryCondition($criteria['prodcat']);
        }

        if (0 < $shop_id) {
            $srch->addShopIdCondition($shop_id);
        }
        

        if (array_key_exists('collection_id', $criteria)) {
            $collection_id =  FatUtility::int($criteria['collection_id']);
            if (0 < $collection_id) {
                $srch->addCollectionIdCondition($collection_id);
            }
        }

        if (!empty($keyword)) {
            $srch->addKeywordSearch($keyword);
            $srch->addFld('if(selprod_title LIKE '.FatApp::getDb()->quoteVariable('%'.$keyword.'%').',  1,   0  ) as keywordmatched');
            $srch->addFld('if(selprod_title LIKE '.FatApp::getDb()->quoteVariable('%'.$keyword.'%').',  IFNULL(splprice_price, selprod_price),   theprice ) as theprice');
            $srch->addFld(
                'if(selprod_title LIKE '.FatApp::getDb()->quoteVariable('%'.$keyword.'%').',  CASE WHEN splprice_selprod_id IS NULL THEN 0 ELSE 1
END,   special_price_found ) as special_price_found'
            );
            $sortBy = 'keyword_relevancy';
        } else {
            $srch->addFld('theprice');
            $srch->addFld('special_price_found');
            $sortBy = 'popularity';
        }

        if (array_key_exists('brand', $criteria)) {
            if (!empty($criteria['brand'])) {
                if (true ===  MOBILE_APP_API_CALL) {
                    $criteria['brand'] = json_decode($criteria['brand'], true);
                }
                $srch->addBrandCondition($criteria['brand']);
            }
        }

        if (array_key_exists('optionvalue', $criteria)) {
            if (!empty($criteria['optionvalue'])) {
                $srch->addOptionCondition($criteria['optionvalue']);
            }
        }

        if (array_key_exists('condition', $criteria)) {
            if (true ===  MOBILE_APP_API_CALL) {
                $criteria['condition'] = json_decode($criteria['condition'], true);
            }
            $condition = is_array($criteria['condition']) ? array_filter($criteria['condition']) : $criteria['condition'];
            $srch->addConditionCondition($condition);
        }

        if (array_key_exists('out_of_stock', $criteria)) {
            if (!empty($criteria['out_of_stock']) && $criteria['out_of_stock'] == 1) {
                $srch->excludeOutOfStockProducts();
            }
        }

        $minPriceRange = '';
        if (array_key_exists('price-min-range', $criteria)) {
            $minPriceRange = floor($criteria['price-min-range']);
        } elseif (array_key_exists('min_price_range', $criteria)) {
            $minPriceRange = floor($criteria['min_price_range']);
        }

        if (!empty($minPriceRange)) {
            $min_price_range_default_currency =  CommonHelper::getDefaultCurrencyValue($minPriceRange, false, false);
            $srch->addHaving('theprice', '>=', $min_price_range_default_currency);
        }

        $maxPriceRange = '';
        if (array_key_exists('price-max-range', $criteria)) {
            $maxPriceRange = ceil($criteria['price-max-range']);
        } elseif (array_key_exists('max_price_range', $criteria)) {
            $maxPriceRange = ceil($criteria['max_price_range']);
        }

        if (!empty($maxPriceRange)) {
            $max_price_range_default_currency =  CommonHelper::getDefaultCurrencyValue($maxPriceRange, false, false);
            $srch->addHaving('theprice', '<=', $max_price_range_default_currency);
        }

        if (array_key_exists('featured', $criteria)) {
            $featured = FatUtility::int($criteria['featured']);
            if (0 < $featured) {
                $srch->addCondition('product_featured', '=', $featured);
            }
        }

        //var_dump($criteria); exit;
        $srch->addOrder('in_stock', 'DESC');

        if (array_key_exists('sortBy', $criteria)) {
            $sortBy = $criteria['sortBy'];
        }

        $sortOrder = 'asc';
        if (array_key_exists('sortOrder', $criteria)) {
            $sortOrder = $criteria['sortOrder'];
        }

        if (!empty($sortBy)) {
            $sortByArr = explode("_", $sortBy);
            $sortBy = isset($sortByArr[0]) ? $sortByArr[0] : $sortBy;
            $sortOrder = isset($sortByArr[1]) ? $sortByArr[1] : $sortOrder;

            if (!in_array($sortOrder, array('asc','desc'))) {
                $sortOrder = 'asc';
            }

            if (!in_array($sortBy, array('keyword','price','popularity','rating', 'discounted'))) {
                $sortOrder = 'keyword_relevancy';
            }

            switch ($sortBy) {
                case 'keyword':
                    $srch->addOrder('keyword_relevancy', 'DESC');
                    break;
                case 'price':
                    $srch->addOrder('theprice', $sortOrder);
                    break;
                case 'popularity':
                    $srch->addOrder('selprod_sold_count', $sortOrder);
                    break;
                case 'discounted':
                    $srch->addFld('ROUND(((selprod_price - theprice)*100)/selprod_price) as discountedValue');
                    $srch->addOrder('discountedValue', 'DESC');
                    break;
                case 'rating':
                    $srch->addOrder('prod_rating', $sortOrder);
                    break;
                default:
                    $srch->addOrder('keyword_relevancy', 'DESC');
                    break;
            }
        }

        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addGroupBy('product_id');

        if (!empty($keyword)) {
            $srch->addGroupBy('keywordmatched');
            $srch->addOrder('keywordmatched', 'desc');
        }
        return $srch;
    }
    public static function getActiveCount($sellerId, $prodId = 0)
    {
        if (0 > FatUtility::int($sellerId)) {
            // $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        $prodId = FatUtility::int($prodId);

        $srch = new SearchBase(static::DB_TBL);

        $srch->addCondition(static::DB_TBL_PREFIX . 'seller_id', '=', $sellerId);

        $srch->addMultipleFields(array(static::DB_TBL_PREFIX . 'id'));
        $srch->addCondition(static::DB_TBL_PREFIX . 'active', '=', applicationConstants::YES);
        $srch->addCondition(static::DB_TBL_PREFIX . 'deleted', '=', applicationConstants::NO);
        $srch->addCondition(static::DB_TBL_PREFIX . 'approved', '=', applicationConstants::YES);
        if ($prodId) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'id', '!=', $prodId);
        }
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        return $srch->recordCount();
    }

    public static function isShippedBySeller($selprodUserId = 0, $productSellerId = 0, $shippedBySellerId = false)
    {
        $productSellerId = FatUtility::int($productSellerId);
        $selprodUserId = FatUtility::int($selprodUserId);
        /* if(FatApp::getConfig('CONF_SHIPPED_BY_ADMIN',FatUtility::VAR_INT,0)){
            return false;
        } */

        if ($productSellerId > 0 && $selprodUserId == $productSellerId) {
            /* Catalog-Product Added By Seller so also shipped by seller */
            return $selprodUserId;
        } else {
            $shippedBySellerId = FatUtility::int($shippedBySellerId);
            if ($shippedBySellerId > 0 && $selprodUserId == $shippedBySellerId) {
                return $shippedBySellerId;
            }
        }
        return false;
    }

    public static function updateMinPrices($productId = 0, $shopId = 0, $brandId = 0)
    {
        $criteria = array();
        $shopId = FatUtility::int($shopId);
        $brandId = FatUtility::int($brandId);
        $productId = FatUtility::int($productId);

        if (0 < $shopId) {
            $criteria = array('shop_id'=>$shopId );
        }/* else {
            $shop = Shop::getAttributesByUserId($sellerId);
            if (!empty($shop) && array_key_exists('shop_id', $shop)) {
                $criteria = array('shop_id'=>$shop['shop_id'] );
            }
        }*/

        if (0 < $brandId) {
            $criteria = array('brand_id'=>$brandId );
        }

        $srch = new ProductSearch();
        $srch->setDefinedCriteria(1, 0, $criteria, true, false);
        $srch->joinProductToCategory();
        $srch->joinSellerSubscription(0, false, true);
        $srch->addSubscriptionValidCondition();
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addMultipleFields(array('product_id','selprod_id','theprice','IFNULL(splprice_id, 0) as splprice_id'));
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addGroupBy('product_id');
        if (!empty($shop) && array_key_exists('shop_id', $shop)) {
            $srch->addCondition('shop_id', '=', $shop['shop_id']);
        }

        if (0 < $productId) {
            $srch->addCondition('product_id', '=', $productId);
        }

        $tmpQry = $srch->getQuery();

        $qry = "INSERT INTO ".static::DB_PRODUCT_MIN_PRICE." (pmp_product_id, pmp_selprod_id, pmp_min_price, pmp_splprice_id) SELECT * FROM (".$tmpQry.") AS t ON DUPLICATE KEY UPDATE pmp_selprod_id = t.selprod_id, pmp_min_price = t.theprice, pmp_splprice_id = t.splprice_id";

        FatApp::getDb()->query($qry);
        $query = "DELETE m FROM ".static::DB_PRODUCT_MIN_PRICE." m LEFT OUTER JOIN (".$tmpQry.") ON pmp_product_id = selprod_product_id WHERE m.pmp_product_id IS NULL";
        FatApp::getDb()->query($query);
    }

    public static function validateFieldsCheck($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }
}
