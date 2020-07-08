<?php
class Cart extends FatModel
{
    private $products = array();
    private $SYSTEM_ARR = array();
    private $warning;

    const CART_KEY_PREFIX_PRODUCT = 'SP_'; /* SP stands for Seller Product */
    const CART_KEY_PREFIX_BATCH = 'SB_'; /* SB stands for Seller Batch/Combo Product */
    const TYPE_PRODUCT = 1;
    const TYPE_SUBSCRIPTION = 2;
    public function __construct($user_id = 0, $langId = 0, $tempCartUserId = 0)
    {
        parent::__construct();

        $user_id = FatUtility::int($user_id);

        $langId = FatUtility::int($langId);

        $this->cart_lang_id = $langId;
        if (1 > $langId) {
            $this->cart_lang_id = CommonHelper::getLangId();
        }

        if (empty($tempCartUserId)) {
            $user_id = (0 < $user_id) ? $user_id : UserAuthentication::getLoggedUserId(true);
            $tempCartUserId = (0 < $user_id) ? $user_id : session_id();
        }

        $this->cart_user_id = $tempCartUserId;
        $this->cart_id = $tempCartUserId;

        if (UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged() || ($user_id > 0)) {
            if ($user_id > 0) {
                $this->cart_user_id = $user_id;
            } else {
                $this->cart_user_id = UserAuthentication::getLoggedUserId();
            }
        }

        $srch = new SearchBase('tbl_user_cart');
        $srch->addCondition('usercart_user_id', '=', $this->cart_user_id);
        $srch->addCondition('usercart_type', '=', CART::TYPE_PRODUCT);
        $rs = $srch->getResultSet();
        $this->cartSameSessionUser = true;
        
        if ($row = FatApp::getDb()->fetch($rs)) {
            if ($row['usercart_last_session_id'] != $this->cart_id) {
                $this->cartSameSessionUser = false;
            }

            $this->SYSTEM_ARR['cart'] = unserialize($row["usercart_details"]);
            // CommonHelper::printArray($this->SYSTEM_ARR['cart'], true);
            if (isset($this->SYSTEM_ARR['cart']['shopping_cart'])) {
                $this->SYSTEM_ARR['shopping_cart'] = $this->SYSTEM_ARR['cart']['shopping_cart'];
               
                unset($this->SYSTEM_ARR['cart']['shopping_cart']);
            }
        }

        if (!$this->cartSameSessionUser) {
            $this->removeUsedRewardPoints();
        }

        if (!isset($this->SYSTEM_ARR['cart']) || !is_array($this->SYSTEM_ARR['cart'])) {
            $this->SYSTEM_ARR['cart'] = array();
        }
        if (!isset($this->SYSTEM_ARR['shopping_cart']) || !is_array($this->SYSTEM_ARR['shopping_cart'])) {
            $this->SYSTEM_ARR['shopping_cart'] = array();
        }
    }

    public static function getCartKeyPrefixArr()
    {
        return array(
            static::CART_KEY_PREFIX_PRODUCT => static::CART_KEY_PREFIX_PRODUCT,
            static::CART_KEY_PREFIX_BATCH => static::CART_KEY_PREFIX_BATCH,
        );
    }

    public static function getCartUserId($tempUserId = 0)
    {
        $cart_user_id = session_id();
        if ($tempUserId != 0) {
            $cart_user_id = $tempUserId;
        }
        if (UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) {
            $cart_user_id = UserAuthentication::getLoggedUserId();
        }
        return $cart_user_id;
    }

    public static function getCartData($userId)
    {
        $srch = new SearchBase('tbl_user_cart');
        $srch->addCondition('usercart_user_id', '=', $userId);
        $srch->addCondition('usercart_type', '=', CART::TYPE_PRODUCT);
        $rs = $srch->getResultSet();
        if ($row = FatApp::getDb()->fetch($rs)) {
            return $row["usercart_details"];
        }
        return;
    }

    public function add($selprod_id, $qty = 1, $prodgroup_id = 0, $returnUserId = false)
    {
        $this->products = array();
        $selprod_id = FatUtility::int($selprod_id);
        $prodgroup_id = FatUtility::int($prodgroup_id);

        if ($qty > 0) {
            $key = static::CART_KEY_PREFIX_PRODUCT . $selprod_id;
            if ($prodgroup_id) {
                $key = static::CART_KEY_PREFIX_BATCH . $prodgroup_id;
            }
            $key = base64_encode(serialize($key));
            if (!isset($this->SYSTEM_ARR['cart'][$key])) {
                $this->SYSTEM_ARR['cart'][$key] = FatUtility::int($qty);
            } else {
                $this->SYSTEM_ARR['cart'][$key] += FatUtility::int($qty);
            }
        }

        if ($prodgroup_id > 0) {
            $products = $this->getProducts($this->cart_lang_id);
            if ($products) {
                foreach ($products as $cartKey => $product) {
                    if ($product['is_batch'] && $prodgroup_id == $product['prodgroup_id']) {
                        foreach ($product['products'] as $pgProduct) {
                            $this->updateTempStockHold($pgProduct['selprod_id'], $this->SYSTEM_ARR['cart'][$key], $product['prodgroup_id']);
                        }
                    }
                }
            }
        } else {
            $this->updateTempStockHold($selprod_id, $this->SYSTEM_ARR['cart'][$key]);
        }
        $this->updateUserCart();
        if ($returnUserId) {
            return $this->cart_user_id;
        }
        return true;
    }

    public function countProducts()
    {
        return count($this->SYSTEM_ARR['cart']);
    }

    public function hasProducts()
    {
        return count($this->SYSTEM_ARR['cart']);
    }

    public function hasStock()
    {
        $stock = true;
        foreach ($this->getProducts($this->cart_lang_id) as $product) {
            if (!$product['in_stock']) {
                $stock = false;
                break;
            }
        }
        return $stock;
    }

    public function hasDigitalProduct()
    {
        $isDigital = false;
        foreach ($this->getProducts($this->cart_lang_id) as $product) {
            if ($product['is_batch'] && !empty($product['products'])) {
                foreach ($product['products'] as $pgproduct) {
                    if ($pgproduct['is_digital_product']) {
                        $isDigital = true;
                        break;
                    }
                }
            } else {
                if ($product['is_digital_product']) {
                    $isDigital = true;
                    break;
                }
            }
        }
        $this->products = array();
        return $isDigital;
    }

    public function hasPhysicalProduct()
    {
        $isPhysical = false;
        foreach ($this->getProducts($this->cart_lang_id) as $product) {
            if ($product['is_batch'] && !empty($product['products'])) {
                foreach ($product['products'] as $pgproduct) {
                    if ($pgproduct['is_physical_product']) {
                        $isPhysical = true;
                        break;
                    }
                }
            } else {
                if (!empty($product['is_physical_product'])) {
                    $isPhysical = true;
                    break;
                }
            }
        }
        $this->products = array();
        return $isPhysical;
    }

