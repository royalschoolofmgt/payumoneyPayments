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
 * Class Paylink
 *
 * Represents a PayU Paylink process
 */
class Paylink extends BaseController
{
	protected $access_token;
	public function __construct() {
		helper('paylink');
		$this->set_access_token();
	}
	
	/**
	 * Setting Access Token
	 */
	public function set_access_token()
	{ 
		if(empty($this->access_token) == true){	
			$session_id = \Paylink::get_access_token();
			if(empty($session_id) == true){
				$session_id = \Paylink::set_access_token();
			}
			if(!empty($session_id)){
				$this->access_token = $session_id;
			}
		} 
              
	}

	/**
	 * Index - default home page in PayU for Paylink
	 */
	public function index()
	{
		return view('paylink/index');
	}
	
	/**
	 * createMerchant Api trigger for create Merchant V3
	 */
	public function createPaymentLink()
	{
		$res = array();
		$res['status'] = false;
		$res['msg'] = 'Some error occurred';
		if($this->request->getMethod() == "post"){
			$postData = $this->request->getPost();
			$data = \Paylink::createPaymentLink($postData);
			if($data['status']){
				$res['status'] = true;
			}else{
				$res['status'] = false;
				$res['msg'] = $data['msg'];
			}
		}
		return json_encode($res,true);exit;
	}
}
