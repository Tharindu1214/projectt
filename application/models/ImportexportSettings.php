<?php
class ImportexportSettings extends MyAppModel
{
    const DB_TBL = 'tbl_import_export_settings';
    const DB_TBL_PREFIX = 'setting_';
    protected $db;

    public function __construct($settingId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $settingId);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'ts');
        return $srch;
    }

    public static function getSetting($code, $shopId = 0)
    {
        $shopId = FatUtility::int($shopId);

        $srch = static::getSearchObject();
        $srch->addCondition('setting_code', '=', $code);
        $srch->addCondition('setting_shop_id', '=', $shopId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch ->getResultSet();

        $settingData = array();
        while ($row = FatApp::getDb()->fetch($rs)) {
            if (!$row['setting_serialized']) {
                $settingData[$row['setting_key']] = $row['setting_value'];
            } else {
                $settingData[$row['setting_key']] = json_decode($row['setting_value'], true);
            }
        }
        $settingData['CONF_USE_USER_ID'] = false;
        return $settingData;
    }

    public function updateSettings($code, $data, $shopId = 0)
    {
        $shopId = FatUtility::int($shopId);
        $this->deleteSetting($code, $shopId);
        $record = new TableRecord(static::DB_TBL);
        foreach ($data as $key => $value) {
            if (!substr($key, 0, strlen($code)) == $code) {
                continue;
            }

            $assignFields = array();
            $assignFields['setting_shop_id'] = $shopId;
            $assignFields['setting_code'] = $code;
            $assignFields['setting_key'] = $key;
            if (!is_array($value)) {
                $assignFields['setting_value'] = $value;
            } else {
                $assignFields['setting_value'] = json_encode($value);
            }
            $record->assignValues($assignFields);
            $record->addNew();
        }
    }

    public function deleteSetting($code, $shopId)
    {
        FatApp::getDb()->deleteRecords(static::DB_TBL, array( 'smt'=>'setting_code = ? and setting_shop_id = ?', 'vals'=>array( $code , $shopId ) ));
    }
}
