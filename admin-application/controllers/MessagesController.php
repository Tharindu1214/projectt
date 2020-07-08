<?php
class MessagesController extends AdminBaseController
{
    private $canView;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewMessages($this->admin_id, true);
        $this->set("canView", $this->canView);
    }

    public function index()
    {
        $this->objPrivilege->canViewMessages();
        $search = $this->getSearchForm();
        $this->set("search", $search);
        $this->_template->render();
    }

    public function view($threadId = 0)
    {
        $this->objPrivilege->canViewMessages();
        $threadId = FatUtility::int($threadId);
        if (empty($threadId)) {
            Message::addErrorMessage($this->str_invalid_request);
            FatApp::redirectUser(CommonHelper::generateUrl('Messages'));
        }
        $search = $this->getMessageSearchForm();
        $search->getField('thread_id')->value = $threadId;
        $this->set("search", $search);
        $this->set("threadId", $threadId);
        $this->_template->render();
    }

    public function searchMessageThreads()
    {
        $this->objPrivilege->canViewMessages();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $srch = new MessageSearch();
        $srch->joinThreadMessage();
        $srch->joinMessagePostedFromUser();
        $srch->joinMessagePostedToUser();
        $srch->joinShops($this->adminLangId);
        $srch->joinOrderProducts($this->adminLangId);
        $srch->addMultipleFields(array('tth.*','ttm.*','tfr.user_id as message_sent_by','tfr.user_name as message_sent_by_username','tfto.user_id as message_sent_to','tfto.user_name as message_sent_to_name','tfto_c.credential_email as message_sent_to_email','tfto.user_name as message_sent_to_name'));
        if (!empty($post['thread_id'])) {
            $srch->addCondition('tth.thread_id', '=', $post['thread_id']);
        }
        $srch->addCondition('ttm.message_deleted', '=', 0);
        if (!empty($post['keyword'])) {
            $condition=$srch->addCondition('tth.thread_subject', 'like', '%'.$post['keyword'].'%');
            $condition->attachCondition('ttm.message_text', 'like', '%'.$post['keyword'].'%');
            /* $condition->attachCondition('tfr.user_name','like','%'.$post['keyword'].'%','OR');
            $condition->attachCondition('tfto.user_name','like','%'.$post['keyword'].'%','OR'); */
        }
        $date_from = FatApp::getPostedData('date_from', FatUtility::VAR_DATE, '');
        if (!empty($date_from)) {
            $srch->addCondition('ttm.message_date', '>=', $date_from. ' 00:00:00');
        }
        $date_to = FatApp::getPostedData('date_to', FatUtility::VAR_DATE, '');
        if (!empty($date_to)) {
            $srch->addCondition('ttm.message_date', '<=', $date_to. ' 23:59:59');
        }
        if (!empty($post['message_by'])) {
            $condition=$srch->addCondition('tfr.user_name', 'like', '%'.$post['message_by'].'%');
        }
        if (!empty($post['message_to'])) {
            $condition=$srch->addCondition('tfto.user_name', 'like', '%'.$post['message_to'].'%');
        }
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addOrder('ttm.message_date', 'DESC');
        $rs = $srch->getResultSet();
        $records = array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }
        /* CommonHelper::printArray($records); die; */
        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function searchMessages()
    {
        $this->objPrivilege->canViewMessages();

        $searchForm = $this->getMessageSearchForm();
        $post = $searchForm->getFormDataFromArray(FatApp::getPostedData());
        if (empty($post['thread_id'])) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $srch = new MessageSearch();
        $srch->joinThreadMessage();
        $srch->joinMessagePostedFromUser();
        $srch->joinMessagePostedToUser();
        $srch->joinShops($this->adminLangId);
        $srch->joinOrderProducts($this->adminLangId);
        $srch->addMultipleFields(array('tth.*','ttm.*','tfr.user_id as message_sent_by','tfr.user_name as message_sent_by_username','tfto.user_id as message_sent_to','tfto.user_name as message_sent_to_name','tfto_c.credential_email as message_sent_to_email','tfto.user_name as message_sent_to_name'));
        $srch->addCondition('message_deleted', '=', applicationConstants::NO);
        if (!empty($post['thread_id'])) {
            $srch->addCondition('tth.thread_id', '=', $post['thread_id']);
        }
        $rs = $srch->getResultSet();
        $records = array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set("arr_listing", $records);
        $this->_template->render(false, false);
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearch');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $frm->addTextBox(Labels::getLabel('LBL_Message_By', $this->adminLangId), 'message_by', '', array('id'=>'message_by','autocomplete'=>'off'));
        $frm->addTextBox(Labels::getLabel('LBL_Message_To', $this->adminLangId), 'message_to', '', array('id'=>'message_to','autocomplete'=>'off'));
        $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender' ));
        $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender'));
        $frm->addHiddenField('', 'thread_id');
        $fld_submit=$frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getMessageSearchForm()
    {
        $frm = new Form('frmSearch');
        $frm->addHiddenField('', 'thread_id');
        return $frm;
    }

    public function form($message_id = 0)
    {
        $this->objPrivilege->canViewMessages();

        $message_id = FatUtility::int($message_id);
        $frm = $this->getForm();

        if (0 < $message_id) {
            $data = Thread::getAttributesById($message_id, array('message_id,message_text'));
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('message_id', $message_id);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    private function getForm()
    {
        $this->objPrivilege->canViewMessages();

        $frm = new Form('frmShippingDuration');
        $frm->addHiddenField('', 'message_id');
        $fld = $frm->addTextArea(Labels::getLabel('LBL_Message_Text', $this->adminLangId), 'message_text');
        $fld->requirements()->setRequired();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function setupMessage()
    {
        $message_id = FatApp::getPostedData('message_id', null, '0');
        $frm = $this->getForm($message_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $message_id = FatUtility::int($message_id);

        $srch = new SearchBase(Thread::DB_TBL_THREAD_MESSAGES);
        $srch->addCondition('message_id', '=', $message_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('message_id'));
        $rs = $srch->getResultSet();
        $requestRow = FatApp::getDb()->fetch($rs);
        if (!$requestRow) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* Save Message[ */
        $data = array(
        'message_text'=>$post['message_text']
        );

        $tObj = new Thread();

        if (!$insertId = $tObj->updateThreadMessages($data, $message_id)) {
            Message::addErrorMessage(Labels::getLabel($tObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $this->set('msg', Labels::getLabel('MSG_Message_Submitted_Successfully!', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canViewMessages();

        $message_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($message_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $obj = new Thread($message_id);
        if (!$obj->deleteThreadMessage($message_id)) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->adminLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }

        FatUtility::dieJsonSuccess($this->str_delete_record);
    }
}
