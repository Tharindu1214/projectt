<?php
class ProductReviewsController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewProductReviews($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditProductReviews($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index($sellerId = 0)
    {
        $sellerId = FatUtility::int($sellerId);
        $this->objPrivilege->canViewProductReviews();

        $srchFrm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        if($data) {
            $data['spreview_id'] = $data['id'];
            //$data['seller_id'] = $sellerId;
            unset($data['id']);
        }else{
            $data = array('seller_id'=>$sellerId);
        }
        $srchFrm->fill($data);

        $this->set("includeEditor", true);

        $this->set("frmSearch", $srchFrm);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewProductReviews();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = ( empty($data['page']) || $data['page'] <= 0 ) ? 1 : $data['page'];
        $post = $searchForm->getFormDataFromArray($data);
        $page = (empty($page) || $page <= 0)?1:$page;
        $page = FatUtility::int($page);

        $srch = new SelProdReviewSearch($this->adminLangId);
        $srch->joinUser();
        $srch->joinSeller();
        $srch->joinShops($this->adminLangId);
        $srch->joinProducts();
        $srch->joinSellerProducts($this->adminLangId);
        $srch->joinSelProdRatingByType(SelProdRating::TYPE_PRODUCT);
        $srch->addMultipleFields(array('IFNULL(product_name,product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'selprod_id', 'usc.credential_username as seller_username','uc.credential_username as reviewed_by', 'uc.credential_user_id', 'spreview_id','spreview_posted_on','spreview_status','sprating_rating', 'shop_id', 'shop_user_id', 'IFNULL(shop_name, shop_identifier) as shop_name'));
        $srch->addOrder('spreview_posted_on', 'DESC');

        if(!empty($post['product'])) {
            $cnd = $srch->addCondition('product_name', 'like', '%'.$post['product'].'%');
            $cnd->attachCondition('product_identifier', 'like', '%'.$post['product'].'%');
        }

        if($post['reviewed_for_id'] > 0) {
            $srch->addCondition('shop_user_id', '=', $post['reviewed_for_id']);
        }

        if($post['seller_id'] > 0) {
            $srch->addCondition('spreview_seller_user_id', '=', $post['seller_id']);
        }

        if($post['spreview_id'] > 0) {
            $srch->addCondition('spreview_id', '=', $post['spreview_id']);
        }

        $spreview_status = FatApp::getPostedData('spreview_status', FatUtility::VAR_INT, -1);
        if($spreview_status > -1) {
            $srch->addCondition('spreview_status', '=', $spreview_status);
        }

        $date_from = FatApp::getPostedData('date_from', FatUtility::VAR_DATE, '');
        if (!empty($date_from) ) {
            $srch->addCondition('spreview_posted_on', '>=', $date_from. ' 00:00:00');
        }

        $date_to = FatApp::getPostedData('date_to', FatUtility::VAR_DATE, '');
        if (!empty($date_to) ) {
            $srch->addCondition('spreview_posted_on', '<=', $date_to. ' 23:59:59');
        }
        $srch->addOrder('spreview_posted_on', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'spreview_id');

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('reviewStatus', SelProdReview::getReviewStatusArr($this->adminLangId));
        $this->set('canViewUsers', $this->objPrivilege->canViewUsers($this->admin_id, true));
        $this->_template->render(false, false);
    }

    public function view($spreview_id = 0)
    {
        $spreview_id = FatUtility::int($spreview_id);
        if(1 > $spreview_id) {
            dieWithError($this->str_invalid_request);
        }

        $srch = new SelProdReviewSearch($this->adminLangId);
        $srch->joinUser();
        $srch->joinProducts();
        //$srch->joinSelProdRatingByType(SelProdRating::TYPE_PRODUCT);
        $srch->addMultipleFields(array('IFNULL(product_name,product_identifier) as product_name','uc.credential_username as reviewed_by','spreview_id','spreview_posted_on','spreview_status','spreview_title','spreview_description'));
        $srch->addOrder('spreview_posted_on', 'DESC');
        $srch->addCondition('spreview_id', '=', $spreview_id);

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetch($rs);

        $avgRatingSrch = SelProdRating::getSearchObj();
        $avgRatingSrch->addCondition('sprating_spreview_id', '=', $spreview_id);
        $avgRatingSrch->addMultipleFields(array('AVG(sprating_rating) as average_rating'));
        $avgRatingSrch->doNotCalculateRecords();
        $avgRatingSrch->doNotLimitRecords();
        $avgRatingRs = $avgRatingSrch->getResultSet();
        $avgRatingData = FatApp::getDb()->fetch($avgRatingRs);

        $ratingSrch = SelProdRating::getSearchObj();
        $ratingSrch->addCondition('sprating_spreview_id', '=', $spreview_id);
        $ratingSrch->addMultipleFields(array('sprating_spreview_id','sprating_rating_type','sprating_rating'));
        $ratingSrch->doNotCalculateRecords();
        $ratingSrch->doNotLimitRecords();

        $ratingRs = $ratingSrch->getResultSet();
        $ratingData = FatApp::getDb()->fetchAll($ratingRs);

        $frm = $this->reviewRequestForm();
        $frm->fill($records);

        $abusiveWords = Abusive::getAbusiveWords();
        $this->set("abusiveWords", $abusiveWords);
        $this->set("data", $records);
        $this->set("ratingData", $ratingData);
        $this->set("avgRatingData", $avgRatingData);
        $this->set("ratingTypeArr", SelProdRating::getRatingAspectsArr($this->adminLangId));
        $this->set("frm", $frm);
        $this->_template->render(false, false);
    }

    public function updateStatus( $spreview_id = 0 )
    {
        $spreview_id = FatApp::getPostedData('spreview_id', FatUtility::VAR_INT, 0);
        $status = FatApp::getPostedData('spreview_status', FatUtility::VAR_INT, 0);
        if(1 > $spreview_id ) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $data = SelProdReview::getAttributesById($spreview_id, array('spreview_id','spreview_status','spreview_lang_id'));
        /* if( false == $data || $data['spreview_status'] != SelProdReview::STATUS_PENDING){ */
        if(false == $data ) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $assignValues = array('spreview_status'=>$status);

        $record = new SelProdReview($spreview_id);
        $record->assignValues($assignValues);
        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $emailNotificationObj = new EmailHandler();
        $emailNotificationObj->sendBuyerReviewStatusUpdatedNotification($spreview_id, $data['spreview_lang_id']);

        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->adminLangId));
        $this->set('spreviewId', $spreview_id);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearch');
        $frm->addHiddenField('', 'reviewed_for_id');
        $frm->addHiddenField('', 'seller_id', 0);
        $frm->addHiddenField('', 'spreview_id', 0);
        $frm->addTextBox(Labels::getLabel('LBL_Product', $this->adminLangId), 'product');
        $frm->addTextBox(Labels::getLabel('LBL_Review_For', $this->adminLangId), 'reviewed_for');
        $statusArr = SelProdReview::getReviewStatusArr($this->adminLangId);
        unset($statusArr[SelProdReview::STATUS_PENDING]);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'spreview_status', array( -1 =>'Does not Matter' ) + $statusArr, '', array(), '');
        $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender' ));
        $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', '', array('readonly' => 'readonly','class' => 'small dateTimeFld field--calender'));
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick'=>'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function reviewRequestForm()
    {
        $frm = new Form('reviewRequestForm');

        $statusArr = SelProdReview::getReviewStatusArr($this->adminLangId);
        //unset($statusArr[SelProdReview::STATUS_PENDING]);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'spreview_status', $statusArr, '')->requirements()->setRequired();
        $frm->addHiddenField('', 'spreview_id', 0);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }
}
