<?php
class NavigationLinkSearch extends SearchBase
{
    private $langId;
    /* private $isJoinedOrderProducts;
    private $isOrdersJoined; */
    public function __construct($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $this->langId = $langId;
        /* $this->isJoinedOrderProducts = false;
        $this->isOrdersJoined = false; */
        parent::__construct(NavigationLinks::DB_TBL, 'link');

        if ($this->langId > 0) {
            $this->joinTable(
                NavigationLinks::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'link.nlink_id = link_l.nlinklang_nlink_id AND nlinklang_lang_id = ' . $this->langId,
                'link_l'
            );
        }
    }

    public function joinNavigation($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(Navigations::DB_TBL, 'LEFT OUTER JOIN', 'nav.nav_id = link.nlink_nav_id', 'nav');
        if ($langId) {
            $this->joinTable(Navigations::DB_TBL_LANG, 'LEFT OUTER JOIN', 'nav.nav_id = nav_l.navlang_nav_id AND navlang_lang_id = '.$langId, 'nav_l');
        }
    }

    public function joinProductCategory($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(ProductCategory::DB_TBL, 'LEFT OUTER JOIN', 'pc.prodcat_id = link.nlink_category_id', 'pc');
    }

    public function joinContentPages($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        $this->joinTable(ContentPage::DB_TBL, 'LEFT OUTER JOIN', 'cp.cpage_id = link.nlink_cpage_id', 'cp');
    }
}
