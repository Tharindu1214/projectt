<?php
class SelProdRating extends MyAppModel
{
    const DB_TBL = 'tbl_seller_product_rating';
    const DB_TBL_PREFIX = '	sprating_';

    const TYPE_PRODUCT = 1;
    const TYPE_SELLER_SHIPPING_QUALITY = 2;
    const TYPE_SELLER_STOCK_AVAILABILITY = 3;
    const TYPE_SELLER_PACKAGING_QUALITY = 4;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObj()
    {
        // return $srch = new SearchBase(static::DB_TBL, 'spr');
        return $srch = new SearchBase(static::DB_TBL, 'sprating');
    }

    public static function getRatingAspectsArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
        static::TYPE_PRODUCT=> Labels::getLabel('LBL_Product', $langId),
        static::TYPE_SELLER_SHIPPING_QUALITY => Labels::getLabel('LBL_Rating_Type_Shipping', $langId),
        static::TYPE_SELLER_STOCK_AVAILABILITY => Labels::getLabel('LBL_Rating_Type_Stock_availabiity', $langId),
        static::TYPE_SELLER_PACKAGING_QUALITY => Labels::getLabel('LBL_Rating_Type_Package_Quality', $langId),
        );
    }

    public static function getSellerRating($userId)
    {
        $userId = FatUtility::int($userId);
        $srch = new SelProdReviewSearch();
        $srch->joinSeller();
        $srch->joinSellerProducts();
        $srch->joinSelProdRating();
        $srch->addMultipleFields(array("ROUND(AVG(sprating_rating),2) as avg_rating"));
        $srch->addCondition('sprating_rating_type', 'in', array(SelProdRating::TYPE_SELLER_SHIPPING_QUALITY , SelProdRating::TYPE_SELLER_STOCK_AVAILABILITY , SelProdRating::TYPE_SELLER_PACKAGING_QUALITY));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('spreview_seller_user_id', '=', $userId);
        $srch->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $srch->addGroupby('spreview_seller_user_id');
        $rs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($rs);
        if ($record == false) {
            return 0;
        }
        return $record['avg_rating'];
    }
}
