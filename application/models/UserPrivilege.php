<?php
class UserPrivilege
{
    private static $instance = null ;

    private $loadedPermissions = array();

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function canSellerEditOption($optionId, $langId)
    {
        $userId =   UserAuthentication::getLoggedUserId();
        $option = new Option();
        if (!$row = $option->getOption($optionId, $userId)) {
            return false;
        }
        return true;
    }

    public static function canSellerEditOptionValue($optionId, $optionValueId, $langId)
    {
        $userId =   UserAuthentication::getLoggedUserId();
        $optionValue = new OptionValue($optionValueId);
        if (!$row = $optionValue->getOptionValue($optionId)) {
            return false;
        }
        return true;
    }

    public static function canEditSellerProductSpecification($specificationId, $productId)
    {
        $prodSpecObj = new ProdSpecification();
        if (!$row = $prodSpecObj->getProdSpecification($specificationId, $productId, '', false)) {
            return false;
        }
        return true;
    }

    public static function canSellerEditCustomProduct($productId = -1)
    {
        if ($productId<0) {
            return false;
        }

        /* Validate product belongs to current logged seller[ */
        $productRow = Product::getAttributesById($productId, array('product_seller_id'));
        if (!$productRow ||  $productRow['product_seller_id'] != UserAuthentication::getLoggedUserId()) {
            return false;
        }
        return true;
        /* ] */
    }

    public static function canEditSellerProduct($productId = -1)
    {
        if ($productId<0) {
            return false;
        }

        /* Validate product belongs to current logged seller[ */
        $sellerProductRow = SellerProduct::getAttributesById($productId, array('selprod_user_id'));

        if (!$sellerProductRow ||  $sellerProductRow['selprod_user_id'] != UserAuthentication::getLoggedUserId()) {
            return false;
        }
        return true;
        /* ] */
    }

    public static function canEditMetaTag($metaId = 0, $metaRecordId = 0)
    {
        if ($metaId==0 && !self::canEditSellerProduct($metaRecordId)) {
            return false;
        }
        if ($metaId>0 &&  !$data = MetaTag::getAttributesById($metaId, array('meta_record_id'))) {
            return false;
        }

        return true;
    }

    public static function canSellerUpdateTag($tagId)
    {
        $userId =   UserAuthentication::getLoggedUserId();

        if (!$data = Tag::getAttributesById($tagId, array('tag_user_id'))) {
            return false;
        } else {
            if ($data['tag_user_id']!=$userId) {
                return false;
            }
        }
        return true;
    }

    public static function canSellerUpdateBrandRequest($brandId)
    {
        $userId =   UserAuthentication::getLoggedUserId();

        if (!$data = Brand::getAttributesById($brandId, array('brand_seller_id'))) {
            return false;
        } else {
            if ($data['brand_seller_id']!=$userId) {
                return false;
            }
        }
        return true;
    }

    public static function canSellerAddNewProduct()
    {
        $userId =   UserAuthentication::getLoggedUserId();
        if (!self::isUserHasValidSubsription($userId)) {
            return false;
        }

        if (!FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0)) {
            return true;
        }
        /* if(FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE',FatUtility::VAR_INT,0)){

        if(!self::isUserHasValidSubsription($userId)){
        return false;
        }
        } */
        $products = new Product();
        $productsAllowed = OrderSubscription::getUserCurrentActivePlanDetails(CommonHelper::getLangId(), $userId, array('ossubs_products_allowed'));

        $totalProducts  =  $products->getTotalProductsAddedByUser($userId);
        if ($totalProducts >= $productsAllowed) {
            return false;
        }
        return true;
    }

    public static function isUserHasValidSubsription($userId = 0)
    {
        if ($userId<1) {
            return false;
        }

        if (!FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0)) {
            return true;
        }

        $latestOrder = OrderSubscription::getUserCurrentActivePlanDetails(CommonHelper::getLangId(), $userId, array('ossubs_till_date','ossubs_id'));
        if (empty($latestOrder)) {
            return false;
        } elseif ($latestOrder['ossubs_till_date']<date("Y-m-d")) {
            return false;
        }

        return true;
    }

    /* Subscription privildges */

    public static function canSellerUpgradeOrDowngradePlan($userId, $spPlanId = 0, $langId = 0)
    {
        $userId = FatUtility::int($userId);
        if (1 > $userId) {
            return false;
        }
        $currentActivePlanId = OrderSubscription:: getUserCurrentActivePlanDetails($langId, $userId, array(OrderSubscription::DB_TBL_PREFIX.'id'));
        if (!$currentActivePlanId) {
            return true;
        } else {
            $totalActiveProducts =  Product::getActiveCount($userId);
            $allowedLimit = SellerPackagePlans::getSubscriptionPlanDataByPlanId($spPlanId, $langId);

            if ($totalActiveProducts > $allowedLimit['spackage_products_allowed']) {
                Message::addErrorMessage(sprintf(Labels::getLabel('M_YOU_ARE_DOWNGRADING_YOUR_PACKAGE', $langId), $allowedLimit['spackage_products_allowed'], $totalActiveProducts));
                return false;
            }

            $totalActiveInventories =  SellerProduct::getActiveCount($userId);
            if ($totalActiveInventories > $allowedLimit['spackage_inventory_allowed']) {
                Message::addErrorMessage(sprintf(Labels::getLabel('M_YOU_ARE_DOWNGRADING_YOUR_PACKAGE', $langId), $allowedLimit['spackage_inventory_allowed'], $totalActiveInventories));
                return false;
            }

            /* if Downgrading package then give message to reduce products */
            /*$planDetails = SellerPackagePlans::getSubscriptionPlanDataByPlanId($spPlanId, $langId);
            $products = new Product();
            $totalProducts  =  $products->getTotalProductsAddedByUser($userId);
            if ($totalProducts > $planDetails[SellerPackages::DB_TBL_PREFIX.'products_allowed']) {
                Message::addErrorMessage(sprintf(Labels::getLabel('M_YOU_ARE_DOWNGRADING_YOUR_PACKAGE', $langId), $planDetails[SellerPackages::DB_TBL_PREFIX.'products_allowed'], $totalProducts));
                return false;
            }*/

            /* ] */
            /* $totalProductsAdded  =
            $totalImagesAdded  = */
        }
        return true;
    }

    public static function canSellerBuyFreePlan($userId = 0, $sPackageId = 0, $langId = 0)
    {
        if (!OrderSubscription::canUserBuyFreeSubscription) {
            return false;
        }
        return true;
    }

    public static function canEditSellerCollection($userId = 0)
    {
        //Pending
        return true;
    }

    public static function canSellerAddProductInCatalog($productId = 0, $userId = 0)
    {
        $product = Product::getAttributesById($productId);

        if ($userId !=$product['product_seller_id'] && $product['product_seller_id']!=0) {
            return false;
        }
        return true;
    }
}
