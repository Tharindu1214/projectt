<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
	<thead>
		<tr>
		<td colspan="2" class="nopadding"><table id="shipping" class="table">
		<thead>
		<tr>
		<th width="17%"><?php echo Labels::getLabel('LBL_Ships_To',$adminLangId)?></th>
		<th width="17%"><?php echo Labels::getLabel('LBL_Shipping_Company',$adminLangId)?></th>
		<th width="17%"><?php echo Labels::getLabel('LBL_Processing_Time',$adminLangId)?></th>
		<th width="25%"><?php echo Labels::getLabel('LBL_Cost',$adminLangId) .' ['.CommonHelper::getCurrencySymbol(true).']';?></th>
		<th width="20%"><?php echo Labels::getLabel('LBL_Each_Additional_Item',$adminLangId).' ['.CommonHelper::getCurrencySymbol(true).']';?> </th>
		<th></th>
		</tr>
		</thead>
		<tbody>
		<?php $shipping_row = 0; /* CommonHelper::printArray($shipping_rates); die; */ ?>
		<?php
			if(!empty($shipping_rates) && count($shipping_rates)>0){
			foreach ($shipping_rates as $shipping) { /* print_r($shipping);die;  */?>
				 <input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][pship_id]" value="<?php echo $shipping['pship_id']; ?>" />
				<tr id="shipping-row<?php echo $shipping_row; ?>">
				<td style="position: relative;">
				<input type="text" name="product_shipping[<?php echo $shipping_row; ?>][country_name]" value="<?php echo $shipping["pship_country"]!="-1"?$shipping["country_name"]:"&#8594;".Labels::getLabel('LBL_EveryWhere_Else',$adminLangId);?>" placeholder="<?php echo Labels::getLabel('LBL_Shipping',$adminLangId)?>" /><input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][country_id]" value="<?php echo $shipping["pship_country"]?>" /></td>
				<td style="position: relative;">
				<input type="text" name="product_shipping[<?php echo $shipping_row; ?>][company_name]" value="<?php echo isset($shipping["scompany_name"]) ? $shipping["scompany_name"] : ''; ?>" placeholder="<?php echo Labels::getLabel('LBL_Company',$adminLangId)?>" /><input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][company_id]" value="<?php echo $shipping["pship_company"]?>" /></td>
				<td style="position: relative;">
				<input type="text" name="product_shipping[<?php echo $shipping_row; ?>][processing_time]" value="<?php echo ShippingDurations::getShippingDurationTitle($shipping,$adminLangId)?>" placeholder="<?php echo Labels::getLabel('LBL_Processing_Time',$adminLangId)?>" /><input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][processing_time_id]" value="<?php echo isset($shipping["pship_duration"]) ? $shipping["pship_duration"] : '';?>" /></td>
				<td><input type="text" name="product_shipping[<?php echo $shipping_row; ?>][cost]" value="<?php echo isset($shipping["pship_charges"]) ? $shipping["pship_charges"] : '';?>" placeholder="<?php echo Labels::getLabel('LBL_Cost',$adminLangId)?>" /></td>
				<td>
				<input type="text" name="product_shipping[<?php echo $shipping_row; ?>][additional_cost]" value="<?php echo isset($shipping["pship_additional_charges"]) ? $shipping["pship_additional_charges"] : ''; ?>" placeholder="<?php echo Labels::getLabel('LBL_Each_Additional_Item',$adminLangId)?>" /></td>
				<td><button type="button" onclick="$('#shipping-row<?php echo $shipping_row; ?>').remove();" class="btn btn--secondary ripplelink" title="<?php echo Labels::getLabel('LBL_Remove',$adminLangId)?>"  ><i class="ion-minus-round"></i></button>
				<!--<a class="button red medium" onclick="$('#shipping-row<?php echo $shipping_row; ?>').remove();"  title="Remove">Remove</a>--></td>
				</tr>
		<?php $shipping_row++; ?>
		<?php }
			}
			?>
		</tbody>
		<tfoot>
			<tr>
			<td colspan="5"></td>
			<td ><button type="button" class="btn btn--secondary ripplelink" title="<?php echo Labels::getLabel('LBL_Shipping',$adminLangId)?>" onclick="addShipping();" ><i class="ion-plus-round"></i></button></td>
			</tr>
		</tfoot>
		<script >
			var shipping_row = <?php echo $shipping_row;?>;
			addShipping = function(){
			
				html  = '<tr id="shipping-row' + shipping_row + '">';
				html += "  <td style='position: relative;'><input type='text' name='product_shipping[" + shipping_row + "][country_name]' value='' placeholder='<?php echo Labels::getLabel('LBL_Ships_To',$adminLangId)?>' /><input type='hidden' name='product_shipping[" + shipping_row + "][country_id]' value='' /></td>";
				
				html += "  <td style='position: relative;'><input type='text' name='product_shipping[" + shipping_row + "][company_name]' value='' placeholder='<?php echo Labels::getLabel('LBL_Shipping_Company',$adminLangId)?>' /><input type='hidden' name='product_shipping[" + shipping_row + "][company_id]' value='' /></td>";
				
				html += "  <td style='position: relative;'><input type='text' name='product_shipping[" + shipping_row + "][processing_time]' value='' placeholder='<?php echo Labels::getLabel('LBL_Processing_Time',$adminLangId)?>' /><input type='hidden' name='product_shipping[" + shipping_row + "][processing_time_id]' value='' /></td>";
				
				html += "  <td><input type='text' name='product_shipping[" + shipping_row + "][cost]' value='' placeholder='<?php echo Labels::getLabel('LBL_Cost',$adminLangId)?>' /></td>";
				
				html += "<td><input type='text' name='product_shipping[" + shipping_row + "][additional_cost]' value='' placeholder='<?php echo Labels::getLabel('LBL_Each_Additional_Item',$adminLangId)?>' /></td>";
				
				html += "  <td><button type='button' class='btn btn--secondary ripplelink' title='<?php echo Labels::getLabel('LBL_Remove',$adminLangId)?>' onclick='removeShippingRow(" + shipping_row + ")' ><i class='ion-minus-round'></i></button></td>";
				
				html += '</tr>';
				$('#shipping tbody').append(html);
				shippingautocomplete(shipping_row);
				shipping_row++;
				
			}
			removeShippingRow = function(shipping_row){
				$("#shipping-row" + shipping_row).remove();
			}
			
			$('#shipping tbody tr').each(function(index, element) {
				shippingautocomplete(index);
			});
		</script>