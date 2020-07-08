<?php
class SmartRecomendedWeightagesController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewRecomendedWeightages($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditRecomendedWeightages($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewRecomendedWeightages();
        $searchFrm = $this->getSearchForm();
        $this->set("searchFrm", $searchFrm);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewRecomendedWeightages();
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0)?1:$data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $obj = new SmartWeightageSettings();
        $srch = $obj->getSearchObject();

        if (!empty($post['keyword'])) {
            $srch->addCondition('sws.swsetting_name', 'like', '%'.$post['keyword'].'%');
        }

        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function update($swsetting_key = 0)
    {
        $this->objPrivilege->canEditRecomendedWeightages();

        $swsetting_key = FatUtility::int($swsetting_key);
        if (1 > $swsetting_key) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $weightage = FatApp::getPostedData('weightage', FatUtility::VAR_FLOAT, 0);


        $weightageKeyArr = SmartWeightageSettings::getWeightageKeyArr();
        if (!array_key_exists($swsetting_key, $weightageKeyArr)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new SmartWeightageSettings($swsetting_key);

        $obj->assignValues(
            array(
            SmartWeightageSettings::tblFld('weightage') => $weightage,
            SmartWeightageSettings::tblFld('name')=>$weightageKeyArr[$swsetting_key])
        );
        if (!$obj->save()) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearch');
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
}
