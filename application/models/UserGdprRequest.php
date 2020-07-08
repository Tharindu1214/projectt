<?php
class UserGdprRequest extends MyAppModel
{
    const DB_TBL = 'tbl_user_requests_history';
    const DB_TBL_PREFIX = 'ureq_';

    const TYPE_TRUNCATE = 1;
    const TYPE_DATA_REQUEST = 2;

    const STATUS_PENDING = 0;
    const STATUS_COMPLETE = 1;


    public function __construct($userReqId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $userReqId);
        $this->objMainTableRecord->setSensitiveFields(
            array(
            'ureq_date'
            )
        );
    }

    public function save()
    {
        if (0 == $this->mainTableRecordId) {
            $this->setFldValue('ureq_date', date('Y-m-d H:i:s'));
        }
        return parent::save();
    }

    public static function getUserRequestTypesArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::TYPE_TRUNCATE    =>    Labels::getLabel('LBL_Truncate_Data', $langId),
        static::TYPE_DATA_REQUEST    =>    Labels::getLabel('LBL_Data_Request', $langId)
        );
    }

    public static function getUserRequestStatusesArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
        static::STATUS_PENDING    =>    Labels::getLabel('LBL_Pending', $langId),
        static::STATUS_COMPLETE =>    Labels::getLabel('LBL_Complete', $langId)
        );
    }

    public function updateRequestStatus($status)
    {
        if ($this->mainTableRecordId < 1) {
            $this->error = Labels::getLabel('ERR_REQUEST_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }
        $status = FatUtility::int($status);

        $assignValues = array(
        'ureq_status'=>$status,
        'ureq_approved_date'=>date('Y-m-d H:i:s'),
        );
        if (!FatApp::getDb()->updateFromArray(static::DB_TBL, $assignValues, array('smt' => static::DB_TBL_PREFIX . 'id = ? ', 'vals' => array($this->mainTableRecordId)))) {
            $this->error = FatApp::getDb()->getError();
            echo $this->error;
            die;
        }
        return true;
    }

    public function deleteRequest()
    {
        if ($this->mainTableRecordId < 1) {
            $this->error = Labels::getLabel('ERR_REQUEST_NOT_INITIALIZED', $this->commonLangId);
            return false;
        }

        $assignValues = array(
        'ureq_deleted'=>applicationConstants::YES,
        );
        if (!FatApp::getDb()->updateFromArray(static::DB_TBL, $assignValues, array('smt' => static::DB_TBL_PREFIX . 'id = ? ', 'vals' => array($this->mainTableRecordId)))) {
            $this->error = FatApp::getDb()->getError();
            echo $this->error;
            die;
        }
        return true;
    }
}
