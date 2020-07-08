<?php
class UpcCode extends MyAppModel
{
    const DB_TBL = 'tbl_upc_codes';
    const DB_TBL_PREFIX = 'upc_';

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'code_id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'upc_code');
        return $srch;
    }

    public static function getUpcCode($product_id, $optionvalue_id)
    {
        $product_id = FatUtility::int($product_id);
        $optionvalue_id = FatUtility::int($optionvalue_id);
        $db = FatApp::getDb();
        if (!$product_id || !$optionvalue_id) {
            trigger_error(Labels::getLabel('ERR_Invalid_Arguments', CommonHelper::getLangId()), E_USER_ERROR);
        }

        $srch = self::getSearchObject();

        $srch->addCondition(self::DB_TBL_PREFIX.'product_id', '=', $product_id);
        $srch->addCondition(self::DB_TBL_PREFIX.'options', '=', $optionvalue_id);
        $srch->addFld('upc_code');
        $rs = $srch->getResultSet();
        $code = $db->fetch($rs);
        return $code['upc_code'];
    }
}