    public function getProducts($siteLangId = 0)
    {
        /* if( !$siteLangId ){
            trigger_error("Language Id not specified.", E_USER_ERROR);
        } */
        if (!$this->products) {
            $productSelectedShippingMethodsArr = $this->getProductShippingMethod();
            $maxConfiguredCommissionVal = FatApp::getConfig("CONF_MAX_COMMISSION", FatUtility::VAR_INT, 0);
            
            $db = FatApp::getDb();
            $prodGroupQtyArr = array();
            $prodGroupPriceArr = array();

            $associatedAffiliateUserId = 0;
            /* detect current logged user has associated affiliate user[ */
            $loggedUserId = 0;
            if (UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) {
                $loggedUserId = UserAuthentication::getLoggedUserId();
                $associatedAffiliateUserId = User::getAttributesById($loggedUserId, 'user_affiliate_referrer_user_id');
                if ($associatedAffiliateUserId > 0) {
                    $prodObj = new Product();
                }
            }
            /* ] */

            $is_cod_enabled = true;
            foreach ($this->SYSTEM_ARR['cart'] as $key => $quantity) {
                $selprod_id = 0;
                $prodgroup_id = 0;
                $sellerProductRow = array();

                $affiliateCommissionPercentage = '';
                $affiliateCommission = 0;

                $keyDecoded = unserialize(base64_decode($key));
                if (strpos($keyDecoded, static::CART_KEY_PREFIX_PRODUCT) !== false) {
                    $selprod_id = FatUtility::int(str_replace(static::CART_KEY_PREFIX_PRODUCT, '', $keyDecoded));
                }

                //To rid of from invalid product detail in listing.
                if (1 > $selprod_id) {
                    unset($this->SYSTEM_ARR['cart'][$key]);
                    continue;
                }

                /* CommonHelper::printArray($keyDecoded); die; */
                // if( strpos($keyDecoded, static::CART_KEY_PREFIX_BATCH ) !== FALSE ){
                // $prodgroup_id = FatUtility::int(str_replace( static::CART_KEY_PREFIX_BATCH, '', $keyDecoded ));
                // }

                $this->products[$key]['shipping_cost'] = 0;
                $this->products[$key]['sduration_id'] = 0;
                $this->products[$key]['commission_percentage'] = '';
                $this->products[$key]['commission'] = 0;


                $this->products[$key]['tax'] = 0;
                $this->products[$key]['reward_point'] = 0;
                $this->products[$key]['volume_discount'] = 0;
                $this->products[$key]['volume_discount_total'] = 0;

                $selProdCost = $shopId = '';
                /* seller products[ */
                if ($selprod_id > 0) {
                    $sellerProductRow = $this->getSellerProductData($selprod_id, $quantity, $siteLangId, $loggedUserId);

                    /* echo "<pre>"; var_dump($sellerProductRow); */
                    if (!$sellerProductRow) {
                        $this->removeCartKey($key);
                        continue;
                    }
                    $this->products[$key] = $sellerProductRow;

                    /*[COD available*/
                    $codEnabled = false;
                    if ($is_cod_enabled && Product::isProductShippedBySeller($sellerProductRow['product_id'], $sellerProductRow['product_seller_id'], $sellerProductRow['selprod_user_id'])) {
                        $walletBalance = User::getUserBalance($sellerProductRow['selprod_user_id']);
                        if ($sellerProductRow['selprod_cod_enabled']) {
                            $codEnabled = true;
                        }
                        $codMinWalletBalance = -1;
                        $shop_cod_min_wallet_balance = Shop::getAttributesByUserId($sellerProductRow['selprod_user_id'], 'shop_cod_min_wallet_balance');
                        if ($shop_cod_min_wallet_balance > -1) {
                            $codMinWalletBalance = $shop_cod_min_wallet_balance;
                        } elseif (FatApp::getConfig('CONF_COD_MIN_WALLET_BALANCE', FatUtility::VAR_FLOAT, -1) > -1) {
                            $codMinWalletBalance = FatApp::getConfig('CONF_COD_MIN_WALLET_BALANCE', FatUtility::VAR_FLOAT, -1);
                        }
                        if ($codMinWalletBalance > -1 && $codMinWalletBalance > $walletBalance) {
                            $codEnabled = false;
                        }
                    } else {
                        if ($sellerProductRow['product_cod_enabled']) {
                            $codEnabled = true;
                        }
                    }
                    $is_cod_enabled = $codEnabled;
                    /* ]*/

                    /*[ Product shipping cost */
                    $shippingCost = 0;
                    if (!empty($productSelectedShippingMethodsArr['product']) && isset($productSelectedShippingMethodsArr['product'][$sellerProductRow['selprod_id']])) {
                        $shippingDurationRow = $productSelectedShippingMethodsArr['product'][$sellerProductRow['selprod_id']];
                        $this->products[$key]['sduration_id'] = isset($shippingDurationRow['sduration_id'])?$shippingDurationRow['sduration_id']:'';
                        $shippingCost = ROUND(($shippingDurationRow['mshipapi_cost']), 2);
                        $this->products[$key]['shipping_cost'] = $shippingCost;
                    }
                    /*]*/
                    //CommonHelper::printArray($sellerProductRow);exit;
                    /*[ Product Tax */
                    $taxableProdPrice = $sellerProductRow['theprice'] - $sellerProductRow['volume_discount'];
                    $taxObj = new Tax();
                    $tax = $taxObj->calculateTaxRates($sellerProductRow['product_id'], $taxableProdPrice, $sellerProductRow['selprod_user_id'], $siteLangId, $quantity);
                    $this->products[$key]['tax'] = $tax;
                    /*]*/

                    /*[ Product Commission */
                    $commissionPercentage = SellerProduct::getProductCommission($sellerProductRow['selprod_id']);
                    $commissionCostValue = $sellerProductRow['theprice'];

                    if (FatApp::getConfig('CONF_COMMISSION_INCLUDING_TAX', FatUtility::VAR_INT, 0) && $tax) {
                        $commissionCostValue = $commissionCostValue + ($tax / $quantity);
                    }

                    if (FatApp::getConfig('CONF_COMMISSION_INCLUDING_SHIPPING', FatUtility::VAR_INT, 0) && $shippingCost && $this->products[$key]['psbs_user_id'] > 0) {
                        $commissionCostValue = $commissionCostValue + ($shippingCost / $quantity);
                    }

                    $commissionCostValue =  ROUND(($commissionCostValue * $quantity), 2);
                    $commission = ROUND(($commissionCostValue * $commissionPercentage/100), 2);
                    $commission = MIN($commission, $maxConfiguredCommissionVal);

                    $this->products[$key]['commission_percentage'] = $commissionPercentage;
                    $this->products[$key]['commission'] = ROUND($commission, 2);
                    /*]*/

                    /* Affiliate Commission[ */
                    if ($associatedAffiliateUserId > 0) {
                        $affiliateCommissionPercentage = AffiliateCommission::getAffiliateCommission($associatedAffiliateUserId, $sellerProductRow['product_id'], $prodObj);
                        $affiliateCommissionCostValue =  ROUND($sellerProductRow['theprice'] * $quantity, 2);
                        $affiliateCommission = ROUND($affiliateCommissionCostValue * $affiliateCommissionPercentage/100, 2);
                    }
                    /* ] */
                    $selProdCost = $sellerProductRow['selprod_cost'];
                    $shopId = $sellerProductRow['shop_id'];
                } else {
                    $is_cod_enabled = false;
                }
                /* ] */

                $this->products[$key]['key'] = $key;
                $this->products[$key]['is_batch'] = 0;
                $this->products[$key]['is_cod_enabled'] = $is_cod_enabled;
                $this->products[$key]['selprod_id'] = $selprod_id;
                $this->products[$key]['quantity'] = $quantity;
                $this->products[$key]['has_physical_product'] = 0;
                $this->products[$key]['has_digital_product'] = 0;
                /* $this->products[$key]['product_ship_free'] = $sellerProductRow['product_ship_free']; */
                $this->products[$key]['selprod_cost'] = $selProdCost;
                $this->products[$key]['is_shipping_selected'] = false;
                $this->products[$key]['affiliate_commission_percentage'] = $affiliateCommissionPercentage;
                $this->products[$key]['affiliate_commission'] = $affiliateCommission;
                $this->products[$key]['affiliate_user_id'] = $associatedAffiliateUserId;
                if (UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) {
                    $this->products[$key]['shipping_address'] =  UserAddress::getUserAddresses(UserAuthentication::getLoggedUserId(), $siteLangId, 0, $this->getCartShippingAddress());
                    $this->products[$key]['seller_address'] =  Shop::getShopAddress($shopId, true, $siteLangId);
                }
            }

            $sellerPrice = $this->getSellersProductItemsPrice($this->products);
            foreach ($this->products as $cartkey => $cartval) {
                $this->products[$cartkey]['shop_eligible_for_free_shipping'] = 0;

                if (!empty($cartval['selprod_user_id']) && array_key_exists($cartval['selprod_user_id'], $sellerPrice)) {
                    $this->products[$cartkey]['totalPrice'] = $sellerPrice[$cartval['selprod_user_id']]['totalPrice'];
                    if ($cartval['shop_free_ship_upto'] > 0 && $cartval['shop_free_ship_upto'] < $sellerPrice[$cartval['selprod_user_id']]['totalPrice']) {
                        $this->products[$cartkey]['shop_eligible_for_free_shipping'] = 1;
                    }
                }
            }
        }
        /* CommonHelper::printArray($this->products); die(); */
        return $this->products;
    }

    public function getSellerProductData($selprod_id, &$quantity, $siteLangId, $loggedUserId = 0)
    {
        $prodSrch = new ProductSearch($siteLangId);
        $prodSrch->setDefinedCriteria();
        $prodSrch->joinProductToCategory();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->joinProductShippedBy();
        $prodSrch->joinProductFreeShipping();
        $prodSrch->joinSellers();
        $prodSrch->doNotCalculateRecords();
        $prodSrch->doNotLimitRecords();
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $prodSrch->addMultipleFields(array( 'product_id', 'product_type', 'product_length', 'product_width', 'product_height','product_ship_free' ,
        'product_dimension_unit', 'product_weight', 'product_weight_unit',
        'selprod_id','selprod_code', 'selprod_stock','selprod_user_id','IF(selprod_stock > 0, 1, 0) AS in_stock','selprod_min_order_qty',
        'special_price_found', 'theprice', 'shop_id', 'shop_free_ship_upto',
        'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type', 'selprod_price', 'selprod_cost','case when product_seller_id=0 then IFNULL(psbs_user_id,0)   else product_seller_id end  as psbs_user_id','product_seller_id','product_cod_enabled','selprod_cod_enabled'));

        if ($siteLangId) {
            $prodSrch->joinBrands();
            $prodSrch->addFld(array('IFNULL(product_name, product_identifier) as product_name','IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title','IFNULL(brand_name, brand_identifier) as brand_name','IFNULL(shop_name, shop_identifier) as shop_name'));
        }

        if (0 < $loggedUserId) {
            if (FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1) == applicationConstants::NO) {
                $prodSrch->joinFavouriteProducts($loggedUserId);
                $prodSrch->addFld('ufp_id');
            } else {
                $prodSrch->joinUserWishListProducts($loggedUserId);
                $prodSrch->addFld('IFNULL(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist, IFNULL(uwlp.uwlp_uwlist_id, 0) as uwlp_uwlist_id');
            }
        }

        $rs = $prodSrch->getResultSet();
        $sellerProductRow = FatApp::getDb()->fetch($rs);
        if (!$sellerProductRow || $sellerProductRow['selprod_stock'] <= 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Product_not_available_or_out_of_stock_so_removed_from_cart_listing', $siteLangId));
            return false;
        }

        $productSelectedShippingMethodsArr = $this->getProductShippingMethod();
        if (($quantity > $sellerProductRow['selprod_stock'])) {
            /* requested quantity cannot more than stock available */
            $quantity = $sellerProductRow['selprod_stock'];
        }

