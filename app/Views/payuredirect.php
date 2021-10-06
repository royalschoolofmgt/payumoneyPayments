<link rel="stylesheet" href="<?= getenv('app.ASSETSPATH') ?>css/247payuloader.css">
<script src="<?= getenv('app.ASSETSPATH') ?>js/247payuloader.js"></script>
<?php

if(getenv('CI_ENVIRONMENT') == "production"){
	$PAYU_BASE_URL = getenv('payu.PAYU_PROD_URL');//PAY target url
}else{
	$PAYU_BASE_URL = getenv('payu.PAYU_TEST_URL');//PAY target url
}
$MERCHANT_KEY = $tokenDetails['merchant_key']; //PAYU key
$SALT = $tokenDetails['merchant_salt'];//PAYU salt


$string = base64_decode($orderDetails['params']);
$string = preg_replace("/[\r\n]+/", " ", $string);
$json = utf8_encode($string);
$cartData = json_decode($json,true);

$billingAddress = $cartData['billing_address'];

$posted = array();

$posted['key'] = $MERCHANT_KEY;
$posted['txnid'] = $orderDetails['order_id'];
$posted['productinfo'] = "BigCommerce Order";
$posted['amount'] = $orderDetails['total_amount'];
$posted['email'] = $billingAddress['email'];
$posted['firstname'] = $billingAddress['first_name'];
$posted['surl'] = getenv('app.baseURL')."payupay/success";
$posted['furl'] = getenv('app.baseURL')."payupay/failure";
$posted['phone'] = $billingAddress['phone'];

$formError = 0;

$hash = '';
// Hash Sequence
$hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|||||";

$hashVarsSeq = explode('|', $hashSequence);
$hash_string = '';	
foreach($hashVarsSeq as $hash_var) {
    $hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] : '';
    $hash_string .= '|';
}

$hash_string .= $SALT;

$hash = strtolower(hash('sha512', $hash_string));
$action = $PAYU_BASE_URL . '/_payment';
?>
<html>
  <head>
  <script>
    var hash = '<?php echo $hash ?>';
	var text = "Please wait...";
	var current_effect = "bounce";
	$("body").waitMe({
		effect: current_effect,
		text: text,
		bg: "rgba(255,255,255,0.7)",
		color: "#000",
		maxSize: "",
		waitTime: -1,
		source: "img/img.svg",
		textPos: "vertical",
		fontSize: "",
		onClose: function(el) {}
	});
    function submitPayuForm() {
      if(hash == '') {
        return;
      }
      var payuForm = document.forms.payuForm;
      payuForm.submit();
    }
  </script>
  </head>
  <body onload="submitPayuForm()">
    <form action="<?php echo $action; ?>" method="post" name="payuForm">
      <input type="hidden" name="key" value="<?php echo $MERCHANT_KEY ?>" />
      <input type="hidden" name="txnid" value="<?php echo $orderDetails['order_id'] ?>" />
      <input type="hidden" name="amount" value="<?php echo $orderDetails['total_amount']?>" />
      <input type="hidden" name="productinfo" value="<?php echo $posted['productinfo'] ?>" />
      <input type="hidden" name="firstname" value="<?php echo $posted['firstname'] ?>" />
      <input type="hidden" name="email" value="<?php echo $posted['email'] ?>" />
      <input type="hidden" name="phone" value="<?php echo $posted['phone'] ?>" />
      <input type="hidden" name="surl" value="<?php echo $posted['surl'] ?>" />
      <input type="hidden" name="furl" value="<?php echo $posted['furl'] ?>" />
       <input type="hidden" name="hash" value="<?php echo $hash ?>"/>      
      <input style="display:none;" type="submit" value="Submit" /></td>
    </form>
  </body>
</html>
