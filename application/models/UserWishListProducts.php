<?php
class UserWishListProducts extends MyAppModel
{
    const DB_TBL = 'tbl_user_wish_list_products';
    const DB_TBL_PREFIX = 'uwlp_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->objMainTableRecord->setSensitiveFields(array());
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'uwlp');
        return $srch;
    }
}
