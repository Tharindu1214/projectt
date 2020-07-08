<?php
class UserFavoriteShopSearch extends SearchBase
{
    private $langId;
    private $shopsJoined;

    public function __construct($langId = 0)
    {
        parent::__construct(Shop::DB_TBL_SHOP_FAVORITE, 'ufs');
        $this->langId = FatUtility::int($langId);
        $this->shopsJoined = false;
    }

    public function setDefinedCriteria($langId = 0, $isActive = true)
    {
        $this->joinShops($langId, $isActive);
        $this->joinShopOwner();
        $this->joinShopCountry($langId);
        $this->joinShopState($langId);
    }

    public function joinShops($langId = 0, $isActive = true, $shopSupplierDisplayStatus = true)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(Shop::DB_TBL, 'INNER JOIN', 'ufs.ufs_shop_id = s.shop_id', 's');

        if ($langId) {
            $this->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 's.shop_id = shop_l.shoplang_shop_id AND shoplang_lang_id = '. $langId, 'shop_l');
        }

        if ($isActive) {
            $this->addCondition('shop_active', '=', applicationConstants::ACTIVE);
            $this->addCondition('shop_supplier_display_status', '=', applicationConstants::ACTIVE);
        }
        if ($shopSupplierDisplayStatus) {
            $this->addCondition('shop_supplier_display_status', '=', applicationConstants::ACTIVE);
        }
        $this->shopsJoined = true;
    }

    /* public function joinShopCountry($langId = 0){
    if( !$this->shopsJoined ){
    trigger_error('joinShopCountry can be joined only if, joinShops is joined.',E_USER_ERROR);
    }
    $this->joinTable( Countries::DB_TBL, 'LEFT OUTER JOIN', 'c.country_id = s.shop_country_id','c');

    $langId = FatUtility::int( $langId );
    if( $this->langId ){
    $langId = $this->langId;
    }

    if( $langId ){
    $this->joinTable( Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'c.country_id = c_l.countrylang_country_id AND c_l.countrylang_lang_id = '. $langId, 'c_l' );
    }
    }

    public function joinShopState($langId = 0){
    if( !$this->shopsJoined ){
    trigger_error('joinShopState can be joined only if, joinShops is joined.',E_USER_ERROR);
    }
    $this->joinTable( States::DB_TBL, 'LEFT OUTER JOIN', 'st.state_id = s.shop_state_id','st');

    $langId = FatUtility::int( $langId );
    if( $this->langId ){
    $langId = $this->langId;
    }

    if( $langId ){
    $this->joinTable( States::DB_TBL_LANG, 'LEFT OUTER JOIN', 'st.state_id = st_l.statelang_state_id AND st_l.statelang_lang_id = '. $langId, 'st_l' );
    }
    } */

    public function joinShopOwner($isActive = true)
    {
        if (!$this->shopsJoined) {
            trigger_error(Labels::getLabel('MSG_joinShopOwner_can_be_joined_only_if,_joinShops_is_joined.', CommonHelper::getLangId()), E_USER_ERROR);
        }

        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 's.shop_user_id = u.user_id', 'u');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'u_cred.credential_user_id = u.user_id', 'u_cred');
        $this->addCondition('u.user_is_supplier', '=', applicationConstants::YES);

        if ($isActive) {
            $this->addCondition('u_cred.credential_active', '=', applicationConstants::ACTIVE);
            $this->addCondition('u_cred.credential_verified', '=', applicationConstants::YES);
        }
    }

    public function joinShopCountry($langId = 0, $isActive = true)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 's.shop_country_id = shop_country.country_id', 'shop_country');

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
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(States::DB_TBL, 'LEFT OUTER JOIN', 's.shop_state_id = shop_state.state_id', 'shop_state');

        if ($langId) {
            $this->joinTable(States::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop_state.state_id = shop_state_l.statelang_state_id AND shop_state_l.statelang_lang_id = '.$langId, 'shop_state_l');
        }
        if ($isActive) {
            $this->addCondition('shop_state.state_active', '=', applicationConstants::ACTIVE);
        }
    }

    public function joinWhosFavouriteUser($isActive = true)
    {
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'ufs.ufs_user_id = uf.user_id', 'uf');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uf_cred.credential_user_id = uf.user_id', 'uf_cred');
        if ($isActive) {
            $this->addCondition('uf_cred.credential_active', '=', applicationConstants::ACTIVE);
            $this->addCondition('uf_cred.credential_verified', '=', applicationConstants::YES);
        }
    }

    public function joinFavouriteUserShopsCount()
    {
        $srch = new SearchBase(Shop::DB_TBL_SHOP_FAVORITE, 'fsc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('fsc.ufs_user_id as userFavShopcount_user_id','count(fsc.ufs_id) as userFavShopcount'));
        $srch->addGroupBy('fsc.ufs_user_id');
        $qrytotal = $srch->getQuery();
        $this->joinTable('(' . $qrytotal . ')', 'LEFT OUTER JOIN', 'ufs.ufs_user_id = fusc.userFavShopcount_user_id', 'fusc');
    }

    public function joinSellerOrder()
    {
        $this->sellerOrderJoined = true;
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $this->joinTable(Orders::DB_TBL, 'INNER JOIN', 'o.order_user_id=shop_user_id and o.order_type='.ORDERS::ORDER_SUBSCRIPTION, 'o');
        }
    }

    public function joinSellerOrderSubscription($langId = 0, $date = '')
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        if (!$this->sellerOrderJoined) {
            trigger_error(Labels::getLabel('ERR_Seller_Subscription_Order_must_joined.', CommonHelper::getLangId()), E_USER_ERROR);
        }
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $this->joinTable(OrderSubscription::DB_TBL, 'INNER JOIN', 'o.order_id = oss.ossubs_order_id ', 'oss');
            if ($langId>0) {
                $this->joinTable(OrderSubscription::DB_TBL_LANG, 'LEFT OUTER JOIN', 'oss.ossubs_id = ossl.'.OrderSubscription::DB_TBL_LANG_PREFIX.'ossubs_id AND ossl.'.OrderSubscription::DB_TBL_LANG_PREFIX.'lang_id = '.$langId, 'ossl');
            }
            if ($date =='') {
                $date = date("Y-m-d");
            }
            $this->addCondition('oss.ossubs_till_date', '>=', $date);
            $this->addCondition('ossubs_status_id', 'IN ', Orders::getActiveSubscriptionStatusArr());
        }
    }
}
