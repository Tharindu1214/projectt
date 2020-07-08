<?php
class ExtraAttribute extends MyAppModel
{
    const DB_TBL = 'tbl_extra_attributes';
    const DB_TBL_PREFIX = 'eattribute_';    
    private $db;

    public function __construct($id = 0) 
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db=FatApp::getDb();
    }
    
    public function getSearchObject($isDeleted=true) 
    {
        $srch = new SearchBase(static::DB_TBL, 'ea');
        return $srch;
    }
    
    public function getAttributesByIdAndGroupId( $groupId, $recordId, $attr = null )
    {
        $groupId = FatUtility::convertToType($groupId, FatUtility::VAR_INT);
        $recordId = FatUtility::convertToType($recordId, FatUtility::VAR_INT);
        
        $srch=$this->getSearchObject();        
        $srch->addCondition(static::tblFld('id'), '=', $recordId);
        $srch->addCondition(static::tblFld('eattrgroup_id'), '=', $groupId);
        if (null != $attr ) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            }
            elseif (is_string($attr)) {
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
    
    public function canDeleteRecord($id)
    {
        $srch =$this->getSearchObject();        
        $srch->addCondition(static::DB_TBL_PREFIX.'id', '=', $id);
        $srch->addFld(static::DB_TBL_PREFIX.'id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if(!empty($row) && $row[static::DB_TBL_PREFIX.'id']==$id) {
            return true;
        }
        return false;
    }
}
?>
