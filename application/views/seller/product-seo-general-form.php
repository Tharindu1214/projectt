<div class="tabs tabs--small   tabs--scroll clearfix">
    <?php require_once('sellerCatalogProductTop.php');?>
</div>
<div class="cards">
	<div class="cards-content pt-3 pl-4 pr-4 ">		
		<div class="tabs__content form">
			<div class="row">
				<div class="col-md-12">
					<?php require_once('sellerProductSeoTop.php');?>
					<div class="form__subcontent">
						<?php
							$productSeoForm->setFormTagAttribute('class', 'form form--horizontal');
							$productSeoForm->setFormTagAttribute('onsubmit', 'setupProductMetaTag(this); return(false);');
							$productSeoForm->developerTags['colClassPrefix'] = 'col-lg-4 col-md-';
							$productSeoForm->developerTags['fld_default_col'] = 4;
							echo $productSeoForm->getFormHtml();
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