        /* update/fetch/apply theprice, according to volume discount module[ */
        $sellerProductRow['volume_discount'] = 0;
        $sellerProductRow['volume_discount_percentage'] = 0;
        $sellerProductRow['volume_discount_total'] = 0;
        $srch = new SellerProductVolumeDiscountSearch();
        $srch->doNotCalculateRecords();
        $srch->addCondition('voldiscount_selprod_id', '=', $sellerProductRow['selprod_id']);
        $srch->addCondition('voldiscount_min_qty', '<=', $quantity);
        $srch->addOrder('voldiscount_min_qty', 'DESC');
        $srch->setPageSize(1);
        $srch->addMultipleFields(array('voldiscount_percentage'));
        $rs = $srch->getResultSet();
        $volumeDiscountRow = FatApp::getDb()->fetch($rs);
        if ($volumeDiscountRow) {
            $volumeDiscount = $sellerProductRow['theprice'] * ($volumeDiscountRow['voldiscount_percentage'] / 100);
            //$sellerProductRow['theprice'] = $sellerProductRow['theprice'] - $volumeDiscount;
            $sellerProductRow['volume_discount_percentage'] = $volumeDiscountRow['voldiscount_percentage'];
            $sellerProductRow['volume_discount'] = $volumeDiscount;
            $sellerProductRow['volume_discount_total'] = $volumeDiscount * $quantity;
        }
        /* ] */

        /* set variable of shipping cost of the product, if shipping already selected[ */
        $sellerProductRow['shipping_cost'] = 0;
        $sellerProductRow['sduration_id'] = 0;
        if (!empty($productSelectedShippingMethodsArr) && isset($productSelectedShippingMethodsArr[$selprod_id])) {
            $shippingDurationRow = $productSelectedShippingMethodsArr[$selprod_id];
            $sellerProductRow['sduration_id'] = $shippingDurationRow['sduration_id'];
            $sellerProductRow['shipping_cost'] = ROUND(($shippingDurationRow['mshipapi_cost'] * $quantity), 2);
        }
        /* ] */

        /* calculation of commission and tax against each product[ */
        $commission = 0;
        $tax = 0;
        $maxConfiguredCommissionVal = FatApp::getConfig("CONF_MAX_COMMISSION");

        $commissionPercentage = SellerProduct::getProductCommission($selprod_id);
        $commission = MIN(ROUND($sellerProductRow['theprice'] * $commissionPercentage/100, 2), $maxConfiguredCommissionVal);
        $sellerProductRow['commission_percentage'] = $commissionPercentage;
        $sellerProductRow['commission'] = ROUND($commission * $quantity, 2);

        $totalPrice = $sellerProductRow['theprice'] * $quantity;
        $taxableProdPrice = $sellerProductRow['theprice'] - $sellerProductRow['volume_discount'];

        $taxObj = new Tax();
        $tax = $taxObj->calculateTaxRates($sellerProductRow['product_id'], $taxableProdPrice, $sellerProductRow['selprod_user_id'], $siteLangId, $quantity);

        $sellerProductRow['tax'] = $tax;
        /* ] */

        $sellerProductRow['total'] = $totalPrice;
        $sellerProductRow['netTotal'] = $sellerProductRow['total'] + $sellerProductRow['shipping_cost'];

        $sellerProductRow['is_digital_product'] = ($sellerProductRow['product_type'] == Product::PRODUCT_TYPE_DIGITAL) ? 1 : 0;
        $sellerProductRow['is_physical_product'] = ($sellerProductRow['product_type'] == Product::PRODUCT_TYPE_PHYSICAL) ? 1 : 0;


