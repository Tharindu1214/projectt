<?php
class OptionValue extends MyAppModel
{
    const DB_TBL = 'tbl_option_values';
    const DB_TBL_LANG ='tbl_option_values_lang';
    const DB_TBL_PREFIX = 'optionvalue_';
    const DB_TBL_LANG_PREFIX = 'optionvaluelang_';
    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db=FatApp::getDb();
    }

    public static function getSearchObject($langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'ov');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'ov_l.'.static::DB_TBL_LANG_PREFIX.'optionvalue_id = ov.'.static::tblFld('id').' and
			ov_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = '.$langId,
                'ov_l'
            );
        }

        $srch->addOrder('ov.'.static::DB_TBL_PREFIX.'display_order', 'ASC');
        return $srch;
    }

    public function getOptionValue($optionId)
    {
        $optionId = FatUtility::convertToType($optionId, FatUtility::VAR_INT);

        $srch = static::getSearchObject();
        $srch->addCondition('ov.'.static::tblFld('option_id'), '=', $optionId);
        $srch->addCondition('ov.'.static::tblFld('id'), '=', $this->mainTableRecordId);

        $rs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($rs, static::DB_TBL_PREFIX.'id');

        if (!empty($record)) {
            $lang_record = CommonHelper::getLangFields(
                $record['optionvalue_id'],
                'optionvaluelang_optionvalue_id',
                'optionvaluelang_lang_id',
                array('optionvalue_name'),
                static::DB_TBL.'_lang'
            );
            return  array_merge($record, $lang_record);
        }

        return $record;
    }

    public function getAtttibutesByIdAndOptionId($optionId, $recordId, $attr = null)
    {
        $optionId = FatUtility::convertToType($optionId, FatUtility::VAR_INT);
        $recordId = FatUtility::convertToType($recordId, FatUtility::VAR_INT);

        $srch=static::getSearchObject();
        $srch->addCondition('ov.'.static::tblFld('id'), '=', $recordId);
        $srch->addCondition('ov.'.static::tblFld('option_id'), '=', $optionId);
        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if (!is_array($row)) {
            return false;
        }

        if (is_string($attr)) {
            return $row[$attr];
        }
        return $row;
    }

    public function getAtttibutesByIdentifierAndOptionId($optionId, $recordId, $attr = null)
    {
        $optionId = FatUtility::convertToType($optionId, FatUtility::VAR_INT);

        $srch=static::getSearchObject();
        $srch->addCondition('ov.'.static::tblFld('identifier'), '=', $recordId);
        $srch->addCondition('ov.'.static::tblFld('option_id'), '=', $optionId);
        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if (!is_array($row)) {
            return false;
        }

        if (is_string($attr)) {
            return $row[$attr];
        }
        return $row;
    }

    public function getAtttibutesByOptionId($optionId, $attr = null)
    {
        $optionId = FatUtility::convertToType($optionId, FatUtility::VAR_INT);

        $srch=static::getSearchObject();
        $srch->addCondition('ov.'.static::tblFld('option_id'), '=', $optionId);
        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAll($rs);

        if (!is_array($row)) {
            return false;
        }

        return $row;
    }

    public function canEditRecord($optionId)
    {
        $optionId = FatUtility::int($optionId);
        $srch = static::getSearchObject();
        $srch->addCondition('ov.'.static::DB_TBL_PREFIX.'id', '=', $this->mainTableRecordId);
        $srch->addCondition('ov.'.static::DB_TBL_PREFIX.'option_id', '=', $optionId);
        $srch->addFld('ov.'.static::DB_TBL_PREFIX.'id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row[static::DB_TBL_PREFIX.'id']==$this->mainTableRecordId) {
            return true;
        }
        return false;
    }

    public function isLinkedWithInventory()
    {
        $srch = SellerProduct::getSearchObject();
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'INNER JOIN', SellerProduct::DB_TBL_PREFIX.'id = '.SellerProduct::DB_TBL_SELLER_PROD_OPTIONS_PREFIX.'selprod_id');
        $srch->joinTable(static::DB_TBL, 'INNER JOIN', static::DB_TBL_PREFIX.'id = '.SellerProduct::DB_TBL_SELLER_PROD_OPTIONS_PREFIX.'optionvalue_id');
        $srch->addCondition(static::DB_TBL_PREFIX.'id', '=', $this->mainTableRecordId);
        $srch->addFld('selprod_id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row)) {
            return true;
        }
        return false;
    }

    /* public function deleteRecordByOptionId($optionId)
    {

    if (!FatApp::getDb()->deleteRecords(static::DB_TBL, array('smt'=>static::DB_TBL_PREFIX . 'option_id = ?', 'vals'=>array($optionId)))) {
    $this->error = FatApp::getDb()->getError();
    return false;
    }
    $prefix = substr(static::DB_TBL_PREFIX, 0, -1);
    if (!FatApp::getDb()->deleteRecords(static::DB_TBL.'_lang', array('smt'=>$prefix . 'lang_' . static::DB_TBL_PREFIX . 'option_id' . ' = ?', 'vals'=>array($optionId)))) {
    $this->error = FatApp::getDb()->getError();
    return false;
    }
    return true;
    } */
}
