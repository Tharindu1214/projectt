<div class="page">
            <div class="container container-fluid">
                <div class="row">
                   <div class="col-lg-12 col-md-12 space">                       
                        <div class="page__title">
                            <div class="row">
                                <div class="col--first col-lg-6">
                                    <span class="page__icon"><i class="ion-android-star"></i></span>
                                    <h5><?php echo Labels::getLabel('LBL_Order_Details',$adminLangId); ?></h5>
										<?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                                </div>
                            </div>
                        </div>
                        
                        
                        <section class="section">
                            <div class="sectionhead">
								<h4><?php echo Labels::getLabel('LBL_View_Return_Order_Request',$adminLangId); ?></h4><?php 
									$ul = new HtmlElement("ul",array("class"=>"actions actions--centered"));
									$li = $ul->appendElement("li",array('class'=>'droplink'));						
									$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green','title'=>Labels::getLabel('LBL_Edit',$adminLangId)),'<i class="ion-android-more-horizontal icon"></i>', true);
									$innerDiv=$li->appendElement('div',array('class'=>'dropwrap'));	
									$innerUl=$innerDiv->appendElement('ul',array('class'=>'linksvertical'));
									$innerLi=$innerUl->appendElement('li');
									$innerLi->appendElement('a', array('href'=>CommonHelper::generateUrl('OrderReturnRequests'),'class'=>'button small green redirect--js','title'=>Labels::getLabel('LBL_Back_to_Order_Return_Requests',$adminLangId)),Labels::getLabel('LBL_Back_to_Order_Return_Requests',$adminLangId), true);	
									echo $ul->getHtml();		
								?>							
                            </div>
                            <div class="sectionbody">
                                                                <table class="table table--details">  
                                    <tr>
                                      <td><strong><?php echo Labels::getLabel('LBL_Refernce_Number',$adminLangId); ?>:</strong> <?php echo $requestRow["orrequest_reference"] ?></td>
                                      <td><strong><?php echo Labels::getLabel('LBL_Product',$adminLangId); ?> : </strong><?php 
											$txt = '';
											if( $requestRow['op_selprod_title'] != '' ){
												$txt .= $requestRow['op_selprod_title'].'<br/>'.'<small>'.$requestRow['op_product_name'].'</small>';
											} else {
												$txt .= $requestRow['op_product_name'];
											}
											if( $requestRow['op_selprod_options'] != '' ){
												$txt .= '<br/>'.$requestRow['op_selprod_options'];
											}
											if( $requestRow['op_brand_name'] != '' ){
												$txt .= '<br/><strong>'.Labels::getLabel('LBL_Brand',$adminLangId).':  </strong> '.$requestRow['op_brand_name'];
											}
											
											if( $requestRow['op_shop_name'] != '' ){
												$txt .= '<br/><strong>'.Labels::getLabel('LBL_Shop',$adminLangId).':  </strong> '.$requestRow['op_shop_name'];
											} 
											echo $txt;
											?></td>
                                      <td><strong><?php echo Labels::getLabel('LBL_Qty',$adminLangId); ?>:</strong> <?php echo $requestRow["orrequest_qty"]?></td>
                                    </tr>
                                    <tr>
                                      <td><strong><?php echo Labels::getLabel('LBL_Reason',$adminLangId); ?>: </strong> <?php echo $requestRow['orreason_title']; ?></td>
                                      <td><strong><?php echo Labels::getLabel('LBL_Date',$adminLangId); ?>: </strong><?php echo FatDate::format( $requestRow['orrequest_date'], true ); ?></td>
                                      <td><strong><?php echo Labels::getLabel('LBL_Status',$adminLangId); ?>:</strong> <?php echo $requestStatusArr[$requestRow['orrequest_status']]; ?></td>
                                    </tr>
                                    <tr>
                                      <td><strong><?php echo Labels::getLabel('LBL_Amount',$adminLangId); ?>: </strong><?php 	
										$returnDataArr = CommonHelper::getOrderProductRefundAmtArr($requestRow);	
										/* $priceTotalPerItem = CommonHelper::orderProductAmount($requestRow,'netamount',true);
										$price = 0;	
										
										if($requestRow['orrequest_status'] != OrderReturnRequest::RETURN_REQUEST_STATUS_REFUNDED){
											if(FatApp::getConfig('CONF_RETURN_SHIPPING_CHARGES_TO_CUSTOMER',FatUtility::VAR_INT,0)){
												$shipCharges = isset($requestRow['charges'][OrderProduct::CHARGE_TYPE_SHIPPING][OrderProduct::DB_TBL_CHARGES_PREFIX.'amount'])?$requestRow['charges'][OrderProduct::CHARGE_TYPE_SHIPPING][OrderProduct::DB_TBL_CHARGES_PREFIX.'amount']:0;
												$unitShipCharges = round(($shipCharges / $requestRow['op_qty']),2);
												$priceTotalPerItem = $priceTotalPerItem + $unitShipCharges;		
												$price = $priceTotalPerItem * $requestRow['orrequest_qty'];
											}	
										}
										
										if(!$price){
											$price = $priceTotalPerItem * $requestRow['orrequest_qty'];
											$price = $price + $requestRow['op_refund_shipping'];
										} */
										/* $price = $priceTotalPerItem * $requestRow['orrequest_qty'];
										$price = $price - $requestRow['op_refund_shipping']; */
										
										/* if(!FatApp::getConfig('CONF_RETURN_SHIPPING_CHARGES_TO_CUSTOMER',FatUtility::VAR_INT,0)){
											$shipCharges = isset($requestRow['charges'][OrderProduct::CHARGE_TYPE_SHIPPING][OrderProduct::DB_TBL_CHARGES_PREFIX.'amount'])?$requestRow['charges'][OrderProduct::CHARGE_TYPE_SHIPPING][OrderProduct::DB_TBL_CHARGES_PREFIX.'amount']:0;
											$unitShipCharges = round(($shipCharges / $requestRow['op_qty']),2);
											$priceTotalPerItem = $priceTotalPerItem - $unitShipCharges;
										}	 */
										echo CommonHelper::displayMoneyFormat($returnDataArr['op_refund_amount'], true, true);		
										?> </td>
									<?php if(isset($attachedFile) && !empty($attachedFile)){ ?>
									<td><strong><?php echo Labels::getLabel('LBL_Download_Attached_Files',$adminLangId); ?>:</strong><a href="<?php echo CommonHelper::generateUrl('OrderReturnRequests','downloadAttachedFileForReturn' , array($requestRow["orrequest_id"]));  ?>" class="button small green" > <?php echo Labels::getLabel('LBL_Download',$adminLangId); ?></a></td>
									<?php } ?>
                                    </tr>   
                                </table>
                            </div>
                        </section>
                          
                        <section class="section">
							<div class="sectionhead">
								<h4><?php echo Labels::getLabel('LBL_Seller_/_Customer_Details',$adminLangId); ?></h4>																
							</div>
							<div class="sectionbody">
								<table class="table bordered rounded">
									<tr>
										<th><?php echo Labels::getLabel('LBL_Seller_Details',$adminLangId); ?></th>
										<th><?php echo Labels::getLabel('LBL_Customer_Details',$adminLangId); ?></th>
									</tr>
									<tr>
										<td><strong><?php echo Labels::getLabel('LBL_Shop_Name',$adminLangId); ?>: </strong><?php echo $requestRow["op_shop_name"]?><br/><strong><?php echo Labels::getLabel('LBL_Name',$adminLangId); ?>: </strong><?php echo $requestRow["seller_name"]?><br/><strong><?php echo Labels::getLabel('LBL_Email_ID',$adminLangId); ?>:</strong> <?php echo $requestRow["seller_email"]?><br/><strong><?php echo Labels::getLabel('LBL_Phone',$adminLangId); ?>:</strong> <?php echo $requestRow["seller_phone"]?></td>
										<td><strong><?php echo Labels::getLabel('LBL_Name',$adminLangId); ?>: </strong><?php echo $requestRow["buyer_name"]?><br/>
										<strong><?php echo Labels::getLabel('LBL_Username',$adminLangId); ?>: </strong><?php echo $requestRow["buyer_username"]; ?><br/>
										<strong><?php echo Labels::getLabel('LBL_Email_ID',$adminLangId); ?>: </strong> <?php echo $requestRow["buyer_email"]?><br/><strong><?php echo Labels::getLabel('LBL_Phone',$adminLangId); ?>:</strong> <?php echo $requestRow["buyer_phone"]?></td>
									</tr>
								</table>
							</div>
						</section>
                        
                        <?php echo $returnRequestMsgsSrchForm->getFormHtml(); ?>
                       <section class="section">
							<div class="sectionhead">
								<h4><?php echo Labels::getLabel('LBL_Message_Communication',$adminLangId); ?></h4>
							</div>
							<div id="loadMoreBtnDiv"></div>
							<div class="sectionbody" id="messagesList">
							</div>
						</section>

                        
						<section class="section" id="frmArea">
							<div class="sectionhead">
								<h4><?php echo FatApp::getConfig("CONF_WEBSITE_NAME_".$adminLangId);?> <?php echo Labels::getLabel('LBL_Says',$adminLangId); ?></h4>
							</div>
							<div class="sectionbody space"><?php 
							$frmMsg->setFormTagAttribute('class', 'web_form'); 
							$frmMsg->setFormTagAttribute('onSubmit','setUpReturnOrderRequestMessage(this); return false;');
							$frmMsg->developerTags['colClassPrefix']='col-md-';
							$frmMsg->developerTags['fld_default_col'] = 8;
							echo $frmMsg->getFormHtml(); ?></div>
						</section>
						<?php if($requestRow['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING || $requestRow['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED){ ?>
						<section class="section" id="frmArea">
							<div class="sectionhead">
								<h4><?php echo Labels::getLabel('LBL_Update_Status',$adminLangId); ?></h4>
							</div>
							<div class="sectionbody space"><?php
							$frmUpdateStatus->setFormTagAttribute('class', 'web_form'); 
							$frmUpdateStatus->setFormTagAttribute('onSubmit','setupStatus(this); return false;');
							$frmUpdateStatus->developerTags['colClassPrefix']='col-md-';
							$frmUpdateStatus->developerTags['fld_default_col'] = 8;

							$frmUpdateStatus->getField('orrequest_status')->setFieldTagAttribute('id','orrequest_status');
							$frmUpdateStatus->getField('orrequest_refund_in_wallet')->setFieldTagAttribute('id','orrequest_refund_in_wallet');

							$frmUpdateStatus->getField('orrequest_refund_in_wallet')->setWrapperAttribute('class','wrapper-orrequest_refund_in_wallet hide');
							$frmUpdateStatus->getField('orrequest_admin_comment')->setWrapperAttribute('class','wrapper-orrequest_admin_comment hide');

							echo $frmUpdateStatus->getFormHtml(); ?>
							</div>
						</section>
						<script language="javascript">
						$(document).ready(function(){
							$('#orrequest_refund_in_wallet').change(function(){
								if($(this).is(':checked')){
									$('.wrapper-orrequest_admin_comment').removeClass('hide');
								} else{
									$('.wrapper-orrequest_admin_comment').addClass('hide');
								}
							});
							<?php if($requestRow["orrequest_type"] == OrderReturnRequest::RETURN_REQUEST_TYPE_REFUND){ ?>
								$('#orrequest_status').change(function(){
									if($(this).val() === '2'){
										$('.wrapper-orrequest_refund_in_wallet').removeClass('hide');
										$('#orrequest_refund_in_wallet').change();
									} else{
										$('.wrapper-orrequest_admin_comment').addClass('hide');
										$('.wrapper-orrequest_refund_in_wallet').addClass('hide');
									}
								});
							<?php } ?>
						});	
						</script>
						<?php } ?>
                    </div>         
                </div>
            </div>
        </div>  