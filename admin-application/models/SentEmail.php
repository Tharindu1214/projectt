<?php
class SentEmail extends MyAppModel
{
    const DB_TBL = 'tbl_email_archives';
    const DB_TBL_PREFIX = 'emailarchive_';
    
    public function __construct($adminId = 0) 
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $adminId);
        $this->objMainTableRecord->setSensitiveFields(array (''));
    }
    
    public function getSearchObject() 
    {
        $srch = new SearchBase(static::DB_TBL, 'm');
        $srch->addOrder('m.emailarchive_sent_on', 'DESC');
        return $srch;
    }
}
?>