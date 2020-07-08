<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row">
	<div class="col-sm-12"> 
		<h1><?php echo Labels::getLabel('LBL_Add_A_Product',$adminLangId); ?></h1>	
		<section class="section searchform_filter">
			<div class="sectionhead">
				<h4><?php echo Labels::getLabel('LBL_Search...',$adminLangId); ?></h4>
			</div>
			<div class="sectionbody space togglewrap" style="display:none;">
				<?php
					$frmSearchCatalogProduct->setFormTagAttribute ( 'id', 'frmSearchCatalogProduct' );
					$frmSearchCatalogProduct->setFormTagAttribute ( 'class', 'web_form' ); 
					$frmSearchCatalogProduct->setFormTagAttribute( 'onsubmit', 'searchCatalogProducts(this); return(false);' );
					$frmSearchCatalogProduct->getField('keyword');
					$frmSearchCatalogProduct->developerTags['colClassPrefix'] = 'col-md-';							
					$frmSearchCatalogProduct->developerTags['fld_default_col'] = 6;
					$frmSearchCatalogProduct->getField('keyword');
					$btn_clear = $frmSearchCatalogProduct->getField('btn_clear');
					$btn_clear->addFieldTagAttribute('onclick', 'clearSearch()');
					$frmSearchCatalogProduct->getField('btn_submit');
					echo  $frmSearchCatalogProduct->getFormHtml();
					/* echo $frmSearchCatalogProduct->getFormTag();
					echo $frmSearchCatalogProduct->getFieldHtml('keyword');
					echo $frmSearchCatalogProduct->getFieldHtml('btn_submit'); */
				?>
				</form>				
			</div>
		</section>
	</div>
	<div class="col-sm-12">  		
		<section class="section">
			<div class="sectionhead">
				<h4><?php echo Labels::getLabel('LBL_Catalog_Listing',$adminLangId); ?></h4>
			</div>
			<div class="sectionbody">
				<div class="tablewrap" >
					<div id="listing"><?php echo Labels::getLabel('LBL_Processing..',$adminLangId); ?></div>  <span class="gap"></span>
				</div> 
			</div>
		</section>
	</div>		
</div>