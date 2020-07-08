<?php
class QuestionBanks extends MyAppModel
{
    const DB_TBL = 'tbl_question_banks';
    const DB_TBL_LANG = 'tbl_question_banks_lang';
    
    const DB_TBL_PREFIX = 'qbank_';    
    const DB_TBL_LANG_PREFIX = 'qbanklang_';    
    private $db;

    public function __construct($id = 0) 
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }
    
    public static function getSearchObject($langId = 0,$active = true, $deleted = true) 
    {
        $langId = FatUtility::int($langId);
        
        $srch = new SearchBase(static::DB_TBL, 'qb');
        if($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG, 'LEFT OUTER JOIN',
                'qb_l.'.static::DB_TBL_LANG_PREFIX.'qbank_id = qb.'.static::tblFld('id').' and 
			qb_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = '.$langId, 'qb_l'
            );
        }
        
        if($active == true) {
            $srch->addCondition('qb.'.static::DB_TBL_PREFIX.'active', '=', applicationConstants::ACTIVE);
        }
        
        if($deleted == true) {
            $srch->addCondition('qb.'.static::DB_TBL_PREFIX.'deleted', '=', 0);
        }
        return $srch;
    }
    
    public static function getQuestionBankForSelectBox( $langId ) 
    {
        $srch = static::getSearchObject($langId);
        $srch->addMultipleFields(array('qbank_id','qbank_name'));
        return FatApp::getDb()->fetchAllAssoc($srch->getResultSet());
    }
}    