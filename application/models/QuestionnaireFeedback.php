<?php
class QuestionnaireFeedback extends MyAppModel
{
    const DB_TBL = 'tbl_questionnaire_feedback';
    const DB_TBL_QUESTION_TO_ANSWERS = 'tbl_question_to_answers';
    const DB_TBL_PREFIX = 'qfeedback_';

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public function addAnswerToQuestion($data)
    {
        $record = new TableRecord(self::DB_TBL_QUESTION_TO_ANSWERS);
        $record->assignValues($data);

        if (!$record->addNew(array(), $data)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'qfeedback');
        return $srch;
    }

    public static function isQuestionnaireAnsweredFromIP($ip, $email)
    {
        $srch = self::getSearchObject();
        $srch->addCondition('qfeedback.qfeedback_user_ip', '=', $ip);
        $srch->addCondition('qfeedback.qfeedback_user_email', '=', $email);
        $srch->getResultset();
        return $srch->recordCount();
    }
}
