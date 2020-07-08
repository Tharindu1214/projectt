<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (!$print) {
    $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<?php } ?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <?php if (!$print) { ?>
        <div class="content-header  row justify-content-between mb-3">
            <div class="col-md-auto">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title">
                    <?php echo Labels::getLabel('LBL_View_Sale_Order', $siteLangId);?>
                </h2>
            </div>
            <?php
            $orderObj = new Orders();
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses();
            if (in_array($orderDetail['orderstatus_id'], $processingStatuses)) { ?>
            <div class="col-md-auto">
                <div class="btn-group">
                    <ul class="actions">
                        <li>
                            <a href="<?php echo CommonHelper::generateUrl('seller', 'cancelOrder', array($orderDetail['op_id']));?>" class="icn-highlighted" title="<?php echo Labels::getLabel('LBL_Cancel_Order', $siteLangId);?>"><i
                                    class="fa fa-close"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
        <div class="content-body">
            <div class="cards">
                <div class="cards-header p-4">
                    <h5 class="cards-title"><?php echo Labels::getLabel('LBL_Order_Details', $siteLangId);?></h5>
                    <?php if (!$print) { ?>
                    <div class="">
                        <iframe src="<?php echo Fatutility::generateUrl('seller', 'viewOrder', $urlParts) . '/print'; ?>" name="frame" style="display:none"></iframe>
                        <a href="javascript:void(0)" onclick="frames['frame'].print()" class="btn btn--primary btn--sm no-print"><?php echo Labels::getLabel('LBL_Print', $siteLangId); ?></a>
                        <a href="<?php echo CommonHelper::generateUrl('Seller', 'sales');?>" class="btn btn--primary-border  btn--sm no-print"><?php echo Labels::getLabel('LBL_Back_to_order', $siteLangId);?></a>
                    </div>
                    <?php } ?>
                </div>
                <div class="cards-content pl-4 pr-4 ">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 mb-4">
                            <div class="info--order">
                                <p><strong><?php echo Labels::getLabel('LBL_Customer_Name', $siteLangId);?>: </strong><?php echo $orderDetail['user_name'];?></p>
                                <?php
                                $selected_method = '';
                                if ($orderDetail['order_pmethod_id'] > 0) {
                                    $selected_method.= CommonHelper::displayNotApplicable($siteLangId, $orderDetail["pmethod_name"]);
                                }
                                if ($orderDetail['order_is_wallet_selected'] > 0) {
                                    $selected_method.= ($selected_method!='') ? ' + '.Labels::getLabel("LBL_Wallet", $siteLangId) : Labels::getLabel("LBL_Wallet", $siteLangId);
                                }
                                if ($orderDetail['order_reward_point_used'] > 0) {
                                    $selected_method.= ($selected_method!='') ? ' + '.Labels::getLabel("LBL_Rewards", $siteLangId) : Labels::getLabel("LBL_Rewards", $siteLangId);
                                } ?>
                                <p><strong><?php echo Labels::getLabel('LBL_Payment_Method', $siteLangId);?>: </strong><?php echo $selected_method;?></p>
                                <p><strong><?php echo Labels::getLabel('LBL_Status', $siteLangId);?>: </strong>
                                <?php echo Orders::getOrderPaymentStatusArr($siteLangId)[$orderDetail['order_is_paid']];
                                if ('' != $orderDetail['pmethod_name'] && 'CashOnDelivery' == $orderDetail['pmethod_code']) {
                                    echo ' ('.$orderDetail['pmethod_name'].' )';
                                } ?>
                                <?php /*echo $orderStatuses[$orderDetail['op_status_id']];*/ ?></p>
                                <p><strong><?php echo Labels::getLabel('LBL_Cart_Total', $siteLangId);?>: </strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'CART_TOTAL'));?></p>

                                <?php if ($shippedBySeller) {?>
                                <p><strong><?php echo Labels::getLabel('LBL_Delivery', $siteLangId);?>: </strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'SHIPPING'));?></p>
                                <?php }?>

                                <?php if ($orderDetail['op_tax_collected_by_seller']) {?>
                                <p><strong><?php echo Labels::getLabel('LBL_Tax', $siteLangId);?>:</strong> <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'TAX'));?></p>
                                <?php }?>
                                <?php /*
                        <p><strong><?php echo Labels::getLabel('LBL_Discount',$siteLangId);?>:</strong> <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail,'DISCOUNT'));?></p> */?>
                                <?php $volumeDiscount = CommonHelper::orderProductAmount($orderDetail, 'VOLUME_DISCOUNT');
                                if ($volumeDiscount) { ?>
                                <p><strong><?php echo Labels::getLabel('LBL_Volume/Loyalty_Discount', $siteLangId);?>:</strong> <?php echo CommonHelper::displayMoneyFormat($volumeDiscount);?></p>
                                <?php } ?>
                                <?php
                        /* $rewardPointDiscount = CommonHelper::orderProductAmount($orderDetail,'REWARDPOINT');
                        if($rewardPointDiscount != 0){?>
                                <p><strong><?php echo Labels::getLabel('LBL_Reward_Point_Discount',$siteLangId);?>:</strong> <?php echo CommonHelper::displayMoneyFormat($rewardPointDiscount);?></p>
                                <?php }  */?>
                                <p><strong><?php echo Labels::getLabel('LBL_Order_Total', $siteLangId);?>: </strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'netamount', false, USER::USER_TYPE_SELLER));?>
                                </p>

                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 mb-4">
                            <div class="info--order">
                                <p><strong><?php echo Labels::getLabel('LBL_Invoice', $siteLangId);?> #: </strong><?php echo $orderDetail['op_invoice_number'];?></p>
                                <p><strong><?php echo Labels::getLabel('LBL_Date', $siteLangId);?>: </strong><?php echo FatDate::format($orderDetail['order_date_added']);?></p>
                                <span class="gap"></span>
                            </div>
                        </div>
                    </div>

                    <table class="table table--orders">
                        <tbody>
                            <tr class="">
                                <th><?php echo Labels::getLabel('LBL_Order_Particulars', $siteLangId);?></th>
                                <?php if (!$print) { ?>
                                <th class="no-print"></th>
                                <?php } ?>
                                <th><?php echo Labels::getLabel('LBL_Qty', $siteLangId);?></th>
                                <th><?php echo Labels::getLabel('LBL_Price', $siteLangId);?></th>
                                <?php if ($shippedBySeller) {?>
                                <th><?php echo Labels::getLabel('LBL_Shipping_Charges', $siteLangId);?></th>
                                <?php }?>
                                <?php if ($volumeDiscount) { ?>
                                <th><?php echo Labels::getLabel('LBL_Volume/Loyalty_Discount', $siteLangId);?></th>
                                <?php } ?>
                                <?php if ($orderDetail['op_tax_collected_by_seller']) {?>
                                <th><?php echo Labels::getLabel('LBL_Tax_Charges', $siteLangId);?></th>
                                <?php }?>
                                <th><?php echo Labels::getLabel('LBL_Total', $siteLangId);?></th>
                            </tr>
                            <tr>
                                <?php if (!$print) { ?>
                                <td>
                                    <div class="pic--cell-left">
                                    <?php
                                    $prodOrBatchUrl = 'javascript:void(0)';
                                    if ($orderDetail['op_is_batch']) {
                                        $prodOrBatchUrl = CommonHelper::generateUrl('Products', 'batch', array($orderDetail['op_selprod_id']));
                                        $prodOrBatchImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'BatchProduct', array($orderDetail['op_selprod_id'],$siteLangId, "SMALL"), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                    } else {
                                        if (Product::verifyProductIsValid($orderDetail['op_selprod_id']) == true) {
                                            $prodOrBatchUrl = CommonHelper::generateUrl('Products', 'view', array($orderDetail['op_selprod_id']));
                                        }
                                        $prodOrBatchImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'product', array($orderDetail['selprod_product_id'], "SMALL", $orderDetail['op_selprod_id'], 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                    }  ?>
                                        <figure class="item__pic"><a href="<?php echo $prodOrBatchUrl;?>"><img src="<?php echo $prodOrBatchImgUrl; ?>" title="<?php echo $orderDetail['op_product_name'];?>"
                                                    alt="<?php echo $orderDetail['op_product_name']; ?>"></a></figure>
                                        <!--</td>
                                             <td>-->
                                    </div>
                                    </td>
                                <?php } ?>
                                <td>
                                    <div class="item__description">
                                        <?php if ($orderDetail['op_selprod_title'] != '') { ?>
                                        <div class="item__title"><a title="<?php echo $orderDetail['op_selprod_title'];?>" href="<?php echo $prodOrBatchUrl;?>"><?php echo $orderDetail['op_selprod_title']; ?></a></div>
                                        <div class="item__category"><?php echo $orderDetail['op_product_name']; ?></div>
                                        <?php } else { ?>
                                        <div class="item__brand"><a title="<?php echo $orderDetail['op_product_name'];?>" href="<?php echo $prodOrBatchUrl; ?>"><?php echo $orderDetail['op_product_name']; ?>
                                            </a></div>
                                        <?php } ?>
                                        <div class="item__brand"><?php echo Labels::getLabel('Lbl_Brand', $siteLangId)?>: <?php echo CommonHelper::displayNotApplicable($siteLangId, $orderDetail['op_brand_name']);?></div>
                                        <?php if ($orderDetail['op_selprod_options'] != '') { ?>
                                        <div class="item__specification"><?php echo $orderDetail['op_selprod_options'];?></div>
                                        <?php }?>
                                        <?php if ($orderDetail['op_shipping_duration_name'] != '') {?>
                                        <div class="item__shipping"><?php echo Labels::getLabel('LBL_Shipping_Method', $siteLangId);?>: <?php echo $orderDetail['op_shipping_durations'].'-'. $orderDetail['op_shipping_duration_name'];?></div>
                                    </div>
                                <?php } ?>
                                </td>
                                <td><?php echo $orderDetail['op_qty'];?></td>
                                <td><?php echo CommonHelper::displayMoneyFormat($orderDetail['op_unit_price']);?></td>

                                <?php if ($shippedBySeller) {?>
                                <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'shipping'));?></td>
                                <?php }?>

                                <?php if ($volumeDiscount) { ?>
                                <td><?php echo CommonHelper::displayMoneyFormat($volumeDiscount);?></td>
                                <?php } ?>

                                <?php if ($orderDetail['op_tax_collected_by_seller']) {?>
                                <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'tax')); ?></td>
                                <?php }?>

                                <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'netamount', false, USER::USER_TYPE_SELLER));?></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="divider"></div>
                    <div class="gap"></div>
                    <div class="gap"></div>

                    <div class="row">
                        <div class="col-lg-6 col-md-6 mb-4">
                            <h5><?php echo Labels::getLabel('LBL_Billing_Details', $siteLangId);?></h5>
                            <?php $billingAddress = $orderDetail['billingAddress']['oua_name'].'<br>';
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
                            ?>
                            <div class="info--order">
                                <p><?php echo $billingAddress;?></p>
                            </div>
                        </div>
                        <?php if (!empty($orderDetail['shippingAddress'])) {?>
                        <div class="col-lg-6 col-md-6 mb-4">

                            <h5><?php echo Labels::getLabel('LBL_Shipping_Details', $siteLangId);?></h5>
                            <?php $shippingAddress = $orderDetail['shippingAddress']['oua_name'].'<br>';
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
                          }
                        ?>
                            <div class="info--order">
                                <p><?php echo $shippingAddress;?></p>
                            </div>

                        </div>
                        <?php } ?>
                    </div>

                    <?php if ($displayForm && !$print) { ?>
                    <div class="section--repeated no-print">
                        <h5><?php echo Labels::getLabel('LBL_Comments_on_order', $siteLangId);?></h5>
                        <?php
                      $frm->setFormTagAttribute('onsubmit', 'updateStatus(this); return(false);');
                      $frm->setFormTagAttribute('class', 'form');
                      $frm->developerTags['colClassPrefix'] = 'col-md-';
                      $frm->developerTags['fld_default_col'] = 12;

                      $fld = $frm->getField('op_status_id');
                      $fld->developerTags['col'] = 6;

                      $fld1 = $frm->getField('customer_notified');
                      $fld1->developerTags['col'] = 6;

                      $fldTracking = $frm->getField('tracking_number');
                      $fldTracking->setWrapperAttribute('class', 'div_tracking_number');
                      $fldTracking->developerTags['col'] = 6;

                      $fldBtn = $fldTracking = $frm->getField('btn_submit');
                      $fldBtn->developerTags['col'] = 6;
                      echo $frm->getFormHtml();?>
                    </div>
                    <?php }?>
                    <span class="gap"></span>
                    <?php if (!empty($orderDetail['comments']) && !$print) {?>
                    <div class="section--repeated no-print">
                        <h5><?php echo Labels::getLabel('LBL_Posted_Comments', $siteLangId);?></h5>
                        <table class="table  table--orders">
                            <tbody>
                                <tr class="">
                                    <th><?php echo Labels::getLabel('LBL_Date_Added', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Customer_Notified', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Status', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Comments', $siteLangId);?></th>
                                </tr>
                                <?php
                    foreach ($orderDetail['comments'] as $row) {?>
                                <tr>
                                    <td><?php echo FatDate::format($row['oshistory_date_added'], true);?></td>
                                    <td><?php echo $yesNoArr[$row['oshistory_customer_notified']];?></td>
                                    <td><?php echo $orderStatuses[$row['oshistory_orderstatus_id']]; echo ($row['oshistory_tracking_number'])? ': '.Labels::getLabel('LBL_Tracking_Number', $siteLangId).' '.$row['oshistory_tracking_number']." VIA <em>".$row['op_shipping_duration_name']."</em>" :''?>
                                    </td>
                                    <td><?php echo !empty($row['oshistory_comments']) ? nl2br($row['oshistory_comments']) : Labels::getLabel('LBL_N/A', $siteLangId);?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php }?>
                    <span class="gap"></span>
                    <?php if (!empty($digitalDownloads)) { ?>
                    <div class="section--repeated">
                        <h5><?php echo Labels::getLabel('LBL_Downloads', $siteLangId);?></h5>
                        <table class="table  table--orders">
                            <tbody>
                                <tr class="">
                                    <th><?php echo Labels::getLabel('LBL_Sr_No', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_File', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Language', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Download_times', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Downloaded_count', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Expired_on', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Action', $siteLangId);?></th>
                                </tr>
                                <?php $sr_no = 1;
                      foreach ($digitalDownloads as $key=>$row) {
                          $lang_name = Labels::getLabel('LBL_All', $siteLangId);
                          if ($row['afile_lang_id'] > 0) {
                              $lang_name = $languages[$row['afile_lang_id']];
                          }

                          $fileName = '<a href="'.CommonHelper::generateUrl('Seller', 'downloadDigitalFile', array($row['afile_id'],$row['afile_record_id'],AttachedFile::FILETYPE_ORDER_PRODUCT_DIGITAL_DOWNLOAD)).'">'.$row['afile_name'].'</a>';
                          $downloads = '<li><a href="'.CommonHelper::generateUrl('Seller', 'downloadDigitalFile', array($row['afile_id'],$row['afile_record_id'],AttachedFile::FILETYPE_ORDER_PRODUCT_DIGITAL_DOWNLOAD)).'"><i class="fa fa-download"></i></a></li>';

                          $expiry = Labels::getLabel('LBL_N/A', $siteLangId) ;
                          if ($row['expiry_date']!='') {
                              $expiry = FatDate::Format($row['expiry_date']);
                          }

                          $downloadableCount = Labels::getLabel('LBL_N/A', $siteLangId) ;
                          if ($row['downloadable_count'] != -1) {
                              $downloadableCount = $row['downloadable_count'];
                          } ?>
                                <tr>
                                    <td><?php echo $sr_no; ?></td>
                                    <td><?php echo $fileName; ?></td>
                                    <td><?php echo $lang_name; ?></td>
                                    <td><?php echo $downloadableCount; ?></td>
                                    <td><?php echo $row['afile_downloaded_times']; ?></td>
                                    <td><?php echo $expiry; ?></td>
                                    <td>
                                        <ul class="actions"><?php echo $downloads; ?></ul>
                                    </td>
                                </tr>
                                <?php $sr_no++;
                      } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } ?>

                    <span class="gap"></span>
                    <?php if (!empty($digitalDownloadLinks)) { ?>
                    <div class="section--repeated">
                        <h5><?php echo Labels::getLabel('LBL_Downloads', $siteLangId);?></h5>
                        <table class="table  table--orders">
                            <tbody>
                                <tr class="">
                                    <th><?php echo Labels::getLabel('LBL_Sr_No', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Link', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Download_times', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Downloaded_count', $siteLangId);?></th>
                                    <th><?php echo Labels::getLabel('LBL_Expired_on', $siteLangId);?></th>
                                </tr>
                                <?php $sr_no = 1;
                      foreach ($digitalDownloadLinks as $key=>$row) {

                        /* $fileName = '<a href="'.CommonHelper::generateUrl('Seller','downloadDigitalFile',array($row['afile_id'],$row['afile_record_id'],AttachedFile::FILETYPE_ORDER_PRODUCT_DIGITAL_DOWNLOAD)).'">'.$row['afile_name'].'</a>'; */
                          /* $downloads = '<li><a href="'.CommonHelper::generateUrl('Seller','downloadDigitalFile',array($row['afile_id'],$row['afile_record_id'],AttachedFile::FILETYPE_ORDER_PRODUCT_DIGITAL_DOWNLOAD)).'"><i class="fa fa-download"></i></a></li>'; */

                          $expiry = Labels::getLabel('LBL_N/A', $siteLangId) ;
                          if ($row['expiry_date']!='') {
                              $expiry = FatDate::Format($row['expiry_date']);
                          }

                          $downloadableCount = Labels::getLabel('LBL_N/A', $siteLangId) ;
                          if ($row['downloadable_count'] != -1) {
                              $downloadableCount = $row['downloadable_count'];
                          } ?>
                                <tr>
                                    <td><?php echo $sr_no; ?></td>
                                    <td><a target="_blank" href="<?php echo $row['opddl_downloadable_link']; ?>" title="<?php echo Labels::getLabel('LBL_Click_to_download', $siteLangId); ?>"><?php echo $row['opddl_downloadable_link']; ?></a></td>
                                    <td><?php echo $downloadableCount; ?></td>
                                    <td><?php echo $row['opddl_downloaded_times']; ?></td>
                                    <td><?php echo $expiry; ?></td>
                                </tr>
                                <?php $sr_no++;
                      } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } ?>
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
<?php } ?>
