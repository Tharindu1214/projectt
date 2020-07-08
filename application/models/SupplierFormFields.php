<?php
class SupplierFormFields extends MyAppModel
{
    const DB_TBL = 'tbl_user_supplier_form_fields';
    const DB_TBL_PREFIX = 'sformfield_';

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db=FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'sf');
        return $srch;
    }

    public function getMaxOrder()
    {
        $srch = static::getSearchObject();
        $srch->addFld("MAX(" . static::DB_TBL_PREFIX . "display_order) as max_order");
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        if (!$rs) {
            return 1;
        }
        $record = FatApp::getDb()->fetch($rs);
        if (!empty($record)) {
            return $record['max_order']+1;
        }
        return 1;
    }

    public function canDeleteRecord()
    {
        if (!($this->mainTableRecordId > 0)) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        $srch = static::getSearchObject();
        $srch->addCondition('sf.'.static::DB_TBL_PREFIX.'id', '=', $this->mainTableRecordId);
        $srch->addFld(array('sf.'.static::DB_TBL_PREFIX.'id','sf.'.static::DB_TBL_PREFIX.'mandatory'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        if ($rs) {
            $row = FatApp::getDb()->fetch($rs); //var_dump($row);
            if (!empty($row) && $row[static::DB_TBL_PREFIX.'id'] == $this->mainTableRecordId) {
                if ($row[static::DB_TBL_PREFIX.'mandatory'] == 1) {
                    return false;
                }
                return true;
            }
        }
        return false;
    }
}
