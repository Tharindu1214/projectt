<?php
class BrandsController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        $brandSrch = Brand::getListingObj($this->siteLangId, array( 'brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name'), true);
        $brandSrch->doNotCalculateRecords();
        $brandSrch->doNotLimitRecords();
        $brandSrch->addOrder('brand_name', 'asc');
        $brandRs = $brandSrch->getResultSet();
        $brandsArr = FatApp::getDb()->fetchAll($brandRs);
        if (true ===  MOBILE_APP_API_CALL) {
            $db = FatApp::getDb();
            $totalProdCountToDisplay = 4;
            $productCustomSrchObj = new ProductSearch($this->siteLangId);
            $productCustomSrchObj->joinProductToCategory($this->siteLangId);
            $productCustomSrchObj->setDefinedCriteria();
            $productCustomSrchObj->joinSellerSubscription($this->siteLangId, true);
            $productCustomSrchObj->addSubscriptionValidCondition();

            if (UserAuthentication::isUserLogged()) {
                $productCustomSrchObj->joinFavouriteProducts(UserAuthentication::getLoggedUserId());
            }

            $productCustomSrchObj->joinProductRating();
            $productCustomSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
            $productCustomSrchObj->addGroupBy('selprod_id');

            $productCustomSrchObj->addMultipleFields(
                array('product_id', 'selprod_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
                'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type',
                'theprice', 'selprod_price','selprod_stock', 'selprod_condition','prodcat_id','IFNULL(prodcat_name, prodcat_identifier) as prodcat_name','ifnull(sq_sprating.prod_rating,0) prod_rating ','ifnull(sq_sprating.totReviews,0) totReviews','selprod_sold_count','selprod_min_order_qty')
            );
            if (UserAuthentication::isUserLogged()) {
                $productCustomSrchObj->addFld(array('IF(ufp_id > 0, 1, 0) as isfavorite','IFNULL(ufp_id, 0) as ufp_id'));
            } else {
                $productCustomSrchObj->addFld(array('0 as isfavorite','0 as ufp_id'));
            }

            $productCustomSrchObj->setPageSize($totalProdCountToDisplay);
            $cnt=0;
            foreach ($brandsArr as $val) {
                $prodSrch = clone $productCustomSrchObj;
                $prodSrch->addBrandCondition($val['brand_id']);
                $prodSrch->addGroupBy('selprod_id');
                $prodRs = $prodSrch->getResultSet();
                $brandsArr[$cnt] = $val;
                $brandProducts = $db->fetchAll($prodRs);

                foreach ($brandProducts as &$brandProduct) {
                    $mainImgUrl = FatCache::getCachedUrl(CommonHelper::generateFullUrl('image', 'product', array($brandProduct['product_id'], "MEDIUM", $brandProduct['selprod_id'], 0, $this->siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                    $brandProduct['discounted_text'] =  CommonHelper::showProductDiscountedText($brandProduct, $this->siteLangId);
                    $brandProduct['product_image'] =  $mainImgUrl;
                    $brandProduct['currency_selprod_price'] = CommonHelper::displayMoneyFormat($brandProduct['selprod_price'], true, false, false);
                    $brandProduct['currency_theprice'] = CommonHelper::displayMoneyFormat($brandProduct['theprice'], true, false, false);
                }
                $brandsArr[$cnt]['products'] = $brandProducts;
                $brandsArr[$cnt]['totalProducts'] = $prodSrch->recordCount();
                $cnt++;
            }
        }
        $this->set('layoutDirection', Language::getLayoutDirection($this->siteLangId));
        $this->set('allBrands', $brandsArr);
        $this->_template->render();
    }

    public function all()
    {
        FatApp::redirectUser(CommonHelper::generateUrl('Brands'));
    }

    public function view($brandId)
    {
        $brandId = FatUtility::int($brandId);
        Brand::recordBrandWeightage($brandId);

        $db = FatApp::getDb();

        $brandSrch = Brand::getListingObj($this->siteLangId, array( 'brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name'), true);
        $brandSrch->addCondition('brand_id', '=', $brandId);
        $brandSrch->addOrder('brand_name', 'asc');
        $brandRs = $brandSrch->getResultSet();
        $brand = FatApp::getDb()->fetch($brandRs);

        if (empty($brand)) {
            FatUtility::exitWithErrorCode(404);
        }

        $frm = $this->getProductSearchForm();

        $get = FatApp::getParameters();
        $get = Product::convertArrToSrchFiltersAssocArr($get);

        $get['join_price'] = 1;
        $get['brand_id'] = $brandId;
        $get['brand'] = array($brandId); /*For filters*/
        $frm->fill($get);


        $userId = 0;
        if (UserAuthentication::isUserLogged()) {
            $userId = UserAuthentication::getLoggedUserId();
        }

        $page = 1;
        if (array_key_exists('page', $get)) {
            $page = FatUtility::int($get['page']);
            if ($page < 2) {
                $page = 1;
            }
        }

        $pageSize = FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);
        if (array_key_exists('pageSize', $get)) {
            $pageSize = FatUtility::int($get['pageSize']);
            if (0 >= $pageSize) {
                $pageSize = FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);
            }
        }

        $srch = Product::getListingObj($get, $this->siteLangId, $userId);
        $srch->setPageNumber($page);
        if ($pageSize) {
            $srch->setPageSize($pageSize);
        }

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $products = $db->fetchAll($rs);

        $data = array(
            'frmProductSearch'=>$frm,
            'products'=>$products,
            'page'=>$page,
            'pageSize'=>$pageSize,
            'pageCount'=>$srch->pages(),
            'postedData'=>$get,
            'recordCount'=>$srch->recordCount(),
            'pageTitle'=>$brand['brand_name'],
            'canonicalUrl'=>CommonHelper::generateFullUrl('Brands', 'view', array($brandId)),
            'productSearchPageType'=>SavedSearchProduct::PAGE_BRAND,
            'recordId'=>$brandId,
            'bannerListigUrl'=>CommonHelper::generateFullUrl('Banner', 'brands'),
            'siteLangId'=>$this->siteLangId,
            'showBreadcrumb'=> true,
        );

        $this->set('data', $data);
        $this->includeProductPageJsCss();
        $this->_template->addJs(array('js/slick.min.js', 'js/responsive-img.min.js'));
        $this->_template->addCss(array('css/slick.css','css/product-detail.css'));
        $this->_template->render();
    }

    public function autoComplete()
    {
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE');
        $post = FatApp::getPostedData();

        $brandObj = new Brand();
        $srch = $brandObj->getSearchObject($this->siteLangId, true, true);

        $srch->addMultipleFields(array('brand_id, IFNULL(brand_name, brand_identifier) as brand_name'));

        if (!empty($post['keyword'])) {
            $srch->addCondition('brand_name', 'LIKE', '%' . $post['keyword'] . '%');
        }
        $srch->addCondition('brand_status', '=', Brand::BRAND_REQUEST_APPROVED);

        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $brands = $db->fetchAll($rs, 'brand_id');
        $json = array();
        foreach ($brands as $key => $brand) {
            $json[] = array(
            'id' => $key,
            'name'      => strip_tags(html_entity_decode($brand['brand_name'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
        /* $this->set('brands', $db->fetchAll($rs,'brand_id') );
        $this->_template->render(false,false); */
    }

    public function checkUniqueBrandName()
    {
        $post = FatApp::getPostedData();

        $langId = FatUtility::int($post['langId']);

        $brandName = $post['brandName'];
        $brandId =  FatUtility::int($post['brandId']);
        if (1>$langId) {
            trigger_error(Labels::getLabel('LBL_Lang_Id_not_Specified', CommonHelper::getLangId()));
        }
        if (1>$brandId) {
            trigger_error(Labels::getLabel('LBL_Brand_Id_not_Specified', CommonHelper::getLangId()));
        }
        $srch = Brand::getSearchObject($langId);
        $srch->addCondition('brand_name', '=', $brandName);
        if ($brandId) {
            $srch->addCondition('brand_id', '!=', $brandId);
        }
        $rs = $srch->getResultSet();
        $records = $srch->recordCount();
        if ($records>0) {
            FatUtility::dieJsonError(sprintf(Labels::getLabel('LBL_%s_not_available', $this->siteLangId), $brandName));
        }
        FatUtility::dieJsonSuccess(array());
    }

    public function getBreadcrumbNodes($action)
    {
        $nodes = array();
        $parameters = FatApp::getParameters();
        switch ($action) {
        case 'view':
            $nodes[] = array('title'=>Labels::getLabel('LBL_Brands', $this->siteLangId), 'href'=>CommonHelper::generateUrl('brands'));
            if (isset($parameters[0]) && $parameters[0] > 0) {
                $brandId = FatUtility::int($parameters[0]);
                if ($brandId>0) {
                    $brandSrch = Brand::getListingObj($this->siteLangId, array( 'IFNULL(brand_name, brand_identifier) as brand_name', ));
                    $brandSrch->doNotCalculateRecords();
                    $brandSrch->doNotLimitRecords();
                    $brandSrch->addCondition('brand_id', '=', $brandId);
                    $brandRs = $brandSrch->getResultSet();
                    $brandsArr = FatApp::getDb()->fetch($brandRs);
                    $nodes[] = array('title'=>$brandsArr['brand_name']);
                }
            }

            break;

        case 'index':
            $nodes[] = array('title'=>Labels::getLabel('LBL_Brands', $this->siteLangId));

            break;


        }
        return $nodes;
    }
}
