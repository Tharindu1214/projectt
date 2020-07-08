<?php
class SuccessStories extends MyAppModel
{
    const DB_TBL = 'tbl_success_stories';
    const DB_LANG_TBL = 'tbl_success_stories_lang';
    const DB_TBL_PREFIX = 'sstory_';
    const DB_TBL_LANG_PREFIX = 'sstorylang_';
    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($langId = 0, $isActive = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'ss');

        if ($isActive == true) {
            $srch->addCondition('ss.'.static::DB_TBL_PREFIX.'active', '=', applicationConstants::ACTIVE);
        }
        $srch->addCondition('ss.'.static::DB_TBL_PREFIX.'deleted', '=', applicationConstants::NO);

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'ss_l.'.static::DB_TBL_LANG_PREFIX.'sstory_id = ss.'.static::tblFld('id').' and
			ss_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = '.$langId,
                'ss_l'
            );
        }
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
