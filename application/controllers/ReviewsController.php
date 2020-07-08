<?php
class ReviewsController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function product($selprod_id = 0)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $prodSrch = new ProductSearch($this->siteLangId);
        $loggedUserId = UserAuthentication::getLoggedUserId(true);
        $prodSrch->setDefinedCriteria();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->joinProductToCategory();
        $prodSrch->doNotCalculateRecords();
        $prodSrch->doNotLimitRecords();
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $productRs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($productRs);
        if (!$product) {
            FatUtility::exitWithErrorCode(404);
        }

        $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinProducts($this->siteLangId);
        $selProdReviewObj->joinSellerProducts($this->siteLangId);
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->addCondition('sprating_rating_type', '=', SelProdRating::TYPE_PRODUCT);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addCondition('spreview_product_id', '=', $product['product_id']);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id',"ROUND(AVG(sprating_rating),2) as prod_rating","count(spreview_id) as totReviews"));
        $selProdReviewObj->addMultipleFields(array('count(spreview_postedby_user_id) totReviews','sum(if(sprating_rating=1,1,0)) rated_1','sum(if(sprating_rating=2,1,0)) rated_2','sum(if(sprating_rating=3,1,0)) rated_3','sum(if(sprating_rating=4,1,0)) rated_4','sum(if(sprating_rating=5,1,0)) rated_5'));
        $reviews = FatApp::getDb()->fetch($selProdReviewObj->getResultSet());
        $this->set('reviews', $reviews);

        $canSubmitFeedback = true;
        $orderProduct = SelProdReview::getProductOrderId($product['product_id'], $loggedUserId);
        if (!Orders::canSubmitFeedback($loggedUserId, $orderProduct['op_order_id'], $selprod_id)) {
            $canSubmitFeedback = false;
        }
        $this->set('canSubmitFeedback', $canSubmitFeedback);
        $frmReviewSearch = $this->getProductReviewSearchForm(FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG'));
        $frmReviewSearch->fill(array('selprod_id'=>$selprod_id));
        $this->set('frmReviewSearch', $frmReviewSearch);
        $this->set('product', $product);
        $this->_template->render();
    }

    public function searchForProduct()
    {
        $selprod_id = FatApp::getPostedData('selprod_id');
        $productId = SellerProduct::getAttributesById($selprod_id, 'selprod_product_id', false);

        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $orderBy = FatApp::getPostedData('orderBy', FatUtility::VAR_STRING, 'most_recent');
        $page = ($page)? $page : 1;
        $pageSize = FatApp::getPostedData('pageSize', FatUtility::VAR_INT, FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10));

        $srch = new SelProdReviewSearch();
        $srch->joinProducts($this->siteLangId);
        $srch->joinSellerProducts($this->siteLangId);
        $srch->joinSelProdRating();
        $srch->joinUser();
        $srch->joinSelProdReviewHelpful();
        $srch->addCondition('sprating_rating_type', '=', SelProdRating::TYPE_PRODUCT);
        $srch->addCondition('spr.spreview_product_id', '=', $productId);
        $srch->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $srch->addMultipleFields(array('spreview_id','spreview_selprod_id',"ROUND(AVG(sprating_rating),2) as prod_rating" ,'spreview_title','spreview_description','spreview_posted_on','spreview_postedby_user_id','user_name','group_concat(case when sprh_helpful = 1 then concat(sprh_user_id,"~",1) else concat(sprh_user_id,"~",0) end ) usersMarked' ,'sum(if(sprh_helpful = 1 , 1 ,0)) as helpful' ,'sum(if(sprh_helpful = 0 , 1 ,0)) as notHelpful','count(sprh_spreview_id) as countUsersMarked' ));
        $srch->addGroupBy('spr.spreview_id');

        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);

        switch ($orderBy) {
            case 'most_helpful':
                $srch->addOrder('helpful', 'desc');
                break;
            default:
                $srch->addOrder('spr.spreview_posted_on', 'desc');
                break;
        }
        $records = FatApp::getDb()->fetchAll($srch->getResultSet());

        $this->set('reviewsList', $records);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', FatApp::getPostedData());
        $this->set('selprod_id', $selprod_id);
        $json['startRecord'] = !empty($records)?($page-1)*$pageSize + 1 :0;

        $json['recordsToDisplay'] = count($records);
        $totalRecords = $srch->recordCount();
        $json['totalRecords'] = $totalRecords;

        if (true ===  MOBILE_APP_API_CALL) {
            $this->set('totalRecords', $totalRecords);
            $this->_template->render();
        }

        $json['html'] = $this->_template->render(false, false, 'reviews/search-for-product.php', true, false);
        $json['loadMoreBtnHtml'] = $this->_template->render(false, false, 'reviews/load-more-product-reviews-btn.php', true, false);
        FatUtility::dieJsonSuccess($json);
    }

    public function shop($shop_id = 0)
    {
        $shop_id = FatUtility::int($shop_id);

        if ($shop_id <= 0) {
            FatApp::redirectUser(CommonHelper::generateUrl('Seller', 'Shop'));
        }

        $db = FatApp::getDb();

        $srch = new ShopSearch($this->siteLangId);
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->joinSellerSubscription();
        $srch->doNotCalculateRecords();

        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

        $srch->addMultipleFields(
            array( 'shop_id','shop_user_id','shop_ltemplate_id', 'shop_created_on', 'shop_name', 'shop_description',
            'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city' )
        );
        $srch->addCondition('shop_id', '=', $shop_id);
        $shopRs = $srch->getResultSet();
        $shop = $db->fetch($shopRs);

        if (!$shop) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Home'));
        }

        $selProdRatingSrch = SelProdRating::getSearchObj();
        $selProdRatingSrch->doNotCalculateRecords();
        $selProdRatingSrch->addMultipleFields(array('sprating_spreview_id','round(avg(sprating_rating),2) seller_rating'));
        $selProdRatingSrch->addCondition('sprating_rating_type', 'in', array(SelProdRating::TYPE_SELLER_SHIPPING_QUALITY , SelProdRating::TYPE_SELLER_STOCK_AVAILABILITY , SelProdRating::TYPE_SELLER_PACKAGING_QUALITY));
        $selProdRatingSrch->addGroupBy('sprating_spreview_id');
        $spratingQuery = $selProdRatingSrch->getQuery();


        $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinProducts($this->siteLangId);
        $selProdReviewObj->joinSellerProducts($this->siteLangId);
        $selProdReviewObj->joinTable("($spratingQuery)", 'left join', 'spr.spreview_id = selRating.sprating_spreview_id', 'selRating');
        $selProdReviewObj->addGroupBy('spr.spreview_seller_user_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addCondition('spreview_seller_user_id', '=', $shop['shop_user_id']);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_seller_user_id','count(*) as totReviews',"ROUND(AVG(seller_rating),2) as avg_seller_rating",'sum(if(round(seller_rating)=1,1,0)) rated_1','sum(if(round(seller_rating)=2,1,0)) rated_2','sum(if(round(seller_rating)=3,1,0)) rated_3','sum(if(round(seller_rating)=4,1,0)) rated_4','sum(if(round(seller_rating)=5,1,0)) rated_5'));
        //echo $selProdReviewObj->getQuery();exit;
        $reviews = FatApp::getDb()->fetch($selProdReviewObj->getResultSet());
        $this->set('reviews', $reviews);

        $frmReviewSearch = $this->getProductReviewSearchForm(FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG'));
        $frmReviewSearch->fill(array('shop_id'=>$shop_id));
        $this->set('frmReviewSearch', $frmReviewSearch);
        $this->set('shop', $shop);
        $this->_template->render();
    }

    public function searchForShop()
    {
        $selprod_id = FatApp::getPostedData('shop_id', FatUtility::VAR_INT, 0);
        $sellerId = Shop::getAttributesById($selprod_id, 'shop_user_id', false);

        if ($selprod_id <= 0 || false === $sellerId) {
            $message = Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $orderBy = FatApp::getPostedData('orderBy', FatUtility::VAR_STRING, 'most_recent');
        $page = ($page)? $page : 1;
        $pageSize = FatApp::getPostedData('pageSize', FatUtility::VAR_INT, FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10));
        $selProdRatingSrch = SelProdRating::getSearchObj();
        $selProdRatingSrch->doNotCalculateRecords();
        $selProdRatingSrch->addMultipleFields(array('sprating_spreview_id','round(avg(sprating_rating),2) seller_rating'));
        $selProdRatingSrch->addCondition('sprating_rating_type', 'in', array(SelProdRating::TYPE_SELLER_SHIPPING_QUALITY , SelProdRating::TYPE_SELLER_STOCK_AVAILABILITY , SelProdRating::TYPE_SELLER_PACKAGING_QUALITY));
        $selProdRatingSrch->addGroupBy('sprating_spreview_id');
        $spratingQuery = $selProdRatingSrch->getQuery();

        $srch = new SelProdReviewSearch();
        $srch->joinProducts($this->siteLangId);
        $srch->joinSellerProducts($this->siteLangId);
        $srch->joinTable("($spratingQuery)", 'left join', 'spr.spreview_id = selRating.sprating_spreview_id', 'selRating');
        $srch->joinUser();
        $srch->joinSelProdReviewHelpful();

        $srch->addCondition('spr.spreview_seller_user_id', '=', $sellerId);
        $srch->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $srch->addMultipleFields(array('selprod_id','IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title','spreview_id','spreview_seller_user_id',"ROUND(AVG(seller_rating),2) as shop_rating" ,'spreview_title','spreview_description','spreview_posted_on','spreview_postedby_user_id','user_name','group_concat(case when sprh_helpful = 1 then concat(sprh_user_id,"~",1) else concat(sprh_user_id,"~",0) end ) usersMarked' ,'sum(if(sprh_helpful = 1 , 1 ,0)) as helpful' ,'sum(if(sprh_helpful = 0 , 1 ,0)) as notHelpful','count(sprh_spreview_id) as countUsersMarked' ));
        $srch->addGroupBy('spr.spreview_id');

        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);

        switch ($orderBy) {
            case 'most_helpful':
                $srch->addOrder('helpful', 'desc');
                break;
            default:
                $srch->addOrder('spr.spreview_posted_on', 'desc');
                break;
        }
        $records = FatApp::getDb()->fetchAll($srch->getResultSet());
        $this->set('reviewsList', $records);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', FatApp::getPostedData());
        $startRecord = !empty($records)?($page-1)*$pageSize + 1 :0;

        $recordCount = $srch->recordCount();
        if (true ===  MOBILE_APP_API_CALL) {
            $this->set('startRecord', $startRecord);
            $this->set('totalRecords', $recordCount);
            $this->_template->render();
        }

        $json['startRecord'] = $startRecord;

        $json['recordsToDisplay'] = count($records);
        $json['totalRecords'] = $recordCount;

        $json['html'] = $this->_template->render(false, false, 'reviews/search-for-shop.php', true, false);
        $json['loadMoreBtnHtml'] = $this->_template->render(false, false, 'reviews/load-more-shop-reviews-btn.php', true, false);
        FatUtility::dieJsonSuccess($json);
    }

    public function productPermalink($selprod_id, $reviewId)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $reviewId = FatUtility::int($reviewId);

        $prodSrch = new ProductSearch($this->siteLangId);

        $prodSrch->setDefinedCriteria();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->joinProductToCategory();
        $prodSrch->doNotCalculateRecords();
        $prodSrch->doNotLimitRecords();
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $productRs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($productRs);

        if (!$product) {
            FatUtility::exitWithErrorCode(404);
        }

        $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinProducts($this->siteLangId);
        $selProdReviewObj->joinSellerProducts($this->siteLangId);
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->addCondition('sprating_rating_type', '=', SelProdRating::TYPE_PRODUCT);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addCondition('spreview_product_id', '=', $product['product_id']);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id',"ROUND(AVG(sprating_rating),2) as prod_rating"));

        $selProdReviewObj2 = clone $selProdReviewObj;

        $selProdReviewObj->addMultipleFields(array("count(spreview_id) as totReviews",'count(spreview_postedby_user_id) totReviews','sum(if(sprating_rating=1,1,0)) rated_1','sum(if(sprating_rating=2,1,0)) rated_2','sum(if(sprating_rating=3,1,0)) rated_3','sum(if(sprating_rating=4,1,0)) rated_4','sum(if(sprating_rating=5,1,0)) rated_5'));

        $reviews = FatApp::getDb()->fetch($selProdReviewObj->getResultSet());
        $this->set('reviews', $reviews);

        $selProdReviewObj2->joinUser();
        $selProdReviewObj2->addMultipleFields(array('u.*','spr.*'));

        $selProdReviewObj2->addCondition('spr.spreview_id', '=', $reviewId);
        $reviewData = FatApp::getDb()->fetch($selProdReviewObj2->getResultSet());

        $srch = new SelProdReviewSearch();
        $srch->joinProducts($this->siteLangId);
        $srch->joinSellerProducts($this->siteLangId);
        $srch->joinSelProdRating();
        $srch->joinUser();
        $srch->joinSelProdReviewHelpful();

        $srch->addCondition('sprating_rating_type', '=', SelProdRating::TYPE_PRODUCT);

        $srch->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $srch->addMultipleFields(array('spreview_id','spreview_selprod_id',"ROUND(AVG(sprating_rating),2) as prod_rating" ,'spreview_title','spreview_description','spreview_posted_on','spreview_postedby_user_id','user_name','group_concat(case when sprh_helpful = 1 then concat(sprh_user_id,"~",1) else concat(sprh_user_id,"~",0) end ) usersMarked' ,'sum(if(sprh_helpful = 1 , 1 ,0)) as helpful' ,'sum(if(sprh_helpful = 0 , 1 ,0)) as notHelpful','count(sprh_spreview_id) as countUsersMarked' ));
        $srch->addCondition('spr.spreview_id', '=', $reviewId);

        $reviewHelpfulData = FatApp::getDb()->fetch($srch->getResultSet());

        $this->set('reviewHelpfulData', $reviewHelpfulData);
        $this->set('product', $product);
        $this->set('reviewData', $reviewData);
        $this->_template->render();
    }

    public function shopPermalink($sellerId, $reviewId)
    {
        $sellerId = FatUtility::int($sellerId);
        $reviewId = FatUtility::int($reviewId);

        if ($sellerId <= 0) {
            Message::addErrorMessage(Labels::getLabel('Msg_Invalid_Request', $siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $db = FatApp::getDb();

        $srch = new ShopSearch($this->siteLangId);
        $srch->joinSellerSubscription();
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->doNotCalculateRecords();

        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

        $srch->addMultipleFields(
            array( 'shop_id','shop_user_id','shop_ltemplate_id', 'shop_created_on', 'shop_name', 'shop_description',
            'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city' )
        );
        $srch->addCondition('shop_user_id', '=', $sellerId);
        $shopRs = $srch->getResultSet();
        $shop = $db->fetch($shopRs);

        if (!$shop) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('Home'));
        }


        $selProdRatingSrch = SelProdRating::getSearchObj();
        $selProdRatingSrch->doNotCalculateRecords();
        $selProdRatingSrch->addMultipleFields(array('sprating_spreview_id','round(avg(sprating_rating),2) seller_rating'));
        $selProdRatingSrch->addCondition('sprating_rating_type', 'in', array(SelProdRating::TYPE_SELLER_SHIPPING_QUALITY , SelProdRating::TYPE_SELLER_STOCK_AVAILABILITY , SelProdRating::TYPE_SELLER_PACKAGING_QUALITY));
        $selProdRatingSrch->addGroupBy('sprating_spreview_id');
        $spratingQuery = $selProdRatingSrch->getQuery();



        $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinProducts($this->siteLangId);
        $selProdReviewObj->joinSellerProducts($this->siteLangId);
        $selProdReviewObj->joinTable("($spratingQuery)", 'left join', 'spr.spreview_id = selRating.sprating_spreview_id', 'selRating');

        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_seller_user_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addCondition('spreview_seller_user_id', '=', $shop['shop_user_id']);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_seller_user_id',"ROUND(AVG(seller_rating),2) as shop_rating"));

        $selProdReviewObj2 = clone $selProdReviewObj;

        $selProdReviewObj->addMultipleFields(array("count(spreview_id) as totReviews",'count(spreview_postedby_user_id) totReviews','sum(if(round(seller_rating)=1,1,0)) rated_1','sum(if(round(seller_rating)=2,1,0)) rated_2','sum(if(round(seller_rating)=3,1,0)) rated_3','sum(if(round(seller_rating)=4,1,0)) rated_4','sum(if(round(seller_rating)=5,1,0)) rated_5'));

        $reviews = FatApp::getDb()->fetch($selProdReviewObj->getResultSet());
        $this->set('reviews', $reviews);

        $selProdReviewObj2->joinUser();
        $selProdReviewObj2->addMultipleFields(array('u.*','spr.*'));

        $selProdReviewObj2->addCondition('spr.spreview_id', '=', $reviewId);
        if (!$reviewData = FatApp::getDb()->fetch($selProdReviewObj2->getResultSet())) {
            Message::addErrorMessage(Labels::getLabel('Msg_Invalid_Request', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $srch = new SelProdReviewSearch();
        $srch->joinProducts($this->siteLangId);
        $srch->joinSellerProducts($this->siteLangId);
        $srch->joinTable("($spratingQuery)", 'left join', 'spr.spreview_id = selRating.sprating_spreview_id', 'selRating');
        $srch->joinUser();
        $srch->joinSelProdReviewHelpful();

        $srch->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $srch->addMultipleFields(array('spreview_id','spreview_seller_user_id',"ROUND(AVG(seller_rating),2) as shop_rating" ,'spreview_title','spreview_description','spreview_posted_on','spreview_postedby_user_id','user_name','group_concat(case when sprh_helpful = 1 then concat(sprh_user_id,"~",1) else concat(sprh_user_id,"~",0) end ) usersMarked' ,'sum(if(sprh_helpful = 1 , 1 ,0)) as helpful' ,'sum(if(sprh_helpful = 0 , 1 ,0)) as notHelpful','count(sprh_spreview_id) as countUsersMarked' ));
        $srch->addCondition('spr.spreview_id', '=', $reviewId);

        $reviewHelpfulData = FatApp::getDb()->fetch($srch->getResultSet());

        $this->set('reviewHelpfulData', $reviewHelpfulData);
        $this->set('shop', $shop);
        $this->set('reviewData', $reviewData);
        $this->_template->render();
    }

    public function markHelpful()
    {
        $reviewId = FatApp::getPostedData('reviewId', FatUtility::VAR_INT, 0);
        $isHelpful = FatApp::getPostedData('isHelpful', FatUtility::VAR_INT, 0);
        if ($reviewId <= 0) {
            $message = Labels::getLabel('Msg_Invalid_Request', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        $userId = UserAuthentication::getLoggedUserId();
        $tblRecObj = new SelProdReviewHelpful();
        $tblRecObj->assignValues(array('sprh_spreview_id'=>$reviewId , 'sprh_user_id'=>$userId, 'sprh_helpful'=>$isHelpful));
        if (!$tblRecObj->addNew(array(), array('sprh_helpful'=>$isHelpful))) {
            if (true ===  MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($tblRecObj->getError());
            }
            Message::addErrorMessage($tblRecObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $tblRecObj = new SelProdReviewHelpful($reviewId);
        $success['msg'] = Labels::getLabel('Msg_Successfully_Updated', $this->siteLangId);
        $success['data'] = $tblRecObj->getData();

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        FatUtility::dieJsonSuccess($success);
    }

    public function write($product_id)
    {
        $product_id = FatUtility::int($product_id);
        if (!$product_id) {
            FatUtility::exitWithErrorCode(404);
        }
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }
        $orderProduct = SelProdReview::getProductOrderId($product_id, $loggedUserId);
        if (empty($orderProduct)) {
            Message::addErrorMessage(Labels::getLabel('Msg_Review_can_be_posted_on_bought_product', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }
        $opId = $orderProduct['op_id'];
        FatApp::redirectUser(CommonHelper::generateUrl('Buyer', 'orderFeedback', array($opId)));
    }

    public function reviewAbuse($reviewId)
    {
        $this->set('frm', $this->getReviewAbuseForm($reviewId));
        $this->_template->render(false, false);
    }

    public function setupReviewAbuse()
    {
        $post = FatApp::getPostedData();
        $reviewId = FatUtility::int($post['spra_spreview_id']);
        if ($reviewId <= 0) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        $frm = $this->getReviewAbuseForm($reviewId);
        $post = $frm->getFormDataFromArray($post);

        $data = array(
        'spra_comments'=>$post['spra_comments'] ,
        'spra_spreview_id'=>$post['spra_spreview_id'] ,
        'spra_user_id'=>UserAuthentication::getLoggedUserId(),
        );
        $obj = new SelProdReview();
        if (!$obj->addSelProdReviewAbuse($data, $data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('reviewId', $reviewId);
        $this->set('msg', Labels::getLabel('MSG_Setup_Successfull', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getProductReviewSearchForm($pageSize = 10)
    {
        $frm = new Form('frmReviewSearch');
        $frm->addHiddenField('', 'selprod_id');
        $frm->addHiddenField('', 'shop_id');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'pageSize', $pageSize);
        $frm->addHiddenField('', 'orderBy', 'most_recent');
        return $frm;
    }

    private function getReviewAbuseForm($reviewId)
    {
        $frm = new Form('frmReviewAbuse');
        $frm->addHiddenField('', 'spra_spreview_id', $reviewId);
        $frm->addTextarea(Labels::getLabel('Lbl_Comments', $this->siteLangId), 'spra_comments');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('Lbl_Report_Abuse', $this->siteLangId));
        return $frm;
    }
}
