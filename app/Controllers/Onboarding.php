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
 * Class Onboarding
 *
 * Represents a PayU Onboarding process
 */
class Onboarding extends BaseController
{
	protected $access_token;
	protected $otp_access_token;
	public function __construct() {
		helper('settingsviews');
		$clientDetails = \SettingsViews::getClientDetails();
		if(empty($clientDetails)){
			return redirect()->to('/');
		}
		helper('onboarding');
		$this->set_access_token();
		$this->set_otp_access_token();
	}
	
	/**
	 * Setting Access Token
	 */
	public function set_access_token()
	{ 
		if(empty($this->access_token) == true){	
			$session_id = \Onboarding::get_access_token();
			if(empty($session_id) == true){
				$session_id = \Onboarding::set_access_token();
			}
			if(!empty($session_id)){
				$this->access_token = $session_id;
			}
		} 
              
	}
	
	/**
	 * Setting Access Token
	 */
	public function set_otp_access_token()
	{ 
		if(empty($this->otp_access_token) == true){	
			$session_id = \Onboarding::get_otp_access_token();
			if(empty($session_id) == true){
				$session_id = \Onboarding::set_otp_access_token();
			}
			if(!empty($session_id)){
				$this->otp_access_token = $session_id;
			}
		} 
              
	}

	/**
	 * Index - default home page in PayU for Onboarding
	 */
	public function index()
	{
		$clientDetails = \SettingsViews::getClientDetails();
		$data = $this->bigcommerceStoreInfo($clientDetails['email_id'],$clientDetails['validation_id']);
		//print_r(json_encode($data,true));exit;
		$postdata['data'] = $data;
		return view('onboarding/index',$postdata);
	}
	
	/**
	 * createMerchant Api trigger for create Merchant V3
	 */
	public function createMerchant()
	{
		$res = array();
		$res['status'] = false;
		$res['msg'] = 'Some error occurred';
		if($this->request->getMethod() == "post"){
			$postData = $this->request->getPost();
			$postData['website_url'] = "https://www.247commerce.co.uk/";
			$postData['android_url'] = "https://www.247commerce.co.uk/";
			$postData['ios_url'] = "https://www.247commerce.co.uk/";
			$data = \Onboarding::createMerchant($postData);
			if($data['status']){
				$res['status'] = true;
				$res['mid'] = $data['data']['merchant']['mid'];
			}else{
				$res['status'] = false;
				$res['msg'] = $data['msg'];
			}
		}
		return json_encode($res,true);exit;
	}
	
	/**
	 * Link Account 
	 */
	public function linkAccount($mid)
	{
		if(!empty($mid)){
			$data = \Onboarding::getMerchant($mid);
			if($data['status']){
				return view('onboarding/linkAccount',$data);
			}else{
				return redirect()->to('/');
			}
		}else{
			return redirect()->to('/');
		}
	}
	/**
	 * Link Merchant 
	 */
	public function linkMerchant($mid)
	{
		if(!empty($mid)){
			$db = \Config\Database::connect();
			$check = \Onboarding::getMerchant($mid);
			if($check['status']){
				if(isset($check['data']['merchant'])){
					$merchantData = $check['data']['merchant'];
					$data = \Onboarding::getMerchantCreds($mid);
					if($data['status']){
						if(isset($data['data']['credentials'])){
							$creds = $data['data']['credentials'];
							$key = '';
							$salt = '';
							$auth_header = '';
							if(getenv('CI_ENVIRONMENT') == 'development'){
								if(isset($creds['test_key'])){
									$key = $creds['test_key'];
								}else if(isset($creds['prod_key'])){
									$key = $creds['prod_key'];
								}
								
								if(isset($creds['test_salt'])){
									$salt = $creds['test_salt'];
								}else if(isset($creds['prod_salt'])){
									$salt = $creds['prod_salt'];
								}
								
								if(isset($creds['test_authheader'])){
									$auth_header = $creds['test_authheader'];
								}else if(isset($creds['prod_authheader'])){
									$auth_header = $creds['prod_authheader'];
								}
							
							}else{
								if(isset($creds['prod_key'])){
									$key = $creds['prod_key'];
								}
								if(isset($creds['prod_salt'])){
									$salt = $creds['prod_salt'];
								}
								if(isset($creds['prod_authheader'])){
									$auth_header = $creds['prod_authheader'];
								}
							}
							if(!empty($key) && !empty($salt)){
								$session = session();
								$email_id = $session->get('email_id');
								$validation_id = $session->get('validation_id');
								$data = [
									'merchant_id' => $mid,
									'merchant_key' => $key,
									'merchant_salt' => $salt,
									'auth_header' => $auth_header
								];
								$builderupdate = $db->table('payu_token_validation'); 
								$builderupdate->where('email_id', $email_id);
								$builderupdate->where('validation_id', $validation_id);
								$builderupdate->update($data);
							}
							if(($merchantData['penny_deposit_status'] == "Not Initiated") || ($merchantData['bank_verification_status'] == "Pending")){
								return redirect()->to('/onboarding/updateBankDetails/'.$mid);
							}else{
								return redirect()->to('/');
							}
						}
					}else{
						return redirect()->to('/');
					}
				}
			}else{
				//return redirect()->to('/');
			}
		}else{
			//return redirect()->to('/');
		}
	}
	
