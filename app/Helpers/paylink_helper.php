<?php
/**
 * This file is part of the 247Commerce BigCommerce PayU App.
 *
 * ©247 Commerce Limited <info@247commerce.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
 /**
 * Class CustomOrderPages
 *
 * Represents a helper class to create Invoice API
 */
class Paylink
{	

	public static function get_access_token(){
		$access_token = '';
		$session_expiry_time = 120;//In minutes
		
		$db = \Config\Database::connect();
		$builder = $db->table('payu_onboarding_token');        
		$builder->select('*');       
		$builder->where('type', 'INVOICE');
		$query = $builder->get();
		$result = $query->getResultArray();
		if (count($result) > 0) {
			$result = $result[0];
			if(!empty($result['access_token'])){
				$startdate = $result['last_updated'];
				$enddate = date("Y-m-d H:m:i");
				$starttimestamp = strtotime($startdate);
				$endtimestamp = strtotime($enddate);
				$difference = abs($endtimestamp - $starttimestamp)/60;
				if($difference <= $session_expiry_time){
					$access_token = $result['access_token'];
				}
			}
		}
		return $access_token;
	}

	/**
	 * Process API Request
	 * @param $request
	 * @param $url
	 */
	public static function set_access_token()
	{
		$access_token = '';
		$url = getenv('payu.HUB_BASE_URL').'oauth/token';
		
		$request = array();
		$request['client_id'] = getenv('payu.CLIENT_ID');
		$request['client_secret'] = getenv('payu.CLIENT_SECRET');
		$request['grant_type'] = 'client_credentials';
		$request['scope'] = 'create_invoice_payumoney read_invoice_payumoney update_invoice_payumoney';
		$request = json_encode($request,true);
		
		try{
			$client = \Config\Services::curlrequest();
			$response = $client->setBody($request)->request('POST', $url, [
					'headers' => ['Content-Type'=>'application/json']
			]);
			if (strpos($response->getHeader('content-type'), 'application/json') != false){
				$res = $response->getBody();
				$response = json_decode($res,true);
				if(isset($response['access_token'])){
					$access_token = $response['access_token'];
					
					$db = \Config\Database::connect();
					$builder = $db->table('payu_onboarding_token');        
					$builder->select('*');       
					$builder->where('type', 'INVOICE');
					$query = $builder->get();
					$result = $query->getResultArray();
					if (count($result) > 0) {
						$result = $result[0];
						$data = [
							'access_token' => $response['access_token'],
							'last_updated' => date("Y-m-d H:m:i"),
						];
						$builderupdate = $db->table('payu_onboarding_token'); 
						$builderupdate->where('id', $result['id']);
						$builderupdate->update($data);
					}else{
						$data = [
							'type' => 'INVOICE',
							'access_token' => $response['access_token'],
							'last_updated' => date("Y-m-d H:m:i"),
						];
						$builderupdate = $db->table('payu_onboarding_token'); 
						$builderupdate->insert($data);
					}
				}
			}
		}catch(\Exception $e){
			print_r($e->getMessage());exit;
		}
		return $access_token;
	}
	
