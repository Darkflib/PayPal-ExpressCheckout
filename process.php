<?php

/**********************************
	
	@project	PayPal Express Checkout API
	@version	1.2
	
***********************************/


session_start();
include_once("paypal.config.php");
include_once("paypal.class.php");

	// get template variables
	$ItemID = $_GET['id'];
	$ItemName = $_GET["item"]; //Item Name
	$ItemPrice = $_GET["price"]; //Item Price
	$ItemDesc = $_GET['desc'];
	$ItemQty = '1'; // Item Quantity
	$ItemTotalPrice = ($ItemPrice*$ItemQty);
	
	$_SESSION['templateInfo'] = array();
	$_SESSION['templateInfo'][] = $ItemID;
	$_SESSION['templateInfo'][] = $ItemName;
	$_SESSION['templateInfo'][] = $ItemPrice;
	$_SESSION['templateInfo'][] = $ItemDesc;

	//Data to be sent to paypal
	$padata = 	'&CURRENCYCODE='.urlencode($PayPalCurrencyCode).
				'&PAYMENTACTION=Sale'.
				'&ALLOWNOTE=1'.
				'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode($PayPalCurrencyCode).
				'&PAYMENTREQUEST_0_AMT='.urlencode($ItemTotalPrice).
				'&PAYMENTREQUEST_0_ITEMAMT='.urlencode($ItemTotalPrice). 
				'&L_PAYMENTREQUEST_0_QTY0='. urlencode($ItemQty).
				'&L_PAYMENTREQUEST_0_AMT0='.urlencode($ItemPrice).
				'&L_PAYMENTREQUEST_0_NAME0='.urlencode($ItemName).
				'&L_PAYMENTREQUEST_0_NUMBER0='.urlencode($id).
				'&AMT='.urlencode($ItemTotalPrice).				
				'&RETURNURL='.urlencode($PayPalReturnURL ).
				'&REQCONFIRMSHIPPING=0'.
				'&NOSHIPPING=1'.
				'&CANCELURL='.urlencode($PayPalCancelURL);	
		
		//execute the "SetExpressCheckOut" method to obtain paypal token
		$paypal= new MyPayPal();
		$httpParsedResponseAr = $paypal->PPHttpPost('SetExpressCheckout', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);
		
		//Respond according to message we receive from Paypal
		if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
		{
					
				// If successful set some session variable we need later when user is redirected back to page from paypal. 
				$_SESSION['itemprice'] =  $ItemPrice;
				$_SESSION['totalamount'] = $ItemTotalPrice;
				$_SESSION['itemName'] =  $ItemName;
				$_SESSION['itemNo'] =  $ItemID;
				$_SESSION['itemQTY'] =  $ItemQty;
				
				if($PayPalMode=='sandbox')
				{
					$paypalmode 	=	'.sandbox';
				}
				else
				{
					$paypalmode 	=	'';
				}
				//Redirect user to PayPal store with Token received.
			 	$paypalurl ='https://www'.$paypalmode.'.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$httpParsedResponseAr["TOKEN"].'';
				header('Location: '.$paypalurl);
			 
		}else{
			//Show error message
			echo '<div style="color:red"><b>Error : </b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
			echo '<pre>';
			print_r($httpParsedResponseAr);
			echo '</pre>';
		}

