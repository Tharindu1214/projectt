<?php
class EmptyCartItems extends MyAppModel
{
    const DB_TBL = 'tbl_empty_cart_items';
    const DB_LANG_TBL ='tbl_empty_cart_items_lang';
    const DB_TBL_PREFIX = 'emptycartitem_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($langId = 0, $isActive = true)
    {
        $srch = new SearchBase(static::DB_TBL);

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_LANG_TBL,
                'LEFT OUTER JOIN',
                'emptycartitemlang_emptycartitem_id = emptycartitem_id
			AND emptycartitemlang_lang_id = ' . $langId
            );
        }

        if ($isActive) {
            $srch->addCondition('emptycartitem_active', '=', applicationConstants::ACTIVE);
        }

        $srch->addOrder(static::DB_TBL_PREFIX . 'active', 'DESC');
        $srch->addOrder(static::DB_TBL_PREFIX . 'display_order');
        return $srch;
    }

    public function canRecordMarkDelete($id)
    {
        $srch =static::getSearchObject(0, false);
        $srch->addCondition(static::DB_TBL_PREFIX.'id', '=', $id);
        $srch->addFld(static::DB_TBL_PREFIX.'id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row[static::DB_TBL_PREFIX.'id']==$id) {
            return true;
        }
        return false;
    }
}
