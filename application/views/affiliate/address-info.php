<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<table class="table table--view">
	<tr>
		<th><?php echo Labels::getLabel('LBL_Company',$siteLangId);?> </th>
		<td><?php echo CommonHelper::displayNotApplicable($siteLangId, $userData['uextra_company_name']);?></td>
	</tr>
	<tr>
		<th><?php echo Labels::getLabel('LBL_Website',$siteLangId);?></th>
		<td><?php echo CommonHelper::displayNotApplicable($siteLangId, $userData['uextra_website']);?></td>
	</tr>
	<tr>
		<th><?php echo Labels::getLabel('LBL_Address_Line_1',$siteLangId);?></th>
		<td><?php echo CommonHelper::displayNotApplicable( $siteLangId, $userData['user_address1']); ?></td>
	</tr>
	<tr>
		<th><?php echo Labels::getLabel('LBL_Address_Line_2',$siteLangId);?> </th>
		<td><?php echo CommonHelper::displayNotApplicable( $siteLangId, $userData['user_address2']); ?></td>
	</tr>
	<tr>
		<th><?php echo Labels::getLabel('LBL_City',$siteLangId);?> </th>
		<td><?php echo CommonHelper::displayNotApplicable( $siteLangId, $userData['user_city']); ?>	</td>
	</tr>
	<tr>
		<th><?php echo Labels::getLabel('LBL_Postcode',$siteLangId);?> </th>
		<td><?php echo CommonHelper::displayNotApplicable( $siteLangId, $userData['user_zip']); ?>	</td>
	</tr>
	<tr>
		<th><?php echo Labels::getLabel('LBL_State',$siteLangId);?> </th>
		<td><?php echo CommonHelper::displayNotApplicable( $siteLangId, $userData['state_name']); ?></td>
	</tr>
	<tr>
		<th><?php echo Labels::getLabel('LBL_Country',$siteLangId);?> </th>
		<td><?php echo CommonHelper::displayNotApplicable( $siteLangId, $userData['country_name']); ?></td>
		</tr>
</table>
