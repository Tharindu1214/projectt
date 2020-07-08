<?php
class AttributeGroup extends MyAppModel
{
    const DB_TBL = 'tbl_attribute_groups';
    const DB_TBL_PREFIX = 'attrgrp_';

    const DB_TBL_ATTRIBUTES = 'tbl_attribute_group_attributes';
    const DB_TBL_ATTRUTE_PREFIX = 'attr_';

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db=FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'ag');
        $srch->addOrder('ag.'.static::DB_TBL_PREFIX.'name', 'DESC');
        return $srch;
    }
}
