<table class="table table--view">
	 <tr>
		 <th><?php echo Labels::getLabel('LBL_Name',$siteLangId);?> </th>
		 <td><?php echo $info['ub_account_holder_name'];?></td>
	 </tr>
	 <tr>
		 <th><?php echo Labels::getLabel('LBL_Bank_Name',$siteLangId);?></th>
		 <td><?php echo $info['ub_bank_name'];?></td>
	 </tr>
	 <tr>
		 <th><?php echo Labels::getLabel('LBL_Account',$siteLangId);?></th>
		 <td><?php echo $info['ub_account_number'];?></td>
	 </tr>
	 <tr>
		 <th><?php echo Labels::getLabel('LBL_IFSC_or_swift_code',$siteLangId);?></th>
		 <td><?php echo $info['ub_ifsc_swift_code'];?>	</td>
	 </tr>
	 <tr>
		 <th><?php echo Labels::getLabel('LBL_Address',$siteLangId);?> </th>
		 <td><?php echo $info['ub_bank_address'];?>	</td>
	 </tr>
</table>