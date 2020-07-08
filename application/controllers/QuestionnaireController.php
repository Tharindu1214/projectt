<?php
class QuestionnaireController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function setup()
    {
        $questionnaireId = FatApp::getPostedData('qfeedback_questionnaire_id', FatUtility::VAR_INT, 0);
        $userEmail = FatApp::getPostedData('qfeedback_user_email', FatUtility::VAR_STRING);
        if ($questionnaireId <= 0 || empty($userEmail)) {
            Message::addErrorMessage(Labels::getLabel('Msg_Invalid_request', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }
        if ($record = $this->hasUserPostedFeedback($userEmail, $questionnaireId)) {
            Message::addErrorMessage(Labels::getLabel('Msg_You_have_already_posted_Feedback_to_this_questionnarie', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }
        $srch = new QuestionnairesSearch($this->siteLangId);
        $srch->addCondition('questionnaire_id', '=', $questionnaireId);
        $questionnaire = FatApp::getDb()->fetch($srch->getResultset());
        if ($questionnaire == false) {
            FatUtility::exitWithErrorCode(404);
        }

        $frm = $this->getQuestionnaireForm($questionnaireId, $this->siteLangId);
        $post = FatApp::getPostedData();
        /* $post = $frm->getFormDataFromArray(FatApp::getPostedData()); */

        if (false == $post) {
            Message::addErrorMessage($frm->getValidationErrors());
            $this->view($questionnaireId);
            return ;
        }

        $feedback = new QuestionnaireFeedback();
        if ($feedback->isQuestionnaireAnsweredFromIP($_SERVER['REMOTE_ADDR'], $post['qfeedback_user_email'])) {
            Message::addErrorMessage(Labels::getLabel('Msg_Feedback_already_posted_from_this_IP', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }
        $feedbackData = array(
        'qfeedback_questionnaire_id' => $post['qfeedback_questionnaire_id'] ,
        'qfeedback_user_name' => $post['qfeedback_user_name'] ,
        'qfeedback_user_email' => $post['qfeedback_user_email'] ,
        'qfeedback_user_ip' => $_SERVER['REMOTE_ADDR'],
        'qfeedback_lang_id' => $this->siteLangId,
        'qfeedback_added_on' => date('Y-m-d H:i:s'),
        );
        $feedback->assignValues($feedbackData);
        if (!$feedback->save()) {
            Message::addErrorMessage($feedback->getError());
            $this->view($questionnaireId);
            return ;
        }
        $feedbackId = $feedback->getMainTableRecordId();
        $srch = new QuestionnairesSearch();
        $srch->joinQuestionnarieToQuestions();
        $srch->joinQuestions($this->siteLangId);
        $srch->addCondition('questionnaire_id', '=', $questionnaireId);
        $questions = FatApp::getDb()->fetchAll($srch->getResultset());

        $isValid = true;
        foreach ($questions as $question) {
            $fieldName = 'question_'.$question['question_id'];
            if ($question['question_required'] == 1 && !isset($post[$fieldName])) {
                $isValid = false;
                Message::addErrorMessage(sprintf(Labels::getLabel('Lbl_%s_is_Mandatory', $this->siteLangId), $question['question_title']));
            }
        }
        if ($isValid == false) {
            $this->view($questionnaireId);
            return ;
        }
        foreach ($questions as $question) {
            $fieldName = 'question_'.$question['question_id'];
            if (isset($post[$fieldName])) {
                $post[$fieldName] = is_array($post[$fieldName])?serialize($post[$fieldName]):$post[$fieldName];
                $feedback->addAnswerToQuestion(array('qta_qfeedback_id'=>$feedbackId ,'qta_question_id'=>$question['question_id'] ,'qta_answers'=>$post[$fieldName]));
            }
        }
        Message::addMessage(Labels::getLabel('Msg_Feedback_sent_successfuly', $this->siteLangId));
        FatApp::redirectUser(CommonHelper::generateUrl(''));
    }

    public function view($questionnaireId)
    {
        $questionnaireId = FatUtility::int($questionnaireId);
        $srch = new QuestionnairesSearch($this->siteLangId);
        $srch->addCondition('questionnaire_id', '=', $questionnaireId);

        if (!User::isAdminLogged()) {
            $srch->addCondition('questionnaire_start_date', '<=', date('Y-m-d'));
            $srch->addCondition('questionnaire_end_date', '>=', date('Y-m-d'));
        }
        $questionnaire = FatApp::getDb()->fetch($srch->getResultset());
        if ($questionnaire == false) {
            FatUtility::exitWithErrorCode(404);
        }
        $frm = $this->getQuestionnaireForm($questionnaireId, $this->siteLangId);
        if ($post = FatApp::getPostedData()) {
            $frm->fill($post);
        }
        $this->set('questionnaire', $questionnaire);
        $this->set('frm', $frm);
        $this->_template->addCss(array('css/star-rating.css'));
        $this->_template->addJs(array('js/jquery.barrating.min.js'));
        $this->_template->render(true, true, 'questionnaire/view.php');
    }

    private function hasUserPostedFeedback($userEmail, $questionnaireId)
    {
        $srch = QuestionnaireFeedback::getSearchObject();
        $srch->addCondition('qfeedback.qfeedback_questionnaire_id', '=', $questionnaireId);
        $srch->addCondition('qfeedback.qfeedback_user_email', '=', $userEmail);
        return FatApp::getDb()->fetch($srch->getResultset());
    }

    private function getQuestionnaireForm($questionnaireId, $langId)
    {
        $frm = new Form('frmQuestionnaire');
        $frm->addHiddenField('', 'qfeedback_questionnaire_id', $questionnaireId);
        $frm->addRequiredField(Labels::getLabel('Lbl_Name', $langId), 'qfeedback_user_name');
        $frm->addEmailField(Labels::getLabel('Lbl_Email', $langId), 'qfeedback_user_email');
        $frm->addSelectBox(Labels::getLabel('Lbl_Gender', $langId), 'qfeedback_user_gender', applicationConstants::getGenderArr($langId))->requirements()->setRequired();

        $srch = new QuestionnairesSearch();
        $srch->joinQuestionnarieToQuestions();
        $srch->joinQuestions($langId);
        $srch->addCondition('questionnaire_id', '=', $questionnaireId);
        $questions = FatApp::getDb()->fetchAll($srch->getResultset());

        foreach ($questions as $question) {
            $fieldName = 'question_'.$question['question_id'];
            $question_options = preg_replace('~\r?\n~', "\n", $question['question_options']);

            $arr_options = explode("\n", $question_options);
            $arr_options = array_combine($arr_options, $arr_options);

            $arr_rating_5 = array(1=>1,2=>2,3=>3,4=>4,5=>5);
            $arr_rating_10 = array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10);
            switch ($question['question_type']) {

            case Questions::TYPE_TEXT:
                $fld = $frm->addTextBox($question['question_title'], $fieldName);
                break;

            case Questions::TYPE_SINGLE_CHOICE:
                $fld = $frm->addRadioButtons($question['question_title'], $fieldName, $arr_options, '');
                break;

            case Questions::TYPE_MULTIPLE_CHOICE:
                $fld = $frm->addCheckboxes($question['question_title'], $fieldName, $arr_options);
                break;

            case Questions::TYPE_RATING_5:
                $fld = $frm->addSelectBox($question['question_title'], $fieldName, $arr_rating_5, "", array('class'=>"star-rating"), Labels::getLabel('L_Rate', $langId));
                $fld->setWrapperAttribute('class', 'rating-f');
                break;

            case Questions::TYPE_RATING_10:
                $fld = $frm->addSelectBox($question['question_title'], $fieldName, $arr_rating_10, "", array('class'=>"star-rating"), Labels::getLabel('L_Rate', $langId));
                $fld->setWrapperAttribute('class', 'rating-f');
                break;
            }

            if ($question['question_required'] == 1) {
                /* if($question['question_type'] == Questions::TYPE_SINGLE_CHOICE || $question['question_type'] == Questions::TYPE_MULTIPLE_CHOICE){

                }
                else{ */
                $fld->requirements()->setRequired(true);
                /* } */
            }
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send', $this->siteLangId));
        return $frm;
    }
}
