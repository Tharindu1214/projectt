<?php
class Credential extends MyAppModel
{
    const DB_TBL = 'tbl_user_credentials';
    const DB_TBL_PREFIX = 'credential_';
    public function __construct($userId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $userId);
        $this->objMainTableRecord->setSensitiveFields(
            array(
            'user_regdate'
            )
        );
    }

    public function getUserCredential($fields = array())
    {
        if ($this->mainTableRecordId <= 0) {
            return false;
        }
        $search = new SearchBase(static::DB_TBL);
        $search->addCondition('credential_user_id', '=', $this->mainTableRecordId);
        if (!empty($fields) && is_array($fields)) {
            $search->addFld($fields);
        }

        $rs = $search->getResultSet();
        $db = FatApp::getDb();
        $row = $db->fetch($rs);
        return $row;
    }
}
