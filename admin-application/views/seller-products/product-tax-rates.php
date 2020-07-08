<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<section class="section">
<div class="sectionhead">
   
    <h4><?php echo Labels::getLabel('LBL_Product_Setup',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
<div class="row">

<div class="col-sm-12">
<?php /*<div class="tabs_nav_container responsive flat">
		<?php require_once('sellerCatalogProductTop.php');?>
	</div>*/?>
	<div class="tabs_nav_container responsive">
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">				
				<?php 
					$arr_flds = array(
						'listserial'=> Labels::getLabel( 'LBL_Sr.', $adminLangId ),
						'taxcat_name' => Labels::getLabel( 'LBL_Tax_Category', $adminLangId ),
						'taxval_value' => Labels::getLabel( 'LBL_Value', $adminLangId ),				
						'action'	=>	Labels::getLabel('LBL_Action', $adminLangId),
					);
					$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table'));
					$th = $tbl->appendElement('thead')->appendElement('tr',array('class' => 'hide--mobile'));
					foreach ($arr_flds as $val) {
						$e = $th->appendElement('th', array(), $val);
					}

					$sr_no = 0;
					if (is_array($arrListing) && count($arrListing) > 0 && !empty($arrListing[0])){
						foreach ($arrListing as $sn => $row){
							$sr_no++;
							$tr = $tbl->appendElement('tr',array());
							
							if(is_array($row) && count($row)){
								foreach ($arr_flds as $key=>$val){
									$td = $tr->appendElement('td');
									switch ($key){
										case 'listserial':
											$td->appendElement('plaintext', array(), ''.$sr_no,true);
										break;						
										case 'taxval_value';
											$str = CommonHelper::displayTaxFormat($row['taxval_is_percent'],$row['taxval_value']);
											$td->appendElement( 'plaintext', array(), ''.$str,true );
										break;						
										case 'action':								
												$ul = $td->appendElement("ul",array("class"=>"actions"),'',true);
												$li = $ul->appendElement("li");
												$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'',
												'title'=>Labels::getLabel('LBL_Edit',$adminLangId),"onclick"=>"changeTaxCategory(".$selprod_id.")"),
												'<i class="icon ion-edit"></i>', true);
												$li = $ul->appendElement("li");
												$li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'',
												'title'=>Labels::getLabel('LBL_Reset_to_Default',$adminLangId),"onclick"=>"resetTaxRates(".$selprod_id.")"),
												'<i class="icon  ion-reply"></i>', true);
										break;
										default:
											$td->appendElement('plaintext', array(), ''.$row[$key],true);
										break;
									}
								}
							}
						}
						echo $tbl->getHtml();
					}
					else{
						// $tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Tax_Rates_added_to_this_product', $adminLangId));
						$this->includeTemplate('_partial/no-record-found.php',array('adminLangId' => $adminLangId),false);
					}
					// echo $tbl->getHtml();
					?>
			</div>
		</div>
	</div>	
</div>
</div>

</div>
</section>