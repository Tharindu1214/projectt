<?php
$selected_method = '';
if ($order['order_pmethod_id']) {
    $selected_method.= CommonHelper::displayNotApplicable($adminLangId, $order["pmethod_name"]);
}
if ($order['order_is_wallet_selected'] == applicationConstants::YES) {
    $selected_method.= ($selected_method!='') ? ' + '.Labels::getLabel("LBL_Wallet", $adminLangId) : Labels::getLabel("LBL_Wallet", $adminLangId);
}
if ($order['order_reward_point_used'] > 0) {
    $selected_method.= ($selected_method!='') ? ' + '.Labels::getLabel("LBL_Rewards", $adminLangId) : Labels::getLabel("LBL_Rewards", $adminLangId);
}
?>
<div class="page">
    <div class="container container-fluid">
        <div class="row">
           <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Order_Detail', $adminLangId); ?></h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Customer_Order_Detail', $adminLangId); ?></h4>
                        <?php
                            $ul = new HtmlElement("ul", array("class"=>"actions actions--centered"));
                            $li = $ul->appendElement("li", array('class'=>'droplink'));
                            $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                            $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                            $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));
                            $innerLi=$innerUl->appendElement('li');

                            $innerLi->appendElement('a', array('href'=>CommonHelper::generateUrl('Orders'),'class'=>'button small green redirect--js','title'=>Labels::getLabel('LBL_Back_To_Orders', $adminLangId)), Labels::getLabel('LBL_Back_To_Orders', $adminLangId), true);
                            echo $ul->getHtml();
                        ?>
                    </div>
                    <div class="sectionbody">
                        <table class="table table--details">
                            <tr>
                              <td><strong><?php echo Labels::getLabel('LBL_Order/Invoice_ID', $adminLangId); ?>:</strong> <?php echo $order["order_id"]; ?></td>
                              <td><strong><?php echo Labels::getLabel('LBL_Order_Date', $adminLangId); ?>: </strong> <?php echo FatDate::format($order['order_date_added'], true, true, FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get())); ?></td>
                              <td><strong><?php echo Labels::getLabel('LBL_Payment_Status', $adminLangId); ?>:</strong> <?php echo Orders::getOrderPaymentStatusArr($adminLangId)[$order['order_is_paid']];
                                if ('' != $order['pmethod_name'] && 'CashOnDelivery' == $order['pmethod_code']) {
                                  echo ' ('.$order['pmethod_name'].' )';
                                }
                                ?>
                                </td>
                            </tr>
                            <tr>
                              <td><strong><?php echo Labels::getLabel('LBL_Customer', $adminLangId); ?>: </strong> <?php echo $order["buyer_user_name"]?></td>
                              <td><strong><?php echo Labels::getLabel('LBL_Payment_Method', $adminLangId); ?>:</strong> <?php echo $selected_method; ?></td>
                              <td><strong><?php echo Labels::getLabel('LBL_Site_Commission', $adminLangId); ?>:</strong> <?php echo CommonHelper::displayMoneyFormat($order['total_commission']+$order['order_site_commission'], true, true); ?> </td>
                            </tr>
                            <tr>
                              <td><strong><?php echo Labels::getLabel('LBL_Order_Amount', $adminLangId); ?>: </strong> <?php echo CommonHelper::displayMoneyFormat($order["order_net_amount"], true, true); ?> </td>
                              <td><strong><?php echo Labels::getLabel('LBL_Discount', $adminLangId); ?>: </strong>- <?php echo CommonHelper::displayMoneyFormat($order["order_discount_total"], true, true); ?> </td>
                              <td><strong><?php echo Labels::getLabel('LBL_Reward_Point_Discount', $adminLangId);?>: </strong><?php echo CommonHelper::displayMoneyFormat($order["order_reward_point_value"], true, true); ?> </td>
                            </tr>
                            <tr>
                              <td><strong><?php echo Labels::getLabel('LBL_Volume/Loyalty_Discount', $adminLangId);?>: </strong>-<?php echo CommonHelper::displayMoneyFormat($order['order_volume_discount_total'], true, true); ?></td>
                            </tr>
                        </table>
                    </div>
                </section>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Order_Details', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody">
                        <table class="table">
                            <tr>
                                <th>#</td>
                                <th><?php echo Labels::getLabel('LBL_Child_Order_Invoice_ID', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Status', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Product/Shop/Seller_Details', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Shipping_Detail', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Unit_Price', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Qty', $adminLangId); ?></th>
                                
                                <?php /*
                                <th class="text-right"><?php echo Labels::getLabel('LBL_Shipping', 
                                $adminLangId); ?></th> */ ?>

                                <th><?php echo Labels::getLabel('LBL_Volume/Loyalty_Discount', $adminLangId);?></th>
                                <th class="text-right"><?php echo Labels::getLabel('LBL_Total', $adminLangId); ?></th>
                            </tr>
                            <?php
                            $k = 1;
                            $cartTotal = 0;
                            $shippingTotal = 0;
                            $userWiseWeight = array();

                            // echo '<pre>';
                            // print_r($order);
                            // exit();
                            
                           //get city identifier
                            if($order['shippingAddress']['oua_city'] != ''){
                                $cityIdentifier = $order['shippingAddress']['oua_city'];
                            }else{
                                $cityIdentifier = 'Colombo 01';
                            }
                          
                           $userCityId = Cities::getCityIdByIdentifier($cityIdentifier);
                           
                           foreach ($order["products"] as $op) {

                                // $shippingCost = CommonHelper::orderProductAmount($op, 'SHIPPING');
                                $shippingCost = 0;
                                $volumeDiscount = CommonHelper::orderProductAmount($op, 'VOLUME_DISCOUNT');
                                $total = CommonHelper::orderProductAmount($op, 'cart_total') + $shippingCost+$volumeDiscount;
                                $cartTotal = $cartTotal + CommonHelper::orderProductAmount($op, 'cart_total');
                                
                               /* // default shipping cost
                                $shippingTotal = $shippingTotal + CommonHelper::orderProductAmount($op, 'shipping'); */ 
                            ?>
                            <tr>
                                <td><?php echo $k; ?></td>
                                <td><?php echo $op['op_invoice_number']; ?></td>
                                <td><?php echo $op['orderstatus_name']; ?></td>
                                <td><?php
                                $txt = '';
                                if ($op['op_selprod_title'] != '') {
                                    $txt .= $op['op_selprod_title'].'<br/>';
                                }
                                $txt .= $op['op_product_name'];
                                $txt .= '<br/>'.Labels::getLabel('LBL_Brand', $adminLangId).': '.$op['op_brand_name'];
                                if ($op['op_selprod_options'] != '') {
                                    $txt .= ' | ' . $op['op_selprod_options'];
                                }
                                if ($op['op_selprod_sku'] != '') {
                                    $txt .= '<br/>'.Labels::getLabel('LBL_SKU', $adminLangId).': ' . $op['op_selprod_sku'];
                                }
                                if ($op['op_product_model'] != '') {
                                    $txt .= '<br/>'.Labels::getLabel('LBL_Model', $adminLangId).':  ' . $op['op_product_model'];
                                }
                                $txt .= '<br/><strong>'.Labels::getLabel('LBL_Shop_Detail', $adminLangId).':</strong><br/>'.Labels::getLabel('LBL_Shop_Name', $adminLangId).': '.$op['op_shop_name'];
                                $txt .= '<br/>'.Labels::getLabel('LBL_Seller_Name', $adminLangId).': '.$op['op_shop_owner_name'].' <br/>'.Labels::getLabel('LBL_Seller_Email_Id', $adminLangId).': '. $op['op_shop_owner_email'];
                                if ($op['op_shop_owner_phone'] != '') {
                                    $txt .= '<br/>'.Labels::getLabel('LBL_Seller_Phone', $adminLangId).': '.$op['op_shop_owner_phone'];
                                }
                                echo $txt; ?></td>
                                <td><strong><?php echo Labels::getLabel('LBL_Shipping_Class', $adminLangId); ?>: </strong><?php echo CommonHelper::displayNotApplicable($adminLangId, $op["op_shipping_duration_name"]); ?><br/>
                                <strong><?php echo Labels::getLabel('LBL_Duration:', $adminLangId); ?> </strong><?php echo CommonHelper::displayNotApplicable($adminLangId, $op["op_shipping_durations"]); ?></td>

                                <td><?php echo CommonHelper::displayMoneyFormat($op["op_unit_price"], true, true); ?></td>
                                <td><?php echo $op['op_qty']; ?></td>
                               
                                <?php /*
                                <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($shippingCost, true, true); ?></td> */ ?>


                                 <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($op, 'VOLUME_DISCOUNT')); ?></td>

                                 <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($total, true, true); ?></td>
                            </tr>
                                <?php

                                // ========= Added by Ali (Additioanl Code)
                            if($op['op_product_weight_unit'] == 1){
                                $kg = $op['op_product_weight']/1000;
                            }else if($op['op_product_weight_unit'] == 3){
                                $kg = $op['op_product_weight']/2.20;
                            }else{
                                $kg = $op['op_product_weight'];
                            }

                            //echo $product['product_weight'];
                            $kg = number_format((float)$kg, 2, '.', '');
                            $productId =  explode("_",$op['op_selprod_code']);
                            $productId = $productId[0];
                                
                                if (array_key_exists($op['op_selprod_user_id'],$userWiseWeight)){
                                    if(!checkOverrideShippingCost($op['op_selprod_user_id'], $productId, $userCityId)){
                                        $userWiseWeight[$op['op_selprod_user_id']]['weight'] += $kg*$op['op_qty'];
                                    }
                                    $userWiseWeight[$op['op_selprod_user_id']]['prod'][] = $productId;
                                    $userWiseWeight[$op['op_selprod_user_id']]['qty'][] = $op['op_qty'];
                                }else{
                                    if(!checkOverrideShippingCost($op['op_selprod_user_id'], $productId, $userCityId)){
                                    $userWiseWeight[$op['op_selprod_user_id']]['weight'] = $kg*$op['op_qty'];
                                    }else{
                                        $userWiseWeight[$op['op_selprod_user_id']]['weight'] = 0;
                                    }
                                    $userWiseWeight[$op['op_selprod_user_id']]['prod'][] = $productId;
                                    $userWiseWeight[$op['op_selprod_user_id']]['qty'][] = $op['op_qty'];
                                }

                                // ========= Added by Ali
                                $k++;
                            } 
                            // ========= Added by Ali
                            $totalweightCost = 0;
                           
                            foreach($userWiseWeight as $key => $weight_Arr){
                                $userId = $key;
                                $weight = ceil($weight_Arr['weight']);
                                // echo $weight; 
                            if($weight != 0){
                                $totalweightCost += globleShippingCost($userId, $weight, $userCityId);
                            }
                            foreach($weight_Arr['prod'] as $pKey => $productId){
                                    $totalweightCost += overRideCostCalculate($userId, $productId, $userCityId, $weight_Arr['qty'][$pKey]);
                                }
                            }
                           $shippingTotal = $totalweightCost;
                            // ========= Added by Ali
                            ?>
                            <tr>
                                <td colspan="8" class="text-right"><?php echo Labels::getLabel('LBL_Cart_Total', $adminLangId); ?></td>
                                <td class="text-right" colspan="2"><?php echo CommonHelper::displayMoneyFormat($cartTotal, true, true); ?></th>
                            </tr>
                            <tr>
                                <td colspan="8" class="text-right"><?php echo Labels::getLabel('LBL_Delivery/Shipping', $adminLangId); ?></td>
                                <td class="text-right" colspan="2">+<?php echo CommonHelper::displayMoneyFormat($shippingTotal, true, true); ?></td>
                            </tr>
                            <tr>
                                <td colspan="8" class="text-right"><?php echo Labels::getLabel('LBL_Tax', $adminLangId); ?></td>
                                <td class="text-right" colspan="2">+<?php echo CommonHelper::displayMoneyFormat($order['order_tax_charged'], true, true); ?></td>
                            </tr>
                            <?php if ($order['order_discount_total'] > 0) {?>
                            <tr>
                                <td colspan="8" class="text-right"><?php echo Labels::getLabel('LBL_Discount', $adminLangId); ?></td>
                                <td class="text-right" colspan="2">-<?php echo CommonHelper::displayMoneyFormat($order['order_discount_total'], true, true); ?></td>
                            </tr>
                            <?php }?>
                            <?php if ($order['order_reward_point_value'] > 0) {?>
                            <tr>
                                <td colspan="8" class="text-right"><?php echo Labels::getLabel('LBL_Reward_Point_Discount', $adminLangId);?></td>
                                <td class="text-right" colspan="2">-<?php echo CommonHelper::displayMoneyFormat($order['order_reward_point_value'], true, true); ?></td>
                            </tr>
                            <?php }?>
                            <?php if ($order['order_volume_discount_total'] > 0) {?>
                            <tr>
                                <td colspan="8" class="text-right"><?php echo Labels::getLabel('LBL_Volume/Loyalty_Discount', $adminLangId);?></td>
                                <td class="text-right" colspan="2">-<?php echo CommonHelper::displayMoneyFormat($order['order_volume_discount_total'], true, true); ?></td>
                            </tr>
                            <?php }?>
                            <tr>
                                <td colspan="8" class="text-right"><strong><?php echo Labels::getLabel('LBL_Order_Total', $adminLangId); ?></strong></td>
                                <td class="text-right" colspan="2"><strong><?php echo CommonHelper::displayMoneyFormat($order['order_net_amount'], true, true); ?></strong></td>
                            </tr>
                        </table>
                    </div>
            </section>
                <div class="row row--cols-group">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <section class="section">
                            <div class="sectionhead">
                                <h4><?php echo Labels::getLabel('LBL_Customer_Details', $adminLangId); ?></h4>
                            </div>
                            <div class="row space">
                                <div class="address-group">
                                    <h5><?php echo Labels::getLabel('LBL_Customer_Details', $adminLangId); ?></h5>
                                    <p><strong><?php echo Labels::getLabel('LBL_Name', $adminLangId); ?>: </strong><?php echo $order["buyer_user_name"]?><br><strong><?php echo Labels::getLabel('LBL_Email', $adminLangId); ?>: </strong><?php echo $order['buyer_email']; ?><br><strong><?php echo Labels::getLabel('LBL_Phone_Number', $adminLangId); ?>:</strong> <?php echo CommonHelper::displayNotApplicable($adminLangId, $order['buyer_phone']); ?></p>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <section class="section">
                            <div class="sectionhead">
                                <h4><?php echo Labels::getLabel('LBL_Billing_/_Shipping_Details', $adminLangId); ?></h4>
                            </div>
                            <div class="row space">
                                 <div class="col-lg-6 col-md-6 col-sm-12">
                                    <h5><?php echo Labels::getLabel('LBL_Billing_Details', $adminLangId); ?> </h5>
                                    <p><strong><?php echo $order['billingAddress']['oua_name']; ?></strong><br>
                                    <?php
                                    $billingAddress = '';
                                    if ($order['billingAddress']['oua_address1']!='') {
                                        $billingAddress.=$order['billingAddress']['oua_address1'].'<br>';
                                    }

                                    if ($order['billingAddress']['oua_address2']!='') {
                                        $billingAddress.=$order['billingAddress']['oua_address2'].'<br>';
                                    }

                                    if ($order['billingAddress']['oua_city']!='') {
                                        $billingAddress.=$order['billingAddress']['oua_city'].',';
                                    }

                                    if ($order['billingAddress']['oua_zip']!='') {
                                        $billingAddress .= ' '.$order['billingAddress']['oua_state'];
                                    }

                                    if ($order['billingAddress']['oua_zip']!='') {
                                        $billingAddress.= '-'.$order['billingAddress']['oua_zip'];
                                    }

                                    if ($order['billingAddress']['oua_phone']!='') {
                                        $billingAddress.= '<br>Phone: '.$order['billingAddress']['oua_phone'];
                                    }
                                    echo $billingAddress;
                                    ?> </p>
                                </div>
                                <?php if (!empty($order['shippingAddress'])) {?>
                                 <div class="col-lg-6 col-md-6 col-sm-12">
                                    <h5><?php echo Labels::getLabel('LBL_Shipping_Details', $adminLangId); ?></h5>
                                    <p><strong><?php echo $order['shippingAddress']['oua_name'];?></strong><br>
                                    <?php
                                    $shippingAddress = '';
                                    if ($order['shippingAddress']['oua_address1']!='') {
                                        $shippingAddress.=$order['shippingAddress']['oua_address1'].'<br>';
                                    }

                                    if ($order['shippingAddress']['oua_address2']!='') {
                                        $shippingAddress.=$order['shippingAddress']['oua_address2'].'<br>';
                                    }

                                    if ($order['shippingAddress']['oua_city']!='') {
                                        $shippingAddress.=$order['shippingAddress']['oua_city'].',';
                                    }

                                    if ($order['shippingAddress']['oua_zip']!='') {
                                        $shippingAddress .= ' '.$order['shippingAddress']['oua_state'];
                                    }

                                    if ($order['shippingAddress']['oua_zip']!='') {
                                        $shippingAddress.= '-'.$order['shippingAddress']['oua_zip'];
                                    }

                                    if ($order['shippingAddress']['oua_phone']!='') {
                                        $shippingAddress.= '<br>Phone: '.$order['shippingAddress']['oua_phone'];
                                    }

                                    echo $shippingAddress; ?>

                                    </p>
                                </div>
                                <?php } ?>
                            </div>
                        </section>
                    </div>
                </div>
                <?php if (count($order["comments"])>0) { ?>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Order_Status_History', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th width="10%"><?php echo Labels::getLabel('LBL_Date_Added', $adminLangId); ?></th>
                                    <th width="15%"><?php echo Labels::getLabel('LBL_Customer_Notified', $adminLangId); ?></th>
                                    <th width="15%"><?php echo Labels::getLabel('LBL_Payment_Status', $adminLangId); ?></th>
                                    <th width="60%"><?php echo Labels::getLabel('LBL_Comments', $adminLangId); ?></th>
                                </tr>
                                <?php foreach ($order["comments"] as $key => $row) {?>
                                <tr>
                                    <td><?php echo FatDate::format($row['oshistory_date_added']);?></td>
                                    <td><?php echo $yesNoArr[$row['oshistory_customer_notified']];?></td>
                                    <td><?php echo ($row['oshistory_orderstatus_id']>0)?$orderStatuses[$row['oshistory_orderstatus_id']]:CommonHelper::displayNotApplicable($adminLangId, '');?></td>
                                    <td><div class="break-me"><?php echo nl2br($row['oshistory_comments']);?></div></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <?php } ?>
            <?php if (!empty($order['payments'])) {?>
            <section class="section">
                <div class="sectionhead">
                    <h4><?php echo Labels::getLabel('LBL_Order_Payment_History', $adminLangId); ?></h4>
                </div>
                <div class="sectionbody">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th width="10%"><?php echo Labels::getLabel('LBL_Date_Added', $adminLangId); ?></th>
                                <th width="10%"><?php echo Labels::getLabel('LBL_Txn_ID', $adminLangId); ?></th>
                                <th width="15%"><?php echo Labels::getLabel('LBL_Payment_Method', $adminLangId); ?></th>
                                <th width="10%"><?php echo Labels::getLabel('LBL_Amount', $adminLangId); ?></th>
                                <th width="15%"><?php echo Labels::getLabel('LBL_Comments', $adminLangId); ?></th>
                                <th width="40%"><?php echo Labels::getLabel('LBL_Gateway_Response', $adminLangId); ?></th>
                            </tr>
                            <?php foreach ($order["payments"] as $key => $row) { ?>
                            <tr>
                                <td><?php echo FatDate::format($row['opayment_date']);?></td>
                                <td><?php echo $row['opayment_gateway_txn_id'];?></td>
                                <td><?php echo $row['opayment_method'];?></td>
                                <td><?php echo CommonHelper::displayMoneyFormat($row['opayment_amount'], true, true);?></td>
                                <td><div class="break-me"><?php echo nl2br($row['opayment_comments']);?></div></td>
                                <td><div class="break-me"><?php echo nl2br($row['opayment_gateway_response']);?></div></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php }?>
            <?php if (!$order["order_is_paid"] && $canEdit && 'CashOnDelivery' != $order['pmethod_code']) {?>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Order_Payments', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody space">
                        <?php
                        $frm->setFormTagAttribute('onsubmit', 'updatePayment(this); return(false);');
                        $frm->setFormTagAttribute('class', 'web_form');
                        $frm->developerTags['colClassPrefix'] = 'col-md-';
                        $frm->developerTags['fld_default_col'] = 12;


                        $paymentFld = $frm->getField('opayment_method');
                        $paymentFld->developerTags['col'] = 4;

                        $gatewayFld = $frm->getField('opayment_gateway_txn_id');
                        $gatewayFld->developerTags['col'] = 4;

                        $amountFld = $frm->getField('opayment_amount');
                        $amountFld->developerTags['col'] = 4;

                        $submitFld = $frm->getField('btn_submit');
                        $submitFld->developerTags['col'] = 4;

                        echo $frm->getFormHtml(); ?>
                    </div>
                </section>
            <?php }?>
            </div>
        </div>
    </div>
</div>
<?php 

function checkOverrideShippingCost($userId, $productId, $userCityId){
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

function globleShippingCost($userId, $weight, $userCityId){
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

function overRideCostCalculate($userId, $productId, $userCityId, $qty){
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
?>