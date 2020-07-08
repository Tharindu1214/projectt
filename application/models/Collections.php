<?php
class Collections extends MyAppModel
{
    const DB_TBL = 'tbl_collections';
    const DB_TBL_PREFIX = 'collection_';

    const DB_TBL_LANG = 'tbl_collections_lang';
    const DB_TBL_LANG_PREFIX = 'collectionlang_';

    const DB_TBL_COLLECTION_TO_SELPROD = 'tbl_collection_to_seller_products';
    const DB_TBL_COLLECTION_TO_SELPROD_PREFIX = 'ctsp_';

    const DB_TBL_COLLECTION_TO_BRANDS = 'tbl_collection_to_brands';
    const DB_TBL_COLLECTION_TO_BRANDS_PREFIX = 'ctpb_';

    const DB_TBL_COLLECTION_TO_PRODUCT_CATEGORIES = 'tbl_collection_to_product_categories';
    const DB_TBL_COLLECTION_TO_PRODUCT_CATEGORIES_PREFIX = 'ctpc_';

    const DB_TBL_COLLECTION_TO_SHOPS = 'tbl_collection_to_shops';
    const DB_TBL_COLLECTION_TO_SHOPS_PREFIX = 'ctps_';

    const COLLECTION_TYPE_PRODUCT = 1;
    const COLLECTION_TYPE_CATEGORY = 2;
    const COLLECTION_TYPE_SHOP = 3;
    const COLLECTION_TYPE_BRAND = 4;

    const TYPE_PRODUCT_LAYOUT1 = 1;
    const TYPE_PRODUCT_LAYOUT2 = 2;
    const TYPE_PRODUCT_LAYOUT3 = 3;
    const TYPE_CATEGORY_LAYOUT1 = 4;
    const TYPE_CATEGORY_LAYOUT2 = 5;
    const TYPE_SHOP_LAYOUT1 = 6;
    const TYPE_BRAND_LAYOUT1 = 7;

    const LIMIT_PRODUCT_LAYOUT1 = 12;
    const LIMIT_PRODUCT_LAYOUT2 = 6;
    const LIMIT_PRODUCT_LAYOUT3 = 12;
    const LIMIT_CATEGORY_LAYOUT1 = 8;
    const LIMIT_CATEGORY_LAYOUT2 = 4;
    const LIMIT_SHOP_LAYOUT1= 4;
    const LIMIT_BRAND_LAYOUT1 = 5;

    const COLLECTION_CRITERIA_PRICE_LOW_TO_HIGH = 1;
    const COLLECTION_CRITERIA_PRICE_HIGH_TO_LOW = 2;

