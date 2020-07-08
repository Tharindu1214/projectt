<?php
class QuestionnairesSearch extends SearchBase
{
    const DB_QUESTION_BANKS_TBL = 'tbl_question_banks';
    const DB_QUESTION_BANKS_LANG_TBL = 'tbl_question_banks_lang';
    const DB_QUESTIONS_TBL = 'tbl_questions';
    const DB_QUESTIONS_LANG_TBL = 'tbl_questions_lang';
    const DB_QUESTIONNAIRES_TBL = 'tbl_questionnaires';
    const DB_QUESTIONNAIRES_LANG_TBL = 'tbl_questionnaires_lang';
    const DB_QUESTIONNAIRES_TO_QUESTIONS_TBL = 'tbl_questionnaires_to_question';

    const DB_QUESTIONS_TBL_PREFIX = 'question_';
    const DB_QUESTIONS_LANG_TBL_PREFIX = 'questionlang_';
    const DB_QUESTIONNAIRES_TBL_PREFIX = 'questionnaire_';
    const DB_QUESTIONNAIRES_TBL_LANG_PREFIX = 'questionnairelang_';
    const DB_QUESTIONNAIRES_TO_QUESTIONS_TBL_PREFIX = 'qtq_';

    private $db;
    private $langId;
    private $joinedQuestionnarieToQuestions;
    private $joinedFeedback;
    private $joinedFeedbackToQuestions;
    private $commonLangId;
    public function __construct($langId = 0, $active = true)
    {
        $langId = FatUtility::int($langId);
        $this->langId = $langId;
        $this->joinedQuestionnarieToQuestions = false;
        $this->commonLangId = CommonHelper::getLangId();
        parent::__construct(self::DB_QUESTIONNAIRES_TBL, 'questionnaire');

        if ($this->langId > 0) {
            $this->joinTable(
                self::DB_QUESTIONNAIRES_LANG_TBL,
                'LEFT OUTER JOIN',
                'questionnaire_l.questionnairelang_questionnaire_id = questionnaire.questionnaire_id and
			questionnaire_l.questionnairelang_lang_id = '.$this->langId,
                'questionnaire_l'
            );
        }
        if ($active) {
            $this->addCondition('questionnaire_active', '=', applicationConstants::ACTIVE);
        }
        $this->addCondition('questionnaire_deleted', '=', applicationConstants::NO);
    }

    public function joinQuestionnarieToQuestions()
    {
        $this->joinTable(self::DB_QUESTIONNAIRES_TO_QUESTIONS_TBL, 'LEFT OUTER JOIN', 'qtq.qtq_questionnaire_id = questionnaire.questionnaire_id', 'qtq');
        $this->joinedQuestionnarieToQuestions = true;
    }

    public function joinQuestions($langId = 0)
    {
        if (!$this->joinedQuestionnarieToQuestions) {
            trigger_error(Labels::getLabel('ERR_Please_join_Questionnaire_to_questions_table', $this->commonLangId), E_USER_ERROR);
        }
        $this->joinTable(self::DB_QUESTIONS_TBL, 'INNER JOIN', 'q.question_id = qtq.qtq_question_id and q.question_deleted = 0', 'q');
        if ($langId) {
            $this->joinTable(self::DB_QUESTIONS_LANG_TBL, 'LEFT OUTER JOIN', 'q.question_id = q_l.questionlang_question_id and q_l.questionlang_lang_id = '.$langId, 'q_l');
        }
        $this->joinedQuestions = true;
    }

    public function joinQuestionBanks($langId = 0)
    {
        if (!$this->joinedQuestions) {
            trigger_error(Labels::getLabel('ERR_Please_join_Question_table', $this->commonLangId), E_USER_ERROR);
        }
        $this->joinTable(self::DB_QUESTION_BANKS_TBL, 'LEFT JOIN', 'qb.qbank_id = q.question_qbank_id and qb.qbank_deleted = 0', 'qb');
        if ($langId) {
            $this->joinTable(self::DB_QUESTION_BANKS_LANG_TBL, 'LEFT OUTER JOIN', 'qb.qbank_id = qb_l.qbanklang_qbank_id and qb_l.qbanklang_lang_id = '.$langId, 'qb_l');
        }
    }

