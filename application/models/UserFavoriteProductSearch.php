<?php
class UserFavoriteProductSearch extends SearchBase
{
    private $langId;
    private $productsJoined;
    private $sellerProductsJoined;
    private $joinSellerOrder ;
    private $commonLangId;
    public function __construct($langId = 0, $alias = 'ufp')
    {
        parent::__construct(Product::DB_TBL_PRODUCT_FAVORITE, 'ufp');
        $this->langId = FatUtility::int($langId);
        $this->productsJoined = false;
        $this->commonLangId = CommonHelper::getLangId();
    }

    public function setDefinedCriteria($langId = 0, $isActive = true)
    {
        $this->joinSellerproducts($langId);
        $this->joinProducts($langId);
    }
    public function joinProducts($langId = 0, $isProductActive = true, $isProductApproved = true, $isProductDeleted = true)
    {
        if (!$this->sellerProductsJoined) {
            trigger_error(Labels::getLabel("ERR_joinProducts_can_be_joined_only_if,_joinSellerProducts_is_joined.", $this->commonLangId), E_USER_ERROR);
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
    public function joinSellers()
    {
        $this->sellerUserJoined = true;
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'selprod_user_id = seller_user.user_id', 'seller_user');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'credential_user_id = seller_user.user_id', 'seller_user_cred');
        $this->addCondition('seller_user.user_is_supplier', '=', applicationConstants::YES);
        $this->addCondition('credential_active', '=', applicationConstants::ACTIVE);
        $this->addCondition('credential_verified', '=', applicationConstants::YES);
    }

    public function joinSellerProductSpecialPrice($forDate = '')
    {
        if (!$this->sellerProductsJoined) {
            trigger_error(Labels::getLabel("ERR_joinSellerProductSpecialPrice_can_be_joined_only_if,_joinSellerProducts_is_joined.", $this->commonLangId), E_USER_ERROR);
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
    public function joinSellerproducts($langId = 0, $isActive = true, $isDeleted = true)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'ufp.ufp_selprod_id = sp.selprod_id', 'sp');

        if ($langId) {
            $this->joinTable(
                SellerProduct::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'sp_l.'.SellerProduct::DB_LANG_TBL_PREFIX.'selprod_id = sp.'.SellerProduct::tblFld('id').' and
			sp_l.'.SellerProduct::DB_LANG_TBL_PREFIX.'lang_id = '.$langId,
                'sp_l'
            );
        }

        if ($isActive) {
            $this->addCondition(SellerProduct::DB_TBL_PREFIX.'active', '=', applicationConstants::ACTIVE);
        }
        if ($isDeleted) {
            $this->addCondition(SellerProduct::DB_TBL_PREFIX.'deleted', '=', applicationConstants::NO);
        }

        $this->sellerProductsJoined = true;
    }

    public function joinShopCountry($langId = 0, $isActive = true)
    {
        $langId = FatUtility::int($langId);
        $this->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'shop.shop_country_id = shop_country.country_id', 'shop_country');

        if ($langId) {
            $this->joinTable(Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop_country.country_id = shop_country_l.countrylang_country_id AND shop_country_l.countrylang_lang_id = '.$langId, 'shop_country_l');
        }
        if ($isActive) {
            $this->addCondition('shop_country.country_active', '=', applicationConstants::ACTIVE);
        }
    }

    public function joinShopState($langId = 0, $isActive = true)
    {
        $langId = FatUtility::int($langId);
        $this->joinTable(States::DB_TBL, 'LEFT OUTER JOIN', 'shop.shop_state_id = shop_state.state_id', 'shop_state');

        if ($langId) {
            $this->joinTable(States::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop_state.state_id = shop_state_l.statelang_state_id AND shop_state_l.statelang_lang_id = '.$langId, 'shop_state_l');
        }
        if ($isActive) {
            $this->addCondition('shop_state.state_active', '=', applicationConstants::ACTIVE);
        }
    }

    public function joinBrands($langId = 0, $isActive = true, $isDeleted = true)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(Brand::DB_TBL, 'LEFT OUTER JOIN', 'p.product_brand_id = brand.brand_id', 'brand');
        if ($isActive) {
            $this->addCondition('brand.brand_active', '=', applicationConstants::ACTIVE);
        }
        if ($isDeleted) {
            $this->addCondition('brand.brand_deleted', '=', '0');
        }

        if ($langId) {
            $this->joinTable(Brand::DB_LANG_TBL, 'LEFT OUTER JOIN', 'brand.brand_id = tb_l.brandlang_brand_id AND brandlang_lang_id = '.$langId, 'tb_l');
        }
    }

    public function joinProductToCategory($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'LEFT OUTER JOIN', 'ptc.ptc_product_id = p.product_id', 'ptc');
        $this->joinTable(ProductCategory::DB_TBL, 'LEFT OUTER JOIN', 'c.prodcat_id = ptc.ptc_prodcat_id', 'c');

        $this->addCondition('c.prodcat_active', '=', applicationConstants::ACTIVE);
        $this->addCondition('c.prodcat_deleted', '=', applicationConstants::NO);


        if ($langId) {
            $this->joinTable(ProductCategory::DB_LANG_TBL, 'LEFT OUTER JOIN', 'c_l.prodcatlang_prodcat_id = c.prodcat_id AND prodcatlang_lang_id = '.$langId, 'c_l');
        } else {
            //$this->addOrder('c.');
        }
    }

    public function joinWhosFavouriteUser($isActive = true)
    {
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'ufp.ufp_user_id = uf.user_id', 'uf');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uf_cred.credential_user_id = uf.user_id', 'uf_cred');
        if ($isActive) {
            $this->addCondition('uf_cred.credential_active', '=', applicationConstants::ACTIVE);
            $this->addCondition('uf_cred.credential_verified', '=', applicationConstants::YES);
        }
    }

    public static function joinFavouriteUserProductsCount($userId = 0)
    {
        $srch = new UserFavoriteProductSearch(0, 'fpc');
        $srch->setDefinedCriteria();
        $srch->joinBrands();
        $srch->joinSellers();
        $srch->joinShops();
        $srch->joinProductToCategory();
        $srch->joinSellerSubscription('', true);
        $srch->addSubscriptionValidCondition();
        $srch->addCondition('ufp_user_id', '=', $userId);

        $srch->addMultipleFields(array( 'selprod_id','ufp_user_id as userFavProductcount_user_id' ));

        $srch->addGroupBy('selprod_id');

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        //$srch->addMultipleFields(array('fpc.ufp_user_id as userFavProductcount_user_id','count(fpc.ufp_id) as userFavProductcount'));
        //$srch->addGroupBy('fpc.ufp_user_id');
        return $qrytotal = $srch->getQuery();
        // $this->joinTable('(' . $qrytotal . ')', 'LEFT OUTER JOIN', 'ufp.ufp_user_id = fupc.userFavProductcount_user_id', 'fupc');
    }

    public function joinSellerOrder($langId = 0)
    {
        $this->joinSellerOrder = true;
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0)) {
            $this->joinTable(Orders::DB_TBL, 'INNER JOIN', 'o.order_user_id=seller_user.user_id AND o.order_type='.ORDERS::ORDER_SUBSCRIPTION.' AND o.order_is_paid =1', 'o');
        }
    }

    public function joinSellerOrderSubscription($langId = 0)
    {
        $langId = FatUtility::int($langId);

        if (!$this->joinSellerOrder) {
            trigger_error(Labels::getLabel('ERR_Seller_Subscription_Order_must_joined.', $this->commonLangId), E_USER_ERROR);
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
