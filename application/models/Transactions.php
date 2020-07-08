<?php
class Transactions extends MyAppModel
{
    const DB_TBL = 'tbl_user_transactions';
    const DB_TBL_PREFIX = 'utxn_';

    const STATUS_PENDING = 0;
    const STATUS_COMPLETED = 1;

    const WITHDRAWL_STATUS_PENDING = 0;
    const WITHDRAWL_STATUS_COMPLETED = 1;
    const WITHDRAWL_STATUS_APPROVED = 2;
    const WITHDRAWL_STATUS_DECLINED = 3;

    const TYPE_AFFILIATE_REFERRAL_SIGN_UP = 1;
    const TYPE_AFFILIATE_REFERRAL_ORDER = 2;
    const TYPE_LOADED_MONEY_TO_WALLET = 3;
    const TYPE_ORDER_PAYMENT = 4;
    const TYPE_ORDER_REFUND = 5;
    const TYPE_PRODUCT_SALE = 6;
    const TYPE_PRODUCT_SALE_ADMIN_COMMISSION = 7;
    const TYPE_MONEY_WITHDRAWN = 8;
    const TYPE_PPC = 9;
    const TYPE_MONEY_WITHDRAWL_REFUND = 10;
    const TYPE_ORDER_SHIPPING = 11;

    const CREDIT_TYPE = 1;
    const DEBIT_TYPE = 2;

    public function __construct($utxnId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $utxnId);
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'utxn');
        return $srch;
    }

    public static function getStatusArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', CommonHelper::getLangId()), E_USER_ERROR);
        }
        $arr=array(
        static::STATUS_PENDING => Labels::getLabel('LBL_Transaction_Pending', $langId),
        static::STATUS_COMPLETED => Labels::getLabel('LBL_Transaction_Completed', $langId)
        );
        return $arr;
    }

    public static function getWithdrawlStatusArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }
        $arr=array(
        static::WITHDRAWL_STATUS_PENDING => Labels::getLabel('LBL_Withdrawal_Request_Pending', $langId),
        static::WITHDRAWL_STATUS_COMPLETED => Labels::getLabel('LBL_Withdrawal_Request_Completed', $langId),
        static::WITHDRAWL_STATUS_APPROVED => Labels::getLabel('LBL_Withdrawal_Request_Approved', $langId),
        static::WITHDRAWL_STATUS_DECLINED => Labels::getLabel('LBL_Withdrawal_Request_Declined', $langId)
        );
        return $arr;
    }

    public static function getCreditDebitTypeArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }

        $arr=array(
        static::CREDIT_TYPE => Labels::getLabel('LBL_Credit', $langId),
        static::DEBIT_TYPE => Labels::getLabel('LBL_Debit', $langId)
        );
        return $arr;
    }

    public function getAttributesBywithdrawlId($withdrawalId, $attr = null)
    {
        $withdrawalId = FatUtility::int($withdrawalId);
        if (1 > $withdrawalId) {
            trigger_error(Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId), E_USER_ERROR);
            return false;
        }

        $srch = static::getSearchObject();
        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $srch->addCondition('utxn.utxn_withdrawal_id', '=', $withdrawalId);

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if (!empty($row)) {
            return $row;
        }

        return false;
    }

    public function getAttributesWithUserInfo($userId = 0, $attr = null)
    {
        $userId = FatUtility::int($userId);
        $srch = static::getSearchObject();
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = utxn.utxn_user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'c.credential_user_id = u.user_id', 'c');

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        if ($this->mainTableRecordId > 0) {
            $srch->addCondition('utxn.utxn_id', '=', $this->mainTableRecordId);
        }

        if ($userId > 0) {
            $srch->addCondition('utxn.utxn_user_id', '=', $userId);
        }

        $rs = $srch->getResultSet();

        if ($this->mainTableRecordId > 0) {
            $row = FatApp::getDb()->fetch($rs);
        } else {
            $row = FatApp::getDb()->fetchAll($rs, 'utxn_id');
        }

        if (!empty($row)) {
            return $row;
        }

        return array();
    }

    public function addTransaction($data)
    {
        $userId = FatUtility::int($data['utxn_user_id']);

        if ($userId < 1) {
            trigger_error(Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId), E_USER_ERROR);
            return false;
        }
        $data['utxn_date'] = date('Y-m-d H:i:s');
        $this->assignValues($data);
        if (!$this->save()) {
            return false;
        }
        return $this->getMainTableRecordId();
    }

    public function getTransactionSummary($userId = 0, $date = '')
    {
        $userId = FatUtility::int($userId);
        $srch = static::getSearchObject();

        if ($userId > 0) {
            $srch->addCondition('utxn.utxn_user_id', '=', $userId);
        }

        if (!empty($date)) {
            $srch->addCondition('mysql_func_DATE(utxn.utxn_date)', '=', $date, 'AND', true);
        }

        $srch->addMultipleFields(array('IFNULL(SUM(utxn.utxn_credit),0) AS total_earned','IFNULL(SUM(utxn.utxn_debit),0) AS total_used'));
        $srch->doNotCalculateRecords();
        $srch->doNotlimitRecords();
        $srch->addCondition('utxn_status', '=', applicationConstants::ACTIVE);
        $rs = $srch->getResultSet();

        if ($row = FatApp::getDb()->fetch($rs)) {
            return $row;
        }

        return array('total_earned'=>0,'total_used'=>0);
    }

    public static function formatTransactionNumber($txnId)
    {
        $newValue = str_pad($txnId, 7, '0', STR_PAD_LEFT);
        $newValue = "TN"."-".$newValue;
        return $newValue;
    }

    public static function formatTransactionComments($txnComments)
    {
        $strComments = $txnComments;
        $strComments = preg_replace('/<\/?a[^>]*>/', '', $strComments);
        return $strComments;
    }

    public static function getUserTransactionsObj($userId)
    {
        $balSrch = static::getSearchObject();
        $balSrch->doNotCalculateRecords();
        $balSrch->doNotLimitRecords();
        $balSrch->addMultipleFields(array('utxn.*',"utxn_credit - utxn_debit as bal"));
        $balSrch->addCondition('utxn_user_id', '=', $userId);
        $balSrch->addCondition('utxn_status', '=', applicationConstants::ACTIVE);
        $qryUserPointsBalance = $balSrch->getQuery();

        $srch = static::getSearchObject();
        $srch->joinTable('(' . $qryUserPointsBalance . ')', 'JOIN', 'tqupb.utxn_id <= utxn.utxn_id', 'tqupb');

        $srch->addMultipleFields(array('utxn.*', "SUM(tqupb.bal) balance", "IF(utxn.utxn_credit > 0, ".static::CREDIT_TYPE.", ".static::DEBIT_TYPE.") as txnPaymentType"));
        $srch->addCondition('utxn.utxn_user_id', '=', $userId);
        $srch->addGroupBy('utxn.utxn_id');
        $srch->addOrder('utxn_id', 'DESC');
        return $srch;
    }
}
