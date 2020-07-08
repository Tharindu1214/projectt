<?php
class SlideSearch extends SearchBase
{
    private $langId;
    private $joinedPromotion = false;
    private $joinedUserWallet = false;

    public function __construct($langId = 0, $isActive = true)
    {
        $this->langId = FatUtility::int($langId);
        parent::__construct(Slides::DB_TBL, 'sl');

        if ($langId > 0) {
            $this->joinTable(
                Slides::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'sl_l.slidelang_slide_id = sl.slide_id
			AND sl_l.slidelang_lang_id = ' . $langId,
                'sl_l'
            );
        }

        if ($isActive) {
            $this->addCondition('sl.slide_active', '=', applicationConstants::ACTIVE);
        }
    }



    public function joinPromotions($langId = 0, $activeOnly = true, $approvedOnly = true, $deleted = true)
    {
        $this->joinedPromotion = true;
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        if ($activeOnly) {
            $this->addFld(array('IF(pr.promotion_id > 0, promotion_active,1) AS promotionActive'));
            $this->addHaving('promotionActive', '=', applicationConstants::ACTIVE);
        }

        if ($approvedOnly) {
            $this->addFld(array('IF(pr.promotion_id > 0, promotion_approved,1) AS promotionApproved'));
            $this->addHaving('promotionApproved', '=', applicationConstants::YES);
        }

        if ($deleted) {
            $this->addFld(array('IF(pr.promotion_id > 0, promotion_deleted,0) AS promotionDeleted'));
            $this->addHaving('promotionDeleted', '=', applicationConstants::NO);
        }



        $this->joinTable(Promotion::DB_TBL, 'LEFT OUTER JOIN', 'pr.promotion_id = sl.slide_record_id and sl.slide_type = '.Slides::TYPE_PPC, 'pr');
        if ($langId > 0) {
            $this->joinTable(Promotion::DB_LANG_TBL, 'LEFT OUTER JOIN', 'pr_l.promotionlang_promotion_id = pr.promotion_id AND pr_l.promotionlang_lang_id = ' . $langId, 'pr_l');
        }
    }

    public function joinActiveUser($isActive = true)
    {
        $this->joinTable(User::DB_TBL, 'LEFT JOIN', 'pr.promotion_user_id = u.user_id', 'u');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT JOIN', 'cu.credential_user_id = u.user_id', 'cu');
        if ($isActive) {
            $this->addFld(array('IF(pr.promotion_id > 0, credential_active,1) AS credential_active'));
            $this->addHaving('credential_active', '=', applicationConstants::ACTIVE);
        }
    }

    public function addSkipExpiredPromotionAndSlideCondition($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        if (!$this->joinedPromotion) {
            trigger_error(Labels::getLabel('ERR_please_join_promotions', $langId), E_USER_ERROR);
        }



        $this->addFld(array('if(sl.slide_type = '.Slides::TYPE_PPC.',pr.promotion_start_date,"0000-00-00") as start_date'));
        $this->addFld(array('if(sl.slide_type = '.Slides::TYPE_PPC.',pr.promotion_end_date,"0000-00-00") as end_date'));

        $this->addFld(array('if(sl.slide_type = '.Slides::TYPE_PPC.',pr.promotion_start_time,"00:00:00") as start_time'));
        $this->addFld(array('if(sl.slide_type = '.Slides::TYPE_PPC.',pr.promotion_end_time,"00:00:00") as end_time'));


        $this->addFld(array('if(sl.slide_type = '.Slides::TYPE_PPC.',pr.promotion_duration,'.Promotion::DURATION_NOT_AVAILABALE.') as promotion_duration'));

        $this->addFld(array('if(sl.slide_type = '.Slides::TYPE_PPC.',pr.promotion_budget,-1) as promotion_budget'));


        $cnd = $this->addHaving('start_date', '=', '0000-00-00 00:00:00');
        $cnd->attachCondition('start_date', '<=', date('Y-m-d 00:00:00'), 'OR');

        $cnd = $this->addHaving('end_date', '=', '0000-00-00');
        $cnd->attachCondition('end_date', '>=', date('Y-m-d 00:00:00'), 'OR');


        $cnd = $this->addHaving('start_time', '=', '00:00:00');
        $cnd->attachCondition('start_time', '<=', date('H:i:s'), 'OR');

        $cnd = $this->addHaving('end_time', '=', '00:00:00');
        $cnd->attachCondition('end_time', '>=', date('H:i:s'), 'OR');
    }

    public function addActivePromotionCondition()
    {
        $this->addCondition('pr.promotion_active', '=', applicationConstants::ACTIVE);
    }

    public function addApprovedPromotionCondition()
    {
        $this->addCondition('pr.promotion_approved', '=', applicationConstants::ACTIVE);
    }
    public function addPromotionTypeCondition()
    {
        $cnd = $this->addCondition('pr.promotion_type', '=', PROMOTION::TYPE_SLIDES);
        $cnd->attachCondition('slide_type', '=', SLIDES::TYPE_SLIDE, 'OR');
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

        $this->addFld(array('IF(pr.promotion_id > 0, userBalance,'.FatApp::getConfig('CONF_PPC_MIN_WALLET_BALANCE', FatUtility::VAR_INT, 0).') AS userBalance'));
        $this->addHaving('userBalance', '>=', FatApp::getConfig('CONF_PPC_MIN_WALLET_BALANCE'));
    }

    public function joinBudget()
    {
        $srch = new SearchBase(Promotion::DB_TBL_ITEM_CHARGES, 'tpic');
        $srch->joinTable(Promotion::DB_TBL_CLICKS, 'LEFT OUTER JOIN', 'tpc.'.Promotion::DB_TBL_CLICKS_PREFIX.'id = tpic.'.Promotion::DB_TBL_ITEM_CHARGES_PREFIX.'pclick_id', 'tpc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('tpc.'.Promotion::DB_TBL_CLICKS_PREFIX.'promotion_id');
        $srch->addMultipleFields(
            array(
            'tpc.pclick_promotion_id',
            "SUM(IF(date(`picharge_datetime`)>CURRENT_DATE - INTERVAL 1 DAY,`picharge_cost`,0)) daily_cost,
			SUM(IF(date(`picharge_datetime`)>CURRENT_DATE - INTERVAL 1 WEEK,`picharge_cost`,0)) weekly_cost,
			SUM(IF(date(`picharge_datetime`)>CURRENT_DATE - INTERVAL 1 MONTH,`picharge_cost`,0)) monthly_cost",
            "SUM(picharge_cost) as total_cost"
            )
        );

        $this->joinTable('(' . $srch->getQuery() . ')', 'LEFT OUTER JOIN', 'pr.promotion_id =pclick_promotion_id', 'pcb');
    }

    public function joinAttachedFile()
    {
        $this->joinTable(AttachedFile::DB_TBL, 'INNER  JOIN', 'af.afile_record_id = sl.slide_id and afile_type ='.AttachedFile::FILETYPE_HOME_PAGE_BANNER, 'af');
        $this-> addGroupBy('slide_id');
    }
}
