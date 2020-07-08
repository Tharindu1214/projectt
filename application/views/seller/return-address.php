<table class="table table--view">
	 <tr>
		 <th><?php echo Labels::getLabel('LBL_Name',$siteLangId);?> </th>
		 <td><?php echo $info['ura_name'];?></td>
	 </tr>
	 <?php if($info['ura_phone']!=''){?>
	 <tr>
		 <th><?php echo Labels::getLabel('LBL_Phone',$siteLangId);?></th>
		 <td><?php echo $info['ura_phone'];?></td>
	 </tr>	
	 <?php }?>
		<tr>
			<th><?php echo Labels::getLabel('LBL_Address',$siteLangId);?> </th>
			<td><?php 
			$address =  $info['ura_address_line_1'];
			$address.= ($info['ura_address_line_2']!='')?' '.$info['ura_address_line_2']:'';
			echo $address;?>	</td>
		</tr>
	 <?php if($info['ura_zip']!=''){?>
		<tr>
			<th><?php echo Labels::getLabel('LBL_Postal_code',$siteLangId);?></th>
			<td><?php echo $info['ura_zip'];?></td>
		</tr>
	<?php }?>	 
		<tr>
			<th><?php echo Labels::getLabel('LBL_Country',$siteLangId);?></th>
			<td><?php echo $info['country_name'];?></td>
		</tr>
		<tr>
			<th><?php echo Labels::getLabel('LBL_State',$siteLangId);?></th>
			<td><?php echo $info['state_name'];?></td>
		</tr>
	  <?php if($info['ura_city']!=''){?>
		<tr>
			<th><?php echo Labels::getLabel('LBL_City',$siteLangId);?></th>
			<td><?php echo $info['ura_city'];?></td>
		</tr>	
	 <?php }?>
	
</table>