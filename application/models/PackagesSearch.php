<?php
class PackagesSearch extends SearchBase
{
    private $langId;


    public function __construct($langId = 0)
    {
        parent::__construct(SellerPackages::DB_TBL, 'sp');

        $this->langId = FatUtility::int($langId);

        if ($langId) {
            $this->joinTable(
                SellerPackages::DB_TBL . '_lang',
                'LEFT OUTER JOIN',
                'spl.spackagelang_spackage_id = sp.spackage_id AND spl.spackagelang_lang_id = ' . $langId,
                'spl'
            );
        }
    }
    public function joinPlans()
    {
        $this->joinTable(SellerPackagePlans::DB_TBL, 'LEFT OUTER JOIN', 'spp.'.SellerPackagePlans::DB_TBL_PREFIX.'spackage_id=sp.'.SellerPackages::DB_TBL_PREFIX.'id', 'spp');
    }
}
