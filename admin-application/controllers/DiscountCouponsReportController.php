<?php
class DiscountCouponsReportController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewDiscountCoupons($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewDiscountCoupons();
        $frmSearch = $this->getSearchForm();
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function search($type =  false)
    {
        $this->objPrivilege->canViewDiscountCoupons();
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0)?1:$post['page'];
        $page = (empty($page) || $page <= 0)?1:FatUtility::int($page);

        $srch = CouponHistory::getSearchObject();
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'user_id = couponhistory_user_id');
        $srch->joinTable(DiscountCoupons::DB_TBL, 'LEFT OUTER JOIN', 'coupon_id = couponhistory_coupon_id');
        $srch->joinTable(Credential::DB_TBL, 'LEFT OUTER JOIN', 'credential_user_id = user_id');
        $srch->addMultipleFields(array('coupon_code', 'couponhistory_id','couponhistory_coupon_id','couponhistory_order_id','couponhistory_user_id','couponhistory_amount','couponhistory_added_on','credential_username'));
        $date_from = FatApp::getPostedData('date_from', FatUtility::VAR_DATE, '');
        if (!empty($date_from)) {
            $srch->addCondition('couponhistory_added_on', '>=', $date_from. ' 00:00:00');
        }
        $date_to = FatApp::getPostedData('date_to', FatUtility::VAR_DATE, '');
        if (!empty($date_to)) {
            $srch->addCondition('couponhistory_added_on', '<=', $date_to. ' 23:59:59');
        }
        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $cond = $srch->addCondition('coupon_code', '=', $keyword);
        }

        $srch->addOrder('couponhistory_added_on', 'DESC');

        if ($type == 'export') {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $sheetData = array();
            $arr = array(Labels::getLabel('LBL_Coupon_Code', $this->adminLangId),Labels::getLabel('LBL_Order_Id', $this->adminLangId),Labels::getLabel('LBL_Customer', $this->adminLangId),Labels::getLabel('LBL_Amount', $this->adminLangId),Labels::getLabel('LBL_Date', $this->adminLangId));
            array_push($sheetData, $arr);
            while ($row = FatApp::getDb()->fetch($rs)) {
                $arr = array($row['coupon_code'], $row['couponhistory_order_id'], $row['credential_username'] ,FatApp::getConfig('conf_currency_symbol').$row['couponhistory_amount'],  FatDate::format($row['couponhistory_added_on']));
                array_push($sheetData, $arr);
            }

            CommonHelper::convertToCsv($sheetData, str_replace("{reportgenerationdate}", date("d-M-Y"), Labels::getLabel("LBL_Discount_Coupons_Report_{reportgenerationdate}", $this->adminLangId)).'.csv', ',');
            exit;
        } else {
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
    }

    public function export()
    {
        $this->search('export');
    }

    private function getSearchForm($couponDate = '')
    {
        $frm = new Form('frmDiscountCouponsReportSearch');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'couponDate', $couponDate);
        $frm->addHiddenField('', 'coupon_id');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');
        if (empty($couponDate)) {
            $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender' ));
            $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender'));
            $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
            $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
            $fld_submit->attachField($fld_cancel);
        }
        return $frm;
    }

    public function autoCompleteJson()
    {
        $this->objPrivilege->canViewDiscountCoupons();
        $coupon = new DiscountCoupons();
        $srch = $coupon->getSearchObject($this->adminLangId, false, false);

        $post = FatApp::getPostedData();
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('coupon_code', 'LIKE', '%' . $post['keyword'] . '%');
            /* $cnd->attachCondition('uc.credential_email', 'LIKE', '%' . $post['keyword'] . '%'); */
        }

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $data = $db->fetchAll($rs, 'coupon_id');
        $json = array();
        foreach ($data as $key => $value) {
            $json[] = array(
            'id' => $key,
            'code'      => strip_tags(html_entity_decode($value['coupon_code'], ENT_QUOTES, 'UTF-8')),
            );
        }
        die(json_encode($json));
    }
}
