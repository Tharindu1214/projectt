<?php
class CollectionSearch extends SearchBase
{
    private $langId;

    private $joinCollectionProducts = false;
    private $joinCollectionCategories = false;
    private $joinCollectionShops = false;
    private $joinCollectionBrands = false;
    private $commonLangId;
    public function __construct($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $this->langId = $langId;

        parent::__construct(Collections::DB_TBL, 'c');
        /* $this->commonLangId = CommonHelper::getLangId();
        $this->langId = FatUtility::int( $langId ); */

        if ($this->langId > 0) {
            $this->joinTable(
                Collections::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'c_l.'.Collections::DB_TBL_LANG_PREFIX.'collection_id = c.'.Collections::tblFld('id').' and
			c_l.'.Collections::DB_TBL_LANG_PREFIX.'lang_id = '.$this->langId,
                'c_l'
            );
        }

        $this->addCondition(Collections::tblFld('active'), '=', applicationConstants::ACTIVE);
        $this->addCondition(Collections::tblFld('deleted'), '=', 0);
    }

    public function joinCollectionProducts()
    {
        $this->joinCollectionProducts = true;

        $this->joinTable(
            Collections::DB_TBL_COLLECTION_TO_SELPROD,
            'LEFT OUTER JOIN',
            'ctsp.'.Collections::DB_TBL_COLLECTION_TO_SELPROD_PREFIX.'collection_id = c.'.Collections::tblFld('id'),
            'ctsp'
        );
    }

    public function joinCollectionCategories()
    {
        $this->joinCollectionCategories = true;
        $this->joinTable(
            Collections::DB_TBL_COLLECTION_TO_PRODUCT_CATEGORIES,
            'LEFT OUTER JOIN',
            'ctpc.'.Collections::DB_TBL_COLLECTION_TO_PRODUCT_CATEGORIES_PREFIX.'collection_id = c.'.Collections::tblFld('id'),
            'ctpc'
        );
    }

    public function joinCollectionShops()
    {
        $this->joinCollectionShops = true;
        $this->joinTable(
            Collections::DB_TBL_COLLECTION_TO_SHOPS,
            'LEFT OUTER JOIN',
            'ctps.'.Collections::DB_TBL_COLLECTION_TO_SHOPS_PREFIX.'collection_id = c.'.Collections::tblFld('id'),
            'ctps'
        );
    }

    public function joinCollectionBrands()
    {
        $this->joinCollectionBrands = true;
        $this->joinTable(Collections::DB_TBL_COLLECTION_TO_BRANDS, 'LEFT OUTER JOIN', 'ctpb.' . Collections::DB_TBL_COLLECTION_TO_BRANDS_PREFIX . 'collection_id = c.' . Collections::tblFld('id'), 'ctpb');
    }

    public function joinSellerProductsForPrice($langId = 0, $forDate = '')
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        if (!$this->joinCollectionProducts) {
            trigger_error(Labels::getLabel('ERR_joinCollectionProducts_must_be_joined.', $this->commonLangId), E_USER_ERROR);
        }

        $now = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');
        if ('' == $forDate) {
            $forDate = $now;
        }

        $this->joinTable(SellerProduct::DB_TBL, 'LEFT OUTER JOIN', SellerProduct::DB_TBL_PREFIX.'id = '.Collections::DB_TBL_COLLECTION_TO_SELPROD_PREFIX.'selprod_id', 'sprods');

        if ($langId > 0) {
            $this->joinTable(SellerProduct::DB_TBL.'_lang', 'LEFT OUTER JOIN', 'sprods_l.selprodlang_selprod_id = ' . SellerProduct::DB_TBL_PREFIX.'id AND sprods_l.selprodlang_lang_id = '.$langId, 'sprods_l');
        }

        $this->joinTable(
            SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE,
            'LEFT OUTER JOIN',
            'splprice_selprod_id = selprod_id AND \'' . $forDate . '\' BETWEEN splprice_start_date AND splprice_end_date'
        );
    }

    public function joinProducts($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(Product::DB_TBL, 'LEFT OUTER JOIN', Product::DB_TBL_PREFIX.'id = '.SellerProduct::DB_TBL_PREFIX.'product_id', 'p');

        if ($langId > 0) {
            $this->joinTable(Product::DB_TBL.'_lang', 'LEFT OUTER JOIN', 'p_l.productlang_product_id = ' . Product::tblFld('id').' AND p_l.productlang_lang_id = '.$langId, 'p_l');
        }
    }
}
