<?php
class Statistics extends MyAppModel
{
    public function __construct()
    {
        $this->db = FatApp::getDb();
    }

    public function getDashboardSummary($type)
    {
        $type = strtolower($type);
        switch($type) {
        case 'signups':
            $userObj = new User();
            $srch = $userObj->getUserSearchObj();
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addCondition('u.user_is_shipping_company', '=', applicationConstants::NO);
            $srch->addMultipleFields(array('count(user_id) as total_users'));
            $rs = $srch->getResultSet();
            return  $this->db->fetch($rs);
         break;
        case 'shops':
            $srch = new ShopSearch();
            $srch->joinShopOwner();
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addMultipleFields(array('count(shop_id) as total_shops'));
            $rs = $srch->getResultSet();
            return $this->db->fetch($rs);
         break;
        case 'products':
            $srch = Product::getSearchObject();
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addMultipleFields(array('count(product_id) as total_products'));
            $rs = $srch->getResultSet();
            $row = $this->db->fetch($rs);

            return $this->db->fetch($rs);
         break;
        case 'orders':
            $srch = new OrderSearch();
            $srch->joinOrderPaymentMethod();
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $cnd = $srch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PAID);
            $cnd->attachCondition('pmethod_code', '=', 'CashOnDelivery');
            $srch->addMultipleFields(array('avg(order_net_amount) AS avg_order,count(order_id) as total_orders'));
            $rs = $srch->getResultSet();
            return $this->db->fetch($rs);
         break;
        case 'sales':
            $srch = new OrderProductSearch();
            $srch->joinorders();
            $srch->joinPaymentMethod();
            $srch->addOrderProductCharges();
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $cnd = $srch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PAID);
            $cnd->attachCondition('pmethod_code', '=', 'CashOnDelivery');
            $completedOrderStatus = unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS"));
            $srch->addStatusCondition($completedOrderStatus);
            $srch->addMultipleFields(array('SUM((op_unit_price * op_qty ) + COALESCE(op_other_charges,0) - op_refund_amount) AS lifetime_sales,avg((op_unit_price * op_qty ) + COALESCE(op_other_charges,0) - op_refund_amount) AS avg_order,count(op_id) as total_orders'));
            $rs = $srch->getResultSet();
            return $this->db->fetch($rs);
         break;
        }
    }

    public function getDashboardLast12MonthsSummary( $langId =0,$type, $userTypeArr = array(), $months = 12  )
    {
        $last12Months = Stats::getLast12MonthsDetails($months);
        $type = strtolower($type);
        switch($type) {
        case 'sales':
            $srch = new OrderProductSearch();
            $srch->joinorders();
            $srch->joinPaymentMethod();
            $srch->addOrderProductCharges();
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")));

            foreach($last12Months as $key=>$val ){
                $srchObj = clone $srch;
                $srchObj->addDirectCondition("month(`order_date_added` ) = $val[monthCount] and year(`order_date_added` )= $val[year]");
                $srchObj->addMultipleFields(array('SUM((op_unit_price * op_qty ) + COALESCE(op_other_charges,0) - op_refund_amount) AS Sales,avg((op_unit_price * op_qty ) + COALESCE(op_other_charges,0) - op_refund_amount) AS avg_order,count(op_id) as total_orders'));
                $rs = $srchObj->getResultSet();
                $row = $this->db->fetch($rs);
                $sales_data[] = array("duration"=>Labels::getLabel('LBL_'.$val['monthShort'], $langId)."-".$val['year'],"value"=>round($row["Sales"], 2));
            }
            return $sales_data;
        break;
        case 'earnings':
            $srch = new OrderProductSearch();
            $srch->joinorders();
            $srch->joinPaymentMethod();
            $srch->addOrderProductCharges();
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")));

            foreach($last12Months as $key=>$val ){
                $srchObj = clone $srch;
                $srchObj->addMultipleFields(array('SUM(op_commission_charged - op_refund_commission) AS Earning'));
                $srchObj->addDirectCondition("month(`order_date_added` ) = $val[monthCount] and year(`order_date_added` )= $val[year]");
                $rs = $srchObj->getResultSet();
                $row = $this->db->fetch($rs);
                $earnings_data[] = array("duration"=>Labels::getLabel('LBL_'.$val['monthShort'], $langId)."-".$val['year'],"value"=>round($row["Earning"], 2));
            }
            return $earnings_data;
        break;

        case 'signups':
            $userObj = new User();
            $srch = $userObj->getUserSearchObj();
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addMultipleFields(array('count(user_id) AS Registrations'));
            $srch->addCondition('u.user_is_shipping_company', '=', applicationConstants::NO);

            foreach($last12Months as $key => $val ){
                $srchObj = clone $srch;
                $srchObj->addDirectCondition("month(`user_regdate` ) = $val[monthCount] and year(`user_regdate` ) = $val[year]");

                if((isset($userTypeArr['user_is_buyer']) && FatUtility::int($userTypeArr['user_is_buyer']) > 0) || (isset($userTypeArr['user_is_supplier']) && FatUtility::int($userTypeArr['user_is_supplier']) > 0)  ) {
                    $cnd = $srchObj->addCondition('u.user_is_buyer', '=',  applicationConstants::YES);
                    $cnd->attachCondition('u.user_is_supplier', '=',  applicationConstants::YES);
                }

                if(isset($userTypeArr['user_is_affiliate']) && FatUtility::int($userTypeArr['user_is_affiliate']) > 0 ) {
                    $srchObj->addCondition('u.user_is_affiliate', '=',  applicationConstants::YES);
                }

                if(isset($userTypeArr['user_is_advertiser']) && FatUtility::int($userTypeArr['user_is_advertiser']) > 0 ) {
                    $srchObj->addCondition('u.user_is_advertiser', '=',  applicationConstants::YES);
                }
                $rs = $srchObj->getResultSet();
                $row = $this->db->fetch($rs);
                $signups_data[] = array("duration"=>Labels::getLabel('LBL_'.$val['monthShort'], $langId)."-".$val['year'],"value"=>round($row["Registrations"], 2));
            }
            return $signups_data;
        break;
        case 'products':
            $srch = SellerProduct::getSearchObject();
            $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
            $srch->addCondition('sp.selprod_active', '=', applicationConstants::ACTIVE);
            $srch->addCondition('sp.selprod_deleted', '=', applicationConstants::NO);
            $srch->addCondition('p.product_active', '=', applicationConstants::ACTIVE);
            $srch->addCondition('p.product_approved', '=', Product::APPROVED);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addMultipleFields(array('count(selprod_id) AS sellerProducts'));
            foreach($last12Months as $key => $val ){
                $srchObj = clone $srch;
                $srchObj->addDirectCondition("month(`selprod_added_on` ) = $val[monthCount] and year(`selprod_added_on` ) = $val[year]");
                $rs = $srchObj->getResultSet();
                $row = $this->db->fetch($rs);
                $products_data[] = array("duration"=>Labels::getLabel('LBL_'.$val['monthShort'], $langId)."-".$val['year'],"value"=>round($row["sellerProducts"], 2));
            }
            return $products_data;
        break;
        }
    }

    function getStats($type)
    {
        $type = strtolower($type);
        switch($type) {
        case 'total_members':
            $sql = "SELECT 1 AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and user_is_shipping_company = ".applicationConstants::NO." and DATE(user_regdate) = DATE(NOW()) and YEAR(user_regdate) = YEAR(NOW())
				UNION ALL
				SELECT 7 AS num_days, count(user_id) FROM `tbl_users` WHERE  user_deleted = ".applicationConstants::NO." and user_is_shipping_company = ".applicationConstants::NO." and  YEARWEEK(user_regdate) = YEARWEEK(NOW()) and YEAR(user_regdate) = YEAR(NOW())
				UNION ALL
				SELECT 30 AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and user_is_shipping_company = ".applicationConstants::NO." and MONTH(user_regdate) = MONTH(NOW()) and YEAR(user_regdate) = YEAR(NOW())
				UNION ALL
				SELECT 90 AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and user_is_shipping_company = ".applicationConstants::NO." and user_regdate > date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH)
				UNION ALL
				SELECT -1 AS num_days, count(user_id) FROM `tbl_users` where user_deleted = ".applicationConstants::NO." and user_is_shipping_company = ".applicationConstants::NO." and user_is_shipping_company!=1";

            /* buyer/seller data [ */
            $sql .= " UNION ALL
				SELECT 'buyer_seller_1' AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and DATE(user_regdate) = DATE(NOW()) AND (user_is_buyer = 1 OR user_is_supplier = 1) and YEAR(user_regdate) = YEAR(NOW())
				UNION ALL
				SELECT 'buyer_seller_7' AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and YEARWEEK(user_regdate) =  YEARWEEK(NOW()) AND (user_is_buyer = 1 OR user_is_supplier = 1) and YEAR(user_regdate) = YEAR(NOW())
				UNION ALL
				SELECT 'buyer_seller_30' AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and MONTH(user_regdate) = MONTH(NOW()) AND (user_is_buyer = 1 OR user_is_supplier = 1) and YEAR(user_regdate) = YEAR(NOW())
				UNION ALL
				SELECT 'buyer_seller_90' AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and user_regdate > date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH) AND (user_is_buyer = 1 OR user_is_supplier = 1)
				UNION ALL
				SELECT 'buyer_seller_all' AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and (user_is_buyer = 1 OR user_is_supplier = 1)";
            /* ] */

            /* advertiser data [ */
            $sql .= " UNION ALL
				SELECT 'advertiser_1' AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and DATE(user_regdate) = DATE(NOW()) AND (user_is_advertiser = 1)  and YEAR(user_regdate) = YEAR(NOW())
				UNION ALL
				SELECT 'advertiser_7' AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and YEARWEEK(user_regdate) =  YEARWEEK(NOW()) AND (user_is_advertiser = 1) and YEAR(user_regdate) = YEAR(NOW())
				UNION ALL
				SELECT 'advertiser_30' AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and MONTH(user_regdate) = MONTH(NOW()) AND (user_is_advertiser = 1)  and YEAR(user_regdate) = YEAR(NOW())
				UNION ALL
				SELECT 'advertiser_90' AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and user_regdate > date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH) AND (user_is_advertiser = 1)
				UNION ALL
				SELECT 'advertiser_all' AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and (user_is_advertiser = 1)";
            /* ] */

            /* Affiliate data [ */
            $sql .= " UNION ALL
				SELECT 'affiliate_1' AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and DATE(user_regdate) = DATE(NOW()) AND (user_is_affiliate = 1)  and YEAR(user_regdate) = YEAR(NOW())
				UNION ALL
				SELECT 'affiliate_7' AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and YEARWEEK(user_regdate) =  YEARWEEK(NOW()) AND (user_is_affiliate = 1) and YEAR(user_regdate) = YEAR(NOW())
				UNION ALL
				SELECT 'affiliate_30' AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and MONTH(user_regdate) = MONTH(NOW()) AND (user_is_affiliate = 1)  and YEAR(user_regdate) = YEAR(NOW())
				UNION ALL
				SELECT 'affiliate_90' AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and user_regdate > date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH) AND (user_is_affiliate = 1)
				UNION ALL
				SELECT 'affiliate_all' AS num_days, count(user_id) FROM `tbl_users` WHERE user_deleted = ".applicationConstants::NO." and (user_is_affiliate = 1)";
            /* ] */

            $rs = $this->db->query($sql);
            return $this->db->fetchAllAssoc($rs);
         break;

        case 'total_shops':
            $srch = Shop::getSearchObject(false);
            $srch->joinTable('tbl_users', 'INNER JOIN', 'u.user_id = s.shop_user_id', 'u');
            $srch->joinTable('tbl_user_credentials', 'INNER JOIN', 'u.user_id = c.credential_user_id', 'c');
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();

            $srchObj1 = clone $srch;
            $srchObj1->addFld(array('1 AS num_days','count(shop_id)'));
            $srchObj1->addDirectCondition('DATE(shop_created_on) = DATE(NOW())');

            $srchObj7 = clone $srch;
            $srchObj7->addFld(array('7 AS num_days','count(shop_id)'));
            $srchObj7->addDirectCondition('YEARWEEK(shop_created_on) = YEARWEEK(NOW())');

            $srchObj30 = clone $srch;
            $srchObj30->addFld(array('30 AS num_days','count(shop_id)'));
            $srchObj30->addDirectCondition('MONTH(shop_created_on)=MONTH(NOW())');

            $srchObj90 = clone $srch;
            $srchObj90->addFld(array('90 AS num_days','count(shop_id)'));
            $srchObj90->addDirectCondition('shop_created_on>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH)');

            $srchObjAll = clone $srch;
            $srchObjAll->addFld(array('-1 AS num_days','count(shop_id)'));

            $sql = $srchObj1->getQuery() ." UNION ALL ".$srchObj7->getQuery() ." UNION ALL ".$srchObj30->getQuery() ." UNION ALL ".$srchObj90->getQuery() ." UNION ALL ".$srchObjAll->getQuery();

            $rs = $this->db->query($sql);
            return  $this->db->fetchAllAssoc($rs);
         break;

        case 'total_orders':
            $srch = new OrderSearch();
            $srch->joinOrderBuyerUser();
            $srch->joinOrderPaymentMethod();
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addCondition('order_type', '=', Orders::ORDER_PRODUCT);
            $srch->addMultipleFields(array('1 AS num_days,count(distinct order_id) as totalorders','IFNULL(SUM(order_net_amount), 0) as totalsales','IFNULL(AVG(order_net_amount),0) avgorder'));

            $srchObj1 = clone $srch;
            $srchObj1->addFld(array('1 AS num_days'));
            $srchObj1->addDirectCondition('DATE(order_date_added) = DATE(NOW())');

            $srchObj7 = clone $srch;
            $srchObj7->addFld(array('7 AS num_days'));
            $srchObj7->addDirectCondition('YEARWEEK(order_date_added) = YEARWEEK(NOW())');

            $srchObj30 = clone $srch;
            $srchObj30->addFld(array('30 AS num_days'));
            $srchObj30->addDirectCondition('MONTH(order_date_added)=MONTH(NOW())');

            $srchObj90 = clone $srch;
            $srchObj90->addFld(array('90 AS num_days'));
            $srchObj90->addDirectCondition('order_date_added>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH)');

            $srchObjAll = clone $srch;
            $srchObjAll->addFld(array('-1 AS num_days'));

            $sql = $srchObj1->getQuery() ." UNION ALL ".$srchObj7->getQuery() ." UNION ALL ".$srchObj30->getQuery() ." UNION ALL ".$srchObj90->getQuery() ." UNION ALL ".$srchObjAll->getQuery();

            $rs = $this->db->query($sql);
            return $this->db->fetchAll($rs);
         break;

        case 'total_sales':
            $srch = new OrderProductSearch();
            $srch->joinorders();
            $srch->joinPaymentMethod();
            $srch->addOrderProductCharges();
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $cnd = $srch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PAID);
            $cnd->attachCondition('pmethod_code', '=', 'CashOnDelivery');
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")));
            $srch->addMultipleFields(array('SUM((op_unit_price * op_qty ) + COALESCE(op_other_charges,0) - op_refund_amount) AS totalsales,SUM(op_commission_charged - op_refund_commission) totalcommission'));

            $srchObj1 = clone $srch;
            $srchObj1->addFld(array('1 AS num_days'));
            $srchObj1->addDirectCondition('DATE(op_completion_date) = DATE(NOW())');

            $srchObj7 = clone $srch;
            $srchObj7->addFld(array('7 AS num_days'));
            $srchObj7->addDirectCondition('YEARWEEK(op_completion_date) = YEARWEEK(NOW())');

            $srchObj30 = clone $srch;
            $srchObj30->addFld(array('30 AS num_days'));
            $srchObj30->addDirectCondition('MONTH(op_completion_date)=MONTH(NOW())');

            $srchObj90 = clone $srch;
            $srchObj90->addFld(array('90 AS num_days'));
            $srchObj90->addDirectCondition('op_completion_date>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH)');

            $srchObjAll = clone $srch;
            $srchObjAll->addFld(array('-1 AS num_days'));

            $sql = $srchObj1->getQuery() ." UNION ALL ".$srchObj7->getQuery() ." UNION ALL ".$srchObj30->getQuery() ." UNION ALL ".$srchObj90->getQuery() ." UNION ALL ".$srchObjAll->getQuery();

            $rs = $this->db->query($sql);
            return $this->db->fetchAll($rs);
            break;

        case 'total_seller_products':
            $sql = "SELECT 1 as num_days, COUNT(selprod_id) FROM ". SellerProduct::DB_TBL." sp
				LEFT OUTER JOIN ". Product::DB_TBL ." p ON sp.selprod_product_id = p.product_id
				WHERE DATE(selprod_added_on) = DATE( NOW() )
				AND product_active = ".applicationConstants::ACTIVE." AND product_approved = ".applicationConstants::YES." AND selprod_active = ".applicationConstants::ACTIVE." AND selprod_deleted = ".applicationConstants::NO."
				UNION ALL
				SELECT 7 as num_days, COUNT(selprod_id) FROM " . SellerProduct::DB_TBL . " sp
				LEFT OUTER JOIN ". Product::DB_TBL. " p ON sp.selprod_product_id = p.product_id
				WHERE YEARWEEK(selprod_added_on) = YEARWEEK(NOW())
				AND product_active = ".applicationConstants::ACTIVE." AND product_approved = ".applicationConstants::YES." AND selprod_active = ".applicationConstants::ACTIVE." AND selprod_deleted = ".applicationConstants::NO."
				UNION ALL
				SELECT 30 as num_days, COUNT(selprod_id) FROM " . SellerProduct::DB_TBL . " sp
				LEFT OUTER JOIN ". Product::DB_TBL. " p ON sp.selprod_product_id = p.product_id
				WHERE MONTH(selprod_added_on) = MONTH( NOW() )
				AND product_active = ".applicationConstants::ACTIVE." AND product_approved = ".applicationConstants::YES." AND selprod_active = ".applicationConstants::ACTIVE." AND selprod_deleted = ".applicationConstants::NO."
				UNION ALL
				SELECT 90 as num_days, COUNT(selprod_id) FROM " . SellerProduct::DB_TBL . " sp
				LEFT OUTER JOIN ". Product::DB_TBL. " p ON sp.selprod_product_id = p.product_id
				WHERE selprod_added_on > DATE_SUB( DATE_ADD( DATE_ADD( LAST_DAY(NOW()), interval 1 DAY ), interval -1 MONTH), INTERVAL 3 MONTH)
				AND product_active = ".applicationConstants::ACTIVE." AND product_approved = ".applicationConstants::YES." AND selprod_active = ".applicationConstants::ACTIVE." AND selprod_deleted = ".applicationConstants::NO."
				UNION ALL
				SELECT -1 AS num_days, COUNT(selprod_id) FROM " . SellerProduct::DB_TBL . " sp
				LEFT OUTER JOIN ". Product::DB_TBL. " p ON sp.selprod_product_id = p.product_id
				AND product_active = ".applicationConstants::ACTIVE." AND product_approved = ".applicationConstants::YES." AND selprod_active = ".applicationConstants::ACTIVE." AND selprod_deleted = ".applicationConstants::NO;
            $rs = $this->db->query($sql);
            return $this->db->fetchAllAssoc($rs);
         break;

        case 'total_withdrawal_requests':
            $srch = new WithdrawalRequestsSearch();
            $srch->joinUsers(true);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();

            $srchObj1 = clone $srch;
            $srchObj1->addFld(array('1 AS num_days','count(withdrawal_id)'));
            $srchObj1->addDirectCondition('DATE(withdrawal_request_date) = DATE(NOW())');

            $srchObj7 = clone $srch;
            $srchObj7->addFld(array('7 AS num_days','count(withdrawal_id)'));
            $srchObj7->addDirectCondition('YEARWEEK(withdrawal_request_date) = YEARWEEK(NOW())');

            $srchObj30 = clone $srch;
            $srchObj30->addFld(array('30 AS num_days','count(withdrawal_id)'));
            $srchObj30->addDirectCondition('MONTH(withdrawal_request_date)=MONTH(NOW())');

            $srchObj90 = clone $srch;
            $srchObj90->addFld(array('90 AS num_days','count(withdrawal_id)'));
            $srchObj90->addDirectCondition('withdrawal_request_date>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH)');

            $srchObjAll = clone $srch;
            $srchObjAll->addFld(array('-1 AS num_days','count(withdrawal_id)'));

            $sql = $srchObj1->getQuery() ." UNION ALL ".$srchObj7->getQuery() ." UNION ALL ".$srchObj30->getQuery() ." UNION ALL ".$srchObj90->getQuery() ." UNION ALL ".$srchObjAll->getQuery();

            $rs = $this->db->query($sql);
            return $this->db->fetchAllAssoc($rs);
            break;

        case 'total_affiliate_commission':
            $commonAndCondition = "( utxn_type = " . Transactions::TYPE_AFFILIATE_REFERRAL_SIGN_UP . " OR utxn_type = " . Transactions::TYPE_AFFILIATE_REFERRAL_ORDER . " )";
            $sql = "SELECT 1 AS num_days, IFNULL(SUM(utxn_credit), 0) FROM tbl_user_transactions WHERE DATE(utxn_date) = DATE( NOW() ) AND " . $commonAndCondition . "
				UNION ALL
				SELECT 7 AS num_days, IFNULL(SUM( utxn_credit ), 0) FROM tbl_user_transactions WHERE YEARWEEK( utxn_date ) = YEARWEEK( NOW() ) AND ". $commonAndCondition ."
				UNION ALL
				SELECT 30 AS num_days, IFNULL(SUM( utxn_credit ), 0) FROM tbl_user_transactions WHERE MONTH(utxn_date) = MONTH( NOW() ) AND ".$commonAndCondition . "
				UNION ALL
				SELECT 90 AS num_days, IFNULL(SUM( utxn_credit ), 0) FROM tbl_user_transactions WHERE utxn_date > DATE_SUB( DATE_ADD( DATE_ADD( LAST_DAY( NOW() ), interval 1 DAY), interval -1 MONTH ), INTERVAL 3 MONTH) AND " . $commonAndCondition. "
				UNION ALL
				SELECT -1 AS num_days, IFNULL(SUM( utxn_credit ), 0) FROM tbl_user_transactions WHERE ".$commonAndCondition;
            $rs = $this->db->query($sql);
            return $this->db->fetchAllAssoc($rs);
         break;

        case 'total_ppc_earnings':
            $sql = "SELECT 1 AS num_days,SUM(pcharge_charged_amount) AS totalppcearnings FROM `tbl_promotions_charges` tpc  WHERE  DATE(pcharge_date)=DATE(NOW())
				UNION ALL
				SELECT 7 AS num_days,SUM(pcharge_charged_amount) AS totalppcearnings FROM `tbl_promotions_charges` tpc  WHERE   YEARWEEK(pcharge_date) = YEARWEEK(NOW())
				UNION ALL
				SELECT 30 AS num_days,SUM(pcharge_charged_amount) AS totalppcearnings FROM `tbl_promotions_charges` tpc  WHERE  MONTH(pcharge_date)=MONTH(NOW())
				UNION ALL
				SELECT 90 AS num_days,SUM(pcharge_charged_amount) AS totalppcearnings FROM `tbl_promotions_charges` tpc  WHERE  pcharge_date>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH)
				UNION ALL
				SELECT -1 AS num_days,SUM(pcharge_charged_amount) AS totalppcearnings FROM `tbl_promotions_charges` tpc";

            $rs = $this->db->query($sql);
            return  $this->db->fetchAllAssoc($rs);
         break;

        case 'total_subscription_earnings':
            $sql = "SELECT 1 AS num_days,SUM(ossubs_price) AS earnings FROM `tbl_order_seller_subscriptions` osub WHERE DATE(ossubs_till_date)=DATE(NOW())
				UNION ALL
				SELECT 7 AS num_days,SUM(ossubs_price) AS earnings FROM `tbl_order_seller_subscriptions` osub  WHERE YEARWEEK(ossubs_till_date) = YEARWEEK(NOW())
				UNION ALL
				SELECT 30 AS num_days,SUM(ossubs_price) AS earnings FROM `tbl_order_seller_subscriptions` osub WHERE MONTH(ossubs_till_date)=MONTH(NOW())
				UNION ALL
				SELECT 90 AS num_days,SUM(ossubs_price) AS earnings FROM `tbl_order_seller_subscriptions` osub WHERE ossubs_till_date>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH)
				UNION ALL
				SELECT -1 AS num_days,SUM(ossubs_price) AS earnings FROM `tbl_order_seller_subscriptions` osub ";
            $rs = $this->db->query($sql);
            return  $this->db->fetchAllAssoc($rs);
         break;

        case 'total_affiliate_withdrawal_requests':
            $srch = new WithdrawalRequestsSearch();
            $srch->joinUsers(true);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addCondition('user_is_affiliate', '=', applicationConstants::YES);
            $srchObj1 = clone $srch;
            $srchObj1->addFld(array('1 AS num_days','count(withdrawal_id)'));
            $srchObj1->addDirectCondition('DATE(withdrawal_request_date) = DATE(NOW())');

            $srchObj7 = clone $srch;
            $srchObj7->addFld(array('7 AS num_days','count(withdrawal_id)'));
            $srchObj7->addDirectCondition('YEARWEEK(withdrawal_request_date) = YEARWEEK(NOW())');

            $srchObj30 = clone $srch;
            $srchObj30->addFld(array('30 AS num_days','count(withdrawal_id)'));
            $srchObj30->addDirectCondition('MONTH(withdrawal_request_date)=MONTH(NOW())');

            $srchObj90 = clone $srch;
            $srchObj90->addFld(array('90 AS num_days','count(withdrawal_id)'));
            $srchObj90->addDirectCondition('withdrawal_request_date>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH)');

            $srchObjAll = clone $srch;
            $srchObjAll->addFld(array('-1 AS num_days','count(withdrawal_id)'));

            $sql = $srchObj1->getQuery() ." UNION ALL ".$srchObj7->getQuery() ." UNION ALL ".$srchObj30->getQuery() ." UNION ALL ".$srchObj90->getQuery() ." UNION ALL ".$srchObjAll->getQuery();

            $rs = $this->db->query($sql);
            return $this->db->fetchAllAssoc($rs);
         break;

        case 'total_product_reviews':
            $sql = "SELECT 1 AS num_days, count(spreview_id) FROM `tbl_seller_product_reviews` WHERE DATE(spreview_posted_on)=DATE(NOW())
				UNION ALL
				SELECT 7 AS num_days, count(spreview_id) FROM `tbl_seller_product_reviews` WHERE YEARWEEK(spreview_posted_on) = YEARWEEK(NOW())
				UNION ALL
				SELECT 30 AS num_days, count(spreview_id) FROM `tbl_seller_product_reviews` WHERE MONTH(spreview_posted_on)=MONTH(NOW())
				UNION ALL
				SELECT 90 AS num_days, count(spreview_id) FROM `tbl_seller_product_reviews` WHERE spreview_posted_on>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH)
				UNION ALL
				SELECT -1 AS num_days, count(spreview_id) FROM `tbl_seller_product_reviews`";

            $rs = $this->db->query($sql);
            return  $this->db->fetchAllAssoc($rs);
         break;
        }
    }

    function getTopProducts($type,$langId = 0, $pageSize = 0)
    {
        $langId = FatUtility::int($langId);
        if($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        $srch = new OrderProductSearch($langId, true);
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts($langId);
        $srch->doNotCalculateRecords();
        if($pageSize > 0) {
            $srch->setPageSize($pageSize);
        }else{
            $srch->doNotLimitRecords();
        }

        $cnd = $srch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PAID);
        $cnd->attachCondition('pmethod_code', '=', 'CashOnDelivery');
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")));
        $srch->addMultipleFields(array('IF(selprod_title is null or op_selprod_title ="",CONCAT(op_product_name,op_selprod_options) , selprod_title) as product_name','sum(op_qty - op_refund_qty) as sold'));
        switch(strtoupper($type)){
        case 'TODAY':
            $srch->addDirectCondition('DATE(o.order_date_added)=DATE(NOW())');
            break;
        case 'WEEKLY':
            $srch->addDirectCondition('YEARWEEK(o.order_date_added)=YEARWEEK(NOW())');
            break;
        case 'MONTHLY':
            $srch->addDirectCondition('MONTH(o.order_date_added)=MONTH(NOW())');
            break;
        case 'YEARLY':
            $srch->addDirectCondition('YEAR(o.order_date_added)=YEAR(NOW())');
            break;
        }
        /* $srch->addGroupBy('product_name'); */
        $srch->addGroupBy('op_selprod_id');
        $srch->addGroupBy('op_is_batch');
        $srch->addOrder('sold', 'desc');
        $rs = $srch->getResultSet();
        return $this->db->fetchAll($rs);
    }

    public function getTopSearchKeywords($type,$pageSize = 0)
    {
        $srch = new SearchBase('tbl_search_items', 'tsi');
        switch(strtoupper($type)){
        case 'TODAY':
            $srch->addDirectCondition('DATE(tsi.searchitem_date)=DATE(NOW())');
            break;
        case 'WEEKLY':
            $srch->addDirectCondition('YEARWEEK(tsi.searchitem_date)=YEARWEEK(NOW())');
            break;
        case 'MONTHLY':
            $srch->addDirectCondition('MONTH(tsi.searchitem_date)=MONTH(NOW())');
            break;
        case 'YEARLY':
            $srch->addDirectCondition('YEAR(tsi.searchitem_date)=YEAR(NOW())');
            break;
        }
        $srch->addMultipleFields(array('tsi.*','sum(tsi.searchitem_count) as search_count'));
        $srch->addOrder('searchitem_count', 'desc');
        $srch->addOrder('search_count', 'desc');
        $srch->addGroupBy('tsi.searchitem_keyword');
        if($pageSize > 0) {
            $srch->setPageSize($pageSize);
        }

        $rs = $srch->getResultSet();
        return $this->db->fetchAll($rs);        
    }

    public function getAddedToCartCount()
    {
        $sql = "Select COUNT(DISTINCT user_id) as cart_count from (SELECT usercart_user_id as user_id,count(usercart_user_id) as count FROM `tbl_user_cart` group by usercart_user_id
				UNION ALL
				SELECT order_user_id as user_id,count(order_user_id) as count FROM `tbl_orders` group by order_user_id) tbl ";
        $rs = $this->db->query($sql);
        return $result = $this->db->fetch($rs);
    }

    public function getUserOrderStatsCount($type='')
    {
        $cancelAndRefundedStatusArr = (array)FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS");
        $srch = new OrderProductSearch(0, true);
        $srch->joinPaymentMethod();
        /* $srch = new SearchBase('tbl_order_products', 'torp');
        $srch->joinTable('tbl_orders', 'LEFT JOIN', 'tord.order_id = torp.op_order_id', 'tord'); */
        switch(strtoupper($type)){
        case 'CANCEL_AND_REFUNDED':
            $srch->addStatusCondition($cancelAndRefundedStatusArr);
            break;
        case 'REACHED_CHECKOUT':
            $srch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PENDING);
            break;
        case 'PURCHASED':
            $cnd = $srch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PAID);
            $cnd->attachCondition('pmethod_code', '=', 'cashondelivery');
            break;
        }
        $srch->addMultipleFields(array('op_id'));
        $srch->addGroupBy('order_user_id');
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        return $srch->recordCount();
    }

    public function getConversionStats()
    {
        $srch = new SearchBase('tbl_users', 'tu');
        $srch->addMultipleFields(array('count(user_id) as total_users'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $res = $this->db->fetch($rs);
        $totalUser = $res['total_users'];
        $cartRes = $this->getAddedToCartCount();
        $addedToCartCount = $cartRes["cart_count"];
        $purchasedCount = $this->getUserOrderStatsCount('purchased');
        $reachedToChecoutCount = $purchasedCount+$this->getUserOrderStatsCount('REACHED_CHECKOUT');
        $cancelAndRefundedUserCount = $this->getUserOrderStatsCount('CANCEL_AND_REFUNDED');

        $data['added_to_cart']['count'] = $addedToCartCount;
        $data['added_to_cart']['%age'] = ( $totalUser ) ? round(($addedToCartCount*100)/$totalUser, 2) : 0;

        $data['reached_checkout']['count'] = $reachedToChecoutCount;
        $data['reached_checkout']['%age'] = ( $totalUser ) ? round(($reachedToChecoutCount*100)/$totalUser, 2) : 0;

        $data['purchased']['count'] = $purchasedCount;
        $data['purchased']['%age'] = ( $totalUser ) ? round((($purchasedCount*100)/$totalUser), 2) : 0;

        $data['cancelled']['count'] = $cancelAndRefundedUserCount;
        $data['cancelled']['%age'] = ( $totalUser ) ? round((($cancelAndRefundedUserCount*100)/$totalUser), 2) : 0;

        /* $data = array(
        'added_to_cart'=>array('count'=>$addedToCartCount,'%age' => ( $totalUser ) ? round(($addedToCartCount*100)/$totalUser,2)) : 0 ,
        'reached_checkout'=>array('count'=>$reachedToChecoutCount,'%age'=>round(($reachedToChecoutCount*100)/$totalUser),2),
        'purchased'=>array('count'=>$purchasedCount,'%age'=>round(($purchasedCount*100)/$totalUser),2),
        'cancelled'=>array('count'=>$cancelAndRefundedUserCount,'%age'=>round(($cancelAndRefundedUserCount*100)/$totalUser),2),
        ); */
        return $data;
    }
}
