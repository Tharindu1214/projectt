<?php
class QuestionsController extends AdminBaseController
{
    private $canView;
    private $canEdit;    
    
    public function __construct($action)
    {
        $ajaxCallArray = array('deleteRecord','form','langForm','search','setup','langSetup');
        if(!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        } 
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewQuestions($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditQuestions($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);        
    }
    
    public function index($qbank_id = 0)
    {        
        $this->objPrivilege->canViewQuestions();
        $qbank_id = FatUtility::int($qbank_id);
        
        $frmSearch = $this->getSearchForm();                    
        $this->set("frmSearch", $frmSearch);    
        $this->_template->render();
    }
    
    public function search()
    {
        $this->objPrivilege->canViewQuestions();
        
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);                
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);
        
        $srch = Questions::getSearchObject($this->adminLangId, false);
        
        $srch->addOrder('q_l.' . QuestionBanks::DB_TBL_PREFIX . 'title', 'ASC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        
        $qbank_id = FatUtility::int($post['qbank_id']);
        $srch->addCondition('question_qbank_id', '=', $qbank_id);
        if(!empty($post['keyword'])) {
            $cond = $srch->addCondition('q_l.question_title', 'like', '%'.$post['keyword'].'%');
            $cond->attachCondition('q.question_identifier', 'like', '%'.$post['keyword'].'%');
        }
        
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'question_id');    
        
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);                        
        $this->_template->render(false, false);
    }
    
}    