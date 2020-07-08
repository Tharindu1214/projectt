<?php
class LayoutTemplate extends MyAppModel
{
    const DB_TBL = 'tbl_layout_templates';
    const DB_TBL_PREFIX = 'ltemplate_';

    const LAYOUTTYPE_SHOP = 1;

    const LAYOUTTYPE_SHOP_IMAGE_PATH = 'template-layouts/';

    public function __construct($userId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $userId);
    }

    public static function getSearchObject($isActive = true, $isDeleted = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'tlayout');

        if ($isActive = true) {
            $srch->addCondition('tlayout.ltemplate_active', '=', applicationConstants::ACTIVE);
        }

        if ($isDeleted = true) {
            $srch->addCondition('tlayout.ltemplate_deleted', '=', applicationConstants::NO);
        }

        return $srch;
    }

    public static function getMultipleLayouts($layoutType, $ltemplateId = 0)
    {
        $ltemplateId = FatUtility::int($ltemplateId);
        $srch = self::getSearchObject();

        $srch->addCondition('ltemplate_type', '=', $layoutType);

        if ($ltemplateId) {
            $srch->addCondition('ltemplate_id', '=', $ltemplateId);
        }

        $srch->addOrder('ltemplate_id');
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs, 'ltemplate_id');
    }

    public static function getLayout($layoutType, $ltemplateId)
    {
        $data = static::getMultipleLayouts($layoutType, $ltemplateId);
        if (count($data > 0)) {
            reset($data);
            return current($data);
        }
        return null;
    }
}
