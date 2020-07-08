<?php
class Tax extends MyAppModel
{
    const DB_TBL = 'tbl_tax_categories';
    const DB_TBL_PREFIX = 'taxcat_';

    const DB_TBL_LANG = 'tbl_tax_categories_lang';
    const DB_TBL_LANG_PREFIX = 'taxcatlang_';

    const DB_TBL_VALUES = 'tbl_tax_values';
    const DB_TBL_VALUES_PREFIX = 'taxval_';

    const DB_TBL_PRODUCT_TO_TAX = 'tbl_product_to_tax';
    const DB_TBL_PRODUCT_TO_TAX_PREFIX = 'ptt__';

    private $db;

    const TYPE_PERCENTAGE = 1;
    const TYPE_FIXED = 0;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getFieldTypeArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }
        $arr = array(
        static::TYPE_PERCENTAGE => Labels::getLabel('LBL_PERCENTAGE', $langId),
        static::TYPE_FIXED => Labels::getLabel('LBL_FIXED', $langId),
        );
        return $arr;
    }

    public static function getSearchObject($langId = 0, $isActive = true)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 't');

        if ($isActive == true) {
            $srch->addCondition('t.'.static::DB_TBL_PREFIX.'active', '=', applicationConstants::ACTIVE);
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                't_l.'.static::DB_TBL_LANG_PREFIX.'taxcat_id = t.'.static::tblFld('id').' and
			t_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = '.$langId,
                't_l'
            );
        }
        return $srch;
    }

    public static function getSaleTaxCatArr($langId, $isActive = true)
    {
        $langId = FatUtility::int($langId);

        $srch = static::getSearchObject($langId, $isActive);
        $srch->addCondition('taxcat_deleted', '=', 0);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(
            array(
            'taxcat_id',
            'IFNULL(taxcat_name,taxcat_identifier)as taxcat_name'
            )
        );

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAllAssoc($rs);

        if (!is_array($row)) {
            return false;
        }
        return $row;
    }

    public static function getTaxCatObjByProductId($productId, $langId = 0)
    {
        $srch = static::getSearchObject($langId);
        $srch->joinTable(
            static::DB_TBL_VALUES,
            'LEFT OUTER JOIN',
            'tv.taxval_taxcat_id = t.taxcat_id',
            'tv'
        );
        $srch->joinTable(
            static::DB_TBL_PRODUCT_TO_TAX,
            'LEFT OUTER JOIN',
            'ptt.ptt_taxcat_id = t.taxcat_id',
            'ptt'
        );

        $srch->addCondition('taxcat_deleted', '=', 0);
        $srch->addCondition('ptt_product_id', '=', FatUtility::int($productId));
        return $srch;
    }

    public function getTaxValuesByCatId($taxcat_id, $userId = 0, $defaultValue = false)
    {
        $taxcat_id = FatUtility::int($taxcat_id);
        $userId = FatUtility::int($userId);

        $srch = new SearchBase(static::DB_TBL_VALUES);
        $srch->addCondition('taxval_taxcat_id', '=', $taxcat_id);

        if ($defaultValue) {
            $srch->addOrder('taxval_seller_user_id', 'ASC');
        } else {
            $srch->addOrder('taxval_seller_user_id', 'DESC');
        }

        if ($userId > 0) {
            $cnd = $srch->addCondition('taxval_seller_user_id', '=', $userId);
            $cnd->attachCondition('taxval_seller_user_id', '=', 0, 'OR');
        }

        $srch->setPageSize(1);
        $srch->doNotCalculateRecords();

        $rs = $srch->getResultSet();
        $row = array();

        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row)) {
            return $row;
        }
        return array();
    }

    public function addUpdateTaxValues($data = array(), $onDuplicateUpdateData = array())
    {
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL_VALUES, $data, false, array(), $onDuplicateUpdateData)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public function addUpdateProductTaxCat($data)
    {
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL_PRODUCT_TO_TAX, $data, false, array(), $data)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public function addUpdateData($data)
    {
        unset($data['taxcat_id']);
        $assignValues = array(
        'taxcat_identifier' =>$data['taxcat_identifier'],
        'taxcat_active' =>$data['taxcat_active'],
        'taxcat_deleted' => 0,
        'taxcat_last_updated' => date('Y-m-d H:i:s')
        );

        if ($this->mainTableRecordId > 0) {
            $assignValues['taxcat_id'] = $this->mainTableRecordId;
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

    public function canRecordMarkDelete($id)
    {
        $srch = $this->getSearchObject(0, false);
        $srch->addCondition('t.'.static::DB_TBL_PREFIX.'id', '=', $id);
        $srch->addFld('t.'.static::DB_TBL_PREFIX.'id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row[static::DB_TBL_PREFIX.'id'] == $id) {
            return true;
        }
        return false;
    }

    public function getTaxRates($productId, $userId, $langId)
    {
        $productId = Fatutility::int($productId);
        $userId = Fatutility::int($userId);
        $langId = Fatutility::int($langId);

        $taxRates = array();
        $taxObj = self::getTaxCatObjByProductId($productId, $langId);
        $taxObj->addMultipleFields(array('IFNULL(taxcat_name,taxcat_identifier) as taxcat_name','ptt_seller_user_id','ptt_taxcat_id','ptt_product_id','taxval_is_percent','taxval_value'));
        $taxObj->doNotCalculateRecords();

        /* if(FatApp::getConfig('CONF_TAX_COLLECTED_BY_SELLER',FatUtility::VAR_INT,0)){
        $cnd = $taxObj->addCondition('ptt_seller_user_id','=',0);
        $cnd->attachCondition('ptt_seller_user_id','=',$userId,'OR');

        $cnd = $taxObj->addCondition('taxval_seller_user_id','=',0);
        $cnd->attachCondition('taxval_seller_user_id','=',$userId,'OR');
        }else{
        $cnd = $taxObj->addCondition('ptt_seller_user_id','=',0);
        $cnd = $taxObj->addCondition('taxval_seller_user_id','=',0);
        } */

        $cnd = $taxObj->addCondition('ptt_seller_user_id', '=', 0);
        $cnd->attachCondition('ptt_seller_user_id', '=', $userId, 'OR');

        $cnd = $taxObj->addCondition('taxval_seller_user_id', '=', 0);
        $cnd->attachCondition('taxval_seller_user_id', '=', $userId, 'OR');

        $taxObj->setPageSize(1);
        if (FatApp::getConfig('CONF_TAX_COLLECTED_BY_SELLER', FatUtility::VAR_INT, 0)) {
            $taxObj->addOrder('taxval_seller_user_id', 'DESC');
            $taxObj->addOrder('ptt_seller_user_id', 'DESC');
        } else {
            $taxObj->addOrder('taxval_seller_user_id', 'ASC');
            $taxObj->addOrder('ptt_seller_user_id', 'ASC');
        }

        $rs = $taxObj->getResultSet();
        return FatApp::getDb()->fetch($rs);
    }

    public function calculateTaxRates($productId, $prodPrice, $sellerId, $langId, $qty = 1, $extraDiscounts = array())
    {
        $tax = 0 ;
        $res = $this->getTaxRates($productId, $sellerId, $langId);

        if (empty($res)) {
            return $tax;
        }
        
        if ($res['taxval_is_percent'] == static::TYPE_PERCENTAGE) { 
            $tax = round((($prodPrice * $qty) * $res['taxval_value'])/100, 2);
        } else {
            $tax = $res['taxval_value']*$qty;
        }
        return $tax;
    }

    public static function getTaxCatByProductId($productId = 0, $userId = 0, $langId = 0, $fields = array())
    {
        $taxData =  array();
        $taxObj = static::getTaxCatObjByProductId($productId, $langId);
        $taxObj->addCondition('ptt_seller_user_id', '=', $userId);
        if ($fields) {
            $taxObj->addMultipleFields($fields);
        }
        $taxObj->doNotCalculateRecords();
        $taxObj->doNotLimitRecords();
        $rs = $taxObj->getResultSet();
        $taxData = FatApp::getDb()->fetch($rs);
        return $taxData;
    }

    public function removeTaxSetByAdmin($productId = 0)
    {
        FatApp::getDb()->deleteRecords(static::DB_TBL_PRODUCT_TO_TAX, array('smt'=>'ptt_seller_user_id = ? and ptt_product_id = ?', 'vals'=>array(0, $productId)));
    }
}