//Paypal redirects back to this page using ReturnURL, receive TOKEN and Payer ID
if(isset($_GET["token"]) && isset($_GET["PayerID"]))
{
	
	$token = $_GET["token"];
	$playerid = $_GET["PayerID"];
	
	//get session variables
	$ItemPrice 		= $_SESSION['itemprice'];
	$ItemTotalPrice = $_SESSION['totalamount'];
	$ItemName 		= $_SESSION['itemName'];
	$ItemNumber 	= $_SESSION['itemNo'];
	$ItemQTY 		= $_SESSION['itemQTY'];
	
	$padata = 	'&TOKEN='.urlencode($token).
				'&PAYERID='.urlencode($playerid).
				'&PAYMENTACTION='.urlencode("SALE").
				'&AMT='.urlencode($ItemTotalPrice).
				'&CURRENCYCODE='.urlencode($PayPalCurrencyCode);
	
	//execute the "DoExpressCheckoutPayment"
	$paypal= new MyPayPal();
	$httpParsedResponseAr = $paypal->PPHttpPost('DoExpressCheckoutPayment', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);
	
	//Check if everything went ok
	if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) 
	{	
		$transactionID = urlencode($httpParsedResponseAr["TRANSACTIONID"]);
		$nvpStr = "&TRANSACTIONID=".$transactionID;
		$paypal= new MyPayPal();
		$httpParsedResponseAr = $paypal->PPHttpPost('GetTransactionDetails', $nvpStr, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);

		if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
					
			/*	uncomment this to store user information in database asynchronously with the api calls
		
			$buyerName = $httpParsedResponseAr["FIRSTNAME"].' '.$httpParsedResponseAr["LASTNAME"];
			$buyerEmail = $httpParsedResponseAr["EMAIL"];
					
			mysqli_connect($db_host, $db_user, $db_pass) OR DIE (mysqli_error());
			mysqli_select_db ($link, $db_name) OR DIE ("Unable to select db".mysqli_error($db_name));
									
			$sql = "INSERT INTO table_name(name,email,transaction_id,template_purchased)
					VALUES 
					('$buyerName','$buyerEmail','$transactionID','$ItemName')";
					
			$strip = $link->prepare($sql);
			$insert_id = mysqli_insert_id($link);

			mysqli_query($link, $sql) or die("Error in Query: " . mysqli_error($link));

			header("Location: thankyou.php");
			*/
			
		} else  {
			echo '<div style="color:red"><b>GetTransactionDetails failed:</b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
			echo '<pre>';
			print_r($httpParsedResponseAr);
			echo '</pre>';

			}
	
	}else{
			echo '<div style="color:red"><b>Error : </b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
			echo '<pre>';
			print_r($httpParsedResponseAr);
			echo '</pre>';
	}
	
					}
				elseif('Pending' == $httpParsedResponseAr["PAYMENTSTATUS"])
				{
					echo '<div style="color:red">Transaction Complete, but payment is still pending! You need to manually authorize this payment in your <a target="_new" href="http://www.paypal.com">Paypal Account</a></div>';
				}

?>
<!DOCTYPE html>
<html>
<head>
<title>Transaction Processed</title>
</head>
<body>
<h1>THANK YOU!</h1>
<br><br>
<h1>Your order has been processed. Please follow the link below to complete your payment</h1>
<br><br>
<br>
<?php
$id				= $_GET['id'];
$token 			= $httpParsedResponseAr['TOKEN'];
$checkoutstatus	= $httpParsedResponseAr['CHECKOUTSTATUS'];
$timestamp		= $httpParsedResponseAr['TIMESTAMP'];
$correlation_id	= $httpParsedResponseAr['CORRELATIONID'];
$acknowledgement= $httpParsedResponseAr['ACK'];
$version		= $httpParsedResponseAr['VERSION'];
$build			= $httpParsedResponseAr['BUILD'];
$e_mail_id		= $httpParsedResponseAr['EMAIL'];
$payer_id		= $httpParsedResponseAr['PAYERID'];
$payer_status	= $httpParsedResponseAr['PAYERSTATUS'];
$first_name		= $httpParsedResponseAr['FIRSTNAME'];
$last_name		= $httpParsedResponseAr['LASTNAME'];
$cust_name		= $first_name." ".$last_name;
$country_code	= $httpParsedResponseAr['COUNTRYCODE'];
$currency_code	= $httpParsedResponseAr['CURRENCYCODE'];
$amount			= $httpParsedResponseAr['AMT'];
$item_amt		= $httpParsedResponseAr['ITEMAMT'];
$shipping_amt	= $httpParsedResponseAr['SHIPPINGAMT'];
$handling_amt	= $httpParsedResponseAr['HANDLINGAMT'];
$tax_amt		= $httpParsedResponseAr['TAXAMT'];

foreach($_SESSION['templateInfo'] as $info)
{
    echo $info;
}

?>

<a href="purchase-thankyou.php?name=<?php echo $cust_name;?>&email=<?php echo $e_mail_id;?>&id=<?php echo $id;?>&itemname=<?php echo $ItemName;?>">
	   Click to Continue</a>
</body>
</html>
