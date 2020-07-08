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
				<?php 
					$arr_flds = array(
						'image'=>'Image',
						'detail'=>'Detail',
						'scatrequestmsg_msg'=>'Message',
						'scatrequestmsg_date'=>'Date',
					);
					$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table--listing catalogMessages'));

					$th = $tbl->appendElement('thead')->appendElement('tr',array('class'=>'tr--first'));
					foreach ($arr_flds as $val) {
						$e = $th->appendElement('th', array(), $val);
					}
					
					$tbl->appendElement('tbody');
					
					echo $tbl->getHtml();					
				?>				
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