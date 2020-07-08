<?php
class CollectionsController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function view($collection_id)
    {
        $searchForm = $this->getCollectionSearchForm($collection_id);
        $collection = Collections::getAttributesById($collection_id);

        /* Collection Data[ */

        $collectionSrch = Collections::getSearchObject(true, $this->siteLangId);
        $collectionSrch->addMultipleFields(
            array(
            'collection_id', 'IFNULL(collection_name, collection_identifier) as collection_name'
            )
        );
        $collectionSrch->doNotCalculateRecords();
        $collectionSrch->doNotLimitRecords();
        $collectionSrch->addCondition('collection_id', '=', $collection_id);
        $collectionSrchRs = $collectionSrch->getResultSet();
        $collectionArr = FatApp::getDb()->fetch($collectionSrchRs);
        $this->set('collection', $collectionArr);

        $this->set('searchForm', $searchForm);
        $this->_template->addJs('js/slick.min.js');
        $this->_template->addCss(array('css/slick.css','css/product-detail.css'));
        $this->_template->render();
    }

    private function getCollectionSearchForm($collection_id)
    {
        $frm = new Form('frmSearchCollections');
        $frm->addHiddenField('', 'collection_id', $collection_id);
        return $frm;
    }

    public function search()
    {
        $db = FatApp::getDb();
        $collection_id = FatApp::getPostedData('collection_id', FatUtility::VAR_INT, 0);

        if ($collection_id < 1) {
            $message = Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId);
            if (true ===  MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }
        $collection = Collections::getAttributesById($collection_id);
        $db = FatApp::getDb();
        $collectionObj = new CollectionSearch();
        $collectionObj->doNotCalculateRecords();
        $collectionObj->doNotLimitRecords();

        $shopSearchObj = new ShopSearch($this->siteLangId);
        $shopSearchObj ->setDefinedCriteria($this->siteLangId);
        $shopSearchObj->joinShopCountry();
        $shopSearchObj->joinShopState();
        $brandSearchObj = Brand::getSearchObject($this->siteLangId, true, true);
        /* sub query to find out that logged user have marked shops as favorite or not[ */
        $favSrchObj = new UserFavoriteShopSearch();
        $favSrchObj->doNotCalculateRecords();
        $favSrchObj->doNotLimitRecords();
        $favSrchObj->addMultipleFields(array('ufs_shop_id','ufs_id'));
        $favSrchObj->addCondition('ufs_user_id', '=', $loggedUserId);
        $shopSearchObj->joinTable('('. $favSrchObj->getQuery() . ')', 'LEFT OUTER JOIN', 'ufs_shop_id = shop_id', 'ufs');
        /* ] */

        $productSrchObj = new ProductSearch($this->siteLangId);
        $productSrchObj->setDefinedCriteria();
        $productSrchObj->joinProductToCategory($this->siteLangId);


        $productSrchObj->doNotCalculateRecords();
        // $productSrchObj->setPageSize(10);

        /* $productSrchObj->joinFavouriteProducts($loggedUserId ); */
        if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
            $productSrchObj->joinFavouriteProducts($loggedUserId);
            $productSrchObj->addFld('ufp_id');
        } else {
            $productSrchObj->joinUserWishListProducts($loggedUserId);
            $productSrchObj->addFld('IFNULL(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
        }

        // $productSrchObj->joinProductRating();
        $productSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $productSrchObj->addMultipleFields(
            array('product_id', 'selprod_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
            'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type',
            'theprice', 'selprod_price','selprod_stock', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'selprod_condition','prodcat_id','IFNULL(prodcat_name, prodcat_identifier) as prodcat_name','selprod_sold_count', 'product_image_updated_on')
        );


        $productCatSrchObj = new ProductCategorySearch($this->siteLangId);
        $productCatSrchObj->doNotCalculateRecords();
        $productCatSrchObj->doNotLimitRecords();
        $productCatSrchObj->addMultipleFields(array( 'prodcat_id', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name','prodcat_content_block'));

        switch ($collection['collection_type']) {
            case Collections::COLLECTION_TYPE_PRODUCT:
                $tempObj = clone $collectionObj;
                $tempObj->joinCollectionProducts();
                $tempObj->addCondition('collection_id', '=', $collection_id);
                // $tempObj->setPageSize( $collection['collection_primary_records']);
                $tempObj->addMultipleFields(array( 'ctsp_selprod_id' ));
                $tempObj->addCondition('ctsp_selprod_id', '!=', 'NULL');
                $rs = $tempObj->getResultSet();

                if (!$productIds = $db->fetchAll($rs, 'ctsp_selprod_id')) {
                    break;
                }

                /* fetch Products data[ */
                $orderBy = 'ASC';
                if ($collection['collection_criteria'] == Collections::COLLECTION_CRITERIA_PRICE_LOW_TO_HIGH) {
                    $orderBy = 'ASC';
                }
                if ($collection['collection_criteria'] == Collections::COLLECTION_CRITERIA_PRICE_HIGH_TO_LOW) {
                    $orderBy = 'DESC';
                }
                $productSrchTempObj = clone $productSrchObj;
                $productSrchTempObj->addCondition('selprod_id', 'IN', array_keys($productIds));
                $productSrchTempObj->addOrder('in_stock', 'DESC');
                $productSrchTempObj->addOrder('theprice', $orderBy);
                $productSrchTempObj->joinSellers();
                $productSrchTempObj->joinSellerSubscription($this->siteLangId);
                $productSrchTempObj->addSubscriptionValidCondition();
                $productSrchTempObj->addGroupBy('selprod_id');

                $rs = $productSrchTempObj->getResultSet();
                $collections[$collection['collection_layout_type']][$collection['collection_id']] = $collection;

                $collections = $db->fetchAll($rs);
                /* ] */
                if (true ===  MOBILE_APP_API_CALL) {
                    foreach ($collections as &$product) {
                        $product['product_image_url'] = CommonHelper::generateFullUrl('image', 'product', array($product['product_id'], "CLAYOUT3", $product['selprod_id'], 0, $this->siteLangId));
                    }
                }

                $this->set('pageCount', $productSrchTempObj->pages());
                $this->set('recordCount', $productSrchTempObj->recordCount());
                unset($tempObj);
                unset($productSrchTempObj);
                $this->set('collections', $collections);
                break;

            case Collections::COLLECTION_TYPE_CATEGORY:
                $tempObj = clone $collectionObj;
                $tempObj->addCondition('collection_id', '=', $collection_id);
                $tempObj->joinCollectionCategories($this->siteLangId);
                $tempObj->addMultipleFields(array( 'ctpc_prodcat_id'));
                $tempObj->addCondition('ctpc_prodcat_id', '!=', 'NULL');
                $tempObj->setPageSize($collection['collection_primary_records']);
                $rs = $tempObj->getResultSet();

                if (!$categoryIds = $db->fetchAll($rs, 'ctpc_prodcat_id')) {
                    break;
                }

                /* fetch Categories data[ */
                $productCatSrchTempObj = clone $productCatSrchObj;
                $productCatSrchTempObj->addCondition('prodcat_id', 'IN', array_keys($categoryIds));

                if (true ===  MOBILE_APP_API_CALL) {
                    $productCatSrchTempObj->addProductsCountField();
                }

                $rs = $productCatSrchTempObj->getResultSet();
                $collections =  $db->fetchAll($rs);
                /* ] */

                if ($collections) {
                    foreach ($collections as &$cat) {
                        if (true ===  MOBILE_APP_API_CALL) {
                            $imgUpdatedOn = ProductCategory::getAttributesById($cat['prodcat_id'], 'prodcat_img_updated_on');
                            $uploadedTime = AttachedFile::setTimeParam($imgUpdatedOn);
                            $cat['image'] = FatCache::getCachedUrl(CommonHelper::generateFullUrl('Category', 'banner', array($cat['prodcat_id'] , $this->siteLangId, 'MOBILE', applicationConstants::SCREEN_MOBILE)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                        } else {
                            $cat['children'] = ProductCategory::getProdCatParentChildWiseArr($this->siteLangId, $cat['prodcat_id']);
                        }
                    }
                }

                /* commonHelper::printArray($collections); die; */
                // $collections[$collection['collection_layout_type']][$collection['collection_id']] = $collection;
                /* $collections[$collection['collection_layout_type']][$collection['collection_id']]['categories'] = $db->fetchAll($rs); */

                unset($tempObj);
                $this->set('collections', $collections);
                break;
            case Collections::COLLECTION_TYPE_SHOP:
                $tempObj = clone $collectionObj;
                $tempObj->addCondition('collection_id', '=', $collection_id);
                $tempObj->joinCollectionShops();

                $tempObj->addMultipleFields(array( 'ctps_shop_id' ));
                $tempObj->addCondition('ctps_shop_id', '!=', 'NULL');
                $tempObj->setPageSize($collection['collection_primary_records']);
                $rs = $tempObj->getResultSet();
                if (!$shopIds = $db->fetchAll($rs, 'ctps_shop_id')) {
                    break;
                }
                $shopObj = clone $shopSearchObj;
                $shopObj->joinSellerSubscription();
                $shopObj->addCondition('shop_id', 'IN', array_keys($shopIds));
                $shopObj->addMultipleFields(
                    array( 'shop_id','shop_user_id','shop_ltemplate_id', 'shop_created_on', 'IFNULL(shop_name, shop_identifier) as shop_name', 'shop_description',
                    'shop_country_l.country_name as country_name', 'shop_state_l.state_name as state_name', 'shop_city',
                    'IFNULL(ufs.ufs_id, 0) as is_favorite' )
                );
                $shopRs = $shopObj->getResultSet();
                $collections = $db->fetchAll($shopRs, 'shop_id');

                $totalProdCountToDisplay = 4;

                foreach ($collections as $val) {
                    $prodSrch = clone $productSrchObj;
                    $prodSrch->addOrder('in_stock', 'DESC');
                    $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
                    $prodSrch->addShopIdCondition($val['shop_id']);
                    $prodSrch->setPageSize(4);
                    $prodSrch->addGroupBy('product_id');

                    $prodRs = $prodSrch->getResultSet();
                    $collections[$val['shop_id']]['products'] = $db->fetchAll($prodRs);
                    $collections[$val['shop_id']]['totalProducts'] = $prodSrch->recordCount();
                    $collections[$val['shop_id']]['shopRating'] = SelProdRating::getSellerRating($val['shop_user_id']);
                    $collections[$val['shop_id']]['shopTotalReviews'] = SelProdReview::getSellerTotalReviews($val['shop_user_id']);
                }
                $rs = $tempObj->getResultSet();

                unset($tempObj);
                $this->set('collections', $collections);
                $this->set('totalProdCountToDisplay', $totalProdCountToDisplay);
                break;
            case Collections::COLLECTION_TYPE_BRAND:
                $tempObj = clone $collectionObj;
                $tempObj->addCondition('collection_id', '=', $collection_id);
                $tempObj->joinCollectionBrands($this->siteLangId);
                $tempObj->addMultipleFields(array('ctpb_brand_id'));
                $tempObj->addCondition('ctpb_brand_id', '!=', 'NULL');
                $tempObj->setPageSize($collection['collection_primary_records']);
                $rs = $tempObj->getResultSet();
                $brandIds = $db->fetchAll($rs, 'ctpb_brand_id');

                unset($tempObj);
                if (empty($brandIds)) {
                    break;
                }

                /* fetch Categories data[ */
                $brandSearchTempObj = clone $brandSearchObj;
                $brandSearchTempObj->addCondition('brand_id', 'IN', array_keys($brandIds));
                $brandSearchTempObj->addOrder('brand_name', 'ASC');
                /* echo $brandSearchTempObj->getQuery(); die; */
                $rs = $brandSearchTempObj->getResultSet();
                /* ] */

                $collections[$collection['collection_layout_type']][$collection['collection_id']] = $collection;
                $collections[$collection['collection_layout_type']][$collection['collection_id']]['brands'] = $db->fetchAll($rs);
                unset($brandSearchTempObj);
                $this->set('collections', $collections);
                break;
        }

        $this->set('collection', $collection);
        $this->set('siteLangId', CommonHelper::getLangId());

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $this->_template->render(false, false);
    }
}
