<?php
class Orders extends MyAppModel
{
    const DB_TBL = 'tbl_orders';
    const DB_TBL_LANG ='tbl_orders_lang';
    const DB_TBL_PREFIX = 'order_';

    const DB_TBL_ORDER_PRODUCTS = 'tbl_order_products';
    const DB_TBL_ORDER_PRODUCTS_LANG = 'tbl_order_products_lang';

    const DB_TBL_ORDER_SELLER_SUBSCRIPTIONS = 'tbl_order_seller_subscriptions';
    const DB_TBL_ORDER_SELLER_SUBSCRIPTIONS_LANG = 'tbl_order_seller_subscriptions_lang';
    const DB_TBL_ORDER_SELLER_SUBSCRIPTIONS_PREFIX = 'ossubs_';
    const DB_TBL_ORDER_SELLER_SUBSCRIPTIONS_LANG_PREFIX = 'ossubslang_';

    const DB_TBL_ORDERS_STATUS = 'tbl_orders_status';
    const DB_TBL_ORDERS_STATUS_LANG = 'tbl_orders_status_lang';
    const DB_TBL_ORDER_STATUS_HISTORY = 'tbl_orders_status_history';

    const DB_TBL_ORDER_USER_ADDRESS = 'tbl_order_user_address';
    const DB_TBL_ORDER_EXTRAS = 'tbl_order_extras';
    const DB_TBL_ORDER_PAYMENTS = 'tbl_order_payments';


    const DB_TBL_ORDER_PRODUCTS_SHIPPING = 'tbl_order_product_shipping';
    const DB_TBL_ORDER_PRODUCTS_SHIPPING_LANG = 'tbl_order_product_shipping_lang';

    const DB_TBL_CHARGES = 'tbl_order_product_charges';
    const DB_TBL_CHARGES_PREFIX=     'opcharge_';

    const BILLING_ADDRESS_TYPE = 1;
    const SHIPPING_ADDRESS_TYPE = 2;

    const ORDER_IS_CANCELLED = -1;
    const ORDER_IS_PENDING = 0;
    const ORDER_IS_PAID = 1;

    const PAYMENT_GATEWAY_STATUS_PENDING = 0;
    const PAYMENT_GATEWAY_STATUS_PAID = 1;
    const PAYMENT_GATEWAY_STATUS_CANCELLED = 2;

    const ORDER_PRODUCT = 1;
    const ORDER_SUBSCRIPTION = 2;
    const ORDER_WALLET_RECHARGE = 3;

    const REPLACE_ORDER_USER_ADDRESS ='XXX';

    //const DEFAULT_CHILD_ORDER_STATUS_ID = 1;

    /* const DB_TBL_ORDER_CANCEL_REQUEST = 'tbl_order_cancel_requests';
    const CANCELLATION_REQUEST_STATUS_PENDING = 0;
    const CANCELLATION_REQUEST_STATUS_APPROVED = 1;
    const CANCELLATION_REQUEST_STATUS_DECLINED = 2; */


