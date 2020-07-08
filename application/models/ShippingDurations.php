<?php
class ShippingDurations extends MyAppModel
{
    const DB_TBL = 'tbl_shipping_durations';
    const DB_TBL_PREFIX = 'sduration_';

    const DB_TBL_LANG = 'tbl_shipping_durations_lang';
    const DB_TBL_PREFIX_LANG = 'sdurationlang_';

    const SHIPPING_DURATION_DAYS = 1;
    const SHIPPING_DURATION_WEEK = 2;

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db=FatApp::getDb();
    }

    public static function getSearchObject($langId = 0, $isDeleted = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'sd');
        if ($isDeleted == true) {
            $srch->addCondition('sd.'.static::DB_TBL_PREFIX.'deleted', '=', applicationConstants::NO);
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'sd_l.sdurationlang_sduration_id = sd.sduration_id AND sd_l.sdurationlang_lang_id = ' . $langId,
                'sd_l'
            );
        }
        return $srch;
    }

    public static function getShippingDurationDaysOrWeekArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }
        $arr=array(
        static::SHIPPING_DURATION_DAYS => Labels::getLabel('LBL_Business_Days', $langId),
        static::SHIPPING_DURATION_WEEK => Labels::getLabel('LBL_Weeks', $langId),
        );
        return $arr;
    }

    public static function getListingObj($langId, $attr = null)
    {
        $srch = self::getSearchObject($langId);

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $srch->addMultipleFields(
            array(
            'IFNULL(sd_l.sduration_name,sd.sduration_identifier) as sduration_name'
            )
        );

        return $srch;
    }

    public function getShippingDurationAssoc($langId)
    {
        $srch = $this->getListingObj($langId, array('sduration_id','sduration_name'));
        $rs = $srch->getResultSet();
        $durationArr = FatApp::getDb()->fetchAllAssoc($rs);
        return $durationArr;
    }

    public function canRecordMarkDelete($id)
    {
        $srch = self::getSearchObject();
        $srch->addCondition('sd.'.static::DB_TBL_PREFIX.'id', '=', $id);
        $srch->addFld('sd.'.static::DB_TBL_PREFIX.'id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row[static::DB_TBL_PREFIX.'id']==$id) {
            return true;
        }
        return false;
    }

    public static function getShippingDurationTitle($sdurationRow, $siteLangId)
    {
        if (empty($sdurationRow) || !array_key_exists('sduration_days_or_weeks', $sdurationRow)) {
            return '';
        }
        $siteLangId = FatUtility::int($siteLangId);
        if (!$siteLangId) {
            trigger_error(Labels::getLabel("ERR_Language_Id_Not_Passed.", $siteLangId), E_USER_ERROR);
        }
        $day_or_week = '';
        $day_or_week = static::getShippingDurationDaysOrWeekArr($siteLangId)[$sdurationRow['sduration_days_or_weeks']];
        $str = Labels::getLabel('LBL_Shipping_Duration_Range_Label', $siteLangId);

        $replacementArr = array(
        '{from}'    =>    $sdurationRow['sduration_from'],
        '{to}'        =>    $sdurationRow['sduration_to'],
        '{day_or_week}' => $day_or_week
        );
        foreach ($replacementArr as $key => $val) {
            $str = str_replace($key, $val, $str);
        }
        return $str;
    }
}
