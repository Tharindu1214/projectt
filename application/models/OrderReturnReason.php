<?php
class OrderReturnReason extends MyAppModel
{
    const DB_TBL = 'tbl_order_return_reasons';
    const DB_TBL_PREFIX = 'orreason_';

    const DB_TBL_LANG = 'tbl_order_return_reasons_lang';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'orreason');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'orreason_l.orreasonlang_orreason_id = orreason.orreason_id
			AND orreasonlang_lang_id = ' . $langId,
                'orreason_l'
            );
        }
        return $srch;
    }

    public static function getOrderReturnReasonArr($langId = 0)
    {
        $srch = static::getSearchObject($langId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orreason_id','IFNULL(orreason_title,orreason_identifier) as orreason_title'));
        $srch->addOrder('orreason_title');
        $rs = $srch->getResultSet();
        return $row = FatApp::getDb()->fetchAllAssoc($rs);
    }
}
