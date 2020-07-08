<?php
class PolicyPoint extends MyAppModel
{
    const DB_TBL = 'tbl_policy_points';
    const DB_TBL_PREFIX = 'ppoint_';

    const DB_TBL_LANG = 'tbl_policy_points_lang';

    const PPOINT_TYPE_WARRANTY = 1;
    const PPOINT_TYPE_RETURN = 2;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($langId = 0, $active = true, $deleted = true)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'pp');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'pp_l.ppointlang_ppoint_id = pp.ppoint_id
			AND ppointlang_lang_id = ' . $langId,
                'pp_l'
            );
        }
        if ($active == true) {
            $srch->addCondition('pp.ppoint_active', '=', applicationConstants::ACTIVE);
        }

        if ($deleted == true) {
            $srch->addCondition('pp.ppoint_deleted', '=', applicationConstants::NO);
        }
        return $srch;
    }

    public static function requiredFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'ppoint_id',
                'ppoint_type',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'ppoint_identifier',
                'ppoint_type_identifier',
            ),
        );
    }

    public static function validateFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function getPolicyPointTypesArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
            static::PPOINT_TYPE_WARRANTY => Labels::getLabel('LBL_Warranty', $langId),
            static::PPOINT_TYPE_RETURN => Labels::getLabel('LBL_Return', $langId),
        );
    }

    public function canRecordMarkDelete($ppointId)
    {
        $srch = static::getSearchObject(0, false);
        $srch->addCondition('ppoint_deleted', '=', applicationConstants::NO);
        $srch->addCondition('ppoint_id', '=', $ppointId);
        $srch->addFld('ppoint_id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row['ppoint_id']==$ppointId) {
            return true;
        }
        return false;
    }
}
