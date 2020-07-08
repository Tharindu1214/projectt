<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>

    <div class='page'>
        <div class='container container-fluid'>
            <div class="row">
                <div class="col-lg-12 col-md-12 space">
                    <div class="page__title">
                        <div class="row">
                            <div class="col--first col-lg-6">
                                <span class="page__icon">
								<i class="ion-android-star"></i></span>
                                <h5><?php echo Labels::getLabel('LBL_Manage_Shipping_Company_Orders_of',$adminLangId); ?> <?php echo $userRow['user_name']; ?></h5>
                                <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>

                            </div>
						</div>
					</div>
					<section class="section searchform_filter">
						<div class="sectionhead">
							<h4> <?php echo Labels::getLabel('LBL_Search...',$adminLangId); ?></h4>
						</div>
						<div class="sectionbody space togglewrap" style="display:none;">
							<?php 
								$frmSearch->setFormTagAttribute ( 'onsubmit', 'searchSellerOrders(this,1); return(false);');
								$frmSearch->setFormTagAttribute ( 'class', 'web_form' );					
								$frmSearch->developerTags['colClassPrefix'] = 'col-md-';							
								$frmSearch->developerTags['fld_default_col'] = 12;
								
								$keywordFld = $frmSearch->getField('keyword');
								$keywordFld->developerTags['col'] = 6;
								$keywordFld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Search_In_Keywords_For_Orders',$adminLangId).'</small>';
								
								$buyerFld = $frmSearch->getField('buyer');
								$buyerFld->developerTags['col'] = 6;
								$buyerFld->htmlAfterField = '<small></small>';
								
								$statusFld = $frmSearch->getField('op_status_id');
								$statusFld->developerTags['col'] = 6;
								
								$shopFld = $frmSearch->getField('shop_name');
								$shopFld->developerTags['col'] = 6;
								$shopFld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Search_In_Shop_Details_For_Orders',$adminLangId).'</small>';
								
								/* $customerFld = $frmSearch->getField('customer_name');
								$customerFld->developerTags['col'] = 6;
								$customerFld->htmlAfterField = '<small>'.Labels::getLabel('LBL_Search_In_Customer_Details_For_Orders',$adminLangId).'</small>'; */
								
								$dateFromFld = $frmSearch->getField('date_from');
								$dateFromFld->setFieldTagAttribute('class','field--calender');
								$dateFromFld->developerTags['col'] = 2;

								$dateToFld = $frmSearch->getField('date_to');
								$dateToFld->setFieldTagAttribute('class','field--calender');
								$dateToFld->developerTags['col'] = 2;

								$priceFromFld = $frmSearch->getField('price_from');
								$priceFromFld->developerTags['col'] = 2;

								$priceToFld = $frmSearch->getField('price_to');
								$priceToFld->developerTags['col'] = 2;
									
								$submitBtnFld = $frmSearch->getField('btn_submit');
								$submitBtnFld->setFieldTagAttribute('class','btn--block');
								$submitBtnFld->developerTags['col'] = 4;

								$btn_clear = $frmSearch->getField('btn_clear');
								$btn_clear->addFieldTagAttribute('onclick', 'clearSellerOrderSearch()');
								echo  $frmSearch->getFormHtml();
							?>
						</div>
					</section>
                   
                    <section class="section">
						<div class="sectionbody">
							<div class="tablewrap">
								<div id="ordersListing">
									<?php echo Labels::getLabel('LBL_Processing...',$adminLangId); ?>
								</div>
							</div>
						</div>
					</section>
			 
				</div>
			</div>
		</div>
	</div>