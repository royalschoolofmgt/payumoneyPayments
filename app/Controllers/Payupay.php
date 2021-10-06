<?php
/**
 * This file is part of the 247Commerce BigCommerce PayU App.
 *
 * ©247 Commerce Limited <info@247commerce.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Controllers;

/**
 * Class Payupay
 *
 * Represents a PayU Payment Authentication and redirection
 */
class Payupay extends BaseController
{
	/**
	 * Index - default page
	 *
	 */
	public function index()
	{
		echo "Under Construnction";exit;
	}
	
	/**
	 * Payment data and redirection
	 *
	 */
	public function authentication()
	{
		
		$res = array();
		$res['status'] = false;
		$res['url'] = '';
		
		helper('settingsviews');
		if(!empty($this->request->getPost('authKey')) && !empty($this->request->getPost('cartId'))){
			
			log_message('info', 'BigCommerce fields-authKey:'.$this->request->getPost('authKey'));
			log_message('info', 'BigCommerce fields-cartId:'.$this->request->getPost('cartId'));
		
			$tokenData = json_decode(base64_decode($this->request->getPost('authKey')),true);
			$email_id = $tokenData['email_id'];
			$validation_id = $tokenData['key'];
			
			if (filter_var($email_id, FILTER_VALIDATE_EMAIL)) {
				$db = \Config\Database::connect();
				$builder = $db->table('payu_token_validation');        
				$builder->select('*');       
				$builder->where('email_id', $email_id);
				$builder->where('validation_id', $validation_id);
				$query = $builder->get();
				$result = $query->getResultArray();
				if (count($result) > 0) {
					$clientDetails = $result[0];
					$payment_option = $clientDetails['payment_option'];
					$cartAPIRes = $this->getCartData($email_id,$this->request->getPost('cartId'),$validation_id);
					if(!is_array($cartAPIRes) || (is_array($cartAPIRes) && count($cartAPIRes) == 0)) {
						exit;
					}
					$cartData = $cartAPIRes;	
					$invoiceId = "PayU-".$validation_id.'-'.uniqid().'-'.time();
					$currency = $cartData['cart']['currency']['code'];
					$cartbillingAddress = $cartData['billing_address'];
					$checkShipping = false;
					if(count($cartData['cart']['line_items']['physical_items']) > 0 || count($cartData['cart']['line_items']['custom_items']) > 0){
						$checkShipping = true;
					}else{
						if(count($cartData['cart']['line_items']['digital_items']) > 0){
							$checkShipping = false;
						}
					}
					if($checkShipping){
						$cart_shipping_address = $cartData['consignments'][0]['shipping_address'];
					}else{
						$cart_shipping_address = $cartData['billing_address'];
					}
					$totalAmount = $cartData['grand_total'];
					
					$transaction_type = "AUTH";
					if($payment_option == "CFO"){
						$transaction_type = "SALE";
						$totalAmount = $cartData['grand_total'];
					}
					
					$tokenData = array("email_id"=>$email_id,"key"=>$validation_id,"invoice_id"=>$invoiceId);
					
					$db = \Config\Database::connect();
					$data = [
						'email_id' => $email_id,
						'type' => $transaction_type,
						'order_id'    => $invoiceId,
						'cart_id'    => $cartData['id'],
						'total_amount' => $cartData['grand_total'],
						'amount_paid' => "0.00",
						'currency' => $currency,
						'status' => "PENDING",
						'params' => base64_encode(json_encode($cartData)),
						'token_validation_id' => $validation_id,
					];
					$builderinsert = $db->table('order_payment_details'); 
					$builderinsert->insert($data);
					
					$res['status'] = true;
					$url = getenv('app.baseURL')."payupay/payment/".base64_encode(json_encode($invoiceId));
					$res['url'] = $url;
				}
			}
		}
		echo json_encode($res,true);exit;
	}
	
