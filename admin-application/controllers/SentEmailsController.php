<?php
class SentEmailsController extends AdminBaseController
{
    public function __construct($action)
    {        
        parent::__construct($action);                
    }
    
    public function index() 
    {
        $frm = $this->sentEmailSearchForm();
        $this->set('srchFrm', $frm);             
        $this->_template->render();
    }
    
    public function search()
    {
        if(!FatUtility::isAjaxCall()) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        $srchFrm = $this->sentEmailSearchForm();
        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        
        $sentEmailObj = new SentEmail();
        $srch = $sentEmailObj->getSearchObject(true);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $arr_listing = FatApp::getDb()->fetchAll($rs);
        
        $this->set("arr_listing", $arr_listing);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        
        $this->_template->render(false, false);
    }
    
    function view($id)
    {
        $row_data =  SentEmail::getAttributesById($id);
        $this->set('data', $row_data);
        $this->_template->render();
    }
    
    private function sentEmailSearchForm()
    {
        $frm = new Form('sentEmailSrchForm');
        $frm->addHiddenField('', 'page');
        //$frm->addSubmitButton('', 'btn_search', 'Search');
        return $frm;
    }
}
?>