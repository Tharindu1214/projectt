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
                                <h5><?php echo Labels::getLabel('LBL_Manage_Order_Cancellation_Requests',$adminLangId); ?> </h5>
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
								$frmSearch->setFormTagAttribute ( 'onsubmit', 'searchOrderCancellationRequests(this); return(false);');
								$frmSearch->setFormTagAttribute ( 'class', 'web_form' );					
								$frmSearch->developerTags['colClassPrefix'] = 'col-md-';							
								$frmSearch->developerTags['fld_default_col'] = 12;
								
								$keywordFld = $frmSearch->getField('keyword');
								$keywordFld->developerTags['col'] = 12;
								$keywordFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Search_in_Order/Invoice,_Request_Comment',$adminLangId));
								
								$statusFld = $frmSearch->getField('ocrequest_status');
								$statusFld->developerTags['col'] = 4;
								
								$opStatusFld = $frmSearch->getField('op_status_id');
								$opStatusFld->developerTags['col'] = 4;
								
								$ocReasonFld = $frmSearch->getField('ocrequest_ocreason_id');
								$ocReasonFld->developerTags['col'] = 4;
								
								$buyerFld = $frmSearch->getField('buyer');
								$buyerFld->developerTags['col'] = 4;
								
								$sellerFld = $frmSearch->getField('seller');
								$sellerFld->developerTags['col'] = 4;
								
								$dateFromFld = $frmSearch->getField('date_from');
								$dateFromFld->setFieldTagAttribute('class','field--calender');
								$dateFromFld->developerTags['col'] = 2;

								$dateToFld = $frmSearch->getField('date_to');
								$dateToFld->setFieldTagAttribute('class','field--calender');
								$dateToFld->developerTags['col'] = 2;

								$submitBtnFld = $frmSearch->getField('btn_submit');
								$submitBtnFld->setFieldTagAttribute('class','btn--block');
								$submitBtnFld->developerTags['col'] = 4;

								$btn_clear = $frmSearch->getField('btn_clear');
								$btn_clear->addFieldTagAttribute('onclick', 'clearOrderCancellationRequestSearch()');
								echo  $frmSearch->getFormHtml();
							?>
						</div>
					</section>
                   
                    <section class="section">
						<div class="sectionhead">
							<h4><?php echo Labels::getLabel('LBL_Order_Cancellation_Requests_List',$adminLangId); ?> </h4>
						</div>
						<div class="sectionbody">
							<div class="tablewrap">
								<div id="requestsListing">
									<?php echo Labels::getLabel('LBL_Processing...',$adminLangId); ?>
								</div>
							</div>
						</div>
					</section>
			 
				</div>
			</div>
		</div>
	</div>