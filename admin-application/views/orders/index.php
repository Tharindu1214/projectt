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
                                <h5><?php echo Labels::getLabel('LBL_Manage_Orders',$adminLangId); ?> </h5>
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
								$frmSearch->setFormTagAttribute ( 'onsubmit', 'searchOrders(this,1); return(false);');
								$frmSearch->setFormTagAttribute ( 'class', 'web_form' );					
								$frmSearch->developerTags['colClassPrefix'] = 'col-md-';							
								$frmSearch->developerTags['fld_default_col'] = 12;
								
								$keywordFld = $frmSearch->getField('keyword');
								$keywordFld->developerTags['col'] = 4;
								$keywordFld->htmlAfterField = '<small>'. Labels::getLabel('LBL_Search_in_Order_Id,_Customer_Name,_Customer_Username_and_Customer_Email_Id',$adminLangId) .'</small>';
								
								$buyerFld = $frmSearch->getField('buyer');
								$buyerFld->developerTags['col'] = 4;
								$buyerFld->htmlAfterField = '<small></small>';
								
								$statusFld = $frmSearch->getField('order_is_paid');
								$statusFld->developerTags['col'] = 4;
								
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
								$btn_clear->addFieldTagAttribute('onclick', 'clearOrderSearch()');
								echo  $frmSearch->getFormHtml();
							?>
						</div>
					</section>
                   
                    <section class="section">
						<div class="sectionhead">
							<h4><?php echo Labels::getLabel('LBL_Customers_Orders_List',$adminLangId); ?> </h4>
						</div>
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