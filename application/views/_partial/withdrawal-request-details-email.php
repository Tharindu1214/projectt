<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>

<table style="border:1px solid #ddd; border-collapse:collapse;" cellspacing="0" cellpadding="0" border="0">
	<tbody>
		
		<tr>
			<td style="padding:10px;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;" width="153"><?php echo Labels::getLabel("LBL_Withdrawal_Payment_Method", $siteLangId); ?></td>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" width="620"><?php echo User::getAffiliatePaymentMethodArr($siteLangId)[$data['withdrawal_payment_method']]; ?></td>
		</tr>
		<?php if( $data['withdrawal_payment_method'] == User::AFFILIATE_PAYMENT_METHOD_BANK ){ ?>
		<tr>
			<td style="padding:10px;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;" width="153"><?php echo Labels::getLabel("LBL_Bank_name", $siteLangId); ?></td>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" width="620"><?php echo $data['withdrawal_bank']; ?></td>
		</tr> 
		<tr>
			<td style="padding:10px;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;" width="153"><?php echo Labels::getLabel("LBL_Beneficiary/Account_Holder_Name", $siteLangId); ?></td>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" width="620"><?php echo $data['withdrawal_account_holder_name']; ?></td>
		</tr>
		<tr>
			<td style="padding:10px;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;" width="153"><?php echo Labels::getLabel("LBL_Bank_Account_Number", $siteLangId); ?></td>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" width="620"><?php echo $data['withdrawal_account_number']; ?></td>
		</tr>
		<tr>
			<td style="padding:10px;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;" width="153"><?php echo Labels::getLabel("LBL_IFSC_Code/Swift_Code", $siteLangId); ?></td>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" width="620"><?php echo $data['withdrawal_ifc_swift_code']; ?></td>
		</tr>
		<tr>
			<td style="padding:10px;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;" width="153"><?php echo Labels::getLabel("LBL_Bank_Address", $siteLangId); ?></td>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" width="620"><?php echo $data['withdrawal_bank_address']; ?></td>
		</tr>
		<?php } ?>
		
		<?php if( $data['withdrawal_payment_method'] == User::AFFILIATE_PAYMENT_METHOD_CHEQUE ){ ?>
		<tr>
			<td style="padding:10px;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;" width="153"><?php echo Labels::getLabel("LBL_Cheque_Payee_Name", $siteLangId); ?></td>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" width="620"><?php echo $data['withdrawal_cheque_payee_name']; ?></td>
		</tr>
		<?php } ?>
		
		
		<?php if( $data['withdrawal_payment_method'] == User::AFFILIATE_PAYMENT_METHOD_PAYPAL ){ ?>
		<tr>
			<td style="padding:10px;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;" width="153"><?php echo Labels::getLabel("LBL_PayPal_Email_Id", $siteLangId); ?></td>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" width="620"><?php echo $data['withdrawal_paypal_email_id']; ?></td>
		</tr>
		<?php } ?>
		
		<tr>
			<td style="padding:10px;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;" width="153"><?php echo Labels::getLabel("LBL_Comments", $siteLangId); ?></td>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" width="620"><?php echo $data['withdrawal_comments']; ?></td>
		</tr>
		
	</tbody>
</table>