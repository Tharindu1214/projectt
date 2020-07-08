<?php
class AffiliateCommission extends MyAppModel
{
    const DB_TBL = 'tbl_affiliate_commission_settings';
    const DB_TBL_HISTORY = 'tbl_affiliate_commission_setting_history';
    const DB_TBL_PREFIX = 'afcommsetting_';
    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'afcs');
        return $srch;
    }

    public static function getHistorySearchObject()
    {
        $srch = new SearchBase(static::DB_TBL_HISTORY, 'tacsh');

        $srch->addOrder('tacsh.acsh_added_on', 'DESC');
        return $srch;
    }

    public static function getAffiliateCommission($affiliateUserId, $product_id, $prodObj)
    {
        $affiliateUserId = FatUtility::int($affiliateUserId);
        $product_id = FatUtility::int($product_id);
        $catIds = array();
        $productCategories = $prodObj->getProductCategories($product_id);
        if ($productCategories) {
            foreach ($productCategories as $catId) {
                $catIds[] = $catId['prodcat_id'];
            }
        }

        $categoryArrCondition = '';
        if (!empty($catIds)) {
            $categoryArrCondition = " AND afcommsetting_prodcat_id IN (".implode(",", $catIds).")";
        }

        $sql = "SELECT afcommsetting_fees,
		CASE
			WHEN afcommsetting_prodcat_id > 0 ".$categoryArrCondition." THEN 1
			WHEN afcommsetting_prodcat_id = 0 THEN 2
		END
		AS matches FROM ".static::DB_TBL." where afcommsetting_user_id = " . $affiliateUserId . " or afcommsetting_user_id = 0
		ORDER BY matches ASC, afcommsetting_user_id DESC, afcommsetting_fees DESC LIMIT 0,1";

        $rs = FatApp::getDb()->query($sql);
        if ($row = FatApp::getDb()->fetch($rs)) {
            return $row['afcommsetting_fees'];
        } else {
            return 0;
        }
    }

    public function addAffiliateCommissionHistory($commissionId)
    {
        $data = AffiliateCommission::getAttributesById($commissionId);
        $assignValues = array(
        'acsh_afcommsetting_id' =>$data['afcommsetting_id'],
        'acsh_afcommsetting_prodcat_id' =>$data['afcommsetting_prodcat_id'],
        'acsh_afcommsetting_user_id' =>$data['afcommsetting_user_id'],
        'acsh_afcommsetting_fees' =>$data['afcommsetting_fees'],
        'acsh_afcommsetting_is_mandatory' =>$data['afcommsetting_is_mandatory'],
        'acsh_added_on' =>date('Y-m-d H:i:s'),
        );
        if ($this->db->insertFromArray(static::DB_TBL_HISTORY, $assignValues)) {
            return true;
        }

        $this->error = $this->db->getError();
        return false;
    }

    public static function getAffiliateCommissionHistoryObj($langId)
    {
        $langId = FatUtility::int($langId);

        $srch = self::getHistorySearchObject();

        $srch->joinTable(ProductCategory::DB_TBL, 'LEFT OUTER JOIN', 'tpc.prodcat_id = tacsh.acsh_afcommsetting_prodcat_id', 'tpc');
        $srch->joinTable(ProductCategory::DB_LANG_TBL, 'LEFT OUTER JOIN', 'tpc_l.prodcatlang_prodcat_id = tpc.prodcat_id and tpc_l.prodcatlang_lang_id ='.$langId, 'tpc_l');

        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'tacsh.acsh_afcommsetting_user_id = tu.user_id', 'tu');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'tuc.credential_user_id = tu.user_id', 'tuc');

        $srch->addMultipleFields(
            array(
            'tacsh.*',
            'IFNULL(tpc_l.prodcat_name,tpc.prodcat_identifier)as prodcat_name',
            'CONCAT(tu.user_name," [",tuc.credential_username,"]") as vendor'
            )
        );

        return $srch;
    }
}