	/**
	 * Process API Request
	 * @param $request
	 * @param $url
	 */
	public static function createPaymentLink($paymentDetails)
	{
		helper('paylink');
		$res = array();
		$data['status'] = false;
		$data['msg'] = '';
		$data['data'] = array();
		$db = \Config\Database::connect();
		$access_token = \Paylink::get_access_token();
		if(!empty($access_token)){
			$status = \Paylink::validateMerchatDetails($paymentDetails);
			if($status){
				$url = 'https://test.payumoney.com/invoicing/v2';
				try{
					$request = \Paylink::formatMerchantDetails($paymentDetails);
					//print_r(json_encode($request,true));exit;
					$request = json_encode($request,true);
					//echo $request;exit;
					
					/*$client = \Config\Services::curlrequest();
					$response = $client->setBody($request)->request('POST', $url, [
							'headers' => [
											'Authorization' =>'Bearer '.$access_token,
											'Content-Type'=>'application/x-www-form-urlencoded'
										]
					]);
					print_r($response->getBody());exit;
					if (strpos($response->getHeader('content-type'), 'application/json') != false){
						$res = $response->getBody();*/
						$header = array(
									'Authorization:Bearer '.$access_token,
									'Content-Type:application/json'
								);
						$ch = curl_init($url);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
						curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						
						$res = curl_exec($ch);
						curl_close($ch);
						
						$api_data = [
							'email_id' => 'admin@payu.com',
							'type' => 'PayU',
							'action'    => 'Create PAyment Invoice',
							'api_url'    => addslashes($url),
							'api_request' => addslashes(json_encode($paymentDetails,true)),
							'api_response' => addslashes($res)
						];
						$builderinsert = $db->table('api_log'); 
						$builderinsert->insert($api_data);
						$response = json_decode($res,true);
						if(isset($response['error'])){
							$data['status'] = false;
							$data['msg'] = json_encode($response['error'],true);
						}else if(isset($response['errors'])){
							$data['status'] = false;
							$data['msg'] = json_encode($response['errors'],true);
						}
					//}
				}catch(\Exception $e){
					$data['msg'] = $e->getMessage();
				}
			}
		}
		return $data;
	}
	public static function validateMerchatDetails($paymentDetails){
		$status = true;
		if(!empty($paymentDetails)){
			if(!isset($paymentDetails['adjustment'])){
				$status = false;
			}
			if(!isset($paymentDetails['amount'])){
				$status = false;
			}
			if(!isset($paymentDetails['viaEmail'])){
				$status = false;
			}
			if(!isset($paymentDetails['viaSms'])){
				$status = false;
			}
			if(!isset($paymentDetails['description'])){
				$status = false;
			}
			if(!isset($paymentDetails['expiryDate'])){
				$status = false;
			}
			if(!isset($paymentDetails['maxPaymentsAllowed'])){
				$status = false;
			}
			if(!isset($paymentDetails['isPartialPaymentAllowed'])){
				$status = false;
			}
			if(!isset($paymentDetails['minAmountForCustomer'])){
				$status = false;
			}
			if(!isset($paymentDetails['name'])){
				$status = false;
			}
			if(!isset($paymentDetails['mobile'])){
				$status = false;
			}
			if(!isset($paymentDetails['email'])){
				$status = false;
			}
			if(!isset($paymentDetails['addressPin'])){
				$status = false;
			}
			if(!isset($paymentDetails['addressCity'])){
				$status = false;
			}
			if(!isset($paymentDetails['addressState'])){
				$status = false;
			}
			if(!isset($paymentDetails['addressStreet1'])){
				$status = false;
			}
			if(!isset($paymentDetails['addressStreet2'])){
				$status = false;
			}
		}
		return $status;
	}
	public static function formatMerchantDetails($paymentDetails){
		$data = array();
		$data['id'] = '247PAYLINK'.time();
		$data['createdDate'] = date("Y-m-d").'T'.date("h:i:s.000\Z");
		$data['currency'] = 'INR';
		$data['surl'] = getenv('app.baseURL').'paylink/success';
		$data['furl'] = getenv('app.baseURL').'paylink/failure';
		$data['merchantId'] = '9999999';
		if(!empty($paymentDetails)){
			if(isset($paymentDetails['adjustment'])){
				$data['adjustment'] = $paymentDetails['adjustment'];
			}
			if(isset($paymentDetails['amount'])){
				$data['amount']['subAmount'] = $paymentDetails['amount'];
			}
			if(isset($paymentDetails['viaEmail'])){
				$data['channels']['viaEmail'] = $paymentDetails['viaEmail'];
			}
			if(isset($paymentDetails['viaSms'])){
				$data['channels']['viaSms'] = $paymentDetails['viaSms'];
			}
			if(isset($paymentDetails['description'])){
				$data['description'] = $paymentDetails['description'];
			}
			if(isset($paymentDetails['expiryDate'])){
				$data['expiryDate'] = $paymentDetails['expiryDate'].'T'.date("h:i:s");
			}
			if(isset($paymentDetails['maxPaymentsAllowed'])){
				$data['maxPaymentsAllowed'] = $paymentDetails['maxPaymentsAllowed'];
			}
			if(isset($paymentDetails['isPartialPaymentAllowed'])){
				$data['isPartialPaymentAllowed'] = $paymentDetails['isPartialPaymentAllowed'];
			}
			if(isset($paymentDetails['minAmountForCustomer'])){
				$data['minAmountForCustomer'] = $paymentDetails['minAmountForCustomer'];
			}
			if(isset($paymentDetails['mobile'])){
				$data['customerDetails']['mobile'] = $paymentDetails['mobile'];
			}
			if(isset($paymentDetails['email'])){
				$data['customerDetails']['email'] = $paymentDetails['email'];
			}
			if(isset($paymentDetails['name'])){
				$data['customerDetails']['name'] = $paymentDetails['name'];
			}
			if(isset($paymentDetails['addressCity'])){
				$data['customerDetails']['address']['addressCity'] = $paymentDetails['addressCity'];
			}
			if(isset($paymentDetails['addressPin'])){
				$data['customerDetails']['address']['addressPin'] = $paymentDetails['addressPin'];
			}
			if(isset($paymentDetails['addressState'])){
				$data['customerDetails']['address']['addressState'] = $paymentDetails['addressState'];
			}
			if(isset($paymentDetails['addressStreet1'])){
				$data['customerDetails']['address']['addressStreet1'] = $paymentDetails['addressStreet1'];
			}
			if(isset($paymentDetails['addressStreet2'])){
				$data['customerDetails']['address']['addressStreet2'] = $paymentDetails['addressStreet2'];
			}
		}
		return $data;
	}
}