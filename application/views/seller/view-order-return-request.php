<?php  defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<?php 
    $returnRequestMsgsForm->addHiddenField('', 'isSeller', 1);
?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header  row justify-content-between mb-3">
            <div class="col-md-auto">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_View_Order_Return_Request', $siteLangId).': <span class="number">' . $request['orrequest_reference'].'</span>' ; ?></h2>
            </div>
            <div class="col-md-auto">
                <div class="action"><a href="<?php echo CommonHelper::generateUrl('Seller', 'orderReturnRequests'); ?>" class="btn btn--primary ripplelink btn--sm"><?php echo Labels::getLabel('LBL_Back_To_Return_Requests', $siteLangId); ?></a></div>
            </div>
        </div>
        <div class="content-body">
            <div class="cards">
                <div class="cards-header p-4">
                    <h5 class="cards-title"><?php echo Labels::getLabel('LBL_Request_Details', $siteLangId); ?></h5>
                    <div class=""><?php if ($canEscalateRequest) { ?>
                            <a class="btn btn--primary ripplelink btn--sm" onClick="javascript: return confirm('<?php echo Labels::getLabel('MSG_Do_you_want_to_proceed?', $siteLangId); ?>')" href="<?php echo CommonHelper::generateUrl('Account', 'EscalateOrderReturnRequest', array($request['orrequest_id'])); ?>"><?php echo str_replace("{website_name}", FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId), Labels::getLabel('LBL_Escalate_to', $siteLangId)); ?></a>
                            <?php } ?>
                            <?php if ($canApproveReturnRequest) { ?>
                            <a class="btn btn--primary-border ripplelink btn--sm" onClick="javascript: return confirm('<?php echo Labels::getLabel('MSG_Do_you_want_to_proceed?', $siteLangId); ?>')" href="<?php echo CommonHelper::generateUrl('Seller', 'approveOrderReturnRequest', array($request['orrequest_id'])); ?>"><?php echo Labels::getLabel('LBL_Approve_Refund', $siteLangId); ?></a>
                            <?php } ?>
                    </div>
                </div>
                <div class="cards-content pl-4 pr-4 ">
           
               <div class="row">
                 <div class="col-lg-6 col-md-6 col-sm-6 mb-4">
                 <div class="info--order">
                  <h6><?php echo Labels::getLabel('LBL_Vendor_Return_Address', $siteLangId); ?></h6>
                  <?php echo ($vendorReturnAddress['ura_name'] != null) ? '<strong>'.$vendorReturnAddress['ura_name'].'</strong>' : '';?>
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
                <div class="col-lg-6 col-md-6 col-sm-6 mb-4">
                <div class="info--order">
                  <h6><?php echo Labels::getLabel('LBL_Customer_Detail', $siteLangId); ?></h6>
                  <p>

                  <?php echo ($request['buyer_name'] != '') ? '<strong>'.Labels::getLabel('LBL_Customer_Name:', $siteLangId).'</strong> '.$request['buyer_name'] : ''; ?></p>

                  <?php if (isset($attachedFile) && !empty($attachedFile)) { ?>
                  <p>
                    <strong><?php echo Labels::getLabel('Lbl_Download_attached_file', $siteLangId); ?>  </strong> <a href="<?php echo CommonHelper::generateUrl('Seller', 'downloadAttachedFileForReturn', array($request['orrequest_id'] , 0)); ?>" ><i class="fa fa-download"></i></a>
                  </p>
                  <?php } ?>
                </div>
                </div>
               </div>
            

            <div class="gap"></div>
            <?php if (!empty($request)) { ?>
            <table class="table table--orders">
              <tbody>
                <tr class="">
                  <th width="15%"><?php echo Labels::getLabel('LBL_ID', $siteLangId); ?></th>
                  <th width="20%"><?php echo Labels::getLabel('LBL_Order_Id/Invoice_Number', $siteLangId); ?></th>
                  <th ><?php echo Labels::getLabel('LBL_Product', $siteLangId); ?></th>
                  <th width="15%"><?php echo Labels::getLabel('LBL_Return_Qty', $siteLangId); ?></th>
                  <th width="15%"><?php echo Labels::getLabel('LBL_Request_Type', $siteLangId); ?></th>
                </tr>
                <tr>
                  <td><?php echo $request['orrequest_reference'] /* CommonHelper::formatOrderReturnRequestNumber($request['orrequest_id']) */; ?></td>
                  <td><?php echo $request['op_invoice_number']; ?>
                  <td>
                    <div class="item__description">
                      <?php if ($request['op_selprod_title'] != '') { ?>
                        <div class="item__title" title="<?php echo $request['op_selprod_title'];?>"><?php echo $request['op_selprod_title']; ?></div>
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
                  <td><?php echo $returnRequestTypeArr[$request['orrequest_type']]; ?></td>
                </tr>
              </tbody>
            </table>

	<div class="gap"></div>
            <table class="table table--orders">
              <tbody>
                <tr class="">
                  <th width="15%"><?php echo Labels::getLabel('LBL_Reason', $siteLangId); ?></th>
                  <th><?php echo Labels::getLabel('LBL_Date', $siteLangId); ?></th>
                  <th width="15%"><?php echo Labels::getLabel('LBL_Product_Price', $siteLangId); ?></th>
                  <th width="15%"><?php echo Labels::getLabel('LBL_Tax', $siteLangId); ?></th>
                  <th width="15%"><?php echo Labels::getLabel('LBL_Shipping', $siteLangId); ?></th>
                  <th width="15%"><?php echo Labels::getLabel('LBL_Status', $siteLangId); ?></th>
                  <th width="15%"><?php echo Labels::getLabel('LBL_Total_Amount', $siteLangId); ?></th>
                </tr>
                <tr>
                  <?php $returnDataArr = CommonHelper::getOrderProductRefundAmtArr($request);?>
                  <td><?php echo $request['orreason_title']; ?></td>
                  <td>
                    <div class="item__description">
                      <span class=""><?php echo FatDate::format($request['orrequest_date']); ?></span>
                    </div>
                  </td>
                  <td>
                  <?php echo CommonHelper::displayMoneyFormat($returnDataArr['op_prod_price'], true, false);?></td>
                  <td>
                  <?php echo CommonHelper::displayMoneyFormat($returnDataArr['op_refund_tax'], true, false);
                  ?></td>
                  <td>
                  <?php echo CommonHelper::displayMoneyFormat($returnDataArr['op_refund_shipping'], true, false);
                  ?></td>
                  <td><?php echo $requestRequestStatusArr[$request['orrequest_status']]; ?></td>
                  <td><?php
                  echo CommonHelper::displayMoneyFormat($returnDataArr['op_refund_amount'], true, false);  ?></td>
                </tr>
              </tbody>
            </table>
            <?php }    ?>

            <?php echo $returnRequestMsgsForm->getFormHtml(); ?>
            <div class="gap"></div>
            <div class="messageListBlock--js">
                <h5><?php echo Labels::getLabel('LBL_Return_Request_Messages', $siteLangId); ?> </h5>
                <div id="loadMoreBtnDiv"></div>
                <ul class="messages-list" id="messagesList"></ul>
            </div>
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
            </div>
        </div>
    </div>
</main>
