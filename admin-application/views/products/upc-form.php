<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
<div class="sectionhead">   
    <h4><?php echo Labels::getLabel('LBL_Product_UPC/EAN_Setup',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
<div class="row">
<div class="col-sm-12">	
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a <?php echo ($product_id) ? "onclick='productOptionsForm( ".$product_id.", 0 );'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>			
			<li ><a class="active" <?php echo ($product_id) ? "onclick='upcForm( ".$product_id.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_EAN/UPC_Setup',$adminLangId); ?></a></li>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">				
				<div class="tabs_panel_wrap">
					<div class="tabs_panel">
					<?php if(!empty($optionCombinations)){?>
						<form name="upcFrm">
						<table width="100%" class="table table-responsive" cellpadding="0" cellspacing="0">
							<thead>
								<tr>
									<th><?php echo Labels::getLabel('LBL_Sr.',$adminLangId);?></th>
									<?php 
									foreach($productOptions as $option){
										echo "<th>".$option['option_name']."</th>";
									}
									?>
									<th><?php echo Labels::getLabel('LBL_EAN/UPC_code',$adminLangId);?></th>									
									<th><?php echo Labels::getLabel('LBL_Action',$adminLangId);?></th>
								</tr>
								<?php 
								$count = 0;												
								foreach($optionCombinations as $optionValueId=>$optionValue){
									$count++;
									$arr = explode('|',$optionValue);
									$key = str_replace('|',',',$optionValueId);
								?>
								<tr>
									<td><?php echo $count;?></td>							
									<?php 							
									foreach($arr as $val){	
										echo "<td>".$val."</td>";							
									}
									?>
									<td><input type="text" id="code<?php echo $optionValueId?>" name="code<?php echo $optionValueId?>" value="<?php echo (isset($upcCodeData[$key]['upc_code']))?$upcCodeData[$key]['upc_code']:'';?>"></td>								
									<td><ul class="actions"><li><a href="javascript:void(0)" title="<?php echo Labels::getLabel('Lbl_Click_to_Save',$adminLangId);?>" onClick="updateUpc('<?php echo $product_id;?>','<?php echo $optionValueId;?>')"><i class="ion-checkmark-circled icon"></i></a></li></ul></td>
								</tr>
								<?php }?>	
							</thead>
						</table>
						</form>
					<?php }else{
						echo Labels::getLabel('LBL_Please_configure_option_group',$adminLangId);
					}?>
					</div>
				</div>
			</div>
		</div>
	</div>	
</div>
</div>
</div>
</section>