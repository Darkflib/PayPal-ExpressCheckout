<?php

/**********************************
	
	@project	PayPal Express Checkout API
	@version	1.2

	@desc		Configuration file -- Place all PayPal Credentials Here
*/


/* LIVE CREDENTIALS 
$PayPalApiUsername = "<<REPLACE WITH YOUR LIVE API USERNAME>>"; //live merchant
$PayPalApiPassword = "<<REPLACE WITH YOUR LIVE API PASSWORD>>" //live password
$PayPalApiSignature  "<<REPLACE WITH YOUR LIVE API SIGNATURE>>" //live signature
*/

/* SANDBOX CREDENTIALS 
$PayPalApiUsername = "<<REPLACE WITH YOUR SANDBOX API USERNAME>>"; //sandbox merchant
$PayPalApiPassword = "<<REPLACE WITH YOUR SANDBOX API PASSWORD>>" //sandbox password
$PayPalApiSignature  "<<REPLACE WITH YOUR SANDBOX API SIGNATURE>>" //sandbox signature
*/

$PayPalMode 			= 'live'; // sandbox or live
$PayPalApiUsername 		= 'example_api1.example.com'; 
$PayPalApiPassword 		= 'VJ9J24YCP82VFBWG'; 
$PayPalApiSignature 		= 'AaquWYnu8m7ONB3A.c3LbD9XfKplAxp21MUlWT264ZLFR49AF4j7oAKx'; 
$PayPalCurrencyCode 		= 'USD'; //Paypal Currency Code
$PayPalReturnURL 		= 'http://example.com/process.php'; //Point to process.php page
$PayPalCancelURL 		= 'http://example.com/cancel.php'; //Cancel URL
?>
