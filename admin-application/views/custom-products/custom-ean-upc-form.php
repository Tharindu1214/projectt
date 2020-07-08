<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
<div class="sectionhead">   
    <h4><?php echo Labels::getLabel('LBL_Custom_Catalog_Request',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
<div class="row">
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a  <?php echo ($preqId) ? "onClick='productForm( ".$preqId.", 0 );'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<li><a <?php echo ($preqId) ? "onClick='sellerProductForm(".$preqId.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Inventory/Info',$adminLangId); ?></a></li>
			<li><a  <?php echo ($preqId) ? "onclick='customCatalogSpecifications( ".$preqId." );'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specifications', $adminLangId );?></a></li>
			<?php 
				foreach($languages as $langId=>$langName){?>
					<li class="<?php echo (!$preqId) ? 'fat-inactive' : ''; ?>"><a href="javascript:void(0);" <?php echo ($preqId) ? "onClick='productLangForm( ".$preqId.",".$langId." );'" : ""; ?>><?php echo Labels::getLabel('LBL_'.$langName,$adminLangId);?></a></li>
				<?php }
			?>
			<li><a class="active" <?php echo ($preqId) ? "onClick='customEanUpcForm(".$preqId.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_EAN/UPC_setup',$adminLangId); ?></a></li>
			<li><a <?php echo ($preqId) ? "onClick='updateStatusForm( ".$preqId.");'" : ""; ?> href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Change_Status',$adminLangId); ?></a></li>
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php if(!empty($productOptions)){?>
						<form class="web_form form_horizontal" name="upcFrm" onSubmit="setupEanUpcCode(<?php echo $preqId;?>,this); return(false);">
						<table width="100%" class="table table-responsive" cellpadding="0" cellspacing="0">
							<thead>
								<tr>
									<th><?php echo Labels::getLabel('LBL_Sr.',$adminLangId);?></th>
									<?php 
									foreach($productOptions as $option){
										echo "<th>".$option['option_name']."</th>";
									}
									?>
									<th><?php echo Labels::getLabel('LBL_EAN/UPC_code',$adminLangId);?></th></tr>
									<?php 
									$arr  = array();
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
										<td><input type="text" id="code<?php echo $optionValueId?>" name="code<?php echo $optionValueId?>" value="<?php echo (isset($upcCodeData[$optionValueId]))?$upcCodeData[$optionValueId]:'';?>" onBlur="validateEanUpcCode(this.value)"></td>
									</tr>
									<?php }?>	
								<tr>
									<td></td>
									<td colspan="<?php echo count($arr);?>"></td>
									<td><input type="submit" name="submit" value="<?php echo Labels::getLabel('LBL_Update',$adminLangId);?>"></td>
								</tr>
							</thead>
						</table>
						</form>
						<?php }else{?>
							<?php echo Labels::getLabel('LBL_Please_Configure_Option_Group_In_General_Section',$adminLangId);?>
						<?php }?>
			</div>
		</div>
	</div>
</div>
</div>
</div>
</section>