<?php
class AffiliatesReportController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewAffiliatesReport($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditAffiliatesReport($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index($orderDate = '')
    {
        $this->objPrivilege->canViewAffiliatesReport();

        $frmSearch = $this->getSearchForm();
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function search($type = false)
    {
        $this->objPrivilege->canViewAffiliatesReport();
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

        /* Affiliate Total Revenue Earned So far[ */
        $srch = Transactions::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('utxn.utxn_user_id');
        $cnd = $srch->addCondition('utxn_type', '=', Transactions::TYPE_AFFILIATE_REFERRAL_SIGN_UP);
        $cnd->attachCondition('utxn_type', '=', Transactions::TYPE_AFFILIATE_REFERRAL_ORDER);
        $srch->addMultipleFields(array( 'utxn.utxn_user_id', 'SUM(utxn_credit) as userRevenue' ));
        $qryUserRevenue = $srch->getQuery();
        /* ] */

        /* Affiliate SignUp Revenue[ */
        $srch = Transactions::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('utxn.utxn_user_id');
        $cnd = $srch->addCondition('utxn_type', '=', Transactions::TYPE_AFFILIATE_REFERRAL_SIGN_UP);
        $srch->addMultipleFields(array( 'utxn.utxn_user_id', 'SUM(utxn_credit) as userSignUpRevenue' ));
        $qryUserSignUpRevenue = $srch->getQuery();
        /* ] */

        /* Affiliate Referrals Order Revenue[ */
        $srch = Transactions::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('utxn.utxn_user_id');
        $cnd = $srch->addCondition('utxn_type', '=', Transactions::TYPE_AFFILIATE_REFERRAL_ORDER);
        $srch->addMultipleFields(array( 'utxn.utxn_user_id', 'SUM(utxn_credit) as userOrderRevenue' ));
        $qryUserOrderRevenue = $srch->getQuery();
        /* ] */

        /* Referred users counts[ */
        $srch = User::getSearchObject(true, false);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('u.user_affiliate_referrer_user_id');
        $srch->addMultipleFields(array('user_affiliate_referrer_user_id', 'COUNT(user_id) as totAffiliatedUsers'));
        $qryUserReffered = $srch->getQuery();
        /* ] */

        $srch = User::getSearchObject(true, false);
        $srch->joinTable('(' . $qryUserBalance . ')', 'LEFT OUTER JOIN', 'u.user_id = tqub.utxn_user_id', 'tqub');
        $srch->joinTable('(' . $qryUserRevenue . ')', 'LEFT OUTER JOIN', 'u.user_id = tqur.utxn_user_id', 'tqur');
        $srch->joinTable('(' . $qryUserSignUpRevenue . ')', 'LEFT OUTER JOIN', 'u.user_id = tqusr.utxn_user_id', 'tqusr');
        $srch->joinTable('(' . $qryUserOrderRevenue . ')', 'LEFT OUTER JOIN', 'u.user_id = tquor.utxn_user_id', 'tquor');
        $srch->joinTable('(' . $qryUserReffered . ')', 'LEFT OUTER JOIN', 'u.user_id = tqureferred.user_affiliate_referrer_user_id', 'tqureferred');
        $srch->addMultipleFields(
            array('u.*','uc.credential_email','user_name',
            'u.user_regdate','COALESCE(tqub.userBalance,0) as totUserBalance',
            'COALESCE( tqur.userRevenue, 0 ) as totRevenue',
            'COALESCE( tqusr.userSignUpRevenue, 0 ) as totSignUpRevenue',
            'COALESCE( tquor.userOrderRevenue, 0 ) as totOrderRevenue',
            'COALESCE( tqureferred.totAffiliatedUsers, 0 ) as totSignUps')
        );
        $srch->addOrder('u.user_regdate', 'DESC');

        $srch->addCondition('u.user_is_affiliate', '=', '1');

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
            $arr = array(Labels::getLabel('LBL_Name', $this->adminLangId),Labels::getLabel('LBL_Email', $this->adminLangId),Labels::getLabel('LBL_Reg.Date', $this->adminLangId),Labels::getLabel('LBL_Balance', $this->adminLangId), Labels::getLabel('LBL_Revenue', $this->adminLangId), Labels::getLabel('LBL_SignUp_Revenue', $this->adminLangId), Labels::getLabel('LBL_Order_Revenue', $this->adminLangId) );
            array_push($sheetData, $arr);
            while ($row = $db->fetch($rs)) {
                $totUserBalance = CommonHelper::displayMoneyFormat($row['totUserBalance'], true, true);
                $totRevenue = CommonHelper::displayMoneyFormat($row['totRevenue'], true, true);
                $totSignUpRevenue = CommonHelper::displayMoneyFormat($row['totSignUpRevenue'], true, true);
                $totOrderRevenue = CommonHelper::displayMoneyFormat($row['totOrderRevenue'], true, true);
                $arr = array($row['user_name'],$row['credential_email'], FatDate::format($row['user_regdate']), $totUserBalance, $totRevenue, $totSignUpRevenue, $totOrderRevenue );
                array_push($sheetData, $arr);
            }

            CommonHelper::convertToCsv($sheetData, str_replace("{reportgenerationdate}", date("d-M-Y"), Labels::getLabel("LBL_Affiliates_Report_{reportgenerationdate}", $this->adminLangId)).'.csv', ',');
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
        $frm = new Form('frmAffiliatesReportSearch');
        $frm->addHiddenField('', 'page', 1);
        $frm->addDateField(Labels::getLabel('LBL_Reg._Date_From', $this->adminLangId), 'date_from', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender' ));
        $frm->addDateField(Labels::getLabel('LBL_Reg._Date_To', $this->adminLangId), 'date_to', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender'));
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        $fld_submit->attachField($fld_cancel);

        return $frm;
    }
}
