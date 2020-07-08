<?php
class Option extends MyAppModel
{
    const DB_TBL = 'tbl_options';
    const DB_LANG_TBL ='tbl_options_lang';
    const DB_TBL_PREFIX = 'option_';

    const OPTION_TYPE_SELECT = 1;
    const OPTION_TYPE_CHECKBOX = 2;
    const OPTION_TYPE_TEXT = 3;
    const OPTION_TYPE_TEXTAREA = 4;

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db=FatApp::getDb();
    }

    public static function getSearchObject($langId = 0, $isDeleted = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'o');

        if ($langId) {
            $srch->joinTable(
                Option::DB_TBL . '_lang',
                'LEFT OUTER JOIN',
                'ol.optionlang_option_id = o.option_id AND ol.optionlang_lang_id = ' . $langId,
                'ol'
            );
        }

        if ($isDeleted == true) {
            $srch->addCondition('o.'.static::DB_TBL_PREFIX.'deleted', '=', applicationConstants::NO);
        }
        return $srch;
    }

    public static function requiredOptionFields()
    {
        return array(
        ImportexportCommon::VALIDATE_POSITIVE_INT => array(
        'option_id',
        ),
        ImportexportCommon::VALIDATE_NOT_NULL => array(
        'option_identifier',
        'option_name',
        'option_seller_id',
        'credential_username',
        ),
        ImportexportCommon::VALIDATE_INT => array(
        'option_seller_id',
        ),
        );
    }

    public static function validateOptionFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredOptionFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredOptionValFields()
    {
        return array(
        ImportexportCommon::VALIDATE_POSITIVE_INT => array(
        'optionvalue_id',
        'optionvalue_option_id',
        ),
        ImportexportCommon::VALIDATE_NOT_NULL => array(
        'optionvalue_identifier',
        'option_identifier',
        'optionvalue_name',
        ),
        );
    }

    public static function validateOptionValFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredOptionValFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredProdOptionFields()
    {
        return array(
        ImportexportCommon::VALIDATE_POSITIVE_INT => array(
        'product_id',
        'option_id',
        ),
        ImportexportCommon::VALIDATE_NOT_NULL => array(
        'product_identifier',
        'option_identifier',
        ),
        );
    }

    public static function validateProdOptionFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredProdOptionFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function getOptionTypes($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }
        $arr = array(
        static::OPTION_TYPE_SELECT => Labels::getLabel('LBL_LISTBOX', $langId),
        /*When uncommened this then it must be handle in import/export*/
        /*  static::OPTION_TYPE_CHECKBOX => Labels::getLabel('LBL_CHECKBOX',$langId),
        static::OPTION_TYPE_TEXT => Labels::getLabel('LBL_TEXT',$langId),
        static::OPTION_TYPE_TEXTAREA => Labels::getLabel('LBL_TEXTAREA',$langId), */
        );
        return $arr;
    }

    public static function ignoreOptionValues()
    {
        return $arr=array(static::OPTION_TYPE_TEXT,static::OPTION_TYPE_TEXTAREA);
    }

    public function getOption($optionId)
    {
        $srch = self::getSearchObject();
        $srch->addCondition('option_id', '=', $optionId);
        $rs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($rs);
        if ($record) {
            $lang_record = CommonHelper::getLangFields(
                $optionId,
                'optionlang_option_id',
                'optionlang_lang_id',
                array('option_name'),
                static::DB_TBL.'_lang'
            );
            return  array_merge($record, $lang_record);
        }
        return $record;
    }


    /* public function getOptions($optionId = 0){
    $srch = self::getSearchObject();
    $srch->joinTable(static::DB_TBL.'_lang','LEFT OUTER JOIN',
    'o_l.optionlang_option_id = o.option_id','o_l');
    if($optionId > 0){
    $srch->addCondition('option_id','=',$optionId);
    }
    $srch->addOrder('option_name');
    return $srch;
    } */

    public function getMaxOrder($userId = 0)
    {
        $srch = new SearchBase(static::DB_TBL);
        $srch->addFld("MAX(" . static::DB_TBL_PREFIX . "display_order) as max_order");

        $userId=FatUtility::int($userId);
        if ($userId>0) {
            $srch->addCondition(static::DB_TBL_PREFIX.'seller_id', '=', $userId);
        }
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        if (!$rs) {
            return 1;
        }
        $record = FatApp::getDb()->fetch($rs);
        if (!empty($record)) {
            return $record['max_order']+1;
        }
        return 1;
    }

    public function canRecordMarkDelete($id)
    {
        $srch =static::getSearchObject();
        $srch->addCondition('o.'.static::DB_TBL_PREFIX.'id', '=', $id);
        $srch->addFld('o.'.static::DB_TBL_PREFIX.'id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row[static::DB_TBL_PREFIX.'id']==$id) {
            return true;
        }
        return false;
    }

    public function isLinkedWithProduct($id)
    {
        $srch = Product::getSearchObject();
        $srch->joinTable(Product::DB_PRODUCT_TO_OPTION, 'INNER JOIN', Product::DB_TBL_PREFIX.'id = '.Product::DB_PRODUCT_TO_OPTION_PREFIX.'product_id');
        $srch->joinTable(static::DB_TBL, 'INNER JOIN', static::DB_TBL_PREFIX.'id = '.Product::DB_PRODUCT_TO_OPTION_PREFIX.'option_id');
        $srch->addCondition(static::DB_TBL_PREFIX.'id', '=', $id);
        $srch->addFld('product_id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row)) {
            return true;
        }
        return false;
    }
}
