<?php
class SellerPackageSearch extends SearchBase
{
    private $langId;


    public function __construct($langId)
    {
        parent::__construct(SellerPackages::DB_TBL, 'sp');
        if ($langId) {
            $this->joinTable(
                SellerPackages::DB_TBL . '_lang',
                'LEFT OUTER JOIN',
                'spl.spackagelang_spackage_id = sp.spackage_id AND spl.spackagelang_lang_id = ' . $langId,
                'spl'
            );
        }
    }

    public function joinPlan()
    {
        $this->joinTable(
            SellerPackagePlans::DB_TBL,
            'LEFT OUTER JOIN',
            'sp.spackage_id = spp.spplan_spackage_id',
            'spp'
        );
    }
}