	/**
	 * Redirecting to PayU Payment Gateway
	 *
	 */
	public function payment($invoiceId)
	{
		$data = array();
		if(!empty($invoiceId)){
			$invoiceId = json_decode(base64_decode($invoiceId),true);
			log_message('info', 'PayU Before Redirection:'.$invoiceId);
			$db = \Config\Database::connect();
			$builder = $db->table('order_payment_details');        
			$builder->select('*');       
			$builder->where('order_id', $invoiceId);
			$query = $builder->get();
			$result_order_payment = $query->getResultArray();
			if (count($result_order_payment) > 0) {
				$result_order_payment = $result_order_payment[0];
				if($result_order_payment['type'] == "AUTH"){
					$s2sresponse = json_decode(stripslashes($result_order_payment['s2sresponse']),true);
					$s2shtmlresp = stripslashes($result_order_payment['s2sresponse']);
					if(isset($s2sresponse['result']['acsTemplate'])){
						echo base64_decode($s2sresponse['result']['acsTemplate']);exit;
					}else if(!empty($s2shtmlresp)){
						echo $s2shtmlresp;exit;
					}else if(!empty($s2shtmlresp)){
						return redirect()->to('/');
					}
				}else{
					$data['orderDetails'] = $result_order_payment;
					$builder = $db->table('payu_token_validation');        
					$builder->select('*');       
					$builder->where('email_id', $result_order_payment['email_id']);
					$builder->where('validation_id', $result_order_payment['token_validation_id']);
					$query = $builder->get();
					$result = $query->getResultArray();
					if (count($result) > 0) {
						$result = $result[0];
						$data['tokenDetails'] = $result;
						return view('payuredirect',$data);
					}else{
						return redirect()->to('/');
					}
				}
			}else{
				return redirect()->to('/');
			}
		}else{
			return redirect()->to('/');
		}
	}
	
