<?php
class Abusive extends MyAppModel
{
    const DB_TBL = 'tbl_abusive_words';
    const DB_TBL_PREFIX = 'abusive_';

    public function __construct($abusiveId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $abusiveId);
    }

    public static function getSearchObject($langId = 0)
    {
        $langId =  FatUtility::int($langId);

        $srch = new SearchBase(static::DB_TBL, 'aw');

        if ($langId > 0) {
            $srch->addCondition('lbl.' . static::DB_TBL_PREFIX . 'lang_id', '=', $langId);
        }
        return $srch;
    }

    public static function getAbusiveWords($langId = 0)
    {
        $srch = static::getSearchObject($langId);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('abusive_id','abusive_keyword'));
        $records = FatApp::getDb()->fetchAllAssoc($srch->getResultSet());
        return array_values($records);
    }

    public static function validateContent($textToBeCheck, &$enteredAbusiveWordsArr = array())
    {
        $srch = Abusive::getSearchObject();
        $srch->joinTable(Language::DB_TBL, 'INNER JOIN', 'abusive_lang_id = language_id AND language_active = '. applicationConstants::ACTIVE);
        $srch->addOrder('aw.' . Abusive::DB_TBL_PREFIX . 'lang_id', 'ASC');
        $srch->addMultipleFields(array( 'abusive_id', 'abusive_keyword' ));
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        $abusiveWordsArr = FatApp::getDb()->fetchAllAssoc($rs);
        $enteredAbusiveWordsArr = array();
        if ($abusiveWordsArr) {
            $abusiveWordsArr = array_map("strtolower", $abusiveWordsArr);
            $textToBeCheckArr = explode(" ", $textToBeCheck);
            foreach ($textToBeCheckArr as $postedWord) {
                if (in_array(strtolower($postedWord), $abusiveWordsArr)) {
                    array_push($enteredAbusiveWordsArr, $postedWord);
                }
            }
        }

        if (!empty($enteredAbusiveWordsArr)) {
            return false;
        }
        return true;
    }
}
