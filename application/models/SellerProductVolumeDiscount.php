<?php
class SellerProductVolumeDiscount extends MyAppModel
{
    const DB_TBL = 'tbl_product_volume_discount';
    const DB_TBL_PREFIX = 'voldiscount_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function updateData($data, $return = false)
    {
        $db = FatApp::getDb();
        if (!$db->insertFromArray(static::DB_TBL, $data, false, array(), $data)) {
            return false;
        }
        if (true === $return) {
            if (!empty($data['voldiscount_id'])) {
                return $data['voldiscount_id'];
            }
            return $db->getInsertId();
        }
        return true;
    }
}
