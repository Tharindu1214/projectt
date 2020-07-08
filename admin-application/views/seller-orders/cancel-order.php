<div class="page">
    <div class="container container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Order_Details', $adminLangId); ?></h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Seller_Order_Details', $adminLangId); ?></h4>
                        <?php $ul = new HtmlElement("ul", array("class"=>"actions actions--centered"));
                        $li = $ul->appendElement("li", array('class'=>'droplink'));
                        $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                        $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                        $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));
                        $innerLi=$innerUl->appendElement('li');
                        $innerLi->appendElement('a', array('href'=>CommonHelper::generateUrl('SellerOrders'),'class'=>'button small green redirect--js','title'=>Labels::getLabel('LBL_Back_to_Orders', $adminLangId)), Labels::getLabel('LBL_Back_to_Orders', $adminLangId), true);
                        echo $ul->getHtml(); ?>
                    </div>
                    <div class="sectionbody">
                        <table class="table ordertable">
                            <tr>
                                <th><?php echo Labels::getLabel('LBL_Invoice_Id', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Order_Date', $adminLangId); ?> </th>
                                <th><?php echo Labels::getLabel('LBL_Status', $adminLangId); ?> </th>
                                <th><?php /* if ($order["opr_refund_qty"]>0):?>Refund for Qty. [<?php echo $order["opr_refund_qty"]?>] <?php endif; */ ?></th>
                            </tr>
                            <tr>
                                <td><?php echo $order["op_invoice_number"]?></td>
                                <td><?php echo FatDate::format($order["order_date_added"], true)?></td>
                                <td><?php echo $order["orderstatus_name"]?></td>
                                <td><?php /* if ($order["opr_refund_qty"]>0): echo $currencyObj->format($order["opr_total_refund_amount"],$order['order_currency_code'],$row['order_currency_value']); endif; */ ?></td>
                            </tr>
                            <tr>
                                <th><?php echo Labels::getLabel('LBL_Customer/Guest', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Payment_Method', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Commission_Charged', $adminLangId); ?> [<?php echo $order["op_commission_percentage"]?>%]</th>
                                <th></th>
                            </tr>
                            <tr>
                                <td><?php echo $order["buyer_user_name"].' ('.$order['buyer_username'].')'; ?></td>
                                <td><?php echo CommonHelper::displayNotApplicable($adminLangId, $order["pmethod_name"])?></td>
                                <td><?php echo CommonHelper::displayMoneyFormat($order['op_commission_charged'], true, true); ?></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th><?php echo Labels::getLabel('LBL_Cart_Total', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Delivery/Shipping', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_VAT', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Total_Paid', $adminLangId); ?></th>
                            </tr>

                            <tr>
                                <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'CART_TOTAL'), true, true);?></td>
                                <td>+<?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'SHIPPING'), true, true);?></td>
                                <td>+<?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'TAX'), true, true);?></td>
                                <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order), true, true);?></td>
                            </tr>
                        </table>
                    </div>
                </section>


                <div class="row row--cols-group">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <section class="section">
                            <div class="sectionhead">
                                <h4><?php echo Labels::getLabel('LBL_Seller_/_Customer_Details', $adminLangId); ?></h4>
                            </div>
                            <div class="row space">
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <h5><?php echo Labels::getLabel('LBL_Seller_Details', $adminLangId); ?></h5>
                                    <p><strong><?php echo Labels::getLabel('LBL_Shop_Name', $adminLangId); ?>: </strong><?php echo $order["op_shop_name"]?><br><strong><?php echo Labels::getLabel('LBL_Name', $adminLangId); ?>:
                                        </strong><?php echo $order["op_shop_owner_name"]?><br><strong><?php echo Labels::getLabel('LBL_Email_ID', $adminLangId); ?>:</strong>
                                        <?php echo $order["op_shop_owner_email"]?><br><strong><?php echo Labels::getLabel('LBL_Phone', $adminLangId); ?>:</strong><?php echo $order["op_shop_owner_phone"]?></p>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <h5><?php echo Labels::getLabel('LBL_Customer_Details', $adminLangId); ?></h5>
                                    <p><strong><?php echo Labels::getLabel('LBL_Name', $adminLangId); ?>: </strong><?php echo $order["buyer_name"]?><br><strong><?php echo Labels::getLabel('LBL_UserName', $adminLangId); ?>:
                                        </strong><?php echo $order["buyer_username"]; ?><br><strong><?php echo Labels::getLabel('LBL_Email_ID', $adminLangId); ?>:</strong><?php echo $order["buyer_email"]?><br><strong><?php echo Labels::getLabel('LBL_Phone', $adminLangId); ?>:</strong><?php echo $order["buyer_phone"]?>
                                    </p>
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

                                    echo $shippingAddress; ?></p>
                                </div>
                                <?php } ?>
                            </div>
                        </section>
                    </div>
                </div>



                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Order_Details', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody">
                        <table class="table">
                            <tr>
                                <th>#</td>
                                <th><?php echo Labels::getLabel('LBL_Product_Name', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Shipping', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Unit Price', $adminLangId); ?> </th>
                                <th><?php echo Labels::getLabel('LBL_Qty', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Shipping', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Tax', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Total', $adminLangId); ?></th>
                            </tr>
                            <tr>
                                <td>#</td>
                                <td><?php
                            $txt = '';
                            if ($order['op_selprod_title'] != '') {
                                $txt .= $order['op_selprod_title'].'<br/>';
                            }
                            $txt .= $order['op_product_name'];
                            $txt .= '<br/>'.Labels::getLabel('LBL_Brand', $adminLangId).':  '.$order['op_brand_name'];
                            if ($order['op_selprod_options'] != '') {
                                $txt .= ' | ' . $order['op_selprod_options'];
                            }
                            if ($order['op_selprod_sku'] != '') {
                                $txt .= '<br/>'.Labels::getLabel('LBL_SKU', $adminLangId).':   ' . $order['op_selprod_sku'];
                            }
                            if ($order['op_product_model'] != '') {
                                $txt .= '<br/>'.Labels::getLabel('LBL_Model', $adminLangId).':   ' . $order['op_product_model'];
                            }
                            echo $txt;
                            ?></td>
                                <td><strong><?php echo Labels::getLabel('LBL_Shipping_Class', $adminLangId); ?>: </strong><?php echo CommonHelper::displayNotApplicable($adminLangId, $order["op_shipping_duration_name"]); ?><br />
                                    <strong><?php echo Labels::getLabel('LBL_Duration', $adminLangId); ?>: </strong><?php echo CommonHelper::displayNotApplicable($adminLangId, $order["op_shipping_durations"]); ?></td>
                                <td><?php echo CommonHelper::displayMoneyFormat($order["op_unit_price"], true, true); ?></td>
                                <td><?php echo $order["op_qty"]?></td>
                                <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'shipping'), true, true);?></td>
                                <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'TAX'), true, true);?></td>
                                <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order), true, true);?></td>
                            </tr>
                        </table>
                    </div>
                </section>



                <?php if (!$notEligible) { ?>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Reason_For_Cancellation', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody space">
                        <?php
                    $frm->setFormTagAttribute('onsubmit', 'cancelReason(this); return(false);');
                    $frm->setFormTagAttribute('class', 'web_form');
                    $frm->developerTags['colClassPrefix'] = 'col-md-';
                    $frm->developerTags['fld_default_col'] = 12;
                    echo $frm->getFormHtml();?>
                    </div>
                </section>
                <?php }?>

            </div>
        </div>
    </div>
</div>
