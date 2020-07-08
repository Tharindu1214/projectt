<?php
class RecommendationActivityBrowsing extends MyAppModel
{
    const DB_TBL = 'tbl_recommendation_activity_browsing';
    const DB_TBL_PREFIX = 'rab_';
    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'key', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'rab');
        return $srch;
    }
}
