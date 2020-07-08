<?php
class UserSearch extends SearchBase
{
    
    public function __construct() 
    {
        parent::__construct('tbl_users', 'u');
        
        $this->joinTable('tbl_user_credentials', 'LEFT OUTER JOIN', 'u.user_id = uc.credential_user_id', 'uc');
        
        $this->addOrder('credential_active', 'DESC');
        $this->addOrder('credential_verified');
        $this->addOrder('user_regdate', 'DESC');
        
        $this->addMultipleFields(
            array (
            'user_id',
            'user_name',
            'user_phone',
            'user_regdate',
            'credential_username',
            'credential_active',
            'credential_verified' 
            ) 
        );
    }
}