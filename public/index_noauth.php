<?php 
$valid_urls = array(
	'PaypalStandardPay/callback',
	'paypal-standard-pay/callback',
	'pay-fort-pay/do-payment',
	'khipu-pay/send',
	'twocheckout-pay/callback',
	'Image/emailLogo',
);

$str = $_GET['url'];


$url = substr($str, 0, strpos($str, '/', strpos($str, '/')+1));

if (!in_array($url, $valid_urls) && !in_array($str, $valid_urls)) die('Unauthorized Access.');

require_once 'index.php';