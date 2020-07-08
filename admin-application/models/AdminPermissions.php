<?php
class AdminPermissions extends MyAppModel
{
    const DB_TBL = 'tbl_admin_permissions';
    const DB_TBL_PREFIX = 'admperm_';

    public function __construct($adminId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $adminId);
        $this->objMainTableRecord->setSensitiveFields(array());
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL);
        return $srch;
    }
}
