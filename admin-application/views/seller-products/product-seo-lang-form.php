<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_SEO_CONTENT', $adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">
			<div class="col-sm-12">
				<div class="border-box">
					<div class="tabs_nav_container responsive">
						<?php require_once('sellerProductSeoTop.php');?>
					</div>
					<div class="tabs_nav_container responsive">
						<div class="tabs_panel_wrap">
							<div class="tabs_panel">
								<?php
                    $productSeoLangForm->setFormTagAttribute('class', 'web_form form--horizontal layout--'.$formLayout);
                    $productSeoLangForm->setFormTagAttribute('onsubmit', 'setupProductLangMetaTag(this); return(false);');
                    $productSeoLangForm->developerTags['colClassPrefix'] = 'col-md-';
                    $productSeoLangForm->developerTags['fld_default_col'] = 8;
                    //$customProductFrm->getField('option_name')->setFieldTagAttribute('class','mini');
                    echo $productSeoLangForm->getFormHtml();
                ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>