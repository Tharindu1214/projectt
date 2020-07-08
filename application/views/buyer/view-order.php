<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $canCancelOrder = true;
    $canReturnRefund = true;
    $canReviewOrders = false;
if (true == $primaryOrder) {
    if ($childOrderDetail['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
        $canCancelOrder = (in_array($childOrderDetail["op_status_id"], (array)Orders::getBuyerAllowedOrderCancellationStatuses(true)));
        $canReturnRefund = (in_array($childOrderDetail["op_status_id"], (array)Orders::getBuyerAllowedOrderReturnStatuses(true)));
    } else {
        $canCancelOrder = (in_array($childOrderDetail["op_status_id"], (array)Orders::getBuyerAllowedOrderCancellationStatuses()));
        $canReturnRefund = (in_array($childOrderDetail["op_status_id"], (array)Orders::getBuyerAllowedOrderReturnStatuses()));
    }

    if (in_array($childOrderDetail["op_status_id"], SelProdReview::getBuyerAllowedOrderReviewStatuses())) {
        $canReviewOrders = true;
    }
}
?> <?php if (!$print) {
    ?> <?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?> <?php
} ?>

<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <?php if (!$print) { ?>
        <div class="content-header row justify-content-between mb-3">
            <div class="col-md-auto"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title no-print"><?php echo Labels::getLabel('LBL_Order_Details', $siteLangId); ?></h2>
            </div>
            <?php if (true == $primaryOrder) { ?>
            <div class="col-md-auto">
                <div class="btn-group">
                    <?php if (!$print) { ?>
                    <ul class="actions no-print">
                        <?php if ($canCancelOrder) { ?>
                        <li>
                            <a href="<?php echo CommonHelper::generateUrl('Buyer', 'orderCancellationRequest', array($childOrderDetail['op_id'])); ?>" class="icn-highlighted" title="<?php echo Labels::getLabel('LBL_Cancel_Order', $siteLangId); ?>"><i
                                    class="fa fa-close"></i></a>
                        </li>
                        <?php }
                            if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0) && $canReviewOrders) {
                                ?> <li>
                            <a href="<?php echo CommonHelper::generateUrl('Buyer', 'orderFeedback', array($childOrderDetail['op_id'])); ?>" class="icn-highlighted" title="<?php echo Labels::getLabel('LBL_Feedback', $siteLangId); ?>"><i
                                    class="fa fa-star"></i></a>
                        </li> <?php
                            }
                            if ($canReturnRefund) { ?>
                        <li>
                            <a href="<?php echo CommonHelper::generateUrl('Buyer', 'orderReturnRequest', array($childOrderDetail['op_id'])); ?>" class="icn-highlighted" title="<?php echo Labels::getLabel('LBL_Refund', $siteLangId); ?>"><i
                                    class="fa fa-dollar"></i></a>
                        </li>
                        <?php } ?>
                    </ul>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
        <div class="content-body">
            <div class="cards">
                <div class="cards-header p-4">
                    <h5 class="cards-title"> <?php echo Labels::getLabel('LBL_Order_Details', $siteLangId);?> </h5>
                    <?php if (!$print) { ?>
                    <div class="action">
                        <div class="">
                            <iframe src="<?php echo Fatutility::generateUrl('buyer', 'viewOrder', $urlParts) . '/print'; ?>" name="frame" style="display:none"></iframe>
                            <a href="javascript:void(0)" onclick="frames['frame'].print()" class="btn btn--primary btn--sm no-print"><?php echo Labels::getLabel('LBL_Print', $siteLangId); ?></a>
                            <a href="<?php echo CommonHelper::generateUrl('Buyer', 'orders'); ?>" class="btn btn--primary-border btn--sm no-print"><?php echo Labels::getLabel('LBL_Back_to_order', $siteLangId); ?></a>
                        </div>
                    </div> <?php
                    } ?>
                </div>
                <div class="cards-content pl-4 pr-4 ">
                    <?php if ($primaryOrder) { ?>
                        <div class="row">
                        <div class="col-lg-6 col-md-6 mb-4">
                            <div class="info--order">
                                <p><strong><?php echo Labels::getLabel('LBL_Customer_Name', $siteLangId); ?>: </strong><?php echo $childOrderDetail['user_name']; ?></p> <?php
                                $paymentMethodName = $childOrderDetail['pmethod_name']?:$childOrderDetail['pmethod_identifier'];
                                if ($childOrderDetail['order_pmethod_id'] > 0 && $childOrderDetail['order_is_wallet_selected'] > 0) {
                                    $paymentMethodName .= ' + ';
                                }
                                if ($childOrderDetail['order_is_wallet_selected'] > 0) {
                                    $paymentMethodName .= Labels::getLabel("LBL_Wallet", $siteLangId);
                                } ?> <p><strong><?php echo Labels::getLabel('LBL_Payment_Method', $siteLangId); ?>: </strong><?php echo $paymentMethodName; ?></p>
                                <p><strong><?php echo Labels::getLabel('LBL_Payment_Status', $siteLangId); ?>: </strong>
                                <?php echo Orders::getOrderPaymentStatusArr($siteLangId)[$childOrderDetail['order_is_paid']];
                                if ('' != $childOrderDetail['pmethod_name'] && 'CashOnDelivery' == $childOrderDetail['pmethod_code']) {
                                    echo ' ('.$childOrderDetail['pmethod_name'].' )';
                                } ?>
                                <?php /*echo $orderStatuses[$childOrderDetail['op_status_id']];*/ ?></p>
                                <p><strong><?php echo Labels::getLabel('LBL_Cart_Total', $siteLangId); ?>: </strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrderDetail, 'CART_TOTAL')); ?></p>
                                <p><strong><?php echo Labels::getLabel('LBL_Delivery', $siteLangId); ?>: </strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrderDetail, 'SHIPPING')); ?></p>
                                <p><strong><?php echo Labels::getLabel('LBL_Tax', $siteLangId); ?>:</strong> <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrderDetail, 'TAX')); ?></p>
                                <p><strong><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?>:</strong> <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrderDetail, 'DISCOUNT')); ?></p> <?php $volumeDiscount = CommonHelper::orderProductAmount($childOrderDetail, 'VOLUME_DISCOUNT');
                        if ($volumeDiscount) {
                            ?> <p><strong><?php echo Labels::getLabel('LBL_Volume/Loyalty_Discount', $siteLangId); ?>:</strong> <?php echo CommonHelper::displayMoneyFormat($volumeDiscount); ?></p> <?php
                        } ?> <?php $rewardPointDiscount = CommonHelper::orderProductAmount($childOrderDetail, 'REWARDPOINT');
                        if ($rewardPointDiscount != 0) {
                            ?> <p><strong><?php echo Labels::getLabel('LBL_Reward_Point_Discount', $siteLangId); ?>:</strong> <?php echo CommonHelper::displayMoneyFormat($rewardPointDiscount); ?></p> <?php
                        } ?> <p><strong><?php echo Labels::getLabel('LBL_Order_Total', $siteLangId); ?>: </strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrderDetail)); ?></p>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 mb-4">
                            <div class="info--order">
                                <p><strong><?php echo Labels::getLabel('LBL_Invoice', $siteLangId); ?> #: </strong><?php echo $childOrderDetail['op_invoice_number']; ?></p>
                                <p><strong><?php echo Labels::getLabel('LBL_Date', $siteLangId); ?>: </strong><?php echo FatDate::format($childOrderDetail['order_date_added']); ?></p>
                            </div>
                        </div>
                    </div> <?php
                    } else {
                        ?> <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6">
                            <p><strong><?php echo Labels::getLabel('LBL_Order', $siteLangId); ?>: </strong><?php echo $orderDetail['order_id']; ?></p>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6">
                            <div class="info--order">
                                <p><strong><?php echo Labels::getLabel('LBL_Date', $siteLangId); ?>: </strong><?php echo FatDate::format($orderDetail['order_date_added']); ?></p>
                            </div>
                        </div>
                    </div> <?php
                    }?> <table class="table  table--orders">
                        <tbody>
                            <tr class="">
                                <th><?php echo Labels::getLabel('LBL_Order_Particulars', $siteLangId);?></th>
                                <?php if (!$print) { ?>
                                <th class="no-print"></th>
                                <?php } ?>
                                <th><?php echo Labels::getLabel('LBL_Qty', $siteLangId);?></th>
                                <th><?php echo Labels::getLabel('LBL_Price', $siteLangId);?></th>
                                <th><?php echo Labels::getLabel('LBL_Shipping_Charges', $siteLangId);?></th>
                                <th><?php echo Labels::getLabel('LBL_Volume/Loyalty_Discount', $siteLangId);?></th>
                                <th> <?php echo Labels::getLabel('LBL_Tax_Charges', $siteLangId);?></th>
                                <th> <?php echo Labels::getLabel('LBL_Reward_Point_Discount', $siteLangId);?></th>
                                <th><?php echo Labels::getLabel('LBL_Total', $siteLangId);?></th>
                            </tr> <?php
                            $cartTotal = 0;
                            $shippingCharges = 0;
                            $total = 0;
                            if ($primaryOrder) {
                                $arr[] = $childOrderDetail;
                            } else {
                                $arr = $childOrderDetail;
                            }
                            foreach ($arr as $childOrder) {
                                $cartTotal = $cartTotal + CommonHelper::orderProductAmount($childOrder, 'cart_total');
                                $shippingCharges = $shippingCharges + CommonHelper::orderProductAmount($childOrder, 'shipping');
                                $volumeDiscount = CommonHelper::orderProductAmount($childOrder, 'VOLUME_DISCOUNT');
                                $rewardPointDiscount = CommonHelper::orderProductAmount($childOrder, 'REWARDPOINT'); ?>
                                <tr>
                                    <?php if (!$print) { ?>
                                        <td class="no-print">
                                            <div class="pic--cell-left"> <?php
                                            $prodOrBatchUrl = 'javascript:void(0)';
                                            if ($childOrder['op_is_batch']) {
                                                $prodOrBatchUrl = CommonHelper::generateUrl('Products', 'batch', array($childOrder['op_selprod_id']));
                                                $prodOrBatchImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'BatchProduct', array($childOrder['op_selprod_id'],$siteLangId, "SMALL"), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                            } else {
                                                if (Product::verifyProductIsValid($childOrder['op_selprod_id']) == true) {
                                                    $prodOrBatchUrl = CommonHelper::generateUrl('Products', 'view', array($childOrder['op_selprod_id']));
                                                }
                                                $prodOrBatchImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'product', array($childOrder['selprod_product_id'], "SMALL", $childOrder['op_selprod_id'], 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                            } ?> <figure class="item__pic"><a href="<?php echo $prodOrBatchUrl; ?>"><img src="<?php echo $prodOrBatchImgUrl; ?>" title="<?php echo $childOrder['op_product_name']; ?>" alt="<?php echo $childOrder['op_product_name']; ?>"></a></figure>
                                                </div>
                                        </td>
                                    <?php } ?>
                                    <td>
                                        <div class="item__description">
                                            <?php if ($childOrder['op_selprod_title']!='') { ?>
                                            <div class="item__title">
                                                <a title="<?php echo $childOrder['op_selprod_title']; ?>" href="<?php echo $prodOrBatchUrl; ?>"><?php echo $childOrder['op_selprod_title'].'<br/>'; ?></a>
                                            </div>
                                            <div class="item__category"><?php echo $childOrder['op_product_name']; ?></div>
                                            <?php } else { ?>
                                                <div class="item__category">
                                                    <a title="<?php echo $childOrder['op_product_name']; ?>"
                                                href="<?php echo CommonHelper::generateUrl('Products', 'view', array($childOrder['op_selprod_id'])); ?>"><?php echo $childOrder['op_product_name']; ?> </a>
                                                </div>
                                            <?php } ?>
                                            <div class="item__brand"><?php echo Labels::getLabel('Lbl_Brand', $siteLangId)?>: <?php echo CommonHelper::displayNotApplicable($siteLangId, $childOrder['op_brand_name']); ?></div>
                                            <?php if ($childOrder['op_selprod_options'] != '') { ?>
                                                <div class="item__specification"><?php echo $childOrder['op_selprod_options']; ?></div>
                                            <?php } ?>
                                            <div class="item__sold_by"><?php echo Labels::getLabel('LBL_Sold_By', $siteLangId).': '.$childOrder['op_shop_name']; ?></div>
                                            <?php if ($childOrder['op_shipping_duration_name'] != '') { ?>
                                                <div class="item__shipping"><?php echo Labels::getLabel('LBL_Shipping_Method', $siteLangId); ?>: <?php echo $childOrder['op_shipping_durations'].'-'. $childOrder['op_shipping_duration_name']; ?></div>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <!--<td style="width:20%;" ><?php echo $childOrder['op_shipping_durations'].'-'. $childOrder['op_shipping_duration_name']; ?></td>-->
                                    <td><?php echo $childOrder['op_qty']; ?></td>
                                    <td><?php echo CommonHelper::displayMoneyFormat($childOrder['op_unit_price']); ?></td>
                                    <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'shipping')); ?></td>
                                    <td><?php echo CommonHelper::displayMoneyFormat($volumeDiscount); ?></td>
                                    <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'tax')); ?></td>
                                    <td><?php echo CommonHelper::displayMoneyFormat($rewardPointDiscount); ?></td>
                                    <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder)); ?></td>
                                </tr>
                            <?php }
                            if (!$primaryOrder) { ?>
                                <tr>
                                    <td colspan="8"><?php echo Labels::getLabel('Lbl_Cart_Total', $siteLangId)?></td>
                                    <td><?php echo CommonHelper::displayMoneyFormat($cartTotal); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="8"><?php echo Labels::getLabel('LBL_Shipping_Charges', $siteLangId)?></td>
                                    <td><?php echo CommonHelper::displayMoneyFormat($shippingCharges); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="8"><?php echo Labels::getLabel('LBL_Tax_Charges', $siteLangId)?></td>
                                    <td><?php echo CommonHelper::displayMoneyFormat($orderDetail['order_tax_charged']); ?></td>
                                </tr>
                                <?php if ($orderDetail['order_discount_total']) { ?>
                                <tr>
                                    <td colspan="8"><?php echo Labels::getLabel('LBL_Discount', $siteLangId)?></td>
                                    <td>-<?php echo CommonHelper::displayMoneyFormat($orderDetail['order_discount_total']); ?></td>
                                </tr>
                                <?php } ?>
                                <?php if ($orderDetail['order_volume_discount_total']) { ?>
                                <tr>
                                    <td colspan="8"><?php echo Labels::getLabel('LBL_Volume/Loyalty_Discount', $siteLangId)?></td>
                                    <td>-<?php echo CommonHelper::displayMoneyFormat($orderDetail['order_volume_discount_total']); ?></td>
                                </tr>
                                <?php } ?>
                                <tr>
                                    <td colspan="8"><?php echo Labels::getLabel('LBL_Total', $siteLangId)?></td>
                                    <td><?php echo CommonHelper::displayMoneyFormat($orderDetail['order_net_amount']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <div class="divider">
                    </div>
                    <div class="gap"></div>
                    <div class="gap"></div>
                    <div class="row">
                        <div class="col-lg-6 col-md-6 mb-4">
                            <h5><?php echo Labels::getLabel('LBL_Billing_Details', $siteLangId);?></h5> <?php $billingAddress = $orderDetail['billingAddress']['oua_name'].'<br>';
                            if ($orderDetail['billingAddress']['oua_address1']!='') {
                                $billingAddress.=$orderDetail['billingAddress']['oua_address1'].'<br>';
                            }

                            if ($orderDetail['billingAddress']['oua_address2']!='') {
                                $billingAddress.=$orderDetail['billingAddress']['oua_address2'].'<br>';
                            }

                            if ($orderDetail['billingAddress']['oua_city']!='') {
                                $billingAddress.=$orderDetail['billingAddress']['oua_city'].',';
                            }

                            if ($orderDetail['billingAddress']['oua_zip']!='') {
                                $billingAddress.=$orderDetail['billingAddress']['oua_state'];
                            }

                            if ($orderDetail['billingAddress']['oua_zip']!='') {
                                $billingAddress.= '-'.$orderDetail['billingAddress']['oua_zip'];
                            }

                            if ($orderDetail['billingAddress']['oua_phone']!='') {
                                $billingAddress.= '<br>'.$orderDetail['billingAddress']['oua_phone'];
                            }
                        ?> <div class="info--order">
                                <p><?php echo $billingAddress;?></p>
                            </div>
                        </div>
                        <?php if (!empty($orderDetail['shippingAddress']) && $productType != Product::PRODUCT_TYPE_DIGITAL) {?>
                            <div class="col-lg-6 col-md-6 mb-4">
                                <h5><?php echo Labels::getLabel('LBL_Shipping_Details', $siteLangId); ?></h5> <?php $shippingAddress = $orderDetail['shippingAddress']['oua_name'].'<br>';
                                if ($orderDetail['shippingAddress']['oua_address1']!='') {
                                    $shippingAddress.=$orderDetail['shippingAddress']['oua_address1'].'<br>';
                                }

                                if ($orderDetail['shippingAddress']['oua_address2']!='') {
                                    $shippingAddress.=$orderDetail['shippingAddress']['oua_address2'].'<br>';
                                }

                                if ($orderDetail['shippingAddress']['oua_city']!='') {
                                    $shippingAddress.=$orderDetail['shippingAddress']['oua_city'].',';
                                }

                                if ($orderDetail['shippingAddress']['oua_zip']!='') {
                                    $shippingAddress.=$orderDetail['shippingAddress']['oua_state'];
                                }

                                if ($orderDetail['shippingAddress']['oua_zip']!='') {
                                    $shippingAddress.= '-'.$orderDetail['shippingAddress']['oua_zip'];
                                }

                                if ($orderDetail['shippingAddress']['oua_phone']!='') {
                                    $shippingAddress.= '<br>'.$orderDetail['shippingAddress']['oua_phone'];
                                } ?> <div class="info--order">
                                    <p><?php echo $shippingAddress; ?></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div> <?php if (!empty($orderDetail['comments'])) {
                            ?> <div class="gap">
                    </div>
                    <div class="section--repeated">
                        <h5><?php echo Labels::getLabel('LBL_Posted_Comments', $siteLangId); ?></h5>
                        <table class="table  table--orders">
                            <tbody>
                                <tr class="">
                                    <th><?php echo Labels::getLabel('LBL_Date_Added', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Customer_Notified', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Status', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Comments', $siteLangId); ?></th>
                                </tr> <?php foreach ($orderDetail['comments'] as $row) {
                                ?> <tr>
                                    <td><?php echo FatDate::format($row['oshistory_date_added']); ?></td>
                                    <td><?php echo $yesNoArr[$row['oshistory_customer_notified']]; ?></td>
                                    <td><?php echo ($row['oshistory_orderstatus_id']>0)?$orderStatuses[$row['oshistory_orderstatus_id']]:CommonHelper::displayNotApplicable($siteLangId, '');
                                echo ($row['oshistory_tracking_number'])? ': '.Labels::getLabel('LBL_Tracking_Number', $siteLangId).' '.$row['oshistory_tracking_number']." VIA <em>".$row['op_shipping_duration_name']."</em>" :'' ?></td>
                                    <td><?php echo !empty(trim(($row['oshistory_comments']))) ? nl2br($row['oshistory_comments']) : Labels::getLabel('LBL_N/A', $siteLangId) ; ?></td>
                                </tr> <?php
                            } ?>
                            </tbody>
                        </table>
                    </div> <?php
                        } ?> <?php if (!empty($orderDetail['payments'])) {
                            ?> <span class="gap"></span>
                    <div class="section--repeated">
                        <h5><?php echo Labels::getLabel('LBL_Payment_History', $siteLangId); ?></h5>
                        <table class="table table--orders">
                            <tbody>
                                <tr class="">
                                    <th><?php echo Labels::getLabel('LBL_Date_Added', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Txn_Id', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Payment_Method', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Amount', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Comments', $siteLangId); ?></th>
                                </tr> <?php foreach ($orderDetail['payments'] as $row) {
                                ?> <tr>
                                    <td><?php echo FatDate::format($row['opayment_date']); ?></td>
                                    <td><?php echo $row['opayment_gateway_txn_id']; ?></td>
                                    <td><?php echo $row['opayment_method']; ?></td>
                                    <td><?php echo CommonHelper::displayMoneyFormat($row['opayment_amount']); ?></td>
                                    <td><?php echo nl2br($row['opayment_comments']); ?></td>
                                </tr> <?php
                            } ?>
                            </tbody>
                        </table>
                    </div> <?php
                        } ?> <?php if (!empty($digitalDownloads)) {
                            ?> <span class="gap"></span>
                    <div class="section--repeated">
                        <h5><?php echo Labels::getLabel('LBL_Downloads', $siteLangId); ?></h5>
                        <table class="table  table--orders">
                            <tbody>
                                <tr class="">
                                    <th><?php echo Labels::getLabel('LBL_Sr_No', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_File', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Language', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Download_times', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Downloaded_count', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Expired_on', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Action', $siteLangId); ?></th>
                                </tr> <?php $sr_no = 1;
                            foreach ($digitalDownloads as $key=>$row) {
                                $lang_name = Labels::getLabel('LBL_All', $siteLangId);
                                if ($row['afile_lang_id'] > 0) {
                                    $lang_name = $languages[$row['afile_lang_id']];
                                }

                                if ($row['downloadable']) {
                                    $fileName = '<a href="'.CommonHelper::generateUrl('Buyer', 'downloadDigitalFile', array($row['afile_id'],$row['afile_record_id'])).'">'.$row['afile_name'].'</a>';
                                } else {
                                    $fileName = $row['afile_name'];
                                }
                                $downloads = '<li><a href="'.CommonHelper::generateUrl('Buyer', 'downloadDigitalFile', array($row['afile_id'],$row['afile_record_id'])).'"><i class="fa fa-download"></i></a></li>';

                                $expiry = Labels::getLabel('LBL_N/A', $siteLangId) ;
                                if ($row['expiry_date']!='') {
                                    $expiry = FatDate::Format($row['expiry_date']);
                                }

                                $downloadableCount = Labels::getLabel('LBL_N/A', $siteLangId) ;
                                if ($row['downloadable_count'] != -1) {
                                    $downloadableCount = $row['downloadable_count'];
                                } ?> <tr>
                                    <td><?php echo $sr_no; ?></td>
                                    <td><?php echo $fileName; ?></td>
                                    <td><?php echo $lang_name; ?></td>
                                    <td><?php echo $downloadableCount; ?></td>
                                    <td><?php echo $row['afile_downloaded_times']; ?></td>
                                    <td><?php echo $expiry; ?></td>
                                    <td><?php if ($row['downloadable']) {
                                    ?><ul class="actions"><?php echo $downloads; ?></ul><?php
                                } ?></td>
                                </tr> <?php $sr_no++;
                            } ?>
                            </tbody>
                        </table>
                    </div> <?php
                        } ?> <?php if (!empty($digitalDownloadLinks)) {
                            ?> <span class="gap"></span>
                    <div class="section--repeated">
                        <h5><?php echo Labels::getLabel('LBL_Download_Links', $siteLangId); ?></h5>
                        <table class="table  table--orders">
                            <tbody>
                                <tr class="">
                                    <th><?php echo Labels::getLabel('LBL_Sr_No', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Link', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Download_times', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Downloaded_count', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Expired_on', $siteLangId); ?></th>
                                </tr> <?php $sr_no = 1;
                            foreach ($digitalDownloadLinks as $key=>$row) {
                                $expiry = Labels::getLabel('LBL_N/A', $siteLangId) ;
                                if ($row['expiry_date']!='') {
                                    $expiry = FatDate::Format($row['expiry_date']);
                                }

                                $downloadableCount = Labels::getLabel('LBL_N/A', $siteLangId) ;
                                if ($row['downloadable_count'] != -1) {
                                    $downloadableCount = $row['downloadable_count'];
                                }

                                $link = ($row['downloadable']!=1) ? Labels::getLabel('LBL_N/A', $siteLangId) : $row['opddl_downloadable_link'];
                                $linkUrl = ($row['downloadable']!=1) ? 'javascript:void(0)' : $row['opddl_downloadable_link'];
                                $linkOnClick = ($row['downloadable']!=1) ? '' : 'return increaseDownloadedCount('.$row['opddl_link_id'].','.$row['op_id'].'); ';
                                $linkTitle = ($row['downloadable']!=1) ? '' : Labels::getLabel('LBL_Click_to_download', $siteLangId); ?> <tr>
                                    <td><?php echo $sr_no; ?></td>
                                    <td><a target="_blank" onClick="<?php echo $linkOnClick; ?> " href="<?php echo $linkUrl; ?>" data-link="<?php echo $linkUrl; ?>" title="<?php echo $linkTitle; ?>"><?php echo $link; ?></a></td>
                                    <td><?php echo $downloadableCount; ?></td>
                                    <td><?php echo $row['opddl_downloaded_times']; ?></td>
                                    <td><?php echo $expiry; ?></td>
                                </tr> <?php $sr_no++;
                            } ?>
                            </tbody>
                        </table>
                    </div> <?php
                        } ?>
                </div>
            </div>
        </div>
    </div>
</main>
<?php if ($print) {?>
<script>
    $(".sidebar-is-expanded").addClass('sidebar-is-reduced').removeClass('sidebar-is-expanded');
    /*window.print();
    window.onafterprint = function() {
        location.href = history.back();
    }*/
</script>
<?php } ?> <script>
    function increaseDownloadedCount(linkId, opId) {
        fcom.ajax(fcom.makeUrl('buyer', 'downloadDigitalProductFromLink', [linkId, opId]), '', function(t) {
            var ans = $.parseJSON(t);
            if (ans.status == 0) {
                $.systemMessage(ans.msg, 'alert--danger');
                return false;
            }
            /* var dataLink = $(this).attr('data-link');
            window.location.href= dataLink; */
            location.reload();
            return true;
        });
    }
</script>
