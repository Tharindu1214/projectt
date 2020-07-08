<?php
class AdvertisersReportController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewAdvertisersReport($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditAdvertisersReport($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index($orderDate = '')
    {
        $this->objPrivilege->canViewAdvertisersReport();

        $frmSearch = $this->getSearchForm();
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function search($type =  false)
    {
        $this->objPrivilege->canViewAdvertisersReport();
        $db = FatApp::getDb();

        $srchFrm = $this->getSearchForm();
        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        /* Wallet Balance [*/
        $srch = Transactions::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('utxn.utxn_user_id');
        $srch->addMultipleFields(array('utxn.utxn_user_id',"SUM(utxn_credit - utxn_debit) as userBalance"));
        $qryUserBalance = $srch->getQuery();
        /* ] */

        $srch = User::getSearchObject(true, false);
        $srch->joinTable('(' . $qryUserBalance . ')', 'LEFT OUTER JOIN', 'u.user_id = tqub.utxn_user_id', 'tqub');
        $srch->addMultipleFields(
            array('u.*','uc.credential_email','user_name',
            'u.user_regdate','COALESCE(tqub.userBalance,0) as totUserBalance')
        );
        $srch->addOrder('u.user_regdate', 'DESC');

        $srch->addCondition('u.user_is_advertiser', '=', '1');

        $date_from = FatApp::getPostedData('date_from', FatUtility::VAR_DATE, '');
        if (!empty($date_from)) {
            $srch->addCondition('u.user_regdate', '>=', $date_from. ' 00:00:00');
        }

        $date_to = FatApp::getPostedData('date_to', FatUtility::VAR_DATE, '');
        if (!empty($date_to)) {
            $srch->addCondition('u.user_regdate', '<=', $date_to. ' 23:59:59');
        }

        if ($type == 'export') {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $sheetData = array();
            $arr = array(Labels::getLabel('LBL_Name', $this->adminLangId),Labels::getLabel('LBL_Email', $this->adminLangId),Labels::getLabel('LBL_Reg.Date', $this->adminLangId),Labels::getLabel('LBL_Balance', $this->adminLangId) );
            array_push($sheetData, $arr);
            while ($row = $db->fetch($rs)) {
                $totUserBalance = CommonHelper::displayMoneyFormat($row['totUserBalance'], true, true);
                $arr = array($row['user_name'],$row['credential_email'], FatDate::format($row['user_regdate']), $totUserBalance);
                array_push($sheetData, $arr);
            }

            CommonHelper::convertToCsv($sheetData, str_replace("{reportgenerationdate}", date("d-M-Y"), Labels::getLabel("LBL_Advertisers_Report_{reportgenerationdate}", $this->adminLangId)).'.csv', ',');
            exit;
        } else {
            $srch->setPageNumber($page);
            $srch->setPageSize($pagesize);
            $rs = $srch->getResultSet();
            $arr_listing = $db->fetchAll($rs);

            $this->set("arr_listing", $arr_listing);
            $this->set('pageCount', $srch->pages());
            $this->set('recordCount', $srch->recordCount());
            $this->set('page', $page);
            $this->set('pageSize', $pagesize);
            $this->set('postedData', $post);
            $this->_template->render(false, false);
        }
    }

    public function export()
    {
        $this->search('export');
    }

    private function getSearchForm()
    {
        $frm = new Form('frmAdvertisersReportSearch');
        $frm->addHiddenField('', 'page', 1);
        $frm->addDateField(Labels::getLabel('LBL_Reg._Date_From', $this->adminLangId), 'date_from', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender' ));
        $frm->addDateField(Labels::getLabel('LBL_Reg._Date_To', $this->adminLangId), 'date_to', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender'));
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        $fld_submit->attachField($fld_cancel);

        return $frm;
    }
}
