<?php
class BlogContribution extends MyAppModel
{
    const DB_TBL = 'tbl_blog_contributions';
    const DB_TBL_PREFIX = 'bcontributions_';

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL);
        return $srch;
    }
}