	/**
	 * Update BankDetails 
	 */
	public function updateBankDetails($mid)
	{
		if(!empty($mid)){
			$check = \Onboarding::getMerchant($mid);
			if($check['status']){
				if(isset($check['data']['merchant'])){
					$merchantData = $check['data']['merchant'];
					if($merchantData['penny_deposit_status'] == "Not Initiated" || $merchantData['bank_verification_status'] == "Pending"){
						//$bank_status = \Onboarding::updateBankDetails($mid);
						return view('onboarding/checkBankVerify',$check);
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
	 * Verify BankDetails 
	 */
	public function VerifyBankDetails()
	{
		if($this->request->getMethod() == "post"){
			$mid = $this->request->getPost('mid');
			if(!empty($mid)){
				$check = \Onboarding::getMerchant($mid);
				if($check['status']){
					if(isset($check['data']['merchant'])){
						$otp_status = \Onboarding::sendOTP($mid);
						if($otp_status['status']){
							return view('onboarding/otpVerify',$check);
						}else{
							$check['error'] = 1;
							$check['otp_status'] = $otp_status;
							return view('onboarding/checkBankVerify',$check);
						}
					}else{
						return redirect()->to('/');
					}
				}else{
					return redirect()->to('/');
				}
			}else{
				return redirect()->to('/');
			}
		}else{
			return redirect()->to('/');
		}
	}
	
	/**
	 * Resend OTP 
	 */
	public function resendOTP()
	{
		$data = array();
		$data['status'] = false;
		$data['msg'] = 'Something went wrong. ';
		if($this->request->getMethod() == "post"){
			$mid = $this->request->getPost('mid');
			if(!empty($mid)){
				$check = \Onboarding::getMerchant($mid);
				if($check['status']){
					if(isset($check['data']['merchant'])){
						$otp_status = \Onboarding::sendOTP($mid);
						//print_r($otp_status);exit;
						if($otp_status['status']){
							$data['status'] = true;
						}else{
							if(isset($otp_status['data']['errors'])){
								if(isset($otp_status['data']['errors']['code'][0])){
									$data['msg'] .= $otp_status['data']['errors']['code'][0].'. ';
								}
								if(isset($otp_status['data']['messages']['code'])){
									$data['msg'] .= $otp_status['data']['messages']['code'].'. ';
								}
								if(isset($otp_status['data']['messages']['user'])){
									$data['msg'] .= $otp_status['data']['messages']['user'].'. ';
								}
							}
						}
					}
				}
			}
		}
		echo json_encode($data,true);exit;
	}
	
	/**
	 * Verify OTP 
	 */
	public function verifyOTP()
	{
		if($this->request->getMethod() == "post"){
			$mid = $this->request->getPost('mid');
			$otp = $this->request->getPost('otp');
			if(!empty($mid) && !empty($otp)){
				$check = \Onboarding::getMerchant($mid);
				if($check['status']){
					if(isset($check['data']['merchant'])){
						$otp_status = \Onboarding::verifyOTP($mid,$otp);
						if($otp_status['status']){
							return view('onboarding/pennyVerify',$check);
						}else{
							$check['error'] = 1;
							$check['otp_status'] = $otp_status;
							return view('onboarding/otpVerify',$check);
						}
					}else{
						return redirect()->to('/');
					}
				}else{
					return redirect()->to('/');
				}
			}else{
				return redirect()->to('/');
			}
		}else{
			return redirect()->to('/');
		}
	}
	
	/**
	 * Verify PennyVerify 
	 */
	public function verifyPenny()
	{
		if($this->request->getMethod() == "post"){
			$mid = $this->request->getPost('mid');
			$amount = $this->request->getPost('amount');
			if(!empty($mid) && !empty($amount) && ($amount >= 1)){
				$check = \Onboarding::getMerchant($mid);
				if($check['status']){
					if(isset($check['data']['merchant'])){
						$verify_status = \Onboarding::verifyPenny($mid,$amount);
						if($verify_status['status']){
							//return redirect()->to('/onboarding/linkMerchant/'.$mid);
							return view('onboarding/payuLink',$check);
						}else{
							$check['error'] = 1;
							$check['verify_status'] = $verify_status;
							return view('onboarding/pennyVerify',$check);
						}
					}else{
						return redirect()->to('/');
					}
				}else{
					return redirect()->to('/');
				}
			}else{
				return redirect()->to('/');
			}
		}else{
			return redirect()->to('/');
		}
	}
	
	/**
	 * Link Existing Account 
	 */
	public function linkExistingAccount()
	{

		return view('onboarding/linkExistingAccount');
			
	}
	
	/**
	 * Link Existing Merchant 
	 */
	public function linkExistingMerchant()
	{
		if($this->request->getMethod() == "post"){
			$db = \Config\Database::connect();
			$mid = $this->request->getPost('mid');
			$merchant_key = $this->request->getPost('merchant_key');
			$merchant_salt = $this->request->getPost('merchant_salt');
			if(!empty($mid) && !empty($merchant_key) && !empty($merchant_salt)){
				$verifyStatus = \Onboarding::verifyLinkMerchant($mid,$merchant_key,$merchant_salt);
				if($verifyStatus['status']){
					$check = \Onboarding::getMerchant($mid);
					if($check['status']){
						
						$builder = $db->table('payu_onboarding_users');        
						$builder->select('*');       
						$builder->where('mid', $check['data']['merchant']['mid']);
						$builder->where('email', $check['data']['merchant']['email']);
						$builder->where('mobile', $check['data']['merchant']['registered_mobile']);
						$query = $builder->get();
						$result = $query->getResultArray();
						if (count($result) > 0) {
						}else{
							$user_data = [
								'mid' => $check['data']['merchant']['mid'],
								'email' => $check['data']['merchant']['email'],
								'mobile'    => $check['data']['merchant']['registered_mobile'],
								'params' => addslashes(json_encode($check))
								];
							$builderinsert = $db->table('payu_onboarding_users'); 
							$builderinsert->insert($user_data);
						}
					
						$merchantData = $check['data']['merchant'];
						$data = \Onboarding::getMerchantCreds($mid);
						if($data['status']){
							if(isset($data['data']['credentials'])){
								$creds = $data['data']['credentials'];
								$key = '';
								$salt = '';
								$auth_header = '';
								if(getenv('CI_ENVIRONMENT') == 'development'){
									if(isset($creds['test_key'])){
										$key = $creds['test_key'];
									}else if(isset($creds['prod_key'])){
										$key = $creds['prod_key'];
									}
									
									if(isset($creds['test_salt'])){
										$salt = $creds['test_salt'];
									}else if(isset($creds['prod_salt'])){
										$salt = $creds['prod_salt'];
									}
									
									if(isset($creds['test_authheader'])){
										$auth_header = $creds['test_authheader'];
									}else if(isset($creds['prod_authheader'])){
										$auth_header = $creds['prod_authheader'];
									}
								
								}else{
									if(isset($creds['prod_key'])){
										$key = $creds['prod_key'];
									}
									if(isset($creds['prod_salt'])){
										$salt = $creds['prod_salt'];
									}
									if(isset($creds['prod_authheader'])){
										$auth_header = $creds['prod_authheader'];
									}
								}
								if(!empty($key) && !empty($salt)){
									$session = session();
									$email_id = $session->get('email_id');
									$validation_id = $session->get('validation_id');
									$data = [
										'merchant_id' => $mid,
										'merchant_key' => $key,
										'merchant_salt' => $salt,
										'auth_header' => $auth_header
									];
									$builderupdate = $db->table('payu_token_validation'); 
									$builderupdate->where('email_id', $email_id);
									$builderupdate->where('validation_id', $validation_id);
									$builderupdate->update($data);
									if(($merchantData['penny_deposit_status'] == "Not Initiated") || ($merchantData['bank_verification_status'] == "Pending")){
										return redirect()->to('/onboarding/updateBankDetails/'.$mid);
									}else{
										return redirect()->to('/');
									}
								}else{
									return redirect()->to('/onboarding/linkExistingAccount?error=1');
								}
							}
						}else{
							return redirect()->to('/onboarding/linkExistingAccount?error=1');
						}
					}else{
						return redirect()->to('/onboarding/linkExistingAccount?error=1');
					}
				}else{
					return redirect()->to('/onboarding/linkExistingAccount?error=1');
				}
			}else{
				return redirect()->to('/onboarding/linkExistingAccount?error=1');
			}
		}else{
			return redirect()->to('/onboarding/linkExistingAccount?error=1');
		}
	}
	
	/**
	 * Store Information of BigCommerce
	 *
	 */
	public function bigcommerceStoreInfo($email_id,$validation_id){
		
		$data = array();
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
					$data = $res;
				}
			}
		}
		return $data;
	}
	
	/**
	 * getMerchant Api trigger
	 */
	public function getMerchantDetails()
	{
		$res = array();
		$res['status'] = false;
		$res['msg'] = 'Some error occurred';
		
		$clientDetails = \SettingsViews::getClientDetails();
		if(!empty($clientDetails)){
			$db = \Config\Database::connect();
			$check = \Onboarding::getMerchant($clientDetails['merchant_id']);
			if($check['status']){
				$merchantData = $check['data']['merchant'];
				//print_r(json_encode($merchantData));exit;
				if(($merchantData['penny_deposit_status'] == "Not Initiated") || ($merchantData['bank_verification_status'] == "Pending") || ($merchantData['is_service_agreement_accepted'] == false)){
					$res['status'] = true;
					$res['msg'] = 'Verification Pending';
				}
			}
		}
		
		return json_encode($res,true);exit;
	}
	
}
