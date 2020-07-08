<?php
class Polling extends MyAppModel
{
    const DB_TBL = 'tbl_polling';
    const DB_TBL_PREFIX = 'polling_';
    const DB_TBL_LANG = 'tbl_polling_lang';
    const DB_TBL_POLLING_TO_PRODUCTS = 'tbl_polling_to_products';
    const DB_TBL_POLLING_TO_CATEGORY = 'tbl_polling_to_category';

    const POLLING_TYPE_PRODUCTS = 1;
    const POLLING_TYPE_CATEGORY = 2;
    const POLLING_TYPE_GENERIC = 3;

    const RESPONSE_TYPE_YES = 1;
    const RESPONSE_TYPE_NO = 2;
    const RESPONSE_TYPE_MAY_BE = 3;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($langId = 0, $isActive = true, $includeResultCount = false)
    {
        $srch = new SearchBase(static::DB_TBL, '');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'pollinglang_polling_id = polling_id
			AND pollinglang_lang_id = ' . $langId
            );
        }

        if ($includeResultCount) {
            $srchFeedback = PollFeedback::getSearchObject();
            $srchFeedback->addGroupby('pollfeedback_polling_id');
            $srchFeedback->addMultipleFields(array('pollfeedback_polling_id','sum(if(pollfeedback_response_type=1,1,0)) count_yes','sum(if(pollfeedback_response_type=2,1,0)) count_no','sum(if(pollfeedback_response_type=3,1,0)) count_maybe'));
            $srchFeedback->doNotCalculateRecords();
            $srchFeedback->doNotLimitRecords();
            $pollFeedbackQuery = $srchFeedback->getQuery();
            $srch->joinTable("($pollFeedbackQuery)", 'left outer join', 'pfq.pollfeedback_polling_id = polling_id', 'pfq');
        }

        if ($isActive) {
            $srch->addCondition('polling_active', '=', applicationConstants::ACTIVE);
        }
        return $srch;
    }

    public static function getPollingTypeArr($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::POLLING_TYPE_PRODUCTS => Labels::getLabel('LBL_Products', $langId),
        static::POLLING_TYPE_CATEGORY => Labels::getLabel('LBL_Category', $langId),
        static::POLLING_TYPE_GENERIC => Labels::getLabel('LBL_Generic', $langId),
        );
    }

    public static function getPollingResponseTypeArr($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::RESPONSE_TYPE_YES => Labels::getLabel('LBL_Yes', $langId),
        static::RESPONSE_TYPE_NO => Labels::getLabel('LBL_No', $langId),
        static::RESPONSE_TYPE_MAY_BE => Labels::getLabel('LBL_May_Be', $langId),
        );
    }

    public static function getLinkedCategories($pollingId = 0, $langId = 0, $active = true)
    {
        $srch = self::getSearchObject($langId, $active);
        $srch->joinTable(self::DB_TBL_POLLING_TO_CATEGORY, 'inner join', 'ptc_polling_id = polling_id');
        $srch->joinTable(ProductCategory::DB_TBL, 'inner join', 'ptc_prodcat_id = prodcat_id');
        if (!empty($langId)) {
            $srch->joinTable(ProductCategory::DB_LANG_TBL, 'left outer join', 'prodcatlang_prodcat_id = prodcat_id and prodcatlang_lang_id = '.$langId);
        }
        $srch->addCondition('polling_id', '=', $pollingId);
        $srch->addMultipleFields(array('prodcat_id' , 'prodcat_identifier','prodcat_name'));
        return FatApp::getDb()->fetchAll($srch->getResultset());
    }

    public static function getLinkedProducts($pollingId = 0, $langId = 0, $active = true)
    {
        $srch = self::getSearchObject($langId, $active);
        $srch->joinTable(self::DB_TBL_POLLING_TO_PRODUCTS, 'inner join', 'ptp_polling_id = polling_id');
        $srch->joinTable(Product::DB_TBL, 'inner join', 'ptp_product_id = product_id');
        if (!empty($langId)) {
            $srch->joinTable(Product::DB_LANG_TBL, 'left outer join', 'productlang_product_id = product_id and productlang_lang_id = '.$langId);
        }
        $srch->addCondition('polling_id', '=', $pollingId);
        $srch->addMultipleFields(array('product_id' , 'IFNULL(product_name,product_identifier) as product_name'));
        return FatApp::getDb()->fetchAll($srch->getResultset());
    }

    public function addUpdatePollingCategory($polling_id, $prodcat_id)
    {
        $polling_id = FatUtility::int($polling_id);
        $prodcat_id = FatUtility::int($prodcat_id);
        if (!$polling_id || !$prodcat_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        $record = new TableRecord(static::DB_TBL_POLLING_TO_CATEGORY);
        $to_save_arr = array();
        $to_save_arr['ptc_polling_id'] = $polling_id;
        $to_save_arr['ptc_prodcat_id'] = $prodcat_id;
        $record->assignValues($to_save_arr);
        if (!$record->addNew(array(), $to_save_arr)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function removePollingCategory($polling_id, $prodcat_id)
    {
        $db = FatApp::getDb();
        $polling_id = FatUtility::int($polling_id);
        $prodcat_id = FatUtility::int($prodcat_id);
        if (!$polling_id || !$prodcat_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        if (!$db->deleteRecords(static::DB_TBL_POLLING_TO_CATEGORY, array('smt'=> 'ptc_polling_id = ? AND ptc_prodcat_id = ?','vals' => array($polling_id, $prodcat_id) ))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function addUpdatePollingProduct($polling_id, $product_id)
    {
        $polling_id = FatUtility::int($polling_id);
        $product_id = FatUtility::int($product_id);
        if (!$polling_id || !$product_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        $record = new TableRecord(static::DB_TBL_POLLING_TO_PRODUCTS);
        $to_save_arr = array();
        $to_save_arr['ptp_polling_id'] = $polling_id;
        $to_save_arr['ptp_product_id'] = $product_id;
        $record->assignValues($to_save_arr);
        if (!$record->addNew(array(), $to_save_arr)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function removePollingProduct($polling_id, $product_id)
    {
        $db = FatApp::getDb();
        $polling_id = FatUtility::int($polling_id);
        $product_id = FatUtility::int($product_id);
        if (!$polling_id || !$product_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        if (!$db->deleteRecords(static::DB_TBL_POLLING_TO_PRODUCTS, array('smt'=> 'ptp_polling_id = ? AND ptp_product_id = ?','vals' => array($polling_id, $product_id) ))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public static function getGeneraicPoll($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId <= 0) {
            // throw error
        }
        $pollSrch = static::getSearchObject($langId);
        $pollSrch->addCondition('polling_type', '=', static::POLLING_TYPE_GENERIC);
        $pollSrch->addCondition('polling_start_date', '<=', date('Y-m-d'));
        $pollSrch->addCondition('polling_end_date', '>=', date('Y-m-d'));
        $pollSrch->addOrder('polling_end_date', 'asc');
        $pollSrch->doNotCalculateRecords();
        $pollSrch->doNotLimitRecords();
        return FatApp::getDb()->fetch($pollSrch->getResultSet());
    }

    public static function getProductPoll($productId, $langId)
    {
        $langId = FatUtility::int($langId);
        $productId = FatUtility::int($productId);
        if ($langId <= 0 || $productId <=0) {
            // throw error
        }
        $pollSrch = static::getSearchObject($langId);
        $pollSrch->joinTable(static::DB_TBL_POLLING_TO_PRODUCTS, 'inner join', 'ptp_polling_id = polling_id and ptp_product_id ='.$productId);
        $pollSrch->addCondition('polling_type', '=', static::POLLING_TYPE_PRODUCTS);
        $pollSrch->addCondition('polling_start_date', '<=', date('Y-m-d'));
        $pollSrch->addCondition('polling_end_date', '>=', date('Y-m-d'));
        $pollSrch->addOrder('polling_end_date', 'asc');
        $pollSrch->doNotCalculateRecords();
        $pollSrch->doNotLimitRecords();
        return FatApp::getDb()->fetch($pollSrch->getResultSet());
    }

    public static function getCategoryPoll($prodcatId, $langId)
    {
        $langId = FatUtility::int($langId);
        $prodcatId = FatUtility::int($prodcatId);
        if ($langId <= 0 || $prodcatId <=0) {
            // throw error
        }
        $pollSrch = static::getSearchObject($langId);
        $pollSrch->joinTable(static::DB_TBL_POLLING_TO_CATEGORY, 'inner join', 'ptc_polling_id = polling_id and ptc_prodcat_id ='.$prodcatId);
        $pollSrch->addCondition('polling_type', '=', static::POLLING_TYPE_CATEGORY);
        $pollSrch->addCondition('polling_start_date', '<=', date('Y-m-d'));
        $pollSrch->addCondition('polling_end_date', '>=', date('Y-m-d'));
        $pollSrch->addOrder('polling_end_date', 'asc');
        $pollSrch->doNotCalculateRecords();
        $pollSrch->doNotLimitRecords();
        /* echo ($pollSrch->getQuery()); */
        return FatApp::getDb()->fetch($pollSrch->getResultSet());
    }
}
