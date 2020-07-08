<?php
class Promotions extends MyAppModel
{
    const DB_TBL = 'tbl_promotions';
    const DB_TBL_PREFIX = 'promotion_';
    const DB_TBL_LANG ='tbl_promotions_lang';
    const DB_TBL_LANG_PREFIX ='promotionlang_';
    const DB_TBL_LOGS ='tbl_promotions_logs';
    const DB_TBL_LOGS_PREFIX ='lprom_';
    const DB_TBL_CLICKS = 'tbl_promotions_clicks' ;
    const DB_TBL_CLICKS_PREFIX ='pclick_';
    const DB_TBL_CHARGES = 'tbl_promotions_charges' ;
    const DB_TBL_CHARGES_PREFIX ='pcharge_';

    const PROMOTE_BANNER =3;
    const PROMOTE_SHOP =2;
    const PROMOTE_PRODUCT =1;

    private $langId = 0;
    public function __construct($id = 0)
    {
        $this->langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }
    public function getTotalPages()
    {
        return $this->total_pages;
    }
    public function getTotalRecords()
    {
        return $this->total_records;
    }
    public function getError()
    {
        return $this->error;
    }
    public function addUpdatePromotion($data)
    {
        $promotion_id = intval($data['promotion_id']);
        $promotion_user_id = intval($data['promotion_user_id']);
        unset($data['promotion_id']);
        unset($data['promotion_user_id']);
        if (($promotion_user_id < 1)) {
            $this->error = Labels::getLabel('LBL_Invalid_Request', $this->langId);
            return false;
        }
        if (isset($data['promotion_banner_url']) && $data['promotion_banner_url'] != '') {
            $assign_fields['promotion_banner_url'] = $data['promotion_banner_url'];
        }
        $record = new TableRecord(static::DB_TBL);
        $assign_fields = array(
        /* 'promotion_product_id' => $data['promotion_product_id'],
        'promotion_shop_id' => $data['promotion_shop_id'], */
        'promotion_identifier' => $data['promotion_identifier'],
        'promotion_banner_url' => $data['promotion_banner_url'],
        'promotion_banner_target' => $data['promotion_banner_target'],
        'promotion_cost' => $data['promotion_cost'],
        'promotion_budget' => $data['promotion_budget'],
        'promotion_budget_period' => $data['promotion_budget_period'],
        'promotion_start_date' => $data['promotion_start_date'],
        'promotion_end_date' => $data['promotion_end_date'],
        'promotion_start_time' => $data['promotion_start_time'],
        'promotion_end_time' => $data['promotion_end_time'],
        'promotion_status' => intval(1) ,
        );
        if (isset($data['promotion_banner_position']) && $data['promotion_banner_position'] != '') {
            $assign_fields['promotion_banner_position'] = $data['promotion_banner_position'];
        }
        /* if (isset($data['promotion_banner_file']) && $data['promotion_banner_file'] != '') {
        $assign_fields['promotion_banner_file'] = $data['promotion_banner_file'];
        $assign_fields['promotion_is_approved'] = 0;
        } */
        if ($promotion_id === 0) {
            $assign_fields['promotion_type'] = $data['promotion_type'];
            $assign_fields['promotion_user_id'] = $promotion_user_id;
            $assign_fields['promotion_resumption_date'] = date("Y-m-d H:i:s");
            $assign_fields['promotion_added_date'] = date("Y-m-d H:i:s");
        }
        if (($promotion_id === 0) && ($data['promotion_type'] != 3)) {
            $assign_fields['promotion_is_approved'] = 1;
        }
        $record->assignValues($assign_fields);
        if ($promotion_id === 0 && $record->addNew()) {
            $this->promotion_id = $record->getId();
        } elseif ($promotion_id > 0 && $record->update(
            array(
            'smt' => '`promotion_id`=? AND `promotion_user_id`=?',
            'vals' => array(
            $promotion_id,
            $promotion_user_id
            )
            )
        )) {
            $this->promotion_id = $promotion_id;
        } else {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        $this->loadData();
        return true;
    }
    protected function loadData()
    {
        $this->attributes = self::getPromotion($this->promotion_id);
    }
    public function getAttribute($attr)
    {
        return isset($this->attributes[$attr]) ? $this->attributes[$attr] : '';
    }
    public function getPromotion($promotion_id)
    {
        $promotion_id = intval($promotion_id);
        $srch = new SearchBase(static::DB_TBL, 'tp');
        $srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'tp.promotion_user_id = u.user_id', 'u');
        $srch->joinTable('tbl_user_credentials', 'LEFT OUTER JOIN', 'tp.promotion_user_id = uc.credential_user_id', 'uc');
        $srch->joinTable('tbl_products', 'LEFT OUTER JOIN', 'tp.promotion_product_id = p.product_id', 'p');
        $srch->joinTable('tbl_shops', 'LEFT OUTER JOIN', 'tp.promotion_shop_id = s.shop_id', 's');
        $srch->addCondition('promotion_id', '=', $promotion_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(
            array(
            'tp.*',
            'p.product_identifier',
            's.shop_identifier',
            'u.user_name',
            'uc.credential_email',
            '
		LPAD(tp.promotion_id, 6,"0") as promotion_number'
            )
        );
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetch($rs);
    }
    public function getUserPromotion($promotion_id, $user_id, $type = 0)
    {
        $promotion_id = intval($promotion_id);
        $user_id = intval($user_id);
        if ($promotion_id < 1 || $user_id < 1) {
            return false;
        }
        $srch = new SearchBase(static::DB_TBL, 'tp');
        $srch->joinTable('tbl_products', 'LEFT OUTER JOIN', 'tp.promotion_product_id = p.product_id', 'p');
        $srch->addCondition('promotion_id', '=', $promotion_id);
        $srch->addCondition('promotion_user_id', '=', $user_id);
        if ($type > 0) {
            $srch->addCondition('promotion_type', '=', $type);
        }
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(
            array(
            'tp.*',
            'p.product_identifier'
            )
        );
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetch($rs);
    }
    public function getPromotions($criterias)
    {
        $srch = new SearchBase(static::DB_TBL_LOGS, 'tpl');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('tpl.lprom_id');
        $srch->addMultipleFields(
            array(
            'tpl.lprom_id',
            "SUM(lprom_impressions) as totImpressions",
            "SUM(lprom_clicks) as totClicks",
            "SUM(lprom_orders) as totOrders"
            )
        );
        $qry_promotion_logs = $srch->getQuery();
        $srch = new SearchBase(static::DB_TBL_CLICKS, 'tpc');
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
        $qry_promotion_clicks = $srch->getQuery();
        $srch = new SearchBase('tbl_user_transactions', 'txn');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('txn.utxn_user_id');
        $srch->addMultipleFields(
            array(
            'txn.utxn_user_id',
            "SUM(utxn_credit-utxn_debit) as userBalance"
            )
        );
        $qry_user_balance = $srch->getQuery();
        $srch = new SearchBase(static::DB_TBL_CHARGES, 'tpc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('tpc.pcharge_promotion_id');
        $srch->addMultipleFields(
            array(
            'tpc.pcharge_promotion_id',
            "SUM(pcharge_charged_amount) as totPromotionPayments"
            )
        );
        $qry_promotion_payments = $srch->getQuery();
        $srch = new SearchBase(static::DB_TBL, 'tp');
        $srch->joinTable('(' . $qry_promotion_clicks . ')', 'LEFT OUTER JOIN', 'tp.promotion_id = tqpc.pclick_promotion_id', 'tqpc');
        $srch->joinTable('(' . $qry_promotion_payments . ')', 'LEFT OUTER JOIN', 'tp.promotion_id = tqpp.pcharge_promotion_id', 'tqpp');
        $srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'tp.promotion_user_id = u.user_id', 'u');
        $srch->joinTable('(' . $qry_user_balance . ')', 'LEFT OUTER JOIN', 'u.user_id = tqub.utxn_user_id', 'tqub');
        $srch->joinTable('tbl_products', 'LEFT OUTER JOIN', 'tp.promotion_product_id = p.product_id and tp.promotion_type=1', 'p');
        $srch->joinTable('tbl_shops', 'LEFT OUTER JOIN', 'tp.promotion_shop_id = s.shop_id and tp.promotion_type=2', 's');
        $srch->joinTable('tbl_promotions_lang', 'LEFT OUTER JOIN', 'tp.promotion_id = tpl.promotionlang_promotion_id and tpl.promotionlang_lang_id='.$this->langId, 'tpl');
        $srch->joinTable('(' . $qry_promotion_logs . ')', 'LEFT OUTER JOIN', 'tp.promotion_id = tqpl.lprom_id', 'tqpl');
        $srch->addCondition('promotion_is_deleted', '=', applicationConstants::NO);

        foreach ($criterias as $key => $val) {
            if (strval($val) == '') {
                continue;
            }
            switch ($key) {
                case 'promoter':
                    $srch->addCondition('user_name', 'like', '%' . $val . '%');
                    break;
                case 'user':
                    $srch->addCondition('promotion_user_id', '=', intval($val));
                    break;
                case 'shop':
                    $srch->addCondition('promotion_shop_id', '=', intval($val));
                    break;
                case 'product':
                    $srch->addCondition('promotion_product_id', '=', intval($val));
                    break;
                case 'type':
                    $srch->addCondition('promotion_type', '=', intval($val));
                    break;
                case 'position':
                    $srch->addCondition('promotion_banner_position', '=', $val);
                    break;
                case 'date_from':
                    $srch->addDirectCondition("('".FatApp::getDb()->quoteVariable($val)."' BETWEEN promotion_start_date and promotion_end_date)");
                    break;
                case 'date_to':
                    $srch->addDirectCondition("('".FatApp::getDb()->quoteVariable($val)."' BETWEEN promotion_start_date and promotion_end_date)");
                    break;
                case 'date_interval':
                    $arr = explode("~", $val);
                    $srch->addDirectCondition("((promotion_start_date BETWEEN ".FatApp::getDb()->quoteVariable($arr[0])." and ".FatApp::getDb()->quoteVariable($arr[1]).") OR (promotion_end_date BETWEEN ".FatApp::getDb()->quoteVariable($arr[0])." and '".FatApp::getDb()->quoteVariable($arr[1])."'))");
                    break;
                case 'impressions_from':
                    $srch->addCondition('totImpressions', '>=', intval($val));
                    break;
                case 'impressions_to':
                    $srch->addCondition('totImpressions', '<=', intval($val));
                    break;
                case 'clicks_from':
                    $srch->addCondition('totClicks', '>=', intval($val));
                    break;
                case 'clicks_to':
                    $srch->addCondition('totClicks', '<=', intval($val));
                    break;
                case 'status':
                    $srch->addCondition('promotion_status', '=', $val);
                    break;
                case 'approved':
                    $srch->addCondition('promotion_is_approved', '=', $val);
                    break;
                case 'page':
                    $srch->setPageNumber($val);
                    break;
                case 'order_by':
                    switch ($val) {
                        case 'cost':
                            $srch->addOrder('promotion_cost', 'desc');
                            break;
                        case 'random':
                            $srch->addOrder('rand()');
                            break;
                    }
                    break;
                case 'pagesize':
                    $srch->setPageSize($val);
                    break;
            }
        }
        $srch->addMultipleFields(
            array(
            'tp.*',
            'tpl.promotion_banner_name',
            'u.user_name',
            'u.user_id',
            //'u.user_email',
            's.shop_identifier',
            //'s.shop_logo',
            'p.product_identifier',
            'COALESCE(tqpl.totImpressions,0) as totImpressions',
            'COALESCE(tqpl.totClicks,0) as totClicks',
            'COALESCE(tqpl.totOrders,0) as totOrders',
            'COALESCE(tqpp.totPromotionPayments,0) as totPayments',
            'tqpc.*',
            'LPAD(tp.promotion_id, 6,"0") as promotion_number'
            )
        );
        $srch->addOrder('promotion_status', 'DESC');
        $srch->addOrder('promotion_id', 'DESC');
        //echo($srch->getquery()."<br/><br/>");
        $rs = $srch->getResultSet();
        $this->total_records = $srch->recordCount();
        $this->total_pages = $srch->pages();
        return FatApp::getDb()->fetchAll($rs);
        /* return $criterias["pagesize"] == 1 ? FatApp::getDb()->fetch($rs) : FatApp::getDb()->fetchAll($rs); */
    }
    public function updatePromotionStatus($promotion_id, $data_update = array())
    {
        $promotion_id = intval($promotion_id);
        if ($promotion_id < 1 || count($data_update) < 1) {
            $this->error = 'Error: Invalid request!!';
            return false;
        }
        if (FatApp::getDb()->updateFromArray(
            static::DB_TBL,
            $data_update,
            array(
            'smt' => '`promotion_id` = ?',
            'vals' => array(
            $promotion_id
            )
            )
        )) {
            return true;
        }
        $this->error = FatApp::getDb()->getError();
        return false;
    }
    public function getPromotionLogs($criterias)
    {
        $srch = new SearchBase(static::DB_TBL_LOGS, 'tpl');
        $srch->joinTable(static::DB_TBL, 'INNER JOIN', 'tpl.lprom_id = p.promotion_id', 'p');
        $srch->addCondition('promotion_is_deleted', '=', applicationConstants::NO);
        foreach ($criterias as $key => $val) {
            if (strval($val) == '') {
                continue;
            }
            switch ($key) {
                case 'id':
                    $srch->addCondition('promotion_id', '=', intval($val));
                    break;
                case 'user':
                    $srch->addCondition('promotion_user_id', '=', intval($val));
                    break;
                case 'date_from':
                    $srch->addCondition('lprom_date', '>=', $val . ' 00:00:00');
                    break;
                case 'date_to':
                    $srch->addCondition('lprom_date', '<=', $val . ' 23:59:59');
                    break;
                case 'page':
                    $srch->setPageNumber($val);
                    break;
                case 'pagesize':
                    $srch->setPageSize($val);
                    break;
            }
        }
        $srch->addMultipleFields(
            array(
            'tpl.*',
            'p.*'
            )
        );
        $rs = $srch->getResultSet();
        $this->total_records = $srch->recordCount();
        $this->total_pages = $srch->pages();
        return $criterias["pagesize"] ? FatApp::getDb()->fetch($rs) : FatApp::getDb()->fetchAll($rs);
    }


    public function addPromotionAnalysisRecord($data = array(), $column = 'impressions')
    {
        $promotion_id = str_replace('mysql_func_', 'mysql_func ', $data['promotion_id']);
        $assign_fields = array(
        'lprom_id' => $promotion_id,
        'lprom_date' => date('Y-m-d') ,
        );
        $onDuplicateKeyUpdate = array_merge(
            $assign_fields,
            array(
            'lprom_' . $column => 'mysql_func_lprom_' . $column . '+1'
            )
        );
        if ($column == "clicks") {
            if ($this->addPromotionClicksHistory($promotion_id)) {
                FatApp::getDb()->insert_from_array(static::DB_TBL_LOGS, $assign_fields, true, array('IGNORE'), $onDuplicateKeyUpdate);
            }
        } else {
            FatApp::getDb()->insert_from_array(static::DB_TBL_LOGS, $assign_fields, true, array('IGNORE'), $onDuplicateKeyUpdate);
        }
    }

    private function addPromotionClicksHistory($promotion_id)
    {
        $promotion_id = intval($promotion_id);
        $promotion = $this->getPromotion($promotion_id);
        $uObj = new User();
        $assign_fields = array();
        $assign_fields['pclick_datetime'] = date('Y-m-d H:i:s');
        $assign_fields['pclick_promotion_id'] = $promotion_id;
        $assign_fields['pclick_cost'] = $promotion['promotion_cost'];
        if ($uObj->isUserLogged()) {
            $user_id = $uObj->getLoggedUserId();
            ;
        }
        $assign_fields['pclick_user_id'] = $user_id;
        $assign_fields['pclick_ip'] = $_SERVER['REMOTE_ADDR'];
        $assign_fields['pclick_session_id'] = session_id();
        $onDuplicateKeyUpdate = array();
        $record = new TableRecord(static::DB_TBL_CLICKS);
        $record->assignValues($assign_fields);
        if ($record->addNew(array('IGNORE'))) {
            return FatApp::getDb()->insert_id()>0?true:false;
        }
        return false;
    }
    public function getPromotionLastChargedEntry($promotion_id)
    {
        $promotion_id = intval($promotion_id);
        if ($promotion_id > 0 != true) {
            return array();
        }
        $srch = new SearchBase(static::DB_TBL_CHARGES, 'tpc');
        $srch->addCondition('tpc.pcharge_promotion_id', '=', $promotion_id);
        $srch->addOrder('pcharge_id', 'desc');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if ($row == false) {
            return array();
        } else {
            return $row;
        }
    }
    public function getPromotionPayments($criterias)
    {
        $srch = new SearchBase(static::DB_TBL_CHARGES, 'tpc');
        $srch->joinTable('tbl_users', 'LEFT JOIN', 'tpc.pcharge_user_id = u.user_id', 'u');
        $srch->addMultipleFields(
            array(
            'tpc.*',
            "u.user_name"
            )
        );
        foreach ($criterias as $key => $val) {
            if (strval($val) == '') {
                continue;
            }
            switch ($key) {
                case 'promotion':
                    $srch->addCondition('pcharge_promotion_id', '=', intval($val));
                    break;
                case 'date_from':
                    $srch->addCondition('pcharge_date', '>=', $val . ' 00:00:00');
                    break;
                case 'date_to':
                    $srch->addCondition('pcharge_date', '<=', $val . ' 23:59:59');
                    break;
                case 'page':
                    $srch->setPageNumber($val);
                    break;
                case 'pagesize':
                    $srch->setPageSize($val);
                    break;
            }
        }
        $rs = $srch->getResultSet();
        $this->total_records = $srch->recordCount();
        $this->total_pages = $srch->pages();
        return FatApp::getDb()->fetchAll($rs);
    }
    public function getPromotionClicks($criterias)
    {
        $srch = new SearchBase(static::DB_TBL_CLICKS, 'tpc');
        $srch->joinTable('tbl_users', 'LEFT JOIN', 'tpc.pclick_user_id = u.user_id', 'u');
        $srch->addMultipleFields(
            array(
            'tpc.*',
            "u.user_name"
            )
        );
        foreach ($criterias as $key => $val) {
            if (strval($val) == '') {
                continue;
            }
            switch ($key) {
                case 'promotion':
                    $srch->addCondition('pclick_promotion_id', '=', intval($val));
                    break;
                case 'date_from':
                    $srch->addCondition('pclick_datetime', '>=', $val . ' 00:00:00');
                    break;
                case 'date_to':
                    $srch->addCondition('pclick_datetime', '<=', $val . ' 23:59:59');
                    break;
                case 'page':
                    $srch->setPageNumber($val);
                    break;
                case 'pagesize':
                    $srch->setPageSize($val);
                    break;
            }
        }
        $rs = $srch->getResultSet();
        $this->total_records = $srch->recordCount();
        $this->total_pages = $srch->pages();
        return FatApp::getDb()->fetchAll($rs);
    }
    public function getPromotionClicksSummary($criterias)
    {
        $srch = new SearchBase(static::DB_TBL_CLICKS, 'tpc');
        $srch->joinTable(static::DB_TBL, 'LEFT JOIN', 'tpc.pclick_promotion_id = tp.promotion_id', 'tp');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('tpc.pclick_promotion_id');
        $srch->addMultipleFields(
            array(
            'tpc.pclick_promotion_id',
            'tp.promotion_user_id',
            "count(pclick_id) as total_clicks",
            "SUM(pclick_cost) as total_cost",
            "MIN(pclick_id) as start_click_id",
            "MAX(pclick_id) as end_click_id",
            "MIN(pclick_datetime) as start_click_date",
            "MAX(pclick_datetime) as end_click_date"
            )
        );
        foreach ($criterias as $key => $val) {
            if (strval($val) == '') {
                continue;
            }
            switch ($key) {
                case 'promotion':
                    $srch->addCondition('pclick_promotion_id', '=', intval($val));
                    break;
                case 'date':
                    $srch->addCondition('pclick_datetime', '>', $val . ' 00:00:00');
                    break;
                case 'start_id':
                    $srch->addCondition('pclick_id', '>', intval($val));
                    //echo($srch->getquery()."<br/><br/>");
                    break;
            }
        }
        //echo($srch->getquery()."<br/><br/>");
        $rs = $srch->getResultSet();
        $this->total_records = $srch->recordCount();
        $this->total_pages = $srch->pages();
        return ($criterias["pagesize"] == 1) ? FatApp::getDb()->fetch($rs) : FatApp::getDb()->fetchAll($rs);
    }
    public function addUpdatePromotionCharges($data)
    {
        $pcharge_user_id = intval($data['user_id']);
        $pcharge_promotion_id = intval($data['promotion_id']);
        $charged_amount = $data['total_cost'];
        if (($pcharge_user_id > 0 != true) || ($pcharge_promotion_id > 0 != true) || ($charged_amount > 0 != true)) {
            return array();
        }
        $record = new TableRecord(static::DB_TBL_CHARGES);
        $assign_fields = array();
        $assign_fields['pcharge_user_id'] = $pcharge_user_id;
        $assign_fields['pcharge_promotion_id'] = $pcharge_promotion_id;
        $assign_fields['pcharge_charged_amount'] = $charged_amount;
        $assign_fields['pcharge_clicks'] = $data['total_clicks'];
        $assign_fields['pcharge_date'] = date("Y-m-d H:i:s");
        $assign_fields['pcharge_start_click_id'] = $data['start_click_id'];
        $assign_fields['pcharge_end_click_id'] = $data['end_click_id'];
        $assign_fields['pcharge_start_date'] = $data['start_click_date'];
        $assign_fields['pcharge_end_date'] = $data['end_click_date'];
        $record->assignValues($assign_fields);
        if ($record->addNew()) {
            $this->charge_log_id = $record->getId();
            $transObj = new Transactions();
            $formatted_request_value = "#" . str_pad($pcharge_promotion_id, 6, '0', STR_PAD_LEFT);
            $txnArray["utxn_user_id"] = $pcharge_user_id;
            $txnArray["utxn_debit"] = $charged_amount;
            $txnArray["utxn_credit"] = 0;
            $txnArray["utxn_status"] = 1;
            $txnArray["utxn_comments"] = sprintf(Labels::getLabel('LBL_Charges_for_promotion_from_duration', $this->langId), $formatted_request_value, FatDate::format($assign_fields['pcharge_start_date'], true), FatDate::format($assign_fields['pcharge_end_date'], true), $assign_fields['pcharge_clicks']);
            if ($txn_id = $transObj->addTransaction($txnArray)) {
                $emailNotificationObj = new Emailnotifications();
                $emailNotificationObj->sendTxnNotification($txn_id);
            }
        } else {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return $this->charge_log_id;
    }
    public function getDistinctPromotionMembers($name)
    {
        $srch = new SearchBase(static::DB_TBL, 'tp');
        $srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'tp.promotion_user_id = tu.user_id', 'tu');
        $srch->addMultipleFields(
            array(
            "tu.user_id",
            "CONCAT(tu.user_name,' (',tu.user_username,')') as name"
            )
        );
        $cndCondition = $srch->addCondition('tu.user_name', 'like', '%' . $name . '%');
        $cndCondition->attachCondition('tu.user_username', 'like', '%' . $name . '%', 'OR');
        $srch->setPageSize(10);
        $srch->addGroupBY('promotion_user_id');
        $rs = $srch->getResultSet();
        $this->total_records = $srch->recordCount();
        if ($this->total_records < 1) {
            return false;
        }
        return FatApp::getDb()->fetchAll($rs);
    }
}
