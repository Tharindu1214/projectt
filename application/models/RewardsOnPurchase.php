<?php
class RewardsOnPurchase extends MyAppModel
{
    const DB_TBL = 'tbl_rewards_on_purchase';
    const DB_TBL_PREFIX = 'rop_';
    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($deleteOnly = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'rop');

        /* if($deleteOnly==true){
        $srch->addCondition('rop.'.static::DB_TBL_PREFIX.'deleted', '=', 0);
        } */

        return $srch;
    }
}
