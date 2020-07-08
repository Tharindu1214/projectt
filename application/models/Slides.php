<?php
class Slides extends MyAppModel
{
    const DB_TBL = 'tbl_slides';
    const DB_TBL_PREFIX = 'slide_';
    const DB_LANG_TBL ='tbl_slides_lang';

    const TYPE_SLIDE = 1;
    const TYPE_PPC = 2;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSlideTypesArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
            static::TYPE_SLIDE => Labels::getLabel('LBL_Slide', $langId),
            static::TYPE_PPC => Labels::getLabel('LBL_Promotion', $langId),
        );
    }

    public static function getSearchObject($langId = 0, $isActive = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'sl');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'slidelang_slide_id = slide_id
			AND slidelang_lang_id = ' . $langId
            );
        }

        if ($isActive) {
            $srch->addCondition('slide_active', '=', applicationConstants::ACTIVE);
        }
        return $srch;
    }

    public static function getSlidesWithPromotionObj($langId = 0, $isActive = true)
    {
        $srch = static::getSearchObject($langId, $isActive);

        $srch->joinTable(
            Promotion::DB_TBL,
            'LEFT OUTER JOIN',
            'sl.slide_type = '.Slides::TYPE_PPC.' and sl.slide_record_id = pr.promotion_id',
            'pr'
        );
        if ($langId) {
            $srch->joinTable(
                Promotion::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'pr_l.promotionlang_promotion_id = pr.promotion_id
			AND pr_l.promotionlang_lang_id = ' . $langId,
                'pr_l'
            );
        }

        $srch->addFld(array('if(sl.slide_type = '.Slides::TYPE_PPC.',pr.promotion_start_date,"0000-00-00") as start_date'));
        $srch->addFld(array('if(sl.slide_type = '.Slides::TYPE_PPC.',pr.promotion_end_date,"0000-00-00") as end_date'));

        $srch->addFld(array('if(sl.slide_type = '.Slides::TYPE_PPC.',pr.promotion_start_time,"00:00:00") as start_time'));
        $srch->addFld(array('if(sl.slide_type = '.Slides::TYPE_PPC.',pr.promotion_end_time,"00:00:00") as end_time'));

        $srch->addFld(array('if(sl.slide_type = '.Slides::TYPE_PPC.',pr.promotion_duration,-1) as promotion_duration'));
        $srch->addFld(array('if(sl.slide_type = '.Slides::TYPE_PPC.',pr.promotion_budget,-1) as promotion_budget'));

        $cnd = $srch->addHaving('start_date', '=', '0000-00-00');
        $cnd->attachCondition('start_date', '<=', date('Y-m-d'), 'OR');

        $cnd = $srch->addHaving('end_date', '=', '0000-00-00');
        $cnd->attachCondition('end_date', '>=', date('Y-m-d'), 'OR');


        $cnd = $srch->addHaving('start_time', '=', '00:00:00');
        $cnd->attachCondition('start_time', '<=', date('H:i:s'), 'OR');

        $cnd = $srch->addHaving('end_time', '=', '00:00:00');
        $cnd->attachCondition('end_time', '>=', date('H:i:s'), 'OR');

        if ($isActive) {
            $srch->addDirectCondition("( (isnull(promotion_approved) or promotion_approved = ".applicationConstants::YES.") and (isnull(promotion_deleted) or promotion_deleted = ".applicationConstants::NO."))");
        }

        return $srch;
    }

    public function joinUserWallet()
    {
        $this->joinedUserWallet = true;
        $txnObj = new Transactions();
        $srch = $txnObj -> getSearchObject();
        $srch->addMultipleFields(array('IFNULL(SUM(utxn.utxn_credit)-SUM(utxn.utxn_debit),0) AS userBalance','utxn_user_id'));
        $srch->doNotCalculateRecords();
        $srch->doNotlimitRecords();
        $srch->addCondition('utxn_status', '=', applicationConstants::ACTIVE);
        $srch->addGroupBy('utxn_user_id');

        $this->joinTable('(' . $srch->getQuery() . ')', 'LEFT OUTER JOIN', 'pr.promotion_user_id = uw.utxn_user_id ', 'uw');
    }

    public function addMinimiumWalletbalanceCondition($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        if (!$this->joinedUserWallet) {
            trigger_error(Labels::getLabel('ERR_please_join_user_wallet', $langId), E_USER_ERROR);
        }

        $this->addFld(array('IF(pr.promotion_id > 0, userBalance,'.FatApp::getConfig('CONF_PPC_MIN_WALLET_BALANCE').') AS userBalance'));
        $this->addHaving('userBalance', '>=', FatApp::getConfig('CONF_PPC_MIN_WALLET_BALANCE'));
    }

    public function joinBudget()
    {
        $srch = new SearchBase(Promotion::DB_TBL_CLICKS, 'tpc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('tpc.pclick_promotion_id');
        $srch->addMultipleFields(
            array(
            'tpc.pclick_promotion_id',
            "SUM(IF(`pclick_datetime`>CURRENT_DATE - INTERVAL 1 DAY,`pclick_cost`,0)) daily_cost,
   SUM(IF(`pclick_datetime`>CURRENT_DATE - INTERVAL 1 WEEK,`pclick_cost`,0)) weekly_cost,
   SUM(IF(`pclick_datetime`>CURRENT_DATE - INTERVAL 1 MONTH,`pclick_cost`,0)) monthly_cost",
            "SUM(pclick_cost) as total_cost"
            )
        );

        $this->joinTable('(' . $srch->getQuery() . ')', 'LEFT OUTER JOIN', 'pr.promotion_id = pcb.pclick_promotion_id', 'pcb');
    }

    public static function setLastModified($slide_id){
        $where = array('smt'=>'slide_id = ?', 'vals'=>array($slide_id));
        FatApp::getDb()->updateFromArray(static::DB_TBL, array('slide_img_updated_on'=>date('Y-m-d  H:i:s')), $where);
    }
}