    private $order_id;
    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getOrderPaymentStatusArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::ORDER_IS_CANCELLED =>    Labels::getLabel('LBL_Order_Payment_Status_Cancelled', $langId),
        static::ORDER_IS_PENDING =>    Labels::getLabel('LBL_Order_Payment_Status_Pending', $langId),
        static::ORDER_IS_PAID    =>    Labels::getLabel('LBL_Order_Payment_Status_Paid', $langId),
        );
    }
    public static function getActiveSubscriptionStatusArr()
    {
        $activeSubscriptionStatuses =
        array(FatApp::getConfig("CONF_DEFAULT_SUBSCRIPTION_PAID_ORDER_STATUS"));



        return $activeSubscriptionStatuses;
    }

    public static function getPaymentGatewayStatusArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::PAYMENT_GATEWAY_STATUS_PENDING        =>    Labels::getLabel('LBL_Payment_Gateway_Status_Pending', $langId),
        static::PAYMENT_GATEWAY_STATUS_PAID    =>    Labels::getLabel('LBL_Order_Payment_Gateway_Paid', $langId),
        static::PAYMENT_GATEWAY_STATUS_CANCELLED    =>    Labels::getLabel('LBL_Payment_Gateway_Status_Cancelled', $langId),
        );
    }

    public static function getOrderStatusArr($langId, $inArray = array(), $current = 0)
    {
        $current = FatUtility::int($current);
        $srch = new SearchBase(Orders::DB_TBL_ORDERS_STATUS, 'ostatus');

        $srchOrderStatus = clone $srch;
        $srchOrderStatus->addCondition('orderstatus_id', '=', $current);
        $srchOrderStatus->doNotCalculateRecords();
        $srchOrderStatus->doNotLimitRecords();
        $srchOrderStatus->addMultipleFields(array('orderstatus_priority'));
        $record = FatApp::getDb()->fetch($srchOrderStatus->getResultSet());
        $orderStatusPriority = FatUtility::int($record['orderstatus_priority']);

        if ($langId > 0) {
            $srch->joinTable(
                Orders::DB_TBL_ORDERS_STATUS_LANG,
                'LEFT OUTER JOIN',
                'ostatus_l.orderstatuslang_orderstatus_id = ostatus.orderstatus_id
			AND ostatus_l.orderstatuslang_lang_id = ' . $langId,
                'ostatus_l'
            );
        }

        if (count($inArray) > 0) {
            $srch->addDirectCondition('orderstatus_id IN (' . implode(",", $inArray) . ')');
        }

        if ($current > 0) {
            $srch->addCondition('orderstatus_priority', '>=', $orderStatusPriority);
        }

        $srch->addCondition('orderstatus_is_active', '=', applicationConstants::ACTIVE);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orderstatus_id','IFNULL(orderstatus_name,orderstatus_identifier) as orderstatus_name'));

        $rs = $srch->getResultSet();
        if (!$rs) {
            return array();
        }
        return $row = FatApp::getDb()->fetchAllAssoc($rs);
    }

    public static function getOrderProductStatusArr($langId, $inArray = array(), $current = 0, $isDigital = 0)
    {
        $current = FatUtility::int($current);
        $srch = new SearchBase(Orders::DB_TBL_ORDERS_STATUS, 'ostatus');
        $srch->addCondition('orderstatus_type', '=', Orders::ORDER_PRODUCT);
        $srchOrderStatus = clone $srch;
        if (0 < $current) {
            $srchOrderStatus->addCondition('orderstatus_id', '=', $current);
        }
        $srchOrderStatus->addCondition('orderstatus_type', '=', Orders::ORDER_PRODUCT);
        $srchOrderStatus->doNotCalculateRecords();
        $srchOrderStatus->doNotLimitRecords();
        $srchOrderStatus->addMultipleFields(array('orderstatus_priority'));
        $record = FatApp::getDb()->fetch($srchOrderStatus->getResultSet());

        $orderStatusPriority = FatUtility::int($record['orderstatus_priority']);

        if ($langId > 0) {
            $srch->joinTable(
                Orders::DB_TBL_ORDERS_STATUS_LANG,
                'LEFT OUTER JOIN',
                'ostatus_l.orderstatuslang_orderstatus_id = ostatus.orderstatus_id
			AND ostatus_l.orderstatuslang_lang_id = ' . $langId,
                'ostatus_l'
            );
        }

        if (count($inArray) > 0) {
            $srch->addDirectCondition('orderstatus_id IN (' . implode(",", $inArray) . ')');
        }

        if ($current > 0) {
            $srch->addCondition('orderstatus_priority', '>=', $orderStatusPriority);
        }

        if ($isDigital) {
            $srch->addCondition('orderstatus_is_digital', '=', applicationConstants::YES);
        }

        $srch->addCondition('orderstatus_is_active', '=', applicationConstants::ACTIVE);
        $srch->addOrder('orderstatus_priority');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orderstatus_id','IFNULL(orderstatus_name,orderstatus_identifier) as orderstatus_name'));

        $rs = $srch->getResultSet();
        if (!$rs) {
            return array();
        }
        return $row = FatApp::getDb()->fetchAllAssoc($rs);
    }
    public static function getOrderSubscriptionStatusArr($langId, $inArray = array(), $current = 0)
    {
        $current = FatUtility::int($current);
        $srch = new SearchBase(Orders::DB_TBL_ORDERS_STATUS, 'ostatus');
        $srch->addCondition('orderstatus_type', '=', Orders::ORDER_SUBSCRIPTION);
        $srchOrderStatus = clone $srch;
        $srchOrderStatus->addCondition('orderstatus_id', '=', $current);
        $srchOrderStatus->addCondition('orderstatus_type', '=', Orders::ORDER_SUBSCRIPTION);
        $srchOrderStatus->doNotCalculateRecords();
        $srchOrderStatus->doNotLimitRecords();
        $srchOrderStatus->addMultipleFields(array('orderstatus_priority'));
        $record = FatApp::getDb()->fetch($srchOrderStatus->getResultSet());
        $orderStatusPriority = FatUtility::int($record['orderstatus_priority']);

        if ($langId > 0) {
            $srch->joinTable(
                Orders::DB_TBL_ORDERS_STATUS_LANG,
                'LEFT OUTER JOIN',
                'ostatus_l.orderstatuslang_orderstatus_id = ostatus.orderstatus_id
			AND ostatus_l.orderstatuslang_lang_id = ' . $langId,
                'ostatus_l'
            );
        }

        if (count($inArray) > 0) {
            $srch->addDirectCondition('orderstatus_id IN (' . implode(",", $inArray) . ')');
        }

        if ($current > 0) {
            $srch->addCondition('orderstatus_priority', '>=', $orderStatusPriority);
        }

        $srch->addCondition('orderstatus_is_active', '=', applicationConstants::ACTIVE);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orderstatus_id','IFNULL(orderstatus_name,orderstatus_identifier) as orderstatus_name'));

        $rs = $srch->getResultSet();
        if (!$rs) {
            return array();
        }
        return $row = FatApp::getDb()->fetchAllAssoc($rs);
    }

    public function getOrderId()
    {
        return $this->order_id;
    }

    public static function getSearchObject($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'o');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'o_l.orderlang_order_id = o.order_id
			AND orderlang_lang_id = ' . $langId,
                'o_l'
            );
        }
        return $srch;
    }

    public static function getOrderProductSearchObject($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL_ORDER_PRODUCTS, 'op');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_ORDER_PRODUCTS_LANG,
                'LEFT OUTER JOIN',
                'oplang_op_id = op.op_id AND oplang_lang_id = ' . $langId,
                'op_l'
            );
        }
        return $srch;
    }


    public function addUpdateOrder($data = array(), $langId = 1)
    {
        $orderType=  $data['order_type'];
        if ($orderType == Orders::ORDER_SUBSCRIPTION) {
            return $this->addUpdateSubscriptionOrder($data, $langId);
        } elseif ($orderType == Orders::ORDER_PRODUCT) {
            return $this->addUpdateProductOrder($data, $langId);
        } elseif ($orderType == Orders::ORDER_WALLET_RECHARGE) {
            return $this->addUpdateWalletRechargeOrder($data, $langId);
        }
    }

    private function addUpdateProductOrder($data = array(), $langId = 1)
    {
        $db  = FatApp::getDb();

        $ordersLangData = $data['orderLangData'];
        unset($data['orderLangData']);

        $discountInfo = array();
        if (array_key_exists('order_discount_info', $data)) {
            $discountInfo = json_decode($data['order_discount_info'], true);
        }

        $products = $data['products'];
        unset($data['products']);

        $addresses = $data['userAddresses'];
        unset($data['userAddresses']);

        $extras = $data['extra'];
        unset($data['extra']);

        $prodCharges = $data['prodCharges'];
        unset($data['prodCharges']);

        if (!$data['order_id']) {
            $order_id = $this->generateOrderId();
            $data['order_id'] = $order_id;
        }
        $this->order_id = $data['order_id'];

        if (array_key_exists('coupon_id', $discountInfo)) {
            $couponInfo = DiscountCoupons::getValidCoupons($data['order_user_id'], $data['order_language_id'], $data['order_discount_coupon_code'], $this->order_id);
            if ($couponInfo == false) {
                $this->error = Labels::getLabel('LBL_Invalid_Coupon_Code', $data['order_language_id']);
                return false;
            }

            $holdCouponData = array(
            'ochold_order_id'=>$this->getOrderId(),
            'ochold_coupon_id'=>$discountInfo['coupon_id'],
            'ochold_added_on'=>date('Y-m-d H:i:s')
            );
            if (!FatApp::getDb()->insertFromArray(DiscountCoupons::DB_TBL_COUPON_HOLD_PENDING_ORDER, $holdCouponData, true, array(), $holdCouponData)) {
                $db->rollbackTransaction();
                $this->error = FatApp::getDb()->getError();
                return false;
            }
        }


        $recordObj = new TableRecord(static::DB_TBL);
        $recordObj->assignValues($data);
        $flds_update_on_duplicate = $data;
        unset($flds_update_on_duplicate['order_id']);

        $db->startTransaction();
        if (!$recordObj->addNew(array(), $flds_update_on_duplicate)) {
            $db->rollbackTransaction();
            $this->error = $recordObj->getError();
            return false;
        }

        $_SESSION['shopping_cart']["order_id"] = $this->getOrderId();

        $db->deleteRecords(static::DB_TBL_LANG, array('smt' => 'orderlang_order_id = ?', 'vals' => array( $this->getOrderId() ) ));
        if (!empty($ordersLangData)) {
            $recordObj = new TableRecord(static::DB_TBL_LANG);
            foreach ($ordersLangData as $orderLangData) {
                $orderLangData['orderlang_order_id'] = $this->getOrderId();
                $recordObj->assignValues($orderLangData);
                if (!$recordObj->addNew()) {
                    $db->rollbackTransaction();
                    $this->error = $recordObj->getError();
                    return false;
                }
            }
        }

        $row = OrderProduct::getOpIdArrByOrderId($this->getOrderId());

        if (!empty($row)) {
            foreach ($row as $opId => $val) {
                $db->deleteRecords(OrderProduct::DB_TBL_CHARGES, array('smt' => OrderProduct::DB_TBL_CHARGES_PREFIX.'op_id = ?', 'vals' => array( $opId ) ));
                $db->deleteRecords(Orders::DB_TBL_ORDER_PRODUCTS_SHIPPING, array('smt' =>'opshipping_op_id = ?', 'vals' => array( $opId ) ));
                $db->deleteRecords(Orders::DB_TBL_ORDER_PRODUCTS_SHIPPING_LANG, array('smt' => 'opshippinglang_op_id = ?', 'vals' => array( $opId ) ));
            }
        }

        $db->deleteRecords(static::DB_TBL_ORDER_PRODUCTS, array('smt' => 'op_order_id = ?', 'vals' => array( $this->getOrderId() ) ));
        $db->deleteRecords(static::DB_TBL_ORDER_PRODUCTS_LANG, array('smt' => 'oplang_order_id = ?', 'vals' => array( $this->getOrderId() ) ));

        if (!empty($products)) {
            $opRecordObj = new TableRecord(static::DB_TBL_ORDER_PRODUCTS);
            $opLangRecordObj = new TableRecord(static::DB_TBL_ORDER_PRODUCTS_LANG);
            $opShippingRecordObj = new TableRecord(static::DB_TBL_ORDER_PRODUCTS_SHIPPING);
            $opShippingLangRecordObj = new TableRecord(static::DB_TBL_ORDER_PRODUCTS_SHIPPING_LANG);

            $counter = 1;
            foreach ($products as $selprodId => $product) {
                $op_invoice_number = $this->getOrderId().'-S'.str_pad($counter, 4, '0', STR_PAD_LEFT);
                $product['op_order_id'] = $this->getOrderId();
                $product['op_invoice_number'] = $op_invoice_number;
                $opRecordObj->assignValues($product);
                if (!$opRecordObj->addNew()) {
                    $db->rollbackTransaction();
                    $this->error = $opRecordObj->getError();
                    return false;
                }

                $op_id = $opRecordObj->getId();

                /*Save order product settings [*/
                $orderProduct = new OrderProduct($op_id);
                if (!$orderProduct->setupSettings()) {
                    $db->rollbackTransaction();
                    return false;
                }
                /*]*/

                /* saving of products lang data[ */
                $productsLangData = $product['productsLangData'];
                if (!empty($productsLangData)) {
                    foreach ($productsLangData as $productLangData) {
                        $productLangData['oplang_op_id'] = $op_id;
                        $productLangData['oplang_order_id'] = $this->getOrderId();
                        $opLangRecordObj->assignValues($productLangData);
                        if (!$opLangRecordObj->addNew()) {
                            $db->rollbackTransaction();
                            $this->error = $opLangRecordObj->getError();
                            return false;
                        }
                    }
                }

                /* Saving of digital download data[ */
                if ($product['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
                    $db->deleteRecords(
                        AttachedFile::DB_TBL,
                        array(
                        'smt' => 'afile_type = ? AND afile_record_id = ?',
                        'vals' => array(AttachedFile::FILETYPE_ORDER_PRODUCT_DIGITAL_DOWNLOAD, $op_id)
                        )
                    );
                    $attachments = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_SELLER_PRODUCT_DIGITAL_DOWNLOAD, $product['op_selprod_id'], 0, -1);
                    if (!empty($attachments)) {
                        foreach ($attachments as $digitalFile) {
                            unset($digitalFile['afile_id']);
                            $digitalFile['afile_record_id'] = $op_id;
                            $digitalFile['afile_type'] = AttachedFile::FILETYPE_ORDER_PRODUCT_DIGITAL_DOWNLOAD;
                            if (!$db->insertFromArray(AttachedFile::DB_TBL, $digitalFile)) {
                                $db->rollbackTransaction();
                                $this->error = $opLangRecordObj->getError();
                                return false;
                            }
                        }
                    }
                }
                /*]*/

                /* Saving of digital download Links[ */
                if ($product['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
                    $linkData = array();
                    $sellerProduct = SellerProduct::getAttributesById($product['op_selprod_id'], array('selprod_downloadable_link'), false);
                    $downlodableLinks = preg_split("/\n|,/", $sellerProduct['selprod_downloadable_link']);
                    foreach ($downlodableLinks as $link) {
                        if ($link == '') {
                            continue;
                        }
                        $linkData['opddl_op_id'] = $op_id;
                        $linkData['opddl_downloadable_link'] = $link;
                        if (!$db->insertFromArray(OrderProductDigitalLinks::DB_TBL, $linkData)) {
                            $db->rollbackTransaction();
                            $this->error = $opLangRecordObj->getError();
                            return false;
                        }
                    }
                }
                /*]*/

                /* saving of products Shipping data[ */
                $productsShippingData = $product['productShippingData'];
                if (!empty($productsShippingData)) {
                    $productsShippingData['opshipping_op_id'] = $op_id;

                    $opShippingRecordObj->assignValues($productsShippingData);
                    if (!$opShippingRecordObj->addNew()) {
                        $db->rollbackTransaction();
                        $this->error = $opShippingRecordObj->getError();
                        return false;
                    }
                }
                /*]*/

                /* saving of products Shipping lang data[ */
                $productsShippingLangData = $product['productShippingLangData'];
                if (!empty($productsShippingLangData)) {
                    foreach ($productsShippingLangData as $productShippingLangData) {
                        $productShippingLangData['opshippinglang_op_id'] = $op_id;
                        $opShippingLangRecordObj->assignValues($productShippingLangData);
                        if (!$opShippingLangRecordObj->addNew()) {
                            $db->rollbackTransaction();
                            $this->error = $opShippingLangRecordObj->getError();
                            return false;
                        }
                    }
                }
                /*]*/

                if (!empty($prodCharges)) {
                    $chargeTypeArr = OrderProduct::getChargeTypeArr($langId);
                    $oChargesRecordObj = new TableRecord(OrderProduct::DB_TBL_CHARGES);
                    foreach ($chargeTypeArr as $chargeType => $chargeVal) {
                        if (!array_key_exists($selprodId, $prodCharges)) {
                            continue;
                        }

                        if (!array_key_exists($chargeType, $prodCharges[$selprodId])) {
                            continue;
                        }

                        $amnt = $prodCharges[$selprodId][$chargeType]['amount'];
                        if ($amnt == 0) {
                            continue;
                        }

                        $assignValues = array(
                        OrderProduct::DB_TBL_CHARGES_PREFIX.'op_id' => $op_id,
                        OrderProduct::DB_TBL_CHARGES_PREFIX.'order_type' => ORDERS::ORDER_PRODUCT,
                        OrderProduct::DB_TBL_CHARGES_PREFIX.'type' => $chargeType,
                        OrderProduct::DB_TBL_CHARGES_PREFIX.'amount' => $prodCharges[$selprodId][$chargeType]['amount'],
                        );

                        $oChargesRecordObj->assignValues($assignValues);
                        if (!$oChargesRecordObj->addNew(array())) {
                            $db->rollbackTransaction();
                            $this->error = $oChargesRecordObj->getError();
                            return false;
                        }
                    }
                }
                /* ] */

                $counter++;
            }
        }
        /* CommonHelper::printArray($addresses);die; */
        $db->deleteRecords(static::DB_TBL_ORDER_USER_ADDRESS, array('smt' => 'oua_order_id = ?', 'vals' => array( $this->getOrderId() ) ));
        if (!empty($addresses)) {
            $ouaRecordObj = new TableRecord(static::DB_TBL_ORDER_USER_ADDRESS);
            foreach ($addresses as $address) {
                $address['oua_order_id'] = $this->getOrderId();
                $ouaRecordObj->assignValues($address);
                if (!$ouaRecordObj->addNew()) {
                    $db->rollbackTransaction();
                    $this->error = $ouaRecordObj->getError();
                    return false;
                }
            }
        }

        if (!empty($extras)) {
            $oextraRecordObj = new TableRecord(static::DB_TBL_ORDER_EXTRAS);
            $extras['oextra_order_id'] = $this->getOrderId();
            $flds_update_on_duplicate = $extras;
            unset($flds_update_on_duplicate['oextra_order_id']);
            $oextraRecordObj->assignValues($extras);
            if (!$oextraRecordObj->addNew(array(), $flds_update_on_duplicate)) {
                $db->rollbackTransaction();
                $this->error = $oextraRecordObj->getError();
                return false;
            }
        }

        $db->commitTransaction();
        return $this->getOrderId();
    }

    private function addUpdateSubscriptionOrder($data = array(), $langId = 1)
    {
        $db  = FatApp::getDb();
        $ordersLangData = $data['orderLangData'];
        unset($data['orderLangData']);
        $subscriptions = $data['subscriptions'];
        unset($data['subscriptions']);
        $extras = $data['extra'];
        unset($data['extra']);
        $subscrCharges = $data['subscrCharges'];
        unset($data['subscrCharges']);

        if (!$data['order_id']) {
            $order_id = $this->generateOrderId();
            $data['order_id'] = $order_id;
        }
        $this->order_id = $data['order_id'];

        $recordObj = new TableRecord(static::DB_TBL);
        $recordObj->assignValues($data);
        $flds_update_on_duplicate = $data;
        unset($flds_update_on_duplicate['order_id']);

        $db->startTransaction();
        if (!$recordObj->addNew(array(), $flds_update_on_duplicate)) {
            $db->rollbackTransaction();
            $this->error = $recordObj->getError();
            return false;
        }

        $_SESSION['subscription_shopping_cart']["order_id"] = $this->getOrderId();

        $db->deleteRecords(static::DB_TBL_LANG, array('smt' => 'orderlang_order_id = ?', 'vals' => array( $this->getOrderId() ) ));
        if (!empty($ordersLangData)) {
            $recordObj = new TableRecord(static::DB_TBL_LANG);
            foreach ($ordersLangData as $orderLangData) {
                $orderLangData['orderlang_order_id'] = $this->getOrderId();
                $recordObj->assignValues($orderLangData);
                if (!$recordObj->addNew()) {
                    $db->rollbackTransaction();
                    $this->error = $recordObj->getError();
                    return false;
                }
            }
        }

        $row = OrderSubscription::getOSSubIdArrByOrderId($this->getOrderId());
        if (!empty($row)) {
            foreach ($row as $opId => $val) {
                $db->deleteRecords(OrderProduct::DB_TBL_CHARGES, array('smt' => OrderProduct::DB_TBL_CHARGES_PREFIX.'op_id = ?  and '.OrderProduct::DB_TBL_CHARGES_PREFIX.'order_type =?', 'vals' => array( $opId ,Orders::ORDER_SUBSCRIPTION) ));
            }
        }

        $db->deleteRecords(
            OrderSubscription::DB_TBL,
            array('smt' => OrderSubscription::DB_TBL_PREFIX.'order_id = ?',
            'vals' => array( $this->getOrderId()
                            )
            )
        );
        $db->deleteRecords(
            OrderSubscription::DB_TBL_LANG,
            array('smt' => OrderSubscription::DB_TBL_LANG_PREFIX.'order_id = ?',
                    'vals' => array( $this->getOrderId()
                                    )
            )
        );

        if (!empty($subscriptions)) {
            $opRecordObj = new TableRecord(OrderSubscription::DB_TBL);
            $opLangRecordObj = new TableRecord(OrderSubscription::DB_TBL_LANG);

            $counter = 1;
            foreach ($subscriptions as $spPlanId => $subscription) {
                $op_invoice_number = $this->getOrderId().'-S'.str_pad($counter, 4, '0', STR_PAD_LEFT);
                $subscription[OrderSubscription::DB_TBL_PREFIX.'order_id'] = $this->getOrderId();
                $subscription[OrderSubscription::DB_TBL_PREFIX.'invoice_number'] = $op_invoice_number;
                $opRecordObj->assignValues($subscription);
                if (!$opRecordObj->addNew()) {
                    $db->rollbackTransaction();
                    $this->error = $opRecordObj->getError();
                    return false;
                }

                $oss_id = $opRecordObj->getId();

                /* saving of products lang data[ */
                $subscriptionsLangData = $subscription['subscriptionsLangData'];
                if (!empty($subscriptionsLangData)) {
                    foreach ($subscriptionsLangData as $subscriptionLangData) {
                        $subscriptionLangData[OrderSubscription::DB_TBL_LANG_PREFIX.'ossubs_id'] = $oss_id;
                        $subscriptionLangData[OrderSubscription::DB_TBL_LANG_PREFIX.'order_id'] = $this->getOrderId();

                        $opLangRecordObj->assignValues($subscriptionLangData);
                        if (!$opLangRecordObj->addNew()) {
                            $db->rollbackTransaction();

                            $this->error = $opLangRecordObj->getError();
                            return false;
                        }
                    }
                }

                if (!empty($subscrCharges)) {
                    $chargeTypeArr = OrderSubscription::getChargeTypeArr($langId);

                    $oChargesRecordObj = new TableRecord(OrderProduct::DB_TBL_CHARGES);
                    foreach ($chargeTypeArr as $chargeType => $chargeVal) {
                        if (!array_key_exists($chargeType, $subscrCharges[$spPlanId])) {
                            continue;
                        }

                        $amnt = $subscrCharges[$spPlanId][$chargeType]['amount'];
                        if ($amnt == 0) {
                            continue;
                        }

                        $assignValues = array(
                        OrderProduct::DB_TBL_CHARGES_PREFIX.'op_id' => $oss_id,
                        OrderProduct::DB_TBL_CHARGES_PREFIX.'order_type' => Orders::ORDER_SUBSCRIPTION,
                        OrderProduct::DB_TBL_CHARGES_PREFIX.'type' => $chargeType,
                        OrderProduct::DB_TBL_CHARGES_PREFIX.'amount' => $subscrCharges[$spPlanId][$chargeType]['amount'],
                        );

                        $oChargesRecordObj->assignValues($assignValues);
                        if (!$oChargesRecordObj->addNew(array())) {
                            $db->rollbackTransaction();
                            $this->error = $oChargesRecordObj->getError();
                            return false;
                        }
                    }
                }
                /* ] */
                $counter++;
            }
        }

        if (!empty($extras)) {
            $oextraRecordObj = new TableRecord(static::DB_TBL_ORDER_EXTRAS);
            $extras['oextra_order_id'] = $this->getOrderId();
            $flds_update_on_duplicate = $extras;
            unset($flds_update_on_duplicate['oextra_order_id']);
            $oextraRecordObj->assignValues($extras);
            if (!$oextraRecordObj->addNew(array(), $flds_update_on_duplicate)) {
                $db->rollbackTransaction();
                $this->error = $oextraRecordObj->getError();
                return false;
            }
        }

        $db->commitTransaction();

        return $this->getOrderId();
    }

    private function addUpdateWalletRechargeOrder($data, $langId)
    {
        $db  = FatApp::getDb();
        $ordersLangData = $data['orderLangData'];
        unset($data['orderLangData']);

        $userAddresses = $data['userAddresses'];
        unset($data['userAddresses']);

        $extras = $data['extra'];
        unset($data['extra']);

        if (!$data['order_id']) {
            $order_id = $this->generateOrderId();
            $data['order_id'] = $order_id;
        }
        $this->order_id = $data['order_id'];

        $recordObj = new TableRecord(static::DB_TBL);
        $recordObj->assignValues($data);
        $flds_update_on_duplicate = $data;
        unset($flds_update_on_duplicate['order_id']);

        $db->startTransaction();
        if (!$recordObj->addNew(array(), $flds_update_on_duplicate)) {
            $db->rollbackTransaction();
            $this->error = $recordObj->getError();
            return false;
        }

        $_SESSION['wallet_recharge_cart']["order_id"] = $this->getOrderId();

        $db->deleteRecords(static::DB_TBL_LANG, array('smt' => 'orderlang_order_id = ?', 'vals' => array( $this->getOrderId() ) ));
        if (!empty($ordersLangData)) {
            $recordObj = new TableRecord(static::DB_TBL_LANG);
            foreach ($ordersLangData as $orderLangData) {
                $orderLangData['orderlang_order_id'] = $this->getOrderId();
                $recordObj->assignValues($orderLangData);
                if (!$recordObj->addNew()) {
                    $db->rollbackTransaction();
                    $this->error = $recordObj->getError();
                    return false;
                }
            }
        }

        if (!empty($extras)) {
            $oextraRecordObj = new TableRecord(static::DB_TBL_ORDER_EXTRAS);
            $extras['oextra_order_id'] = $this->getOrderId();
            $flds_update_on_duplicate = $extras;
            unset($flds_update_on_duplicate['oextra_order_id']);
            $oextraRecordObj->assignValues($extras);
            if (!$oextraRecordObj->addNew(array(), $flds_update_on_duplicate)) {
                $db->rollbackTransaction();
                $this->error = $oextraRecordObj->getError();
                return false;
            }
        }
        $db->commitTransaction();
        return $this->getOrderId();
    }

    public function getOrderById($order_id, $langId = 0)
    {
        if (!$order_id) {
            trigger_error(Labels::getLabel('MSG_Order_Id_Is_Not_Passed', $this->commonLangId), E_USER_ERROR);
        }
        $srch = static::getSearchObject($langId);
        $srch->addCondition('order_id', '=', $order_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        return $row;
    }

    public function getOrderAddresses($order_id)
    {
        $srch = new SearchBase(static::DB_TBL_ORDER_USER_ADDRESS);
        $srch->addCondition('oua_order_id', '=', $order_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAll($rs, 'oua_type');
        return $row;
    }

    public function addOrderHistory($order_id, $order_status_id, $comment = '', $notify = false, $langId)
    {
        $notify = FatUtility::int($notify);
        $langId = FatUtility::int($langId);
        $order_status_id = FatUtility::int($order_status_id);
        $orderInfo = $this->getOrderById($order_id, $langId);
        if (!$orderInfo) {
            $this->error = Labels::getLabel('MSG_Error_in_updating_the_order,_Please_try_after_some_time.', $langId);
            return false;
        }

        if (!$langId) {
            trigger_error(Labels::getLabel('MSG_Language_Not_Specified', $this->commonLangId), E_USER_ERROR);
        }

        $db = FatApp::getDb();

        if (!$db->updateFromArray(
            static::DB_TBL,
            array('order_status' => $order_status_id,'order_date_updated' => date('Y-m-d H:i:s')),
            array('smt' => 'order_id = ? ', 'vals' => array($orderInfo["order_id"])),
            true
        )) {
            $this->error = $db->db->getError();
            return false;
        }

        $data_to_save_arr = array(
        'oshistory_order_id'    =>    $orderInfo["order_id"],
        'oshistory_op_id'        =>    0,
        'oshistory_orderstatus_id'    =>    $order_status_id,
        'oshistory_order_payment_status' => 0,
        'oshistory_date_added'    =>    date('Y-m-d H:i:s'),
        'oshistory_customer_notified'    =>    $notify,
        'oshistory_tracking_number'    =>    '',
        'oshistory_comments'    =>    $comment
        );
        if (!$db->insertFromArray(static::DB_TBL_ORDER_STATUS_HISTORY, $data_to_save_arr, true)) {
            $this->error = $db->getError();
            return false;
        }

        // If order status is 0 then becomes greater than 0 send main html email
        if (!$orderInfo['order_status'] && $order_status_id) {
            $emailObj = new EmailHandler();
            $emailObj->newOrderBuyerAdmin($orderInfo["order_id"], $langId);
        }
        return true;
    }

    public function getChildOrders($criterias, $orderType = Orders::ORDER_PRODUCT, $langId = 0)
    {
        $langId = FatUtility::int($langId);

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(
            array(
            OrderProduct::DB_TBL_CHARGES_PREFIX.'op_id',
            'sum('.OrderProduct::DB_TBL_CHARGES_PREFIX.'amount) as op_other_charges')
        );

        $ocSrch->addGroupBy('opc.'.OrderProduct::DB_TBL_CHARGES_PREFIX.'op_id');
        $qryOtherCharges = $ocSrch->getQuery();
        if ($orderType == Orders::ORDER_PRODUCT) {
            $srch = self::searchOrderProducts($criterias, $langId);
            $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.'.OrderProduct::DB_TBL_CHARGES_PREFIX.'op_id', 'opcc');
            $srch->joinTable(OrderProduct::DB_TBL_OP_TO_SHIPPING_USERS, 'LEFT OUTER JOIN', 'optosu.optsu_op_id = op.op_id', 'optosu');
            $srch->joinTable(Orders::DB_TBL_ORDER_PRODUCTS_SHIPPING, 'LEFT OUTER JOIN', 'ops.opshipping_op_id = op.op_id', 'ops');
            $srch->joinTable(OrderProduct::DB_TBL_SETTINGS, 'LEFT OUTER JOIN', 'op.op_id = opst.opsetting_op_id', 'opst');
            $srch->addOrder("op_id", "desc");
            $rs = $srch->getResultSet();
            $childOrders = array();

            $oObj = new Orders();
            while ($row = FatApp::getDb()->fetch($rs)) {
                $childOrders[$row['op_id']] = $row;

                $charges = $oObj->getOrderProductChargesArr($row['op_id']);
                $childOrders[$row['op_id']]['charges'] = $charges;
            };
        } elseif ($orderType == Orders::ORDER_SUBSCRIPTION) {
            $srch = OrderSubscription::searchOrderSubscription($criterias, $langId);
            $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'oss.'.OrderSubscription::DB_TBL_PREFIX.'id = opcc.'.OrderProduct::DB_TBL_CHARGES_PREFIX.'op_id', 'opcc');

            $srch->addOrder(OrderSubscription::DB_TBL_PREFIX."id", "desc");
            $rs = $srch->getResultSet();
            $childOrders = array();

            $osObj = new OrderSubscription();
            while ($row = FatApp::getDb()->fetch($rs)) {
                $childOrders[$row[OrderSubscription::DB_TBL_PREFIX.'id']] = $row;

                $charges = $osObj->getOrderSubscriptionChargesArr($row[OrderSubscription::DB_TBL_PREFIX.'id']);
                $childOrders[$row[OrderSubscription::DB_TBL_PREFIX.'id']]['charges'] = $charges;
            }
        }

        return $childOrders;
    }

    public function getOrderComments($langId, $criteria = array(), $pagesize = 0)
    {
        if (count($criteria)==0) {
            return array();
        }

        $langId = FatUtility::int($langId);

        $srch = new SearchBase(Orders::DB_TBL_ORDER_STATUS_HISTORY, 'tosh');
        $srch->joinTable(Orders::DB_TBL_ORDERS_STATUS, 'LEFT OUTER JOIN', 'tosh.oshistory_orderstatus_id = tos.orderstatus_id', 'tos');
        $srch->addCondition('orderstatus_type', '=', Orders::ORDER_PRODUCT);
        if ($langId > 0) {
            $srch->joinTable(Orders::DB_TBL_ORDERS_STATUS_LANG, 'LEFT OUTER JOIN', 'tos_l.orderstatuslang_orderstatus_id = tos.orderstatus_id and tos_l.orderstatuslang_lang_id = '.$langId, 'tos_l');
            $srch->addMultipleFields(array('IFNULL(orderstatus_name,orderstatus_identifier) as orderstatus_name'));
        }

        $srch->joinTable(Orders::DB_TBL_ORDER_PRODUCTS, 'LEFT OUTER JOIN', 'torp.op_id = tosh.oshistory_op_id', 'torp');

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array(OrderProduct::DB_TBL_CHARGES_PREFIX.'op_id','sum('.OrderProduct::DB_TBL_CHARGES_PREFIX.'amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.'.OrderProduct::DB_TBL_CHARGES_PREFIX.'op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'torp.op_id = opcc.'.OrderProduct::DB_TBL_CHARGES_PREFIX.'op_id', 'opcc');
        if ($langId > 0) {
            $srch->joinTable(Orders::DB_TBL_ORDER_PRODUCTS_LANG, 'LEFT OUTER JOIN', 'torp_l.oplang_op_id = torp.op_id and torp_l.oplang_lang_id = '.$langId, 'torp_l');
            $srch->addMultipleFields(array('op_product_name','op_selprod_options','op_brand_name','op_shop_name','op_other_charges','op_shipping_duration_name'));
        }

        $srch->joinTable(Orders::DB_TBL, 'LEFT OUTER JOIN', 'torp.op_order_id = tor.order_id', 'tor');
        if ($langId > 0) {
            $srch->joinTable(Orders::DB_TBL_LANG, 'LEFT OUTER JOIN', 'tor_l.orderlang_order_id = tor.order_id and tor_l.orderlang_lang_id = '.$langId, 'tor_l');
            $srch->addMultipleFields(array('order_shippingapi_name'));
        }

        $srch->joinTable(OrderProduct::DB_TBL_OP_TO_SHIPPING_USERS, 'LEFT OUTER JOIN', 'optosu.optsu_op_id = torp.op_id', 'optosu');
        $srch->joinTable(Orders::DB_TBL_ORDER_PRODUCTS_SHIPPING, 'LEFT OUTER JOIN', 'ops.opshipping_op_id = torp.op_id', 'ops');
        $srch->joinTable(ShippingCompanies::DB_TBL, 'LEFT OUTER JOIN', 'ops.opshipping_company_id = opsc.scompany_id', 'opsc');
        $srch->joinTable(ShippingCompanies::DB_LANG_TBL, 'LEFT OUTER JOIN', 'opscl.scompanylang_scompany_id = opsc.scompany_id', 'opscl');
        $srch->addMultipleFields(array('opshipping_by_seller_user_id,opshipping_company_id','IFNULL(scompany_name, scompany_identifier) as scompany_name'));

        if (isset($criteria['seller_id'])) {
            $srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 's.user_id = torp.op_selprod_user_id', 's');
            $srch->joinTable('tbl_user_credentials', 'LEFT OUTER JOIN', 'sc.credential_user_id = s.user_id', 'sc');
            $srch->addMultipleFields(array('s.user_name as seller_name','sc.credential_email as seller_email'));
        }

        if (isset($criteria['buyer_id'])) {
            $srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'b.user_id = tor.order_user_id', 'b');
            $srch->joinTable('tbl_user_credentials', 'LEFT OUTER JOIN', 'bc.credential_user_id = b.user_id', 'bc');
            $srch->addMultipleFields(array('b.user_name as buyer_name','bc.credential_email as buyer_email'));
        }

        $srch->addMultipleFields(array('tosh.*','tor.order_is_paid','order_language_id','torp.*','torp.op_id' ));

        foreach ($criteria as $key => $val) {
            if (strval($val)=='') {
                continue;
            }
            switch ($key) {
                case 'id':
                    $srch->addCondition('tosh.oshistory_id', '=', intval($val));
                    break;
                case 'order_id':
                    $srch->addCondition('tosh.oshistory_order_id', '=', $val);
                    break;
                case 'op_id':
                    $srch->addCondition('tosh.oshistory_op_id', '=', intval($val));
                    break;
                case 'seller_id':
                    $srch->addCondition('torp.op_selprod_user_id', '=', intval($val));
                    break;
                case 'buyer_id':
                    $srch->addCondition('tor.order_user_id', '=', intval($val));
                    break;
            }
        }

        if (intval($pagesize)>0) {
            $srch->setPageSize($pagesize);
        } else {
            $srch->doNotLimitRecords();
        }
        $srch->doNotCalculateRecords(true);
        $srch->addOrder('oshistory_date_added', 'desc');
        $srch->addOrder('oshistory_orderstatus_id');
        $srch->addGroupBy('oshistory_id');

        $rs = $srch->getResultSet();
        if (!$rs) {
            return false;
        }
        return ($pagesize==1)?FatApp::getDb()->fetch($rs):FatApp::getDb()->fetchAll($rs);
    }

    public function addOrderPaymentHistory($orderId, $orderPaymentStatus, $comment = '', $notify = false)
    {
        $orderInfo = $this->getOrderById($orderId);

        if ($orderInfo) {
            if (!FatApp::getDb()->updateFromArray(
                Orders::DB_TBL,
                array('order_is_paid' => FatUtility::int($orderPaymentStatus),'order_date_updated' => date('Y-m-d H:i:s')),
                array('smt' => 'order_id = ? ', 'vals' => array($orderId))
            )) {
                $this->error = FatApp::getDb()->getError();
                return false;
            }
        }

        if (!FatApp::getDb()->insertFromArray(Orders::DB_TBL_ORDER_STATUS_HISTORY, array('oshistory_order_id' => $orderId, 'oshistory_order_payment_status' => $orderPaymentStatus, 'oshistory_date_added' => date('Y-m-d H:i:s'),'oshistory_customer_notified' => FatUtility::int($notify),'oshistory_comments' => $comment))) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }

        if ($orderInfo['order_type']==ORDERS::ORDER_PRODUCT) {
            $this->addProductOrderPayment($orderId, $orderInfo, $orderPaymentStatus, $comment, $notify);
        } elseif ($orderInfo['order_type']==ORDERS::ORDER_SUBSCRIPTION) {
            $this->addSubscriptionOrderPayment($orderId, $orderInfo, $orderPaymentStatus, $comment, $notify);
        }

        return true;
    }
    public function addSubscriptionOrderPayment($orderId, $orderInfo, $orderPaymentStatus, $comment = '', $notify = false)
    {
        $emailObj = new EmailHandler();


        // If order Payment status is 0 then becomes greater than 0 mail to Vendors and Update Child Order Status to Paid & Give Referral Reward Points

        if (!$orderInfo['order_is_paid'] && ($orderPaymentStatus > 0)) {
            $subOrders = $this->getChildOrders(array("order"=>$orderId), $orderInfo['order_type']);

            $orderInfo = $this->getOrderById($orderId);

            foreach ($subOrders as $subkey => $subval) {
                $this->addChildSubscriptionOrderHistory($orderId, $subval[OrderSubscription::DB_TBL_PREFIX."id"], $orderInfo[Orders::DB_TBL_PREFIX.'language_id'], FatApp::getConfig("CONF_DEFAULT_SUBSCRIPTION_PAID_ORDER_STATUS"), '', true);



                // If order Payment status is 0 then becomes greater than 0 send main html email
                if ($orderPaymentStatus && !$orderInfo['order_renew']) {
                    //Add Commission settings for the user

                    $commissionId = 0;
                    $record = new Commission($commissionId);


                    $dataToSave     = array(
                    'commsetting_product_id'=>0,
                    'commsetting_prodcat_id'=>0,
                    'commsetting_user_id'=>$orderInfo["order_user_id"],
                    'commsetting_fees'=>$subval[OrderSubscription::DB_TBL_PREFIX."commission"],
                    'commsetting_by_package'=>applicationConstants::YES,
                    );
                    if (!$record->addUpdateData($dataToSave)) {
                        Message::addErrorMessage($record->getError());
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                    $insertId = $record->getMainTableRecordId();
                    if (!$insertId) {
                        $insertId = FatApp::getDb()->getInsertId();
                    }
                    /* if(!$insertId){
                    $insertId = Commission::getComissionSettingIdByUser($orderInfo["order_user_id"]);
                    } */
                    if ($insertId) {
                        if (!$record->addCommissionHistory($insertId)) {
                            Message::addErrorMessage($record->getError());
                            FatUtility::dieJsonError(Message::getHtml());
                        }
                    }


                    $emailObj->orderPurchasedSubscriptionEmail($orderId);
                } elseif ($orderPaymentStatus && $orderInfo['order_renew']) {
                    $emailObj->orderRenewSubscriptionEmail($orderId);
                }

                /* Use Reward Point [ */
                if ($orderInfo['order_reward_point_used'] > 0) {
                    UserRewards::debit($orderInfo['order_user_id'], $orderInfo['order_reward_point_used'], $orderId, $orderInfo['order_language_id']);
                }
                /*]*/
            }
        }
    }

    public function addChildSubscriptionOrderHistory($orderId, $ossubs_id, $langId, $opStatusId, $comment = '', $notify = false, $trackingNumber = '', $releasePayments = 0, $moveRefundToWallet = true)
    {
        $ossubs_id = FatUtility::int($ossubs_id);
        $langId = FatUtility::int($langId);
        $opStatusId = FatUtility::int($opStatusId);
        $orderSubObj = new OrderSubscription();

        $childOrderInfo = $orderSubObj->getOrderSubscriptionByOssubId($ossubs_id, $langId);
        if (empty($childOrderInfo)) {
            $this->error = Labels::getLabel("MSG_Invalid_Access", $langId);
            return false;
        }

        $db = FatApp::getDb();
        $emailNotificationObj = new EmailHandler();

        $currentActiveSubscrId = OrderSubscription:: getUserCurrentActivePlanDetails($langId, $childOrderInfo['order_user_id'], array(OrderSubscription::DB_TBL_PREFIX.'id' ));
        if ($currentActiveSubscrId) {
            $this->cancelCurrentActivePlan($orderId, $currentActiveSubscrId, $childOrderInfo['order_user_id'], $notify);
        }

        $planDetails = OrderSubscription::getAttributesById($childOrderInfo['ossubs_id']);

        $updateArr = array(
        OrderSubscription::DB_TBL_PREFIX.'status_id' => $opStatusId,
        OrderSubscription::DB_TBL_PREFIX.'from_date'=> date("Y-m-d"),
        OrderSubscription::DB_TBL_PREFIX.'till_date'=> CommonHelper::getValidTillDate($planDetails)
        );

        if (!$db->updateFromArray(
            OrderSubscription::DB_TBL,
            $updateArr,
            array('smt' => OrderSubscription::DB_TBL_PREFIX.'id = ? ', 'vals' => array($ossubs_id))
        )) {
            $this->error = $db->getError();
            return false;
        }

        if (!$db->insertFromArray(Orders::DB_TBL_ORDER_STATUS_HISTORY, array('oshistory_op_id' => $ossubs_id, 'oshistory_order_id'=>$orderId,'oshistory_orderstatus_id' => $opStatusId, 'oshistory_date_added' => date('Y-m-d H:i:s'),'oshistory_customer_notified' => (int)$notify,'oshistory_comments' => $comment), true)) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function cancelCurrentActivePlan($orderId, $ossubs_id, $userId, $notify = false)
    {
        $db = FatApp::getDb();
        $opStatusId =  FatApp::getConfig('CONF_DEFAULT_CANCEL_SUBSCRIPTION_ORDER_STATUS');
        $updateArr = array(
        OrderSubscription::DB_TBL_PREFIX.'status_id' =>$opStatusId,

        );

        if (!$db->updateFromArray(
            OrderSubscription::DB_TBL,
            $updateArr,
            array('smt' => OrderSubscription::DB_TBL_PREFIX.'id = ? ', 'vals' => array($ossubs_id))
        )) {
            $this->error = $db->getError();
            return false;
        }
        $orderInfo = $this->getOrderById($orderId);
        if ($orderInfo['order_renew']<1) {
            $emailNotificationObj = new EmailHandler();
            $emailNotificationObj->sendCancelSubscriptionNotification($userId, $orderInfo['order_language_id']);
        }
        $OldOrderInfo = OrderSubscription::getAttributesById($ossubs_id);
        if (!$db->insertFromArray(Orders::DB_TBL_ORDER_STATUS_HISTORY, array('oshistory_op_id' => $ossubs_id, 'oshistory_order_id'=>$orderId, 'oshistory_orderstatus_id' => $opStatusId,'oshistory_order_id' => $OldOrderInfo['ossubs_order_id'], 'oshistory_date_added' => date('Y-m-d H:i:s'),'oshistory_customer_notified' => (int)$notify,'oshistory_comments' => Labels::getLabel("LBL_Plan_Canceled", CommonHelper::getLangId())), true)) {
            $this->error = $db->getError();
            return false;
        }
    }

    public function addProductOrderPayment($orderId, $orderInfo, $orderPaymentStatus, $comment = '', $notify = false)
    {
        /* CommonHelper::printArray($orderInfo); die; */
        $emailObj = new EmailHandler();

        // If order Payment status is 0 then becomes greater than 0 send main html email
        if (!$orderInfo['order_is_paid'] && $orderPaymentStatus) {
            $emailNotify = $emailObj->orderPaymentUpdateBuyerAdmin($orderId);
        } elseif ($orderInfo['order_pmethod_id']==PaymentSettings::CASH_ON_DELIVERY) {
            $emailNotify = $emailObj->cashOnDeliveryOrderUpdateBuyerAdmin($orderId);
        }

        // If order Payment status is 0 then becomes greater than 0 mail to Vendors and Update Child Order Status to Paid & Give Referral Reward Points
        if (!$orderInfo['order_is_paid'] && ($orderPaymentStatus > 0)) {
            $emailObj->newOrderVendor($orderId);
            $emailObj->newOrderBuyerAdmin($orderId, $orderInfo['order_language_id']);

            $subOrders = $this->getChildOrders(array("order"=>$orderId), $orderInfo['order_type']);
            foreach ($subOrders as $subkey => $subval) {
                $this->addChildProductOrderHistory($subval["op_id"], $orderInfo['order_language_id'], FatApp::getConfig("CONF_DEFAULT_PAID_ORDER_STATUS"), '', true);
                if ($subval['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
                    $emailObj->newDigitalOrderBuyer($orderId, $subval["op_id"], $orderInfo['order_language_id']);
                }
            }

            $isReferrerRewarded = false;
            $isReferralRewarded = false;

            $paymentMethodRow = PaymentMethods::getAttributesById($orderInfo['order_pmethod_id']);

            /* Use Reward Point [ */
            if (strtolower($paymentMethodRow['pmethod_code']) != 'cashondelivery' && $orderInfo['order_reward_point_used'] > 0) {
                UserRewards::debit($orderInfo['order_user_id'], $orderInfo['order_reward_point_used'], $orderId, $orderInfo['order_language_id']);
            }
            /*]*/

            /* Reward Points to Referrer[ */
            if ($orderInfo['order_referrer_user_id'] && $orderInfo['order_referrer_reward_points']) {
                $rewardExpiryDate = '0000-00-00';
                $CONF_SALE_REFERRER_REWARD_POINTS_VALIDITY = FatApp::getConfig("CONF_SALE_REFERRER_REWARD_POINTS_VALIDITY", FatUtility::VAR_INT, 0);
                if ($CONF_SALE_REFERRER_REWARD_POINTS_VALIDITY > 0) {
                    $rewardExpiryDate = date('Y-m-d', strtotime('+'.$CONF_SALE_REFERRER_REWARD_POINTS_VALIDITY.' days'));
                }

                $rewardsRecord = new UserRewards();
                $urpComments = Labels::getLabel("LBL_Purchase_Reward_Points:_Your_Referral_{username}_placed_first_order.", CommonHelper::getLangId());
                $referralUserName = User::getAttributesById($orderInfo['order_user_id'], "user_name");
                $urpComments = str_replace("{username}", $referralUserName, $urpComments);

                $rewardsRecord->assignValues(
                    array(
                    'urp_user_id'            => $orderInfo['order_referrer_user_id'],
                    'urp_referral_user_id'    =>    $orderInfo['order_user_id'],
                    'urp_points'    =>    $orderInfo['order_referrer_reward_points'],
                    'urp_comments'    =>    $urpComments,
                    'urp_used'        =>    0,
                    'urp_date_expiry'    =>    $rewardExpiryDate
                    )
                );
                if ($rewardsRecord->save()) {
                    $isReferrerRewarded = true;
                    $urpId = $rewardsRecord->getMainTableRecordId();
                    $emailObj = new EmailHandler();
                    $emailObj->sendRewardPointsNotification(CommonHelper::getLangId(), $urpId);
                }
            }
            /* ] */

            /* Reward Point to Referral[ */
            if ($orderInfo['order_referrer_user_id'] && $orderInfo['order_referral_reward_points']) {
                $rewardExpiryDate = '0000-00-00';
                $CONF_SALE_REFERRAL_REWARD_POINTS_VALIDITY = FatApp::getConfig("CONF_SALE_REFERRAL_REWARD_POINTS_VALIDITY", FatUtility::VAR_INT, 0);
                if ($CONF_SALE_REFERRAL_REWARD_POINTS_VALIDITY > 0) {
                    $rewardExpiryDate = date('Y-m-d', strtotime('+'.$CONF_SALE_REFERRAL_REWARD_POINTS_VALIDITY.' days'));
                }

                $rewardsRecord = new UserRewards();
                $urpComments = Labels::getLabel("LBL_Purchase_Reward_Points:_[1st_purchase]_you_are_referral_of_{username}.", CommonHelper::getLangId());
                $referralUserName = User::getAttributesById($orderInfo['order_referrer_user_id'], "user_name");
                $urpComments = str_replace("{username}", $referralUserName, $urpComments);

                $rewardsRecord->assignValues(
                    array(
                    'urp_user_id'            => $orderInfo['order_user_id'],
                    'urp_referral_user_id'    =>    $orderInfo['order_referrer_user_id'],
                    'urp_points'    =>    $orderInfo['order_referral_reward_points'],
                    'urp_comments'    =>    $urpComments,
                    'urp_used'        =>    0,
                    'urp_date_expiry'    =>    $rewardExpiryDate
                    )
                );
                if ($rewardsRecord->save()) {
                    $isReferralRewarded = true;
                    $urpId = $rewardsRecord->getMainTableRecordId();
                    $emailObj = new EmailHandler();
                    $emailObj->sendRewardPointsNotification(CommonHelper::getLangId(), $urpId);
                }
            }
            /* ] */

            /* remove referrer checkout cookie, becoz, Referrer and Referral are rewarded[ */
            if ($isReferrerRewarded || $isReferralRewarded) {
                setcookie('referrer_code_checkout', '', time() - 3600, CONF_WEBROOT_URL, '', false, true);
            }
            /* ] */
        }

        // If order Payment status is 0 then becomes less than 0 send mail to Vendors and Update Child Order Status to Cancelled
        if (!$orderInfo['order_is_paid'] && ($orderPaymentStatus < 0)) {
            $subOrders = $this->getChildOrders(array("order"=>$orderId), $orderInfo['order_type']);
            foreach ($subOrders as $subkey => $subval) {
                $this->addChildProductOrderHistory($subval["op_id"], $orderInfo['order_language_id'], FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS"), '', true);
            }
        }
    }


    public function addChildProductOrderHistory($op_id, $langId, $opStatusId, $comment = '', $notify = false, $trackingNumber = '', $releasePayments = 0, $moveRefundToWallet = true)
    {
        $op_id = FatUtility::int($op_id);
        $langId = FatUtility::int($langId);
        $opStatusId = FatUtility::int($opStatusId);

        $childOrderInfo = $this->getOrderProductsByOpId($op_id, $langId);
        if (empty($childOrderInfo)) {
            $this->error = Labels::getLabel("MSG_Invalid_Access", $langId);
            return false;
        }
        $db = FatApp::getDb();
        $emailNotificationObj = new EmailHandler();

        if (!$db->updateFromArray(
            Orders::DB_TBL_ORDER_PRODUCTS,
            array('op_status_id' => $opStatusId),
            array('smt' => 'op_id = ? ', 'vals' => array($op_id))
        )) {
            $this->error = $db->getError();
            return false;
        }

        if (!$db->insertFromArray(Orders::DB_TBL_ORDER_STATUS_HISTORY, array('oshistory_op_id' => $op_id, 'oshistory_orderstatus_id' => $opStatusId, 'oshistory_date_added' => date('Y-m-d H:i:s'),'oshistory_customer_notified' => (int)$notify,'oshistory_comments' => $comment,'oshistory_tracking_number'=>$trackingNumber), true)) {
            $this->error = $db->getError();
            return false;
        }
        $commentId = $db->getInsertId();

        // If current order status is not paid up but new status is paid then commence updating the product's weightage
        if (!in_array($childOrderInfo['op_status_id'], (array)FatApp::getConfig("CONF_DEFAULT_PAID_ORDER_STATUS")) && in_array($opStatusId, (array)FatApp::getConfig("CONF_DEFAULT_PAID_ORDER_STATUS")) && strtolower($childOrderInfo['pmethod_code']) != "cashondelivery") {
            if ($childOrderInfo['op_is_batch']) {
                $opSelprodCodeArr = explode('|', $childOrderInfo['op_selprod_code']);
            } else {
                $opSelprodCodeArr = array($childOrderInfo['op_selprod_code']);
            }

            foreach ($opSelprodCodeArr as $opSelprodCode) {
                if (empty($opSelprodCode)) {
                    continue;
                }
                Product::recordProductWeightage($opSelprodCode, SmartWeightageSettings::PRODUCT_ORDER_PAID);
            }

            if (CommonHelper::canAvailShippingChargesBySeller($childOrderInfo['op_selprod_user_id'], $childOrderInfo['opshipping_by_seller_user_id'])) {
                $shippingUserdata = array('optsu_op_id' => $childOrderInfo['op_id'], 'optsu_user_id' => $childOrderInfo['op_selprod_user_id']);
                $db->insertFromArray(OrderProduct::DB_TBL_OP_TO_SHIPPING_USERS, $shippingUserdata, false, array('IGNORE'));
            }
        }

        /* If current order status is not processing or complete but new status is processing or complete then commence completing the order [ */
        $arr = array_merge(
            unserialize(FatApp::getConfig("CONF_PROCESSING_ORDER_STATUS")),
            unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS"))
        );

        if (!in_array($childOrderInfo['op_status_id'], $arr) && in_array($opStatusId, array_diff($arr, array(FatApp::getConfig("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS"))))) {
            $selProdIdArr = array();
            if ($childOrderInfo['op_is_batch']) {
                $selProdIdArr = explode('|', $childOrderInfo['op_batch_selprod_id']);
            } else {
                $selProdIdArr = array($childOrderInfo['op_selprod_id']);
            }

            foreach ($selProdIdArr as $opSelprodId) {
                if (empty($opSelprodId)) {
                    continue;
                }

                /* Stock subtraction */

                $db->query("UPDATE tbl_seller_products SET selprod_stock = (selprod_stock - " . (int)$childOrderInfo['op_qty'] . "),selprod_sold_count = (selprod_sold_count + " . (int)$childOrderInfo['op_qty'] . ") WHERE selprod_id = '" . (int)$opSelprodId . "' AND selprod_subtract_stock = '1'");

                $sellProdInfo = SellerProduct::getAttributesById($opSelprodId, array('selprod_stock','selprod_subtract_stock','selprod_track_inventory','selprod_threshold_stock_level'));
                if (($sellProdInfo["selprod_threshold_stock_level"] >= $sellProdInfo["selprod_stock"]) && ($sellProdInfo["selprod_track_inventory"] == 1)) {
                    $emailNotificationObj->sendProductStockAlert($opSelprodId);
                }
            }

            /* if ( FatApp::getConfig("CONF_ALLOW_REVIEWS") ){
            $emailNotificationObj->sendBuyerReviewNotification( $childOrderInfo['op_id'] , $childOrderInfo['order_language_id'] );
            } */
        }
        /* ] */

        /* If old order status is the processing or complete status but new status is not then commence restock, and remove coupon, voucher and reward history [ */
        if (in_array($childOrderInfo['op_status_id'], array_merge($arr, array(FatApp::getConfig("CONF_RETURN_REQUEST_ORDER_STATUS")))) && in_array($opStatusId, array(FatApp::getConfig("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS"),FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")))) {
            // ReStock subtraction can work manually
            /* foreach($selProdIdArr as $opSelprodId){
            if(empty($opSelprodId)) { continue; }
            $db->query("UPDATE tbl_seller_products SET selprod_stock = (selprod_stock + " . (int)$childOrderInfo['op_qty'] . "),selprod_sold_count = (selprod_sold_count - " . (int)$childOrderInfo['op_qty'] . ") WHERE selprod_id = '" . (int)$opSelprodId . "' AND selprod_subtract_stock = '1'");
            }*/
        }
        /* ] */

        /* If current order status is not cancelled but new status is cancelled then commence cancelling the order [ */
        if (($childOrderInfo['op_status_id'] != FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")) && ($opStatusId == FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")) && ($childOrderInfo["order_is_paid"] == Orders::ORDER_IS_PAID)) {
            if ($moveRefundToWallet) {
                /* CommonHelper::printArray($childOrderInfo); die; */
                $formattedRequestValue = "#".$childOrderInfo["op_invoice_number"];
                $retReqObj = new OrderCancelRequest();
                $returnRequestDetail = $retReqObj->getCancelRequestById($childOrderInfo['op_id']);
                if (!empty($returnRequestDetail)) {
                    $comments = sprintf(Labels::getLabel('LBL_Cancel_Request_Approved', $langId), $formattedRequestValue);
                } else {
                    $comments = sprintf(Labels::getLabel('LBL_Order_has_been_Cancelled', $langId), $formattedRequestValue);
                }

                $txnAmount = (($childOrderInfo["op_unit_price"] * $childOrderInfo["op_qty"]) + $childOrderInfo["op_other_charges"]);

                /*Refund to Buyer[*/
                if ($txnAmount > 0) {
                    $txnDataArr = array(
                    'utxn_user_id'    =>    $childOrderInfo['order_user_id'],
                    'utxn_credit'    =>    $txnAmount,
                    'utxn_status'    =>    Transactions::STATUS_COMPLETED,
                    'utxn_op_id'    =>    $childOrderInfo['op_id'],
                    'utxn_comments'    =>    $comments,
                    'utxn_type'        =>    Transactions::TYPE_ORDER_REFUND
                    );

                    $transObj = new Transactions();
                    if ($txnId = $transObj->addTransaction($txnDataArr)) {
                        $emailNotificationObj->sendTxnNotification($txnId, $langId);
                    }
                }
                /*]*/
                /*Deduct Shipping Amount[*/
                if (0 < $childOrderInfo["op_free_ship_upto"] && array_key_exists(OrderProduct::CHARGE_TYPE_SHIPPING, $childOrderInfo['charges']) && $childOrderInfo["op_actual_shipping_charges"] != $childOrderInfo['charges'][OrderProduct::CHARGE_TYPE_SHIPPING]['opcharge_amount']) {
                    $sellerProdTotalPrice = 0;
                    $rows = Orderproduct::getOpArrByOrderId($childOrderInfo["op_order_id"]);
                    foreach ($rows as $row) {
                        if ($row['op_selprod_user_id'] != $childOrderInfo['op_selprod_user_id']) {
                            continue;
                        }
                        if ($row['op_refund_qty'] == $row['op_qty']) {
                            continue;
                        }
                        $qty = $row['op_qty'] - $row['op_refund_qty'];
                        $sellerProdTotalPrice+= $row['op_unit_price'] * $qty;
                    }
                    $actualShipCharges = 0;
                    //$sellerPriceIfItemWillRefund = $sellerProdTotalPrice - ($childOrderInfo["op_unit_price"] * $childOrderInfo["op_qty"]);
                    if ($childOrderInfo["op_free_ship_upto"] > $sellerProdTotalPrice) {
                        if (!FatApp::getConfig('CONF_RETURN_SHIPPING_CHARGES_TO_CUSTOMER', FatUtility::VAR_INT, 0)) {
                            $actualShipCharges = $childOrderInfo['op_actual_shipping_charges'];
                        }
                    }
                    //$actualShipCharges = min($txnAmount,$actualShipCharges);

                    if (0 < $actualShipCharges) {
                        $comments = str_replace('{invoice}', $formattedRequestValue, Labels::getLabel('LBL_Deducted_Shipping_Charges_{invoice}', $langId));
                        $txnDataArr = array(
                        'utxn_user_id'    => $childOrderInfo['order_user_id'],
                        'utxn_comments'    => $comments,
                        'utxn_status'    => Transactions::STATUS_COMPLETED,
                        'utxn_debit'    => $actualShipCharges,
                        'utxn_op_id'    => $childOrderInfo['op_id'],
                        'utxn_type'        => Transactions::TYPE_ORDER_SHIPPING,
                        );
                        $transObj = new Transactions();
                        if ($txnId = $transObj->addTransaction($txnDataArr)) {
                            $emailNotificationObj->sendTxnNotification($txnId, $langId);
                        }

                        $comments = str_replace('{invoice}', $formattedRequestValue, Labels::getLabel('LBL_Credited_Shipping_Charges_{invoice}', $langId));
                        $txnDataArr = array(
                        'utxn_user_id'    => $childOrderInfo['op_selprod_user_id'],
                        'utxn_comments'    => $comments,
                        'utxn_status'    => Transactions::STATUS_COMPLETED,
                        'utxn_credit'    => $actualShipCharges,
                        'utxn_op_id'    => $childOrderInfo['op_id'],
                        'utxn_type'        => Transactions::TYPE_ORDER_SHIPPING,
                        );
                        $transObj = new Transactions();
                        if ($txnId = $transObj->addTransaction($txnDataArr)) {
                            $emailNotificationObj->sendTxnNotification($txnId, $langId);
                        }
                    }
                }
                /* ]*/
            }
        }
        /* ] */

        /* If current order status is not return request approved but new status is return request approved then commence the order operation [ */
        if (!in_array($childOrderInfo['op_status_id'], (array)FatApp::getConfig("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS")) && in_array($opStatusId, (array)FatApp::getConfig("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS")) && ($childOrderInfo["order_is_paid"] == Orders::ORDER_IS_PAID || strtolower($childOrderInfo['pmethod_code']) == "cashondelivery")) {
            if ($moveRefundToWallet) {
                $formattedRequestValue = "#".$childOrderInfo["op_invoice_number"];
                $comments = sprintf(Labels::getLabel('LBL_Return_Request_Approved', $langId), $formattedRequestValue);
                $txnAmount = $childOrderInfo['op_refund_amount'];

                $txnAmount = $txnAmount;
                /*Refund to Buyer[*/
                if ($txnAmount > 0) {
                    $txnArray["utxn_user_id"] = $childOrderInfo['order_user_id'];
                    $txnArray["utxn_credit"] = $txnAmount;
                    $txnArray["utxn_status"] = Transactions::STATUS_COMPLETED;
                    $txnArray["utxn_op_id"] = $childOrderInfo['op_id'];
                    $txnArray["utxn_comments"] = $comments;
                    $txnArray["utxn_type"] = Transactions::TYPE_ORDER_REFUND;
                    $transObj = new Transactions();
                    if ($txnId = $transObj->addTransaction($txnArray)) {
                        $emailNotificationObj->sendTxnNotification($txnId, $langId);
                    }
                }
                /* ] */

                /*Deduct Shipping Amount[*/
                if (0 < $childOrderInfo["op_free_ship_upto"] && array_key_exists(OrderProduct::CHARGE_TYPE_SHIPPING, $childOrderInfo['charges']) && $childOrderInfo["op_actual_shipping_charges"] != $childOrderInfo['charges'][OrderProduct::CHARGE_TYPE_SHIPPING]['opcharge_amount']) {
                    $actualShipCharges = 0 ;
                    $sellerProdTotalPrice = 0;
                    $rows = Orderproduct::getOpArrByOrderId($childOrderInfo["op_order_id"]);
                    foreach ($rows as $row) {
                        if ($row['op_selprod_user_id'] != $childOrderInfo['op_selprod_user_id']) {
                            continue;
                        }
                        if ($row['op_refund_qty'] == $row['op_qty']) {
                            continue;
                        }
                        $qty = $row['op_qty'] - $row['op_refund_qty'];
                        $sellerProdTotalPrice+= $row['op_unit_price'] * $qty;
                    }

                    //$sellerPriceIfItemWillRefund = $sellerProdTotalPrice - ($childOrderInfo["op_unit_price"] * $childOrderInfo["op_refund_qty"]);
                    if ($childOrderInfo["op_free_ship_upto"] > $sellerProdTotalPrice) {
                        $unitShipCharges = round($childOrderInfo['op_actual_shipping_charges']/$childOrderInfo["op_qty"], 2);
                        $returnShipChargesToCust = 0;
                        if (FatApp::getConfig('CONF_RETURN_SHIPPING_CHARGES_TO_CUSTOMER', FatUtility::VAR_INT, 0)) {
                            $returnShipChargesToCust = $unitShipCharges * $childOrderInfo["op_refund_qty"];
                        }

                        $actualShipCharges = $childOrderInfo['op_actual_shipping_charges'] - $returnShipChargesToCust;
                    }
                    //$actualShipCharges = min($txnAmount,$actualShipCharges);
                    if (0 < $actualShipCharges) {
                        $comments = str_replace('{invoice}', $formattedRequestValue, Labels::getLabel('LBL_Deducted_Shipping_Charges_{invoice}', $langId));
                        $txnDataArr = array(
                        'utxn_user_id'    => $childOrderInfo['order_user_id'],
                        'utxn_comments'    => $comments,
                        'utxn_status'    => Transactions::STATUS_COMPLETED,
                        'utxn_debit'    => $actualShipCharges,
                        'utxn_op_id'    => $childOrderInfo['op_id'],
                        'utxn_type'        => Transactions::TYPE_ORDER_SHIPPING,
                        );
                        $transObj = new Transactions();
                        if ($txnId = $transObj->addTransaction($txnDataArr)) {
                            $emailNotificationObj->sendTxnNotification($txnId, $langId);
                        }

                        $comments = str_replace('{invoice}', $formattedRequestValue, Labels::getLabel('LBL_Credited_Shipping_Charges_{invoice}', $langId));
                        $txnDataArr = array(
                        'utxn_user_id'    => $childOrderInfo['op_selprod_user_id'],
                        'utxn_comments'    => $comments,
                        'utxn_status'    => Transactions::STATUS_COMPLETED,
                        'utxn_credit'    => $actualShipCharges,
                        'utxn_op_id'    => $childOrderInfo['op_id'],
                        'utxn_type'        => Transactions::TYPE_ORDER_SHIPPING,
                        );
                        $transObj = new Transactions();
                        if ($txnId = $transObj->addTransaction($txnDataArr)) {
                            $emailNotificationObj->sendTxnNotification($txnId, $langId);
                        }
                    }
                }
                /* ]*/
            }
        }
        /* ] */

        /* If current order status is not shipped but new status is shipped then commence shipping the order [ */
        if (!in_array($childOrderInfo['op_status_id'], (array)FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS")) && in_array($opStatusId, (array)FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS")) && ($childOrderInfo["order_is_paid"] == Orders::ORDER_IS_PAID)) {
            $db->updateFromArray(
                Orders::DB_TBL_ORDER_PRODUCTS,
                array('op_shipped_date' => date('Y-m-d H:i:s')),
                array('smt' => 'op_id = ? ', 'vals' => array($op_id))
            );
        }
        /* ] */

        /* If current order status is not delivered but new Status is delivered and the order is of COD, then, check if delivered by seller then debiting transaction entry from seller wallet [ */
        if (!in_array($childOrderInfo['op_status_id'], (array)FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS")) && in_array($opStatusId, (array)FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS")) && strtolower($childOrderInfo['pmethod_code']) == "cashondelivery") {
            if (CommonHelper::canAvailShippingChargesBySeller($childOrderInfo['op_selprod_user_id'], $childOrderInfo['opshipping_by_seller_user_id'])) {
                $formattedInvoiceNumber = "#".$childOrderInfo["op_invoice_number"];
                $comments = Labels::getLabel('Msg_Cash_collected_for_COD_order', $langId).' '.$formattedInvoiceNumber;
                $amt = CommonHelper::orderProductAmount($childOrderInfo);
                $txnObj = new Transactions();

                $txnDataArr = array(
                'utxn_user_id'    => $childOrderInfo['op_selprod_user_id'],
                'utxn_comments'    => $comments,
                'utxn_status'    => Transactions::STATUS_COMPLETED,
                'utxn_debit'    => $amt,
                'utxn_op_id'    => $childOrderInfo['op_id'],
                'utxn_type'        => Transactions::TYPE_ORDER_SHIPPING,
                );
                $transObj = new Transactions();
                if ($txnId = $transObj->addTransaction($txnDataArr)) {
                    $emailNotificationObj->sendTxnNotification($txnId, $langId);
                }
            }
        }
        /*]*/

        // If COD order and shipping user not set then assign shipping company  user seller/admin shiping company.
        if (strtolower($childOrderInfo['pmethod_code']) == "cashondelivery" && !$childOrderInfo['optsu_user_id'] && CommonHelper::canAvailShippingChargesBySeller($childOrderInfo['op_selprod_user_id'], $childOrderInfo['opshipping_by_seller_user_id'])) {
            if (CommonHelper::canAvailShippingChargesBySeller($childOrderInfo['op_selprod_user_id'], $childOrderInfo['opshipping_by_seller_user_id'])) {
                $shippingUserdata = array('optsu_op_id' => $childOrderInfo['op_id'], 'optsu_user_id' => $childOrderInfo['op_selprod_user_id']);
                $db->insertFromArray(OrderProduct::DB_TBL_OP_TO_SHIPPING_USERS, $shippingUserdata, false, array('IGNORE'));
            }
        }
        /* if( !in_array( $childOrderInfo['op_status_id'], (array)FatApp::getConfig("CONF_COD_ORDER_STATUS")) && in_array($opStatusId, (array)FatApp::getConfig("CONF_COD_ORDER_STATUS") ) && strtolower($childOrderInfo['pmethod_code']) == "cashondelivery" ){
        if(CommonHelper::canAvailShippingChargesBySeller($childOrderInfo['op_selprod_user_id'],$childOrderInfo['opshipping_by_seller_user_id'])){
        $shippingUserdata = array('optsu_op_id' => $childOrderInfo['op_id'], 'optsu_user_id' => $childOrderInfo['op_selprod_user_id']);
        $db->insertFromArray(OrderProduct::DB_TBL_OP_TO_SHIPPING_USERS,$shippingUserdata,false,array('IGNORE'));
        }
        } */

        // If current order status is not completed but new status is completed then commence completing the order
        if (!in_array($childOrderInfo['op_status_id'], (array)$this->getVendorOrderPaymentCreditedStatuses()) && in_array($opStatusId, (array)$this->getVendorOrderPaymentCreditedStatuses()) && ($childOrderInfo["order_is_paid"] == Orders::ORDER_IS_PAID || strtolower($childOrderInfo['pmethod_code']) == "cashondelivery")) {
            /* If shipped by admin credit to shipping user as COD order payment deposited by them[*/
            if (!CommonHelper::canAvailShippingChargesBySeller($childOrderInfo['op_selprod_user_id'], $childOrderInfo['opshipping_by_seller_user_id']) && strtolower($childOrderInfo['pmethod_code']) == "cashondelivery") {
                $formattedInvoiceNumber = "#".$childOrderInfo["op_invoice_number"];
                $comments = Labels::getLabel('Msg_Cash_Deposited_for_COD_order', $langId).' '.$formattedInvoiceNumber;
                $amt = CommonHelper::orderProductAmount($childOrderInfo);
                $txnObj = new Transactions();

                $txnDataArr = array(
                'utxn_user_id'    => $childOrderInfo['optsu_user_id'],
                'utxn_comments'    => $comments,
                'utxn_status'    => Transactions::STATUS_COMPLETED,
                'utxn_credit'    => $amt,
                'utxn_op_id'    => $childOrderInfo['op_id'],
                );
                $transObj = new Transactions();
                if ($txnId = $transObj->addTransaction($txnDataArr)) {
                    $emailNotificationObj->sendTxnNotification($txnId, $langId);
                }
            }
            /*]*/


            /* Start Order Payment to Vendor [ */
            $formattedInvoiceNumber = "#".$childOrderInfo["op_invoice_number"];
            $comments = sprintf(Labels::getLabel('Msg_Received_credits_for_order', $langId), $formattedInvoiceNumber);

            $availQty = $childOrderInfo['op_qty'] - $childOrderInfo['op_refund_qty'];

            $taxCharges = isset($childOrderInfo['charges'][OrderProduct::CHARGE_TYPE_TAX][OrderProduct::DB_TBL_CHARGES_PREFIX.'amount'])?$childOrderInfo['charges'][OrderProduct::CHARGE_TYPE_TAX][OrderProduct::DB_TBL_CHARGES_PREFIX.'amount']:0;

            $volumeDiscount = isset($childOrderInfo['charges'][OrderProduct::CHARGE_TYPE_VOLUME_DISCOUNT]['opcharge_amount'])?abs($childOrderInfo['charges'][OrderProduct::CHARGE_TYPE_VOLUME_DISCOUNT]['opcharge_amount']):0;

            $volumeDiscountPerQty = 0;
            $deductVolumeDiscount = 0;
            if ($volumeDiscount > 0) {
                $volumeDiscountPerQty = ($volumeDiscount/$childOrderInfo['op_qty']);
                $deductVolumeDiscount = ($volumeDiscountPerQty * $availQty);
            }

            $shipCharges = isset($childOrderInfo['charges'][OrderProduct::CHARGE_TYPE_SHIPPING][OrderProduct::DB_TBL_CHARGES_PREFIX.'amount'])?$childOrderInfo['charges'][OrderProduct::CHARGE_TYPE_SHIPPING][OrderProduct::DB_TBL_CHARGES_PREFIX.'amount']:0;
            $unitShipCharges = round(($shipCharges / $childOrderInfo['op_qty']), 2);

            if (CommonHelper::canAvailShippingChargesBySeller($childOrderInfo['op_selprod_user_id'], $childOrderInfo['opshipping_by_seller_user_id'])) {
                $shipCharges = $shipCharges - $childOrderInfo['op_refund_shipping'];
            } else {
                $shipCharges = 0;
            }

            $txnAmount = ($availQty * $childOrderInfo['op_unit_price']) - $deductVolumeDiscount + $shipCharges;

            if ($childOrderInfo['op_tax_collected_by_seller']) {
                $unitTaxCharges = round(($taxCharges / $childOrderInfo['op_qty']), 2);
                $txnAmount = $txnAmount + ($unitTaxCharges * $availQty);
            }

            if ($txnAmount > 0) {
                $txnArray["utxn_user_id"] = $childOrderInfo['op_selprod_user_id'];
                $txnArray["utxn_credit"] = $txnAmount;
                $txnArray["utxn_debit"] = 0;
                $txnArray["utxn_status"] = Transactions::STATUS_COMPLETED;
                $txnArray["utxn_op_id"] = $childOrderInfo['op_id'];
                $txnArray["utxn_comments"] = $comments;
                $txnArray["utxn_type"] = Transactions::TYPE_PRODUCT_SALE;
                $transObj = new Transactions();
                if ($txnId = $transObj->addTransaction($txnArray)) {
                    $emailNotificationObj->sendTxnNotification($txnId, $langId);
                }
            }
            /* ] */


            /* Charge Commission/fees to Vendor [*/
            $commissionFees = $childOrderInfo['op_commission_charged'] - $childOrderInfo['op_refund_commission'] ;

            if ($commissionFees > 0) {
                $comments = sprintf(Labels::getLabel('Msg_Charged_Commission_for_order', $langId), $formattedInvoiceNumber);
                $txnArray["utxn_user_id"] = $childOrderInfo['op_selprod_user_id'];
                $txnArray["utxn_debit"] = $commissionFees;
                $txnArray["utxn_credit"] = 0;
                $txnArray["utxn_status"] = Transactions::STATUS_COMPLETED;
                $txnArray["utxn_op_id"] = $childOrderInfo['op_id'];
                $txnArray["utxn_comments"] = $comments;
                $txnArray["utxn_type"] = Transactions::TYPE_PRODUCT_SALE_ADMIN_COMMISSION;
                $transObj = new Transactions();
                if ($txnId = $transObj->addTransaction($txnArray)) {
                    $emailNotificationObj->sendTxnNotification($txnId, $langId);
                }
            }
            /* ] */


            /* Commission to Affiliate, if sale is linked with Affiliate referral [ */
            $affiliateCommissionFees = $childOrderInfo['op_affiliate_commission_charged'] - $childOrderInfo['op_refund_affiliate_commission'] ;
            if ($affiliateCommissionFees > 0 && $childOrderInfo['order_affiliate_user_id'] > 0) {
                $commentString = Labels::getLabel('Msg_Commission_Received_Order{invoicenumber}_Placed_by_Referrar_User', $langId);
                $commentString = str_replace("invoicenumber", $formattedInvoiceNumber, $commentString);
                $txnArray["utxn_user_id"] = $childOrderInfo['order_affiliate_user_id'];
                $txnArray["utxn_credit"] = $affiliateCommissionFees;
                $txnArray["utxn_debit"] = 0;
                $txnArray["utxn_status"] = Transactions::STATUS_COMPLETED;
                $txnArray["utxn_op_id"] = $childOrderInfo['op_id'];
                $txnArray["utxn_comments"] = $commentString;
                $txnArray["utxn_type"] = Transactions::TYPE_AFFILIATE_REFERRAL_ORDER;
                $transObj = new Transactions();
                if ($txnId = $transObj->addTransaction($txnArray)) {
                    $emailNotificationObj->sendTxnNotification($txnId, $langId);
                }
            }
            /* ] */

            $db->updateFromArray(
                Orders::DB_TBL_ORDER_PRODUCTS,
                array('op_completion_date' => date('Y-m-d H:i:s')),
                array('smt' => 'op_id = ? ', 'vals' => array($op_id))
            );

            /* Allocation of Rewards points [ */
            /* Handeled in addOrderPaymentHistory Function. */
            /*]*/

            Cronjob::RewardsOnPurchase($childOrderInfo['op_order_id']);
            Cronjob::firstTimeBuyerDiscount($childOrderInfo['order_user_id'], $childOrderInfo['op_order_id']);
        }

        // If order status is in buyer order statuses then send update email
        if (in_array($opStatusId, unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS"))) && $notify) {
            $emailNotificationObj->orderStatusUpdateBuyer($commentId, $childOrderInfo['order_language_id'], $childOrderInfo['order_user_id']);
        }
        return true;
    }

    public static function getBuyerAllowedOrderCancellationStatuses($isDigitalProduct = false)
    {
        if ($isDigitalProduct) {
            $buyerAllowCancelStatuses = unserialize(FatApp::getConfig("CONF_DIGITAL_ALLOW_CANCELLATION_ORDER_STATUS"));
        } else {
            $buyerAllowCancelStatuses = unserialize(FatApp::getConfig("CONF_ALLOW_CANCELLATION_ORDER_STATUS"));
        }

        // $buyerAllowCancelStatuses = array_diff($buyerAllowCancelStatuses, (array)FatApp::getConfig("CONF_DEFAULT_ORDER_STATUS"));

        $buyerAllowCancelStatuses = array_diff($buyerAllowCancelStatuses, (array)FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS"));
        $buyerAllowCancelStatuses = array_diff($buyerAllowCancelStatuses, unserialize(FatApp::getConfig("CONF_PROCESSING_ORDER_STATUS")));
        $buyerAllowCancelStatuses = array_diff($buyerAllowCancelStatuses, unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")));

        return $buyerAllowCancelStatuses;
    }

    public static function getBuyerAllowedOrderReturnStatuses($isDigitalProduct = false)
    {
        $buyerAllowReturnStatuses = unserialize(FatApp::getConfig("CONF_RETURN_EXCHANGE_READY_ORDER_STATUS"));
        /* if( $isDigitalProduct ){
        $buyerAllowReturnStatuses = unserialize(FatApp::getConfig("CONF_DIGITAL_RETURN_READY_ORDER_STATUS"));
        } */
        $buyerAllowReturnStatuses = array_diff($buyerAllowReturnStatuses, (array)FatApp::getConfig("CONF_DEFAULT_ORDER_STATUS"));
        $buyerAllowReturnStatuses = array_diff($buyerAllowReturnStatuses, (array)FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS"));
        $buyerAllowReturnStatuses = array_diff($buyerAllowReturnStatuses, unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")));
        return $buyerAllowReturnStatuses;
    }

    public static function getVendorOrderPaymentCreditedStatuses()
    {
        $vendorPaymentStatuses = unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS"));
        $vendorPaymentStatuses = array_diff($vendorPaymentStatuses, (array)FatApp::getConfig("CONF_DEFAULT_ORDER_STATUS"));
        $vendorPaymentStatuses = array_diff($vendorPaymentStatuses, (array)FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS"));
        $vendorPaymentStatuses = array_diff($vendorPaymentStatuses, (array)FatApp::getConfig("CONF_RETURN_REQUEST_ORDER_STATUS"));
        return $vendorPaymentStatuses;
    }

    public static function getBuyerAllowedDigitalDownloadStatues()
    {
        $buyerAllowDigitalDownloadStatuses = unserialize(FatApp::getConfig("CONF_ENABLE_DIGITAL_DOWNLOADS", null, ''));
        return $buyerAllowDigitalDownloadStatuses ;
    }

    public function getVendorAllowedUpdateOrderStatuses($fetchForDigitalProduct = false, $fetchForCOD = false)
    {
        $processingStatuses = array_merge(unserialize(FatApp::getConfig("CONF_PROCESSING_ORDER_STATUS")), (array)FatApp::getConfig("CONF_DEFAULT_PAID_ORDER_STATUS", FatUtility::VAR_STRING, ''));
        $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_ORDER_STATUS"));
        $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS"));
        $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_RETURN_REQUEST_ORDER_STATUS"));
        $processingStatuses = array_diff($processingStatuses, unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")));
        $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS"));

        $digitalProdOrderStatusArr = Orders::getOrderProductStatusArr(CommonHelper::getLangId(), array(), 0, true);
        $digitalProductOrderStatusArr = array();
        foreach ($digitalProdOrderStatusArr as $k => $v) {
            $digitalProductOrderStatusArr[] = $k;
        }

        if ($fetchForDigitalProduct) {
            $processingStatuses = array_intersect($digitalProductOrderStatusArr, $processingStatuses);
            $processingStatuses = array_merge((array)$processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_PAID_ORDER_STATUS", FatUtility::VAR_INT, 0));
        } else {
            $processingStatuses = array_diff((array)$processingStatuses, $digitalProductOrderStatusArr);
        }

        if ($fetchForCOD) {
            $processingStatuses = array_diff((array)$processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_PAID_ORDER_STATUS", FatUtility::VAR_INT, 0));
            $processingStatuses = array_merge((array)$processingStatuses, (array)FatApp::getConfig("CONF_COD_ORDER_STATUS", FatUtility::VAR_INT, 0));
        }

        return $processingStatuses;
    }

    public function getAdminAllowedUpdateOrderStatuses($fetchForCOD = false, $productType = false)
    {
        $processingStatuses = array_merge(unserialize(FatApp::getConfig("CONF_PROCESSING_ORDER_STATUS")), unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")));
        $processingStatuses = array_merge((array)$processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_PAID_ORDER_STATUS"));
        $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_ORDER_STATUS"));
        $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS"));
        $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_RETURN_REQUEST_ORDER_STATUS"));
        $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS"));

        if ($fetchForCOD) {
            $processingStatuses = array_diff((array)$processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_PAID_ORDER_STATUS"));
            $processingStatuses = array_merge((array)$processingStatuses, (array)FatApp::getConfig("CONF_COD_ORDER_STATUS"));
        }

        switch ($productType) {
            case Product::PRODUCT_TYPE_DIGITAL:
                $processingStatuses = array_diff((array)$processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"));
                $processingStatuses = array_diff((array)$processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS"));
                break;
        }

        return $processingStatuses;
    }

    public function getAdminAllowedUpdateShippingUser()
    {
        $processingStatuses = unserialize(FatApp::getConfig("CONF_PROCESSING_ORDER_STATUS"));
        $processingStatuses = array_merge((array)$processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_PAID_ORDER_STATUS"));
        $processingStatuses = array_diff($processingStatuses, unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")));
        $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS"));
        $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_ORDER_STATUS"));
        $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS"));
        $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_RETURN_REQUEST_ORDER_STATUS"));
        $processingStatuses = array_diff($processingStatuses, (array)FatApp::getConfig("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS"));
        return $processingStatuses;
    }

    public function getNotAllowedOrderCancellationStatuses()
    {
        $cancellationStatuses = array_merge(
            (array)FatApp::getConfig("CONF_DEFAULT_ORDER_STATUS", null, ''),
            (array)FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS", null, ''),
            (array)FatApp::getConfig("CONF_RETURN_REQUEST_ORDER_STATUS", null, ''),
            (array)FatApp::getConfig("CONF_RETURN_REQUEST_WITHDRAWN_ORDER_STATUS", null, ''),
            (array)FatApp::getConfig("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS", null, ''),
            unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS"))
        );
        return $cancellationStatuses;
    }

    public function getOrderProductsByOpId($op_id, $langId)
    {
        $op_id = FatUtility::int($op_id);
        $langId = FatUtility::int($langId);
        $srch = new OrderProductSearch($langId, true);
        $srch->joinPaymentMethod();
        $srch->joinShippingUsers();
        //$srch->joinTable(Orders::DB_TBL,'LEFT OUTER JOIN','o.order_id = op.op_order_id','o');
        $srch->joinTable(OrderProduct::DB_TBL_CHARGES, 'LEFT OUTER JOIN', 'opc.'.OrderProduct::DB_TBL_CHARGES_PREFIX.'op_id = op.op_id', 'opc');
        $srch->joinTable(Orders::DB_TBL_ORDER_PRODUCTS_SHIPPING, 'LEFT OUTER JOIN', 'ops.opshipping_op_id = op.op_id', 'ops');

        $srch->addMultipleFields(array('op.*','opst.*','op_l.*','o.order_id','o.order_is_paid','o.order_language_id','o.order_user_id','sum('.OrderProduct::DB_TBL_CHARGES_PREFIX.'amount) as op_other_charges', 'o.order_affiliate_user_id','pmethod_code','optsu_user_id','ops.opshipping_by_seller_user_id'));
        $srch->addCondition('op_id', '=', $op_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $records = array();
        $row = FatApp::getDb()->fetch($srch->getResultSet());
        $charges = $this->getOrderProductChargesArr($op_id);
        if (!empty($row)) {
            $records = $row;
            $records['charges'] = $charges;
        }
        return $records;
    }

    public function getOrderProductChargesArr($op_id, $mobileApiCall = false)
    {
        $op_id = FatUtility::int($op_id);
        $srch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array(OrderProduct::DB_TBL_CHARGES_PREFIX.'type',OrderProduct::DB_TBL_CHARGES_PREFIX.'amount'));
        $srch->addCondition(OrderProduct::DB_TBL_CHARGES_PREFIX.'op_id', '=', $op_id);
        $srch->addCondition(OrderProduct::DB_TBL_CHARGES_PREFIX.'order_type', '=', Orders::ORDER_PRODUCT);
        $rs = $srch->getResultSet();

        if (true === $mobileApiCall) {
            return FatApp::getDb()->fetchAll($rs);
        } else {
            $row = FatApp::getDb()->fetchAll($rs, OrderProduct::DB_TBL_CHARGES_PREFIX.'type');

            if (!array_key_exists(OrderProduct::CHARGE_TYPE_SHIPPING, $row)) {
                $row[OrderProduct::CHARGE_TYPE_SHIPPING] = array(
                'opcharge_type'=>OrderProduct::CHARGE_TYPE_SHIPPING,
                'opcharge_amount'=>0,
                );
            }
            return $row;
        }
    }

    public function getOrderProductChargesByOrderId($orderId)
    {
        $srch = new SearchBase(OrderProduct::DB_TBL, 'op');
        $srch->joinTable(OrderProduct::DB_TBL_CHARGES, 'LEFT OUTER JOIN', 'opc.'.OrderProduct::DB_TBL_CHARGES_PREFIX.'op_id = op.op_id', 'opc');
        $srch->addCondition('op_order_id', '=', $orderId);
        $srch->addMultipleFields(array('op_id',OrderProduct::DB_TBL_CHARGES_PREFIX.'type',OrderProduct::DB_TBL_CHARGES_PREFIX.'amount'));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAll($rs);
        $charges = array();
        if (!empty($row)) {
            foreach ($row as $val) {
                $charges[$val['op_id']][$val[OrderProduct::DB_TBL_CHARGES_PREFIX.'type']] = array(
                OrderProduct::DB_TBL_CHARGES_PREFIX.'type' =>$val[OrderProduct::DB_TBL_CHARGES_PREFIX.'type'],
                OrderProduct::DB_TBL_CHARGES_PREFIX.'amount' =>$val[OrderProduct::DB_TBL_CHARGES_PREFIX.'amount'],
                );
            }
        }
        return $charges;
    }

    public function getOrderPaymentFinancials($orderId, $langId = 0)
    {
        $langId = FatUtility::int($langId);
        $orderInfo = $this->getOrderById($orderId, $langId);

        $userBalance = User::getUserBalance($orderInfo["order_user_id"]);
        $orderCreditsCharge = $orderInfo["order_wallet_amount_charge"]?min($orderInfo["order_wallet_amount_charge"], $userBalance):0;
        $orderPaymentGatewayCharge = $orderInfo["order_net_amount"] - $orderInfo["order_wallet_amount_charge"];
        $orderPaymentSummary = array(
          "net_payable"=>$orderInfo["order_net_amount"],
          "order_user_balance"=>$userBalance,
          "order_credits_charge"=>$orderCreditsCharge,
          "order_payment_gateway_charge"=>$orderPaymentGatewayCharge,
                        );
        return $orderPaymentSummary;
    }

    public function getOrderPaymentPaid($orderId)
    {
        $srch = new SearchBase(static::DB_TBL_ORDER_PAYMENTS, 'opayment');
        $srch->addMultipleFields(
            array('sum(opayment_amount) as totalPaid')
        );
        $srch->addCondition('opayment_order_id', '=', $orderId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $row = FatApp::getDb()->fetch($srch->getResultSet());
        if (!empty($row)) {
            return ($row['totalPaid'] != null)?$row['totalPaid']:0;
        }
        return 0;
    }

    public function getOrderPayments($criteria = array())
    {
        if (count($criteria) == 0) {
            return array();
        }

        $srch = new SearchBase(static::DB_TBL_ORDER_PAYMENTS, 'opayment');

        foreach ($criteria as $key => $val) {
            if (strval($val) == '') {
                continue;
            }
            switch ($key) {
                case 'id':
                    $srch->addCondition('opayment.opayment_id', '=', intval($val));
                    break;
                case 'order_id':
                    $srch->addCondition('opayment.opayment_order_id', '=', $val);
                    break;
            }
        }

        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords(true);
        $srch->addOrder('opayment_id');

        $row = FatApp::getDb()->fetchAll($srch->getResultSet(), 'opayment_id');
        if ($row == false) {
            return array();
        }
        return $row;
    }

    public static function getOrderProductDigitalDownloads($op_id, $fileId = 0)
    {
        $op_id = FatUtility::int($op_id);
        $fileId = FatUtility::int($fileId);

        $srch = new OrderProductSearch(0, true);
        $srch->joinOrderUser();
        $srch->joinDigitalDownloads();
        $srch->addDigitalDownloadCondition();
        $srch->addMultipleFields(array('op_id','op_invoice_number','order_user_id','op_product_type','order_date_added','op_qty','op_status_id','op_selprod_max_download_times','op_selprod_download_validity_in_days','opa.*'));
        $srch->addCondition('op_id', '=', $op_id);
        if ($fileId > 0) {
            $srch->addCondition('afile_id', '=', $fileId);
        }

        $rs = $srch->getResultSet();

        $downloads = FatApp::getDb()->fetchAll($rs);

        $digitalDownloads = static::digitalDownloadFormat($downloads);

        return $digitalDownloads;
    }

    public static function digitalDownloadFormat($downloads = array())
    {
        $digitalDownloads = array();
        foreach ($downloads as $key => $row) {
            $digitalDownloads[$key] = $row;

            $dateAvailable = '';
            if ($row['op_selprod_download_validity_in_days'] != '-1') {
                $dateAvailable = date('Y-m-d', strtotime($row['order_date_added']. ' + '.$row['op_selprod_download_validity_in_days'].' days'));
            }

            $digitalDownloads[$key]['expiry_date'] = $dateAvailable;

            $digitalDownloads[$key]['downloadable'] = true;
            if ($dateAvailable !='' && $dateAvailable < date('Y-m-d')) {
                $digitalDownloads[$key]['downloadable'] = false;
            }

            $digitalDownloads[$key]['downloadable_count'] = -1;
            if ($row['op_selprod_max_download_times'] != '-1') {
                $digitalDownloads[$key]['downloadable_count'] = $row['op_selprod_max_download_times'];
            }

            if ($row['op_selprod_max_download_times'] != '-1') {
                if ($row['afile_downloaded_times'] >= $row['op_selprod_max_download_times']) {
                    $digitalDownloads[$key]['downloadable'] = false;
                }
            }
        }
        return $digitalDownloads;
    }

    public static function getOrderProductDigitalDownloadLinks($op_id, $link_id = 0)
    {
        $op_id = FatUtility::int($op_id);
        $link_id = FatUtility::int($link_id);

        $srch = new OrderProductSearch(0, true);
        $srch->joinOrderUser();
        $srch->joinDigitalDownloadLinks();
        $srch->addDigitalDownloadCondition();
        $srch->addMultipleFields(array('op_id','op_invoice_number','order_user_id','op_product_type','order_date_added','op_qty','op_status_id','op_selprod_max_download_times','op_selprod_download_validity_in_days','opd.*'));
        $srch->addCondition('op_id', '=', $op_id);

        if ($link_id > 0) {
            $srch->addCondition('opddl_link_id', '=', $link_id);
        }
        $rs = $srch->getResultSet();

        $downloads = FatApp::getDb()->fetchAll($rs);

        $digitalDownloads = static::digitalDownloadLinksFormat($downloads);

        return $digitalDownloads;
    }

    public static function digitalDownloadLinksFormat($downloads = array())
    {
        $digitalDownloads = array();
        foreach ($downloads as $key => $row) {
            $digitalDownloads[$key] = $row;

            $dateAvailable = '';
            if ($row['op_selprod_download_validity_in_days'] != '-1') {
                $dateAvailable = date('Y-m-d', strtotime($row['order_date_added']. ' + '.$row['op_selprod_download_validity_in_days'].' days'));
            }

            $digitalDownloads[$key]['expiry_date'] = $dateAvailable;

            $digitalDownloads[$key]['downloadable'] = true;
            if ($dateAvailable !='' && $dateAvailable < date('Y-m-d')) {
                $digitalDownloads[$key]['downloadable'] = false;
            }

            $digitalDownloads[$key]['downloadable_count'] = -1;
            if ($row['op_selprod_max_download_times'] != '-1') {
                $digitalDownloads[$key]['downloadable_count'] = $row['op_selprod_max_download_times']*$row['op_qty'];
            }

            if ($row['op_selprod_max_download_times'] != '-1') {
                if ($row['opddl_downloaded_times'] >= $row['op_selprod_max_download_times']) {
                    $digitalDownloads[$key]['downloadable'] = false;
                }
            }
        }
        return $digitalDownloads;
    }

    public function searchOrderProducts($criteria = array(), $langId = 0)
    {
        $srch = static::getOrderProductSearchObject($langId);

        foreach ($criteria as $key => $val) {
            if (strval($val)=='') {
                continue;
            }
            switch ($key) {
                case 'id':
                case 'op_id':
                    $op_id = FatUtility::int($op_id);
                    $srch->addCondition('op.op_id', '=', $op_id);
                    break;
                case 'order':
                case 'order_id':
                    $srch->addCondition('op_order_id', '=', $val);
                    break;
            }
        }
        return $srch;
    }

    public function refundOrderPaidAmount($order_id, $langId)
    {
        $order = $this->getOrderById($order_id, $langId);

        $formattedRequestValue = "#".$order["order_id"];
        $comments = sprintf(Labels::getLabel('LBL_Order_Number_Comments', $langId), $formattedRequestValue);
        $txnAmount = $order['order_net_amount'];

        if ($txnAmount <= 0) {
            return false;
        }

        if ($txnAmount > 0) {
            $txnArray["utxn_user_id"] = $order['order_user_id'];
            $txnArray["utxn_credit"] = $txnAmount;
            $txnArray["utxn_status"] = Transactions::STATUS_COMPLETED;
            $txnArray["utxn_order_id"] = $order['order_id'];
            $txnArray["utxn_comments"] = $comments;
            $txnArray["utxn_type"] = Transactions::TYPE_ORDER_REFUND;
            $transObj = new Transactions();
            if ($txnId = $transObj->addTransaction($txnArray)) {
                $emailNotificationObj = new EmailHandler();
                $emailNotificationObj->sendTxnNotification($txnId, $langId);
            }
            return true;
        }

        return false;
    }

    public function updateOrderInfo($order_id, $data)
    {
        if (empty($data) || sizeof($data) <= 0 || !$order_id) {
            $this->error = 'Error, in updating the order, no parameters passed to update record.';
            return false;
        }
        $record = new TableRecord(static::DB_TBL);
        $record->assignValues($data);
        if (!$record->update(array('smt'=>'order_id=?', 'vals'=>array($order_id)))) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    /* public function addOrderCancellationRequest( $data = array() ){
    $recordObj = new TableRecord( static::DB_TBL_ORDER_CANCEL_REQUEST );
    $recordObj->assignValues( $data );
    if( !$recordObj->addNew() ){
    $this->error = $recordObj->getError();
    return false;
    }
    return $recordObj->getId();
    } */

    private function generateOrderId()
    {
        /* $defaultSiteLangid = FatApp::getConfig('conf_default_site_lang');
        $websiteName = FatApp::getConfig('CONF_WEBSITE_NAME_'.$defaultSiteLangid);
        $order_id = strtoupper(substr( $websiteName, 0, 1)); */
        $order_id = 'O';
        $order_id .= time();
        if ($this->checkUniqueOrderId($order_id)) {
            return $order_id;
        } else {
            $this->generateOrderId();
        }
        /* do{
        $row = Orders::getAttributesById($order_id, array('order_id'));
        } while ($row);
        return $order_id; */
    }

    private function checkUniqueOrderId($order_id)
    {
        $row = Orders::getAttributesById($order_id, array('order_id'));
        if ($row == false) {
            return true;
        }
        return false;
    }

    /* For Subscription Module  Get latest Subscription Order */
    public static function getLatestSubscriptionOrder($userId = 0, $langId = 0)
    {
        $srch = new  OrderSearch($langId);
        $srch->joinTableOrderSellerSubscription();
        $srch ->addCondition('order_type', '=', Orders::ORDER_SUBSCRIPTION);
        $srch->setPageSize(1);
        $srch->doNotCalculateRecords(true);
        $srch->addOrder('order_id');
        $rowCount =  $srch->recordCount();
        if ($rowCount == 0) {
            return false;
        }
        return true;
    }

    public static function getOrderCommentById($orderId = 0, $langId)
    {
        $formattedOrderValue = " #".$orderId;

        $srch = new  OrderSearch($langId);
        $srch->addCondition('order_id', '=', $orderId);
        $rs = $srch->getResultSet();
        $orderInfo = FatApp::getDb()->fetch($rs);

        /* CommonHelper::printArray($orderInfo); die; */
        if ($orderInfo['order_type'] == Orders::ORDER_SUBSCRIPTION) {
            if ($orderInfo['order_renew']) {
                return sprintf(Labels::getLabel('LBL_Your_Subscription_is_renewed_%s', $langId), $formattedOrderValue);
            } else {
                return sprintf(Labels::getLabel('LBL_New_Subscription_Purchased_%s', $langId), $formattedOrderValue);
            }
        } else {
            return sprintf(Labels::getLabel('LBL_ORDER_PLACED_%s', $langId), $formattedOrderValue);
        }
    }

    public function updateOrderUserAddress($userId)
    {
        $srch = new  OrderSearch();
        $srch->addCondition('order_user_id', '=', $userId);
        $rs = $srch->getResultSet();
        $orderInfo = FatApp::getDb()->fetchAll($rs);

        if (!$orderInfo) {
            return true;
        }
        /* print_r($orderInfo);die; */
        foreach ($orderInfo as $order) {
            if (!FatApp::getDb()->updateFromArray(
                Orders::DB_TBL_ORDER_USER_ADDRESS,
                array('oua_address1' => static::REPLACE_ORDER_USER_ADDRESS,'oua_address2' => static::REPLACE_ORDER_USER_ADDRESS,'oua_city' => static::REPLACE_ORDER_USER_ADDRESS,'oua_state' => static::REPLACE_ORDER_USER_ADDRESS,'oua_country' => static::REPLACE_ORDER_USER_ADDRESS,'oua_country_code' => static::REPLACE_ORDER_USER_ADDRESS,'oua_phone' => static::REPLACE_ORDER_USER_ADDRESS,'oua_zip' => static::REPLACE_ORDER_USER_ADDRESS),
                array('smt' => 'oua_order_id = ? ', 'vals' => array($order['order_id']))
            )) {
                $this->error = FatApp::getDb()->getError();
                return false;
            }
        }

        return true;
    }

    public static function canSubmitFeedback($userId, $op_order_id, $selprod_id){
        if (!FatApp::getConfig('CONF_ALLOW_REVIEWS', FatUtility::VAR_INT, 0)) {
            return false;
        }
        $oFeedbackSrch = new SelProdReviewSearch();
        $oFeedbackSrch->doNotCalculateRecords();
        $oFeedbackSrch->doNotLimitRecords();
        $oFeedbackSrch->addCondition('spreview_postedby_user_id', '=', $userId);
        $oFeedbackSrch->addCondition('spreview_order_id', '=', $op_order_id);
        $oFeedbackSrch->addCondition('spreview_selprod_id', '=', $selprod_id);
        $oFeedbackRs = $oFeedbackSrch->getResultSet();
        if (!empty(FatApp::getDb()->fetch($oFeedbackRs))) {
            return false;
        }
        return true;
    }
}
