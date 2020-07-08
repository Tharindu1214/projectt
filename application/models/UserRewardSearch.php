<?php
class UserRewardSearch extends SearchBase
{
    private $langId;

    public function __construct($langId = 0)
    {
        parent::__construct(UserRewards::DB_TBL, 'urp');
        $this->langId = FatUtility::int($langId);
    }

    public function joinUserRewardBreakup()
    {
        $this->joinTable(UserRewardBreakup::DB_TBL, 'LEFT OUTER JOIN', 'urp.urp_id = urpb.urpbreakup_urp_id', 'urpb');
    }

    public function joinUser()
    {
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'urp.urp_user_id = u.user_id', 'u');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'u.user_id = uc.credential_user_id', 'uc');
    }

    public function joinReferalUser()
    {
    }
}
