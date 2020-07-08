<?php
class ProductGroupSearch extends SearchBase
{
    private $langId;

    public function __construct($langId = 0)
    {
        parent::__construct(ProductGroup::DB_TBL, 'pg');
        $this->langId = FatUtility::int($langId);

        if ($this->langId > 0) {
            $this->joinTable(
                ProductGroup::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'prodgrouplang_prodgroup_id = pg.prodgroup_id AND prodgrouplang_lang_id = ' . $this->langId,
                'pg_l'
            );
        }
    }
}
