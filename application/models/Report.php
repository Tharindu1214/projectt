<?php
class Report extends MyAppModel
{
    public static function salesReportObject($langId = 0, $joinSeller = false, $attr = array())
    {
        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id','sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($langId, true);
        $srch->joinPaymentMethod();

        if ($joinSeller) {
            $srch->joinSellerUser();
        }

        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->joinOrderProductCharges(OrderProduct::CHARGE_TYPE_TAX, 'optax');
        $srch->joinOrderProductCharges(OrderProduct::CHARGE_TYPE_SHIPPING, 'opship');

        $cnd = $srch->addCondition('o.order_is_paid', '=', Orders::ORDER_IS_PAID);
        $cnd->attachCondition('pmethod_code', '=', 'cashondelivery');
        $srch->addStatusCondition(unserialize(FatApp::getConfig('CONF_COMPLETED_ORDER_STATUS')));

        if (empty($attr)) {
            $srch->addMultipleFields(array('DATE(order_date_added) as order_date','count(op_id) as totOrders','SUM(op_qty) as totQtys','SUM(op_refund_qty) as totRefundedQtys','SUM(op_qty - op_refund_qty) as netSoldQty','sum((op_commission_charged - op_refund_commission)) as totalSalesEarnings','sum(op_refund_amount) as totalRefundedAmount','op.op_qty','op.op_unit_price','op.op_unit_cost','SUM( op.op_unit_cost * op_qty ) as inventoryValue','op_other_charges','sum(( op_unit_price * op_qty ) + op_other_charges - op_refund_amount) as orderNetAmount','(SUM(optax.opcharge_amount)) as taxTotal','(SUM(opship.opcharge_amount)) as shippingTotal'));
        } else {
            $srch->addMultipleFields($attr);
        }

        return $srch ;
    }
}
