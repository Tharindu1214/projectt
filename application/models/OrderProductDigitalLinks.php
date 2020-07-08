<?php
class OrderProductDigitalLinks extends MyAppModel
{
    const DB_TBL = 'tbl_order_product_digital_download_links';
    const DB_TBL_PREFIX = 'opddl_';

    public function __construct($linkId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $linkId);
    }

    public static function updateDownloadCount($linkId = 0)
    {
        $linkId = FatUtility::int($linkId);
        $digitalFile = array('opddl_downloaded_times'=>'mysql_func_opddl_downloaded_times+1');
        $where = array('smt'=>'opddl_link_id = ?', 'vals'=>array($linkId));
        $db = FatApp::getDb();
        if (!$db->updateFromArray(static::DB_TBL, $digitalFile, $where, true)) {
            return false;
        }
        return true;
    }
}
