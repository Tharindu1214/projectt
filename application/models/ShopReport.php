<?php
class ShopReport extends MyAppModel
{
    const DB_TBL = 'tbl_shop_reports';
    const DB_TBL_PREFIX = 'sreport_';

    public function __construct($sreport_id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $sreport_id);
        $this->objMainTableRecord->setSensitiveFields(array());
    }

    public static function getSearchObject($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'sreport');
        return $srch;
    }
}
