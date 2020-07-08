<?php
class CommonHelper extends FatUtility
{
    private static $_ip;
    private static $_user_agent;
    private static $_lang_id;
    private static $_layout_direction;
    private static $_currency_id;
    private static $_currency_symbol_left;
    private static $_currency_symbol_right;
    private static $_currency_code;
    private static $_currency_value;
    private static $_default_currency_symbol_left;
    private static $_default_currency_symbol_right;
    private static $_appToken;

    public static function initCommonVariables($isAdmin = false)
    {
        self::$_ip = self::getClientIp();
        self::$_user_agent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
        self::$_lang_id = FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1);
        self::$_currency_id = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);

        if (!$isAdmin) {
            if (true ===  MOBILE_APP_API_CALL) {
                if (!empty($_SERVER['HTTP_X_LANGUAGE_ID'])) {
                    self::$_lang_id = FatUtility::int($_SERVER['HTTP_X_LANGUAGE_ID']);
                }

                if (!empty($_SERVER['HTTP_X_CURRENCY_ID'])) {
                    self::$_currency_id = FatUtility::int($_SERVER['HTTP_X_CURRENCY_ID']);
                }
            } else {
                if (isset($_COOKIE['defaultSiteLang'])) {
                    $languages = Language::getAllNames();
                    if (array_key_exists($_COOKIE['defaultSiteLang'], $languages)) {
                        self::$_lang_id = FatUtility::int(trim($_COOKIE['defaultSiteLang']));
                    }
                }

                if (isset($_COOKIE['defaultSiteCurrency'])) {
                    $currencies = Currency::getCurrencyAssoc(self::$_lang_id);
                    if (array_key_exists($_COOKIE['defaultSiteCurrency'], $currencies)) {
                        self::$_currency_id = FatUtility::int(trim($_COOKIE['defaultSiteCurrency']));
                    }
                }
            }

            if (true ===  MOBILE_APP_API_CALL && array_key_exists('HTTP_X_TOKEN', $_SERVER) && !empty($_SERVER['HTTP_X_TOKEN'])) {
                self::$_appToken = ($_SERVER['HTTP_X_TOKEN'] != '')?$_SERVER['HTTP_X_TOKEN']:'';
            }
        } else {
            if (isset($_COOKIE['defaultAdminSiteLang'])) {
                $languages = Language::getAllNames();
                if (array_key_exists($_COOKIE['defaultAdminSiteLang'], $languages)) {
                    self::$_lang_id = FatUtility::int(trim($_COOKIE['defaultAdminSiteLang']));
                }
            } else {
                self::$_lang_id = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
            }
        }

        $currencyData = Currency::getAttributesById(
            self::$_currency_id,
            array('currency_code','currency_symbol_left','currency_symbol_right','currency_value')
        );

        self::$_currency_symbol_left = $currencyData['currency_symbol_left'];
        self::$_currency_symbol_right = $currencyData['currency_symbol_right'];
        self::$_currency_code = $currencyData['currency_code'];
        self::$_currency_value = $currencyData['currency_value'];
        self::$_layout_direction = Language::getLayoutDirection(self::$_lang_id);
    }

    public static function getAppToken()
    {
        return self::$_appToken;
    }

    public static function getLangId()
    {
        return self::$_lang_id;
    }

    public static function getLayoutDirection()
    {
        return self::$_layout_direction;
    }

    public static function getCurrencyId()
    {
        return self::$_currency_id;
    }

    public static function getCurrencySymbolLeft()
    {
        return self::$_currency_symbol_left;
    }

    public static function getCurrencySymbolRight()
    {
        return self::$_currency_symbol_right;
    }

    public static function getCurrencyCode()
    {
        return self::$_currency_code;
    }

    public static function getCurrencyValue()
    {
        return self::$_currency_value;
    }

    public static function userIp()
    {
        return self::$_ip;
    }

    public static function userAgent()
    {
        return self::$_user_agent;
    }

    public static function getClientIp()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
        } elseif (getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }

    public static function getUserIdFromCookies()
    {
        $userId = 0;
        if (isset($_COOKIE['uc_id'])) {
            $userId = $_COOKIE['uc_id'];
        }
        return $userId;
    }
    /* public static function encryptPassword($pwd) {
        return md5(PASSWORD_SALT . $pwd . PASSWORD_SALT);
    } */

    public static function canAvailShippingChargesBySeller($opSellerId = 0, $shippedByUserId = 0)
    {
        /* if(FatApp::getConfig('CONF_SHIPPED_BY_ADMIN',FatUtility::VAR_INT,0)){
            return false;
        } */

        $opSellerId = FatUtility::int($opSellerId);
        $shippedByUserId = FatUtility::int($shippedByUserId);
        if ($opSellerId > 0 && $opSellerId == $shippedByUserId) {
            return true;
        }

        return false;
    }


    public static function generateUrl($controller = '', $action = '', $queryData = array(), $use_root_url = '', $url_rewriting = null, $encodeUrl = false, $getOriginalUrl = false)
    {
        if (self::isThemePreview()) {
            array_push($queryData, '?theme-preview');
        }
        $url = FatUtility::generateUrl($controller, $action, $queryData, $use_root_url, $url_rewriting);

        if ($getOriginalUrl) {
            return $url;
        }

        if (!$use_root_url) {
            $use_root_url = CONF_WEBROOT_URL;
        }
        /* $urlString = FatUtility::camel2dashed($controller);
        $urlString.= '/'.FatUtility::camel2dashed($action);
        $urlString.= '/'.implode('/',$queryData);
        $urlString = trim($urlString,'/'); */
        $urlString = trim(ltrim($url, CONF_WEBROOT_FRONTEND), '/');
        $srch = UrlRewrite::getSearchObject();
        $srch->addMultipleFields(array('urlrewrite_custom'));
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addCondition(UrlRewrite::DB_TBL_PREFIX . 'original', 'LIKE', $urlString);
        $rs = $srch->getResultSet();
        if ($row = FatApp::getDb()->fetch($rs)) {
            if ($encodeUrl) {
                $url = $use_root_url.urlencode($row['urlrewrite_custom']);
            } else {
                $url = $use_root_url.$row['urlrewrite_custom'];
            }
        }
        return $url;
    }

    public static function generateFullUrl($controller = '', $action = '', $queryData = array(), $use_root_url = '', $url_rewriting = null, $encodeUrl = false)
    {
        $url = static::generateUrl($controller, $action, $queryData, $use_root_url, $url_rewriting);
        $protocol = (FatApp::getConfig('CONF_USE_SSL')==1)?'https://':'http://';
        if ($encodeUrl) {
            $url = urlencode($url);
        }
        return $protocol . $_SERVER['SERVER_NAME'] . $url;
    }

    public static function generateNoAuthUrl($model = '', $action = '', $queryData = array(), $use_root_url = '')
    {
        $url = CommonHelper::generateUrl($model, $action, $queryData, $use_root_url, false);
        $url = str_replace('index.php?', 'index_noauth.php?', $url);
        $protocol = (FatApp::getConfig('CONF_USE_SSL')==1)?'https://':'http://';
        return $protocol . $_SERVER['SERVER_NAME'] . $url;
    }

    public static function underMyDevelopment($sessionId = false)
    {
        if ($sessionId && $sessionId != session_id()) {
            return false;
        }
        return true;
    }

    public static function printArray($attr, $exit = false, $sessionId = false)
    {
        if ($sessionId && $sessionId != session_id()) {
            return;
        }

        echo 'IN PRINT Function: <pre>';
        print_r($attr);
        echo '</pre>';

        if ($exit) {
            exit;
        }
    }

    public static function combinationOfElementsOfArr($arr = array(), $useKey = '')
    {
        $tempArr = array();
        $loopCount = count($arr);

        for ($i=0; $i<$loopCount; $i++) {
            $count = 0;
            foreach ($arr as $key => $val) {
                if ($count != $i) {
                    continue;
                }
                asort($val[$useKey]);

                if (!empty($tempArr)) {
                    foreach ($tempArr as $tempKey => $tempVal) {
                        foreach ($val[$useKey] as $k => $v) {
                            $tempArr[$tempKey.'|'.$k] = $tempVal.'|'.$v;
                            unset($tempArr[$tempKey]);
                        }
                    }
                } else {
                    foreach ($val[$useKey] as $k => $v) {
                        $tempArr[$k] = $v;
                    }
                }
            }
            $count++;
        }
        return $tempArr;
    }

    public static function rewardPointDiscount($orderNetAmount, $rewardPoints)
    {
        return $rewardPointValues = min(static::convertRewardPointToCurrency($rewardPoints), $orderNetAmount);
        //return $rewardPoints = static::convertCurrencyToRewardPoint($rewardPointValues);
    }

    public static function orderProductAmount($opArr = array(), $amountType = 'netamount', $pricePerItem = false, $userType = false)
    {
        $amount = 0;

        if (empty($opArr)) {
            trigger_error('Order Product Array should not be empty', E_USER_ERROR);
        }

        $shippingAmount = isset($opArr['charges'][OrderProduct::CHARGE_TYPE_SHIPPING]['opcharge_amount']) ? $opArr['charges'][OrderProduct::CHARGE_TYPE_SHIPPING]['opcharge_amount'] : 0;
        $cartTotal = $opArr['op_qty'] * $opArr['op_unit_price'];

        switch (strtoupper($amountType)) {
            case 'NETAMOUNT':
                $amount = $cartTotal + $opArr['op_other_charges'] ;
                if ($userType == User::USER_TYPE_SELLER) {
                    if ($opArr['op_tax_collected_by_seller'] == 0) {
                        $tax = isset($opArr['charges'][OrderProduct::CHARGE_TYPE_TAX]['opcharge_amount'])?$opArr['charges'][OrderProduct::CHARGE_TYPE_TAX]['opcharge_amount']:0;
                        $amount = $amount - $tax;
                    }

                    if (!CommonHelper::canAvailShippingChargesBySeller($opArr['op_selprod_user_id'], $opArr['opshipping_by_seller_user_id'])) {
                        $shippingCharges = isset($opArr['charges'][OrderProduct::CHARGE_TYPE_SHIPPING]['opcharge_amount'])?$opArr['charges'][OrderProduct::CHARGE_TYPE_SHIPPING]['opcharge_amount']:0;
                        $amount = $amount - $shippingCharges;
                    }

                    $discount = isset($opArr['charges'][OrderProduct::CHARGE_TYPE_DISCOUNT]['opcharge_amount'])?abs($opArr['charges'][OrderProduct::CHARGE_TYPE_DISCOUNT]['opcharge_amount']):0;
                    $amount = $amount + $discount;

                    $rewardDiscount = isset($opArr['charges'][OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT]['opcharge_amount'])?abs($opArr['charges'][OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT]['opcharge_amount']):0;
                    $amount = $amount + $rewardDiscount;
                }
                break;
            case 'SHIPPING':
                $amount = $shippingAmount;
                break;
            case 'REWARDPOINT':
                $amount = isset($opArr['charges'][OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT]['opcharge_amount'])?$opArr['charges'][OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT]['opcharge_amount']:0;
                break;
            case 'DISCOUNT':
                $amount = isset($opArr['charges'][OrderProduct::CHARGE_TYPE_DISCOUNT]['opcharge_amount'])?$opArr['charges'][OrderProduct::CHARGE_TYPE_DISCOUNT]['opcharge_amount']:0;
                break;
            case 'CART_TOTAL':
                $amount = $cartTotal;
                break;
            case 'TAX':
                //$amount = FatUtility::convertToType($opArr['op_tax_total'] , FatUtility::VAR_FLOAT);
                $amount = isset($opArr['charges'][OrderProduct::CHARGE_TYPE_TAX]['opcharge_amount'])?$opArr['charges'][OrderProduct::CHARGE_TYPE_TAX]['opcharge_amount']:0;
                break;
            case 'VOLUME_DISCOUNT':
                $amount = isset($opArr['charges'][OrderProduct::CHARGE_TYPE_VOLUME_DISCOUNT]['opcharge_amount']) ? $opArr['charges'][OrderProduct::CHARGE_TYPE_VOLUME_DISCOUNT]['opcharge_amount'] : 0;
                break;
        }

        if ($pricePerItem) {
            $amount = round($amount/$opArr['op_qty'], 2);
        }
        return (float)$amount;
    }

    public static function getOrderProductRefundAmtArr($requestRow = array())
    {
        $volumeDiscount = isset($requestRow['charges'][OrderProduct::CHARGE_TYPE_VOLUME_DISCOUNT]['opcharge_amount'])?abs($requestRow['charges'][OrderProduct::CHARGE_TYPE_VOLUME_DISCOUNT]['opcharge_amount']):0;
        $shipCharges = isset($requestRow['charges'][OrderProduct::CHARGE_TYPE_SHIPPING][OrderProduct::DB_TBL_CHARGES_PREFIX.'amount'])?$requestRow['charges'][OrderProduct::CHARGE_TYPE_SHIPPING][OrderProduct::DB_TBL_CHARGES_PREFIX.'amount']:0;

        $productAvaliedFreeShip = false;
        if (0 < $requestRow["op_free_ship_upto"] && array_key_exists(OrderProduct::CHARGE_TYPE_SHIPPING, $requestRow['charges']) && $requestRow["op_actual_shipping_charges"] != $requestRow['charges'][OrderProduct::CHARGE_TYPE_SHIPPING]['opcharge_amount']) {
            $productAvaliedFreeShip = true;
            $shipCharges = $requestRow['op_actual_shipping_charges'];
        }

        $perUnitShippingCost = $shipCharges / $requestRow["op_qty"];

        $couponDiscount = isset($requestRow['charges'][OrderProduct::CHARGE_TYPE_DISCOUNT]['opcharge_amount'])?abs($requestRow['charges'][OrderProduct::CHARGE_TYPE_DISCOUNT]['opcharge_amount']):0;

        $taxCharges = isset($requestRow['charges'][OrderProduct::CHARGE_TYPE_TAX]['opcharge_amount'])?$requestRow['charges'][OrderProduct::CHARGE_TYPE_TAX]['opcharge_amount']:0;
        $taxPerQty = ($taxCharges/$requestRow['op_qty']);

        $cartAmount = $requestRow["op_unit_price"] * $requestRow["orrequest_qty"];

        $commissionCostValue = $requestRow["op_unit_price"];
        if ($requestRow['op_commission_include_tax'] && $taxPerQty) {
            $commissionCostValue = $commissionCostValue + $taxPerQty;
        }

        if ($requestRow['op_commission_include_shipping'] && $perUnitShippingCost) {
            $commissionCostValue = $commissionCostValue + $perUnitShippingCost;
        }

        $op_refund_commission = round((($commissionCostValue*$requestRow['op_qty']) * $requestRow['op_commission_percentage'])/100, 2);

        $op_refund_commission = min($op_refund_commission, FatApp::getConfig("CONF_MAX_COMMISSION"));

        $perProdRefundCommission = round($op_refund_commission /$requestRow['op_qty'], 2);
        $op_refund_commission = $perProdRefundCommission * $requestRow["orrequest_qty"];
        //$op_refund_commission = min( $op_refund_commission, (FatApp::getConfig("CONF_MAX_COMMISSION")*$requestRow["orrequest_qty"] ));

        /* $op_refund_commission = round( ($cartAmount * $requestRow['op_commission_percentage'])/100, 2 );
        $op_refund_commission = min( $op_refund_commission, (FatApp::getConfig("CONF_MAX_COMMISSION")*$requestRow["orrequest_qty"] )); */

        $op_refund_affiliate_commission = round(($cartAmount * $requestRow['op_affiliate_commission_percentage'])/100, 2);


        $taxToRefund = 0;
        if ($taxCharges > 0) {
            $taxToRefund = ($taxPerQty * ($requestRow['orrequest_qty']));
        }

        $volumeDiscountPerQty = 0;
        $deductVolumeDiscountFromRefund = 0;
        if ($volumeDiscount > 0) {
            $volumeDiscountPerQty = ($volumeDiscount/$requestRow['op_qty']);
            $deductVolumeDiscountFromRefund = ($volumeDiscountPerQty * $requestRow['orrequest_qty']);
        }

        $couponDiscountPerQty = 0;
        $deductCouponDiscountFromRefund = 0;
        if ($couponDiscount > 0) {
            $couponDiscountPerQty = ($couponDiscount/$requestRow['op_qty']);
            $deductCouponDiscountFromRefund = ($couponDiscountPerQty * $requestRow['orrequest_qty']);
        }

        $totalPaidAmtBuyer = ($requestRow["op_unit_price"]*$requestRow['op_qty']) + $requestRow["op_other_charges"];
        if (!$productAvaliedFreeShip) {
            $totalPaidAmtBuyer = $totalPaidAmtBuyer - $shipCharges;
        }

        if ($requestRow['op_qty'] == $requestRow['orrequest_qty']) {
            $op_refund_amount = $totalPaidAmtBuyer;
        } else {
            $op_refund_amount = $cartAmount + $taxToRefund - $deductVolumeDiscountFromRefund - $deductCouponDiscountFromRefund;
        }

        $op_refund_shipping = 0;
        /*
        if(0 < $requestRow["op_free_ship_upto"] && array_key_exists(OrderProduct::CHARGE_TYPE_SHIPPING,$requestRow['charges']) && $requestRow["op_actual_shipping_charges"] != $requestRow['charges'][OrderProduct::CHARGE_TYPE_SHIPPING]['opcharge_amount']){
            $unitShipCharges = round(($requestRow['op_actual_shipping_charges'] / $requestRow['op_qty']),2);
            $op_refund_amount = $op_refund_amount - $requestRow['op_actual_shipping_charges'];
            $shipCharges = $requestRow['op_actual_shipping_charges'];
        }else{
            $unitShipCharges = round(($shipCharges / $requestRow['op_qty']),2);
        } */

        if (FatApp::getConfig('CONF_RETURN_SHIPPING_CHARGES_TO_CUSTOMER', FatUtility::VAR_INT, 0)) {
            $unitShipCharges = round(($shipCharges / $requestRow['op_qty']), 2);
            if (!$productAvaliedFreeShip) {
                $op_refund_shipping = round(($unitShipCharges * $requestRow["orrequest_qty"]), 2);
            }
            $op_refund_amount = $op_refund_amount + $op_refund_shipping;
        }

        $opDataToUpdate = array(
            'op_refund_qty'=>$requestRow['orrequest_qty'],
            'op_cart_amount'=>$cartAmount,
            'op_prod_price'=>$cartAmount- $deductVolumeDiscountFromRefund - $deductCouponDiscountFromRefund,
            'op_refund_amount'=>round($op_refund_amount, 2),
            'op_refund_shipping'=>$op_refund_shipping,
            'op_refund_commission'=>$op_refund_commission,
            'op_refund_affiliate_commission' => $op_refund_affiliate_commission,
            'op_refund_tax' => $taxToRefund,
        );

        return $opDataToUpdate;
    }

    public static function orderSubscriptionAmount($opArr = array(), $amountType = 'netamount', $pricePerItem = false)
    {
        $amount = 0;

        if (empty($opArr)) {
            trigger_error('Order Product Array should not be empty', E_USER_ERROR);
        }

        $cartTotal = $opArr['ossubs_price'];

        switch (strtoupper($amountType)) {
            case 'NETAMOUNT':
                $amount = $cartTotal + $opArr['op_other_charges'] ;
                break;

            case 'REWARDPOINT':
                $amount = isset($opArr['charges'][OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT]['opcharge_amount'])?$opArr['charges'][OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT]['opcharge_amount']:0;
                break;
            case 'DISCOUNT':
                $amount = isset($opArr['charges'][OrderProduct::CHARGE_TYPE_DISCOUNT]['opcharge_amount'])?$opArr['charges'][OrderProduct::CHARGE_TYPE_DISCOUNT]['opcharge_amount']:0;
                break;
            case 'ADJUSTEDAMOUNT':
                $amount = isset($opArr['charges'][OrderProduct::CHARGE_TYPE_ADJUST_SUBSCRIPTION_PRICE]['opcharge_amount'])?$opArr['charges'][OrderProduct::CHARGE_TYPE_ADJUST_SUBSCRIPTION_PRICE]['opcharge_amount']:0;
                break;
            case 'CART_TOTAL':
                $amount = $cartTotal;
                break;
        }


        return $amount;
    }
    /*
    public static function calculateTaxRates($productId, $prodPrice, $sellerId, $langId,$qty = 1){
        $tax = 0 ;
        $res = $this->getTaxRates($productId, $sellerId, $langId);

        if(empty($res)){
            return $tax;
        }

        if($res['taxval_is_percent'] == static::TYPE_PERCENTAGE){
            $tax = round((($prodPrice*$qty) * $res['taxval_value'])/100,2);
        }else{
            $tax = $res['taxval_value']*$qty;
        }

        return $tax;
    } */

    public static function renderHtml($content = '', $stripJs = false)
    {
        $str = html_entity_decode($content);
        $str = ($stripJs == true)?static::stripJavascript($str):$str;

        return $str;
    }

    public static function displayTaxFormat($isPercent, $val, $position = 'R')
    {
        if (!$isPercent) {
            return self::displayMoneyFormat($val);
        }

        if ($position == 'L') {
            return '% '.$val;
        }

        return $val.' %';
    }

    public static function getDefaultCurrencyValue($val, $format = true, $displaySymbol = true)
    {
        $currencyValue = self::getCurrencyValue();
        $defaultCurrencyValue = $val / $currencyValue;
        return static::displayMoneyFormat($defaultCurrencyValue, $format, true, $displaySymbol);
    }

    public static function displayComissionPercentage($value = 0)
    {
        if (round($value, 0) == $value) {
            return  round($value, 0);
        } else {
            return $value;
        }
    }

    public static function getCurrencySymbol($showDefaultSiteCurrenySymbol = false)
    {
        if ($showDefaultSiteCurrenySymbol) {
            $currency_id = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
            $currencyData = Currency::getAttributesById(
                $currency_id,
                array('currency_symbol_left','currency_symbol_right')
            );
            $currencySymbolLeft = $currencyData['currency_symbol_left'];
            $currencySymbolRight = $currencyData['currency_symbol_right'];
        } else {
            $currencySymbolLeft = self::getCurrencySymbolLeft();
            $currencySymbolRight = self::getCurrencySymbolRight();
        }
        return   $currencySymbolLeft.$currencySymbolRight;
    }

    public static function numberStringFormat($number)
    {
        $prefixes = 'KMGTPEZY';
        if ($number >= 1000) {
            for ($i=-1; $number>=1000; ++$i) {
                $number =  $number/1000;
            }
            return floor($number).$prefixes[$i];
        }
        return $number;
    }

    public static function convertExistingToOtherCurrency($currCurrencyId, $val, $otherCurrencyId, $numberFormat = true)
    {
        $currencyData = Currency::getAttributesById(
            $currCurrencyId,
            array('currency_value')
        );
        $currencyValue = $currencyData['currency_value'];
        $val = $val/$currencyValue;

        $currencyData = Currency::getAttributesById(
            $otherCurrencyId,
            array('currency_value')
        );
        $currencyValue = $currencyData['currency_value'];
        $val = $val* $currencyValue;

        if ($numberFormat) {
            $val = number_format($val, 2);
        }

        return $val;
    }

    public static function displayMoneyFormat($val, $numberFormat = true, $showInConfiguredDefaultCurrency = false, $displaySymbol = true, $stringFormat = false)
    {
        $val = FatUtility::convertToType($val, FatUtility::VAR_FLOAT);
        $currencyValue = self::getCurrencyValue();
        $currencySymbolLeft = self::getCurrencySymbolLeft();
        $currencySymbolRight = self::getCurrencySymbolRight();

        if ($showInConfiguredDefaultCurrency) {
            $currency_id = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
            $currencyData = Currency::getAttributesById(
                $currency_id,
                array('currency_code','currency_symbol_left','currency_symbol_right','currency_value')
            );
            $currencyValue = $currencyData['currency_value'];
            $currencySymbolLeft = $currencyData['currency_symbol_left'];
            $currencySymbolRight = $currencyData['currency_symbol_right'];
        }

        $val = $val * $currencyValue;

        $sign = '';
        if ($val < 0) {
            $val = abs($val);
            $sign = '-';
        }

        if ($numberFormat && !$stringFormat) {
            $val = number_format($val, 2);
        } else {
            $afterDecimal = $val - floor($val);
            $val = (0 < $afterDecimal ? number_format($val, 2, '.', '') : $val);
        }

        if ($stringFormat) {
            $val = static::numberStringFormat($val);
        }

        if ($displaySymbol) {
            $sign.= ' ';
            $val =  $sign.$currencySymbolLeft.$val.$currencySymbolRight;
        } else {
            $val =  $sign.$val;
        }

        return trim($val);
        /* if($displaySymbol){
            if($val < 0){

                if ( $numberFormat ){
                    $val = number_format( abs($val), 2 );
                }
                if($customHtml){
                    return '<sup>- '.$currencySymbolLeft.' </sup><span> '.$val.' </span>'.$currencySymbolRight;

                }else{

                return '- '.$currencySymbolLeft.$val.$currencySymbolRight;
                }
            }else{
                if ( $numberFormat ){
                    $val = number_format( $val, 2 );
                }
                if($customHtml){

                    return '<sup>'.$currencySymbolLeft.'</sup><span>'.$val.$currencySymbolRight.'</span>';

                }else{

                    return $currencySymbolLeft.$val.$currencySymbolRight;
                }

            }
        } */
    }
    public static function convertCurrencyToRewardPoint($currencyValue)
    {
        $currencyValue = FatUtility::convertToType($currencyValue, FatUtility::VAR_FLOAT);
        if ($currencyValue == 0) {
            return 0;
        }
        return round(($currencyValue * FatApp::getConfig('CONF_REWARD_POINT')), 2);
    }

    public static function convertRewardPointToCurrency($rewardPoints)
    {
        $rewardPoints = FatUtility::int($rewardPoints);
        if ($rewardPoints == 0) {
            return 0;
        }
        return round(($rewardPoints / FatApp::getConfig('CONF_REWARD_POINT')), 2);
    }

    public static function displayNotApplicable($langId, $val, $str = "-NA-")
    {
        $str = ($str == "") ? Labels::getLabel("LBL_-NA-", $langId) : $str;
        return $val != "" ? $val : $str;
    }

    public static function editorSvg($path)
    {
        $headers = FatApp::getApacheRequestHeaders();
        if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($path))) {
            header('Content-type: image/svg+xml');
            header('Cache-Control: public, must-revalidate');
            header("Pragma: public");
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($path)).' GMT', true, 304);
            header("Expires: " . date('D, d M Y H:i:s', strtotime("+30 days")));
            exit;
        }
        header('Content-type: image/svg+xml');
        header("Pragma: public");
        header('Cache-Control: public, must-revalidate');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($path)).' GMT', true, 200);
        header("Expires: " . date('D, d M Y H:i:s', strtotime("+30 days")));
        readfile($path);
    }

    /* public static function captchaImg() {
        require_once  CONF_INSTALLATION_PATH . 'library/securimage/securimage.php';

        $options = array(
                'text_color' => '#000',
                'noise_color' => '#000',
                'code_length' => 5,
                'num_lines' => 0,
                'image_width' => 300,
                'font_ratio' => 1
            );
        $img = new Securimage($options);
        $img->case_sensitive = true;
        $img->show();
    } */



    public static function convertToCsv($input_array, $output_file_name, $delimiter = ',')
    {
        /** open raw memory as file, no need for temp files */
        $temp_memory = fopen('php://memory', 'w');
        /** loop through array  */
        foreach ($input_array as $key => $line) {
            /** default php csv handler **/
            //fprintf( $temp_memory, chr(0xEF).chr(0xBB).chr(0xBF) );
            //$line = array_map("utf8_decode", $line);
            //$line = array_map( FatUtility::decodeHtmlEntities, $line );
            //fprintf( $temp_memory, chr(0xEF).chr(0xBB).chr(0xBF) );
            //fputs( $temp_memory, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );
            fputcsv($temp_memory, $line, $delimiter);
        }
        /** rewrind the "file" with the csv lines **/
        fseek($temp_memory, 0);
        /** modify header to be downloadable csv file **/
        header('Content-Description: File Transfer');
        /* header('Content-Transfer-Encoding: binary');
        header('Content-Type: application/octet-stream'); */
        header('Content-Encoding: UTF-8');
        header('Content-type: application/csv; charset=UTF-8; encoding=UTF-8');
        header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
        /** Send file to browser for download */

        //echo "\xEF\xBB\xBF";
        header("Cache-Control: cache, must-revalidate");
        header("Pragma: public");

        fpassthru($temp_memory);
    }

    public static function addToCSV($handle, $fileContent = array())
    {
        if (!$handle) {
            return false;
        }

        if (is_array($fileContent) && 0 < count($fileContent)) {
            fputcsv($handle, $fileContent);
        }
    }

    /* File creation in temporary memory. */
    public static function writeExportDataToCSV($handle, $fileContent = array(), $download = false, $output_file_name = '')
    {
        self::addToCSV($handle, $fileContent);

        if ($download) {
            /** rewrind the "file" with the csv lines **/
            fseek($handle, 0);
            /** modify header to be downloadable csv file **/
            header('Content-Description: File Transfer');
            header('Content-Encoding: UTF-8');
            header('Content-type: application/csv; charset=UTF-8; encoding=UTF-8');
            header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
            /** Send file to browser for download */

            header("Cache-Control: cache, must-revalidate");
            header("Pragma: public");

            fpassthru($handle);
        }
    }

    /* To retain file on server. */
    public static function writeToCSVFile($handle, $fileContent = array(), $fileClose = false)
    {
        self::addToCSV($handle, $fileContent);

        if ($fileClose) {
            fclose($handle);
        }
    }

    public static function checkCSVFile($fileName)
    {
        if (empty($fileName)) {
            return false;
        }

        $file = fopen(ImportexportCommon::IMPORT_ERROR_LOG_PATH.$fileName, "r");

        /**** Skip first heading row ****/
        fgetcsv($file);
        /**** Skip first heading row ****/

        $havingData = fgetcsv($file);

        if (!$havingData) {
            unlink(ImportexportCommon::IMPORT_ERROR_LOG_PATH.$fileName);
        }
        return $havingData;
    }

    public static function getPercentValue($percentage, $total)
    {
        if (!$total) {
            return 0;
        }
        $percent = $percentage/$total;
        return $percent_friendly = number_format($percent * 100, 2) . '%';
    }

    public static function verifyCaptcha($fld_name = 'g-recaptcha-response')
    {
        require_once(CONF_INSTALLATION_PATH . 'library/ReCaptcha/src/autoload.php');
        if (!empty(FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '')) && !empty(FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, ''))) {
            $recaptcha = new \ReCaptcha\ReCaptcha(FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, ''));
            $post = FatApp::getPostedData();
            if (isset($post[$fld_name])) {
                $resp = $recaptcha->verify($post[$fld_name], $_SERVER['REMOTE_ADDR']);
                return $resp->isSuccess()==true?true:false;
            } else {
                return false;
            }
        } else {
            return true;
        }
        /* require_once CONF_INSTALLATION_PATH . 'library/securimage/securimage.php';
        $img = new Securimage();
        $img->case_sensitive = true;
        if (!$img->check(FatApp::getPostedData('security_code', FatUtility::VAR_STRING))) {
            return false;
        }
        return true; */
    }

    public static function stripJavascript($content = '')
    {
        $javascript = '/<script[^>]*?>.*?<\/script>/si';
        $noscript = '';
        return preg_replace($javascript, $noscript, $content);
    }

    public static function addhttp($url)
    {
        return $url;
    }

    public static function escapeStringAndAddQuote($string)
    {
        $db = FatApp::getDb();
        if (method_exists($db, 'quoteVariable')) {
            return $db->quoteVariable($string);
        } else {
            return "'" . mysql_real_escape_string($string) . "'";
        }
    }

    public static function setAppUser()
    {
        $_SESSION['app_user'] = true;
    }

    public static function isAppUser()
    {
        if (isset($_SESSION['app_user'])) {
            return true;
        }
        return false;
    }

    public static function escapeString($string)
    {
        return trim(self::escapeStringAndAddQuote($string), "'");
    }

    public static function parseYouTubeurl($url)
    {
        /*$pattern = '#^(?:https?://)?';    # Optional URL scheme. Either http or https.
        $pattern .= '(?:www\.)?';         #  Optional www subdomain.
        $pattern .= '(?:';                #  Group host alternatives:
        $pattern .=   'youtu\.be/';       #    Either youtu.be,
        $pattern .=   '|youtube\.com';    #    or youtube.com
        $pattern .=   '(?:';              #    Group path alternatives:
        $pattern .=     '/embed/';        #      Either /embed/,
        $pattern .=     '|/v/';           #      or /v/,
        $pattern .=     '|/watch\?v=';    #      or /watch?v=,
        $pattern .=     '|/watch\?.+&v='; #      or /watch?other_param&v=
        $pattern .=   ')';                #    End path alternatives.
        $pattern .= ')';                  #  End host alternatives.
        $pattern .= '([\w-]{11})';        # 11 characters (Length of Youtube video ids).
        $pattern .= '(?:.+)?$#x';         # Optional other ending URL parameters.

        preg_match($pattern, $url, $matches);*/

        preg_match("/(?:[\/]|v=)([a-zA-Z0-9-_]{11})/", $url, $matches);

        return (isset($matches[1])) ? $matches[1] : false;
    }
    public static function getCurrUrl()
    {
        return self::getUrlScheme() . $_SERVER["REQUEST_URI"];
    }

    public static function isThemePreview()
    {
        if (strpos(urldecode($_SERVER['REQUEST_URI']), '?theme-preview') > 0) {
            return true;
        }
        return false;
    }

    public static function getnavigationUrl($type, $nav_url = '', $nav_cpage_id = 0, $nav_category_id = 0, $getOriginalUrl = false)
    {
        if ($type == NavigationLinks::NAVLINK_TYPE_CMS) {
            $url = CommonHelper::generateUrl('cms', 'view', array($nav_cpage_id), '', null, false, $getOriginalUrl);
        } elseif ($type==NavigationLinks::NAVLINK_TYPE_EXTERNAL_PAGE) {
            $url = str_replace('{SITEROOT}', CONF_WEBROOT_URL, $nav_url) ;
            $url = str_replace('{siteroot}', CONF_WEBROOT_URL, $url) ;
            $url = CommonHelper::processURLString($url);
        } elseif ($type == NavigationLinks::NAVLINK_TYPE_CATEGORY_PAGE) {
            $url = CommonHelper::generateUrl('category', 'view', array($nav_category_id), '', null, false, $getOriginalUrl);
        }

        if (self::isThemePreview()) {
            $url = $url.'?theme-preview';
        }

        return $url;
    }

    public static function getUrlScheme()
    {
        $pageURL = 'http';
        if (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"];
        }
        return $pageURL;
    }

    public static function redirectUserReferer($returnUrl = false)
    {
        if (!defined('REFERER')) {
            if (self::getCurrUrl() == $_SERVER['HTTP_REFERER'] || empty($_SERVER['HTTP_REFERER'])) {
                define('REFERER', CommonHelper::generateUrl('/'));
            } else {
                define('REFERER', $_SERVER['HTTP_REFERER']);
            }
        }

        if ($returnUrl) {
            return REFERER;
        }
        FatApp::redirectUser(REFERER);
    }

    public static function renderJsonError($tpl, $msg)
    {
        $tpl->set('msg', $msg);
        $tpl->render(false, false, 'json-error.php', false, false);
    }

    public static function renderJsonSuccess($tpl, $msg)
    {
        $tpl->set('msg', $msg);
        $tpl->render(false, false, 'json-success.php', false, false);
    }

    public static function checkMsgs()
    {
        $msgs_result['has_msgs'] = false;
        $msgs_result['msgs_html'] = '';
        if (Message::getErrorCount() > 0 || Message::getMessageCount() > 0) {
            $msgs_result['has_msgs'] = true;
            $msgs_result['msgs_html'] = Message::getHtml();
        }
        return $msgs_result;
    }

    public static function getRandomPassword($n)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = '';
        for ($i = 0; $i < $n; $i++) {
            $pass .= substr($chars, rand(0, strlen($chars)-1), 1);
        }
        return $pass;
    }

    public static function getAdminUrl($controller = '', $action = '', $queryData = array(), $use_root_url = '/admin/', $url_rewriting = null)
    {
        return FatUtility::generateFullUrl($controller, $action, $queryData, $use_root_url, $url_rewriting);
    }

    /* static function getInnovaEditorObj($textareaId, $divId, $jsTag = true){
        $innovaObj = 'window["fatbit_'.$textareaId.'"] = new InnovaEditor("fatbit_'.$textareaId.'");
                    window["fatbit_'.$textareaId.'"].width = "100%";
                    window["fatbit_'.$textareaId.'"].groups = [
                    ["group1", "", ["Bold", "Italic", "Underline", "FontDialog", "ForeColor", "TextDialog", "RemoveFormat"]],
                    ["group2", "", ["Bullets", "Numbering", "JustifyLeft", "JustifyCenter", "JustifyRight"]],
                    ["group3", "", ["LinkDialog"]],
                    ["group4", "", ["Undo", "Redo", "FullScreen", "SourceDialog","ImageDialog"]]
                    ];
                    window["fatbit_'.$textareaId.'"].REPLACE("'.$textareaId.'", "'.$divId.'");
                    window["fatbit_'.$textareaId.'"] .fileBrowser = "/admin/innova/assetmanager/asset.php";
                    ';

        if($jsTag){
            $innovaObj ='<script>'.$innovaObj.'</script>';
        }
        return $innovaObj;
    } */

    public static function currentDateTime($dateFormat = null, $dateTime = false, $timeFormat = null, $timeZone = null)
    {
        if ($timeZone == null) {
            $timeZone = FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get());
        }

        if ($dateFormat == null) {
            $dateFormat = FatApp::getConfig('CONF_DATE_FORMAT', FatUtility::VAR_STRING, 'Y-m-d');
        }

        if ($dateTime) {
            if ($timeFormat == null) {
                $timeFormat = FatApp::getConfig('CONF_DATEPICKER_FORMAT_TIME', FatUtility::VAR_STRING, 'H:i');
            }
        }

        $format = $dateFormat . ' ' . $timeFormat;
        return FatDate::nowInTimezone($timeZone, trim($format));
    }

    public static function getLangFields($condition_id = 0, $condition_field = "", $condition_lang_field = "", $lang_flds = array(), $lang_table = "")
    {
        $condition_id = FatUtility::int($condition_id);
        if ($condition_id ==0 || $condition_field == "" || $condition_lang_field =="" || $lang_table=="" || empty($lang_flds)) {
            return array();
        }
        $langs = Language::getAllNames();
        ;
        $array = array();
        $srch = new SearchBase($lang_table);
        $srch->addCondition($condition_field, '=', $condition_id);
        $rs = $srch->getResultSet();

        $record = FatApp::getDb()->fetchAll($rs);
        foreach ($langs as $langId => $lang) {
            foreach ($record as $rec) {
                if ($rec[$condition_lang_field] == $langId) {
                    foreach ($lang_flds as $fld) {
                        $array[$fld][$langId] = $rec[$fld];
                        $array[$fld.$langId] = $rec[$fld];
                    }
                    continue;
                }
            }
        }
        return $array;
    }

    /* static function generateOptionsArr($option_ids, $option_value_ids, $option_names, $option_value_names){
        $option_ids_arr = explode(",",$option_ids);
        $option_value_ids_arr = explode(",",$option_value_ids);
        $option_names_arr = explode(",",$option_names);
        $option_value_names_arr = explode(",",$option_value_names);

        $options_arr = array();
        if( count($option_ids_arr) > 0 && $option_ids_arr[0] != '' ){
            foreach($option_ids_arr as $key => $option_id)
            $options_arr[$option_id] = array(
                'option_id'  => $option_id,
                'optionvalue_id' => $option_value_ids_arr[$key],
                'option_name'=> $option_names_arr[$key],
                'optionvalue_name' => $option_value_names_arr[$key]
            );
        }
        return $options_arr;
    } */

    public static function arrayToAssocArray($arr)
    {
        $arr_url_params = array();
        if (!empty($arr)) {
            foreach ($arr as $key => $val) {
                $v = 0;
                if ($key%2 == 0) {
                    $k = $val;
                } else {
                    $v = $val;
                }
                $arr_url_params[$k] = $v;
            }
        }

        return $arr_url_params;
    }

    public static function crop($data, $src, $langId)
    {
        if (empty($data)) {
            return;
        }

        $size = getimagesize($src);
        $size_w = $size[0]; // natural width
        $size_h = $size[1]; // natural height

        $src_img_w = $size_w;
        $src_img_h = $size_h;

        $degrees = $data -> rotate;

        switch ($size['mime']) {
            case "image/gif":
                $src_img = imagecreatefromgif($src);
                break;

            case "image/jpeg":
                $src_img = imagecreatefromjpeg($src);
                break;

            case "image/png":
                $src_img = imagecreatefrompng($src);
                break;
        }

        //  $src_img = imagecreatefromjpeg($src);
        // Rotate the source image
        if (is_numeric($degrees) && $degrees != 0) {
            // PHP's degrees is opposite to CSS's degrees
            $new_img = imagerotate($src_img, -$degrees, imagecolorallocatealpha($src_img, 0, 0, 0, 127));

            imagedestroy($src_img);
            $src_img = $new_img;

            $deg = abs($degrees) % 180;
            $arc = ($deg > 90 ? (180 - $deg) : $deg) * M_PI / 180;

            $src_img_w = $size_w * cos($arc) + $size_h * sin($arc);
            $src_img_h = $size_w * sin($arc) + $size_h * cos($arc);

            // Fix rotated image miss 1px issue when degrees < 0
            $src_img_w -= 1;
            $src_img_h -= 1;
        }

        $tmp_img_w = $data -> width;
        $tmp_img_h = $data -> height;
        $dst_img_w = 320;
        $dst_img_h = 320;

        $src_x = $data -> x;
        $src_y = $data -> y;

        if ($src_x <= -$tmp_img_w || $src_x > $src_img_w) {
            $src_x = $src_w = $dst_x = $dst_w = 0;
        } elseif ($src_x <= 0) {
            $dst_x = -$src_x;
            $src_x = 0;
            $src_w = $dst_w = min($src_img_w, $tmp_img_w + $src_x);
        } elseif ($src_x <= $src_img_w) {
            $dst_x = 0;
            $src_w = $dst_w = min($tmp_img_w, $src_img_w - $src_x);
        }

        if ($src_w <= 0 || $src_y <= -$tmp_img_h || $src_y > $src_img_h) {
            $src_y = $src_h = $dst_y = $dst_h = 0;
        } elseif ($src_y <= 0) {
            $dst_y = -$src_y;
            $src_y = 0;
            $src_h = $dst_h = min($src_img_h, $tmp_img_h + $src_y);
        } elseif ($src_y <= $src_img_h) {
            $dst_y = 0;
            $src_h = $dst_h = min($tmp_img_h, $src_img_h - $src_y);
        }

        // Scale to destination position and size
        $ratio = $tmp_img_w / $dst_img_w;
        $dst_x /= $ratio;
        $dst_y /= $ratio;
        $dst_w /= $ratio;
        $dst_h /= $ratio;

        $dst_img = imagecreatetruecolor($dst_img_w, $dst_img_h);

        // Add transparent background to destination image
        imagefill($dst_img, 0, 0, imagecolorallocatealpha($dst_img, 0, 0, 0, 127));
        imagesavealpha($dst_img, true);

        $result = imagecopyresampled($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

        if ($result) {
            if (!imagepng($dst_img, $src)) {
                return Labels::getLabel('MSG_Failed_to_save_cropped_file', $langId);
            }
        } else {
            return Labels::getLabel('MSG_Failed_to_crop_file', $langId);
        }

        imagedestroy($src_img);
        imagedestroy($dst_img);
    }

    public static function isRenderTemplateExist($template = '')
    {
        $instance = FatApplication::getInstance();
        if ($template == '') {
            $themeDirName = FatUtility::camel2dashed(substr($instance->getController(), 0, -(strlen('controller'))));
            $actionName = FatUtility::camel2dashed($instance->getAction()) . '.php';
            $template = $themeDirName . '/' . $actionName;
        }

        if (file_exists(CONF_THEME_PATH.$template)) {
            return true;
        }
        return false;
    }

    public static function subStringByWords($str, $maxlength)
    {
        if (strlen($str)<$maxlength) {
            return $str;
        }
        $str = substr($str, 0, $maxlength);
        $rpos = strrpos($str, ' ');
        if ($rpos>0) {
            $str = substr($str, 0, $rpos);
        }
        return $str;
    }

    public static function getWeightInGrams($unit, $val)
    {
        $unit = FatUtility::int($unit);
        switch ($unit) {
            case applicationConstants::WEIGHT_GRAM:
                $weight = $val;
                break;

            case applicationConstants::WEIGHT_POUND:
                $weight = $val * 453.592;
                break;

            /* case 'OU':
            case 'OUNCE':
                $weight = $val * 28.3495;
            break; */

            case applicationConstants::WEIGHT_KILOGRAM:
                $weight = $val * 0.001;
                break;
            default:
                trigger_error("Invalid Argument", E_USER_ERROR);
        }
        return $weight;
    }

    public static function getLengthInCentimeter($val, $unit)
    {
        $unit = FatUtility::int($unit);
        switch ($unit) {
            case applicationConstants::LENGTH_CENTIMETER:
                $length = $val;
                break;
            case applicationConstants::LENGTH_METER:
                $length = $val*100;
                break;
            case applicationConstants::LENGTH_INCH:
                $length = $val*2.54;
                break;
            default:
                trigger_error("Invalid Argument", E_USER_ERROR);
        }
        return $length;
    }

    public static function getVolumeInCC($unit, $val)
    {
        $unit = FatUtility::int($unit);
        return $val;
    }

    public static function isMultidimArray($arr)
    {
        if (!is_array($arr)) {
            return false;
        }
        foreach ($arr as $elm) {
            if (!is_array($elm)) {
                return false;
            }
        }
        return true;
    }

    public static function formatOrderReturnRequestNumber($requestId)
    {
        $new_value = str_pad($requestId, 5, '0', STR_PAD_LEFT);
        $new_value = "R".$new_value;
        return $new_value;
    }

    public static function processURLString($urlString)
    {
        $strtestpos = strpos(" ".$urlString, ".");
        if (!$strtestpos) {
            return $urlString;
        }
        $urlString = trim($urlString);
        if ($urlString) {
            $my_bool = false;
            if (substr($urlString, 0, 5) == 'https') {
                $my_bool = true;
            }
            $urlString = preg_replace('/https?:\/\//', '', $urlString);
            $urlString = trim($urlString);
            $pre_str = 'http://';
            if ($my_bool) {
                $pre_str = 'https://';
            }
            $urlString = $pre_str.$urlString;
        }
        return $urlString;
    }

    public static function currencyDisclaimer($langId, $amount = 0)
    {
        $str = Labels::getLabel('LBL_Note_charged_in_currency_disclaimer_{default-currency-symbol}', $langId);
        if ($amount) {
            $str = str_replace("{default-currency-symbol}", static::displayMoneyFormat($amount, true, true), $str);
        } else {
            $str = str_replace("{default-currency-symbol}", ' $ ', $str);
        }
        return $str;
    }

    public static function showProductDiscountedText($product = array(), $langId)
    {
        $langId = FatUtility::int($langId);
        if (empty($product) || $langId <= 0) {
            trigger_error("Invalid Argument Passed!", E_USER_ERROR);
        }

        $originalPrice  = $product['selprod_price'];
        $specialPrice  = $product['theprice'];
        $discount =  (($originalPrice - $specialPrice)*100)/$originalPrice;
        return $disVal = round($discount)."% ".Labels::getLabel('LBL_Off', $langId);
        /* $str = '';
        $listPrice = $product['splprice_display_list_price'];
        if( $listPrice > 0 ){
            $disVal = $product['splprice_display_dis_val'];
            $disVal = $disVal + 0;
            if (($disVal * 100) % 100 > 0) {
                $disVal = number_format($disVal, 2, '.', '');
            }

            $str .= Labels::getLabel( 'LBL_Save_{saveprice}_({offprice})', $langId );
            if( $product['splprice_display_dis_type'] == applicationConstants::PERCENTAGE ){
                $disVal .= '%';
            }
            elseif( $product['splprice_display_dis_type'] == applicationConstants::FLAT ){
                $disVal = static::displayMoneyFormat($listPrice) ;
            }

            $arrReplacements = array(
                '{saveprice}' => static::displayMoneyFormat($listPrice),
                '{offprice}'=> $disVal
            );

            foreach ($arrReplacements as $key => $val) {
                $str = str_replace($key, $val, $str);
            }
        }
        return $str;*/
    }

    public static function truncateCharacters($string, $limit, $break = " ", $pad = "...", $nl2br = false)
    {
        if (strlen($string) <= $limit) {
            return ($nl2br)? nl2br($string) : $string ;
        }


        $tempString = str_replace('\n', '^', $string);
        $tempString = mb_substr($tempString, 0, $limit);
        if (mb_substr($tempString, -1) == "^") {
            $limit = $limit - 1;
        }
        $string = mb_substr($string, 0, $limit);

        if (false !== ($breakpoint = mb_strrpos($string, $break))) {
            $string = mb_substr($string, 0, $breakpoint);
        }
        return (($nl2br)? nl2br($string) : $string) . $pad;
    }

    public static function displayName($string)
    {
        if (!empty($string)) {
            return ucfirst($string);
        }
    }

    public static function getFirstChar($string, $capitalize = false)
    {
        if (!empty($string)) {
            if ($capitalize == true) {
                return strtoupper($string[0]);
            } else {
                return $string[0];
            }
        }
    }

    public static function seoUrl($string)
    {

        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        //$string = preg_replace("/[^a-z0-9,&_\s-\/]/", "", $string);
        //covert / to -
        $string = preg_replace("/[\s,&\/]/", "-", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);

        $keyword = strtolower($string);
        $keyword = ucfirst(FatUtility::dashed2Camel($keyword));

        if (file_exists(CONF_INSTALLATION_PATH . 'application/controllers/' .$keyword.'Controller' . '.php')) {
            return $string.'-'.rand(1, 100);
        }

        return trim($string, '-');
    }

    public static function recursiveDelete($str)
    {
        if (is_file($str)) {
            return @unlink($str);
        } elseif (is_dir($str)) {
            $scan = glob(rtrim($str, '/').'/*');
            foreach ($scan as $index => $path) {
                static::recursiveDelete($path);
            }
            return @rmdir($str);
        }
    }

    public static function displayText($value = '')
    {
        return empty(trim($value)) ? '-' : $value ;
    }

    public static function getPlaceholderForAmtField($langId)
    {
        return Labels::getLabel('Lbl_Amount_in', $langId).' '.static::concatCurrencySymbolWithAmtLbl();
    }

    public static function concatCurrencySymbolWithAmtLbl()
    {
        $currencyId = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
        $currencyData = Currency::getAttributesById(
            $currencyId,
            array('currency_code','currency_symbol_left','currency_symbol_right','currency_value')
        );

        $currencySymbolLeft = $currencyData['currency_symbol_left'];
        $currencySymbolRight = $currencyData['currency_symbol_right'];

        $symbol = $currencySymbolRight ? $currencySymbolRight : $currencySymbolLeft ;

        return empty($symbol) ? '' : " ($symbol)" ;
    }

    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function multipleExplode($delimiters = array(), $string = '')
    {
        $mainDelim = end($delimiters);
        array_pop($delimiters);
        foreach ($delimiters as $delimiter) {
            $string= str_replace($delimiter, $mainDelim, $string);
        }
        $result= explode($mainDelim, $string);
        return self::arrayTrim($result);
    }

    public static function arrayTrim($ar)
    {
        foreach ($ar as $key => $val) {
            $val=trim($val);
            if (!empty($val)) {
                $reArray[]=$val;
            }
        }
        return $reArray;
    }

    public static function referralTrackingUrl($code)
    {
        return self::generateFullUrl('Home', 'Referral', array($code));
    }

    public static function affiliateReferralTrackingUrl($code)
    {
        return self::generateFullUrl('Home', 'AffiliateReferral', array($code));
    }

    public static function createSlug($string)
    {
        $slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
        return $slug;
    }

    public static function getProdRatingInPercentage($rating, $total, $circleView)
    {
        $percentage=($rating/$total)*100;
        if ($circleView==true) {
            $degree=($percentage*360)/100;
            return $degree;
        }
        return $percentage;
    }

    public static function getValidTillDate($packageInfo, $sub_last_date = '')
    {
        $days = $packageInfo['ossubs_interval'];
        $durationArr = SellerPackagePlans::getSubscriptionPeriodValues();
        $duration = $durationArr[$packageInfo['ossubs_frequency']];
        if ($sub_last_date =='') {
            $sub_last_date =  date('Y-m-d');
        }
        return  date('Y-m-d', strtotime("+" . $days . " ".$duration, strtotime($sub_last_date)));
    }

    public static function isCsvValidMimes()
    {
        $csvValidMimes = array(
            'text/x-comma-separated-values',
            'text/comma-separated-values',
            'application/octet-stream',
            'application/vnd.ms-excel',
            'application/x-csv',
            'text/x-csv',
            'text/csv',
            'application/csv',
            'application/excel',
            'application/vnd.msexcel',
            'text/plain'
        );
        return $csvValidMimes;
    }

    public static function createDropDownFromArray($name = '', $arr = array(), $selected = 0, $extra = ' ', $selectCaption = '')
    {
        $dropDown ='<select name="'.$name.'" '.$extra.'>';
        if ($selectCaption) {
            $dropDown .='<option  value="0">'.$selectCaption.'</option>';
        }

        foreach ($arr as $key => $val) {
            $selectedStr = ($key==$selected)?"selected=selected":"" ;
            $dropDown .='<option '.$selectedStr.' value="'.$key.'">'.$val.'</option>';
        }
        $dropDown .='</select>';
        return  $dropDown;
    }

    public static function getUserFirstName($userName = '')
    {
        $nameArr = explode(" ", $userName);
        $firstName =  $nameArr[0];
        if (strlen($firstName)>15) {
            return substr($firstName, 0, 11)."...";
        } else {
            return $firstName;
        }
    }

    public static function setCookie($cookieName, $cookieValue, $cookieExpiryTime, $cookiePath = '', $cokieSubDomainName = '', $isCookieSecure = false, $isCookieHttpOnly = true)
    {
        $cookiePath = ($cookiePath == "") ? CONF_WEBROOT_URL : $cookiePath;

        /* manipulating $cookieValue to make it array containg real data and storing creation datetime [ */
        /* */
        /* ] */

        setcookie($cookieName, $cookieValue, $cookieExpiryTime, $cookiePath, $cokieSubDomainName, $isCookieSecure, $isCookieHttpOnly);
    }

    public static function writeFile($name, $data, &$response)
    {
        $fName = CONF_UPLOADS_PATH . preg_replace('/[^a-zA-Z0-9\/\-\_\.]/', '', $name);
        $dest = dirname($fName);

        if (!file_exists($dest)) {
            mkdir($dest, 0777, true);
        }

        $file = fopen($fName, 'w');
        if (!fwrite($file, $data)) {
            $response = Labels::getLabel('MSG_Could_not_save_file.', CommonHelper::getLangId());
            return false;
        }
        fclose($file);
        $response = $fName;
        return true;
    }

    public static function getPaymentCancelPageUrl()
    {
        return  CommonHelper::generateFullUrl("Custom", "paymentCancel");
    }
    public static function getPaymentFailurePageUrl()
    {
        return  CommonHelper::generateFullUrl("Custom", "paymentFailed");
    }

    public static function minifyHtml($input)
    {
        if (trim($input) === "") {
            return $input;
        }
        // Remove extra white-space(s) between HTML attribute(s)
        $input = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s', function ($matches) {
            return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
        }, str_replace("\r", "", $input));
        // Minify inline CSS declaration(s)
        if (strpos($input, ' style=') !== false) {
            $input = preg_replace_callback('#<([^<]+?)\s+style=([\'"])(.*?)\2(?=[\/\s>])#s', function ($matches) {
                return '<' . $matches[1] . ' style=' . $matches[2] . CommonHelper::minifyCss($matches[3]) . $matches[2];
            }, $input);
        }
        if (strpos($input, '</style>') !== false) {
            $input = preg_replace_callback('#<style(.*?)>(.*?)</style>#is', function ($matches) {
                return '<style' . $matches[1] .'>'. CommonHelper::minifyCss($matches[2]) . '</style>';
            }, $input);
        }
        if (strpos($input, '</script>') !== false) {
            $input = preg_replace_callback('#<script(.*?)>(.*?)</script>#is', function ($matches) {
                return '<script' . $matches[1] .'>'. CommonHelper::minifyJs($matches[2]) . '</script>';
            }, $input);
        }
        return preg_replace(
            array(
                // t = text
                // o = tag open
                // c = tag close
                // Keep important white-space(s) after self-closing HTML tag(s)
                '#<(img|input)(>| .*?>)#s',
                // Remove a line break and two or more white-space(s) between tag(s)
                '#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
                '#(<!--.*?-->)|(?<!\>)\s+(<\/.*?>)|(<[^\/]*?>)\s+(?!\<)#s', // t+c || o+t
                '#(<!--.*?-->)|(<[^\/]*?>)\s+(<[^\/]*?>)|(<\/.*?>)\s+(<\/.*?>)#s', // o+o || c+c
                '#(<!--.*?-->)|(<\/.*?>)\s+(\s)(?!\<)|(?<!\>)\s+(\s)(<[^\/]*?\/?>)|(<[^\/]*?\/?>)\s+(\s)(?!\<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
                '#(<!--.*?-->)|(<[^\/]*?>)\s+(<\/.*?>)#s', // empty tag
                '#<(img|input)(>| .*?>)<\/\1>#s', // reset previous fix
                '#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...
                '#(?<=\>)(&nbsp;)(?=\<)#', // --ibid
                // Remove HTML comment(s) except IE comment(s)
                '#\s*<!--(?!\[if\s).*?-->\s*|(?<!\>)\n+(?=\<[^!])#s'
            ),
            array(
                '<$1$2</$1>',
                '$1$2$3',
                '$1$2$3',
                '$1$2$3$4$5',
                '$1$2$3$4$5$6$7',
                '$1$2$3',
                '<$1$2',
                '$1 ',
                '$1',
                ""
            ),
            $input
        );
    }

    public static function minifyCss($input)
    {
        if (trim($input) === "") {
            return $input;
        }
        return preg_replace(
            array(
                // Remove comment(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
                // Remove unused white-space(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
                // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
                // Replace `:0 0 0 0` with `:0`
                '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
                // Replace `background-position:0` with `background-position:0 0`
                '#(background-position):0(?=[;\}])#si',
                // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
                '#(?<=[\s:,\-])0+\.(\d+)#s',
                // Minify string value
                '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
                '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
                // Minify HEX color code
                '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
                // Replace `(border|outline):none` with `(border|outline):0`
                '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
                // Remove empty selector(s)
                '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
            ),
            array(
                '$1',
                '$1$2$3$4$5$6$7',
                '$1',
                ':0',
                '$1:0 0',
                '.$1',
                '$1$3',
                '$1$2$4$5',
                '$1$2$3',
                '$1:0',
                '$1$2'
            ),
            $input
        );
    }
    // JavaScript Minifier
    public static function minifyJs($input)
    {
        if (trim($input) === "") {
            return $input;
        }
        return preg_replace(
            array(
                // Remove comment(s)
                '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
                // Remove white-space(s) outside the string and regex
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
                // Remove the last semicolon
                '#;+\}#',
                // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
                '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
                // --ibid. From `foo['bar']` to `foo.bar`
                '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
            ),
            array(
                '$1',
                '$1$2',
                '}',
                '$1$3',
                '$1.$3'
            ),
            $input
        );
    }

    public static function getUserCookiesEnabled()
    {
        return (isset($_SESSION['cookies_enabled']) && $_SESSION['cookies_enabled']==true)?true:false;
    }

    public static function getDefaultCurrencySymbol()
    {
        $row = Currency::getAttributesById(FatApp::getConfig('CONF_CURRENCY'), array('currency_symbol_left','currency_symbol_right'));
        if (!empty($row)) {
            return ($row['currency_symbol_left']!='')?$row['currency_symbol_left']:$row['currency_symbol_right'];
        }
        trigger_error(Labels::getLabel('ERR_Default_currency_not_specified.', CommonHelper::getLangId()), E_USER_ERROR);
    }

    public static function logData($str)
    {
        if (is_array($str)) {
            $str = json_encode($str);
        }
        //Something to write to txt log
        $log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i:s:u a").PHP_EOL.
                "data: ".$str.PHP_EOL.
                "-------------------------".PHP_EOL;
        $file = CONF_UPLOADS_PATH.'./log_'.date("Y-m-d").'.txt';
        //Save string to log, use FILE_APPEND to append.
        file_put_contents($file, $log, FILE_APPEND);
    }

    public static function fullCopy($source, $target, $empty_first = true)
    {
        if ($empty_first) {
            self::recursiveDelete($target);
        }
        if (is_dir($source)) {
            @mkdir($target);
            $d = dir($source);
            while (false !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $Entry = $source . '/' . $entry;
                if (is_dir($Entry)) {
                    self::fullCopy($Entry, $target . '/' . $entry);
                    continue;
                }
                copy($Entry, $target . '/' . $entry);
            }

            $d->close();
        } else {
            copy($source, $target);
        }
    }

    public static function demoUrl()
    {
        if (strpos($_SERVER ['SERVER_NAME'], 'demo.yo-kart.com') !== false) {
            return true;
        }
        return false;
    }

    public static function jsonEncodeUnicode($data, $convertToType = false)
    {
        if (true === $convertToType) {
            $data = static::cleanArray($data);
        }

        die(LibHelper::convertToJson($data, JSON_UNESCAPED_UNICODE));
    }

    public static function cleanArray($obj)
    {
        $orig_obj = $obj;

        // We want to preserve the object name to the array
        // So we get the object name in case it is an object before we convert to an array (which we lose the object name)
        if (is_object($obj)) {
            $obj = (array)$obj;
            if (empty($obj)) {
                return $orig_obj;
            }
        }

        // If obj is now an array, we do a recursion
        // If obj is not, just return the value
        if (is_array($obj)) {
            $new = [];
            //initiate the recursion
            foreach ($obj as $key => $val) {
                if (is_object($orig_obj)) {
                    // Remove full class name from the key
                    $key = str_replace(get_class($orig_obj), '', $key);
                    // We don't want those * infront of our keys due to protected methods
                }

                $new[$key] = self::cleanArray($val);
            }
        } else {
            $new = FatUtility::convertToType($obj, FatUtility::VAR_STRING);
        }

        return $new;
    }

    public static function displayBadgeCount($totalCount, $maxValue = 99)
    {
        if ($totalCount > $maxValue) {
            return $maxValue.'+';
        }
        return $totalCount;
    }

    public static function replaceStringData($str, $replacements = array(), $replaceTags = false)
    {
        foreach ($replacements as $key => $val) {
            if ($replaceTags) {
                $val = strip_tags($val);
            }
            $str = str_replace($key, $val, $str);
            $str = str_replace(strtolower($key), $val, $str);
            $str = str_replace(strtoupper($key), $val, $str);
        }
        return $str;
    }

    public static function getUrlTypeData($url)
    {
        if (strpos($url, "?") !== false) {
            $url = str_replace('?', '/?', $url);
        }

        $originalUrl = $url;
        $url = preg_replace('/https:/', 'http:', $url, 1);
        /* [ Check url rewritten by the system and "/" discarded in url rewrite*/
        $systemUrl = CommonHelper::generateFullUrl();
        $systemUrl = preg_replace('/https:/', 'http:', $systemUrl, 1);
        $customUrl = substr($url, strlen($systemUrl));
        $customUrl = rtrim($customUrl, '/');
        $customUrl = explode('/', $customUrl);
        $srch = UrlRewrite::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addCondition(UrlRewrite::DB_TBL_PREFIX . 'custom', '=', $customUrl[0]);
        $rs = $srch->getResultSet();

        if (!$row = FatApp::getDb()->fetch($rs)) {
            return array(
                'url' => $originalUrl,
                'urlType'=> applicationConstants::URL_TYPE_EXTERNAL
            );
        }

        $url = $row['urlrewrite_original'];
        $arr = explode('/', $url);

        $controller = (isset($arr[0]))?$arr[0]:'';
        array_shift($arr);

        $action = (isset($arr[0]))?$arr[0]:'';
        array_shift($arr);

        $queryString = $arr;

        if ($controller != '' && $action == '') {
            $action = 'index';
        }

        if ($controller == '') {
            $controller = 'Content';
        }

        $recordId = $queryString[0];
        switch ($controller.'/'.$action) {
            case 'category/view':
                $urlType = applicationConstants::URL_TYPE_CATEGORY;
                break;
            case 'brands/view':
                $urlType = applicationConstants::URL_TYPE_BRAND;
                break;
            case 'shops/view':
                $urlType = applicationConstants::URL_TYPE_SHOP;
                break;
            case 'products/view':
                $urlType = applicationConstants::URL_TYPE_PRODUCT;
                break;
            default:
                $recordId = applicationConstants::NO;
                $urlType = applicationConstants::URL_TYPE_EXTERNAL;
                break;
        }

        return array(
            'url' => $url,
            'recordId'=> $recordId,
            'urlType'=> $urlType
        );
    }
}
