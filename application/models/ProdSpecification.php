<?php
class ProdSpecification extends MyAppModel
{
    const DB_TBL = 'tbl_product_specifications';
    const DB_TBL_PREFIX = 'prodspec_';


    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db=FatApp::getDb();
    }
    public static function getSearchObject($langId = 0, $bothLanguageData = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'ps');
        $langQuery = '';
        if ($langId  || $bothLanguageData) {
            if (!$bothLanguageData) {
                $langQuery= 'AND psl.prodspeclang_lang_id = ' . $langId;
            }
            $srch->joinTable(
                static::DB_TBL . '_lang',
                'LEFT OUTER JOIN',
                'psl.prodspeclang_prodspec_id = ps.prodspec_id '.$langQuery,
                'psl'
            );
        }

        return $srch;
    }

    public static function requiredFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'product_id',
                'prodspeclang_lang_id',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'product_identifier',
                'prodspeclang_lang_code',
                'prodspec_name',
                'prodspec_value',
            ),
        );
    }

    public static function validateFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function getProdSpecification($prodSpecId, $productId, $langId = 0, $values = true)
    {
        $srch= static::getSearchObject($langId, $values);
        if ($prodSpecId) {
            $srch->addCondition('ps.prodspec_id', '=', $prodSpecId);
        }
        if ($productId) {
            $srch->addCondition('ps.prodspec_product_id', '=', $productId);
        }

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $data = $db->fetchAll($rs);

        return $data;
    }
}
