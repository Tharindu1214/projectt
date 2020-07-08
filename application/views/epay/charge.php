<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>


<?php

$p_id_invoice="A11".time();
$p_cust_id_cliente='15189';
$p_key='cc543de2d47c85935f766a7c87aeefdc44aad4ab';
$p_amount='24000';
$p_tax="0";
$p_base="0";
$p_currency_code='COP';


$p_split_type='01';
$p_split_merchant_receiver='15189';
$p_split_primary_receiver='15189';
$p_split_primary_receiver_fee='0';
$p_split_receivers=array();
$p_signature_receivers="";
$p_split_receivers[0]=array('id'=>'15234','fee'=>'1500');
$p_split_receivers[1]=array('id'=>'15231','fee'=>'1500');
$p_signature= md5($p_cust_id_cliente.'^'.$p_key.'^'.$p_id_invoice.'^'.$p_amount.'^'.$p_currency_code);
$p_signature_receivers ='';

foreach($p_split_receivers as $receiver){
  $p_signature_receivers.= $receiver['id']. '^' .$receiver['fee'];
}

$p_signature_split = md5(
	 $p_split_type.'^'
	.$p_split_merchant_receiver.'^'
	.$p_split_primary_receiver.'^'
	.$p_split_primary_receiver_fee.'^'
	.$p_signature_receivers
);

?>


<p>Pruebas Split 1</p>
<form action="https://secure.payco.co/splitpayments.php" target="_blank" method="post"> 
   <input name="p_cust_id_cliente" type="hidden" value="<?php echo $p_cust_id_cliente ?>"> 
   <input name="p_key" type="hidden" value="<?php echo $p_key ?>"> 
   <input name="p_id_invoice" type="hidden" value="<?php echo $p_id_invoice ?>"> 
   <input name="p_description" type="hidden" value="Plan Emprendedor/Anula"> 
   <input name="p_currency_code" type="hidden" value="COP"> 
   <input name="p_amount" id="p_amount" type="hidden" value="<?php echo $p_amount ?>"> 
   <input name="p_tax" id="p_tax" type="hidden" value="0"> 
   <input name="p_amount_base" id="p_amount_base" type="hidden" value="0"> 
   <input name="p_test_request" type="hidden" value="true"> 
   <input name="p_url_response" type="hidden" value="http://yokart-v8.4demo.biz/epay/response"> 
   <input name="p_url_confirmation" type="hidden" value="http://yokart-v8.4demo.biz/epay/confirm"> 
   <input name="p_signature" type="hidden" id="signature" value="<?php echo $p_signature ?>"> 
   <input name="p_confirm_method" type="hidden" value="POST"> 
   <input name="p_billing_document" type="hidden" id="p_billing_document" value="A11"> 
   <input name="p_billing_name" type="hidden" id="p_billing_name" value="Pooja"> 
   <input name="p_billing_lastname" type="hidden" id="p_billing_lastname" value="Rani"> 
   <input name="p_billing_address" type="hidden" id="p_billing_address" value="Test Address"> 
   <input name="p_billing_country" type="hidden" id="p_billing_country" value="CO"> 
   <input name="p_billing_email" type="hidden" id="p_billing_email" value="pooja@dummyid.com"> 
   <input name="p_billing_phone" type="hidden" id="p_billing_phone" value="65487569"> 
   <input name="p_split_type" type="hidden" id="p_split_type" value="<?php echo $p_split_type ?>"> 
   <input name="p_split_merchant_receiver" type="hidden" id="p_split_merchant_receiver" value="<?php echo $p_split_merchant_receiver ?>"> 
   <input name="p_split_primary_receiver" type="hidden" id="p_split_primary_receiver" value="<?php echo $p_split_primary_receiver ?>"> 
   <input name="p_split_primary_receiver_fee" type="hidden" id="" value="<?php echo $p_split_primary_receiver_fee ?>"> 
   <?php $i =0;
   foreach($p_split_receivers as $reciever){
	   ?>
	   <input name="p_split_receivers[<?php echo $i;?>][id]" type="hidden" value="<?php echo $reciever['id'] ?>">  
		<input name="p_split_receivers[<?php echo $i;?>][fee]" type="hidden" value="<?php echo $reciever['fee'] ?>">  
	   <?php
	   $i++;
   } ?>
   
   <input name="p_signature_split" type="hidden" value="<?php echo $p_signature_split;?>">  
  
   <input type="image" id="imagen" src="https://369969691f476073508a-60bf0867add971908d4f26a64519c2aa.ssl.cf5.rackcdn.com/btns/btn4.png"> 
</form>
