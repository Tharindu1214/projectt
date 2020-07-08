<?php
class ProductTempImage extends MyAppModel
{
    const DB_TBL = 'tbl_attached_files_temp';
    const DB_TBL_PREFIX = 'afile_';
    public function __construct($recordId = 0) 
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $recordId);
    }
    public function getTempImageSearchObject() 
    {
        $srch = static::getSearchObject();
        if($this->mainTableRecordId > 0) {
            $srch->addCondition('af.'.static::DB_TBL_PREFIX.'id', '=', $this->mainTableRecordId);
        }
        return $srch;
    }
    public static function getSearchObject() 
    {
        $srch = new SearchBase(static::DB_TBL, 'af');
        return $srch;
    }
}
