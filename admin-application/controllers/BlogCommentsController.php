<?php
class BlogCommentsController extends AdminBaseController
{
    private $canView;
    private $canEdit;
    public function __construct($action)
    {
        $ajaxCallArray = array('deleteRecord','search','view','updateStatus');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die(Labels::getLabel('MSG_Invalid_Action', $this->adminLangId));
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewBlogComments($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditBlogComments($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }
    public function index()
    {
        $this->objPrivilege->canViewBlogComments();

        $search = $this->getSearchForm();
        $data = FatApp::getPostedData();
        if ($data) {
            $data['bpcomment_id'] = $data['id'];
            unset($data['id']);
            $search->fill($data);
        }
        $this->set("search", $search);
        $this->_template->render();
    }
    public function search()
    {
        $this->objPrivilege->canViewBlogComments();
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $post = $searchForm->getFormDataFromArray($data);
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $srch = BlogComment::getSearchObject(true, $this->adminLangId);

        if (!empty($post['keyword'])) {
            $keywordCond =  $srch->addCondition('bpcomment_author_name', 'like', '%'.$post['keyword'].'%');
            $keywordCond->attachCondition('bpcomment_author_email', 'like', '%'.$post['keyword'].'%');
        }

        if (isset($post['bpcomment_approved']) && $post['bpcomment_approved']!='') {
            $srch->addCondition('bpcomment_approved', '=', $post['bpcomment_approved']);
        }
        if (isset($post['bpcomment_id']) && $post['bpcomment_id']!='') {
            $srch->addCondition('bpcomment_id', '=', $post['bpcomment_id']);
        }
        $srch->addMultipleFields(array('bpcomment_id','bpcomment_author_name','bpcomment_author_email','bpcomment_approved','bpcomment_added_on','post_id','ifnull(post_title,post_identifier) post_title'));
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addOrder('bpcomment_added_on', 'desc');
        $rs = $srch->getResultSet();
        $pageCount = $srch->pages();

        $records = FatApp::getDb()->fetchAll($rs);
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);

        $this->_template->render(false, false);
    }
    public function view($bpcomment_id)
    {
        $this->objPrivilege->canViewBlogComments();
        $bpcomment_id = FatUtility::int($bpcomment_id);
        if ($bpcomment_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $frm = $this->getForm($bpcomment_id);
        $srch = BlogComment::getSearchObject(true, $this->adminLangId);
        $srch->addCondition('bpcomment_id', '=', $bpcomment_id);
        $data = FatApp::getDb()->fetch($srch->getResultSet());
        if ($data === false) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        $frm->fill($data);
        $statusArr = applicationConstants::getBlogCommentStatusArr($this->adminLangId);

        $this->set('statusArr', $statusArr);
        $this->set('data', $data);
        $this->set('frm', $frm);
        $this->set('bpcomment_id', $bpcomment_id);
        $this->_template->render(false, false);
    }
    public function updateStatus()
    {
        $this->objPrivilege->canEditBlogComments();

        $bpcomment_id = FatApp::getPostedData('bpcomment_id', FatUtility::VAR_INT, 0);
        if ($bpcomment_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $frm = $this->getForm($bpcomment_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $bpcomment_id = FatUtility::int($post['bpcomment_id']);
        unset($post['bpcomment_id']);

        $oldData = BlogComment::getAttributesById($bpcomment_id);
        $record = new BlogComment($bpcomment_id);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($oldData['bpcomment_approved'] != $post['bpcomment_approved']) {
            $srch = BlogComment::getSearchObject(true, $this->adminLangId);
            $srch->addCondition('bpcomment_id', '=', $bpcomment_id);
            $newData = FatApp::getDb()->fetch($srch->getResultSet());
            $this->sendEmail($newData);
        }

        $this->set('msg', Labels::getLabel('MSG_Blog_Post_Setup_Successful', $this->adminLangId));
        $this->set('bpcommentId', $bpcomment_id);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditBlogComments();
        $bpcomment_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($bpcomment_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->markAsDeleted($bpcomment_id);

        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditBlogComments();
        $bpcommentIdsArr = FatUtility::int(FatApp::getPostedData('bpcomment_ids'));

        if (empty($bpcommentIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($bpcommentIdsArr as $bpcommentId) {
            if (1 > $bpcommentId) {
                continue;
            }
            $this->markAsDeleted($bpcommentId);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($bpcommentId)
    {
        $bpcommentId = FatUtility::int($bpcommentId);
        if (1 > $bpcommentId) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $obj = new BlogComment($bpcommentId);
        if (!$obj->canMarkRecordDelete($bpcommentId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Unauthorized_Access', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj->assignValues(array(BlogComment::tblFld('deleted') => 1));

        if (!$obj->save()) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }


    private function sendEmail($data)
    {
        if (empty($data)) {
            return false;
        }
        $emailObj = new EmailHandler();
        $emailObj->sendBlogCommentStatusChangeEmail($this->adminLangId, $data);
    }

    private function getForm($bpcomment_id = 0)
    {
        $bpcomment_id = FatUtility::int($bpcomment_id);

        $frm = new Form('frmBlogComment', array('id'=>'frmBlogComment'));
        $frm->addHiddenField('', 'bpcomment_id', $bpcomment_id);
        $statusArr = applicationConstants::getBlogCommentStatusArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Comment_Status', $this->adminLangId), 'bpcomment_approved', $statusArr);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearch', array('id'=>'frmSearch'));

        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('class'=>'search-input'));
        $statusArr = applicationConstants::getBlogCommentStatusArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Comment_Status', $this->adminLangId), 'bpcomment_approved', $statusArr, '', array(), 'Select');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'bpcomment_id');
        $fld_submit=$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
}
