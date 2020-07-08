<?php
class ProductGroupProductSearch extends SearchBase
{
    private $langId;
    private $prodgroup_id;
    private $sellerProductsJoined;
    private $productsJoined;
    private $commonLangId;
    public function __construct($langId = 0, $prodgroup_id = 0)
    {
        $this->commonLangId = CommonHelper::getLangId();
        parent::__construct(ProductGroup::DB_PRODUCT_TO_GROUP, 'ptg');
        $this->langId = FatUtility::int($langId);
        $this->prodgroup_id = FatUtility::int($prodgroup_id);

        if ($this->prodgroup_id > 0) {
            $this->addCondition(ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX.'prodgroup_id', '=', $prodgroup_id);
        }

        $this->addOrder('ptg.ptg_is_main_product', 'DESC');

        $this->sellerProductsJoined = false;
        $this->productsJoined = false;
        /* if ( $this->langId > 0 ) {
        $this->joinTable( ProductGroup::DB_TBL_LANG, 'LEFT OUTER JOIN',
        'prodgrouplang_prodgroup_id = pg.prodgroup_id
        AND prodgrouplang_lang_id = ' . $this->langId, 'pg_l');
        } */
    }

    public function joinSellerProducts($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'ptg.'.ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX . 'selprod_id = sp.selprod_id', 'sp');

        if ($langId) {
            $this->joinTable(SellerProduct::DB_LANG_TBL, 'LEFT OUTER JOIN', 'sp.selprod_id = sp_l.selprodlang_selprod_id AND sp_l.selprodlang_lang_id = '.$langId, 'sp_l');
        }
        $this->sellerProductsJoined = true;
    }

    public function joinProducts($langId = 0)
    {
        if (!$this->sellerProductsJoined) {
            trigger_error(Labels::getLabel('MSG_joinProducts_can_be_joined_only_if,_joinSellerProducts_is_joined.', $this->commonLangId), E_USER_ERROR);
        }

        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'sp.selprod_product_id = p.product_id', 'p');

        if ($langId) {
            $srch->joinTable(Product::DB_LANG_TBL, 'LEFT OUTER JOIN', 'p.product_id = pl.productlang_product_id AND productlang_lang_id = '.$langId, 'p_l');
        }

        $this->productsJoined = true;
    }

    public function joinProductGroup($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $srch->joinTable(ProductGroup::DB_TBL, 'INNER JOIN', 'ptg.'.ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX.'prodgroup_id = pg.prodgroup_id', 'pg');
    }

    public function joinShops()
    {
    }
}
