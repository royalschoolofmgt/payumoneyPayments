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
 * Class RefundOrder
 *
 * Represents a PayU Refunds
 */
class RefundOrder extends BaseController
{
	/**
	 * Index - default page
	 *
	 */
	public function index($authKey)
	{
		helper('settingsviews');
		$clientDetails = \SettingsViews::getClientDetails();
		$data = array();
		if(!empty($clientDetails) && !empty($authKey)){
			$data['clientDetails'] = $clientDetails;
			$invoice_id = json_decode(base64_decode($authKey));
			if(!empty($invoice_id)){
				$db = \Config\Database::connect();
				$builder = $db->table('order_payment_details opd');
				$builder->select('*');
				$builder->join('order_details od', 'opd.order_id = od.invoice_id','left');
				$builder->where('opd.order_id', $invoice_id);
				$query = $builder->get();
				$result = $query->getResultArray();
				$data['orderDetails'] = array();
				if(count($result)>0){
					$data['orderDetails'] = $result[0];
				}
				
				$builder = $db->table('order_refund');
				$builder->select('*');
				$builder->where('invoice_id', $invoice_id);
				$query = $builder->get();
				$ref_result = $query->getResultArray();
				$data['ref_result'] = array();
				if(count($ref_result) > 0){
					$data['ref_result'] = $ref_result;
				}
				
				return view('refundOrder',$data);
			}else{
				return redirect()->to('/');
			}
		}else{
			return redirect()->to('/');
		}
	}
	
