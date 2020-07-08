<?php
class Faq extends MyAppModel
{
    const DB_TBL = 'tbl_faqs';
    const DB_LANG_TBL = 'tbl_faqs_lang';
    const DB_TBL_PREFIX = 'faq_';
    const DB_TBL_LANG_PREFIX = 'faqlang_';
    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($langId = 0, $isDeleted = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'f');

        if ($isDeleted == true) {
            $srch->addCondition('f.'.static::DB_TBL_PREFIX.'deleted', '=', applicationConstants::NO);
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'f_l.'.static::DB_TBL_LANG_PREFIX.'faq_id = f.'.static::tblFld('id').' and
			f_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = '.$langId,
                'f_l'
            );
        }

        $srch->addOrder('f.'.static::DB_TBL_PREFIX.'active', 'DESC');
        return $srch;
    }

    public function getMaxOrder()
    {
        $srch = new SearchBase(static::DB_TBL);
        $srch->addFld("MAX(" . static::DB_TBL_PREFIX . "display_order) as max_order");
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($rs);
        if (!empty($record)) {
            return $record['max_order']+1;
        }
        return 1;
    }
}
