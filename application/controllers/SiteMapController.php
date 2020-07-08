<?php
class SiteMapController extends MyAppController
{
    public function index()
    {
        $brandSrch = Brand::getListingObj($this->siteLangId, array( 'brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name'), true);
        $brandSrch->doNotCalculateRecords();
        $brandSrch->doNotLimitRecords();
        $brandSrch->addOrder('brand_name', 'asc');
        $brandRs = $brandSrch->getResultSet();
        $brandsArr = FatApp::getDb()->fetchAll($brandRs);
        $categoriesArr = ProductCategory::getProdCatParentChildWiseArr($this->siteLangId, '', true, false, true);
        $contentPages = ContentPage:: getPagesForSelectBox($this->siteLangId);
        $this->set('contentPages', $contentPages);
        $this->set('categoriesArr', $categoriesArr);
        $this->set('allBrands', $brandsArr);
        $this->_template->render();
    }
}
