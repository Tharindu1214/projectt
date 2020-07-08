<?php
class CatalogRequestMessageSearch extends SearchBase
{
    private $langId;
    private $isCatalogRequestJoined;
    private $isReceiverUserJoined;
    public function __construct($langId = 0, $isDeleted = true)
    {
        $langId = FatUtility::int($langId);
        $this->langId = $langId;
        $this->isCatalogRequestJoined = false;
        parent::__construct(User::DB_TBL_USR_CATALOG_REQ_MSG, 'scatrequestmsg');

        if ($isDeleted == true) {
            $this->addCondition('scatrequestmsg_deleted', '=', applicationConstants::NO);
        }
    }

    public function joinMessageUser()
    {
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'scatrequestmsg.scatrequestmsg_from_user_id = msg_user.user_id', 'msg_user');
        $this->joinTable(User::DB_TBL_CRED, 'LEFt OUTER JOIN', 'msg_user.user_id = msg_user_cred.credential_user_id', 'msg_user_cred');
    }

    public function joinMessageAdmin()
    {
        $this->joinTable('tbl_admin', 'LEFT OUTER JOIN', 'scatrequestmsg.scatrequestmsg_from_admin_id = msg_admin.admin_id', 'msg_admin');
    }

    public function joinCatalogRequests()
    {
        $this->joinTable(User::DB_TBL_USR_CATALOG_REQ, 'LEFT OUTER JOIN', 'scatrequest_id = scatrequestmsg_scatrequest_id', 'scatrequest');
        $this->isCatalogRequestJoined = true;
    }

    public function joinReceiverUser()
    {
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'scatrequest.scatrequest_user_id = receiver_user.user_id', 'receiver_user');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'receiver_user.user_id = receiver_user_cred.credential_user_id', 'receiver_user_cred');
    }
}
