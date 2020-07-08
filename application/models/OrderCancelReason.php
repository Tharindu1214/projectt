<?php
class OrderCancelReason extends MyAppModel
{
    const DB_TBL = 'tbl_order_cancel_reasons';
    const DB_TBL_PREFIX = 'ocreason_';

    const DB_TBL_LANG = 'tbl_order_cancel_reasons_lang';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'ocreason');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'ocreason_l.ocreasonlang_ocreason_id = ocreason.ocreason_id
			AND ocreasonlang_lang_id = ' . $langId,
                'ocreason_l'
            );
        }
        return $srch;
    }

    public static function getOrderCancelReasonArr($langId = 0)
    {
        $srch = static::getSearchObject($langId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('ocreason_id','IFNULL(ocreason_title,ocreason_identifier) as ocreason_title'));
        $srch->addOrder('ocreason_title');
        $rs = $srch->getResultSet();
        return $row = FatApp::getDb()->fetchAllAssoc($rs);
    }
}
