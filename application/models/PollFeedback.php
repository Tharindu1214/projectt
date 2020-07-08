<?php
class PollFeedback extends MyAppModel
{
    const DB_TBL = 'tbl_polling_feedback';
    const DB_TBL_PREFIX = 'pollfeedback_';

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'pollfeedback');
        return $srch;
    }

    public function isPollAnsweredFromIP($pollId, $ip)
    {
        $srch = self::getSearchObject();
        $srch->addCondition('pollfeedback.pollfeedback_polling_id', '=', $pollId);
        $srch->addCondition('pollfeedback.pollfeedback_response_ip', '=', $ip);
        $srch->getResultset();
        return $srch->recordCount();
    }
}
