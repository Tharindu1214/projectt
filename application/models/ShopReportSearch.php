<?php
class ShopReportSearch extends SearchBase
{
    private $langId;

    public function __construct($langId = 0)
    {
        parent::__construct(ShopReport::DB_TBL, 'sreport');
        $this->langId = FatUtility::int($langId);
    }

    public function joinUser()
    {
        $this->joinTable(User::DB_TBL, 'INNER JOIN', 'sreport.sreport_user_id = u.user_id', 'u');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'credential_user_id = u.user_id', 'u_cred');
    }

    public function joinShops($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $this->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', 'sreport_shop_id = shop_id', 'shop');

        if ($this->langId) {
            $langId = $this->langId;
        }

        if ($langId) {
            $this->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop.shop_id = shop_l.shoplang_shop_id AND shoplang_lang_id = '.$langId, 'shop_l');
        }
    }
}
