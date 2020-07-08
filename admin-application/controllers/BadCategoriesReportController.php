<?php
class BadCategoriesReportController extends AdminBaseController
{
    private $canView;
    private $canEdit;
    
    public function __construct($action)
    { 
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewPerformanceReport($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditPerformanceReport($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }
    
    public function index() 
    {
        $this->objPrivilege->canViewPerformanceReport();    
        $frmSearch = $this->getSearchForm();
        $this->set('frmSearch', $frmSearch);    
        $this->_template->render();
    }
    
    public function export()
    {
        $this->search('export');
    }
    
    private function getSearchForm()
    {
        $frm = new Form('frmBadCategoriesReportSearch');
        $frm->addHiddenField('', 'page', 1);
        $frm->addSelectBox(Labels::getLabel('LBL_Records_Per_Page', $this->adminLangId), 'pagesize', array( 10 => '10', 20 => '20', 30 => '30', 50 => '50'), '', array(), '');
        $frm->addHiddenField('', 'order_by', 'ASC');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
}
?>