	/**
	 * RefundOrder - page
	 *
	 */
	public function proceedRefund()
	{
		if($this->request->getMethod() == "post"){
			$invoice_id = $this->request->getVar('invoice_id');
			$refund_amount = $this->request->getVar('refund_amount');
			if(!empty($invoice_id) && ($refund_amount > 0)){
				$db = \Config\Database::connect();
				$builder = $db->table('order_payment_details');
				$builder->select('*');
				$builder->where('order_id', $invoice_id);
				$query = $builder->get();
				$result_refund = $query->getResultArray();
				if(isset($result_refund[0]) && ($result_refund[0]['status'] == "CONFIRMED")) {
					$payment_details = json_decode(str_replace("\\","",$result_refund[0]['api_response']),true);
					if(isset($payment_details['transaction_details'][$invoice_id])){
						$transaction_details = $payment_details['transaction_details'][$invoice_id];
						//print_r($transaction_details);exit;
						if(isset($transaction_details['mihpayid'])){
							$status = $this->refunds($transaction_details,$refund_amount);
							//print_r($status);exit;
							if($status){
								return redirect()->to('/refundOrder/index/'.base64_encode(json_encode($transaction_details['txnid'])).'?error=0');
							}else{
								return redirect()->to('/refundOrder/index/'.base64_encode(json_encode($transaction_details['txnid'])).'?error=1');
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
		}else{
			return redirect()->to('/');
		}
	}
	
	public function refunds($request,$amount){
		
		$paymentURL = getenv('payu.PAYU_TEST_URL');
		if(getenv('CI_ENVIRONMENT') == "production"){
			$paymentURL = getenv('payu.PAYU_PROD_URL');
		}
		
		$db = \Config\Database::connect();
		$status = false;
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
				$ref_reference = 'REF'.$request['txnid'].time();
				/* paymet Request */
				$posted = array();
				$posted['key'] = $clientDetails['merchant_key'];
				$posted['command'] = "cancel_refund_transaction";
				$posted['var1'] = $request['mihpayid'];
				$posted['var2'] = $ref_reference;
				$posted['var3'] = $amount;
				$posted['var4'] = '';
				$posted['var5'] = '';
				$posted['var6'] = '';
				$posted['var7'] = '';
				$posted['var8'] = '';
				$posted['var9'] = '';
				
				$hash_string = $clientDetails['merchant_key'].'|cancel_refund_transaction|'.$request['mihpayid'].'|'.$clientDetails['merchant_salt'];//appending salt
				$posted['hash'] = strtolower(hash('sha512', $hash_string));
				
				$req = '';
				foreach($posted as $k=>$v){
					$req .= $k.'='.$v.'&';
				}
				//echo $posted['hash'];exit;
				try{
					
					$data = [
						'email_id' => $clientDetails['email_id'],
						'invoice_id' => $request['txnid'],
						'ref_reference' => $ref_reference,
						'refund_status'    => "PENDING",
						'refund_amount'    => $amount,
						'api_request'    => addslashes(json_encode($posted,true)),
						'token_validation_id'    => $clientDetails['validation_id'],
					];
					$builderinsert = $db->table('order_refund'); 
					$builderinsert->insert($data);
					$r_id = $db->insertID();
					
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
						
						$db = \Config\Database::connect();
						$data = [
							'email_id' => $clientDetails['email_id'],
							'type' => "PayU",
							'action'    => "Refund",
							'api_url'    => $paymentURL.'/merchant/postservice?form=2',
							'api_request' => addslashes($req),
							'api_response' => addslashes($body),
							'token_validation_id' => $clientDetails['validation_id'],
						];
						$builderinsert = $db->table('api_log'); 
						$builderinsert->insert($data);
						
						//print_r($resp);exit;
						if(isset($resp['status']) && ($resp['status'] == "1")){
							$status = true;
							$data = [
								'refund_status' => 'CONFIRMED',
								'api_response' => addslashes($body)
							];
							
							$r_data = [
								'settlement_status' => 'REFUND'
							];
							
							$builderupdate = $db->table('order_payment_details');
							$builderupdate->where('order_id', $request['txnid']); 
							$builderupdate->update($r_data);
							
						}else{
							$data = [
								'refund_status' => 'Failed',
								'api_response' => addslashes($body),
							];
						}
						$builderupdate = $db->table('order_refund');
						$builderupdate->where('r_id', $r_id); 
						$builderupdate->update($data);
						if(isset($resp['status']) && ($resp['status'] == "1")){
							$statusResponse = $this->updateOrderStatus($clientDetails['email_id'],$r_id,$request['txnid'],$clientDetails['validation_id']);
						}
					}
				}catch(\Exception $e){
					$db = \Config\Database::connect();
					$data = [
						'email_id' => $clientDetails['email_id'],
						'type' => "PayU",
						'action'    => "Refund",
						'api_url'    => $paymentURL.'/merchant/postservice?form=2',
						'api_request' => addslashes($req),
						'api_response' => addslashes($e->getMessage()),
						'token_validation_id' => $clientDetails['validation_id'],
					];
					$builderinsert = $db->table('api_log'); 
					$builderinsert->insert($data);
				}
			}
		}
		return $status;
	}
	public function testing1(){
		$this->updateOrderStatus("payu.247commerce@gmail.com","23","PayU-1625655139","1");
	}
	public function updateOrderStatus($email_id,$rder_refund_id,$invoice_id,$token_validation_id) {
		
		helper('settingsviews');
		helper('bigcommerceorder');
		$clientDetails = \BigCommerceOrder::getClientDetails($email_id,$token_validation_id);
		if(!empty($clientDetails)){
			$db = \Config\Database::connect();
			
			$order_details = array();
			$builder = $db->table('order_details');        
			$builder->select('*');       
			$builder->where('invoice_id', $invoice_id);
			$query = $builder->get();
			$result_od = $query->getResultArray();
			if (count($result_od) > 0) {
				$order_details = $result_od[0];
			}
			
			$order_refund_details = array();
			$builder = $db->table('order_refund');        
			$builder->select('*');       
			$builder->where('r_id', $rder_refund_id);
			$query = $builder->get();
			$result_or = $query->getResultArray();
			if (count($result_or) > 0) {
				$order_refund_details = $result_or[0];
			}
			if(isset($order_details['order_id']) && !empty($order_details['order_id']) && isset($order_refund_details['refund_status']) && ($order_refund_details['refund_status'] == "CONFIRMED")){
				$url_u = getenv('bigcommerceapp.STORE_URL').$clientDetails['store_hash'].'/v2/orders/'.$order_details['order_id'];
				$staff_comments = "Payment Number : ".$invoice_id.",Status : Refunded,Refunded Date : ".$order_refund_details['created_date'].",Refunded Amount : ".$order_details['currecy']." ".$order_refund_details['refund_amount'];

				$request_u = array("status_id"=>4,"staff_notes"=>$staff_comments);
				$request_u = json_encode($request_u,true);
				try{
					$client = \Config\Services::curlrequest();
					$response = $client->setBody($request_u)->request('put', $url_u, [
							'headers' => [
									'X-Auth-Token' => $clientDetails['acess_token'],
									'store_hash' => $clientDetails['store_hash'],
									'Accept' => 'application/json',
									'Content-Type' => 'application/json'
							]
					]);
					
					if (strpos($response->getHeader('content-type'), 'application/json') != false){
						$res_u = $response->getBody();
					
						$db = \Config\Database::connect();
						$data = [
							'email_id' => $email_id,
							'type' => "BigCommerce",
							'action'    => "Update Order",
							'api_url'    => addslashes($url_u),
							'api_request' => addslashes($request_u),
							'api_response' => addslashes($res_u),
							'token_validation_id' => $token_validation_id,
						];
						$builderinsert = $db->table('api_log'); 
						$builderinsert->insert($data);
					}
				}catch(\Exception $e){
					log_message('info', 'exception:'.$e->getMessage());
					$db = \Config\Database::connect();
					$data = [
						'email_id' => $email_id,
						'type' => "BigCommerce",
						'action'    => "Update Order",
						'api_url'    => addslashes($url_u),
						'api_request' => addslashes($request_u),
						'api_response' => addslashes($e->getMessage()),
						'token_validation_id' => $token_validation_id,
					];
					$builderinsert = $db->table('api_log'); 
					$builderinsert->insert($data);
				}
			}
		}
	}
}
