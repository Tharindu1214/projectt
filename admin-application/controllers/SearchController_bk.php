<?php
class SearchController extends AdminBaseController
{
    public function __construct($action) 
    {
        AdminPrivilege::canViewUsers();
        parent::__construct($action);
    }
    
    public function index()
    {
        
    }
    
    public function getSearchForm()
    {
        $frm = new Form('search_form');
        $frm->addTextBox(Labels::getLabel('LBL_Name_or_Email_ID:', $this->adminLangId), 'name');
        $frm->addSelectBox(Labels::getLabel('LBL_Active', $this->adminLangId), 'user_active', array(-1=>'Does not Matter', 0=>'No', 1=>'Yes'), -1, array());
        $frm->addSelectBox(Labels::getLabel('LBL_Verified', $this->adminLangId), 'user_verified', array(-1=>'Does not Matter', 0=>'No', 1=>'Yes'), -1, array());
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        return $frm;
    }
}
