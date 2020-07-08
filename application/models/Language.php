<?php
class Language extends MyAppModel
{
    const DB_TBL = 'tbl_languages';
    const DB_TBL_PREFIX = 'language_';
    public function __construct($langId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $langId);
        $this->objMainTableRecord->setSensitiveFields(array( ));
    }

    public static function getSearchObject($isActive = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'l');

        if ($isActive == true) {
            $srch->addCondition('l.' . static::DB_TBL_PREFIX.'active', '=', applicationConstants::ACTIVE);
        }
        return $srch;
    }

    public static function getAllNames($assoc = true, $recordId = 0, $active = true, $deleted = false)
    {
        $srch = new SearchBase(static::DB_TBL);
        $srch->addOrder(static::tblFld('id'));
        if ($active === true) {
            $srch->addCondition(static::DB_TBL_PREFIX.'active', '=', applicationConstants::ACTIVE);
        }

        if ($recordId > 0) {
            $srch->addCondition(static::tblFld('id'), '=', FatUtility::int($recordId));
        }

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        if ($assoc) {
            $srch->addMultipleFields(array(static::tblFld('id'), static::tblFld('name')));
            return FatApp::getDb()->fetchAllAssoc($srch->getResultSet());
        } else {
            return FatApp::getDb()->fetchAll($srch->getResultSet(), static::tblFld('id'));
        }
    }

    public static function getAllCodesAssoc($withDefaultValue = false, $recordId = 0, $active = true, $deleted = false)
    {
        $srch = new SearchBase(static::DB_TBL);
        $srch->addOrder(static::tblFld('id'));
        if ($active === true) {
            $srch->addCondition('language_active', '=', applicationConstants::ACTIVE);
        }

        if ($recordId > 0) {
            $srch->addCondition(static::tblFld('id'), '=', FatUtility::int($recordId));
        }

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array(static::tblFld('id'), static::tblFld('code')));
        $row = FatApp::getDb()->fetchAllAssoc($srch->getResultSet());
        if ($withDefaultValue) {
            return array(0=>'Universal')+$row;
        }
        return $row;
    }

    public static function getLayoutDirection($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }

        $langData = self::getAttributesById($langId, array('language_layout_direction'));
        if (false != $langData) {
            return $langData['language_layout_direction'];
        }
    }
}
