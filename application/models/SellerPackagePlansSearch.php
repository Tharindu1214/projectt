<?php
class SellerPackagePlansSearch extends SearchBase
{
    private $langId;


    public function __construct()
    {
        parent::__construct(SellerPackagePlans::DB_TBL, 'spp');
    }
    public function joinPackage($langId = 0)
    {
        $this->joinTable(SellerPackages::DB_TBL, 'LEFT OUTER JOIN', 'spp.'.SellerPackagePlans::DB_TBL_PREFIX.'spackage_id=sp.'.SellerPackages::DB_TBL_PREFIX.'id', 'sp');
        if ($langId) {
            $this->joinTable(
                SellerPackages::DB_TBL . '_lang',
                'LEFT OUTER JOIN',
                'spl.spackagelang_spackage_id = sp.spackage_id AND spl.spackagelang_lang_id = ' . $langId,
                'spl'
            );
        }
    }
}
