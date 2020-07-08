<?php
/* created this class to access direct functions of getAttributesById and save function for below mentioned DB table. */
class SellerProduct extends MyAppModel
{
    const DB_TBL = 'tbl_seller_products';
    const DB_TBL_PREFIX = 'selprod_';

    const DB_PROD_TBL = 'tbl_products';
    const DB_PROD_TBL_PREFIX = 'product_';

    const DB_LANG_TBL = 'tbl_seller_products_lang';
    const DB_LANG_TBL_PREFIX = 'selprodlang_';

    const DB_TBL_SELLER_PROD_OPTIONS = 'tbl_seller_product_options';
    const DB_TBL_SELLER_PROD_OPTIONS_PREFIX = 'selprodoption_';

    const DB_TBL_SELLER_PROD_SPCL_PRICE = 'tbl_product_special_prices';
    const DB_TBL_SELLER_PROD_POLICY = 'tbl_seller_product_policies';

    const DB_TBL_UPSELL_PRODUCTS = 'tbl_upsell_products';
    const DB_TBL_UPSELL_PRODUCTS_PREFIX = 'upsell_';
    const DB_TBL_RELATED_PRODUCTS = 'tbl_related_products';
    const DB_TBL_RELATED_PRODUCTS_PREFIX = 'related_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'sp');