    public function joinFeedbacks()
    {
        $this->joinTable(QuestionnaireFeedback::DB_TBL, 'LEFT OUTER JOIN', 'qfeedback.qfeedback_questionnaire_id = questionnaire.questionnaire_id', 'qfeedback');
        $this->joinedFeedback = true;
    }

    public function joinFeedbackToQuestions()
    {
        if (!$this->joinedFeedback) {
            trigger_error(Labels::getLabel('ERR_Please_join_Feedback_table', $this->commonLangId), E_USER_ERROR);
        }
        $this->joinTable(QuestionnaireFeedback::DB_TBL_QUESTION_TO_ANSWERS, 'LEFT OUTER JOIN', 'qta.qta_qfeedback_id = qfeedback.qfeedback_id', 'qta');
        $this->joinedFeedbackToQuestions = true;
    }

    public function joinFeedbackQuestionsToQuestions($langId = 0)
    {
        if (!$this->joinedFeedbackToQuestions) {
            trigger_error(Labels::getLabel('ERR_Please_join_Feedback_to_questions_table.', $this->commonLangId), E_USER_ERROR);
        }
        $this->joinTable(self::DB_QUESTIONS_TBL, 'LEFT OUTER JOIN', 'qta.qta_question_id = fq.question_id', 'fq');
        if ($langId) {
            $this->joinTable(self::DB_QUESTIONS_LANG_TBL, 'LEFT OUTER JOIN', 'fq.question_id = fq_l.questionlang_question_id and fq_l.questionlang_lang_id = '.$langId, 'fq_l');
        }
    }

    public function countQuestions()
    {
        $childSrchbase = new SearchBase(static::DB_QUESTIONNAIRES_TO_QUESTIONS_TBL);
        $childSrchbase->joinTable(static::DB_QUESTIONS_TBL, 'inner join', 'question.question_id=qtq_question_id and question.question_deleted = 0', 'question');
        $childSrchbase->doNotCalculateRecords();
        $childSrchbase->doNotLimitRecords();
        $childSrchbase->addGroupBy('qtq_questionnaire_id');
        $childSrchbase->addFld('COUNT(*) AS qnCount');
        $childSrchbase->addFld('qtq_questionnaire_id');

        $this->joinTable('('.$childSrchbase->getQuery().')', 'LEFT OUTER JOIN', 's.qtq_questionnaire_id = questionnaire.questionnaire_id', 's');
    }

    public function countResponse()
    {
        $childSrchbase = new SearchBase(QuestionnaireFeedback::DB_TBL);
        $childSrchbase->doNotCalculateRecords();
        $childSrchbase->doNotLimitRecords();
        $childSrchbase->addGroupBy('qfeedback_questionnaire_id');
        $childSrchbase->addFld('COUNT(*) AS qFeedbackCount');
        $childSrchbase->addFld('qfeedback_questionnaire_id');

        $this->joinTable('('.$childSrchbase->getQuery().')', 'LEFT OUTER JOIN', 'qfeedback_rs.qfeedback_questionnaire_id = questionnaire.questionnaire_id', 'qfeedback_rs');
    }

    public function addDateFromCondition($dateFrom)
    {
        $dateFrom = FatDate::convertDatetimeToTimestamp($dateFrom);
        $dateFrom = date('Y-m-d', strtotime($dateFrom));

        if ($dateFrom != '') {
            $this->addCondition('questionnaire_start_date', '>=', $dateFrom. ' 00:00:00');
        }
    }

    public function addDateToCondition($dateTo)
    {
        $dateTo = FatDate::convertDatetimeToTimestamp($dateTo);
        $dateTo = date('Y-m-d', strtotime($dateTo));

        if ($dateTo != '') {
            $this->addCondition('questionnaire_end_date', '<=', $dateTo. ' 23:59:59');
        }
    }
}
