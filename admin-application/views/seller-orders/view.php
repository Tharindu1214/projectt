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
                <?php if (!$print) { ?>
                <div class="page__title no-print">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Orders_Details', $adminLangId); ?></h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Seller_Order_Details', $adminLangId); ?></h4>
                        <?php if (!$print) {
                            $ul = new HtmlElement("ul", array("class"=>"actions actions--centered"));
                            $li = $ul->appendElement("li", array('class'=>'droplink'));

                            $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green no-print','title'=>Labels::getLabel('LBL_Back_to_Orders', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                            $innerDiv=$li->appendElement('div', array('class'=>'dropwrap'));
                            $innerUl=$innerDiv->appendElement('ul', array('class'=>'linksvertical'));
                            $innerLi=$innerUl->appendElement('li');

                            $innerLi->appendElement('a', array('href'=>CommonHelper::generateUrl('SellerOrders'),'class'=>'button small green redirect--js no-print','title'=>Labels::getLabel('LBL_Back_to_Orders', $adminLangId)), Labels::getLabel('LBL_Back_to_Orders', $adminLangId), true);
                            $innerLi->appendElement('a', array('href'=> Fatutility::generateUrl('sellerOrders', 'view', $urlParts) . '/print','class'=>'button small green redirect--js no-print','title'=>Labels::getLabel('LBL_Print', $adminLangId)), Labels::getLabel('LBL_Print', $adminLangId), true);

                            echo $ul->getHtml();
                        } ?>
                    </div>
                    <div class="sectionbody">
                        <table class="table table--details">
                            <tr>
                                <td><strong><?php echo Labels::getLabel('LBL_Invoice_Id', $adminLangId); ?>:</strong><?php echo $order["op_invoice_number"]?></td>
                                <td><strong><?php echo Labels::getLabel('LBL_Order_Date', $adminLangId); ?>: </strong><?php echo FatDate::format($order["order_date_added"], true)?></td>
                                <td><strong><?php echo Labels::getLabel('LBL_Status', $adminLangId); ?>:</strong> <?php echo $order["orderstatus_name"]?></td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>
                                    <?php if ($order["op_refund_qty"]>0) {
                                        echo Labels::getLabel('LBL_Refund_for_Qty.', $adminLangId); ?> [<?php echo $order["op_refund_qty"]?>] :
                                                </strong><?php echo CommonHelper::displayMoneyFormat($order["op_refund_amount"], true, true);
                                    }  ?>
                                </td>
                                <td><strong><?php echo Labels::getLabel('LBL_Customer/Guest', $adminLangId); ?>: </strong><?php echo $order["buyer_user_name"].' ('.$order['buyer_username'].')'; ?></td>
                                <td><strong><?php echo Labels::getLabel('LBL_Payment_Method', $adminLangId); ?>:</strong> <?php echo $selected_method;?> </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo Labels::getLabel('LBL_Commission_Charged', $adminLangId); ?>[<?php echo $order["op_commission_percentage"]?>%] + Shipping Charge[3.5%] :
                                    </strong>
                                    <?php 
                                        $Commission_Charged = $order['op_commission_charged'] - $order['op_refund_commission'];

                                        $shipping_charge = CommonHelper::orderProductAmount($order, 'SHIPPING');
                                        echo CommonHelper::displayMoneyFormat(floatval($Commission_Charged) + (floatval($shipping_charge)*0.035), true, true);
                                    ?>
                                </td>
                                <td><strong><?php echo Labels::getLabel('LBL_Cart_Total', $adminLangId); ?>:</strong>
                                <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'CART_TOTAL'), true, true);?> </td>
                                <td><strong>
                                    <?php if ($shippingHanldedBySeller) {
                                        echo Labels::getLabel('LBL_Delivery/Shipping', $adminLangId); ?>:
                                                </strong><?php echo '+'.CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'SHIPPING'), true, true);
                                    } ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>
                                    <?php if ($order['op_tax_collected_by_seller']) {
                                        echo Labels::getLabel('LBL_Tax', $adminLangId); ?>:
                                                </strong><?php  echo '+'.CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'TAX'), true, true);
                                    }?>
                                </td>
                                <td><strong><?php echo Labels::getLabel('LBL_Volume_Discount', $adminLangId); ?></strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'VOLUME_DISCOUNT'), true, true);?> </td>
                                <td><strong><?php echo Labels::getLabel('LBL_Total_Paid', $adminLangId); ?>:</strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'netamount', false, USER::USER_TYPE_SELLER), true, true);?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </section>
                <div class="row row--cols-group">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <section class="section">
                            <div class="sectionhead">
                                <h4><?php echo Labels::getLabel('LBL_Seller/_Customer_Details', $adminLangId); ?></h4>
                            </div>
                            <div class="row space">
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <h5><?php echo Labels::getLabel('LBL_Seller_Details', $adminLangId); ?></h5>
                                    <p><strong><?php echo Labels::getLabel('LBL_Shop_Name', $adminLangId); ?>:</strong><?php echo $order["op_shop_name"]?><br><strong><?php echo Labels::getLabel('LBL_Name', $adminLangId); ?>:
                                        </strong><?php echo $order["op_shop_owner_name"]?><br><strong><?php echo Labels::getLabel('LBL_Email_ID', $adminLangId); ?>:</strong>
                                        <?php echo $order["op_shop_owner_email"]?><br><strong><?php echo Labels::getLabel('LBL_Phone', $adminLangId); ?>:</strong> <?php echo $order["op_shop_owner_phone"]?></p>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <h5><?php echo Labels::getLabel('LBL_Customer_Details', $adminLangId); ?></h5>
                                    <p><strong><?php echo Labels::getLabel('LBL_Name', $adminLangId); ?>: </strong><?php echo $order["buyer_name"]?><br><strong><?php echo Labels::getLabel('LBL_UserName', $adminLangId); ?>:
                                        </strong><?php echo $order["buyer_username"]; ?><br><strong><?php echo Labels::getLabel('LBL_Email_ID', $adminLangId); ?>:</strong><?php echo $order["buyer_email"]?><br><strong><?php echo Labels::getLabel('LBL_Phone', $adminLangId); ?>:</strong>
                                        <?php echo $order["buyer_phone"]?></p>
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
                                    <p><strong><?php echo $order['billingAddress']['oua_name']; ?></strong><br> <?php
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
                                    ?><br>
                                    </p>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <?php if (!empty($order['shippingAddress'])) { ?>
                                        <h5><?php echo Labels::getLabel('LBL_Shipping_Details', $adminLangId); ?></h5>
                                        <p>
                                            <strong>
                                            <?php echo $order['shippingAddress']['oua_name']; ?></strong><br>
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

                                            echo $shippingAddress;
                                    } ?>
                                </div>
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
                                <th>#</th>
                                <th><?php echo Labels::getLabel('LBL_Product_Name', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Shipping', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Unit_Price', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Qty', $adminLangId); ?></th>
                                <?php if ($shippingHanldedBySeller) { ?>
                                    <th><?php echo Labels::getLabel('LBL_Shipping', $adminLangId); ?></th>
                                <?php }?>
                                <?php if ($order['op_tax_collected_by_seller']) { ?>
                                    <th><?php echo Labels::getLabel('LBL_Tax', $adminLangId); ?></th>
                                <?php } ?>
                                <th><?php echo Labels::getLabel('LBL_Discount', $adminLangId); ?></th>
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
                                $txt .= '<br/>'.Labels::getLabel('LBL_Brand', $adminLangId).': '.$order['op_brand_name'];
                                if ($order['op_selprod_options'] != '') {
                                    $txt .= ' | ' . $order['op_selprod_options'];
                                }
                                if ($order['op_selprod_sku'] != '') {
                                    $txt .= '<br/>'.Labels::getLabel('LBL_SKU', $adminLangId).':  ' . $order['op_selprod_sku'];
                                }
                                if ($order['op_product_model'] != '') {
                                    $txt .= '<br/>'.Labels::getLabel('LBL_Model', $adminLangId).':  ' . $order['op_product_model'];
                                }
                                echo $txt;
                                ?></td>
                                <td><strong><?php echo Labels::getLabel('LBL_Shipping_Class', $adminLangId); ?>: </strong><?php echo CommonHelper::displayNotApplicable($adminLangId, $order["op_shipping_duration_name"]); ?><br />
                                    <strong><?php echo Labels::getLabel('LBL_Duration', $adminLangId); ?>: </strong><?php echo CommonHelper::displayNotApplicable($adminLangId, $order["op_shipping_durations"]); ?></td>
                                <td><?php echo CommonHelper::displayMoneyFormat($order["op_unit_price"], true, true); ?></td>
                                <td><?php echo $order["op_qty"]?></td>
                                <?php if ($shippingHanldedBySeller) {
                                    ?> <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'SHIPPING'), true, true); ?></td>
                                <?php } ?>
                                <?php if ($order['op_tax_collected_by_seller']) {
                                    ?> <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'TAX'), true, true); ?></td> <?php
                                }?> <td>
                                    <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'VOLUME_DISCOUNT'), true, true);?></td>
                                <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'netamount', false, USER::USER_TYPE_SELLER), true, true);?></td>
                            </tr>
                        </table>
                    </div>
                </section>
                <?php if (!empty($digitalDownloads) && !$print) { ?>
                <section class="section no-print">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Downloads', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody">
                        <table class="table">
                            <tr>
                                <th><?php echo Labels::getLabel('LBL_Sr_No', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_File', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Language', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Downloaded_count', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Expired_on', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Action', $adminLangId); ?></th>
                            </tr>
                            <?php $sr_no = 1;
                            foreach ($digitalDownloads as $key => $row) {
                                $lang_name = Labels::getLabel('LBL_All', $adminLangId);
                                if ($row['afile_lang_id'] > 0) {
                                    $lang_name = $allLanguages[$row['afile_lang_id']]['language_name'];
                                }
                                if ($row['downloadable']) {
                                    $fileName = '<a href="'.CommonHelper::generateUrl('SellerOrders', 'digitalDownloads', array($row['afile_id'],$row['afile_record_id'])).'">'.$row['afile_name'].'</a>';
                                } else {
                                    $fileName = $row['afile_name'];
                                }
                                $downloads = '<a href="'.CommonHelper::generateUrl('SellerOrders', 'digitalDownloads', array($row['afile_id'],$row['afile_record_id'])).'">'.Labels::getLabel('LBL_Downloads', $adminLangId).'</a>';

                                $expiry = Labels::getLabel('LBL_N/A', $adminLangId) ;
                                if ($row['expiry_date']!='') {
                                    $expiry = FatDate::Format($row['expiry_date']);
                                } ?>
                                <tr>
                                    <td><?php echo $sr_no; ?></td>
                                    <td><?php echo $fileName; ?></td>
                                    <td><?php echo $lang_name; ?></td>
                                    <td><?php echo $row['afile_downloaded_times']; ?></td>
                                    <td><?php echo $expiry; ?></td>
                                    <td><?php echo $downloads; ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </section>
                <?php }
                if (!empty($digitalDownloadLinks) && !$print) { ?>
                <section class="section no-print">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Downloads', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody">
                        <table class="table">
                            <tr>
                                <th><?php echo Labels::getLabel('LBL_Sr_No', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Link', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Download_times', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Downloaded_count', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Expired_on', $adminLangId); ?></th>
                            </tr>
                            <?php $sr_no = 1;
                            foreach ($digitalDownloadLinks as $key => $row) {
                                    /* $fileName = '<a href="'.CommonHelper::generateUrl('Seller','downloadDigitalFile',array($row['afile_id'],$row['afile_record_id'],AttachedFile::FILETYPE_ORDER_PRODUCT_DIGITAL_DOWNLOAD)).'">'.$row['afile_name'].'</a>'; */
                                    /* $downloads = '<li><a href="'.CommonHelper::generateUrl('Seller','downloadDigitalFile',array($row['afile_id'],$row['afile_record_id'],AttachedFile::FILETYPE_ORDER_PRODUCT_DIGITAL_DOWNLOAD)).'"><i class="fa fa-download"></i></a></li>'; */

                                    $expiry = Labels::getLabel('LBL_N/A', $adminLangId) ;
                                if ($row['expiry_date']!='') {
                                    $expiry = FatDate::Format($row['expiry_date']);
                                }

                                $downloadableCount = Labels::getLabel('LBL_N/A', $adminLangId) ;
                                if ($row['downloadable_count'] != -1) {
                                    $downloadableCount = $row['downloadable_count'];
                                } ?>
                                <tr>
                                    <td><?php echo $sr_no; ?></td>
                                    <td><a target="_blank" href="<?php echo $row['opddl_downloadable_link']; ?>" title="<?php echo Labels::getLabel('LBL_Click_to_download', $adminLangId); ?>"><?php echo $row['opddl_downloadable_link']; ?></a></td>
                                    <td><?php echo $downloadableCount; ?></td>
                                    <td><?php echo $row['opddl_downloaded_times']; ?></td>
                                    <td><?php echo $expiry; ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </section>
                <?php }
                if (!empty($order['comments']) && !$print) {  ?>
                    <section class="section no-print">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Order_Comments', $adminLangId); ?></h4>
                        </div>
                        <div class="sectionbody">
                            <table class="table">
                                <tr>
                                    <th><?php echo Labels::getLabel('LBL_Date_Added', $adminLangId); ?></td>
                                    <th><?php echo Labels::getLabel('LBL_Customer_Notified', $adminLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Status', $adminLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Comments', $adminLangId); ?></th>
                                </tr>
                                <?php foreach ($order['comments'] as $row) { ?>
                                <tr>
                                    <td><?php echo FatDate::format($row['oshistory_date_added']); ?></td>
                                    <td><?php echo $yesNoArr[$row['oshistory_customer_notified']]; ?></td>
                                    <td><?php echo $orderStatuses[$row['oshistory_orderstatus_id']]; ?></td>
                                    <td><?php echo nl2br($row['oshistory_comments']); ?>
                                        <?php   echo ($row['oshistory_orderstatus_id']>0)?$orderStatuses[$row['oshistory_orderstatus_id']]:CommonHelper::displayNotApplicable($adminLangId, '');
                                        echo ($row['oshistory_tracking_number'])? ': '.Labels::getLabel('LBL_Tracking_Number', $adminLangId).' '.$row['oshistory_tracking_number']." VIA <em>".$row['op_shipping_duration_name']."</em>" :''; ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </section>
                <?php }
                if ($displayShippingUserForm) { ?>
                    <section class="section">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Assign_to_shipping_company_user', $adminLangId); ?></h4>
                        </div>
                        <div class="sectionbody space">
                            <?php
                                $shippingUserFrm->setFormTagAttribute('onsubmit', 'updateShippingCompany(this); return(false);');
                                $shippingUserFrm->setFormTagAttribute('class', 'web_form form_horizontal');
                                $shippingUserFrm->developerTags['colClassPrefix'] = 'col-md-';
                                $shippingUserFrm->developerTags['fld_default_col'] = 12;
                                echo $shippingUserFrm->getFormHtml();
                            ?>
                        </div>
                    </section>
                <?php } if ($displayForm && !$print) { ?>
                    <section class="section no-print">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Comments_on_order', $adminLangId); ?></h4>
                        </div>
                        <div class="sectionbody space">
                            <?php
                            $frm->setFormTagAttribute('onsubmit', 'updateStatus(this); return(false);');
                            $frm->setFormTagAttribute('class', 'web_form');

                            $frm->developerTags['colClassPrefix'] = 'col-md-';
                            $frm->developerTags['fld_default_col'] = 12;

                            $fldTracking = $frm->getField('tracking_number');
                            $fldTracking->setWrapperAttribute('class', 'div_tracking_number');
                            echo $frm->getFormHtml(); ?>
                        </div>
                    </section>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php if ($print) { ?>
    <script>
        window.print();
        window.onafterprint = function() {
            location.href = history.back();
        }
    </script>
<?php } ?>
