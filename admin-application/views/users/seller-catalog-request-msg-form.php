<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row">
	<div class="col-sm-12">
		<h1><?php echo Labels::getLabel('LBL_Seller_Catalog_Request_Message',$adminLangId); ?></h1>
	</div>
	<div class="col-sm-12">
		<?php echo $searchFrm->getFormHtml(); ?>
		<section class="section">
			<div class="sectionhead">
				<h4><?php echo Labels::getLabel('LBL_Messages_Communication',$adminLangId); ?></h4>																
			</div>
			
			<div class="sectionbody space" >
				<div id="loadMoreBtnDiv"></div>			
				<div id="messagesList">
				</div>
			</div>
		</section>
		<section class="section" id="frmArea">
			<div class="sectionhead">
				<h4><?php echo FatApp::getConfig("CONF_WEBSITE_NAME_".$adminLangId);?> <?php echo Labels::getLabel('LBL_Says',$adminLangId); ?></h4>
			</div>
			<div class="sectionbody space"><?php 
				$frm->setFormTagAttribute('class', 'web_form form_horizontal'); 
				$frm->setFormTagAttribute('onSubmit','setUpCatalogRequestMessage(this); return false;');
				$frm->developerTags['colClassPrefix']='col-md-';
				$frm->developerTags['fld_default_col'] = 12;
				echo $frm->getFormHtml(); ?>
			</div>
		</section>
	</div>
</div>