<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
 <div class="content-wrapper content-space">
	<div class="content-header  row justify-content-between mb-3">
		<div class="col-md-auto">
			<?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
			<h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Cancel_Order',$siteLangId);?></h2>
		</div>
	</div>
	<div class="content-body">
		<div class="cards">
			<div class="cards-header p-4">
				<h5 class="cards-title "><?php echo Labels::getLabel('LBL_Order_Details',$siteLangId);?></h5>
				 <div class="btn-group"><a href="<?php echo CommonHelper::generateUrl('Seller','sales');?>" class="btn btn--primary btn--sm"><?php echo Labels::getLabel('LBL_Back_to_order',$siteLangId);?></a></div>
			</div>
			<div class="cards-content pl-4 pr-4 ">
     			<div class="box__body">
				         <div class="row">
                                             <div class="col-lg-6 col-md-6 col-sm-6">
                                                <div class="info--order">
                                                 <p><strong><?php echo Labels::getLabel('LBL_Customer_Name',$siteLangId);?>: </strong><?php echo $orderDetail['user_name'];?></p>
                                                 <p><strong><?php echo Labels::getLabel('LBL_Status',$siteLangId);?>: </strong><?php echo $orderStatuses[$orderDetail['op_status_id']];?></p>
                                                 <p><strong><?php echo Labels::getLabel('LBL_Cart_Total',$siteLangId);?>: </strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail,'CART_TOTAL'));?></p>
                                                 <p><strong><?php echo Labels::getLabel('LBL_Delivery',$siteLangId);?>: </strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail,'SHIPPING'));?></p>
                                                 <p><strong><?php echo Labels::getLabel('LBL_Tax',$siteLangId);?>:</strong> <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail,'TAX'));?></p>
                                                 <p><strong><?php echo Labels::getLabel('LBL_Order_Total',$siteLangId);?>: </strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail));?></p>
                                             </div>
                                             </div>
                                             <div class="col-lg-6 col-md-6 col-sm-6">
                        						<div class="info--order">
                                                    <p><strong><?php echo Labels::getLabel('LBL_Invoice',$siteLangId);?> #: </strong><?php echo $orderDetail['op_invoice_number'];?></p>
                                                    <p><strong><?php echo Labels::getLabel('LBL_Date',$siteLangId);?>: </strong><?php echo FatDate::format($orderDetail['order_date_added']);?></p>
                        						</div>
                                             </div>
                                         </div>
                                         <div class="gap"></div>
                                 <table class="table table--orders">
                                       <tbody><tr class="">
                                           <th colspan="2"><?php echo Labels::getLabel('LBL_Order_Particulars',$siteLangId);?></th>
                                           <th><?php echo Labels::getLabel('LBL_Shipping_Method',$siteLangId);?></th>
                                           <th><?php echo Labels::getLabel('LBL_Qty',$siteLangId);?></th>
                                           <th><?php echo Labels::getLabel('LBL_Price',$siteLangId);?></th>
                                           <th><?php echo Labels::getLabel('LBL_Shipping_Charges',$siteLangId);?></th>
                                           <th><?php echo Labels::getLabel('LBL_Total',$siteLangId);?></th>
                                       </tr>
                                       <tr>
                                           <td>
                     <?php
                     $prodOrBatchUrl = 'javascript:void(0)';
                    if($orderDetail['op_is_batch']){
                      $prodOrBatchUrl = CommonHelper::generateUrl('Products','batch',array($orderDetail['op_selprod_id']));
                      $prodOrBatchImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('image','BatchProduct', array($orderDetail['op_selprod_id'],$siteLangId, "SMALL"),CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                     }else{
                      if(Product::verifyProductIsValid($orderDetail['op_selprod_id']) == true){
                        $prodOrBatchUrl = CommonHelper::generateUrl('Products','view',array($orderDetail['op_selprod_id']));
                      }
                      $prodOrBatchImgUrl = FatCache::getCachedUrl(CommonHelper::generateUrl('image','product', array($orderDetail['selprod_product_id'], "SMALL", $orderDetail['op_selprod_id'], 0, $siteLangId),CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                     }  ?>
                                           <figure class="item__pic"><a href="<?php echo $prodOrBatchUrl;?>"><img src="<?php echo $prodOrBatchImgUrl; ?>" title="<?php echo $orderDetail['op_product_name'];?>" alt="<?php echo $orderDetail['op_product_name']; ?>"></a></figure></td>
                                           <td>
                                               <div class="item__description">
                                                   <span class="item__title"><a title="<?php echo $orderDetail['op_product_name'];?>" href="<?php echo $prodOrBatchUrl;?>">
                         <?php if($orderDetail['op_selprod_title']!=''){
                          echo  $orderDetail['op_selprod_title'].'<br/>';
                        }else{
                          echo $orderDetail['op_product_name'];
                        }
                        ?>
                         </a></span>
                                                   <p><?php echo Labels::getLabel('Lbl_Brand',$siteLangId)?>: <?php echo CommonHelper::displayNotApplicable($siteLangId,$orderDetail['op_brand_name']);?></p>
                                                   <?php if( $orderDetail['op_selprod_options'] != '' ){ ?>
                         <p><?php echo $orderDetail['op_selprod_options'];?></p>
                         <?php }?>
                                               </div>
                                           </td>
                                            <td><?php echo $orderDetail['op_shipping_durations'].'-'. $orderDetail['op_shipping_duration_name'];?></td>
                                            <td><?php echo $orderDetail['op_qty'];?></td>
                                            <td><?php echo CommonHelper::displayMoneyFormat($orderDetail['op_unit_price']);?></td>
                                            <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail,'shipping'));?></td>
                                            <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail));?></td>
                                        </tr>
                                 </tbody></table>
                                 <div class="gap"></div>
                                 
                                     <div class="row">
                                         <div class="col-lg-6 col-md-6 col-sm-6">
                                             <div class="info--order">
                                                <h5><?php echo Labels::getLabel('LBL_Billing_Details',$siteLangId);?></h5>
                      <?php $billingAddress = $orderDetail['billingAddress']['oua_name'].'<br>';
                        if($orderDetail['billingAddress']['oua_address1']!=''){
                          $billingAddress.=$orderDetail['billingAddress']['oua_address1'].'<br>';
                        }

                        if($orderDetail['billingAddress']['oua_address2']!=''){
                          $billingAddress.=$orderDetail['billingAddress']['oua_address2'].'<br>';
                        }

                        if($orderDetail['billingAddress']['oua_city']!=''){
                          $billingAddress.=$orderDetail['billingAddress']['oua_city'].',';
                        }

                        if($orderDetail['billingAddress']['oua_zip']!=''){
                          $billingAddress.=$orderDetail['billingAddress']['oua_state'];
                        }

                        if($orderDetail['billingAddress']['oua_zip']!=''){
                          $billingAddress.= '-'.$orderDetail['billingAddress']['oua_zip'];
                        }

                        if($orderDetail['billingAddress']['oua_phone']!=''){
                          $billingAddress.= '<br>'.$orderDetail['billingAddress']['oua_phone'];
                        }
                      ?>
                                                <p><?php echo $billingAddress;?></p>
                                             </div>
                                         </div>
                    <?php if(!empty($orderDetail['shippingAddress'])){?>
                                         <div class="col-lg-6 col-md-6 col-sm-6">
                                             <div class="info--order">
                                                   <h5><?php echo Labels::getLabel('LBL_Shipping_Detail',$siteLangId);?></h5>
                       <?php $shippingAddress = $orderDetail['shippingAddress']['oua_name'].'<br>';
                        if($orderDetail['shippingAddress']['oua_address1']!=''){
                          $shippingAddress.=$orderDetail['shippingAddress']['oua_address1'].'<br>';
                        }

                        if($orderDetail['shippingAddress']['oua_address2']!=''){
                          $shippingAddress.=$orderDetail['shippingAddress']['oua_address2'].'<br>';
                        }

                        if($orderDetail['shippingAddress']['oua_city']!=''){
                          $shippingAddress.=$orderDetail['shippingAddress']['oua_city'].',';
                        }

                        if($orderDetail['shippingAddress']['oua_zip']!=''){
                          $shippingAddress.=$orderDetail['shippingAddress']['oua_state'];
                        }

                        if($orderDetail['shippingAddress']['oua_zip']!=''){
                          $shippingAddress.= '-'.$orderDetail['shippingAddress']['oua_zip'];
                        }

                        if($orderDetail['shippingAddress']['oua_phone']!=''){
                          $shippingAddress.= '<br>'.$orderDetail['shippingAddress']['oua_phone'];
                        }
                      ?>
                                                <p><?php echo $shippingAddress;?></p>
                                             </div>
                                         </div>
                   <?php }?>
                                     </div>
                                 

                                 <span class="gap"></span>
               <?php if(!empty($orderDetail['comments'])){?>
                                 <div class="section--repeated">
                                     <h5><?php echo Labels::getLabel('LBL_Posted_Comments',$siteLangId);?></h5>                                         <table class="table align--left">
                                        <tbody><tr class="hide--mobile">
                                            <th><?php echo Labels::getLabel('LBL_Date_Added',$siteLangId);?></th>
                                            <th><?php echo Labels::getLabel('LBL_Customer_Notified',$siteLangId);?></th>
                                            <th><?php echo Labels::getLabel('LBL_Status',$siteLangId);?></th>
                                            <th><?php echo Labels::getLabel('LBL_Comments',$siteLangId);?></th>
                                        </tr>
                  <?php
                  foreach($orderDetail['comments'] as $row){?>
                                        <tr>
                                            <td><?php echo FatDate::format($row['oshistory_date_added']);?></td>
                                            <td><?php echo $yesNoArr[$row['oshistory_customer_notified']];?></td>
                                            <td><?php echo $orderStatuses[$row['oshistory_orderstatus_id']];?></td>
                                            <td><?php echo nl2br($row['oshistory_comments']);?></td>
                                          </tr>
                    <?php } ?>
                  </tbody></table>
                                </div>
              <?php }?>
              <?php if (!$notEligible){?>
                    <div class="gap"></div>
                    <div class="section--repeated no-print cancelReason-js">
                        <h5><?php echo Labels::getLabel('LBL_Reason_for_cancellation',$siteLangId);?></h5>
                        <?php
                        $frm->setFormTagAttribute('onsubmit', 'cancelReason(this); return(false);');
                        $frm->setFormTagAttribute('class','form');
                        $frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
                        $frm->developerTags['fld_default_col'] = 12;

                        echo $frm->getFormHtml();?>
                    </div>
               <?php }?>
                            </div>
                         
			</div>
		</div>
	</div>
  </div>
</main>
