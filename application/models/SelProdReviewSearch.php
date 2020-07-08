<?php
class SelProdReviewSearch extends SearchBase
{
    private $langId;
    private $commonLangId;
    public function __construct($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $this->langId = $langId;
        parent::__construct(SelProdReview::DB_TBL, 'spr');
    }

    public function joinSeller($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $this->commonLangId = CommonHelper::getLangId();
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'us.user_id = spr.spreview_seller_user_id', 'us');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'usc.credential_user_id = us.user_id', 'usc');
    }

    public function joinShops($langId = 0, $isActive = true)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', 'us.user_id = shop.shop_user_id', 'shop');

        if ($isActive) {
            $this->addCondition('shop.shop_active', '=', applicationConstants::ACTIVE);
        }

        if ($langId) {
            $this->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop.shop_id = s_l.shoplang_shop_id AND shoplang_lang_id = '. $langId, 's_l');
        }
    }

    public function joinUser()
    {
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = spr.spreview_postedby_user_id', 'u');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.credential_user_id = u.user_id', 'uc');
    }

    public function joinProducts($langId = 0, $isProductActive = true, $isProductApproved = true, $isProductDeleted = true)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(Product::DB_TBL, 'LEFT OUTER JOIN', 'p.product_id = spr. 	spreview_product_id', 'p');

        if ($langId > 0) {
            $this->joinTable(Product::DB_LANG_TBL, 'LEFT OUTER JOIN', 'p_l.productlang_product_id = p.product_id and p_l.productlang_lang_id = '.$langId, 'p_l');
        }

        if ($isProductActive) {
            $this->addCondition('product_active', '=', applicationConstants::ACTIVE);
        }

        if ($isProductApproved) {
            $this->addCondition('product_approved', '=', PRODUCT::APPROVED);
        }

        if ($isProductDeleted) {
            $this->addCondition('product_deleted', '=', applicationConstants::NO);
        }
    }

    public function joinSellerProducts($langId = 0, $active = true, $deleted = false)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(SellerProduct::DB_TBL, 'LEFT OUTER JOIN', 'sp.selprod_code = spr.spreview_selprod_code and sp.selprod_user_id = spr.spreview_seller_user_id', 'sp');
        if ($langId > 0) {
            $this->joinTable(SellerProduct::DB_LANG_TBL, 'LEFT OUTER JOIN', 'sp_l.selprodlang_selprod_id = sp.selprod_id and sp_l.selprodlang_lang_id = '.$langId, 'sp_l');
        }
        if ($active == true) {
            $this->addCondition('sp.selprod_active', '=', applicationConstants::YES);
        }
        if ($deleted == false) {
            $this->addCondition('sp.selprod_deleted', '=', applicationConstants::NO);
        }
    }

    public function joinSelProdRating()
    {
        $this->joinTable(SelProdRating::DB_TBL, 'LEFT OUTER JOIN', 'sprating.sprating_spreview_id = spr.spreview_id', 'sprating');
    }

    public function joinSelProdRatingByType($ratingType, $obj = 'sprt')
    {
        if (!$ratingType) {
            trigger_error(Labels::getLabel('ERR_Please_supply_rating_type_argument.', $this->commonLangId), E_USER_ERROR);
        }
        if (!is_array($ratingType)) {
            $ratingType = FatUtility::int($ratingType);
            $this->joinTable(SelProdRating::DB_TBL, 'LEFT OUTER JOIN', $obj.'.sprating_spreview_id = spr.spreview_id and '.$obj.'.sprating_rating_type = '.$ratingType, $obj);
        } else {
            if (count($ratingType)) {
                $this->joinTable(SelProdRating::DB_TBL, 'LEFT OUTER JOIN', $obj.'.sprating_spreview_id = spr.spreview_id and '.$obj.'.sprating_rating_type in ('.implode(',', $ratingType).')', $obj);
            } else {
                trigger_error(Labels::getLabel('ERR_Please_supply_non_empty_rating_types_array', $this->commonLangId), E_USER_ERROR);
            }
        }
    }

    public function joinSelProdReviewHelpful()
    {
        $this->joinTable(SelProdReviewHelpful::DB_TBL, 'LEFT OUTER JOIN', 'sprh.sprh_spreview_id = spr.spreview_id', 'sprh');
    }
}
