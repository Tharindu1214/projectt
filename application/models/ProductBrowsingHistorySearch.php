<?php
class ProductBrowsingHistorySearch extends SearchBase
{
    private $langId;

    public function __construct($langId = 0)
    {
        parent::__construct('tbl_products_browsing_history', 'pbh');
        $this->langId = FatUtility::int($langId);
    }

    /* public function joinProducts( $langId = 0 ){
    $langId = FatUtility::int( $langId );
    if($this->langId > 0){
    $langId = $this->langId;
    }
    } */
}
