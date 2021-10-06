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
 * Represents a helper class to create Onboarding Merchant APIV3 
 */
class Onboarding
{		
	public static function get_access_token(){
		$access_token = '';
		$session_expiry_time = 120;//In minutes
		
		$db = \Config\Database::connect();
		$builder = $db->table('payu_onboarding_token');        
		$builder->select('*');       
		$builder->where('type', 'ONBOARDING');
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
		if(getenv('CI_ENVIRONMENT') == "production"){
			$HUB_BASE_URL = getenv('payu.HUB_BASE_URL');//PAY target url
		}else{
			$HUB_BASE_URL = getenv('payu.HUB_BASE_URL_TEST');//PAY target url
		}
		$url = $HUB_BASE_URL.'oauth/token';
		
		$request = array();
		$request['client_id'] = getenv('payu.CLIENT_ID');
		$request['client_secret'] = getenv('payu.CLIENT_SECRET');
		$request['grant_type'] = 'client_credentials';
		$request['scope'] = 'refer_merchant';
		$request = http_build_query($request);
			
		$db = \Config\Database::connect();
		
		try{
			$client = \Config\Services::curlrequest();

			$response = $client->setBody($request)->request('POST', $url, [
					'headers' => ['Content-Type'=>'application/x-www-form-urlencoded']
			]);
			if (strpos($response->getHeader('content-type'), 'application/json') != false){
				$res = $response->getBody();
				$response = json_decode($res,true);
				if(isset($response['access_token'])){
					$access_token = $response['access_token'];
					
					$builder = $db->table('payu_onboarding_token');        
					$builder->select('*');       
					$builder->where('type', 'ONBOARDING');
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
							'access_token' => $response['access_token'],
							'last_updated' => date("Y-m-d H:m:i"),
						];
						$builderupdate = $db->table('payu_onboarding_token'); 
						$builderupdate->insert($data);
					}
				}
			}
		}catch(\Exception $e){
			$api_data = [
				'email_id' => 'admin@payu.com',
				'type' => 'PayU',
				'action'    => 'Generate Access TOken',
				'api_url'    => addslashes($url),
				'api_request' => addslashes($request),
				'api_response' => addslashes($e->getMessage())
			];
			$builderinsert = $db->table('api_log'); 
			$builderinsert->insert($api_data);
		}
		return $access_token;
	}
	
	public static function get_otp_access_token(){
		$access_token = '';
		$session_expiry_time = 120;//In minutes
		
		$db = \Config\Database::connect();
		$builder = $db->table('payu_onboarding_token');        
		$builder->select('*');       
		$builder->where('type', 'OTP');
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
	public static function set_otp_access_token()
	{
		$access_token = '';
		if(getenv('CI_ENVIRONMENT') == "production"){
			$HUB_BASE_URL = getenv('payu.HUB_BASE_URL');//PAY target url
		}else{
			$HUB_BASE_URL = getenv('payu.HUB_BASE_URL_TEST');//PAY target url
		}
		$url = $HUB_BASE_URL.'oauth/token';
		
		$request = array();
		$request['client_id'] = getenv('payu.CLIENT_ID');
		$request['client_secret'] = getenv('payu.CLIENT_SECRET');
		$request['grant_type'] = 'client_credentials';
		$request['scope'] = 'send_sign_in_otp verify_sign_in_otp';
		$request = http_build_query($request);
			
		$db = \Config\Database::connect();
		
		try{
			$client = \Config\Services::curlrequest();

			$response = $client->setBody($request)->request('POST', $url, [
					'headers' => ['Content-Type'=>'application/x-www-form-urlencoded']
			]);
			if (strpos($response->getHeader('content-type'), 'application/json') != false){
				$res = $response->getBody();
				$response = json_decode($res,true);
				if(isset($response['access_token'])){
					$access_token = $response['access_token'];
					
					$builder = $db->table('payu_onboarding_token');        
					$builder->select('*');       
					$builder->where('type', 'OTP');
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
							'type' => 'OTP',
							'access_token' => $response['access_token'],
							'last_updated' => date("Y-m-d H:m:i"),
						];
						$builderupdate = $db->table('payu_onboarding_token'); 
						$builderupdate->insert($data);
					}
				}
			}
		}catch(\Exception $e){
			$api_data = [
				'email_id' => 'admin@payu.com',
				'type' => 'PayU',
				'action'    => 'Generate OTP Access TOken',
				'api_url'    => addslashes($url),
				'api_request' => addslashes($request),
				'api_response' => addslashes($e->getMessage())
			];
			$builderinsert = $db->table('api_log'); 
			$builderinsert->insert($api_data);
		}
		return $access_token;
	}
	
	/**
	 * Process API Request
	 * @param $request
	 * @param $url
	 */
	public static function createMerchant($merchantDetails)
	{
		helper('onboarding');
		$res = array();
		$data['status'] = false;
		$data['msg'] = '';
		$data['data'] = array();
		$db = \Config\Database::connect();
		$access_token = \Onboarding::get_access_token();
		if(!empty($access_token)){
			$status = \Onboarding::validateMerchatDetails($merchantDetails);
			if($status){
				if(getenv('CI_ENVIRONMENT') == "production"){
					$PARTNER_BASE_URL = getenv('payu.PARTNER_BASE_URL');//PAY target url
				}else{
					$PARTNER_BASE_URL = getenv('payu.PARTNER_BASE_URL_TEST');//PAY target url
				}
				$url = $PARTNER_BASE_URL.'api/v3/merchants';
				try{
					$request = \Onboarding::formatMerchantDetails($merchantDetails);
					$request = http_build_query($request);
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
									'Content-Type:application/x-www-form-urlencoded'
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
							'action'    => 'Create Merchant',
							'api_url'    => addslashes($url),
							'api_request' => addslashes(json_encode($merchantDetails,true)),
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
						}else if(isset($response['merchant'])){
							$data['status'] = true;
							$data['data'] = $response;
							
							$user_data = [
								'mid' => $response['merchant']['mid'],
								'email' => $response['merchant']['email'],
								'mobile'    => $response['merchant']['registered_mobile'],
								'params' => addslashes($res)
							];
							$builderinsert = $db->table('payu_onboarding_users'); 
							$builderinsert->insert($user_data);
							
						}
					//}
				}catch(\Exception $e){
					$data['msg'] = $e->getMessage();
				}
			}
		}
		return $data;
	}
	public static function validateMerchatDetails($merchantDetails){
		$status = true;
		if(!empty($merchantDetails)){
			if(!isset($merchantDetails['display_name'])){
				$status = false;
			}
			if(!isset($merchantDetails['email'])){
				$status = false;
			}
			if(!isset($merchantDetails['mobile'])){
				$status = false;
			}
			if(!isset($merchantDetails['registered_name'])){
				$status = false;
			}
			if(!isset($merchantDetails['business_entity_type'])){
				$status = false;
			}
			if(!isset($merchantDetails['business_category'])){
				$status = false;
			}
			if(!isset($merchantDetails['business_subcategory'])){
				$status = false;
			}
			if(!isset($merchantDetails['monthly_expected_volume'])){
				$status = false;
			}
			if(!isset($merchantDetails['pancard_name'])){
				$status = false;
			}
			if(!isset($merchantDetails['pan'])){
				$status = false;
			}
			if(!isset($merchantDetails['addr_line1'])){
				$status = false;
			}
			if(!isset($merchantDetails['pin'])){
				$status = false;
			}
			if(!isset($merchantDetails['account_holder_name'])){
				$status = false;
			}
			if(!isset($merchantDetails['account_no'])){
				$status = false;
			}
			if(!isset($merchantDetails['ifsc_code'])){
				$status = false;
			}
		}
		return $status;
	}
	public static function formatMerchantDetails($merchantDetails){
		$data = array();
		if(!empty($merchantDetails)){
			if(isset($merchantDetails['display_name'])){
				$data['merchant[display_name]'] = $merchantDetails['display_name'];
			}
			if(isset($merchantDetails['email'])){
				$data['merchant[email]'] = $merchantDetails['email'];
			}
			if(isset($merchantDetails['mobile'])){
				$data['merchant[mobile]'] = $merchantDetails['mobile'];
			}
			if(isset($merchantDetails['registered_name'])){
				$data['merchant[business_details][registered_name]'] = $merchantDetails['registered_name'];
			}
			if(isset($merchantDetails['business_entity_type'])){
				$data['merchant[business_details][business_entity_type]'] = $merchantDetails['business_entity_type'];
			}
			if(isset($merchantDetails['business_category'])){
				$data['merchant[business_details][business_category]'] = $merchantDetails['business_category'];
			}
			if(isset($merchantDetails['business_subcategory'])){
				$data['merchant[business_details][business_sub_category]'] = $merchantDetails['business_subcategory'];
			}
			if(isset($merchantDetails['monthly_expected_volume'])){
				$data['merchant[monthly_expected_volume]'] = $merchantDetails['monthly_expected_volume'];
			}
			if(isset($merchantDetails['pancard_name'])){
				$data['merchant[business_details][pancard_name]'] = $merchantDetails['pancard_name'];
			}
			if(isset($merchantDetails['pan'])){
				$data['merchant[business_details][pan]'] = $merchantDetails['pan'];
			}
			if(isset($merchantDetails['addr_line1'])){
				$data['merchant[business_address][addr_line1]'] = $merchantDetails['addr_line1'];
			}
			if(isset($merchantDetails['pin'])){
				$data['merchant[business_address][pin]'] = $merchantDetails['pin'];
			}
			if(isset($merchantDetails['city'])){
				$data['merchant[business_address][city]'] = $merchantDetails['city'];
			}
			if(isset($merchantDetails['state'])){
				$data['merchant[business_address][state]'] = $merchantDetails['state'];
			}
			if(isset($merchantDetails['operating_addr_line1'])){
				$data['merchant[operating_address][addr_line1]'] = $merchantDetails['operating_addr_line1'];
			}
			if(isset($merchantDetails['operating_pin'])){
				$data['merchant[operating_address][pin]'] = $merchantDetails['operating_pin'];
			}
			if(isset($merchantDetails['operating_city'])){
				$data['merchant[operating_address][city]'] = $merchantDetails['operating_city'];
			}
			if(isset($merchantDetails['operating_state'])){
				$data['merchant[operating_address][state]'] = $merchantDetails['operating_state'];
			}
			if(isset($merchantDetails['account_holder_name'])){
				$data['merchant[bank_details][account_holder_name]'] = $merchantDetails['account_holder_name'];
			}
			if(isset($merchantDetails['account_no'])){
				$data['merchant[bank_details][account_no]'] = $merchantDetails['account_no'];
			}
			if(isset($merchantDetails['ifsc_code'])){
				$data['merchant[bank_details][ifsc_code]'] = $merchantDetails['ifsc_code'];
			}
			if(isset($merchantDetails['website_url'])){
				$data['merchant[website_url]'] = $merchantDetails['website_url'];
			}
			if(isset($merchantDetails['android_url'])){
				$data['merchant[android_url]'] = $merchantDetails['android_url'];
			}
			if(isset($merchantDetails['ios_url'])){
				$data['merchant[ios_url]'] = $merchantDetails['ios_url'];
			}
		}
		return $data;
	}
	
	/**
	 * Process API Request
	 * @param $request
	 * @param $url
	 */
	public static function getMerchant($mid)
	{
		helper('onboarding');
		$res = array();
		$data['status'] = false;
		$data['msg'] = '';
		$data['data'] = array();
		$db = \Config\Database::connect();
		$access_token = \Onboarding::get_access_token();
		if(!empty($access_token)){
			if(!empty($mid)){
				if(getenv('CI_ENVIRONMENT') == "production"){
					$PARTNER_BASE_URL = getenv('payu.PARTNER_BASE_URL');//PAY target url
				}else{
					$PARTNER_BASE_URL = getenv('payu.PARTNER_BASE_URL_TEST');//PAY target url
				}
				$url = $PARTNER_BASE_URL.'api/v1/merchants/'.$mid;
				try{
					$request = '';
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
									'Content-Type:application/x-www-form-urlencoded'
								);
						$ch = curl_init($url);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
						//curl_setopt($ch, CURLOPT_POST, 1);
						//curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
						curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						
						$res = curl_exec($ch);
						curl_close($ch);
						$api_data = [
							'email_id' => 'admin@payu.com',
							'type' => 'PayU',
							'action'    => 'Get Merchant',
							'api_url'    => addslashes($url),
							'api_request' => '',
							'api_response' => addslashes($res)
						];
						$builderinsert = $db->table('api_log'); 
						$builderinsert->insert($api_data);
						$response = json_decode($res,true);
						
						if(isset($response['merchant'])){
							$data['status'] = true;
							$data['data'] = $response;
						}
					//}
				}catch(\Exception $e){
					$data['msg'] = $e->getMessage();
				}
			}
		}
		return $data;
	}
	
	/**
	 * Process API Request
	 * @param $request
	 * @param $url
	 */
	public static function getMerchantCreds($mid)
	{
		helper('onboarding');
		$res = array();
		$data['status'] = false;
		$data['msg'] = '';
		$data['data'] = array();
		$db = \Config\Database::connect();
		$access_token = \Onboarding::get_access_token();
		if(!empty($access_token)){
			if(!empty($mid)){
				if(getenv('CI_ENVIRONMENT') == "production"){
					$PARTNER_BASE_URL = getenv('payu.PARTNER_BASE_URL');//PAY target url
				}else{
					$PARTNER_BASE_URL = getenv('payu.PARTNER_BASE_URL_TEST');//PAY target url
				}
				$url = $PARTNER_BASE_URL.'api/v1/merchants/'.$mid.'/credential';
				try{
					$request = '';
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
									'Content-Type:application/x-www-form-urlencoded'
								);
						$ch = curl_init($url);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
						//curl_setopt($ch, CURLOPT_POST, 1);
						//curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
						curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						
						$res = curl_exec($ch);
						curl_close($ch);
						$api_data = [
							'email_id' => 'admin@payu.com',
							'type' => 'PayU',
							'action'    => 'Get Merchant Credentials',
							'api_url'    => addslashes($url),
							'api_request' => '',
							'api_response' => addslashes($res)
						];
						$builderinsert = $db->table('api_log'); 
						$builderinsert->insert($api_data);
						$response = json_decode($res,true);
						
						if(isset($response['data']['credentials'])){
							$data['status'] = true;
							$data['data'] = $response['data'];
						}
					//}
				}catch(\Exception $e){
					$data['msg'] = $e->getMessage();
				}
			}
		}
		return $data;
	}
	
	/**
	 * Process API Request
	 * @param $request
	 * @param $url
	 */
	public static function verifyLinkMerchant($mid,$merchant_key,$merchant_salt)
	{
		helper('onboarding');
		$res = array();
		$data['status'] = false;
		$data['msg'] = '';
		$data['data'] = array();
		$db = \Config\Database::connect();
		$access_token = \Onboarding::get_access_token();
		if(!empty($access_token)){
			$uuid = getenv('payu.UUID');
			if(!empty($mid) && !empty($merchant_key) && !empty($merchant_salt) && !empty($uuid)){
				if(getenv('CI_ENVIRONMENT') == "production"){
					$PARTNER_BASE_URL = getenv('payu.PARTNER_BASE_URL');//PAY target url
				}else{
					$PARTNER_BASE_URL = getenv('payu.PARTNER_BASE_URL_TEST');//PAY target url
				}
				$url = $PARTNER_BASE_URL.'api/v1/merchants/'.$mid.'/verify';
				//data = [reseller_uuid|product|mid|prod_key|prod_salt].
				$check = $uuid.'|payumoney|'.$mid.'|'.$merchant_key.'|'.$merchant_salt;
				$checksum = strtolower(hash('sha512', $check));
				try{
					$request = array('checksum'=>$checksum,'merchant_key'=>$merchant_key);
					$request_build = http_build_query($request);
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
									'Content-Type:application/x-www-form-urlencoded'
								);
						$ch = curl_init($url);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $request_build);
						curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						
						$res = curl_exec($ch);
						curl_close($ch);
						$api_data = [
							'email_id' => 'admin@payu.com',
							'type' => 'PayU',
							'action'    => 'Verify and Link Merchant',
							'api_url'    => addslashes($url),
							'api_request' => addslashes(json_encode($request)),
							'api_response' => addslashes($res)
						];
						$builderinsert = $db->table('api_log'); 
						$builderinsert->insert($api_data);
						$response = json_decode($res,true);
						
						if(isset($response['message']) && ($response['message'] == "Successfully linked to the Partner")){
							$data['status'] = true;
						}else if(isset($response['error']) && ($response['error']['mid'] == "Merchant already linked with same Partner account")){
							$data['status'] = true;
						}
					//}
				}catch(\Exception $e){
					$data['msg'] = $e->getMessage();
				}
			}
		}
		return $data;
	}
	
	/**
	 * Process API Request
	 * @param $request
	 * @param $url
	 */
	public static function updateBankDetails($mid)
	{
		helper('onboarding');
		$res = array();
		$data['status'] = false;
		$data['msg'] = '';
		$data['data'] = array();
		$db = \Config\Database::connect();
		if(!empty($mid)){
			$merchantDetails = \Onboarding::getMerchant($mid);
			$merchantCreds = \Onboarding::getMerchantCreds($mid);
			if($merchantDetails['status'] == true && $merchantDetails['status'] == true){
				$access_token = \Onboarding::get_user_access_token($mid,$merchantDetails['data']['merchant']['mobile']);
				if(!empty($access_token)){
					$creds = $merchantCreds['data']['credentials'];
					$key = '';
					$salt = '';
					$auth_header = '';
					if(getenv('CI_ENVIRONMENT') == 'development'){
						$key = $creds['test_key'];
						$salt = $creds['test_salt'];
						$auth_header = $creds['test_authheader'];
					}else{
						$key = $creds['prod_key'];
						$salt = $creds['prod_salt'];
						$auth_header = $creds['prod_authheader'];
					}
					if(getenv('CI_ENVIRONMENT') == "production"){
						$PARTNER_BASE_URL = getenv('payu.PARTNER_BASE_URL');//PAY target url
					}else{
						$PARTNER_BASE_URL = getenv('payu.PARTNER_BASE_URL_TEST');//PAY target url
					}
					$url = $PARTNER_BASE_URL.'api/v1/merchants/'.$merchantDetails['data']['merchant']['uuid'].'/add_bank_detail';
					try{
						$bankDetails = $merchantDetails['data']['merchant']['bank_detail'];
						$request = '{"bank_detail": {"bank_account_number": "'.$bankDetails['bank_account_number'].'","ifsc_code": "'.$bankDetails['ifsc_code'].'","holder_name": "'.$bankDetails['holder_name'].'"}}';
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
								'action'    => 'Update Bank Details',
								'api_url'    => addslashes($url),
								'api_request' => addslashes(json_encode($request)),
								'api_response' => addslashes($res)
							];
							$builderinsert = $db->table('api_log'); 
							$builderinsert->insert($api_data);
							$response = json_decode($res,true);
							
							if(isset($response['bank_detail'])){
								$data['status'] = true;
								$data['bank_detail'] = $response['bank_detail'];
							}
						//}
					}catch(\Exception $e){
						$data['msg'] = $e->getMessage();
					}
				}
			}
		}
		return $data;
	}
	
	public static function get_user_access_token($mid,$mobile){
		$access_token = '';
		$session_expiry_time = 120;//In minutes
		
		$db = \Config\Database::connect();
		$builder = $db->table('payu_onboarding_user_token');        
		$builder->select('*');       
		$builder->where('mid', $mid);
		$builder->where('mobile', $mobile);
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
	public static function sendOTP($mid)
	{
		helper('onboarding');
		$res = array();
		$data['status'] = false;
		$data['msg'] = '';
		$data['data'] = array();
		$db = \Config\Database::connect();
		$access_token = \Onboarding::get_otp_access_token();
		if(!empty($access_token)){
			if(!empty($mid)){
				$merchantDetails = \Onboarding::getMerchant($mid);
				if($merchantDetails['status'] == true){
					if(getenv('CI_ENVIRONMENT') == "production"){
						$PARTNER_BASE_URL = getenv('payu.PARTNER_BASE_URL');//PAY target url
					}else{
						$PARTNER_BASE_URL = getenv('payu.PARTNER_BASE_URL_TEST');//PAY target url
					}
					$url = $PARTNER_BASE_URL.'api/v1/otps/send_otp';
					try{
						$bankDetails = $merchantDetails['data']['merchant']['bank_detail'];
						$request = array(
										'otp[identity]'=>$merchantDetails['data']['merchant']['mobile'],
										'otp[scope]'=>'create_bank_details update_bank_details',
										'otp[channels][]'=>'sms',
										'otp[type]'=>'SignIn',
									);
						$request = http_build_query($request);
						
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
										'Content-Type:application/x-www-form-urlencoded'
									);
							
							$curl = curl_init();

							curl_setopt_array($curl, array(
								CURLOPT_URL => $url,
								CURLOPT_RETURNTRANSFER => true,
								CURLOPT_ENCODING => '',
								CURLOPT_MAXREDIRS => 10,
								CURLOPT_TIMEOUT => 0,
								CURLOPT_FOLLOWLOCATION => true,
								CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
								CURLOPT_CUSTOMREQUEST => 'POST',
								CURLOPT_POSTFIELDS => $request,
								CURLOPT_HTTPHEADER => $header,
							));
							
							$res = curl_exec($curl);
							curl_close($curl);
							
							$api_data = [
								'email_id' => 'admin@payu.com',
								'type' => 'PayU',
								'action'    => 'Send OTP',
								'api_url'    => addslashes($url),
								'api_request' => addslashes(json_encode($request)),
								'api_response' => addslashes($res)
							];
							$builderinsert = $db->table('api_log'); 
							$builderinsert->insert($api_data);
							
							//$res = '{"data": {"id": "11ea-bf84-27aef522-85a0-02f413145cce","type": "notifications","attributes": {"status": "sidekiq_queued","send-at": "1594038491","status-details": {},"payload": {"sms": ["90xxxxxx21"]}}}}';
							
							$response = json_decode($res,true);
							
							if(isset($response['data'])){
								$data['status'] = true;
								$data['data'] = $response['data'];
							}else{
								$data['data'] = $response;
							}
						//}
					}catch(\Exception $e){
						$data['msg'] = $e->getMessage();
					}
				}
			}
		}
		return $data;
	}
	
	/**
	 * Process API Request
	 * @param $request
	 * @param $url
	 */
	public static function verifyOTP($mid,$otp)
	{
		helper('onboarding');
		$res = array();
		$data['status'] = false;
		$data['msg'] = '';
		$data['data'] = array();
		$db = \Config\Database::connect();
		$access_token = \Onboarding::get_otp_access_token();
		if(!empty($access_token)){
			if(!empty($mid)){
				$merchantDetails = \Onboarding::getMerchant($mid);
				if($merchantDetails['status'] == true){
					if(getenv('CI_ENVIRONMENT') == "production"){
						$PARTNER_BASE_URL = getenv('payu.PARTNER_BASE_URL');//PAY target url
					}else{
						$PARTNER_BASE_URL = getenv('payu.PARTNER_BASE_URL_TEST');//PAY target url
					}
					$url = $PARTNER_BASE_URL.'api/v1/otps/verify_otp';
					try{
						$request = array(
										'otp[identity]'=>$merchantDetails['data']['merchant']['mobile'],
										'otp[email]'=>$merchantDetails['data']['merchant']['email'],
										'otp[code]'=>$otp,
										'otp[type]'=>'SignIn',
									);
						$request = http_build_query($request);
						
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
										'Content-Type:application/x-www-form-urlencoded'
									);
							
							$curl = curl_init();

							curl_setopt_array($curl, array(
								CURLOPT_URL => $url,
								CURLOPT_RETURNTRANSFER => true,
								CURLOPT_ENCODING => '',
								CURLOPT_MAXREDIRS => 10,
								CURLOPT_TIMEOUT => 0,
								CURLOPT_FOLLOWLOCATION => true,
								CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
								CURLOPT_CUSTOMREQUEST => 'POST',
								CURLOPT_POSTFIELDS => $request,
								CURLOPT_HTTPHEADER => $header,
							));
							
							$res = curl_exec($curl);
							curl_close($curl);
							
							$api_data = [
								'email_id' => 'admin@payu.com',
								'type' => 'PayU',
								'action'    => 'Verify OTP',
								'api_url'    => addslashes($url),
								'api_request' => addslashes(json_encode($request)),
								'api_response' => addslashes($res)
							];
							$builderinsert = $db->table('api_log'); 
							$builderinsert->insert($api_data);
							
							//$res = '{"access_token": "2ed402f549adc354c7f681ca84a0dec7d9a91a8e229d3ee61e2440d1b61d26ec","token_type": "Bearer","expires_in": 7200,"scope": "user_profile create_bank_details update_bank_details","created_at": 1594038150,"user_uuid": "11ea-bf7f-3063406a-85a0-02f413145cce","reseller_uuid": null,"mid": "7060011","merchant_uuid": "11ea-bf7f-2db8ef5e-9363-0acb18027a2a"}';
							
							$response = json_decode($res,true);
							
							if(isset($response['access_token'])){
								$db = \Config\Database::connect();
								$builder = $db->table('payu_onboarding_user_token');        
								$builder->select('*');       
								$builder->where('mobile', $merchantDetails['data']['merchant']['mobile']);
								$builder->where('mid', $mid);
								$query = $builder->get();
								$result = $query->getResultArray();
								if (count($result) > 0) {
									$result = $result[0];
									$udata = [
										'access_token' => $response['access_token'],
										'last_updated' => date("Y-m-d H:m:i"),
									];
									$builderupdate = $db->table('payu_onboarding_user_token'); 
									$builderupdate->where('id', $result['id']);
									$builderupdate->update($udata);
								}else{
									$udata = [
										'mid' => $mid,
										'mobile' => $merchantDetails['data']['merchant']['mobile'],
										'access_token' => $response['access_token'],
										'last_updated' => date("Y-m-d H:m:i"),
									];
									$builderupdate = $db->table('payu_onboarding_user_token'); 
									$builderupdate->insert($udata);
								}
								//$bank_status = \Onboarding::updateBankDetails($mid);
								$data['status'] = true;
								$data['data'] = $response;
							}else{
								$data['data'] = $response;
							}
						//}
					}catch(\Exception $e){
						$data['msg'] = $e->getMessage();
					}
				}
			}
		}
		return $data;
	}
	
	/**
	 * Process API Request
	 * @param $request
	 * @param $url
	 */
	public static function verifyPenny($mid,$amount)
	{
		helper('onboarding');
		$res = array();
		$data['status'] = false;
		$data['msg'] = '';
		$data['data'] = array();
		$db = \Config\Database::connect();
		
		if(!empty($mid)){
			$merchantDetails = \Onboarding::getMerchant($mid);
			if($merchantDetails['status'] == true){
				if(getenv('CI_ENVIRONMENT') == "production"){
					$PARTNER_BASE_URL = getenv('payu.PARTNER_BASE_URL');//PAY target url
				}else{
					$PARTNER_BASE_URL = getenv('payu.PARTNER_BASE_URL_TEST');//PAY target url
				}
				$url = $PARTNER_BASE_URL.'api/v1/merchants/'.$merchantDetails['data']['merchant']['uuid'].'/verify_penny';
				$mobile = $merchantDetails['data']['merchant']['mobile'];
				$access_token = \Onboarding::get_user_access_token($mid,$mobile);
				if(!empty($access_token)){
					try{
						$request = '{"penny_amount":"'.$amount.'"}';
						
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
							
							$curl = curl_init();

							curl_setopt_array($curl, array(
								CURLOPT_URL => $url,
								CURLOPT_RETURNTRANSFER => true,
								CURLOPT_ENCODING => '',
								CURLOPT_MAXREDIRS => 10,
								CURLOPT_TIMEOUT => 0,
								CURLOPT_FOLLOWLOCATION => true,
								CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
								CURLOPT_CUSTOMREQUEST => 'POST',
								CURLOPT_POSTFIELDS => $request,
								CURLOPT_HTTPHEADER => $header,
							));
							
							$res = curl_exec($curl);
							curl_close($curl);
							$api_data = [
								'email_id' => 'admin@payu.com',
								'type' => 'PayU',
								'action'    => 'Penny Verify',
								'api_url'    => addslashes($url),
								'api_request' => addslashes(json_encode($request)),
								'api_response' => addslashes($res)
							];
							$builderinsert = $db->table('api_log'); 
							$builderinsert->insert($api_data);
							
							//$res = '{"message": "Bank verification successful"}';
							$response = json_decode($res,true);
							
							if(isset($response['message']) &&($response['message'] == "Bank verification successful")){
								$data['status'] = true;
								$data['data'] = $response;
							}else{
								$data['data'] = $response;
							}
						//}
					}catch(\Exception $e){
						$data['msg'] = $e->getMessage();
					}
				}
			}
		}
		return $data;
	}
}