        if ($siteLangId) {
            $sellerProductRow['options'] = SellerProduct::getSellerProductOptions($selprod_id, true, $siteLangId);
        } else {
            $sellerProductRow['options'] = SellerProduct::getSellerProductOptions($selprod_id, false);
        }
        return $sellerProductRow;
    }

    public function removeCartKey($key)
    {
        unset($this->products[$key]);
        unset($this->SYSTEM_ARR['cart'][$key]);
        $this->updateUserCart();
        return true;
    }

    public function remove($key)
    {
        $this->products = array();
        $cartProducts = $this->getProducts($this->cart_lang_id);
        $found = false;
        if (is_array($cartProducts)) {
            foreach ($cartProducts as $cartKey => $product) {
                if ($key == 'all') {
                    $found = true;
                    unset($this->SYSTEM_ARR['cart'][$cartKey]);
                    /* to keep track of temporary hold the product stock[ */
                    $this->updateTempStockHold($product['selprod_id'], 0, 0);
                /* ] */
                } elseif (md5($product['key']) == $key && !$product['is_batch']) {
                    $found = true;
                    unset($this->SYSTEM_ARR['cart'][$cartKey]);
                    /* to keep track of temporary hold the product stock[ */
                    $this->updateTempStockHold($product['selprod_id'], 0, 0);
                    /* ] */
                    break;
                }
            }
        }
        $this->updateUserCart();
        if (false === $found) {
            $this->error = Labels::getLabel('ERR_Invalid_Product', $this->cart_lang_id);
        }
        return $found;
    }

    public function removeGroup($prodgroup_id)
    {
        $prodgroup_id = FatUtility::int($prodgroup_id);
        $this->products = array();
        $cartProducts = $this->getProducts($this->cart_lang_id);
        if (is_array($cartProducts)) {
            foreach ($cartProducts as $cartKey => $product) {
                if ($product['is_batch'] && $product['prodgroup_id'] == $prodgroup_id) {
                    unset($this->SYSTEM_ARR['cart'][$cartKey]);

                    /* to keep track of temporary hold the product stock[ */
                    foreach ($product['products'] as $pgproduct) {
                        $this->updateTempStockHold($pgproduct['selprod_id'], 0, $prodgroup_id);
                    }
                    /* ] */
                    break;
                }
            }
        }
        $this->updateUserCart();
        return true;
    }

    public function getWarning()
    {
        return $this->warning;
    }

    public function update($key, $quantity)
    {
        $quantity = FatUtility::int($quantity);
        $found = false;
        if ($quantity > 0) {
            $cartProducts = $this->getProducts($this->cart_lang_id);
            $cart_user_id = $this->cart_user_id;

            if (is_array($cartProducts)) {
                foreach ($cartProducts as $cartKey => $product) {
                    if (md5($product['key']) == $key) {
                        $found = true;
                        /* minimum quantity check[ */
                        $minimum_quantity = ($product['selprod_min_order_qty']) ? $product['selprod_min_order_qty'] : 1;
                        if ($quantity < $minimum_quantity) {
                            $str = Labels::getLabel('LBL_Please_add_minimum_{minimumquantity}', $this->cart_lang_id);
                            $str = str_replace("{minimumquantity}", $minimum_quantity, $str);
                            $this->warning = $str." ".FatUtility::decodeHtmlEntities($product['product_name']);
                            break;
                        }
                        /* ] */


                        $tempHoldStock = Product::tempHoldStockCount($product['selprod_id']);
                        $availableStock = $cartProducts[$cartKey]['selprod_stock'] - $tempHoldStock;
                        $userTempHoldStock = Product::tempHoldStockCount($product['selprod_id'], $cart_user_id, 0, true);

                        if ($quantity > $userTempHoldStock) {
                            if ($availableStock == 0 || ($availableStock < ($quantity - $userTempHoldStock))) {
                                $this->warning = Labels::getLabel('MSG_Requested_quantity_more_than_stock_available', $this->cart_lang_id);
                                $quantity = $userTempHoldStock + $availableStock;
                            }
                        }

                        if ($quantity) {
                            $this->SYSTEM_ARR['cart'][$cartKey] = $quantity;
                            /* to keep track of temporary hold the product stock[ */
                            $this->updateTempStockHold($product['selprod_id'], $quantity);
                            /* ] */
                            break;
                        } else {
                            $this->remove($key);
                        }
                    }
                }
            }
            $this->updateUserCart();
        } else {
            $this->error = Labels::getLabel('ERR_Quantity_should_be_greater_than_0', $this->cart_lang_id);
            return false;
        }
        if (false === $found) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->cart_lang_id);
        }
        return $found;
    }

    public function updateGroup($prodgroup_id, $quantity)
    {
        $prodgroup_id = FatUtility::int($prodgroup_id);
        $quantity = FatUtility::int($quantity);

        $cart_user_id = $this->cart_user_id;
        /* not handled the case, if any product from the group is added separately, stock sum from that product and product in group is not checked, need to handle the same. */

        if ($quantity > 0) {
            $cartProducts = $this->getProducts($this->cart_lang_id);
            if (is_array($cartProducts)) {
                $prodGroupQtyArr = array();
                $inStock = true;
                foreach ($cartProducts as $cartKey => $product) {
                    if ($product['is_batch'] && $product['prodgroup_id'] == $prodgroup_id) {
                        foreach ($product['products'] as $pgproduct) {
                            $tempHoldStock = Product::tempHoldStockCount($pgproduct['selprod_id']);
                            $availableStock = $pgproduct['selprod_stock'] - $tempHoldStock;
                            $userTempHoldStock = Product::tempHoldStockCount($pgproduct['selprod_id'], $cart_user_id, $product['prodgroup_id'], true);

                            if ($availableStock == 0 || ($availableStock < ($quantity - $userTempHoldStock))) {
                                $this->warning = Labels::getLabel('MSG_Requested_quantity_more_than_stock_available', $this->cart_lang_id);
                                $quantity = $userTempHoldStock + $availableStock;
                                $inStock = false;
                                break;
                            }
                            $prodGroupQtyArr[$pgproduct['selprod_id']] = $quantity;
                        }

                        if (!$inStock) {
                            break;
                        }
                    }
                }

                if (!empty($prodGroupQtyArr)) {
                    $maxAvailableQty = min($prodGroupQtyArr);
                    if ($quantity > $maxAvailableQty) {
                        /* $msgString = str_replace("{n}", $maxAvailableQty, "MSG_One_of_the_product_in_combo_is_not_available_in_requested_quantity,_you_can_buy_upto_max_{n}_quantity."); */
                        $this->warning = Labels::getLabel("MSG_One_of_the_product_in_combo_is_not_available_in_requested_quantity,_you_can_buy_upto_max_{n}_quantity.", $this->cart_lang_id);
                        $this->warning = str_replace("{n}", $maxAvailableQty, $this->warning);
                        return true;
                    }
                }

                if ($inStock) {
                    foreach ($cartProducts as $cartKey => $product) {
                        if ($product['is_batch'] && $product['prodgroup_id'] == $prodgroup_id) {
                            $this->SYSTEM_ARR['cart'][$cartKey] = $quantity;
                            foreach ($product['products'] as $pgproduct) {
                                $this->updateTempStockHold($pgproduct['selprod_id'], $quantity, $prodgroup_id);
                            }
                        }
                    }
                }
            }
            $this->updateUserCart();
        }
        return true;
    }

    public function setCartBillingAddress($address_id = 0)
    {
        $address_id = FatUtility::int($address_id);
        if (1 > $address_id) {
            $address = UserAddress::getDefaultAddressId($this->cart_user_id);
            if (!empty($address)) {
                $address_id = $address['ua_id'];
            }
        }
        $this->SYSTEM_ARR['shopping_cart']['billing_address_id'] = $address_id;
        $this->updateUserCart();
        return true;
    }

    public function setCartShippingAddress($address_id)
    {
        $this->SYSTEM_ARR['shopping_cart']['shipping_address_id'] = $address_id;
        $this->updateUserCart();
        return true;
    }

    public function unsetCartShippingAddress()
    {
        unset($this->SYSTEM_ARR['shopping_cart']['shipping_address_id']);
        $this->updateUserCart();
        return true;
    }

    public function setShippingAddressSameAsBilling()
    {
        $billing_address_id = $this->getCartBillingAddress();
        if ($billing_address_id) {
            $this->setCartShippingAddress($billing_address_id);
            $this->SYSTEM_ARR['shopping_cart']['isShippingSameAsBilling'] = true;
        }
    }

    public function unSetShippingAddressSameAsBilling()
    {
        if (isset($this->SYSTEM_ARR['shopping_cart']['isShippingSameAsBilling'])) {
            unset($this->SYSTEM_ARR['shopping_cart']['isShippingSameAsBilling']);
        }
    }

    public function setCartShippingApi($shippingapi_id)
    {
        $this->SYSTEM_ARR['shopping_cart']['shippingapi_id'] = FatUtility::int($shippingapi_id);
        $this->updateUserCart();
        return true;
    }

    public function getCartBillingAddress()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['billing_address_id']) ? FatUtility::int($this->SYSTEM_ARR['shopping_cart']['billing_address_id']) : 0;
    }

    public function getCartShippingAddress()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['shipping_address_id']) ? FatUtility::int($this->SYSTEM_ARR['shopping_cart']['shipping_address_id']) : 0;
    }

    public function getCartShippingApi()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['shippingapi_id']) ? FatUtility::int($this->SYSTEM_ARR['shopping_cart']['shippingapi_id']) : 0;
    }

    public function getShippingAddressSameAsBilling()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['isShippingSameAsBilling']) ? FatUtility::int($this->SYSTEM_ARR['shopping_cart']['isShippingSameAsBilling']) : 0;
    }

    public function setProductShippingMethod($arr)
    {
        $this->SYSTEM_ARR['shopping_cart']['product_shipping_methods'] = $arr;
        $this->updateUserCart();
        return true;
    }

    public function removeProductShippingMethod()
    {
        unset($this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']);
        $this->updateUserCart();
        return true;
    }

    public function getProductShippingMethod()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']) ? $this->SYSTEM_ARR['shopping_cart']['product_shipping_methods'] : array();
    }

    public function isProductShippingMethodSet()
    {
        foreach ($this->getProducts($this->cart_lang_id) as $product) {
            if ($product['is_batch']) {
                if ($product['has_physical_product'] && !isset($this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']['group'][$product['prodgroup_id']])) {
                    return false;
                }
                if (isset($this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']['group'][$product['prodgroup_id']]['mshipapi_id'])) {
                    $mshipapi_id = $this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']['group'][$product['prodgroup_id']]['mshipapi_id'];
                    $manualShipingApiRow = ManualShippingApi::getAttributesById($mshipapi_id, 'mshipapi_id');
                    if (!$manualShipingApiRow) {
                        return false;
                    }
                }
            } else {
                if ($product['is_physical_product'] && !isset($this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']['product'][$product['selprod_id']])) {
                    return false;
                }


                if (isset($this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']['product'][$product['selprod_id']]['mshipapi_id'])) {
                    $shipapi_id = $this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']['product'][$product['selprod_id']]['mshipapi_id'];
                    $ShipingApiRow = ShippingApi::getAttributesById($shipapi_id, 'shippingapi_id');

                    if (!$ShipingApiRow) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function getSubTotal()
    {
        $cartTotal = 0;
        $products = $this->getProducts($this->cart_lang_id);
        // CommonHelper::printArray($products); die;
        if (is_array($products) && count($products) > 0) {
            foreach ($products as $product) {
                $cartTotal += $product['total'];
            }
        }
        return $cartTotal;
    }

    public function getCartFinancialSummary($langId)
    {
        $products = $this->getProducts($langId);
        
        $cartTotal = 0;
        $cartTotalNonBatch = 0;
        $cartTotalBatch = 0;
        $shippingTotal = 0;
        $originalShipping = 0;
        $cartTotalAfterBatch = 0;
        $orderPaymentGatewayCharges = 0;
        $cartTaxTotal = 0;
        $cartDiscounts = self::getCouponDiscounts();
        
        $totalSiteCommission = 0;
        $orderNetAmount = 0;
        $cartRewardPoints = self::getCartRewardPoint();
        $cartVolumeDiscount = 0;

        $isCodEnabled = true;
        $userCityId = 0;
        if (is_array($products) && count($products)) {
            $totalWeight = 0;
            $userWiseWeight = array();
            foreach ($products as $product) {
                // print "<pre>";
                // print_r($product);
                // exit();
                if(isset($product['shipping_address'])){
                    if(isset($product['shipping_address'][0])){
                        $userCityId = $product['shipping_address'][0]['ua_city_[id']; //Get user Session City
                    }else if(!empty($product['shipping_address'])){
                        $userCityId = $product['shipping_address']['ua_city_id']; //Get user Session City
                    }
                    
                }
                $codEnabled = false;
                if ($isCodEnabled && $product['is_cod_enabled']) {
                    $codEnabled = true;
                }
                $isCodEnabled = $codEnabled;

                if ($product['is_batch']) {
                    //$cartTotalBatch += $product['prodgroup_total'];
                    $cartTotal += $product['prodgroup_total'];
                } else {
                    //$cartTotalNonBatch += $product['total'];
                    $cartTotal += !empty($product['total']) ? $product['total'] : 0;
                }

                $cartVolumeDiscount += $product['volume_discount_total'];
                
                /* $taxableProdPrice = $product['theprice'] - $product['volume_discount'] - ($cartDiscounts['discountedSelProdIds'][$product['selprod_id']])/$product['quantity'];
                $taxObj = new Tax();
                $tax = $taxObj->calculateTaxRates($product['product_id'], $taxableProdPrice, $product['selprod_user_id'], $langId, $product['quantity']);
                $cartTaxTotal += $tax; */
                $cartTaxTotal +=  $product['tax'];
                
                $originalShipping += $product['shipping_cost'];
                //$totalSiteCommission += $product['commission'];

                // Actul Logic
               /* if (!$product['shop_eligible_for_free_shipping'] || $product['psbs_user_id'] == 0) {
                    $shippingTotal += $product['shipping_cost'];
                }*/

               // ========= Added by Ali (Additioanl Code)
                if($product['product_weight_unit'] == 1){
                    $kg = $product['product_weight']/1000;
                }else if($product['product_weight_unit'] == 3){
                    $kg = $product['product_weight']/2.20;
                }else{
                    $kg = $product['product_weight'];
                }

               //echo $product['product_weight'];
               $kg = number_format((float)$kg, 2, '.', '');
                
                if (array_key_exists($product['selprod_user_id'],$userWiseWeight)){
                    if(!$this->checkOverrideShippingCost($product['selprod_user_id'], $product['product_id'], $userCityId)){
                        $userWiseWeight[$product['selprod_user_id']]['weight'] += $kg*$product['quantity'];
                    }
                    $userWiseWeight[$product['selprod_user_id']]['prod'][] = $product['product_id'];
                    $userWiseWeight[$product['selprod_user_id']]['qty'][] = $product['quantity'];
                }else{
                    if(!$this->checkOverrideShippingCost($product['selprod_user_id'], $product['product_id'], $userCityId)){
                       $userWiseWeight[$product['selprod_user_id']]['weight'] = $kg*$product['quantity'];
                    }else{
                        $userWiseWeight[$product['selprod_user_id']]['weight'] = 0;
                    }
                    $userWiseWeight[$product['selprod_user_id']]['prod'][] = $product['product_id'];
                    $userWiseWeight[$product['selprod_user_id']]['qty'][] = $product['quantity'];
                }

                // ========= Added by Ali
            }
                
             // ========= Added by Ali
                $totalweightCost = 0;
                // print "<pre>";
                // print_r($userWiseWeight);
                // print "</pre>";
                foreach($userWiseWeight as $key => $weight_Arr){
                    $userId = $key;
                    $weight = ceil($weight_Arr['weight']);
                    // echo $weight; 
                   if($weight != 0){
                    $totalweightCost += $this->globleShippingCost($userId, $weight, $userCityId);
                   }
                   foreach($weight_Arr['prod'] as $pKey => $productId){
                        $totalweightCost += $this->overRideCostCalculate($userId, $productId, $userCityId, $weight_Arr['qty'][$pKey]);
                    }
                }
                $shippingTotal = $totalweightCost;
                //echo $totalweightCost;
                //exit();
             // ========= Added by Ali

             //by Ali added 3.5% commission on shipping Cost
             $totalSiteCommission += ($shippingTotal*3.5)/100;
        }

        // echo '<pre>';
        // print_r($products);
        // exit();

        $cartTotalAfterBatch = $cartTotalBatch + $cartTotalNonBatch;
        //$netTotalAfterDiscount = $netTotalWithoutDiscount;
        $userWalletBalance = User::getUserBalance($this->cart_user_id);
        //$orderCreditsCharge = $this->isCartUserWalletSelected() ? min($netTotalAfterDiscount, $userWalletBalance) : 0;
        //$orderPaymentGatewayCharges = $netTotalAfterDiscount - $orderCreditsCharge;

        $totalDiscountAmount = (isset($cartDiscounts['coupon_discount_total'])) ? $cartDiscounts['coupon_discount_total'] : 0;
        $orderNetAmount = (max($cartTotal - $cartVolumeDiscount - $totalDiscountAmount, 0)  + $shippingTotal  + $cartTaxTotal) ;

        $orderNetAmount = $orderNetAmount - CommonHelper::rewardPointDiscount($orderNetAmount, $cartRewardPoints);
        $WalletAmountCharge = ($this->isCartUserWalletSelected()) ? min($orderNetAmount, $userWalletBalance) : 0;
        $orderPaymentGatewayCharges = $orderNetAmount - $WalletAmountCharge;

        $isCodValidForNetAmt = true;
        if (FatApp::getConfig("CONF_MAX_COD_ORDER_LIMIT", FatUtility::VAR_INT, 0) > 0) {
            if (($orderPaymentGatewayCharges >= FatApp::getConfig("CONF_MIN_COD_ORDER_LIMIT", FatUtility::VAR_INT, 0)) && ($orderPaymentGatewayCharges <= FatApp::getConfig("CONF_MAX_COD_ORDER_LIMIT", FatUtility::VAR_INT, 0)) && ($isCodEnabled)) {
                $isCodValidForNetAmt = true;
            } else {
                $isCodValidForNetAmt = false;
            }
        }

        $netChargeAmt = $cartTotal + $cartTaxTotal - ((0 < $cartVolumeDiscount) ? $cartVolumeDiscount: 0);

        $cartSummary = array(
            'cartTotal' => $cartTotal,
            'shippingTotal' => $shippingTotal,
            'originalShipping' => $originalShipping,
            'cartTaxTotal' => $cartTaxTotal,
            'cartDiscounts' => $cartDiscounts,
            'cartVolumeDiscount'=> $cartVolumeDiscount,
            'cartRewardPoints' => $cartRewardPoints,
            'cartWalletSelected'=> $this->isCartUserWalletSelected(),
            'siteCommission' => $totalSiteCommission,
            'orderNetAmount' => $orderNetAmount,
            'WalletAmountCharge'=> $WalletAmountCharge,
            'isCodEnabled' => $isCodEnabled,
            'isCodValidForNetAmt' => $isCodValidForNetAmt,
            'orderPaymentGatewayCharges' => $orderPaymentGatewayCharges,
            'netChargeAmount' => $netChargeAmt,
        );

        //CommonHelper::printArray($cartSummary); die();
        return $cartSummary;
    }

    public function checkOverrideShippingCost($userId, $productId, $userCityId){
        $srch = new SearchBase('tbl_product_shipping_rates');
        $srch->addCondition('pship_user_id', '=', $userId);
        $srch->addCondition('pship_city', '=', $userCityId);
        $srch->addCondition('pship_prod_id', '=', $productId);
        $rs = $srch->getResultSet();
        $result = FatApp::getDb()->fetchAll($rs);
        if($result){
            return true;
        }else{
            return false;
        }
    }

    public function globleShippingCost($userId, $weight, $userCityId){
        $cost = 0;
        $srch = new SearchBase('tbl_shipping_settings');
        $srch->addCondition('ship_set_user_id', '=', $userId);
        $srch->addCondition('ship_set_city', '=', $userCityId);
        $rs = $srch->getResultSet();
        $result = FatApp::getDb()->fetchAll($rs);
        if($result){
            if($weight > 1){
                $extraWeight = $weight - 1;
                $cost = $result[0]['cost_for_1st_kg']; 
                $cost = $cost + $extraWeight*$result[0]['each_additional_kg']; 
            }else{
                $cost = $result[0]['cost_for_1st_kg'];
            }
        }
        return  $cost;
    }

    public function overRideCostCalculate($userId, $productId, $userCityId, $qty){
        $cost = 0;
        $srch = new SearchBase('tbl_product_shipping_rates');
        $srch->addCondition('pship_user_id', '=', $userId);
        $srch->addCondition('pship_city', '=', $userCityId);
        $srch->addCondition('pship_prod_id', '=', $productId);
        $rs = $srch->getResultSet();
        $result = FatApp::getDb()->fetchAll($rs);
        if($result){
            $cost = $result[0]['pship_charges']; 
            if($qty > 1){
                $cost += (($qty - 1) * $result[0]['pship_additional_charges']); 
            }
        }
        return $cost;
    }

    public function getCouponDiscounts()
    {
        $couponObj = new DiscountCoupons();
        if (!self::getCartDiscountCoupon()) {
            return false;
        }

        $orderId = isset($_SESSION['order_id'])?$_SESSION['order_id']:'';
        $couponInfo = $couponObj->getValidCoupons($this->cart_user_id, $this->cart_lang_id, self::getCartDiscountCoupon(), $orderId);
        //$couponInfo = $couponObj->getCoupon( self::getCartDiscountCoupon(), $this->cart_lang_id );
        //CommonHelper::printArray($couponInfo); die();
        $cartSubTotal = self::getSubTotal();

        /* $var = false;
        if(count($var>0)){
            echo 'its false';
        } else { echo 'not false'; }
        die(); */
        $couponData = array();

        if ($couponInfo) {
            $discountTotal = 0;
            $cartProducts = $this->getProducts($this->cart_lang_id);
            $prodObj = new Product();

            /* binded product_ids are not in array, are in string, so converting the same to array[ */
            if (!empty($couponInfo['grouped_coupon_products'])) {
                $couponInfo['grouped_coupon_products'] = explode(",", $couponInfo['grouped_coupon_products']);
            }

            if (!empty($couponInfo['grouped_coupon_categories'])) {
                $couponInfo['grouped_coupon_categories'] = explode(",", $couponInfo['grouped_coupon_categories']);
                if (!is_array($couponInfo['grouped_coupon_categories'])) {
                    return array();
                }

                $productIdsArr = array();

                foreach ($cartProducts as $cartProduct) {
                    $cartProdCategoriesArr = $prodObj->getProductCategories($cartProduct['product_id']);
                    if ($cartProdCategoriesArr == false || empty($cartProdCategoriesArr)) {
                        continue;
                    }

                    foreach ($cartProdCategoriesArr as $cartProdCategory) {
                        if (in_array($cartProdCategory['prodcat_id'], $couponInfo['grouped_coupon_categories'])) {
                            $productIdsArr[] = $cartProduct['product_id'];
                        }
                    }
                }

                if (!empty($productIdsArr)) {
                    if (empty($couponInfo['grouped_coupon_products']) || $this->cart_user_id == $couponInfo['grouped_coupon_users']) {
                        $couponInfo['grouped_coupon_products'] = $productIdsArr;
                    } else {
                        $couponInfo['grouped_coupon_products'] = array_merge($couponInfo['grouped_coupon_products'], $productIdsArr);
                    }
                }
            }
            /* ] */

            if (empty($couponInfo['grouped_coupon_products'])|| $this->cart_user_id == $couponInfo['grouped_coupon_users']) {
                $subTotal = $cartSubTotal;
            } else {
                $subTotal = 0;
                foreach ($cartProducts as $cartProduct) {
                    if ($cartProduct['is_batch']) {
                        /* if ( in_array($product['prodgroup_id'], $couponInfo['groups']) ){
                            $subTotal += $product['prodgroup_total'];
                        } */
                    } else {
                        if (in_array($cartProduct['product_id'], $couponInfo['grouped_coupon_products'])) {
                            $subTotal += $cartProduct['total'];
                        }
                    }
                }
            }

            if ($couponInfo['coupon_discount_in_percent'] == applicationConstants::FLAT) {
                $couponInfo['coupon_discount_value'] = min($couponInfo['coupon_discount_value'], $subTotal);
            }

            $cartVolumeDiscount = 0;
            foreach ($cartProducts as $cartProduct) {
                $discount = 0;
                $cartVolumeDiscount += $cartProduct['volume_discount_total'];
                if (empty($couponInfo['grouped_coupon_products']) || $this->cart_user_id == $couponInfo['grouped_coupon_users']) {
                    $status = true;
                } else {
                    if ($cartProduct['is_batch']) {
                        /* if (in_array($cartProduct['prodgroup_id'], $couponInfo['groups'])) {
                            $status = true;
                        } else {
                            $status = false;
                        } */
                    } else {
                        if (in_array($cartProduct['product_id'], $couponInfo['grouped_coupon_products'])) {
                            $status = true;
                        } else {
                            $status = false;
                        }
                    }
                }

                if ($status) {
                    if ($cartProduct['is_batch']) {
                        /* if (!$couponInfo['coupon_discount_in_percent']) {
                            $discount = $couponInfo['coupon_discount_value'] * ($cartProduct['prodgroup_total'] / $subTotal);
                        }else{
                            $discount = ( $cartProduct['prodgroup_total'] / 100 ) * $couponInfo['coupon_discount_value'];
                        } */
                    } else {
                        if ($couponInfo['coupon_discount_in_percent'] == applicationConstants::FLAT) {
                            $discount = $couponInfo['coupon_discount_value'] * (($cartProduct['total'] - $cartProduct['volume_discount_total']) / $subTotal);
                        } else {
                            $discount = (($cartProduct['total'] - $cartProduct['volume_discount_total']) / 100) * $couponInfo['coupon_discount_value'];
                        }
                    }
                }
                $discountTotal += $discount;
            }

            if ($discountTotal > $couponInfo['coupon_max_discount_value'] && $couponInfo['coupon_discount_in_percent'] == applicationConstants::PERCENTAGE) {
                $discountTotal = $couponInfo['coupon_max_discount_value'];
            }

            $selProdDiscountTotal = 0;
            $discountTypeArr = DiscountCoupons::getTypeArr($this->cart_lang_id);

            /*[ Calculate discounts for each Seller Products*/
            $discountedSelProdIds = array();
            $discountedProdGroupIds = array();
            if (empty($couponInfo['grouped_coupon_products']) || $this->cart_user_id == $couponInfo['grouped_coupon_users']) {
                foreach ($cartProducts as $cartProduct) {
                    if ($cartProduct['is_batch']) {
                        /* $totalSelProdDiscount = round(($discountTotal*$cartProduct['prodgroup_total'])/$subTotal,2);
                        $selProdDiscountTotal += $totalSelProdDiscount;
                        $discountedProdGroupIds[$cartProduct['prodgroup_id']] = round($totalSelProdDiscount,2); */
                    } else {
                        $totalSelProdDiscount = round(($discountTotal*($cartProduct['total'] - $cartProduct['volume_discount_total']))/($subTotal-$cartVolumeDiscount), 2);
                        $selProdDiscountTotal += $totalSelProdDiscount;
                        $discountedSelProdIds[$cartProduct['selprod_id']] = round($totalSelProdDiscount, 2);
                    }
                }
            } else {
                foreach ($cartProducts as $cartProduct) {
                    if ($cartProduct['is_batch']) {
                        /* if (in_array($cartProduct['prodgroup_id'], $couponInfo['groups'])) {
                            $totalSelProdDiscount = round(($discountTotal*$cartProduct['prodgroup_total'])/$subTotal,2);
                            $selProdDiscountTotal += $totalSelProdDiscount;
                            $discountedProdGroupIds[$cartProduct['prodgroup_id']] = round($totalSelProdDiscount,2);
                        } */
                    } else {
                        if (in_array($cartProduct['product_id'], $couponInfo['grouped_coupon_products'])) {
                            $totalSelProdDiscount = round(($discountTotal*($cartProduct['total'] - $cartProduct['volume_discount_total']))/($subTotal-$cartVolumeDiscount), 2);
                            $selProdDiscountTotal += $totalSelProdDiscount;
                            $discountedSelProdIds[$cartProduct['selprod_id']] = round($totalSelProdDiscount, 2);
                        }
                    }
                }
            }
            /*]*/
            $selProdDiscountTotal = $selProdDiscountTotal /*- $cartVolumeDiscount*/;
            $labelArr = array(
                'coupon_label'=>$couponInfo["coupon_title"],
                'coupon_id'=>$couponInfo["coupon_id"],
                'coupon_discount_in_percent'=>$couponInfo["coupon_discount_in_percent"],
                'max_discount_value' =>$couponInfo["coupon_max_discount_value"]
            );

            if ($couponInfo['coupon_discount_in_percent'] == applicationConstants::PERCENTAGE) {
                if ($selProdDiscountTotal > $couponInfo['coupon_max_discount_value']) {
                    $selProdDiscountTotal = $couponInfo['coupon_max_discount_value'];
                }
            } elseif ($couponInfo['coupon_discount_in_percent'] == applicationConstants::FLAT) {
                if ($selProdDiscountTotal > $couponInfo["coupon_discount_value"]) {
                    $selProdDiscountTotal = $couponInfo["coupon_discount_value"];
                }
            }

            $couponData = array(
                'coupon_discount_type'       => $couponInfo["coupon_type"],
                'coupon_code' => $couponInfo["coupon_code"],
                'coupon_discount_value'      =>$couponInfo["coupon_discount_value"],
                'coupon_discount_total'      => ($selProdDiscountTotal < 0)?0:$selProdDiscountTotal,
                'coupon_info'      => json_encode($labelArr),
                'discountedSelProdIds'=>$discountedSelProdIds,
                'discountedProdGroupIds'=>$discountedProdGroupIds,
            );
        }

        /* Existing old functionality[ */
        // if( $couponInfo ){
        // $discountTotal = 0;

        // if (empty($couponInfo['products']) && empty($couponInfo['groups'])) {
        // $subTotal = $cartSubTotal;
        // }else{
        // $subTotal = 0;
        // foreach ($this->getProducts($this->cart_lang_id) as $product) {
        // if($product['is_batch']){
        // if (in_array($product['prodgroup_id'], $couponInfo['groups'])){
        // $subTotal += $product['prodgroup_total'];
        // }
        // }else{
        // if (in_array($product['product_id'], $couponInfo['products'])){
        // $subTotal += $product['total'];
        // }
        // }
        // }
        // }

        // if ( !$couponInfo['coupon_discount_in_percent'] ) {
        // $couponInfo['coupon_discount_value'] = min($couponInfo['coupon_discount_value'], $subTotal);
        // }

        // foreach ($this->getProducts($this->cart_lang_id) as $product) {
        // $discount = 0;

        // if (empty($couponInfo['products']) && empty($couponInfo['groups'])) {
        // $status = true;
        // }else{
        // if($product['is_batch']){
        // if (in_array($product['prodgroup_id'], $couponInfo['groups'])) {
        // $status = true;
        // } else {
        // $status = false;
        // }
        // }else{
        // if (in_array($product['product_id'], $couponInfo['products'])) {
        // $status = true;
        // } else {
        // $status = false;
        // }
        // }
        // }


        // if ($status) {
        // if($product['is_batch']){
        // if (!$couponInfo['coupon_discount_in_percent']) {
        // $discount = $couponInfo['coupon_discount_value'] * ($product['prodgroup_total'] / $subTotal);
        // }else{
        // $discount = ( $product['prodgroup_total'] / 100 ) * $couponInfo['coupon_discount_value'];
        // }
        // }else{
        // if (!$couponInfo['coupon_discount_in_percent']) {
        // $discount = $couponInfo['coupon_discount_value'] * ($product['total'] / $subTotal);
        // }else{
        // $discount = ( $product['total'] / 100 ) * $couponInfo['coupon_discount_value'];
        // }
        // }
        // }
        // $discountTotal += $discount;
        // }

        // // If discount greater than total
        // if ($discountTotal > $couponInfo['coupon_max_discount_value']) {
        // $discountTotal = $couponInfo['coupon_max_discount_value'];
        // }

        // $selProdDiscountTotal = 0;
        // $discountTypeArr = DiscountCoupons::getTypeArr($this->cart_lang_id);

        // /*[ Calculate discounts for each Seller Products*/
        // $discountedSelProdIds = array();
        // $discountedProdGroupIds = array();
        // if (empty($couponInfo['products'])&& empty($couponInfo['groups'])) {
        // foreach ($this->getProducts($this->cart_lang_id) as $product) {
        // if($product['is_batch']){
        // $totalSelProdDiscount = round(($discountTotal*$product['prodgroup_total'])/$subTotal,2);
        // $selProdDiscountTotal += $totalSelProdDiscount;
        // $discountedProdGroupIds[$product['prodgroup_id']] = round($totalSelProdDiscount,2);
        // }else{
        // $totalSelProdDiscount = round(($discountTotal*$product['total'])/$subTotal,2);
        // $selProdDiscountTotal += $totalSelProdDiscount;
        // $discountedSelProdIds[$product['selprod_id']] = round($totalSelProdDiscount,2);
        // }
        // }
        // }else{
        // foreach ($this->getProducts($this->cart_lang_id) as $product) {
        // if($product['is_batch']){
        // if (in_array($product['prodgroup_id'], $couponInfo['groups'])) {
        // $totalSelProdDiscount = round(($discountTotal*$product['prodgroup_total'])/$subTotal,2);
        // $selProdDiscountTotal += $totalSelProdDiscount;
        // $discountedProdGroupIds[$product['prodgroup_id']] = round($totalSelProdDiscount,2);
        // }
        // }else{
        // if (in_array($product['product_id'], $couponInfo['products'])) {
        // $totalSelProdDiscount = round(($discountTotal*$product['total'])/$subTotal,2);
        // $selProdDiscountTotal += $totalSelProdDiscount;
        // $discountedSelProdIds[$product['selprod_id']] = round($totalSelProdDiscount,2);
        // }
        // }
        // }
        // }
        // /*]*/

        // $labelArr = array(
        // 'coupon_label'=>$couponInfo["coupon_title"],
        // 'coupon_discount_in_percent'=>$couponInfo["coupon_discount_in_percent"],
        // 'max_discount_value' =>$couponInfo["coupon_max_discount_value"]
        // );

        // $couponData = array(
        // 'coupon_discount_type'       => $couponInfo["coupon_type"],
        // 'coupon_code' => $couponInfo["coupon_code"],
        // 'coupon_discount_value'      =>$couponInfo["coupon_discount_value"],
        // 'coupon_discount_total'      => $selProdDiscountTotal,
        // 'coupon_info'      => json_encode($labelArr),
        // 'discountedSelProdIds'=>$discountedSelProdIds,
        // 'discountedProdGroupIds'=>$discountedProdGroupIds,
        // );
        // }
        /* ] */

        if (empty($couponData)) {
            return false;
        }
        return $couponData;
    }

    public function updateCartWalletOption($val)
    {
        $this->SYSTEM_ARR['shopping_cart']['Pay_from_wallet'] = $val;
        $this->updateUserCart();
        return true;
    }

    public function updateCartDiscountCoupon($val)
    {
        $this->SYSTEM_ARR['shopping_cart']['discount_coupon'] = $val;
        $this->updateUserCart();
        return true;
    }

    public function removeCartDiscountCoupon()
    {
        $couponCode = array_key_exists('discount_coupon', $this->SYSTEM_ARR['shopping_cart']) ? $this->SYSTEM_ARR['shopping_cart']['discount_coupon'] : '';
        unset($this->SYSTEM_ARR['shopping_cart']['discount_coupon']);

        /* Removing from temp hold[ */
        if ((UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) && $couponCode != '') {
            $loggedUserId = UserAuthentication::getLoggedUserId();

            $srch = DiscountCoupons::getSearchObject(0, false, false);
            $srch->addCondition('coupon_code', '=', $couponCode);
            $srch->setPageSize(1);
            $srch->addMultipleFields(array('coupon_id'));
            $rs = $srch->getResultSet();
            $couponRow = FatApp::getDb()->fetch($rs);

            if ($couponRow && $loggedUserId) {
                FatApp::getDb()->deleteRecords(DiscountCoupons::DB_TBL_COUPON_HOLD, array( 'smt' => 'couponhold_coupon_id = ? AND couponhold_user_id = ?', 'vals'=> array( $couponRow['coupon_id'], $loggedUserId ) ));
            }
        }

        $orderId = isset($_SESSION['order_id'])?$_SESSION['order_id']:'';
        if ($orderId != '') {
            FatApp::getDb()->deleteRecords(DiscountCoupons::DB_TBL_COUPON_HOLD_PENDING_ORDER, array( 'smt' => 'ochold_order_id = ?', 'vals'=> array( $orderId ) ));
        }

        /* ] */

        $this->updateUserCart();
        return true;
    }

    public function updateCartUseRewardPoints($val)
    {
        $this->SYSTEM_ARR['shopping_cart']['reward_points'] = $val;
        $this->updateUserCart();
        return true;
    }

    public function removeUsedRewardPoints()
    {
        if (isset($this->SYSTEM_ARR['shopping_cart']) && array_key_exists('reward_points', $this->SYSTEM_ARR['shopping_cart'])) {
            unset($this->SYSTEM_ARR['shopping_cart']['reward_points']);
            $this->updateUserCart();
        }
        return true;
    }

    public function getCartRewardPoint()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['reward_points'])?$this->SYSTEM_ARR['shopping_cart']['reward_points']:0;
    }

    public function getCartDiscountCoupon()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['discount_coupon']) ? $this->SYSTEM_ARR['shopping_cart']['discount_coupon'] : '';
    }

    public function isDiscountCouponSet()
    {
        return !empty($this->SYSTEM_ARR['shopping_cart']['discount_coupon']);
    }

    public function isCartUserWalletSelected()
    {
        return (isset($this->SYSTEM_ARR['shopping_cart']['Pay_from_wallet']) && intval($this->SYSTEM_ARR['shopping_cart']['Pay_from_wallet'])==1) ? 1: 0;
    }

    public function updateUserCart()
    {
        if (isset($this->cart_user_id)) {
            $record = new TableRecord('tbl_user_cart');
            $cart_arr = $this->SYSTEM_ARR['cart'];
           
            if (isset($this->SYSTEM_ARR['shopping_cart']) && is_array($this->SYSTEM_ARR['shopping_cart']) && (!empty($this->SYSTEM_ARR['shopping_cart']))) {
                $cart_arr["shopping_cart"] = $this->SYSTEM_ARR['shopping_cart'];
            }
            $cart_arr = serialize($cart_arr);
            $record->assignValues(array("usercart_user_id" => $this->cart_user_id,"usercart_type" =>CART::TYPE_PRODUCT, "usercart_details" => $cart_arr, "usercart_added_date" => date('Y-m-d H:i:s'),"usercart_last_used_date" => date('Y-m-d H:i:s'),"usercart_last_session_id" => $this->cart_id ));
            if (!$record->addNew(array(), array( 'usercart_details' => $cart_arr, "usercart_added_date" => date('Y-m-d H:i:s'),"usercart_last_used_date" => date('Y-m-d H:i:s'),"usercart_last_session_id" => $this->cart_id, "usercart_sent_reminder" => 0 ))) {
                Message::addErrorMessage($record->getError());
                throw new Exception('');
            }
        }
    }

    /* to keep track of temporary hold the product stock[ */
    public function updateTempStockHold($selprod_id, $quantity = 0, $prodgroup_id = 0)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $quantity = FatUtility::int($quantity);
        $prodgroup_id = FatUtility::int($prodgroup_id);
        if (!$selprod_id) {
            return;
        }
        $db = FatApp::getDb();

        if ($quantity <= 0) {
            $db->deleteRecords('tbl_product_stock_hold', array('smt'=>'pshold_selprod_id = ? AND pshold_user_id = ? AND pshold_prodgroup_id = ?', 'vals' => array($selprod_id, $this->cart_user_id, $prodgroup_id)));
            return;
        }

        //$record = new TableRecord('tbl_product_stock_hold');
        $dataArrToSave = array(
            'pshold_selprod_id' => $selprod_id,
            'pshold_user_id' => $this->cart_user_id,
            'pshold_prodgroup_id' => $prodgroup_id,
            'pshold_selprod_stock' => $quantity,
            'pshold_added_on' => date('Y-m-d H:i:s')
        );

        //$qty = isset($this->SYSTEM_ARR['cart'][$selprod_id]) ? FatUtility::int($this->SYSTEM_ARR['cart'][$selprod_id]) : 0;
        $dataUpdateOnDuplicate = array_merge($dataArrToSave, array( 'pshold_selprod_stock' => $quantity));
        if (!$db->insertFromArray('tbl_product_stock_hold', $dataArrToSave, true, array(), $dataUpdateOnDuplicate)) {
            Message::addErrorMessage($db->getError());
            throw new Exception('');
        }
        /* $record->assignValues( $dataArrToSave );
        if( !$record->addNew( array(), $dataUpdateOnDuplicate ) ){
            Message::addErrorMessage( $record->getError() );
            throw new Exception('');
        } */

        /* delete old records[ */
        $intervalInMinutes = FatApp::getConfig('cart_stock_hold_minutes', FatUtility::VAR_INT, 15);
        $deleteQuery = "DELETE FROM tbl_product_stock_hold WHERE pshold_added_on < DATE_SUB(NOW(), INTERVAL ". $intervalInMinutes ." MINUTE)";
        $db->query($deleteQuery);
        /* ] */
    }
    /* ] */

    public function clear()
    {
        $this->products = array();
        $this->SYSTEM_ARR['cart'] = array();
        $this->SYSTEM_ARR['shopping_cart'] = array();
        unset($_SESSION['shopping_cart']["order_id"]);
        unset($_SESSION['wallet_recharge_cart']["order_id"]);
    }

    public static function setCartAttributes($userId = 0, $tempUserId = 0)
    {
        $db = FatApp::getDb();

        $cart_user_id = static::getCartUserId($tempUserId);

        if (empty($tempUserId)) {
            $tempUserId = session_id();
        }

        /* to keep track of temporary hold the product stock[ */
        $db->updateFromArray('tbl_product_stock_hold', array( 'pshold_user_id' => $cart_user_id ), array('smt' => 'pshold_user_id = ?', 'vals' => array($tempUserId) ));
        /* ] */

        $userId = FatUtility::int($userId);
        if ($userId == 0 && $tempUserId == 0) {
            return false;
        }

        $srch = new SearchBase('tbl_user_cart');
        $srch->addCondition('usercart_user_id', '=', $tempUserId);
        $srch->addCondition('usercart_type', '=', CART::TYPE_PRODUCT);
        $rs = $srch->getResultSet();

        if (!$row = FatApp::getDb()->fetch($rs)) {
            return false;
        }

        $cartInfo = unserialize($row["usercart_details"]);

        $cartObj = new Cart($userId, 0, $tempUserId);

        foreach ($cartInfo as $key => $quantity) {
            $keyDecoded = unserialize(base64_decode($key));

            $selprod_id = 0;
            $prodgroup_id = 0;

            if (strpos($keyDecoded, static::CART_KEY_PREFIX_PRODUCT) !== false) {
                $selprod_id = FatUtility::int(str_replace(static::CART_KEY_PREFIX_PRODUCT, '', $keyDecoded));
            }

            if (strpos($keyDecoded, static::CART_KEY_PREFIX_BATCH) !== false) {
                $prodgroup_id = FatUtility::int(str_replace(static::CART_KEY_PREFIX_BATCH, '', $keyDecoded));
            }

            $cartObj->add($selprod_id, $quantity, $prodgroup_id);

            $db->deleteRecords('tbl_user_cart', array('smt'=>'`usercart_user_id`=? and usercart_type=?', 'vals'=>array($tempUserId,CART::TYPE_PRODUCT)));
        }
        $cartObj->updateUserCart();
    }

    public function shipStationCarrierList()
    {
        $carrierList = array();
        $carrierList[0] =  Labels::getLabel('MSG_Select_Services', commonHelper::getLangId());
        try {
            require_once(CONF_INSTALLATION_PATH . 'library/APIs/shipstatation/ship.class.php');
            $carriers = null;
            //$carriers = $this->getCache("shipstationcarriers");
            if (!$carriers) {
                $apiKeys = ShippingMethods::getShipStationApiKeys(ShippingMethods::SHIPSTATION_SHIPPING);
                $ship = new Ship($apiKeys['shipstation_api_key'], $apiKeys['shipstation_api_secret_key']);
                $carriers = $ship->getCarriers();
                // $this->setCache("shipstationcarriers", $carriers);
            }

            foreach ($carriers as $carrier) {
                $code = $carrier->code;
                $name = $carrier->name;
                $carrierList[$code] = $name;
            }
        } catch (Exception $ex) {
            // $carriers = new stdClass();
        }
        return $carrierList;
    }

    public function getCache($key)
    {
        require_once(CONF_INSTALLATION_PATH . 'library/phpfastcache.php');
        phpFastCache::setup("storage", "files");

        phpFastCache::setup("path", CONF_UPLOADS_PATH . "caching");

        $cache = phpFastCache();
        return $cache->get($key);
    }

    private function setCache($key, $value)
    {
        require_once(CONF_INSTALLATION_PATH . 'library/phpfastcache.php');
        phpFastCache::setup("storage", "files");
        phpFastCache::setup("path", CONF_UPLOADS_PATH . "caching");
        $cache = phpFastCache();
        return $cache->set($key, $value, 60 * 60);
    }

    public function getCarrierShipmentServicesList($cartKey, $carrier_id = 0, $lang_id =0)
    {
        $products = $this->getProducts($this->cart_lang_id);
        $prodKey = $this->getProductByKey($cartKey);

        $services = $this->getCarrierShipmentServices($cartKey, $carrier_id, $lang_id);
        $servicesList = array();

        $servicesList[0] = Labels::getLabel('MSG_Select_Services', $lang_id);

        if (!empty($carrier_id)) {
            foreach ($services as $key => $value) {
                $code = $value->serviceCode;
                $price = $value->shipmentCost + $value->otherCost;
                $name = $value->serviceName;
                if ($products[$prodKey]['shop_eligible_for_free_shipping'] > 0 && $products[$prodKey]['psbs_user_id'] > 0) {
                    /* $displayPrice = CommonHelper::displayMoneyFormat(0); */
                    $displayPrice = Labels::getLabel('LBL_Free_Shipping', $lang_id);
                } else {
                    $displayPrice = CommonHelper::displayMoneyFormat($price);
                }
                $label = $name . " (" . $displayPrice . " )";
                $servicesList[$code . "-" . $price] = $label;
            }
        }

        return $servicesList;
    }

    public function getProductByKey($find_key)
    {
        if (!$this->hasPhysicalProduct()) {
            return false;
        }

        foreach ($this->SYSTEM_ARR['cart'] as $key => $cart) {
            if ($find_key == md5($key)) {
                return $key;
            }
        }
        return false;
    }

    public function getCarrierShipmentServices($product_key, $carrier_id, $lang_id)
    {
        if (!$key = $this->getProductByKey($product_key)) {
            return array();
        }

        $products = $this->getProducts($this->cart_lang_id);
        $weightUnitsArr = applicationConstants::getWeightUnitsArr($lang_id);
        $lengthUnitsArr = applicationConstants::getLengthUnitsArr($lang_id);


        $product = $products[$key];
        $productShippingAddress = $product['shipping_address'];
        $productShopAddress = $product['seller_address'];

        $sellerPinCode = $productShopAddress['shop_postalcode'];
        $quantity = $product['quantity'];
        $productWeight = $product['product_weight'] / $quantity;
        $productWeightClass = ($product['product_dimension_unit']) ? $lengthUnitsArr[$product['product_dimension_unit']] : '';
        $productLength = $product['product_length'];
        $productWidth = $product['product_width'];
        $productHeight = $product['product_height'];

        $productLengthUnit = ($product['product_weight_unit']) ? $weightUnitsArr[$product['product_weight_unit']] : '';



        $productWeightInOunce = $this->convertWeightInOunce($productWeight, $productWeightClass);
        $productLengthInCenti = $this->convertLengthInCenti($productLength, $productLengthUnit);
        $productWidthInCenti = $this->convertLengthInCenti($productWidth, $productLengthUnit);
        $productHeightInCenti = $this->convertLengthInCenti($productHeight, $productLengthUnit);

        $product_rates = array();
        try {
            require_once(CONF_INSTALLATION_PATH . 'library/APIs/shipstatation/ship.class.php');
            $apiKeys = ShippingMethods::getShipStationApiKeys(ShippingMethods::SHIPSTATION_SHIPPING);
            $Ship = new Ship($apiKeys['shipstation_api_key'], $apiKeys['shipstation_api_secret_key']);

            $Ship->setProductDeliveryAddress($productShippingAddress['state_code'], $productShippingAddress['country_code'], $productShippingAddress['ua_city'], $productShippingAddress['ua_zip']);

            $Ship->setProductWeight($productWeightInOunce);
            // $Ship->setProductDim($productLengthInCenti,$productWidthInCenti,$productHeightInCenti);
            if ($productLengthInCenti > 0 && $productWidthInCenti > 0 && $productHeightInCenti > 0) {
                $Ship->setProductDim($productLengthInCenti, $productWidthInCenti, $productHeightInCenti);
            }

            $product_rates = (array) $Ship->getProductShippingRates($carrier_id, $sellerPinCode, $Ship->getProductWeight(), $Ship->getProductDeliveryAddress(), $Ship->getProductDim());
        } catch (Exception $ex) {
            $ex->getMessage();
            return array();
        }

        return $product_rates;
    }

    public function convertWeightInOunce($productWeight, $productWeightClass)
    {
        $coversionRate = 1;
        switch ($productWeightClass) {
            case "KG":
                $coversionRate = "35.274";
                break;
            case "GM":
                $coversionRate = "0.035274";
                break;
            case "PN":
                $coversionRate = "16";
                break;
            case "OU":
                $coversionRate = "1";
                break;
            case "Ltr":
                $coversionRate = "33.814";
                break;
            case "Ml":
                $coversionRate = "0.033814";
                break;
        }

        return $productWeight * $coversionRate;
    }

    public function convertLengthInCenti($productWeight, $productWeightClass)
    {
        $coversionRate = 1;
        switch ($productWeightClass) {
            case "IN":
                $coversionRate = "2.54";
                break;
            case "MM":
                $coversionRate = "0.1";
                break;
            case "CM":
                $coversionRate = "1";
                break;
        }

        return $productWeight * $coversionRate;
    }

    public function getSellersProductItemsPrice($cartProducts)
    {
        $sellerPrice = array();
        if (is_array($cartProducts) && count($cartProducts)) {
            foreach ($cartProducts as $selprod) {
                $shipBy = 0;
                if (!empty($selprod['psbs_user_id'])) {
                    $shipBy = $selprod['psbs_user_id'];
                }

                if (!empty($selprod['selprod_user_id']) && (!array_key_exists($selprod['selprod_user_id'], $sellerPrice) || $shipBy == 0)) {
                    $sellerPrice[$selprod['selprod_user_id']]['totalPrice'] = 0;
                }

                if ($shipBy) {
                    $sellerPrice[$selprod['selprod_user_id']]['totalPrice']+= $selprod['theprice'] * $selprod['quantity'];
                }
            }
        }
        return $sellerPrice;
    }

    public function getSelprodIdByKey($key)
    {
        $keyDecoded = unserialize(base64_decode($key));
        if (strpos($keyDecoded, static::CART_KEY_PREFIX_PRODUCT) !== false) {
            $selprod_id = FatUtility::int(str_replace(static::CART_KEY_PREFIX_PRODUCT, '', $keyDecoded));
            return $selprod_id;
        }
    }
}
