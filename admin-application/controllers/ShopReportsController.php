<?php
class ShopReportsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        $ajaxCallArray = array('deleteRecord','search');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewShopReports($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditShopReports($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index($shopId = 0)
    {
        if ($shopId==0) {
            $shopId = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        }
        $this->objPrivilege->canViewShopReports();
        $this->set('shopId', $shopId);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewShopReports();

        $shopId = FatApp::getPostedData('shopId', FatUtility::VAR_INT, 0);

        $reportReasonObj = ShopReportReason::getSearchObject($this->adminLangId);
        $reportReasonObj->addMultipleFields(array('reportreason.*' , 'reportreason_l.reportreason_title'));
        $reportReasonObj->doNotCalculateRecords();
        $reportReasonObj->doNotLimitRecords();
        $result_report_reasons = $reportReasonObj->getQuery();

        $srch = ShopReport::getSearchObject($this->adminLangId);
        $srch->joinTable('tbl_users', 'INNER JOIN', 'u.user_id = sreport.sreport_user_id', 'u');
        $srch->joinTable('(' . $result_report_reasons . ')', 'LEFT OUTER JOIN', 'reportreason.reportreason_id = sreport.sreport_reportreason_id', 'reportreason');

        if ($shopId > 0) {
            $srch->addCondition('sreport.sreport_shop_id', '=', $shopId);
        }
        $srch->addMultipleFields(array('sreport.*' ,'u.user_name','reportreason.reportreason_title'));

        $rs = $srch->getResultSet();
        $records = array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set("arr_listing", $records);
        $this->set('recordCount', $srch->recordCount());
        $this->_template->render(false, false);
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditShopReports();

        $sreportId = FatApp::getPostedData('sreportId', FatUtility::VAR_INT, 0);
        if ($sreportId < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new ShopReport($sreportId);
        if (!$obj->deleteRecord()) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        FatUtility::dieJsonSuccess($this->str_delete_record);
    }
}
