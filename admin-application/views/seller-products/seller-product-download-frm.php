
<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Digital_Downloads',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">
			<div class="col-sm-12">
				<div class="tabs_nav_container responsive flat">
					<?php /* require_once('sellerCatalogProductTop.php'); */?>
					<div class="tabs_panel_wrap ">						
						<?php
						$selprodDownloadFrm->setFormTagAttribute('id', 'frmDownload');
						$selprodDownloadFrm->setFormTagAttribute('class','web_form');
						$selprodDownloadFrm->developerTags['colClassPrefix'] = 'col-md-';
						$selprodDownloadFrm->developerTags['fld_default_col'] = 8; 
						
						$langFld = $selprodDownloadFrm->getField('lang_id');	
						$langFld->setWrapperAttribute( 'class' , 'lang_fld');
						
						$downloadableLinkFld = $selprodDownloadFrm->getField('selprod_downloadable_link');	
						$downloadableLinkFld->setWrapperAttribute( 'class' , 'downloadable_link_fld');
						
						$downloadableFileFld = $selprodDownloadFrm->getField('downloadable_file');	
						$downloadableFileFld->setWrapperAttribute( 'class' , 'downloadable_file_fld');
						$downloadableFileFld->setFieldTagAttribute( 'onchange','setUpSellerProductDownloads('.applicationConstants::DIGITAL_DOWNLOAD_FILE.'); return false;');
						
						$submitButton = $selprodDownloadFrm->getField('btn_submit');	
						$submitButton->setWrapperAttribute( 'class' , 'submit_button');
						$submitButton->setFieldTagAttribute( 'onClick','setUpSellerProductDownloads('.applicationConstants::DIGITAL_DOWNLOAD_FILE.'); return false;');
						
						echo $selprodDownloadFrm->getFormHtml(); ?>
						<div class="col-md-12 filesList">
							<?php 
							$arr_flds = array(
								'listserial'=>Labels::getLabel('LBL_Sr_No.', $adminLangId),
								'afile_name' => Labels::getLabel('LBL_File', $adminLangId),
								'afile_lang_id' => Labels::getLabel('LBL_Language', $adminLangId),
								'action' => Labels::getLabel('LBL_Action', $adminLangId),					
							);
							
							$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table'));
							$th = $tbl->appendElement('thead')->appendElement('tr',array('class' => 'hide--mobile'));
							foreach ($arr_flds as $val) {
								$e = $th->appendElement('th', array(), $val);
							}

							$sr_no = 0;
							foreach ($attachments as $sn => $row){
								$sr_no++;
								$tr = $tbl->appendElement('tr');

								foreach ($arr_flds as $key=>$val){
									$td = $tr->appendElement('td');
									switch ($key){
										case 'listserial':
											$td->appendElement('plaintext', array(), $sr_no,true);
										break;
										case 'afile_lang_id':
											$lang_name = Labels::getLabel('LBL_All',$adminLangId);
											if( $row['afile_lang_id'] > 0 ){
												$lang_name = $languages[$row['afile_lang_id']];
											}
											$td->appendElement('plaintext', array(),  $lang_name, true);
										break;
										case 'action':
											$ul = $td->appendElement("ul",array("class"=>"actions"),'',true);
											
											$li = $ul->appendElement("li");
											$li->appendElement("a", array('title' => Labels::getLabel('LBL_Product_Images', $adminLangId),
											'onclick' => 'deleteDigitalFile('.$row['afile_record_id'].','.$row['afile_id'].')', 'href'=>'javascript:void(0)'),
											Labels::getLabel('LBL_Delete', $adminLangId), true);

										break;
										default:
											$td->appendElement('plaintext', array(), $row[$key],true);
										break;
									}
								}
							}
							if( !empty($attachments) ){					
								echo $tbl->getHtml();				
							}
							?>		
						</div>
					</div>
				</div>	
			</div>
		</div>
	</div>
</section>
	
<script  type="text/javascript">
	var DIGITAL_DOWNLOAD_FILE = <?php echo applicationConstants::DIGITAL_DOWNLOAD_FILE; ?>;
	var DIGITAL_DOWNLOAD_LINK = <?php echo applicationConstants::DIGITAL_DOWNLOAD_LINK; ?>;
	
	$(document).ready(function(){
		$("select[name='download_type']").change(function(){
			if( $(this).val() == DIGITAL_DOWNLOAD_FILE ){
				$(".lang_fld").show();
				$(".downloadable_file_fld").show();
				$(".filesList").show();
				$(".downloadable_link_fld").hide();
				$(".submit_button").hide();
			}else{
				$(".lang_fld").hide();
				$(".downloadable_file_fld").hide();
				$(".filesList").hide();
				$(".downloadable_link_fld").show();
				$(".submit_button").show();
			}
		});
	});
	
	$("select[name='download_type']").trigger('change');
	
</script>