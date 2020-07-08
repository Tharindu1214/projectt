<?php
class WithdrawalRequestsSearch extends SearchBase
{
    private $langId;
    private $joinUsers = false;
    private $commonLangId;
    const DB_TBL = 'tbl_user_withdrawal_requests';

    public function __construct()
    {
        parent::__construct(static::DB_TBL, 'tuwr');
        $this->commonLangId = CommonHelper::getLangId();
    }

    public function joinUsers($activeUser = false)
    {
        $this->joinUsers = true;
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'tuwr.withdrawal_user_id = tu.user_id', 'tu');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'tc.credential_user_id = tu.user_id', 'tc');

        if ($activeUser) {
            $this->addCondition('tc.credential_active', '=', applicationConstants::ACTIVE);
            $this->addCondition('tc.credential_verified', '=', applicationConstants::YES);
        }
    }

    public function joinForUserBalance()
    {
        if (!$this->joinUsers) {
            trigger_error(Labels::getLabel('ERR_You_must_join_joinUsers', $this->commonLangId), E_USER_ERROR);
        }
        $srch = new SearchBase(Transactions::DB_TBL, 'txn');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('txn.utxn_user_id');
        $srch->addCondition('txn.utxn_status', '=', Transactions::STATUS_COMPLETED);
        $srch->addMultipleFields(array('txn.utxn_user_id as userId',"SUM(utxn_credit - utxn_debit) as user_balance"));
        $qryUserBalance = $srch->getQuery();

        $this->joinTable('('.$qryUserBalance.')', 'LEFT OUTER JOIN', 'tu.user_id = tqub.userId', 'tqub');
    }
}
