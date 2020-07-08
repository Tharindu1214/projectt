<?php
class SelProdReview extends MyAppModel
{
    const DB_TBL = 'tbl_seller_product_reviews';
    const DB_TBL_PREFIX = 'spreview_';

    const DB_TBL_ABUSE = 'tbl_seller_product_reviews_abuse';
    const DB_TBL_ABUSE_PREFIX = 'spra_';

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_CANCELLED = 2;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public function addSelProdReviewAbuse($data = array(), $onDuplicateUpdateData = array())
    {
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL_ABUSE, $data, false, array(), $onDuplicateUpdateData)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public static function getReviewStatusArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }
        $arr = array(
        static::STATUS_PENDING => Labels::getLabel('LBL_Pending', $langId),
        static::STATUS_APPROVED => Labels::getLabel('LBL_Approved', $langId),
        static::STATUS_CANCELLED => Labels::getLabel('LBL_Cancelled', $langId),
        );
        return $arr;
    }

    public static function getBuyerAllowedOrderReviewStatuses()
    {
        $buyerAllowReviewStatuses = unserialize(FatApp::getConfig("CONF_REVIEW_READY_ORDER_STATUS"));
        return $buyerAllowReviewStatuses;
    }

    public static function getSellerTotalReviews($userId)
    {
        $userId = FatUtility::int($userId);

        $srch = new SelProdReviewSearch();
        $srch->joinUser();
        $srch->joinSeller();
        $srch->joinSellerProducts();
        $srch->joinProducts();
        $srch->joinSelProdRatingByType(SelProdRating::TYPE_PRODUCT);
        $srch->addMultipleFields(array('count(*) as numOfReviews'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('spreview_seller_user_id', '=', $userId);
        $srch->addGroupby('spreview_seller_user_id');
        $srch->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);

        $rs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($rs);
        if ($record == false) {
            return 0;
        }
        return $record['numOfReviews'];
    }

    public static function getProductOrderId($product_id, $loggedUserId)
    {
        $selProdSrch = SellerProduct::getSearchObject(0);
        $selProdSrch->addCondition('selprod_product_id', '= ', $product_id);
        $selProdSrch->addCondition('selprod_active', '= ', applicationConstants::ACTIVE);
        $selProdSrch->addCondition('selprod_deleted', '= ', applicationConstants::NO);
        $selProdSrch->addMultipleFields(array('selprod_id'));
        $rs = $selProdSrch->getResultSet();
        $selprodListing = FatApp::getDb()->fetchAll($rs);
        $selProdList = array();
        foreach ($selprodListing as $key => $val) {
            $selProdList[$key] = $val['selprod_id'];
        }
        $srch = new OrderProductSearch(0, true);
        $allowedReviewStatus = implode(",", SelProdReview::getBuyerAllowedOrderReviewStatuses());
        $allowedSelProdId = implode(",", $selProdList);
        $srch->addDirectCondition('order_user_id ='.$loggedUserId.' and ( FIND_IN_SET(op_selprod_id,(\''.$allowedSelProdId.'\')) and op_is_batch = 0) and  FIND_IN_SET(op_status_id,(\''.$allowedReviewStatus.'\')) ');
        /* $srch->addOrder('order_date_added'); */
        $orderProduct = FatApp::getDb()->fetch($srch->getResultSet());
        return $orderProduct;
    }
}