        if ($langId) {
            $srch->joinTable(
                static::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'sp_l.'.static::DB_LANG_TBL_PREFIX.'selprod_id = sp.'.static::tblFld('id').' and
			sp_l.'.static::DB_LANG_TBL_PREFIX.'lang_id = '.$langId,
                'sp_l'
            );
        }
        return $srch;
    }

    public static function requiredGenDataFields()
    {
        $arr = array(
            ImportexportCommon::VALIDATE_INT => array(
                'selprod_max_download_times',
                'selprod_download_validity_in_days'
            ),
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
                'selprod_product_id',
                'selprod_price',
                'selprod_stock',
                'selprod_min_order_qty',
                'selprod_condition'
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'product_identifier',
                'credential_username',
                'selprod_subtract_stock',
                'selprod_track_inventory',
                'selprod_threshold_stock_level',
                'selprod_condition_identifier',
                'selprod_title',
                'selprod_url_keyword',
                'selprod_available_from',
            ),
        );

        if (FatApp::getConfig('CONF_PRODUCT_SKU_MANDATORY', FatUtility::VAR_INT, 1)) {
            $physical = array(
                'selprod_sku'
                );
            $arr[ImportexportCommon::VALIDATE_NOT_NULL] = array_merge($arr[ImportexportCommon::VALIDATE_NOT_NULL], $physical);
        }

        return $arr;
    }

    public static function validateGenDataFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredGenDataFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredOptionDataFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprodoption_selprod_id',
                'option_id',
                'optionvalue_id',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'option_identifier',
                'optionvalue_identifier',
            ),
        );
    }

    public static function validateOptionDataFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredOptionDataFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredSEODataFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'meta_identifier',
            ),
        );
    }

    public static function validateSEODataFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredSEODataFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredSplPriceFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'splprice_start_date',
                'splprice_end_date',
                'splprice_price',
            ),
        );
    }

    public static function validateSplPriceFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredSplPriceFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredVolDiscountFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
                'voldiscount_min_qty',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'voldiscount_percentage',
            ),
            ImportexportCommon::VALIDATE_FLOAT => array(
                'voldiscount_percentage',
            ),
        );
    }

    public static function validateVolDiscountFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredVolDiscountFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredBuyTogetherFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
                'upsell_recommend_sellerproduct_id',
            ),
        );
    }

    public static function validateBuyTogetherFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredBuyTogetherFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredRelatedProdFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
                'related_recommend_sellerproduct_id',
            ),
        );
    }

    public static function validateRelatedProdFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredRelatedProdFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredProdPolicyFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
                'sppolicy_ppoint_id',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'ppoint_identifier',
            ),
        );
    }

    public static function validateProdPolicyFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredProdPolicyFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public function addUpdateSellerUpsellProducts($selprod_id, $upsellProds = array())
    {
        if (!$selprod_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', CommonHelper::getLangId());
            return false;
        }

        FatApp::getDb()->deleteRecords(static::DB_TBL_UPSELL_PRODUCTS, array('smt'=> static::DB_TBL_UPSELL_PRODUCTS_PREFIX.'sellerproduct_id = ?','vals' => array($selprod_id) ));
        if (empty($upsellProds)) {
            return true;
        }

        $record = new TableRecord(static::DB_TBL_UPSELL_PRODUCTS);
        foreach ($upsellProds as $upsell_id) {
            $to_save_arr = array();
            $to_save_arr[static::DB_TBL_UPSELL_PRODUCTS_PREFIX.'sellerproduct_id'] = $selprod_id;
            $to_save_arr[static::DB_TBL_UPSELL_PRODUCTS_PREFIX.'recommend_sellerproduct_id'] = $upsell_id;
            $record->assignValues($to_save_arr);
            if (!$record->addNew(array(), $to_save_arr)) {
                $this->error = $record->getError();
                return false;
            }
        }
        return true;
    }

    public function addUpdateSellerRelatedProdcts($selprod_id, $relatedProds = array())
    {
        if (!$selprod_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', CommonHelper::getLangId());
            return false;
        }

        FatApp::getDb()->deleteRecords(static::DB_TBL_RELATED_PRODUCTS, array('smt'=> static::DB_TBL_RELATED_PRODUCTS_PREFIX.'sellerproduct_id = ?','vals' => array($selprod_id) ));
        if (empty($relatedProds)) {
            return true;
        }

        $record = new TableRecord(static::DB_TBL_RELATED_PRODUCTS);
        foreach ($relatedProds as $relprod_id) {
            $to_save_arr = array();
            $to_save_arr[static::DB_TBL_RELATED_PRODUCTS_PREFIX.'sellerproduct_id'] = $selprod_id;
            $to_save_arr[static::DB_TBL_RELATED_PRODUCTS_PREFIX.'recommend_sellerproduct_id'] = $relprod_id;
            $record->assignValues($to_save_arr);
            if (!$record->addNew(array(), $to_save_arr)) {
                $this->error = $record->getError();
                return false;
            }
        }
        return true;
    }

    public function getUpsellProducts($sellProdId, $lang_id, $userId = 0)
    {
        $sellProdId = FatUtility::convertToType($sellProdId, FatUtility::VAR_INT);
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        if (!$sellProdId) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", CommonHelper::getLangId()), E_USER_ERROR);
            return false;
        }
        $splPriceForDate = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');

        $srch = new SearchBase(static::DB_TBL_UPSELL_PRODUCTS);

        $srch->addCondition(static::DB_TBL_UPSELL_PRODUCTS_PREFIX . 'sellerproduct_id', '=', $sellProdId);
        $srch->joinTable(static::DB_TBL, 'INNER JOIN', static::DB_TBL_PREFIX.'id = '.static::DB_TBL_UPSELL_PRODUCTS_PREFIX.'recommend_sellerproduct_id');
        $srch->joinTable(static::DB_TBL.'_lang', 'LEFT JOIN', 'slang.'.static::DB_LANG_TBL_PREFIX.'selprod_id = '.static::DB_TBL_UPSELL_PRODUCTS_PREFIX . 'recommend_sellerproduct_id AND '.static::DB_LANG_TBL_PREFIX.'lang_id = '.$lang_id, 'slang');
        $srch->joinTable(Product::DB_TBL, 'LEFT JOIN', Product::DB_TBL_PREFIX.'id = '.static::DB_TBL_PREFIX.'product_id');
        $srch->joinTable(Product::DB_TBL.'_lang', 'LEFT JOIN', 'lang.productlang_product_id = '.static::DB_LANG_TBL_PREFIX . 'selprod_id AND productlang_lang_id = '.$lang_id, 'lang');
        $srch->joinTable(
            SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE,
            'LEFT OUTER JOIN',
            'm.splprice_selprod_id = selprod_id AND \'' . $splPriceForDate . '\' BETWEEN m.splprice_start_date AND m.splprice_end_date',
            'm'
        );

        $srch->joinTable(
            SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE,
            'LEFT OUTER JOIN',
            's.splprice_selprod_id = selprod_id AND s.splprice_price < m.splprice_price
             AND \'' . $splPriceForDate . '\' BETWEEN s.splprice_start_date AND s.splprice_end_date',
            's'
        );

        $srch->joinTable(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'LEFT OUTER JOIN', 'ptc.ptc_product_id = product_id', 'ptc');
        $srch->joinTable(Brand::DB_TBL, 'LEFT OUTER JOIN', 'product_brand_id = brand.brand_id', 'brand');
        $srch->joinTable(ProductCategory::DB_TBL, 'LEFT OUTER JOIN', 'c.prodcat_id = ptc.ptc_prodcat_id', 'c');

        $srch->addCondition('c.prodcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('c.prodcat_deleted', '=', applicationConstants::NO);
        $srch->addCondition('brand.brand_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('brand.brand_deleted', '=', applicationConstants::NO);
        $srch->addGroupBy('selprod_id');

        $srch->addMultipleFields(array(
            'selprod_id', 'product_id', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title','selprod_price','selprod_stock', 'IFNULL(product_identifier ,product_name) as product_name','product_identifier','selprod_product_id','CASE WHEN m.splprice_selprod_id IS NULL THEN 0 ELSE 1 END AS special_price_found',
        'IFNULL(m.splprice_price, selprod_price) AS theprice', 'selprod_min_order_qty','product_image_updated_on'));

        if (true ===  MOBILE_APP_API_CALL) {
            if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
                $this->joinFavouriteProducts($srch, $userId);
                $srch->addFld('ufp_id');
            } else {
                $this->joinUserWishListProducts($srch, $userId);
                $srch->addFld('IFNULL(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
            }
        }

        $srch->addCondition(Product::DB_TBL_PREFIX . 'active', '=', applicationConstants::YES);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addCondition('product_deleted', '=', applicationConstants::NO);
        $srch->addOrder('selprod_active', 'DESC');
        $srch->addOrder('selprod_id', 'DESC');
        /* echo $srch->getQuery();die; */
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $data = array();
        if ($row = $db->fetchAll($rs)) {
            return $row;
        }
        return $data;
    }

    public static function getAttributesById($recordId, $attr = null, $fetchOptions = true)
    {
        $row = parent::getAttributesById($recordId, $attr);
        /* get seller product options[ */
        if ($fetchOptions) {
            $op = static::getSellerProductOptions($recordId, false);
            if (is_array($op) && count($op)) {
                foreach ($op as $o) {
                    $row['selprodoption_optionvalue_id'][$o['selprodoption_option_id']] = array($o['selprodoption_option_id'] => $o['selprodoption_optionvalue_id']);
                }
            }
        }
        /* ] */

        return $row;
    }

    public function addUpdateSellerProductOptions($selprod_id, $data)
    {
        $selprod_id = FatUtility::int($selprod_id);
        if (!$selprod_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', CommonHelper::getLangId());
            return false;
        }
        $db = FatApp::getDb();
        $db->deleteRecords(static::DB_TBL_SELLER_PROD_OPTIONS, array('smt' => static::DB_TBL_SELLER_PROD_OPTIONS_PREFIX.'selprod_id = ?', 'vals' => array($selprod_id)));
        if (is_array($data) && count($data)) {
            $record = new TableRecord(static::DB_TBL_SELLER_PROD_OPTIONS);
            foreach ($data as $option_id => $optionvalue_id) {
                $data_to_save = array(
                    static::DB_TBL_SELLER_PROD_OPTIONS_PREFIX . 'selprod_id'=>$selprod_id,
                    static::DB_TBL_SELLER_PROD_OPTIONS_PREFIX . 'option_id'=>$option_id,
                    static::DB_TBL_SELLER_PROD_OPTIONS_PREFIX . 'optionvalue_id' => $optionvalue_id
                );
                $record->assignValues($data_to_save);
                if (!$record->addNew()) {
                    $this->error = $record->getError();
                    return false;
                }
            }
        }
        return true;
    }

    public static function getSellerProductOptions($selprod_id, $withAllJoins = true, $lang_id = 0, $option_id = 0)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $lang_id = FatUtility::int($lang_id);
        $option_id = FatUtility::int($option_id);
        if (!$selprod_id) {
            trigger_error(Labels::getLabel('ERR_Invalid_Arguments', CommonHelper::getLangId()), E_USER_ERROR);
        }
        $srch = new SearchBase(static::DB_TBL_SELLER_PROD_OPTIONS, 'spo');

        if ($option_id) {
            $srch->addCondition(static::DB_TBL_SELLER_PROD_OPTIONS_PREFIX . 'option_id', '=', $option_id);
        }

        if ($withAllJoins) {
            if (!$lang_id) {
                trigger_error(Labels::getLabel('ERR_Invalid_Arguments', CommonHelper::getLangId()), E_USER_ERROR);
            }

            $srch->joinTable(OptionValue::DB_TBL, 'INNER JOIN', 'spo.selprodoption_optionvalue_id = ov.optionvalue_id', 'ov');
            $srch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'ov_lang.optionvaluelang_optionvalue_id = ov.optionvalue_id AND ov_lang.optionvaluelang_lang_id = '.$lang_id, 'ov_lang');

            $srch->joinTable(Option::DB_TBL, 'INNER JOIN', 'o.option_id = ov.optionvalue_option_id', 'o');
            $srch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'o.option_id = o_lang.optionlang_option_id AND o_lang.optionlang_lang_id = '.$lang_id, 'o_lang');
            $srch->addMultipleFields(array('o.option_id', 'ov.optionvalue_id', 'IFNULL(option_name, option_identifier) as option_name', 'IFNULL(optionvalue_name, optionvalue_identifier) as optionvalue_name'));
        }

        $srch->addCondition(static::DB_TBL_SELLER_PROD_OPTIONS_PREFIX . 'selprod_id', '=', $selprod_id);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    public static function getSellerProductOptionsBySelProdCode($selprod_code = '', $langId = 0, $displayInFilterOnly = false)
    {
        if ($selprod_code == '') {
            return array();
        }
        $opValArr = explode("_", $selprod_code);

        /* removing product_id from the begining of the array[ */
        $opValArr = array_reverse($opValArr);
        array_pop($opValArr);
        $opValArr = array_reverse($opValArr);
        if (empty($opValArr)) {
            return array();
        }
        /* ] */

        $srch = new SearchBase(OptionValue::DB_TBL, 'ov');
        $srch->joinTable(Option::DB_TBL, 'INNER JOIN', 'o.option_id = ov.optionvalue_option_id', 'o');

        if ($langId) {
            $srch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'ov_lang.optionvaluelang_optionvalue_id = ov.optionvalue_id AND ov_lang.optionvaluelang_lang_id = '.$langId, 'ov_lang');

            $srch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'o.option_id = o_lang.optionlang_option_id AND o_lang.optionlang_lang_id = '.$langId, 'o_lang');
        }

        $srch->addCondition('optionvalue_id', 'IN', $opValArr);
        if ($displayInFilterOnly) {
            $srch->addCondition('option_display_in_filter', '=', applicationConstants::YES);
        }
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs, 'optionvalue_id');
    }

    public static function getSellerProductSpecialPrices($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $srch = new SearchBase(static::DB_TBL_SELLER_PROD_SPCL_PRICE);
        $srch->addCondition('splprice_selprod_id', '=', $selprod_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('splprice_id');
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        return $db->fetchAll($rs);
    }

    public static function getSellerProductSpecialPriceById($splprice_id)
    {
        $splprice_id = FatUtility::int($splprice_id);
        $srch = new SearchBase(static::DB_TBL_SELLER_PROD_SPCL_PRICE, 'prodSp');
        $srch->joinTable(static::DB_TBL, 'INNER JOIN', 'prodSp.splprice_selprod_id = slrPrd.selprod_id', 'slrPrd');
        $srch->addCondition('splprice_id', '=', $splprice_id);
        $srch->addMultipleFields(array('prodSp.*', 'slrPrd.selprod_id', 'slrPrd.selprod_user_id'));
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        return $db->fetch($rs);
    }

    public function deleteSellerProductSpecialPrice($splprice_id, $splprice_selprod_id, $userId = 0)
    {
        $splprice_id = FatUtility::int($splprice_id);
        $splprice_selprod_id = FatUtility::int($splprice_selprod_id);
        if (!$splprice_id || !$splprice_selprod_id) {
            trigger_error(Labels::getLabel('ERR_Invalid_Arguments', CommonHelper::getLangId()), E_USER_ERROR);
        }
        if (0 < $userId) {
            $selProdUserId = SellerProduct::getAttributesById($splprice_selprod_id, 'selprod_user_id', false);
            if ($selProdUserId != $userId) {
                $this->error = Labels::getLabel('ERR_Invalid_Request', CommonHelper::getLangId());
                return false;
            }
        }
        $db = FatApp::getDb();
        $smt = 'splprice_id = ? AND splprice_selprod_id = ? ';
        $smtValues = array($splprice_id, $splprice_selprod_id);
        if (!$db->deleteRecords(static::DB_TBL_SELLER_PROD_SPCL_PRICE, array( 'smt' => $smt, 'vals' => $smtValues))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function addUpdateSellerProductSpecialPrice($data, $return = false)
    {
        $db = FatApp::getDb();
        if (!$db->insertFromArray(static::DB_TBL_SELLER_PROD_SPCL_PRICE, $data, false, array(), $data)) {
            $this->error = $db->getError();
            return false;
        }
        if (true === $return) {
            if (!empty($data['splprice_id'])) {
                return $data['splprice_id'];
            }
            return FatApp::getDb()->getInsertId();
        }
        return true;
    }

    public static function getProductCommission($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        if (!$selprod_id) {
            trigger_error(Labels::getLabel('ERR_Invalid_Arguments!', CommonHelper::getLangId()), E_USER_ERROR);
        }
        //return 10;
        $sellerProductRow = static::getAttributesById($selprod_id, array( 'selprod_id', 'selprod_product_id', 'selprod_user_id'));
        $product_id = $sellerProductRow['selprod_product_id'];
        $selprod_user_id = $sellerProductRow['selprod_user_id'];

        $prodObj = new Product();
        $productCategories = $prodObj->getProductCategories($sellerProductRow['selprod_product_id']);
        $catIds = array();
        if ($productCategories) {
            foreach ($productCategories as $catId) {
                $catIds[] = $catId['prodcat_id'];
            }
        }

        /* to fetch the single row from the commission settings table, if single product is connected with multiple categories then will fetch the category according to price min or max, for now we have added min price i.e sort order of price is asc. [ */
        /* $srch = new SearchBase( Commission::DB_TBL );
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('commsetting_prodcat_id'));
        $srch->addCondition( 'commsetting_prodcat_id', 'IN', $catIds );
        $srch->addOrder('commsetting_fees', 'ASC');
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if( !$row ){
            $category_id = 0;
        } else {
            $category_id = $row['commsetting_prodcat_id'];
        } */
        /* ] */

        $db = FatApp::getDb();
        $sql = "SELECT commsetting_fees,
			CASE
				WHEN commsetting_product_id = '". $product_id ."' AND commsetting_user_id = '". $selprod_user_id ."' AND commsetting_prodcat_id IN (".implode(",", $catIds).") THEN 10
  				WHEN commsetting_product_id = '". $product_id ."' AND commsetting_user_id = '". $selprod_user_id ."' AND commsetting_prodcat_id = '0' THEN 9
				WHEN commsetting_product_id = '". $product_id ."' AND commsetting_user_id = 0 AND commsetting_prodcat_id IN (".implode(",", $catIds).") THEN 8
				WHEN commsetting_product_id = '". $product_id ."' AND commsetting_user_id = '0' AND commsetting_prodcat_id = '0' THEN 7

				WHEN commsetting_product_id = 0 AND commsetting_user_id = '". $selprod_user_id ."' AND commsetting_prodcat_id IN (".implode(",", $catIds).") THEN 6
				WHEN commsetting_product_id = 0 AND commsetting_user_id = '". $selprod_user_id ."' AND commsetting_prodcat_id = 0 THEN 5

				WHEN commsetting_product_id = 0 AND commsetting_user_id = 0 AND commsetting_prodcat_id IN (".implode(",", $catIds).") THEN 4

				WHEN (commsetting_product_id = '0' AND commsetting_user_id = '0' AND commsetting_prodcat_id = '0') THEN 1
			END
       		as matches FROM ". Commission::DB_TBL ." WHERE commsetting_deleted = 0 order by matches desc, commsetting_fees desc  limit 0,1";
        $rs = $db->query($sql);
        if ($row = $db->fetch($rs)) {
            return $row['commsetting_fees'];
        }
    }

    public function getProductsToGroup($prodgroup_id, $lang_id = 0)
    {
        $prodgroup_id = FatUtility::int($prodgroup_id);
        $lang_id = FatUtility::int($lang_id);
        $now = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');
        $forDate = $now;

        if ($prodgroup_id <= 0) {
            trigger_error(Labels::getLabel('ERR_Invalid_Arguments', CommonHelper::getLangId()), E_USER_ERROR);
        }

        $srch = new SearchBase(ProductGroup::DB_PRODUCT_TO_GROUP, 'ptg');
        $srch->joinTable(static::DB_TBL, 'INNER JOIN', 'ptg.'.ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX . 'selprod_id = sp.selprod_id', 'sp');
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'sp.selprod_product_id = p.product_id', 'p');
        $srch->joinTable(ProductGroup::DB_TBL, 'INNER JOIN', 'ptg.'.ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX.'prodgroup_id = pg.prodgroup_id', 'pg');
        $srch->joinTable(
            SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE,
            'LEFT OUTER JOIN',
            'splprice_selprod_id = selprod_id AND \'' . $forDate . '\' BETWEEN splprice_start_date AND splprice_end_date'
        );

        $srch->addCondition(ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX . 'prodgroup_id', '=', $prodgroup_id);
        $srch->addCondition('p.product_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('p.product_approved', '=', Product::APPROVED);
        $srch->addCondition('sp.selprod_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('sp.selprod_available_from', '<=', $now);

        if ($lang_id > 0) {
            $srch->joinTable(static::DB_LANG_TBL, 'LEFT OUTER JOIN', 'sp.selprod_id = sp_l.selprodlang_selprod_id AND selprodlang_lang_id = '.$lang_id, 'sp_l');
            $srch->addFld('selprod_title');

            $srch->joinTable(Product::DB_LANG_TBL, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND productlang_lang_id = '.$lang_id, 'p_l');
            $srch->addFld('IFNULL(product_name, product_identifier) as product_name');
        }

        /* if( $selprod_id > 0 ){
            $srch->addCondition( ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX . 'selprod_id', '=', $selprod_id );
        } */

        $srch->addMultipleFields(array('selprod_id', 'product_id', 'IFNULL(splprice_price, selprod_price) AS theprice','IF(selprod_stock > 0, 1, 0) AS in_stock', 'selprod_sold_count', 'CASE WHEN splprice_selprod_id IS NULL THEN 0 ELSE 1 END AS special_price_found', 'ptg.ptg_is_main_product'  ));
        $srch->addOrder('ptg_is_main_product', 'DESC');
        $srch->addOrder('product_name');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $products = FatApp::getDb()->fetchAll($rs);
        return $products;
    }

    public function getGroupsToProduct($lang_id = 0)
    {
        if ($this->mainTableRecordId < 1) {
            return array();
        }

        $lang_id = FatUtility::int($lang_id);

        $srch = new SearchBase(ProductGroup::DB_PRODUCT_TO_GROUP, 'ptg');
        $srch->joinTable(static::DB_TBL, 'INNER JOIN', 'ptg.'.ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX . 'selprod_id = sp.selprod_id', 'sp');
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'sp.selprod_product_id = p.product_id', 'p');
        $srch->joinTable(ProductGroup::DB_TBL, 'INNER JOIN', 'ptg.'.ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX.'prodgroup_id = pg.prodgroup_id', 'pg');

        $srch->addCondition(ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX . 'selprod_id', '=', $this->mainTableRecordId);
        $srch->addCondition('pg.prodgroup_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('p.product_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('p.product_approved', '=', Product::APPROVED);
        $srch->addCondition('sp.selprod_active', '=', applicationConstants::ACTIVE);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        if ($lang_id > 0) {
            $srch->joinTable(ProductGroup::DB_TBL_LANG, 'LEFT OUTER JOIN', 'pg.prodgroup_id = pg_l.prodgrouplang_prodgroup_id AND pg_l.prodgrouplang_lang_id = '.$lang_id, 'pg_l');
            $srch->addFld('IFNULL(prodgroup_name, prodgroup_identifier) as prodgroup_name');
        }
        $srch->addMultipleFields(array( 'selprod_id', 'ptg_prodgroup_id', 'pg.prodgroup_price' ));
        $srch->addOrder('pg.prodgroup_price');
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    public function addPolicyPointToSelProd($data)
    {
        $record = new TableRecord(self::DB_TBL_SELLER_PROD_POLICY);
        $record->assignValues($data);

        if (!$record->addNew(array(), $data)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function getRelatedProducts($sellProdId = 0, $lang_id = 0, $criteria = array())
    {
        $sellProdId = FatUtility::convertToType($sellProdId, FatUtility::VAR_INT);
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        if (!$sellProdId) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", CommonHelper::getLangId()), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_RELATED_PRODUCTS);
        $srch->addCondition(static::DB_TBL_RELATED_PRODUCTS_PREFIX . 'sellerproduct_id', '=', $sellProdId);
        $srch->joinTable(static::DB_TBL, 'INNER JOIN', static::DB_TBL_PREFIX.'id = '.static::DB_TBL_RELATED_PRODUCTS_PREFIX.'recommend_sellerproduct_id');
        $srch->joinTable(static::DB_TBL.'_lang', 'LEFT JOIN', 'slang.'.static::DB_LANG_TBL_PREFIX.'selprod_id = '.static::DB_TBL_RELATED_PRODUCTS_PREFIX . 'recommend_sellerproduct_id AND '.static::DB_LANG_TBL_PREFIX.'lang_id = '.$lang_id, 'slang');
        $srch->joinTable(Product::DB_TBL, 'LEFT JOIN', Product::DB_TBL_PREFIX.'id = '.static::DB_TBL_PREFIX.'product_id');
        $srch->joinTable(Product::DB_TBL.'_lang', 'LEFT JOIN', 'lang.productlang_product_id = '.static::DB_LANG_TBL_PREFIX . 'selprod_id AND productlang_lang_id = '.$lang_id, 'lang');
        if ($criteria) {
            $srch->addMultipleFields(array($criteria));
        } else {
            $srch->addMultipleFields(array(
            'selprod_id', 'IFNULL(selprod_title ,product_name) as product_name','product_identifier','selprod_price','product_image_updated_on'));
        }
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $data = array();
        if ($row = $db->fetchAll($rs, 'selprod_id')) {
            return $row;
        }
        return $data;
    }

    public function deleteSellerProduct($selprod_id)
    {
        if (!$selprod_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', CommonHelper::getLangId());
            return false;
        }

        $db = FatApp::getDb();
        if (!$db->updateFromArray(static::DB_TBL, array( static::DB_TBL_PREFIX . 'deleted' => 1), array('smt' => static::DB_TBL_PREFIX . 'id = ?','vals' => array($selprod_id)))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public static function getSelprodPolicies($selprod_id, $policy_type, $langId, $limit = null, $active = true, $deleted = false)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $policy_type = FatUtility::int($policy_type);
        $limit = FatUtility::int($limit);
        $srch = new SearchBase(static::DB_TBL_SELLER_PROD_POLICY);
        $srch->joinTable(PolicyPoint::DB_TBL, 'left outer join', 'sppolicy_ppoint_id = ppoint_id', 'pp');
        $srch->joinTable(
            PolicyPoint::DB_TBL_LANG,
            'LEFT OUTER JOIN',
            'pp_l.ppointlang_ppoint_id = pp.ppoint_id
			AND ppointlang_lang_id = ' . $langId,
            'pp_l'
        );
        $srch->addCondition('pp.ppoint_type', '=', $policy_type);
        $srch->addCondition('sppolicy_selprod_id', '=', $selprod_id);
        $srch->addMultipleFields(array('ppoint_id','ifnull(ppoint_title,ppoint_identifier) ppoint_title'));
        $srch->doNotCalculateRecords();
        $srch->addOrder('pp.ppoint_display_order');
        if ($deleted == false) {
            $srch->addCondition('pp.ppoint_deleted', '=', applicationConstants::NO);
        }

        if ($active == true) {
            $srch->addCondition('pp.ppoint_active', '=', applicationConstants::ACTIVE);
        }

        if ($limit) {
            $srch->setPageSize($limit);
        }
        return FatApp::getDb()->fetch($srch->getResultSet());
    }

    public static function getProductDisplayTitle($selProdId, $langId, $toHtml = false)
    {
        $prodSrch = new ProductSearch($langId, null, null, true, false);
        $prodSrch->joinSellerProducts(0, '', array(), false);
        if (is_array($selProdId) && 0 < count($selProdId)) {
            $prodSrch->addCondition('selprod_id', 'IN', $selProdId);
        } else {
            $prodSrch->addCondition('selprod_id', '=', $selProdId);
        }
        $prodSrch->addMultipleFields(array('selprod_id', 'product_id','product_identifier', 'IFNULL(product_name, product_identifier) as product_name', 'selprod_title'));
        $prodSrch->addGroupBy('selprod_id');
        $productRs = $prodSrch->getResultSet();
        $products = FatApp::getDb()->fetchAll($productRs, 'selprod_id');

        $productTitle = SellerProduct::getProductsOptionsString($products, $langId, $toHtml);

        return (is_array($selProdId)) ? $productTitle : $productTitle[$selProdId];
    }

    public static function getProductsOptionsString($products, $langId, $toHtml = false)
    {
        if (empty($products) || empty($langId)) {
            return false;
        }
        $optionsStringArr = array();
        foreach ($products as $selProdId => $product) {
            $variantStr = (!empty($product['product_name'])) ? $product['product_name'] : $product['selprod_title'];

            $options = static::getSellerProductOptions($selProdId, true, $langId);
            if (is_array($options) && count($options)) {
                $variantStr .= (true === $toHtml) ? '<br/>' : ' - ';
                $counter = 1;
                foreach ($options as $op) {
                    $variantStr .= (true === $toHtml) ? $op['option_name'].': '.$op['optionvalue_name'] : $op['optionvalue_name'];
                    if ($counter != count($options)) {
                        $variantStr .= (true === $toHtml) ? '<br/>' : ' - ';
                    }
                    $counter++;
                }
            }
            $optionsStringArr[$selProdId] = $variantStr;
        }
        return $optionsStringArr;
    }

    public function getVolumeDiscounts()
    {
        if ($this->mainTableRecordId < 1) {
            return array();
        }

        $srch = new SellerProductVolumeDiscountSearch();
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('voldiscount_min_qty', 'voldiscount_percentage'));
        $srch->addCondition('voldiscount_selprod_id', '=', $this->mainTableRecordId);
        $srch->addOrder('voldiscount_min_qty', 'ASC');
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    private function rewriteUrl($keyword, $type = 'product')
    {
        if ($this->mainTableRecordId < 1) {
            return false;
        }

        $keyword = preg_replace('/-'.$this->mainTableRecordId.'$/', '', $keyword);
        $seoUrl = CommonHelper::seoUrl($keyword);

        switch (strtolower($type)) {
            case 'reviews':
                $originalUrl = Product::PRODUCT_REVIEWS_ORGINAL_URL.$this->mainTableRecordId;
                $seoUrl = preg_replace('/-reviews$/', '', $seoUrl);
                $seoUrl.=  '-reviews';
                break;
            case 'moresellers':
                $originalUrl = Product::PRODUCT_MORE_SELLERS_ORGINAL_URL.$this->mainTableRecordId;
                $seoUrl = preg_replace('/-sellers$/', '', $seoUrl);
                $seoUrl.=  '-sellers';
                break;
            default:
                $originalUrl = Product::PRODUCT_VIEW_ORGINAL_URL.$this->mainTableRecordId;
                break;
        }

        $seoUrl.= '-'.$this->mainTableRecordId;

        $customUrl = UrlRewrite::getValidSeoUrl($seoUrl, $originalUrl);
        return UrlRewrite::update($originalUrl, $customUrl);
    }

    public function rewriteUrlProduct($keyword)
    {
        return $this->rewriteUrl($keyword, 'product');
    }

    public function rewriteUrlReviews($keyword)
    {
        return $this->rewriteUrl($keyword, 'reviews');
    }

    public function rewriteUrlMoreSellers($keyword)
    {
        return $this->rewriteUrl($keyword, 'moresellers');
    }

    public static function getActiveCount($userId, $selprodId = 0)
    {
        $selprodId = FatUtility::int($selprodId);
        $userId = FatUtility::int($userId);

        $srch = static::getSearchObject();
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id and p.product_deleted = '.applicationConstants::NO.' and p.product_active = '.applicationConstants::YES, 'p');

        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addCondition('selprod_user_id', '=', $userId);
        if ($selprodId) {
            $srch->addCondition('selprod_id', '!=', $selprodId);
        }

        $srch->addMultipleFields(array('selprod_id'));
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $records = $db->fetchAll($rs);
        return $srch->recordCount();
    }

    public function joinUserWishListProducts($srch, $user_id)
    {
        $wislistPSrchObj = new UserWishListProductSearch();
        $wislistPSrchObj->joinWishLists();
        $wislistPSrchObj->doNotCalculateRecords();
        $wislistPSrchObj->doNotLimitRecords();
        $wislistPSrchObj->addCondition('uwlist_user_id', '=', $user_id);
        $wislistPSrchObj->addMultipleFields(array('uwlp_selprod_id', 'uwlp_uwlist_id'));
        $wishListSubQuery = $wislistPSrchObj->getQuery();
        $srch->joinTable('(' . $wishListSubQuery . ')', 'LEFT OUTER JOIN', 'uwlp.uwlp_selprod_id = selprod_id', 'uwlp');
    }

    public function joinFavouriteProducts($srch, $user_id)
    {
        $srch->joinTable(Product::DB_TBL_PRODUCT_FAVORITE, 'LEFT OUTER JOIN', 'ufp.ufp_selprod_id = selprod_id and ufp.ufp_user_id = '.$user_id, 'ufp');
    }

    public static function specialPriceForm($langId)
    {
        $frm = new Form('frmSellerProductSpecialPrice');
        $fld = $frm->addFloatField(Labels::getLabel('LBL_Special_Price', $langId).CommonHelper::concatCurrencySymbolWithAmtLbl(), 'splprice_price');
        $fld->requirements()->setPositive();
        $fld = $frm->addDateField(Labels::getLabel('LBL_Price_Start_Date', $langId), 'splprice_start_date', '', array('readonly' => 'readonly'));
        $fld->requirements()->setRequired();

        $fld = $frm->addDateField(Labels::getLabel('LBL_Price_End_Date', $langId), 'splprice_end_date', '', array('readonly' => 'readonly'));
        $fld->requirements()->setRequired();
        $fld->requirements()->setCompareWith('splprice_start_date', 'ge', Labels::getLabel('LBL_Price_Start_Date', $langId));

        $frm->addHiddenField('', 'splprice_selprod_id');
        $frm->addHiddenField('', 'splprice_id');
        $fld1 = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        $fld2 = $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Cancel', $langId), array('onClick' => 'javascript:$("#sellerProductsForm").html(\'\')'));
        $fld1->attachField($fld2);
        return $frm;
    }

    public static function volumeDiscountForm($langId)
    {
        $frm = new Form('frmSellerProductSpecialPrice');

        $frm->addHiddenField('', 'voldiscount_selprod_id', 0);
        $frm->addHiddenField('', 'voldiscount_id', 0);
        $qtyFld = $frm->addIntegerField(Labels::getLabel("LBL_Minimum_Quantity", $langId), 'voldiscount_min_qty');
        $qtyFld->requirements()->setPositive();
        $discountFld = $frm->addFloatField(Labels::getLabel("LBL_Discount_in_(%)", $langId), "voldiscount_percentage");
        $discountFld->requirements()->setPositive();
        $fld1 = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        $fld2 = $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Cancel', $langId), array('onClick' => 'javascript:$("#sellerProductsForm").html(\'\')'));
        $fld1->attachField($fld2);
        return $frm;
    }

    public static function searchSpecialPriceProductsObj($langId, $selProdId = 0, $keyword = '', $userId = 0)
    {
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $srch = static::getSearchObject($langId);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'tuc.credential_user_id = sp.selprod_user_id', 'tuc');
        $srch->joinTable(static::DB_TBL_SELLER_PROD_SPCL_PRICE, 'INNER JOIN', 'spp.splprice_selprod_id = sp.selprod_id', 'spp');
        $srch->joinTable(Product::DB_LANG_TBL, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = '.$langId, 'p_l');

        $srch->addMultipleFields(
            array(
            'selprod_id', 'credential_username', 'selprod_price', 'date(splprice_start_date) as splprice_start_date', 'splprice_end_date', 'IFNULL(product_name, product_identifier) as product_name', 'selprod_title', 'splprice_id', 'splprice_price')
        );

        if (0 < $selProdId) {
            $srch->addCondition('selprod_id', '=', $selProdId);
        }
        if (!empty($keyword)) {
            $cnd = $srch->addCondition('product_name', 'like', "%$keyword%");
            $cnd->attachCondition('selprod_title', 'LIKE', '%'. $keyword . '%', 'OR');
        }

        if (0 < $userId) {
            $srch->addCondition('selprod_user_id', '=', $userId);
        }

        $srch->addCondition('selprod_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        // $srch->addOrder('selprod_active', 'DESC');
        // $srch->addOrder('selprod_added_on', 'DESC');
        $srch->addOrder('splprice_id', 'DESC');
        $srch->setPageSize($pageSize);
        return $srch;
    }

    public static function searchVolumeDiscountProducts($langId, $selProdId = 0, $keyword = '', $userId = 0)
    {
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $srch = static::getSearchObject($langId);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'tuc.credential_user_id = sp.selprod_user_id', 'tuc');
        $srch->joinTable(SellerProductVolumeDiscount::DB_TBL, 'INNER JOIN', 'vd.voldiscount_selprod_id = sp.selprod_id', 'vd');
        $srch->joinTable(Product::DB_LANG_TBL, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = '.$langId, 'p_l');
        $srch->addMultipleFields(
            array(
            'selprod_id', 'credential_username', 'voldiscount_min_qty', 'voldiscount_percentage', 'IFNULL(product_name, product_identifier) as product_name', 'selprod_title', 'voldiscount_id')
        );

        if (0 < $selProdId) {
            $srch->addCondition('selprod_id', '=', $selProdId);
        }


        if ($keyword != '') {
            $cnd = $srch->addCondition('product_name', 'like', "%$keyword%");
            $cnd->attachCondition('selprod_title', 'LIKE', '%'. $keyword . '%', 'OR');
        }

        if (0 < $userId) {
            $srch->addCondition('selprod_user_id', '=', $userId);
        }

        $srch->addCondition('selprod_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->setPageSize($pageSize);
        $srch->addOrder('voldiscount_id', 'DESC');
        return $srch;
    }
}
