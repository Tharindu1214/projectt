<?php
class Cron extends MyAppModel
{
    const DB_TBL = 'tbl_cron_schedules';
    const DB_TBL_PREFIX = 'cron_';

    const DB_TBL_LOG = 'tbl_cron_log';

    public function __construct($cronId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $cronId);
        /* CommonHelper::initCommonVariables(); */
    }

    public static function clearOldLog()
    {
        FatApp::getDb()->deleteRecords(
            static::DB_TBL_LOG,
            array(
            'smt' => 'cronlog_started_at < ?',
            'vals' => array(
            date('Y-m-d', strtotime("-3 Day"))
            )
            )
        );
    }

    public static function getAllRecords($activeOnly = true, $id = 0)
    {
        $srch = new SearchBase(static::DB_TBL);
        if ($activeOnly) {
            $srch->addCondition('cron_active', '=', applicationConstants::ACTIVE);
        }

        if ($id > 0) {
            $srch->addCondition('cron_id', '=', FatUtility::int($id));
        }

        return FatApp::getDb()->fetchAll($srch->getResultSet(), 'cron_id');
    }

    public function markStarted()
    {
        if (!$this->canStart()) {
            return false;
        }

        FatApp::getDb()->insertFromArray(
            static::DB_TBL_LOG,
            array(
            'cronlog_cron_id'=>$this->mainTableRecordId,
            'cronlog_started_at'=>date('Y-m-d H:i:s'),
            )
        );

        return FatApp::getDb()->getInsertId();
    }

    public function markFinished($logId, $message)
    {
        $db = FatApp::getDb();

        $db->updateFromArray(
            static::DB_TBL_LOG,
            array(
            'cronlog_ended_at' => date('Y-m-d H:i:s'),
            'cronlog_details' => "mysql_func_CONCAT(cronlog_details, '\n ', " . $db->quoteVariable($message) . ")"
            ),
            array(
            'smt' => 'cronlog_id = ?',
            'vals' => array(
                        $logId
            )
            ),
            true
        );
    }

    private function canStart()
    {
        $db = FatApp::getDb();
        $rs = $db->query(
            'SELECT * FROM ' . static::DB_TBL_LOG . ' WHERE cronlog_cron_id = ' . $this->mainTableRecordId . '
				ORDER BY cronlog_started_at DESC LIMIT 0, 1'
        );

        if (!$row = $db->fetch($rs)) {
            return true;
        }

        $diff = (time() - strtotime($row['cronlog_started_at'])) / 60;

        if ($diff < $this->getFldValue('cron_duration') || $diff < 1) {
            return false;
        }

        if ($row['cronlog_ended_at'] < '1972-01-01') {
            if ($diff > $this->getFldValue('cron_duration') * 3 && $diff > 2) {
                $this->markFinished($row['cronlog_id'], 'Marked Ended by cronjob manager at ' . date('Y-m-d H:i:s'));
            }
            return false;
        }

        return true;
    }
}