	/**
	 * Payment data and redirection S2Scheckout
	 *
	 */
	public function s2scheckout()
	{
		
		$res = array();
		$res['status'] = false;
		$res['url'] = '';
		$res['msg'] = 'Technical Error';
		helper('settingsviews');
		$db = \Config\Database::connect();
		if(!empty($this->request->getPost('authKey')) && !empty($this->request->getPost('cartId'))){
			
			log_message('info', 'BigCommerce fields-authKey:'.$this->request->getPost('authKey'));
			log_message('info', 'BigCommerce fields-cartId:'.$this->request->getPost('cartId'));
			log_message('info', 'BigCommerce fields-postData-CardDetails:'.$this->request->getPost('postData'));
		
			$tokenData = json_decode(base64_decode($this->request->getPost('authKey')),true);
			$email_id = $tokenData['email_id'];
			$validation_id = $tokenData['key'];
			
			$postData = json_decode(base64_decode($_REQUEST['postData']),true);
			
			if (filter_var($email_id, FILTER_VALIDATE_EMAIL)) {
				$db = \Config\Database::connect();
				$builder = $db->table('payu_token_validation');        
				$builder->select('*');       
				$builder->where('email_id', $email_id);
				$builder->where('validation_id', $validation_id);
				$query = $builder->get();
				$result = $query->getResultArray();
				
				if (count($result) > 0) {
					$clientDetails = $result[0];
					$payment_option = $clientDetails['payment_option'];
					$cartAPIRes = $this->getCartData($email_id,$this->request->getPost('cartId'),$validation_id);
					if(!is_array($cartAPIRes) || (is_array($cartAPIRes) && count($cartAPIRes) == 0)) {
						exit;
					}
					$cartData = $cartAPIRes;
					$invoiceId = "PayU-".$validation_id.'-'.uniqid().'-'.time();
					$currency = $cartData['cart']['currency']['code'];
					$cartbillingAddress = $cartData['billing_address'];
					$checkShipping = false;
					if(count($cartData['cart']['line_items']['physical_items']) > 0 || count($cartData['cart']['line_items']['custom_items']) > 0){
						$checkShipping = true;
					}else{
						if(count($cartData['cart']['line_items']['digital_items']) > 0){
							$checkShipping = false;
						}
					}
					if($checkShipping){
						$cart_shipping_address = $cartData['consignments'][0]['shipping_address'];
					}else{
						$cart_shipping_address = $cartData['billing_address'];
					}
					$totalAmount = $cartData['grand_total'];
					
					$transaction_type = "AUTH";
					if($payment_option == "CFO"){
						$transaction_type = "SALE";
						$totalAmount = $cartData['grand_total'];
					}
					
					$tokenData = array("email_id"=>$email_id,"key"=>$validation_id,"invoice_id"=>$invoiceId);
					
					$posted['key'] = $clientDetails['merchant_key'];
					$posted['txnid'] = $invoiceId;
					$posted['amount'] = $totalAmount;
					$posted['firstname'] = $cartbillingAddress['first_name'];
					$posted['email'] = $cartbillingAddress['email'];
					$posted['productinfo'] = "BigCommerce Order";
					$posted['pg'] = "cc";
					$posted['bankcode'] = "cc";
					$posted['surl'] = getenv('app.baseURL')."payupay/success";
					$posted['furl'] = getenv('app.baseURL')."payupay/failure";
					$posted['phone'] = $cartbillingAddress['phone'];
					$posted['ccnum'] = $postData['ccnum'];
					$posted['ccexpmon'] = $postData['ccexpmon'];
					$posted['ccexpyr'] = $postData['ccexpyr'];
					$posted['ccvv'] = $postData['ccvv'];
					$posted['ccname'] = $postData['ccname'];
					$posted['txn_s2s_flow'] = 4;

					$hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|||||";
					
					$hashVarsSeq = explode('|', $hashSequence);
					$hash_string = '';	
					foreach($hashVarsSeq as $hash_var) {
						$hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] : '';
						$hash_string .= '|';
					}
					$hash_string .= $clientDetails['merchant_salt'];//appending salt
					
					$posted['hash'] = strtolower(hash('sha512', $hash_string));
					
					$request = '';

					foreach($posted as $k=>$v){
						$request .= $k.'='.$v.'&';
					}
					if(getenv('CI_ENVIRONMENT') == "production"){
						$PAYU_BASE_URL = getenv('payu.PAYU_PROD_URL');//PAY target url
					}else{
						$PAYU_BASE_URL = getenv('payu.PAYU_TEST_URL');//PAY target url
					}
					$url = $PAYU_BASE_URL.'/_payment';
					//echo $request;exit;
					try{
						/*$client = \Config\Services::curlrequest();
						$response = $client->setBody($request)->request('post', $url, [
								'headers' => [
										//'Accept' => 'application/json',
										'Content-Type' => 'application/x-www-form-urlencoded'
								]
						]);
						if (strpos($response->getHeader('content-type'), 'text/html') != false){
							$body = $response->getBody();*/
							$header = array(
								"Accept: application/json",
								"Content-Type: x-www-form-urlencoded"
							);
							$ch = curl_init($url);
							curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
							curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
							curl_setopt($ch, CURLOPT_POST, 1);
							curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
							curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
							$body = curl_exec($ch);
							curl_close($ch);
							
							$data = [
								'email_id' => $email_id,
								'type' => 'PayU',
								'action'    => 'S2SCHECKOUT',
								'api_url'    => addslashes($url),
								'api_request' => addslashes($request),
								'api_response' => addslashes($body),
								'token_validation_id' => $validation_id,
							];
							$builderinsert = $db->table('api_log'); 
							$builderinsert->insert($data);
					
							$check_errors = json_decode($body);
							if(isset($check_errors->errors)){
							}else{
								if(json_last_error() === 0){
									$response = json_decode($body,true);
									if(isset($response['result']['acsTemplate']) && !empty($response['result']['acsTemplate'])) {
										$data = [
											'email_id' => $email_id,
											'type' => $transaction_type,
											'order_id'    => $invoiceId,
											'cart_id'    => $cartData['id'],
											'total_amount' => $cartData['grand_total'],
											'amount_paid' => "0.00",
											'currency' => $currency,
											'status' => "PENDING",
											's2sresponse' => addslashes($body),
											'params' => base64_encode(json_encode($cartData)),
											'token_validation_id' => $validation_id,
										];
										$builderinsert = $db->table('order_payment_details'); 
										$builderinsert->insert($data);
										
										$res['status'] = true;
										$url = getenv('app.baseURL')."payupay/payment/".base64_encode(json_encode($invoiceId));
										$res['url'] = $url;
									}else{
										if(isset($response['message'])){
											$res['msg'] = $response['message'];
										}else if(isset($response['metaData']['message'])){
											$res['msg'] = $response['metaData']['message'];
										}
									}
								}else{
									if(!empty($body)){
										$data = [
											'email_id' => $email_id,
											'type' => $transaction_type,
											'order_id'    => $invoiceId,
											'cart_id'    => $cartData['id'],
											'total_amount' => $cartData['grand_total'],
											'amount_paid' => "0.00",
											'currency' => $currency,
											'status' => "PENDING",
											's2sresponse' => addslashes($body),
											'params' => base64_encode(json_encode($cartData)),
											'token_validation_id' => $validation_id,
										];
										$builderinsert = $db->table('order_payment_details'); 
										$builderinsert->insert($data);
										
										$res['status'] = true;
										$url = getenv('app.baseURL')."payupay/payment/".base64_encode(json_encode($invoiceId));
										$res['url'] = $url;
									}
								}
							}
						//}
					}catch(\Exception $e){
						$data = [
							'email_id' => $email_id,
							'type' => 'PayU',
							'action'    => 'S2SCHECKOUT',
							'api_url'    => addslashes($url),
							'api_request' => addslashes($request),
							'api_response' => addslashes($e->getMessage()),
							'token_validation_id' => $validation_id,
						];
						$builderinsert = $db->table('api_log'); 
						$builderinsert->insert($data);
						$res['msg'] = $e->getMessage();
					}
				}
			}
		}
		echo json_encode($res,true);exit;
	}
	
	/**
	 * Success Page after Payment Successfull
	 *
	 */
	public function success()
	{			
		helper('settingsviews');
		helper('bigcommerceorder');
		$db = \Config\Database::connect();
		log_message('info', 'PostLink Update Order PayU Payment');
		if(isset($_REQUEST['status']) && isset($_REQUEST['txnid'])){
			if(!empty($_REQUEST['status']) && !empty($_REQUEST['txnid'])){
				$invoice_id = $_REQUEST['txnid'];
				if(!empty($invoice_id)) {
					
					$builder = $db->table('order_payment_details');        
					$builder->select('*');       
					$builder->where('order_id', $invoice_id);
					$query = $builder->get();
					$result_order_payment = $query->getResultArray();

					if (isset($result_order_payment[0])) {
						$result_order_payment = $result_order_payment[0];
						
						//duplicate checking
						$email_id = $result_order_payment['email_id'];
						$validation_id = $result_order_payment['token_validation_id'];
						$builder = $db->table('order_details');        
						$builder->select('*');       
						$builder->where('email_id', $email_id);
						$builder->where('invoice_id', $invoice_id);
						$builder->where('token_validation_id', $validation_id);
						$query = $builder->get();
						$invoice_result = $query->getResultArray();
						if(isset($invoice_result[0])) {
							$this->redirectBigcommerce($result_order_payment['email_id'],$invoice_id,$result_order_payment['token_validation_id']);exit;
						}
						//duplicate code end
						
						$builder = $db->table('payu_token_validation');        
						$builder->select('*');       
						$builder->where('email_id', $result_order_payment['email_id']);
						$builder->where('validation_id', $result_order_payment['token_validation_id']);
						$query = $builder->get();
						$result = $query->getResultArray();
						if (count($result) > 0) {
							$clientDetails = $result[0];
							$payment_option = $clientDetails['payment_option'];
							$status = $this->paymentStatus($_REQUEST);
							if(!$status){
								$this->redirectBigcommerce($result_order_payment['email_id'],$invoice_id,$result_order_payment['token_validation_id']);exit;
							}
						}
						
						$string = base64_decode($result_order_payment['params']);
						$string = preg_replace("/[\r\n]+/", " ", $string);
						$json = utf8_encode($string);
						$cartData = json_decode($json,true);
						$items_total = 0;
						//print_r(json_encode($cartData));exit;
						$order_products = array();
						foreach($cartData['cart']['line_items'] as $liv){
							$cart_products = $liv;
							foreach($cart_products as $k=>$v){
								if($v['variant_id'] > 0){
									$details = array();
									$productOptions = \BigCommerceOrder::productOptions($result_order_payment['email_id'],$v['product_id'],$v['variant_id'],$result_order_payment['token_validation_id']);
									
									log_message('info', "Product variant options: ".json_encode($productOptions));
									
									$temp_option_values = $productOptions['option_values'];
									$option_values = array();
									if(!empty($temp_option_values) && isset($temp_option_values[0])){
										foreach($temp_option_values as $tk=>$tv){
											$option_values[] = array(
															"id" => $tv['option_id'],
															"value" => strval($tv['id'])
														);
										}
									}else{
										if(isset($v['options']) && !empty($v['options'])){
											foreach($v['options'] as $tk=>$tv){
												if(isset($tv['name_id']) && isset($tv['value_id'])){
													$option_values[] = array(
																"id" => $tv['name_id'],
																"value" => strval($tv['value_id'])
															);
												}
											}
										}
									}
									$items_total += $v['quantity'];
									$details = array(
													"product_id" => $v['product_id'],
													"quantity" => $v['quantity'],
													"product_options" => $option_values,
													"price_inc_tax" => $v['sale_price'],
													"price_ex_tax" => $v['sale_price'],
													"upc" => @$productOptions['upc'],
													"variant_id" => $v['variant_id']
												);
									$order_products[] = $details;
								}
							}
						}
						
						$checkShipping = false;
						if(count($cartData['cart']['line_items']['physical_items']) > 0 || count($cartData['cart']['line_items']['custom_items']) > 0){
							$checkShipping = true;
						}else{
							if(count($cartData['cart']['line_items']['digital_items']) > 0){
								$checkShipping = false;
							}
						}
						$cart_billing_address = $cartData['billing_address'];
						$billing_address = array(
												"first_name" => $cart_billing_address['first_name'],
												"last_name" => $cart_billing_address['last_name'],
												"phone" => $cart_billing_address['phone'],
												"email" => $cart_billing_address['email'],
												"street_1" => $cart_billing_address['address1'],
												"street_2" => $cart_billing_address['address2'],
												"city" => $cart_billing_address['city'],
												"state" => $cart_billing_address['state_or_province'],
												"zip" => $cart_billing_address['postal_code'],
												"country" => $cart_billing_address['country'],
												"company" => $cart_billing_address['company']
											);
						if($checkShipping){
							$cart_shipping_address = $cartData['consignments'][0]['shipping_address'];
							$cart_shipping_options = $cartData['consignments'][0]['selected_shipping_option'];
							$shipping_address = array(
													"first_name" => $cart_shipping_address['first_name'],
													"last_name" => $cart_shipping_address['last_name'],
													"company" => $cart_shipping_address['company'],
													"street_1" => $cart_shipping_address['address1'],
													"street_2" => $cart_shipping_address['address2'],
													"city" => $cart_shipping_address['city'],
													"state" => $cart_shipping_address['state_or_province'],
													"zip" => $cart_shipping_address['postal_code'],
													"country" => $cart_shipping_address['country'],
													"country_iso2" => $cart_shipping_address['country_code'],
													"phone" => $cart_shipping_address['phone'],
													"email" => $cart_billing_address['email'],
													"shipping_method" => $cart_shipping_options['description']
												);
						}
						$createOrder = array();
						$createOrder['customer_id'] = $cartData['cart']['customer_id'];
						$createOrder['products'] = $order_products;
						if($checkShipping){
							$createOrder['shipping_addresses'][] = $shipping_address;
						}
						$createOrder['billing_address'] = $billing_address;
						if(isset($cartData['coupons'][0]['discounted_amount'])){
							$createOrder['discount_amount'] = $cartData['coupons'][0]['discounted_amount'];
						}
						$createOrder['customer_message'] = $cartData['customer_message'];
						$createOrder['customer_locale'] = "en";
						$total_ex_tax = $cartData['subtotal_ex_tax']+$cartData['shipping_cost_total_ex_tax'];
						$createOrder['total_ex_tax'] = $total_ex_tax;
						$createOrder['total_inc_tax'] = $cartData['grand_total'];
						$createOrder['geoip_country'] = $cart_billing_address['country'];
						$createOrder['geoip_country_iso2'] = $cart_billing_address['country_code'];
						$createOrder['status_id'] = 0;
						$createOrder['ip_address'] = \BigCommerceOrder::get_client_ip();
						if($checkShipping){
							$createOrder['order_is_digital'] = true;
						}
						$createOrder['shipping_cost_ex_tax'] = $cartData['shipping_cost_total_ex_tax'];
						$createOrder['shipping_cost_inc_tax'] = $cartData['shipping_cost_total_inc_tax'];
						$createOrder['subtotal_ex_tax'] = $cartData['subtotal_ex_tax'];
						$createOrder['subtotal_inc_tax'] = $cartData['subtotal_inc_tax'];
						$createOrder['tax_provider_id'] = "BasicTaxProvider";
						if(isset($cartData['taxes'][0])){
							$createOrder['tax_provider_id'] = $cartData['taxes'][0]['name'];
						}
						$createOrder['payment_method'] = "PAYUMONEY";
						$createOrder['external_source'] = "247 PAYU";
						$createOrder['default_currency_code'] = $cartData['cart']['currency']['code'];
						
						log_message('info', "Before create order API call");
						$bigComemrceOrderId = \BigCommerceOrder::createOrder($result_order_payment['email_id'],$createOrder,$invoice_id,$result_order_payment['token_validation_id']);
						
						
						log_message('info', "Create order API response: ".$bigComemrceOrderId);
						if($bigComemrceOrderId != "") {
							log_message('info', "Before update order API call");
							//update order status for trigger status update mail from bigcommerce
							$statusResponse = \BigCommerceOrder::updateOrderStatus($bigComemrceOrderId,$result_order_payment['email_id'],$result_order_payment['token_validation_id']);
							log_message('info', "Update order status API response: ".$statusResponse);
						}
						log_message('info', "Before delete cart API call");
						$delCartResponse = \BigCommerceOrder::deleteCart($result_order_payment['email_id'],$result_order_payment['cart_id'],$result_order_payment['token_validation_id']);
						log_message('info', "delete cart API response: ".$delCartResponse);
						$this->redirectBigcommerce($result_order_payment['email_id'],$invoice_id,$result_order_payment['token_validation_id']);
					}
				}
			}else{
				$invoice_id = $_REQUEST['orderRef'];
				$data = [
					'status' => "FAILED",
					'api_response' => addslashes(json_encode($_REQUEST))
				];
				$builderupdate = $db->table('order_payment_details'); 
				$builderupdate->where('order_id', $invoice_id); 
				$builderupdate->update($data);
				
				$db = \Config\Database::connect();
				$builder = $db->table('order_payment_details');        
				$builder->select('*');
				$builder->where('order_id', $invoice_id);
				$query = $builder->get();
				$result = $query->getResultArray();
				if (count($result) > 0) {
					$orderDetails = $result[0];
					$this->redirectBigcommerce($orderDetails['email_id'],$invoice_id,$orderDetails['token_validation_id']);
				}
			}
		}
	}
	
	
	public function paymentStatus($request){
		
		$status = false;
		
		$paymentURL = getenv('payu.PAYU_TEST_URL');
		if(getenv('CI_ENVIRONMENT') == "production"){
			$paymentURL = getenv('payu.PAYU_PROD_URL');
		}
		
		$db = \Config\Database::connect();
		
		$builder = $db->table('order_payment_details');        
		$builder->select('*');       
		$builder->where('order_id', $request['txnid']);
		$query = $builder->get();
		$orderDetails = $query->getResultArray();
		if(count($orderDetails) > 0){
			$orderDetails = $orderDetails[0];
			$builder = $db->table('payu_token_validation');        
			$builder->select('*');       
			$builder->where('email_id', $orderDetails['email_id']);
			$builder->where('validation_id', $orderDetails['token_validation_id']);
			$query = $builder->get();
			$result = $query->getResultArray();
			if (count($result) > 0) {
				$clientDetails = $result[0];
				$payment_option = $clientDetails['payment_option'];
				
				$posted = array();
				$posted['key'] = $clientDetails['merchant_key'];
				$posted['command'] = "verify_payment";
				$posted['var1'] = $request['txnid'];
				
				$hash_string = $clientDetails['merchant_key'].'|verify_payment|'.$request['txnid'].'|'.$clientDetails['merchant_salt'];//appending salt
				
				$posted['hash'] = strtolower(hash('sha512', $hash_string));
				
				$req = '';
				foreach($posted as $k=>$v){
					$req .= $k.'='.$v.'&';
				}
				
				try{
					
					$client = \Config\Services::curlrequest();
					$response = $client->setBody($req)->request('post', $paymentURL.'/merchant/postservice?form=2', [
							'headers' => [
									'Accept' => 'application/json',
									'Content-Type' => 'application/x-www-form-urlencoded'
							]
					]);
					if (strpos($response->getHeader('content-type'), 'text/html') != false){
						$body = $response->getBody();
						$resp = json_decode($body,true);
						if(isset($resp['status']) && ($resp['status'] == "1")){
							$status = true;
							$amount_paid = 0;
							if(isset($resp['transaction_details'][$request['txnid']]['amt'])){
								$amount_paid = $resp['transaction_details'][$request['txnid']]['amt'];
							}
							$data = [
								'status' => "CONFIRMED",
								'amount_paid' => $amount_paid,
								'api_response' => addslashes($body)
							];
						}else{
							$data = [
								'api_response' => addslashes($body)
							];
						}
						$builderupdate = $db->table('order_payment_details');
						$builderupdate->where('order_id', $request['txnid']); 
						$builderupdate->update($data);
					}
				}catch(\Exception $e){
					print_r($e->getMessage());exit;
				}
			}
		}
		return $status;
	}
	
	/**
	 *Payment data failure and redirection
	 *
	 */
	public function failure()
	{
		helper('settingsviews');
		helper('bigcommerceorder');
		$db = \Config\Database::connect();
		if(isset($_REQUEST['mihpayid']) && isset($_REQUEST['txnid'])){
		
			$builder = $db->table('order_payment_details');        
			$builder->select('*');       
			$builder->where('order_id', $_REQUEST['txnid']);
			$query = $builder->get();
			$orderDetails = $query->getResultArray();
			if(count($orderDetails) > 0){
				$orderDetails = $orderDetails[0];
				$builder = $db->table('payu_token_validation');        
				$builder->select('*');       
				$builder->where('email_id', $orderDetails['email_id']);
				$builder->where('validation_id', $orderDetails['token_validation_id']);
				$query = $builder->get();
				$result = $query->getResultArray();
				if (count($result) > 0) {
					$clientDetails = $result[0];
					$data = [
						'status' => "FAILED",
						'api_response' => addslashes(json_encode($_REQUEST))
					];
					$builderupdate = $db->table('order_payment_details'); 
					$builderupdate->where('order_id', $_REQUEST['txnid']); 
					$builderupdate->update($data);
				}
				$this->redirectBigcommerce($clientDetails['email_id'],$_REQUEST['txnid'],$clientDetails['validation_id']);
			}
		}
	}
	
	/**
	 * get Cart Data from BigCommerce API
	 * @param text| $email_id
	 * @param text| $cartId
	 * @param text| $validation_id
	 * @return cart Data from BigCommerce api
	 */
	public function getCartData($email_id,$cartId,$validation_id){
		$data = array();
		if(!empty($cartId) && !empty($email_id)){
			$db = \Config\Database::connect();
			$builder = $db->table('payu_token_validation');        
			$builder->select('*');       
			$builder->where('email_id', $email_id);
			$builder->where('validation_id', $validation_id);
			$query = $builder->get();
			$result = $query->getResultArray();
			if (count($result) > 0) {
				$result = $result[0];
				$request = '';
				$url = getenv('bigcommerceapp.STORE_URL').$result['store_hash'].'/v3/checkouts/'.$cartId.'?include=cart.line_items.physical_items.options%2Ccart.line_items.digital_items.options%2Ccustomer%2Ccustomer.customerGroup%2Cpayments%2Cpromotions.banners%2Ccart.line_items.physical_items.categoryNames%2Ccart.line_items.digital_items.category_names';
				
				$client = \Config\Services::curlrequest();
				$response = $client->request('get', $url, [
						'headers' => [
								'X-Auth-Token' => $result['acess_token'],
								'store_hash' => $result['store_hash'],
								'Accept' => 'application/json',
								'Content-Type' => 'application/json'
						]
				]);
				
				if (strpos($response->getHeader('content-type'), 'application/json') != false){
					$res = $response->getBody();
				
					$data = [
						'email_id' => $email_id,
						'type' => 'BigCommerce',
						'action'    => 'Cart Data',
						'api_url'    => addslashes($url),
						'api_request' => addslashes($request),
						'api_response' => addslashes($res),
						'token_validation_id' => $validation_id,
					];
					$builderinsert = $db->table('api_log'); 
					$builderinsert->insert($data);
					if(!empty($res)){
						$res = json_decode($res,true);
						if(isset($res['data'])){
							$data = $res['data'];
						}
					}
				}
			}
		}
		
		return $data;
	}
	/**
	 * redirect to BigCommerce
	 *
	 */
	public function redirectBigcommerce($email_id,$invoice_id,$validation_id){
		$db = \Config\Database::connect();
		$builder = $db->table('payu_token_validation');        
		$builder->select('*');       
		$builder->where('email_id', $email_id);
		$builder->where('validation_id', $validation_id);
		$query = $builder->get();
		$result = $query->getResultArray();
		if (count($result) > 0) {
			$result = $result[0];
			$url = getenv('bigcommerceapp.STORE_URL').$result['store_hash'].'/v2/store';
			$client = \Config\Services::curlrequest();
			$response = $client->request('get', $url, [
					'headers' => [
							'X-Auth-Token' => $result['acess_token'],
							'store_hash' => $result['store_hash'],
							'Accept' => 'application/json',
							'Content-Type' => 'application/json'
					]
			]);
			if (strpos($response->getHeader('content-type'), 'application/json') != false){
				$res = $response->getBody();
				
				log_message('info', "RedirectBigcommerce - Store API Response : ".$res);
				
				if(!empty($res)){
					$res = json_decode($res,true);
					if(isset($res['secure_url'])){
						$builder = $db->table('order_details');        
						$builder->select('*');       
						$builder->where('email_id', $email_id);
						$builder->where('invoice_id', $invoice_id);
						$builder->where('token_validation_id', $validation_id);
						$query = $builder->get();
						$invoice_result = $query->getResultArray();
						if(isset($invoice_result[0])) {
							$invoice_result = $invoice_result[0];
							$order_id = $invoice_result['order_id'];
							$invoice_id = $invoice_result['invoice_id'];
							$bg_customer_id = $invoice_result['bg_customer_id'];
							
								log_message('info', "Redirecting to carsaver-order-confirmation.");
								
								$invoice_id = base64_encode(json_encode($invoice_id,true));
								$url = $res['secure_url'].'/payu-order-confirmation?authKey='.$invoice_id;
								echo '<script>window.parent.location.href="'.$url.'";</script>';
						}else{
							$url = $res['secure_url']."/checkout?payuinv=".base64_encode(json_encode($invoice_id));
							echo '<script>window.parent.location.href="'.$url.'";</script>';
						}
					}
				}
			}
		}
	}
	
	/*
	* Check Payment Status whether it is failed or not
	*/
	public function getPaymentStatus(){
		
		$final_data = array();
		$final_data['status'] = false;
		$final_data['data'] = array();
		$final_data['msg'] = '';
		if(!empty($this->request->getPost('authKey'))){
			$invoiceId = json_decode(base64_decode($this->request->getPost('authKey')),true);
			if($invoiceId != ""){
				$db = \Config\Database::connect();
				$builder = $db->table('order_payment_details');        
				$builder->select('*');       
				$builder->where('order_id', $invoiceId);
				$query = $builder->get();
				$result_order_payment = $query->getResultArray();
				if($result_order_payment['status'] != "CONFIRMED"){
					$api_response = stripslashes($result_order_payment['api_response']);
					$api_response = json_decode($api_response,true);
					$final_data['status'] = true;
					$final_data['msg'] = "Payment Unsuccessful, Please try again.";
					if(($api_response['status'] == "failure")){
						$final_data['msg'] = $api_response['error_Message'];
					}
				}
			}
		}
		echo json_encode($final_data,true);exit;
	}
}
