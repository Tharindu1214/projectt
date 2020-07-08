<?php
class UserWishListProductSearch extends SearchBase
{
    private $langId;
    private $sellerProductsJoined;
    private $productsJoined;
    private $sellerUserJoined;
    private $commonLangId;
    private $joinSellerOrder ;

    public function __construct($langId = 0)
    {
        parent::__construct(UserWishListProducts::DB_TBL, 'uwlp');
        $this->langId = FatUtility::int($langId);
        $this->sellerProductsJoined = false;
        $this->productsJoined = false;
        $this->commonLangId = CommonHelper::getLangId();
    }

    public function joinWishLists()
    {
        $this->joinTable(UserWishList::DB_TBL, 'INNER JOIN', 'uwl.uwlist_id = uwlp.uwlp_uwlist_id', 'uwl');
    }

    public function joinFavouriteProducts($user_id)
    {
        $this->joinTable(Product::DB_TBL_PRODUCT_FAVORITE, 'LEFT OUTER JOIN', 'ufp.ufp_selprod_id = selprod_id and ufp.ufp_user_id = '.$user_id, 'ufp');
    }

    public function joinSellerProducts($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'uwlp.uwlp_selprod_id = sp.selprod_id', 'sp');

        if ($langId) {
            $this->joinTable(SellerProduct::DB_LANG_TBL, 'LEFT OUTER JOIN', 'sp.selprod_id = sp_l.selprodlang_selprod_id AND sp_l.selprodlang_lang_id = '.$langId, 'sp_l');
        }
        $this->sellerProductsJoined = true;
    }

    public function joinSellerProductSpecialPrice($forDate = '')
    {
        if (!$this->sellerProductsJoined) {
            trigger_error(Labels::getLabel('MSG_joinSellerProductSpecialPrice_can_be_joined_only_if,_joinSellerProducts_is_joined.', $this->commonLangId), E_USER_ERROR);
        }
        if ('' == $forDate) {
            $forDate = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');
            ;
        }
        $this->joinTable(
            SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE,
            'LEFT OUTER JOIN',
            'splprice_selprod_id = sp.selprod_id AND \'' . $forDate . '\' BETWEEN splprice_start_date AND splprice_end_date'
        );
    }

    public function joinProducts($langId = 0, $isProductActive = true, $isProductApproved = true, $isProductDeleted = true)
    {
        if (!$this->sellerProductsJoined) {
            trigger_error(Labels::getLabel('MSG_joinProducts_can_be_joined_only_if,_joinSellerProducts_is_joined.', $this->commonLangId), E_USER_ERROR);
        }
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(Product::DB_TBL, 'INNER JOIN', 'sp.selprod_product_id = p.product_id', 'p');

        if ($langId) {
            $this->joinTable(Product::DB_LANG_TBL, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = '.$langId, 'p_l');
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

        $this->productsJoined = true;
    }

    public function joinBrands($langId = 0)
    {
        if (!$this->productsJoined) {
            trigger_error(Labels::getLabel('MSG_joinBrands_can_be_joined_only_if,_joinProducts_is_joined.', $this->commonLangId), E_USER_ERROR);
        }
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(Brand::DB_TBL, 'LEFT OUTER JOIN', 'p.product_brand_id = brand.brand_id', 'brand');
        $this->addCondition('brand.brand_active', '=', applicationConstants::ACTIVE);
        $this->addCondition('brand.brand_deleted', '=', '0');

        if ($langId) {
            $this->joinTable(Brand::DB_LANG_TBL, 'LEFT OUTER JOIN', 'brand.brand_id = tb_l.brandlang_brand_id AND brandlang_lang_id = '.$langId, 'tb_l');
        }
    }

    public function joinProductToCategory($langId = 0)
    {
        if (!$this->productsJoined) {
            trigger_error(Labels::getLabel('MSG_joinBrands_can_be_joined_only_if,_joinProducts_is_joined.', $this->commonLangId), E_USER_ERROR);
        }
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(PRODUCT::DB_TBL_PRODUCT_TO_CATEGORY, 'LEFT OUTER JOIN', 'ptc.ptc_product_id = p.product_id', 'ptc');
        $this->joinTable(ProductCategory::DB_TBL, 'LEFT OUTER JOIN', 'c.prodcat_id = ptc.ptc_prodcat_id', 'c');

        $this->addCondition('c.prodcat_active', '=', applicationConstants::ACTIVE);
        $this->addCondition('c.prodcat_deleted', '=', applicationConstants::NO);

        if ($langId) {
            $this->joinTable(ProductCategory::DB_LANG_TBL, 'LEFT OUTER JOIN', 'c_l.prodcatlang_prodcat_id = c.prodcat_id AND prodcatlang_lang_id = '.$langId, 'c_l');
        }
    }

    public function joinSellerOrder()
    {
        $this->joinSellerOrder = true;
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $this->joinTable(Orders::DB_TBL, 'INNER JOIN', 'o.order_user_id=shop_user_id and o.order_type='.ORDERS::ORDER_SUBSCRIPTION, 'o');
        }
    }

    public function joinShops($langId = 0, $isActive = true, $isDisplayStatus = true)
    {
        if (!$this->sellerUserJoined) {
            trigger_error(Labels::getLabel("ERR_joinShops_cannot_be_joined,_unless_joinSellers_is_not_applied.", $this->commonLangId), E_USER_ERROR);
        }
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', 'seller_user.user_id = shop.shop_user_id', 'shop');

        if ($isActive) {
            $this->addCondition('shop.shop_active', '=', applicationConstants::ACTIVE);
        }
        if ($isDisplayStatus) {
            $this->addCondition('shop.shop_supplier_display_status', '=', applicationConstants::ON);
        }

        if ($langId) {
            $this->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop.shop_id = s_l.shoplang_shop_id AND shoplang_lang_id = '. $langId, 's_l');
        }
    }

    public function joinSellers()
    {
        $this->sellerUserJoined = true;
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'selprod_user_id = seller_user.user_id', 'seller_user');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'seller_user_cred.credential_user_id = seller_user.user_id', 'seller_user_cred');
        $this->addCondition('seller_user.user_is_supplier', '=', applicationConstants::YES);
        $this->addCondition('seller_user_cred.credential_active', '=', applicationConstants::ACTIVE);
        $this->addCondition('seller_user_cred.credential_verified', '=', applicationConstants::YES);
        $this->addCondition('seller_user.user_deleted', '=', applicationConstants::NO);
    }

    public function joinSellerOrderSubscription($langId = 0)
    {
        $langId = FatUtility::int($langId);

        if (!$this->joinSellerOrder) {
            trigger_error(Labels::getLabel('ERR_Seller_Subscription_Order_must_joined.', CommonHelper::getLangId()), E_USER_ERROR);
        }
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $this->joinTable(OrderSubscription::DB_TBL, 'INNER JOIN', 'o.order_id = oss.ossubs_order_id and oss.ossubs_status_id='.FatApp::getConfig('CONF_DEFAULT_SUBSCRIPTION_PAID_ORDER_STATUS'), 'oss');
            if ($langId > 0) {
                $this->joinTable(OrderSubscription::DB_TBL_LANG, 'LEFT OUTER JOIN', 'oss.ossubs_id = ossl.'.OrderSubscription::DB_TBL_LANG_PREFIX.'ossubs_id AND ossubslang_lang_id = ' . $langId, 'ossl');
            }
        }
    }

    public function joinSellerSubscription($langId = 0, $joinSeller = false)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        if ($joinSeller) {
            $this->joinSellers();
        }
        $this->joinSellerOrder();
        $this->joinSellerOrderSubscription($langId);

        //$this->addSubscriptionValidCondition();
    }

    public function addSubscriptionValidCondition($date = '')
    {
        if ($date =='') {
            $date = date("Y-m-d");
        }
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $this->addCondition('oss.ossubs_till_date', '>=', $date);
            $this->addCondition('ossubs_status_id', 'IN ', Orders::getActiveSubscriptionStatusArr());
        }
    }
}
