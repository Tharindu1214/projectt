<?php
class ProductGroup extends MyAppModel
{
    const DB_TBL = 'tbl_product_groups';
    const DB_TBL_LANG ='tbl_product_groups_lang';
    const DB_TBL_PREFIX = 'prodgroup_';

    const DB_PRODUCT_TO_GROUP = 'tbl_product_to_groups';
    const DB_PRODUCT_TO_GROUP_PREFIX = 'ptg_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObj($langId = 0, $active = true)
    {
        $langId =  FatUtility::int($langId);

        $srch = new SearchBase(static::DB_TBL, 'b');

        if ($active) {
            $srch->addCondition('b.prodgroup_active', '=', applicationConstants::ACTIVE);
        }

        if ($langId) {
            $srch->joinTable(
                static::DB_TBL . '_lang',
                'LEFT OUTER JOIN',
                'b_l.prodgrouplang_prodgroup_id = b.prodgroup_id AND b_l.prodgrouplang_lang_id = ' . $langId,
                'b_l'
            );
        }
        return $srch;
    }

    public function addUpdateProductToGroup($prodgroup_id, $selprod_id)
    {
        $prodgroup_id = FatUtility::int($prodgroup_id);
        $selprod_id = FatUtility::int($selprod_id);
        if (!$prodgroup_id || !$selprod_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            ;
            return false;
        }
        $record = new TableRecord(static::DB_PRODUCT_TO_GROUP);
        $to_save_arr = array();
        $to_save_arr[static::DB_PRODUCT_TO_GROUP_PREFIX.'prodgroup_id'] = $prodgroup_id;
        $to_save_arr[static::DB_PRODUCT_TO_GROUP_PREFIX.'selprod_id'] = $selprod_id;

        /* check current record is first record, then mark it as main product[ */
        $srch = new ProductGroupProductSearch(0, $prodgroup_id);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!$row) {
            $to_save_arr[static::DB_PRODUCT_TO_GROUP_PREFIX.'is_main_product'] = 1;
        }
        /* ] */

        $record->assignValues($to_save_arr);
        if (!$record->addNew(array(), $to_save_arr)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function removeProductToGroup($prodgroup_id, $selprod_id)
    {
        $db = FatApp::getDb();
        $prodgroup_id = FatUtility::int($prodgroup_id);
        $selprod_id = FatUtility::int($selprod_id);
        if ($prodgroup_id <= 0 || $selprod_id <= 0) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            ;
            return false;
        }

        if (!$db->deleteRecords(ProductGroup::DB_PRODUCT_TO_GROUP, array('smt'=> ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX.'prodgroup_id = ? AND '.ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX . 'selprod_id = ?','vals' => array($prodgroup_id, $selprod_id) ))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function setMainProductFromGroup($prodgroup_id, $selprod_id)
    {
        $db = FatApp::getDb();
        $prodgroup_id = FatUtility::int($prodgroup_id);
        $selprod_id = FatUtility::int($selprod_id);
        if ($prodgroup_id <= 0 || $selprod_id <= 0) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            ;
            return false;
        }

        $db->updateFromArray(ProductGroup::DB_PRODUCT_TO_GROUP, array('ptg_is_main_product' => 0), array( 'smt' => 'ptg_prodgroup_id = ?', 'vals' => array($prodgroup_id) ));

        $db->updateFromArray(ProductGroup::DB_PRODUCT_TO_GROUP, array('ptg_is_main_product' => 1), array( 'smt' => 'ptg_prodgroup_id = ? AND ptg_selprod_id = ?', 'vals' => array( $prodgroup_id, $selprod_id )));
        return true;
    }
}
