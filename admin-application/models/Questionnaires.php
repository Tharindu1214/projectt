<?php
class Questionnaires extends MyAppModel
{
    const DB_TBL = 'tbl_questionnaires';
    const DB_TBL_LANG = 'tbl_questionnaires_lang';
    const DB_TBL_QUESTIONNAIRE_TO_QUESTION = 'tbl_questionnaires_to_question';
    
    const DB_TBL_QUESTIONNAIRE_TO_QUESTION_PREFIX = 'qtq_';    
    const DB_TBL_PREFIX = 'questionnaire_';    
    const DB_TBL_LANG_PREFIX = 'questionnairelang_';
    private $db;

    public function __construct($id = 0) 
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }
    
    public static function getSearchObject($langId = 0,$active = true, $deleted = true) 
    {
        $langId = FatUtility::int($langId);
        
        $srch = new SearchBase(static::DB_TBL, 'questionnaire');
        if($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG, 'LEFT OUTER JOIN',
                'questionnaire_l.'.static::DB_TBL_LANG_PREFIX.'questionnaire_id = questionnaire.'.static::tblFld('id').' and 
			questionnaire_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = '.$langId, 'questionnaire_l'
            );
        }
        
        if($active == true) {
            $srch->addCondition('questionnaire.'.static::DB_TBL_PREFIX.'active', '=', applicationConstants::active);
        }
        
        if($deleted == true) {
            $srch->addCondition('questionnaire.'.static::DB_TBL_PREFIX.'deleted', '=', 0);
        }
        return $srch;
    }
    
    public function addQuestionToQuestionnaire($data)
    {
        $record = new TableRecord(self::DB_TBL_QUESTIONNAIRE_TO_QUESTION);
        $record->assignValues($data);
        
        if(!$record->addNew(array(), $data) ) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }
}    