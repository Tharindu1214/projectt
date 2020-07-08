<?php
class DiscountCoupons extends MyAppModel
{
    const DB_TBL = 'tbl_coupons';
    const DB_TBL_PREFIX = 'coupon_';

    const DB_TBL_LANG = 'tbl_coupons_lang';
    const DB_TBL_LANG_PREFIX = 'coupon_';

    const DB_TBL_COUPON_TO_CATEGORY = 'tbl_coupon_to_category';
    const DB_TBL_COUPON_TO_PRODUCT = 'tbl_coupon_to_products';
    const DB_TBL_COUPON_TO_USER = 'tbl_coupon_to_users';
    const DB_TBL_COUPON_TO_PLAN = 'tbl_coupon_to_plan';
    const DB_TBL_COUPON_HOLD = 'tbl_coupons_hold';
    const DB_TBL_COUPON_HOLD_PENDING_ORDER = 'tbl_coupons_hold_pending_order';
    const DB_TBL_COUPON_HISTORY = 'tbl_coupons_history';

    private $db;

    const TYPE_DISCOUNT = 1;
    const TYPE_FREE_SHIPPING = 2;
    const TYPE_SELLER_PACKAGE = 3;

    const VALID_FOR_ONE_TIME = 1;
    const VALID_FOR_RECURRING_ALSO = 2;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($langId = 0, $active = true, $isDeleted = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'dc');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'couponlang_coupon_id = dc.coupon_id
AND couponlang_lang_id = ' . $langId,
                'dc_l'
            );
        }

        if ($isDeleted == true) {
            $srch->addCondition('dc.'.static::DB_TBL_PREFIX.'deleted', '=', applicationConstants::NO);
        }

        if ($active == true) {
            $srch->addCondition('dc.'.static::DB_TBL_PREFIX.'active', '=', applicationConstants::ACTIVE);
        }
        return $srch;
    }

    public static function getTypeArr($langId, $select = false)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        $select_arr = array(''=>Labels::getLabel('LBL_Coupon_Type', $langId));

        $coupon_type_arr =  array(
        static::TYPE_DISCOUNT        =>    Labels::getLabel('LBL_Product_Purchase', $langId),
        /* static::TYPE_FREE_SHIPPING    =>    Labels::getLabel('LBL_Free_Shipping', $langId),     */
        static::TYPE_SELLER_PACKAGE    =>    Labels::getLabel('LBL_Subscription_Package', $langId),
        );
        if ($select) {
            return $select_arr+$coupon_type_arr;
        } else {
            return $coupon_type_arr;
        }
    }

    public static function getValidForArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::VALID_FOR_ONE_TIME        =>    Labels::getLabel('LBL_One_Time', $langId),
        static::VALID_FOR_RECURRING_ALSO    =>    Labels::getLabel('LBL_Include_Recurring', $langId),
        );
    }

    public static function getCouponCategories($coupon_id, $lang_id = 0)
    {
        $coupon_id = FatUtility::int($coupon_id);
        $lang_id = FatUtility::int($lang_id);

        if (!$coupon_id) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $lang_id), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COUPON_TO_CATEGORY);
        $srch->addCondition('ctc_coupon_id', '=', $coupon_id);
        $srch->joinTable(ProductCategory::DB_TBL, 'LEFT OUTER JOIN', 'prodcat_id = ctc_prodcat_id', 'c');

        $srch->addMultipleFields(array("prodcat_id","prodcat_identifier"));
        if ($lang_id) {
            $srch->joinTable(ProductCategory::DB_TBL.'_lang', 'LEFT OUTER JOIN', 'c_l.prodcatlang_prodcat_id = prodcat_id AND prodcatlang_lang_id = '.$lang_id, 'c_l');
            $srch->addFld(array("prodcat_id","prodcat_name"));
        }

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAll($rs, 'prodcat_id');

        return $row;
    }

    public static function getCouponPlans($coupon_id, $lang_id = 0)
    {
        $coupon_id = FatUtility::int($coupon_id);
        $lang_id = FatUtility::int($lang_id);

        if (!$coupon_id) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $lang_id), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COUPON_TO_PLAN);
        $srch->addCondition('ctplan_coupon_id', '=', $coupon_id);
        $srch->joinTable(SellerPackagePlans::DB_TBL, 'LEFT OUTER JOIN', 'spplan_id = ctplan_spplan_id', 'spp');
        $srch->joinTable(
            SellerPackages::DB_TBL,
            'LEFT OUTER JOIN',
            'sp.spackage_id = spp.spplan_spackage_id ',
            'sp'
        );
        $srch->addMultipleFields(array("spplan_id","spackage_identifier"));
        if ($lang_id) {
            $srch->joinTable(
                SellerPackages::DB_TBL . '_lang',
                'LEFT OUTER JOIN',
                'spl.spackagelang_spackage_id = sp.spackage_id AND spl.spackagelang_lang_id = ' . CommonHelper::getlangId(),
                'spl'
            );
            $srch->addMultipleFields(array('spplan_id', "IFNULL( spl.spackage_name, sp.spackage_identifier ) as spackage_name","spplan_interval","spplan_frequency"));
        }

        $rs = $srch->getResultSet();

        $row = FatApp::getDb()->fetchAll($rs, 'spplan_id');

        return $row;
    }

    public static function getCouponProducts($coupon_id, $lang_id = 0)
    {
        $coupon_id = FatUtility::int($coupon_id);
        $lang_id = FatUtility::int($lang_id);

        if (!$coupon_id) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $lang_id), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COUPON_TO_PRODUCT);
        $srch->addCondition('ctp_coupon_id', '=', $coupon_id);
        $srch->joinTable(Product::DB_TBL, 'LEFT OUTER JOIN', 'product_id = ctp_product_id', 'p');
        $srch->addMultipleFields(array("product_id","product_identifier"));
        if ($lang_id) {
            $srch->joinTable(Product::DB_TBL.'_lang', 'LEFT OUTER JOIN', 'p_l.productlang_product_id = product_id AND productlang_lang_id = '.$lang_id, 'p_l');
            $srch->addFld(array('product_name'));
        }
        $rs = $srch->getResultSet();

        $row = FatApp::getDb()->fetchAll($rs, 'product_id');
        return $row;
    }

    public static function getCouponUsers($coupon_id)
    {
        $coupon_id = FatUtility::int($coupon_id);

        if (!$coupon_id) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", CommonHelper::getLangId()), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COUPON_TO_USER);
        $srch->addCondition('ctu_coupon_id', '=', $coupon_id);
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'user_id = ctu_user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'credential_user_id = user_id', 'c');
        $srch->addMultipleFields(array("user_id","user_name","credential_username"));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAll($rs, 'user_id');
        return $row;
    }

    public static function getUserCoupons($user_id, $lang_id, $coupon_type = self::TYPE_DISCOUNT)
    {
        $user_id = FatUtility::int($user_id);

        if (!$user_id) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $lang_id), E_USER_ERROR);
            return false;
        }
        $interval = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' - 15 minute'));
        /* coupon history[ */
        $cHistorySrch = CouponHistory::getSearchObject();
        $cHistorySrch->doNotLimitRecords();
        $cHistorySrch->doNotCalculateRecords();
        $cHistorySrch->addMultipleFields(array('couponhistory_coupon_id','couponhistory_id'));
        /* ] */
        /* coupon temp hold[ */
        $cHoldSrch = new SearchBase(DiscountCoupons::DB_TBL_COUPON_HOLD);
        $cHoldSrch->addCondition('couponhold_added_on', '>=', $interval);
        $cHoldSrch->addCondition('couponhold_user_id', '!=', $user_id);
        $cHoldSrch->addMultipleFields(array('couponhold_coupon_id'));
        $cHoldSrch->doNotLimitRecords();
        $cHoldSrch->doNotCalculateRecords();
        /* ] */

        /* coupon User History[ */
        $userCouponHistorySrch = CouponHistory::getSearchObject();
        $userCouponHistorySrch->addCondition('couponhistory_user_id', '=', $user_id);
        $userCouponHistorySrch->doNotLimitRecords();
        $userCouponHistorySrch->doNotCalculateRecords();
        //$userCouponHistorySrch->addMultipleFields(array('count(couponhistory_id) as user_coupon_used_count'));
        /* ] */

        $srch = new SearchBase(self::DB_TBL, 'dc');
        if ($coupon_type == self::TYPE_DISCOUNT) {
            $srch->addCondition('ctu_user_id', '=', $user_id);
            $srch->joinTable(self::DB_TBL_COUPON_TO_USER, 'LEFT OUTER JOIN', 'coupon_id = ctu_coupon_id', 'ctu');
        }
        $srch->joinTable(self::DB_TBL_LANG, 'LEFT OUTER JOIN', 'couponlang_coupon_id = coupon_id and couponlang_lang_id = '.$lang_id, 'dc_l');
        $srch->joinTable('('.$cHistorySrch->getQuery().')', 'LEFT OUTER JOIN', 'coupon_history.couponhistory_coupon_id = dc.coupon_id', 'coupon_history');
        $srch->joinTable('('.$cHoldSrch->getQuery().')', 'LEFT OUTER JOIN', 'dc.coupon_id = coupon_hold.couponhold_coupon_id', 'coupon_hold');
        $srch->joinTable('('. $userCouponHistorySrch->getQuery() .')', 'LEFT OUTER JOIN', 'dc.coupon_id = user_coupon_history.couponhistory_coupon_id', 'user_coupon_history');
        $srch->addMultipleFields(array("dc.*","dc_l.*",'IFNULL(COUNT(coupon_history.couponhistory_id), 0) as coupon_used_count', 'IFNULL(COUNT(coupon_hold.couponhold_coupon_id), 0) as coupon_hold_count','count(user_coupon_history.couponhistory_id) as user_coupon_used_count'));
        $srch->addCondition('dc.'.static::DB_TBL_PREFIX.'active', '=', applicationConstants::ACTIVE);
        $cnd = $srch->addCondition('dc.coupon_end_date', '>=', date('Y-m-d'));
        $cnd->attachCondition('dc.coupon_end_date', '=', '0000-00-00');
        if ($coupon_type) {
            $srch->addCondition('coupon_type', '=', $coupon_type);
        }
        $srch->addGroupBy('dc.coupon_id');
        $srch->addHaving('dc.coupon_uses_count', '>', 'mysql_func_coupon_used_count + coupon_hold_count', 'AND', true);
        $srch->addHaving('dc.coupon_uses_coustomer', '>', 'mysql_func_user_coupon_used_count', 'AND', true);
        // echo $srch->getQuery(); die;
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        $rs = $srch->getResultSet();

        $row = FatApp::getDb()->fetchAll($rs, 'coupon_id');
        return $row;
    }

    public function getCoupon($code, $langId = 0)
    {
        $langId = FatUtility::int($langId);
        if (!$code) {
            return false;
        }

        $status = true;
        $currDate = date('Y-m-d');

        $srch = static::getSearchObject($langId);
        $srch->addMultipleFields(array('dc.*','IFNULL(dc_l.coupon_title,dc.coupon_identifier) as coupon_title'));

        $srch->addCondition('coupon_code', '=', $code);

        $cnd = $srch->addCondition('coupon_start_date', '=', '0000-00-00', 'AND');
        $cnd->attachCondition('coupon_start_date', '<=', $currDate, 'OR');

        $cnd1 = $srch->addCondition('coupon_end_date', '=', '0000-00-00', 'AND');
        $cnd1->attachCondition('coupon_end_date', '>=', $currDate, 'OR');
        $rs = $srch->getResultSet();
        $couponData = FatApp::getDb()->fetch($rs);

        if ($couponData == false) {
            return false;
        }

        $cartObj = new Cart();
        $cartSubTotal = $cartObj->getSubTotal($langId);
        //CommonHelper::printArray($cartSubTotal); die();
        //die("dsd");

        if ($couponData['coupon_min_order_value'] > $cartSubTotal) {
            $status = false;
        }

        $chistorySrch = CouponHistory::getSearchObject();
        $chistorySrch->addCondition('couponhistory_coupon_id', '=', $couponData['coupon_id']);
        $chistorySrch->addMultipleFields(array('count(couponhistory_id) as total'));
        $chistorySrch->doNotLimitRecords();
        $chistorySrch->doNotCalculateRecords();
        $chistoryRs = $chistorySrch->getResultSet();

        $couponHistoryData = FatApp::getDb()->fetch($chistoryRs);

        $interval = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' - 15 minute'));

        FatApp::getDb()->deleteRecords(DiscountCoupons::DB_TBL_COUPON_HOLD, array('smt' => 'couponhold_added_on < ?', 'vals' => array($interval ) ));

        $cHoldSrch = new SearchBase(static::DB_TBL_COUPON_HOLD);
        $cHoldSrch->addCondition('couponhold_coupon_id', '=', $couponData['coupon_id']);
        $cHoldSrch->addCondition('couponhold_added_on', '>=', $interval);
        $cHoldSrch->addCondition('couponhold_user_id', '!=', UserAuthentication::getLoggedUserId());
        $cHoldSrch->addMultipleFields(array('count(couponhold_id) as total'));
        $cHoldSrch->doNotLimitRecords();
        $cHoldSrch->doNotCalculateRecords();
        $cHoldRs = $cHoldSrch->getResultSet();
        $couponHoldData = FatApp::getDb()->fetch($cHoldRs);

        $total = $couponHistoryData['total'] + $couponHoldData['total'];
        if ($couponData['coupon_uses_count'] > 0 && $total >= $couponData['coupon_uses_count']) {
            $status = false;
        }


        $userSpecificCoupon = false;
        if (UserAuthentication::isUserLogged()) {
            $userId = UserAuthentication::getLoggedUserId();

            $cUserhistorySrch = CouponHistory::getSearchObject();
            $cUserhistorySrch->addCondition('couponhistory_coupon_id', '=', $couponData['coupon_id']);
            $cUserhistorySrch->addCondition('couponhistory_user_id', '=', $userId);
            $cUserhistorySrch->addMultipleFields(array('count(couponhistory_id) as total'));
            $cUserhistorySrch->doNotLimitRecords();
            $cUserhistorySrch->doNotCalculateRecords();
            $cUserhistoryRs = $cUserhistorySrch->getResultSet();
            $couponUserHistoryData = FatApp::getDb()->fetch($cUserhistoryRs);

            if ($couponData['coupon_uses_coustomer'] > 0 && $couponUserHistoryData['total'] >= $couponData['coupon_uses_coustomer']) {
                $status = false;
            }
            $couponUserData = static::getCouponUsers($couponData['coupon_id']);

            if (array_key_exists($userId, $couponUserData)) {
                $userSpecificCoupon = true;
            }
        }

        // Products
        $productData = array('group'=>'','product'=>'');
        $products = $cartObj->getProducts($langId);
        $prodObj = new Product();

        if ($userSpecificCoupon) {
            foreach ($products as $product) {
                if ($product['is_batch']) {
                    $productData['group'][] = $product['prodgroup_id'];
                } else {
                    $productData['product'][] = $product['product_id'];
                }
            }
        } else {
            $couponProductData = static::getCouponProducts($couponData['coupon_id']);
            $couponCategoryData = static::getCouponCategories($couponData['coupon_id']);

            if (!empty($couponProductData) || !empty($couponCategoryData)) {
                foreach ($products as $product) {
                    if ($product['is_batch']) {
                        $productData['group'][] = array();
                        /* if(!empty($product['products'])){
                        foreach($product['products'] as $pgproduct){
                        if (array_key_exists($pgproduct['product_id'], $couponProductData)) {
                        $productData['group'][] = $pgproduct['product_id'];
                        continue;
                        }

                        $prodCategories = $prodObj->getProductCategories($pgproduct['product_id']);
                        if(count($prodCategories>0)){
                        foreach($prodCategories as $category){
                        if(array_key_exists($category['prodcat_id'],$couponCategoryData)){
                        $productData['group'][] = $pgproduct['product_id'];
                        continue;
                        }
                        }
                        }
                        }
                        } */
                    } else {
                        if (array_key_exists($product['product_id'], $couponProductData)) {
                            $productData['product'][] = $product['product_id'];
                            continue;
                        }

                        $prodCategories = $prodObj->getProductCategories($product['product_id']);
                        if (count($prodCategories>0) && $prodCategories != false) {
                            foreach ($prodCategories as $category) {
                                if (array_key_exists($category['prodcat_id'], $couponCategoryData)) {
                                    $productData['product'][] = $product['product_id'];
                                    continue;
                                }
                            }
                        }
                    }
                }
            } else {
                //
            }
        }

        if (empty($productData['product']) && empty($productData['group'])) {
            $status = false;
        }

        if ($status) {
            return array_merge($couponData, array("products"=>$productData['product'],'groups'=>$productData['group']));
        }
    }

    public static function getValidCoupons($userId, $langId, $coupon_code = '', $orderId = '')
    {
        $userId = FatUtility::int($userId);
        $langId = FatUtility::int($langId);

        if ($userId <= 0) {
            trigger_error(Labels::getLabel("ERR_User_id_is_mandatory", $langId), E_USER_ERROR);
        }
        if ($langId <= 0) {
            trigger_error(Labels::getLabel("ERR_Language_id_is_mandatory", $langId), E_USER_ERROR);
        }

        $currDate = date('Y-m-d');
        $interval = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' - 15 minute'));

        $cartObj = new Cart($userId);
        $cartProducts = $cartObj->getProducts($langId);
        $cartSubTotal = $cartObj->getSubTotal($langId);

        /* coupon history[ */
        $cHistorySrch = CouponHistory::getSearchObject();
        $cHistorySrch->doNotLimitRecords();
        $cHistorySrch->doNotCalculateRecords();
        $cHistorySrch->addGroupBy('couponhistory_coupon_id');
        $cHistorySrch->addMultipleFields(array('count(couponhistory_id) as coupon_used_count', 'couponhistory_coupon_id'));
        //$cHistorySrch->addMultipleFields(array('couponhistory_coupon_id','couponhistory_id'));
        /* ] */

        /* coupon User History[ */
        $userCouponHistorySrch = CouponHistory::getSearchObject();
        $userCouponHistorySrch->addCondition('couponhistory_user_id', '=', $userId);
        $userCouponHistorySrch->doNotLimitRecords();
        $userCouponHistorySrch->doNotCalculateRecords();
        //$userCouponHistorySrch->addMultipleFields(array('count(couponhistory_id) as user_coupon_used_count'));
        /* ] */

        /* coupon temp hold for order[ */
        $pendingOrderHoldSrch = new SearchBase(DiscountCoupons::DB_TBL_COUPON_HOLD_PENDING_ORDER);
        $pendingOrderHoldSrch->addMultipleFields(array('count(ochold_order_id) as pending_order_hold_count','ochold_coupon_id'));
        $pendingOrderHoldSrch->doNotLimitRecords();
        $pendingOrderHoldSrch->addGroupBy('ochold_coupon_id');
        $pendingOrderHoldSrch->doNotCalculateRecords();
        if ($orderId !='') {
            $pendingOrderHoldSrch->addCondition('ochold_order_id', '!=', $orderId);
        }
        /* ] */

        /* coupon temp hold[ */
        $cHoldSrch = new SearchBase(DiscountCoupons::DB_TBL_COUPON_HOLD);
        $cHoldSrch->addCondition('couponhold_added_on', '>=', $interval);
        $cHoldSrch->addCondition('couponhold_user_id', '!=', $userId);
        /* $cHoldSrch->addCondition('couponhold_usercart_id','!=',$cartObj->cart_id); */
        $cHoldSrch->addMultipleFields(array('couponhold_coupon_id'));
        $cHoldSrch->doNotLimitRecords();
        $cHoldSrch->doNotCalculateRecords();
        /* ] */

        /* Coupon Users[ */
        $cUsersSrch = new SearchBase(DiscountCoupons::DB_TBL_COUPON_TO_USER);
        $cUsersSrch->doNotCalculateRecords();
        $cUsersSrch->doNotLimitRecords();
        $cUsersSrch->addGroupBy('ctu_coupon_id');
        $cUsersSrch->addMultipleFields(array('ctu_coupon_id','GROUP_CONCAT(ctu_user_id) as grouped_coupon_users'));
        /* ] */

        /* Coupon Products[ */
        $cProductSrch = new SearchBase(DiscountCoupons::DB_TBL_COUPON_TO_PRODUCT);
        $cProductSrch->doNotCalculateRecords();
        $cProductSrch->doNotLimitRecords();
        $cProductSrch->addGroupBy('ctp_coupon_id');
        $cProductSrch->addMultipleFields(array('ctp_coupon_id','GROUP_CONCAT(ctp_product_id) as grouped_coupon_products'));
        /* ] */

        /* Coupon categories[ */
        $cCategorySrch = new SearchBase(DiscountCoupons::DB_TBL_COUPON_TO_CATEGORY);
        $cCategorySrch->doNotCalculateRecords();
        $cCategorySrch->doNotLimitRecords();
        $cCategorySrch->addGroupBy('ctc_coupon_id');
        $cCategorySrch->addMultipleFields(array('ctc_coupon_id', 'GROUP_CONCAT(ctc_prodcat_id) as grouped_coupon_categories'));
        /* ] */

        $srch = DiscountCoupons::getSearchObject($langId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->joinTable('('.$cHistorySrch->getQuery().')', 'LEFT OUTER JOIN', 'coupon_history.couponhistory_coupon_id = dc.coupon_id', 'coupon_history');
        $srch->joinTable('('.$cHoldSrch->getQuery().')', 'LEFT OUTER JOIN', 'dc.coupon_id = coupon_hold.couponhold_coupon_id', 'coupon_hold');

        $srch->joinTable('('.$cUsersSrch->getQuery().')', 'LEFT OUTER JOIN', 'dc.coupon_id = ctu.ctu_coupon_id', 'ctu');

        $srch->joinTable('('. $userCouponHistorySrch->getQuery() .')', 'LEFT OUTER JOIN', 'dc.coupon_id = user_coupon_history.couponhistory_coupon_id', 'user_coupon_history');
        $srch->joinTable('('.$cProductSrch->getQuery().')', 'LEFT OUTER JOIN', 'dc.coupon_id = ctp.ctp_coupon_id', 'ctp');
        $srch->joinTable('('.$cCategorySrch->getQuery().')', 'LEFT OUTER JOIN', 'dc.coupon_id = ctc.ctc_coupon_id', 'ctc');


        // if ($orderId !='') {
        $srch->joinTable('('.$pendingOrderHoldSrch->getQuery().')', 'LEFT OUTER JOIN', 'dc.coupon_id = ctop.ochold_coupon_id', 'ctop');
        // }


        $srch->addCondition('coupon_type', '=', DiscountCoupons::TYPE_DISCOUNT);

        $cnd = $srch->addCondition('coupon_start_date', '=', '0000-00-00', 'AND');
        $cnd->attachCondition('coupon_start_date', '<=', $currDate, 'OR');

        $cnd1 = $srch->addCondition('coupon_end_date', '=', '0000-00-00', 'AND');
        $cnd1->attachCondition('coupon_end_date', '>=', $currDate, 'OR');

        $srch->addCondition('coupon_min_order_value', '<=', $cartSubTotal);

        if ($coupon_code != '') {
            $srch->addCondition('coupon_code', '=', $coupon_code);
        }

        /* $srch->addMultipleFields(array( 'dc.*', 'dc_l.coupon_description', 'IFNULL(dc_l.coupon_title, dc.coupon_identifier) as coupon_title', 'IFNULL(COUNT(coupon_history.couponhistory_id), 0) as coupon_used_count', 'IFNULL(COUNT(coupon_hold.couponhold_coupon_id), 0) as coupon_hold_count','count(user_coupon_history.couponhistory_id) as user_coupon_used_count', 'ctu.grouped_coupon_users', 'ctp.grouped_coupon_products', 'ctc.grouped_coupon_categories')); */

        $selectArr = array( 'dc.*', 'dc_l.coupon_description', 'IFNULL(dc_l.coupon_title, dc.coupon_identifier) as coupon_title', 'IFNULL(coupon_history.coupon_used_count, 0) as coupon_used_count', 'IFNULL(COUNT(coupon_hold.couponhold_coupon_id), 0) as coupon_hold_count','count(user_coupon_history.couponhistory_id) as user_coupon_used_count', 'ctu.grouped_coupon_users', 'ctp.grouped_coupon_products', 'ctc.grouped_coupon_categories');
        // if ($orderId !='') {
        $selectArr =  array_merge($selectArr, array('IFNULL(ctop.pending_order_hold_count,0) as pending_order_hold_count'));
        // }
        $srch->addMultipleFields($selectArr);

        /* checking current coupon is valid for current logged user[ */
        $directCondtion1 = ' (grouped_coupon_users IS NULL AND grouped_coupon_products IS NULL AND grouped_coupon_categories IS NULL) ';
        $directCondtion2 = ' ( grouped_coupon_users IS NOT NULL AND ( FIND_IN_SET('.$userId.', grouped_coupon_users) ) ) ';
        /* ] */

        /* Or current coupon is valid for current cart products[  */
        $directCondtion3 = '';
        foreach ($cartProducts as $cartProduct) {
            if (!$cartProduct['is_batch']) {
                $directCondtion3 .= ' OR ( grouped_coupon_products IS NOT NULL AND ( FIND_IN_SET( '. $cartProduct['product_id'] .', grouped_coupon_products) ) ) ';
            }
        }
        /* ] */

        /* or current coupon is valid for current cart products categories[ */
        $prodObj = new Product();
        $directCondition4 = '';
        foreach ($cartProducts as $cartProduct) {
            if (!$cartProduct['is_batch']) {
                $prodCategories = $prodObj->getProductCategories($cartProduct['product_id']);
                if ($prodCategories) {
                    foreach ($prodCategories as $prodcat_id => $prodCategory) {
                        $directCondition4 .= ' OR (grouped_coupon_categories IS NOT NULL AND ( FIND_IN_SET('.$prodcat_id.', grouped_coupon_categories) ) ) ';
                    }
                }
            }
        }
        /* ] */

        $srch->addDirectCondition("(". $directCondtion1.' OR ( '. $directCondtion2 . $directCondtion3 . $directCondition4 .' )' . " )", 'AND');

        $srch->addGroupBy('dc.coupon_id');
        $srch->addHaving('coupon_uses_count', '>', 'mysql_func_coupon_used_count + coupon_hold_count + pending_order_hold_count', 'AND', true);
        $srch->addHaving('coupon_uses_coustomer', '>', 'mysql_func_user_coupon_used_count', 'AND', true);
        // if ($orderId !='') {
        //     $srch->addHaving('coupon_uses_count', '>', 'mysql_func_coupon_used_count + coupon_hold_count + pending_order_hold_count', 'AND', true);
        //     $srch->addHaving('coupon_uses_coustomer', '>', 'mysql_func_user_coupon_used_count', 'AND', true);
        // } else {
        //     $srch->addHaving('coupon_uses_count', '>', 'mysql_func_coupon_used_count + coupon_hold_count', 'AND', true);
        //     $srch->addHaving('coupon_uses_coustomer', '>', 'mysql_func_user_coupon_used_count', 'AND', true);
        // }

        $rs = $srch->getResultSet();
        if ($coupon_code != '') {
            $data = FatApp::getDb()->fetch($rs);
            /* if( $cartProducts ){
            foreach( $cartProducts as $cartProduct ){
            if( $cartProduct['is_batch'] ){
            $data['groups'][] = $cartProduct['prodgroup_id'];
            }
            }
            } */
        } else {
            $data = FatApp::getDb()->fetchAll($rs, 'coupon_id');
        }
        return $data;
    }

    public function getSubscriptionCoupon($code, $langId = 0)
    {
        $langId = FatUtility::int($langId);
        if (!$code) {
            return false;
        }

        $status = true;
        $currDate = date('Y-m-d');

        $srch = static::getSearchObject($langId);
        $srch->addMultipleFields(array('dc.*','IFNULL(dc_l.coupon_title,dc.coupon_identifier) as coupon_title'));

        $srch->addCondition('coupon_code', '=', $code);
        $srch->addCondition('coupon_type', '=', static::TYPE_SELLER_PACKAGE);

        $cnd = $srch->addCondition('coupon_start_date', '=', '0000-00-00', 'AND');
        $cnd->attachCondition('coupon_start_date', '<=', $currDate, 'OR');

        $cnd1 = $srch->addCondition('coupon_end_date', '=', '0000-00-00', 'AND');
        $cnd1->attachCondition('coupon_end_date', '>=', $currDate, 'OR');
        $rs = $srch->getResultSet();
        $couponData = FatApp::getDb()->fetch($rs);

        if ($couponData == false) {
            return false;
        }

        $scartObj = new SubscriptionCart();
        $cartSubTotal = $scartObj->getSubTotal($langId);

        if ($couponData['coupon_min_order_value'] > $cartSubTotal) {
            $status = false;
        }

        $chistorySrch = CouponHistory::getSearchObject();
        $chistorySrch->addCondition('couponhistory_coupon_id', '=', $couponData['coupon_id']);
        $chistorySrch->addMultipleFields(array('count(couponhistory_id) as total'));
        $chistorySrch->doNotLimitRecords();
        $chistorySrch->doNotCalculateRecords();
        $chistoryRs = $chistorySrch->getResultSet();

        $couponHistoryData = FatApp::getDb()->fetch($chistoryRs);

        $interval = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' - 15 minute'));

        FatApp::getDb()->deleteRecords(DiscountCoupons::DB_TBL_COUPON_HOLD, array('smt' => 'couponhold_added_on < ?', 'vals' => array($interval ) ));

        $cHoldSrch = new SearchBase(static::DB_TBL_COUPON_HOLD);
        $cHoldSrch->addCondition('couponhold_coupon_id', '=', $couponData['coupon_id']);
        $cHoldSrch->addCondition('couponhold_added_on', '>=', $interval);
        $cHoldSrch->addCondition('couponhold_user_id', '!=', UserAuthentication::getLoggedUserId());
        $cHoldSrch->addMultipleFields(array('count(couponhold_id) as total'));
        $cHoldSrch->doNotLimitRecords();
        $cHoldSrch->doNotCalculateRecords();
        $cHoldRs = $cHoldSrch->getResultSet();
        $couponHoldData = FatApp::getDb()->fetch($cHoldRs);

        $total = $couponHistoryData['total'] + $couponHoldData['total'];
        if ($couponData['coupon_uses_count'] > 0 && $total >= $couponData['coupon_uses_count']) {
            $status = false;
        }

        $userSpecificCoupon = false;
        if (UserAuthentication::isUserLogged()) {
            $userId = UserAuthentication::getLoggedUserId();

            $cUserhistorySrch = CouponHistory::getSearchObject();
            $cUserhistorySrch->addCondition('couponhistory_coupon_id', '=', $couponData['coupon_id']);
            $cUserhistorySrch->addCondition('couponhistory_user_id', '=', $userId);
            $cUserhistorySrch->addMultipleFields(array('count(couponhistory_id) as total'));
            $cUserhistorySrch->doNotLimitRecords();
            $cUserhistorySrch->doNotCalculateRecords();
            $cUserhistoryRs = $cUserhistorySrch->getResultSet();
            $couponUserHistoryData = FatApp::getDb()->fetch($cUserhistoryRs);

            if ($couponData['coupon_uses_coustomer'] > 0 && $couponUserHistoryData['total'] >= $couponData['coupon_uses_coustomer']) {
                $status = false;
            }
            $couponUserData = static::getCouponUsers($couponData['coupon_id']);
            if (array_key_exists($userId, $couponUserData)) {
                $userSpecificCoupon = true;
            }
        }

        // Products
        $subscriptionData = array('product'=>'');
        $subscriptions = $scartObj->getSubscription($langId);


        if ($userSpecificCoupon) {
            foreach ($subscriptions as $product) {
                $subscriptionData['product'][] = $product['spplan_id'];
            }
        } else {
            $couponPackagesData = static::getCouponPlans($couponData['coupon_id']);
            if (!empty($couponPackagesData)) {
                foreach ($subscriptions as $product) {
                    if (array_key_exists($product['spplan_id'], $couponPackagesData)) {
                        $subscriptionData['product'][] = $product['spplan_id'];
                        continue;
                    }
                }
            } else {
                //
            }
        }

        /* if (empty($subscriptionData['product']) ) {
        $status = false;
        } */

        if ($status) {
            return array_merge($couponData, array("products"=>$subscriptionData['product']));
        }
    }

    public function addUpdateCouponCategory($coupon_id, $prodcat_id)
    {
        $coupon_id = FatUtility::int($coupon_id);
        $prodcat_id = FatUtility::int($prodcat_id);
        if (1 > $coupon_id || 1 > $prodcat_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            ;
            return false;
        }

        $record = new TableRecord(static::DB_TBL_COUPON_TO_CATEGORY);
        $assignValues = array();
        $assignValues['ctc_coupon_id'] = $coupon_id;
        $assignValues['ctc_prodcat_id'] = $prodcat_id;
        $record->assignValues($assignValues);
        if (!$record->addNew(array(), $assignValues)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function removeCouponCategory($coupon_id, $prodcat_id)
    {
        $coupon_id = FatUtility::int($coupon_id);
        $prodcat_id = FatUtility::int($prodcat_id);

        if (1 > $coupon_id || 1 > $prodcat_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            ;
            return false;
        }

        if (!FatApp::getDb()->deleteRecords(static::DB_TBL_COUPON_TO_CATEGORY, array('smt'=>'ctc_coupon_id = ? AND ctc_prodcat_id = ?','vals' => array($coupon_id, $prodcat_id) ))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function addUpdateCouponPlan($coupon_id, $spplan_id)
    {
        $coupon_id = FatUtility::int($coupon_id);
        $spplan_id = FatUtility::int($spplan_id);
        if (1 > $coupon_id || 1 > $spplan_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            ;
            return false;
        }

        $record = new TableRecord(static::DB_TBL_COUPON_TO_PLAN);
        $assignValues = array();
        $assignValues['ctplan_coupon_id'] = $coupon_id;
        $assignValues['ctplan_spplan_id'] = $spplan_id;
        $record->assignValues($assignValues);
        if (!$record->addNew(array(), $assignValues)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function removeCouponPlan($coupon_id, $spplan_id)
    {
        $coupon_id = FatUtility::int($coupon_id);
        $spplan_id = FatUtility::int($spplan_id);

        if (1 > $coupon_id || 1 > $spplan_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            ;
            return false;
        }

        if (!FatApp::getDb()->deleteRecords(static::DB_TBL_COUPON_TO_PLAN, array('smt'=>'ctplan_coupon_id = ? AND ctplan_spplan_id = ?','vals' => array($coupon_id, $spplan_id) ))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function removeCouponProduct($coupon_id, $product_id)
    {
        $coupon_id = FatUtility::int($coupon_id);
        $product_id = FatUtility::int($product_id);

        if (1 > $coupon_id || 1 > $product_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            ;
            return false;
        }

        if (!FatApp::getDb()->deleteRecords(static::DB_TBL_COUPON_TO_PRODUCT, array('smt'=>'ctp_coupon_id = ? AND ctp_product_id = ?','vals' => array($coupon_id, $product_id) ))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function addUpdateCouponProduct($coupon_id, $product_id)
    {
        $coupon_id = FatUtility::int($coupon_id);
        $product_id = FatUtility::int($product_id);
        if (1 > $coupon_id || 1 > $product_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            ;
            return false;
        }

        $record = new TableRecord(static::DB_TBL_COUPON_TO_PRODUCT);
        $assignValues = array();
        $assignValues['ctp_coupon_id'] = $coupon_id;
        $assignValues['ctp_product_id'] = $product_id;
        $record->assignValues($assignValues);
        if (!$record->addNew(array(), $assignValues)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function addUpdateCouponUser($coupon_id, $user_id)
    {
        $coupon_id = FatUtility::int($coupon_id);
        $user_id = FatUtility::int($user_id);
        if (1 > $coupon_id || 1 > $user_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            ;
            return false;
        }

        $record = new TableRecord(static::DB_TBL_COUPON_TO_USER);
        $assignValues = array();
        $assignValues['ctu_coupon_id'] = $coupon_id;
        $assignValues['ctu_user_id'] = $user_id;
        $record->assignValues($assignValues);
        if (!$record->addNew(array(), $assignValues)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function removeCouponUser($coupon_id, $user_id)
    {
        $coupon_id = FatUtility::int($coupon_id);
        $user_id = FatUtility::int($user_id);

        if (1 > $coupon_id || 1 > $user_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            ;
            return false;
        }

        if (!FatApp::getDb()->deleteRecords(static::DB_TBL_COUPON_TO_USER, array('smt'=>'ctu_coupon_id = ? AND ctu_user_id = ?','vals' => array($coupon_id, $user_id) ))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public static function getPlanTitle($sPlanRow = array(), $siteLangId =0)
    {
        if (empty($sPlanRow)) {
            return '';
        }
        $siteLangId = FatUtility::int($siteLangId);
        if (!$siteLangId) {
            trigger_error(Labels::getLabel("ERR_Language_Id_Not_Passed.", $siteLangId), E_USER_ERROR);
        }


        $str = Labels::getLabel('LBL_Seller_AutoSuggest_Plan_Name', $siteLangId);
        $planIntervals = SellerPackagePlans::getSubscriptionPeriods($siteLangId);
        $replacementArr = array(
        '{package_name}'    =>    $sPlanRow['spackage_name'],
        '{plan_days}'        =>    $sPlanRow['spplan_interval']." ".$planIntervals[$sPlanRow['spplan_frequency']],

        );
        foreach ($replacementArr as $key => $val) {
            $str = str_replace($key, $val, $str);
        }
        return $str;
    }
}
