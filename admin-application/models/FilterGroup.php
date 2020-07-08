<?php
class FilterGroup extends MyAppModel
{
    const DB_TBL = 'tbl_filter_groups';
    const DB_TBL_PREFIX = 'filtergroup_';    
    private $db;

    public function __construct($id = 0) 
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db=FatApp::getDb();
    }
    
    public function getSearchObject($isDeleted=true) 
    {
        $srch = new SearchBase(static::DB_TBL, 'fg');
        if($isDeleted==true) {
            $srch->addCondition('fg.'.static::DB_TBL_PREFIX.'deleted', '=', 0);
        }
        $srch->addOrder('fg.'.static::DB_TBL_PREFIX.'active', 'DESC');
        return $srch;
    }    
    
    public function canRecordMarkDelete($id)
    {
        $srch =$this->getSearchObject();        
        $srch->addCondition('fg.'.static::DB_TBL_PREFIX.'id', '=', $id);
        $srch->addFld('fg.'.static::DB_TBL_PREFIX.'id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if(!empty($row) && $row[static::DB_TBL_PREFIX.'id']==$id) {
            return true;
        }
        return false;
    }

    public function canRecordUpdateStatus($id)
    {
        $srch =$this->getSearchObject();        
        $srch->addCondition('fg.'.static::DB_TBL_PREFIX.'id', '=', $id);
        $srch->addFld('fg.'.static::DB_TBL_PREFIX.'id', 'fg.'.static::DB_TBL_PREFIX.'active');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if(!empty($row) && $row[static::DB_TBL_PREFIX.'id']==$id) {
            return $row;
        }
        return false;
    }
}
