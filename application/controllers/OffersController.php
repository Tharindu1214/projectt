<?php
class OffersController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        $frm = $this->getSearchForm($this->siteLangId);
        $this->set('frm', $frm);
        $this->_template->render();
    }

    public function search()
    {
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $currDate = date('Y-m-d');

        $srch = DiscountCoupons::getSearchObject($this->siteLangId);
        $cnd = $srch->addCondition('coupon_start_date', '=', '0000-00-00', 'AND');
        $cnd->attachCondition('coupon_start_date', '<=', $currDate, 'OR');

        $cnd1 = $srch->addCondition('coupon_end_date', '=', '0000-00-00', 'AND');
        $cnd1->attachCondition('coupon_end_date', '>=', $currDate, 'OR');

        $srch->addOrder('coupon_active', 'DESC');
        $srch->addOrder('coupon_end_date', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'coupon_id');

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);

        $startRecord = ($page-1)* $pagesize + 1 ;
        $endRecord = $pagesize;
        $totalRecords = $srch->recordCount();
        if ($totalRecords < $endRecord) {
            $endRecord = $totalRecords;
        }
        $json['totalRecords'] = $totalRecords;
        $json['startRecord'] = $startRecord;
        $json['endRecord'] = $endRecord;
        $json['html'] = $this->_template->render(false, false, 'offers/search.php', true, false);
        $json['loadMoreBtnHtml'] = $this->_template->render(false, false, '_partial/load-more-btn.php', true, false);
        FatUtility::dieJsonSuccess($json);
    }

    private function getSearchForm($langId = 0)
    {
        $frm = new Form('frmCouponSrch');
        return $frm;
    }
}