    const COLLECTION_WITHOUT_MEDIA = [
            Collections::COLLECTION_TYPE_SHOP,
            Collections::COLLECTION_TYPE_BRAND
        ];

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db=FatApp::getDb();
    }

    public static function getSearchObject($isActive = true, $langId = 0)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'c');

        $srch->addCondition('c.'.static::DB_TBL_PREFIX.'deleted', '=', applicationConstants::NO);
        if ($isActive==true) {
            $srch->addCondition('c.'.static::DB_TBL_PREFIX.'active', '=', applicationConstants::ACTIVE);
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'c_l.'.static::DB_TBL_LANG_PREFIX.'collection_id = c.'.static::tblFld('id').' and
			c_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = '.$langId,
                'c_l'
            );
        }

        return $srch;
    }

    public static function getTypeArr($langId = 0)
    {
        $langId = FatUtility::convertToType($langId, FatUtility::VAR_INT);
        if (!$langId) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
            return false;
        }
        return array(
        self::COLLECTION_TYPE_PRODUCT => Labels::getLabel('LBL_Product', $langId),
        self::COLLECTION_TYPE_CATEGORY => Labels::getLabel('LBL_Category', $langId),
        self::COLLECTION_TYPE_SHOP => Labels::getLabel('LBL_Shop', $langId),
        self::COLLECTION_TYPE_BRAND => Labels::getLabel('LBL_Brand', $langId),
        );
    }

    public static function getLayoutTypeArr($langId = 0)
    {
        $langId = FatUtility::convertToType($langId, FatUtility::VAR_INT);
        if (!$langId) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
            return false;
        }

        return array(
        self::TYPE_PRODUCT_LAYOUT1 => Labels::getLabel('LBL_Product_Layout1', $langId),
        self::TYPE_PRODUCT_LAYOUT2 => Labels::getLabel('LBL_Product_Layout2', $langId),
        self::TYPE_PRODUCT_LAYOUT3 => Labels::getLabel('LBL_Product_Layout3', $langId),
        self::TYPE_CATEGORY_LAYOUT1 => Labels::getLabel('LBL_Category_Layout1', $langId),
        self::TYPE_CATEGORY_LAYOUT2 => Labels::getLabel('LBL_Category_Layout2', $langId),
        self::TYPE_SHOP_LAYOUT1 => Labels::getLabel('LBL_Shop_Layout1', $langId),
        self::TYPE_BRAND_LAYOUT1 => Labels::getLabel('LBL_Brand_Layout1', $langId),
        );
    }

    public static function getCriteria()
    {
        return array(
        static::COLLECTION_CRITERIA_PRICE_LOW_TO_HIGH=>"Price Low to High",
        static::COLLECTION_CRITERIA_PRICE_HIGH_TO_LOW=>"Price High to Low",
        );
    }

    public function addUpdateCollectionSelProd($collection_id, $selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $collection_id = FatUtility::int($collection_id);
        if (!$selprod_id || !$collection_id) {
            $this->error = Labels::getLabel('MSG_Invalid_Request', $this->commonLangId);
            return false;
        }
        $record = new TableRecord(static::DB_TBL_COLLECTION_TO_SELPROD);
        $to_save_arr = array();
        $to_save_arr[static::DB_TBL_COLLECTION_TO_SELPROD_PREFIX.'collection_id'] = $collection_id;
        $to_save_arr[static::DB_TBL_COLLECTION_TO_SELPROD_PREFIX.'selprod_id'] = $selprod_id;
        $record->assignValues($to_save_arr);
        if (!$record->addNew(array(), $to_save_arr)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function addUpdateCollectionCategories($collection_id, $prodcat_id)
    {
        $prodcat_id = FatUtility::int($prodcat_id);
        $collection_id = FatUtility::int($collection_id);
        if (!$prodcat_id || !$collection_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        $record = new TableRecord(static::DB_TBL_COLLECTION_TO_PRODUCT_CATEGORIES);
        $to_save_arr = array();
        $to_save_arr[static::DB_TBL_COLLECTION_TO_PRODUCT_CATEGORIES_PREFIX.'collection_id'] = $collection_id;
        $to_save_arr[static::DB_TBL_COLLECTION_TO_PRODUCT_CATEGORIES_PREFIX.'prodcat_id'] = $prodcat_id;
        $record->assignValues($to_save_arr);
        if (!$record->addNew(array(), $to_save_arr)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function addUpdateCollectionShops($collection_id, $shop_id)
    {
        $shop_id = FatUtility::int($shop_id);
        $collection_id = FatUtility::int($collection_id);
        if (!$shop_id || !$collection_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        $record = new TableRecord(static::DB_TBL_COLLECTION_TO_SHOPS);
        $to_save_arr = array();
        $to_save_arr[static::DB_TBL_COLLECTION_TO_SHOPS_PREFIX.'collection_id'] = $collection_id;
        $to_save_arr[static::DB_TBL_COLLECTION_TO_SHOPS_PREFIX.'shop_id'] = $shop_id;
        $record->assignValues($to_save_arr);
        if (!$record->addNew(array(), $to_save_arr)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function addUpdateCollectionBrands($collectionId, $brandId)
    {
        $brandId = FatUtility::int($brandId);
        $collectionId = FatUtility::int($collectionId);
        if (!$brandId || !$collectionId) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        $record = new TableRecord(static::DB_TBL_COLLECTION_TO_BRANDS);

        $brandData[static::DB_TBL_COLLECTION_TO_BRANDS_PREFIX . 'collection_id'] = $collectionId;
        $brandData[static::DB_TBL_COLLECTION_TO_BRANDS_PREFIX . 'brand_id'] = $brandId;
        $record->assignValues($brandData);
        if (!$record->addNew(array(), $brandData)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function addUpdateData($data)
    {
        unset($data['collection_id']);
        $assignValues = $data;
        $assignValues['collection_deleted'] = 0;
        if ($this->mainTableRecordId > 0) {
            $assignValues['collection_id'] = $this->mainTableRecordId;
        }
        $record = new TableRecord(self::DB_TBL);

        $record->assignValues($assignValues);
        if (!$record->addNew(array(), $assignValues)) {
            $this->error = $record->getError();
            return false;
        }

        $this->mainTableRecordId = $record->getId();
        return true;
    }

    public static function getSellProds($collection_id, $lang_id)
    {
        $collection_id = FatUtility::convertToType($collection_id, FatUtility::VAR_INT);
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        if (!$collection_id || !$lang_id) {
            trigger_error(Labels::getLabel('MSG_Arguments_not_specified.', $lang_id), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COLLECTION_TO_SELPROD);
        $srch->addCondition(static::DB_TBL_COLLECTION_TO_SELPROD_PREFIX . 'collection_id', '=', $collection_id);
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', SellerProduct::DB_TBL_PREFIX.'id = '.static::DB_TBL_COLLECTION_TO_SELPROD_PREFIX.'selprod_id');
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', SellerProduct::DB_TBL_PREFIX.'product_id = '.Product::DB_TBL_PREFIX.'id');

        $srch->joinTable(SellerProduct::DB_TBL.'_lang', 'LEFT JOIN', 'lang.selprodlang_selprod_id = ' . SellerProduct::DB_TBL_PREFIX.'id AND selprodlang_lang_id = '.$lang_id, 'lang');

        $srch->addMultipleFields(array('selprod_id','IFNULL(selprod_title,product_identifier) as selprod_title'));

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $data = array();
        while ($row = $db->fetch($rs)) {
            $data[] = $row;
        }
        return $data;
    }

    public function removeCollectionSelProd($collection_id, $selprod_id)
    {
        $db = FatApp::getDb();
        $collection_id = FatUtility::int($collection_id);
        $selprod_id = FatUtility::int($selprod_id);
        if (!$collection_id || !$selprod_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        if (!$db->deleteRecords(static::DB_TBL_COLLECTION_TO_SELPROD, array('smt'=> static::DB_TBL_COLLECTION_TO_SELPROD_PREFIX.'collection_id = ? AND '.static::DB_TBL_COLLECTION_TO_SELPROD_PREFIX . 'selprod_id = ?','vals' => array($collection_id, $selprod_id) ))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function removeCollectionCategories($collection_id, $prodcat_id)
    {
        $db = FatApp::getDb();
        $collection_id = FatUtility::int($collection_id);
        $prodcat_id = FatUtility::int($prodcat_id);
        if (!$collection_id || !$prodcat_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            ;
            return false;
        }
        if (!$db->deleteRecords(static::DB_TBL_COLLECTION_TO_PRODUCT_CATEGORIES, array('smt'=> static::DB_TBL_COLLECTION_TO_PRODUCT_CATEGORIES_PREFIX.'collection_id = ? AND '.static::DB_TBL_COLLECTION_TO_PRODUCT_CATEGORIES_PREFIX . 'prodcat_id = ?','vals' => array($collection_id, $prodcat_id) ))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function removeCollectionShops($collection_id, $shop_id)
    {
        $db = FatApp::getDb();
        $collection_id = FatUtility::int($collection_id);
        $shop_id = FatUtility::int($shop_id);
        if (!$collection_id || !$shop_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            ;
            return false;
        }
        if (!$db->deleteRecords(static::DB_TBL_COLLECTION_TO_SHOPS, array('smt'=> static::DB_TBL_COLLECTION_TO_SHOPS_PREFIX.'collection_id = ? AND '.static::DB_TBL_COLLECTION_TO_SHOPS_PREFIX . 'shop_id = ?','vals' => array($collection_id, $shop_id) ))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function removeCollectionBrands($collectionId, $brandId)
    {
        $db = FatApp::getDb();
        $collection_id = FatUtility::int($collectionId);
        $brandId = FatUtility::int($brandId);
        if (!$collectionId || !$brandId) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            ;
            return false;
        }
        if (!$db->deleteRecords(static::DB_TBL_COLLECTION_TO_BRANDS, array('smt' => static::DB_TBL_COLLECTION_TO_BRANDS_PREFIX . 'collection_id = ? AND ' . static::DB_TBL_COLLECTION_TO_BRANDS_PREFIX . 'brand_id = ?', 'vals' => array($collectionId, $brandId)))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function canRecordMarkDelete($collection_id)
    {
        $srch = static::getSearchObject(false);
        $srch->addCondition('collection_deleted', '=', applicationConstants::NO);
        $srch->addCondition('collection_id', '=', $collection_id);
        $srch->addFld('collection_id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row['collection_id']==$collection_id) {
            return true;
        }
        return false;
    }

    public static function getCategories($collection_id, $lang_id)
    {
        $collection_id = FatUtility::convertToType($collection_id, FatUtility::VAR_INT);

        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        if (!$collection_id || !$lang_id) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $lang_id), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COLLECTION_TO_PRODUCT_CATEGORIES);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addCondition(static::DB_TBL_COLLECTION_TO_PRODUCT_CATEGORIES_PREFIX. 'collection_id', '=', $collection_id);

        $srch->joinTable(ProductCategory::DB_TBL, 'INNER JOIN', ProductCategory::DB_TBL_PREFIX .'id = ' . static::DB_TBL_COLLECTION_TO_PRODUCT_CATEGORIES_PREFIX.'prodcat_id');

        $srch->joinTable(ProductCategory::DB_LANG_TBL, 'LEFT JOIN', 'lang.prodcatlang_prodcat_id = ' . ProductCategory::DB_TBL_PREFIX . 'id AND prodcatlang_lang_id = ' . $lang_id, 'lang');
        $srch->addMultipleFields(array('prodcat_id', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name'));
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $data =  $db->fetchAll($rs);
        return $data;
    }

    public static function getShops($collection_id, $lang_id)
    {
        $collection_id = FatUtility::convertToType($collection_id, FatUtility::VAR_INT);

        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        if (!$collection_id || !$lang_id) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $lang_id), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COLLECTION_TO_SHOPS);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addCondition(static::DB_TBL_COLLECTION_TO_SHOPS_PREFIX. 'collection_id', '=', $collection_id);

        $srch->joinTable(Shop::DB_TBL, 'INNER JOIN', Shop::DB_TBL_PREFIX .'id = ' . static::DB_TBL_COLLECTION_TO_SHOPS_PREFIX.'shop_id');

        $srch->joinTable(Shop::DB_TBL_LANG, 'LEFT JOIN', 'lang.shoplang_shop_id = ' . Shop::DB_TBL_PREFIX . 'id AND shoplang_lang_id = ' . $lang_id, 'lang');
        $srch->addMultipleFields(array('shop_id', 'IFNULL(shop_name, shop_identifier) as shop_name'));
        $rs = $srch->getResultSet();

        $db = FatApp::getDb();
        $data =  $db->fetchAll($rs);
        return $data;
    }

    public static function getBrands($collectionId, $langId)
    {
        $collectionId = FatUtility::convertToType($collectionId, FatUtility::VAR_INT);

        $langId = FatUtility::convertToType($langId, FatUtility::VAR_INT);
        if (!$collectionId || !$langId) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $langId), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COLLECTION_TO_BRANDS);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addCondition(static::DB_TBL_COLLECTION_TO_BRANDS_PREFIX . 'collection_id', '=', $collectionId);

        $srch->joinTable(Brand::DB_TBL, 'INNER JOIN', Brand::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_COLLECTION_TO_BRANDS_PREFIX . 'brand_id');

        $srch->joinTable(Brand::DB_LANG_TBL, 'LEFT JOIN', 'lang.brandlang_brand_id = ' . Brand::DB_TBL_PREFIX . 'id AND brandlang_lang_id = ' . $langId, 'lang');
        $srch->addMultipleFields(array('brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name'));
        $rs = $srch->getResultSet();

        $db = FatApp::getDb();
        $data = $db->fetchAll($rs);
        return $data;
    }

    public static function setLastUpdatedOn($collectionId)
    {
        $collectionId = FatUtility::int($collectionId);
        if (1 > $collectionId) {
            return false;
        }

        $collectionObj = new Collections($collectionId);
        $collectionObj->addUpdateData(array('collection_img_updated_on' => date('Y-m-d H:i:s')));
        return true;
    }
}
