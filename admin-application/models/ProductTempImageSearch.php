<?php
class ProductTempImageSearch extends SearchBase
{
    private $langId;
    public function __construct($langId = 0) 
    {

        $this->langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        if(1 < $langId) {
            $this->langId = $langId;
        }

        parent::__construct(ProductTempImage::DB_TBL, 'af');
    }

    public function joinProduct($langId = 0)
    {
        if(1 < $langId) {
            $this->langId = $langId;
        }

        $this->joinTable(
            Product::DB_TBL, 'LEFT JOIN',
            'afile_record_id = tp.product_id', 'tp'
        );

        $this->joinTable(
            Product::DB_LANG_TBL, 'LEFT JOIN',
            'afile_record_id = productlang_product_id
		AND productlang_lang_id = ' . $this->langId, 'tp_l'
        );

        return $this;

    }
}
