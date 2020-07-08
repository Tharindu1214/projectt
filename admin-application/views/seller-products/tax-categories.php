<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div id="body" class="body bg--gray">
    <section class="dashboard">
		<?php $this->includeTemplate('_partial/dashboardTop.php'); ?>  
		<div class="fixed-container">
			<div class="row">
				<?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?>                      
				<div class="col-md-10 panel__right--full " >
					<div class="cols--group">
						<div class="panel__head">
						   <h2><?php echo Labels::getLabel('LBL_Tax_Categories',$adminLangId); ?></h2>
						</div>
						<div class="panel__body">
							<div class="box box--white box--space"> 
								<div class="box__head">
								   <h5><?php echo Labels::getLabel('LBL_Manage_Tax_Rates',$adminLangId); ?></h5>
								</div>
								<div class="box__body">										
									<div class="form__cover">
										<div class="search search--sort">
											<div class="search__field">
												<?php
												$frmSearch->setFormTagAttribute ( 'id', 'frmSearchTaxCat' );
												$frmSearch->setFormTagAttribute( 'onsubmit', 'searchTaxCategories(this); return(false);' );
												$frmSearch->getField('keyword')->addFieldTagAttribute('placeholder',Labels::getLabel('LBL_Search' , $adminLangId));
												echo $frmSearch->getFormTag();
												echo $frmSearch->getFieldHtml('keyword');
													
												echo $frmSearch->getFieldHtml('btn_submit');?>  
												<i class="fa fa-search"></i>
												</form>
											</div>
										</div>
									</div>
									<span class="gap"></span>										
										<div id="listing"><?php echo Labels::getLabel('LBL_Loading..',$adminLangId); ?></div>                                   	
								
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>	