<?php
class UserRewardBreakup extends MyAppModel
{
    const DB_TBL = 'tbl_user_reward_point_breakup';
    const DB_TBL_PREFIX = 'urpbreakup_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch =  new SearchBase(static::DB_TBL, 'urpb');
        return $srch;
    }

    public static function rewardPointBalance($userId = 0, $orderId = '')
    {
        $userId = FatUtility::int($userId);
        if (1 > $userId) {
            return 0;
        }

        $totalBalance = 0;

        $srch = new UserRewardSearch();
        $srch->joinUserRewardBreakup();
        $srch->addCondition('urp.urp_user_id', '=', $userId);
        $srch->addCondition('urpb.urpbreakup_used', '=', 0);
        $cond = $srch->addCondition('urpb.urpbreakup_expiry', '>=', date('Y-m-d'), 'AND');
        $cond->attachCondition('urpb.urpbreakup_expiry', '=', '0000-00-00', 'OR');
        $srch->addMultipleFields(array('sum(urpbreakup_points) as balance'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        /* die($srch->getQuery()); */
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if ($row != false) {
            $totalBalance = $totalBalance + FatUtility::int($row['balance']);
        }

        $srch = new OrderProductSearch();
        $srch->joinorders();
        $srch->joinPaymentMethod();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('order_reward_point_used', '>', 0);
        $cnd = $srch->addCondition('order_is_paid', '=', Orders::ORDER_IS_PENDING);
        $cnd->attachCondition('pmethod_code', '=', 'CashOnDelivery');
        $srch->addCondition('op.op_status_id', '=', FatApp::getConfig("CONF_DEFAULT_ORDER_STATUS"));
        $date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'). ' - 2 hours'));
        //$srch->addDirectCondition('DATE(o.order_date_added) = DATE(NOW())');
        $srch->addCondition('o.order_date_added', '>=', $date);
        $srch->addMultipleFields(array('sum(order_reward_point_used) as usedRewards'));
        if ($orderId != '') {
            $srch->addCondition('order_id', '!=', $orderId);
        }
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if ($row == false || $totalBalance < $row['usedRewards']) {
            return 0;
        }

        $totalBalance = $totalBalance - FatUtility::int($row['usedRewards']);
        return $totalBalance;
    }
}
