<?php
class SmartUserActivityBrowsing extends MyAppModel
{
    const DB_TBL = 'tbl_smart_user_activity_browsing';
    const DB_TBL_PREFIX = 'uab_';
    private $db;

    const TYPE_PRODUCT = 1;
    const TYPE_BRAND = 2;
    const TYPE_CATEGORY = 3;
    const TYPE_TAG = 4;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public function addUpdate($recordId, $recordType)
    {
        $recordId = FatUtility::int($recordId);
        $recordType = FatUtility::int($recordType);

        if (1 > $recordId || 1 >$recordType) {
            return false;
        }

        if (!UserAuthentication::isUserLogged()) {
            $userId = CommonHelper::getUserIdFromCookies();
        } else {
            $userId = UserAuthentication::getLoggedUserId();
        }

        $data = array(
        'uab_session_id' =>session_id(),
        'uab_user_id' =>$userId,
        'uab_record_id' =>$recordId,
        'uab_record_type' =>$recordType,
        'uab_last_action_datetime' =>date('Y-m-d H:i:s'),
        );
        $record = new TableRecord(static::DB_TBL);
        $record->assignValues($data);
        if (!$record->addNew(array('IGNORE'), array('uab_last_action_datetime'=>date('Y-m-d H:i:s')))) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }
}
