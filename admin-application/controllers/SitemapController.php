<?php
class SitemapController extends AdminBaseController
{

    public function generate()
    {

        $this->startSitemapXml();


        $prodSrchObj = new ProductSearch($this->adminLangId);
        $prodSrchObj->setDefinedCriteria(1);
        $prodSrchObj->joinProductToCategory();
        $prodSrchObj->joinSellerSubscription();
        $prodSrchObj->addSubscriptionValidCondition();
        $prodSrchObj->doNotCalculateRecords();
        $prodSrchObj->doNotLimitRecords();


        /* Category Pages [ */

        $catSrch = clone $prodSrchObj;
        $catSrch->addGroupBy('prodcat_id');
        $categoriesArr = productCategory::getProdCatParentChildWiseArr($this->adminLangId, 0, true, false, true, $catSrch);

        foreach ($categoriesArr as $key => $val) {
            $this->writeSitemapUrl(CommonHelper::generateFullUrl('category', 'view', array($val['prodcat_id']), CONF_WEBROOT_FRONT_URL), $freq = 'daily');
        }
        /* ]*/

        /* Product Pages [ */

        $prodSrch = clone $prodSrchObj;

        $prodSrch->addMultipleFields(array('selprod_id'));
        $prodSrch->addGroupBy('selprod_id');
        $prodSrch->doNotCalculateRecords();
        $prodSrch->doNotLimitRecords();
        $rs = $prodSrch->getResultSet();
        $productsList = FatApp::getDb()->fetchAll($rs);
        foreach ($productsList as $key => $val) {
            $this->writeSitemapUrl(CommonHelper::generateFullUrl('products', 'view', array($val['selprod_id']), CONF_WEBROOT_FRONT_URL), $freq = 'daily');
        }
        /* ]*/

        /* Brand Pages [ */
        $brandSrch = clone $prodSrchObj;
        $brandSrch->addMultipleFields(array('brand_id'));
        $brandSrch->addGroupBy('brand_id');
        $brandSrch->addOrder('brand_name');
        $brandSrch->doNotCalculateRecords();
        $brandSrch->doNotLimitRecords();
        $brandRs = $brandSrch->getResultSet();
        $brandsArr = FatApp::getDb()->fetchAll($brandRs);

        foreach ($brandsArr as $key => $val) {
            $this->writeSitemapUrl(CommonHelper::generateFullUrl('brands', 'view', array($val['brand_id']), CONF_WEBROOT_FRONT_URL), $freq = 'daily');
        }
        /* ]*/

        /* Shop Pages [ */

        $shopSrch = new ShopSearch($this->adminLangId);
        $shopSrch->setDefinedCriteria($this->adminLangId);
        $shopSrch->joinShopCountry();
        $shopSrch->joinShopState();
        $shopSrch->joinSellerSubscription();
        $shopSrch->doNotCalculateRecords();
        $shopSrch->doNotLimitRecords();
        $shopSrch->addMultipleFields(array('shop_id'));
        $rs = $shopSrch->getResultSet();
        $shopsList = FatApp::getDb()->fetchAll($rs);
        foreach ($shopsList as $key => $val) {
            $this->writeSitemapUrl(CommonHelper::generateFullUrl('shops', 'view', array($val['shop_id']), CONF_WEBROOT_FRONT_URL), $freq = 'daily');
        }
        /* ]*/

        /* CMS Pages [ */
        $cmsSrch = new NavigationLinkSearch($this->adminLangId);
        $cmsSrch->joinNavigation();
        $cmsSrch->joinProductCategory();
        $cmsSrch->joinContentPages();
        $cmsSrch->doNotCalculateRecords();
        $cmsSrch->doNotLimitRecords();
        $cmsSrch->addOrder('nav_id');
        $cmsSrch->addOrder('nlink_display_order');

        $cmsSrch->addCondition('nlink_deleted', '=', '0');
        $cmsSrch->addCondition('nav_active', '=', applicationConstants::ACTIVE);
        $cmsSrch->addMultipleFields(array('nlink_cpage_id, nlink_type'));
        $rs = $cmsSrch->getResultSet();
        $linksList = FatApp::getDb()->fetchAll($rs);
        foreach ($linksList as $key => $link) {
            if ($link['nlink_type'] == NavigationLinks::NAVLINK_TYPE_CMS && $link['nlink_cpage_id']) {
                $this->writeSitemapUrl(CommonHelper::generateFullUrl('Cms', 'view', array($link['nlink_cpage_id']), CONF_WEBROOT_FRONT_URL), $freq = 'monthly');
            }
        }
        /* ]*/

        $this->endSitemapXml();
        $this->writeSitemapIndex();
        Message::addMessage(Labels::getLabel('MSG_Sitemap_has_been_updated_successfully', $this->adminLangId));
        CommonHelper::redirectUserReferer();
    }

    private function startSitemapXml()
    {
        ob_start();
        echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    }

    private function writeSitemapUrl($url, $freq)
    {
        static $sitemap_i;
        $sitemap_i++;
        if ($sitemap_i > 2000) {
            $sitemap_i = 1;
            $this->endSitemapXml();
            $this->startSitemapXml();
        }
        echo "
			<url>
				<loc>".$url."</loc>
                <lastmod>".date('Y-m-d')."</lastmod>
                <changefreq>weekly</changefreq>
                <priority>0.8</priority>
			</url>";
        echo "\n";
    }

    private function endSitemapXml()
    {
        global $sitemapListInc;
        $sitemapListInc++;
        echo '</urlset>' . "\n";
        $contents = ob_get_clean();
        $rs = '';
        CommonHelper::writeFile('sitemap/list_'.$sitemapListInc.'.xml', $contents, $rs);
    }

    private function writeSitemapIndex()
    {
        global $sitemapListInc;
        ob_start();
        echo "<?xml version='1.0' encoding='UTF-8'?>
		<sitemapindex xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd' xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\n";
        for ($i=1; $i <= $sitemapListInc; $i++) {
            echo "<sitemap><loc>".CommonHelper::getUrlScheme()."/sitemap/list_".$i.".xml</loc></sitemap>\n";
        }
        echo "</sitemapindex>";
        $contents = ob_get_clean();
        $rs = '';
        CommonHelper::writeFile('sitemap.xml', $contents, $rs);
    }
}
