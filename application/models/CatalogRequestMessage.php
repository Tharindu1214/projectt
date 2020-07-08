<?php
class CatalogRequestMessage extends MyAppModel
{
    const DB_TBL = 'tbl_catalog_request_messages';
    const DB_TBL_PREFIX = 'scatrequestmsg_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }
}
