<?php
class HomePageElementsController extends AdminBaseController
{
    private $canView;
    private $canEdit;    
    
    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewHomePageElements($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditHomePageElements($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);        
    }
    
    public function index()
    {
        $this->objPrivilege->canViewHomePageElements();
        $this->_template->render();
    }
}    