<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!$print) { ?>
    <?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<?php } ?>
<main id="main-area" class="main" role="main">
 <div class="content-wrapper content-space">
    <?php if (!$print) { ?>
    <div class="content-header row justify-content-between mb-3">
        <div class="col-md-auto">
            <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
            <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_View_Order_Return_Request', $siteLangId).': <span class="number">' . $request['orrequest_reference'].'</span>' ; ?></h2>
        </div>
    </div>
    <?php } ?>
    <div class="content-body">
        <div class="cards">
            <div class="cards-header p-4">
                <h5 class="cards-title"><?php echo Labels::getLabel('LBL_Request_Details', $siteLangId); ?></h5>
                <?php if (!$print) { ?>
                    <div class="">
                        <iframe src="<?php echo Fatutility::generateUrl('buyer', 'viewOrderReturnRequest', $urlParts) . '/print'; ?>" name="frame" style="display:none"></iframe>
                        <a href="javascript:void(0)" onclick="frames['frame'].print()" class="btn btn--primary btn--sm no-print"><?php echo Labels::getLabel('LBL_Print', $siteLangId); ?></a>
                        <a href="<?php echo CommonHelper::generateUrl('Buyer', 'orderReturnRequests');?>" class="btn btn--primary-border btn--sm no-print"><?php echo Labels::getLabel('LBL_Back', $siteLangId);?></a>
                    </div>
                <?php } ?>
            </div>
            <div class="cards-content pl-4 pr-4 ">
               
                     <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6  mb-4">
                               <div class="info--order">
                                <h5><?php echo Labels::getLabel('LBL_Vendor_Return_Address', $siteLangId); ?></h5>
                                <?php echo ($vendorReturnAddress['ura_name'] != null) ? '<h6>'.$vendorReturnAddress['ura_name'].'</h6>' : '';?>
                                <p>
                                <?php echo (strlen($vendorReturnAddress['ura_address_line_1']) > 0) ? $vendorReturnAddress['ura_address_line_1'].'<br/>' : '';?>
                                <?php echo (strlen($vendorReturnAddress['ura_address_line_2'])>0)?$vendorReturnAddress['ura_address_line_2'].'<br>':'';?>
                                <?php echo (strlen($vendorReturnAddress['ura_city'])>0)?$vendorReturnAddress['ura_city'].',':'';?>
                                <?php echo (strlen($vendorReturnAddress['state_name'])>0)?$vendorReturnAddress['state_name'].'<br>':'';?>
                                <?php echo (strlen($vendorReturnAddress['country_name'])>0)?$vendorReturnAddress['country_name'].'<br>':'';?>
                                <?php echo (strlen($vendorReturnAddress['ura_zip'])>0) ? Labels::getLabel('LBL_Zip:', $siteLangId).$vendorReturnAddress['ura_zip'].'<br>':'';?>
                                <?php echo (strlen($vendorReturnAddress['ura_phone'])>0) ? Labels::getLabel('LBL_Phone:', $siteLangId).$vendorReturnAddress['ura_phone'].'<br>':''; ?>
                                </p>
                            </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6  mb-4">
                                <div class="info--order">
                                    <h5><?php echo Labels::getLabel('LBL_Vendor_Detail', $siteLangId); ?></h5>
                                    <p>
                                    <?php echo ($request['op_shop_owner_name'] != '') ? '<strong>'.Labels::getLabel('LBL_Vendor_Name', $siteLangId).':</strong> '.$request['op_shop_owner_name'] : ''; ?></p>
                                    <p>
                                    <?php
                                    $vendorShopUrl = CommonHelper::generateUrl('Shops', 'View', array($request['op_shop_id']));
                                    echo ($request['op_shop_name'] != '') ? '<strong>'.Labels::getLabel('LBL_Shop_Name', $siteLangId).':</strong> <a href="'.$vendorShopUrl.'">'.$request['op_shop_name'].'</a><br/>' : ''; ?>
                                    </p>
                                    <span class="gap"></span>
                                </div>
                            </div>
                        </div>
                    <?php if ($canEscalateRequest && !$print) { ?>
                    <a class="btn btn--primary no-print" onClick="javascript: return confirm('<?php echo Labels::getLabel('MSG_Do_you_want_to_proceed?', $siteLangId); ?>')" href="<?php echo CommonHelper::generateUrl('Account', 'escalateOrderReturnRequest', array($request['orrequest_id'])); ?>"><?php echo str_replace("{website_name}", FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId), Labels::getLabel('LBL_Escalate_to', $siteLangId)); ?></a>
                    <?php } ?>

                    <?php if ($canWithdrawRequest && !$print) { ?>
                    <a class="btn btn--primary no-print" onClick="javascript: return confirm('<?php echo Labels::getLabel('MSG_Do_you_want_to_proceed?', $siteLangId); ?>')" href="<?php echo CommonHelper::generateUrl('Buyer', 'WithdrawOrderReturnRequest', array($request['orrequest_id'])); ?>"><?php echo Labels::getLabel('LBL_Withdraw_Request', $siteLangId); ?></a>
                    <?php } ?>
                    
                
                <?php if (!empty($request)) { ?>
                <table class="table table--orders">
                    <tbody>
                        <tr class="">
                            <th width="15%"><?php echo Labels::getLabel('LBL_ID', $siteLangId); ?></th>
                            <th width="20%"><?php echo Labels::getLabel('LBL_Order_Id/Invoice_Number', $siteLangId); ?></th>
                            <th><?php echo Labels::getLabel('LBL_Product', $siteLangId); ?></th>
                            <th width="15%"><?php echo Labels::getLabel('LBL_Return_Qty', $siteLangId); ?></th>
                            <th width="15%"><?php echo Labels::getLabel('LBL_Request_Type', $siteLangId); ?></th>
                        </tr>
                        <tr>
                            <td><?php echo $request['orrequest_reference'] /* CommonHelper::formatOrderReturnRequestNumber($request['orrequest_id']) */; ?></td>
                            <td><?php echo $request['op_invoice_number']; ?>
                            </td>
                            <td>
                                <div class="item__description">
                                    <?php if ($request['op_selprod_title'] != '') { ?>
                                        <div class="item__title" title="<?php echo $request['op_selprod_title']; ?>"><?php echo $request['op_selprod_title']; ?></div>
                                        <div class="item__sub_title"><?php echo $request['op_product_name']; ?></div>
                                        <?php } else { ?>
                                        <div class="item__title" title="<?php echo $request['op_product_name']; ?>"><?php echo $request['op_product_name']; ?></div>
                                        <?php } ?>
                                    <div class="item__brand"><?php echo Labels::getLabel('LBL_Brand', $siteLangId); ?>: <?php echo $request['op_brand_name']; ?></div>
                                    <?php
                                    if ($request['op_selprod_options'] != '') { ?>
                                        <div class="item__specification"><?php echo $request['op_selprod_options']; ?></div>
                                    <?php }    ?>

                                    <?php if ($request['op_selprod_sku'] != '') { ?>
                                        <div class="item__sku"><?php echo Labels::getLabel('LBL_SKU', $siteLangId).':  ' . $request['op_selprod_sku']; ?> </div>
                                    <?php } ?>

                                    <?php if ($request['op_product_model'] != '') { ?>
                                        <div class="item__model"><?php echo Labels::getLabel('LBL_Model', $siteLangId).':  ' . $request['op_product_model']; ?></div>
                                    <?php }    ?>
                                </div>
                            </td>
                            <td><?php echo $request['orrequest_qty']; ?></td>
                            <td> <?php echo $returnRequestTypeArr[$request['orrequest_type']]; ?></td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table--orders">
                    <tbody>
                        <tr class="">
                            <th width="20%"><?php echo Labels::getLabel('LBL_Reason', $siteLangId); ?></th>
                            <th width="20%"><?php echo Labels::getLabel('LBL_Date', $siteLangId); ?></th>
                            <th width="20%"><?php echo Labels::getLabel('LBL_Status', $siteLangId); ?></th>
                            <th width="20%"><?php echo Labels::getLabel('LBL_Amount', $siteLangId); ?></th>
                            <?php if (isset($attachedFile) && !empty($attachedFile)) { ?>
                            <th width="20%"><?php echo Labels::getLabel('LBL_Download_Attached_Files', $siteLangId); ?></th>
                            <?php } ?>
                        </tr>
                        <tr>
                            <td><?php echo $request['orreason_title']; ?></td>
                            <td>
                                <div class="item__description">
                                    <span class=""><?php echo FatDate::format($request['orrequest_date']); ?></span>
                                </div>
                            </td>
                            <td><?php echo $requestRequestStatusArr[$request['orrequest_status']]; ?></td>
                            <td><?php
                            $returnDataArr = CommonHelper::getOrderProductRefundAmtArr($request);
                            /* $priceTotalPerItem = CommonHelper::orderProductAmount($request,'netamount',true);

                            $price = 0;
                            if($request['orrequest_status'] != OrderReturnRequest::RETURN_REQUEST_STATUS_REFUNDED){
                                if(FatApp::getConfig('CONF_RETURN_SHIPPING_CHARGES_TO_CUSTOMER',FatUtility::VAR_INT,0)){
                                    $shipCharges = isset($request['charges'][OrderProduct::CHARGE_TYPE_SHIPPING][OrderProduct::DB_TBL_CHARGES_PREFIX.'amount'])?$request['charges'][OrderProduct::CHARGE_TYPE_SHIPPING][OrderProduct::DB_TBL_CHARGES_PREFIX.'amount']:0;
                                    $unitShipCharges = round(($shipCharges / $request['op_qty']),2);
                                    $priceTotalPerItem = $priceTotalPerItem + $unitShipCharges;
                                    $price = $priceTotalPerItem * $request['orrequest_qty'];
                                }
                            }
                            if(!$price){
                                $price = $priceTotalPerItem * $request['orrequest_qty'];
                                $price = $price + $request['op_refund_shipping'];
                            } */
                            echo CommonHelper::displayMoneyFormat($returnDataArr['op_refund_amount'], true, false); ?></td>
                            <?php if (isset($attachedFile) && !empty($attachedFile)) { ?>
                            <td><a href="<?php echo CommonHelper::generateUrl('Buyer', 'downloadAttachedFileForReturn', array($request["orrequest_id"]));  ?>" class="button small green" > <?php echo Labels::getLabel('LBL_Download', $siteLangId); ?></a></td>
                            <?php } ?>
                        </tr>
                    </tbody>
                </table>
                <?php }    ?>
                <?php if (!$print) { ?>
                <div class="no-print">
                    <?php echo $returnRequestMsgsSrchForm->getFormHtml(); ?>
                    <div class="gap"></div>
                    <h5><?php echo Labels::getLabel('LBL_Return_Request_Messages', $siteLangId); ?> </h5>
                    <div id="loadMoreBtnDiv"></div>
                    <ul class="messages-list" id="messagesList"></ul>
                    
 <div class="gap"></div>
                    <?php if ($request && ($request['orrequest_status'] != OrderReturnRequest::RETURN_REQUEST_STATUS_REFUNDED && $request['orrequest_status'] != OrderReturnRequest::RETURN_REQUEST_STATUS_WITHDRAWN)) {
                                $frmMsg->setFormTagAttribute('onSubmit', 'setUpReturnOrderRequestMessage(this); return false;');
                                $frmMsg->setFormTagAttribute('class', 'form');
                                $frmMsg->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
                                $frmMsg->developerTags['fld_default_col'] = 12; ?>

                    <div class="messages-list" >
                        <ul>
                           <li>
                               <div class="msg_db">
                                   <div class="avtar"><img src="<?php echo CommonHelper::generateUrl('Image', 'user', array($logged_user_id, 'THUMB', 1)); ?>" alt="<?php echo $logged_user_name; ?>" title="<?php echo $logged_user_name; ?>"></div>
                               </div>
                               <div class="msg__desc">
                                   <span class="msg__title"><?php echo $logged_user_name; ?></span>

                                        <?php echo $frmMsg->getFormHtml(); ?>


                               </div>
                           </li>
                        </ul>
                    </div>
                   

                    <?php
                            } ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
  </div>
</main>
<?php if ($print) { ?>
    <script>
        $(".sidebar-is-expanded").addClass('sidebar-is-reduced').removeClass('sidebar-is-expanded');
        /*window.print();
        window.onafterprint = function(){
            location.href = history.back();
        }*/
    </script>
<?php } ?>
