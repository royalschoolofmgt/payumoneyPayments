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
 * Class CustomPaymentScript
 *
 * Represents a helper class to create Payment Script in BigCommerce 
 */
class CustomPaymentScript
{
	/* creating folder Based on Seller */
	public static function createPaymentScript($sellerdb,$email_id,$validation_id,$payment_option='CFO'){
		$tokenData = array("email_id"=>$email_id,"key"=>$validation_id);
		if(!empty($sellerdb)){
			
			$url = getenv('app.baseURL').'payupay/s2scheckout';
			
			$enable = 0;
			
			$buttonCode = '<button type="submit" class="button button--action button--large button--slab optimizedCheckout-buttonPrimary" style="background-color: #424242;border-color: #424242;color: #fff;">PayU PAYMENTS</button>';
			
			$db = \Config\Database::connect();
			$builder = $db->table('custom_payupay_button');  
			$builder->select('*');       
			$builder->where('email_id', $email_id);
			$builder->where('token_validation_id', $validation_id);
			$query = $builder->get();
			$result_c = $query->getResultArray();
			if (count($result_c) > 0) {
				$result_c = $result_c[0];
				
				if(isset($result_c['is_enabled']) && $result_c['is_enabled'] == 1){
					$enable = 1;
				}
				
				if($enable == 1){
					if(!empty($result_c['html_code'])){
						$buttonCode = html_entity_decode($result_c['html_code']);
					}
				}
			}
			
			if($payment_option == "CFO"){
				$FormCode = '<form id="payuPaymentForm" name="payuPayment"><input type="hidden" id="247payukey" value="'.base64_encode(json_encode($tokenData)).'" >'.$buttonCode.'</form>';
			}else{
				$months = '';
				for($i=1;$i<=12;$i++){
					$val = $i;
					if($i<10){
						$val = '0'.$i;
					}
					$months .= '<option>'.$val.'</option>';
				}
				
				$year = date("Y");
				$years = '';
				for($j=$year;$j<=($year+15);$j++){
					$years .= '<option>'.$j.'</option>';
				}
					
				$FormCode = '<form id="payuPaymentForm" name="payuPayment" ><input type="hidden" id="247payukey" value="'.base64_encode(json_encode($tokenData)).'" >Card Number : <input type="text" id="ccnum" class="form-input optimizedCheckout-form-input" name="ccnum" value="" required /> Exp Mon : <select id="ccexpmon" class="form-input optimizedCheckout-form-input" name="ccexpmon" required>'.$months.'</select> Exp Year : <select id="ccexpyr" class="form-input optimizedCheckout-form-input" name="ccexpyr" required>'.$years.'</select> CVV : <input type="text" class="form-input optimizedCheckout-form-input" id="ccvv" name="ccvv" value="" required /> Name : <input type="text" class="form-input optimizedCheckout-form-input" id="ccname" name="ccname" value="" required />'.$buttonCode.'</form>';
			}
			
			$folderPath = getenv('app.SCRIPSPATH').$sellerdb;
			$filecontent = '$("head").append("<script src=\"'.getenv('app.ASSETSPATH').'js/247payuloader.js\" ></script>");';
			$filecontent .= '$("head").append("<link rel=\"stylesheet\" type=\"text/css\" href=\"'.getenv('app.ASSETSPATH').'css/247payuloader.css\" />");';
			if($enable == 1){
				$id = $result_c['container_id'];
				$css_prop = $result_c['css_prop'];
				if(!empty($id)){
					$filecontent .= 'var payment_option = "'.$payment_option.'";$(document).ready(function() {
						var stIntIdPayu = setInterval(function() {
							if($(".checkout-step--payment").length > 0) {
								if($("#247payupayment").length == 0){
									$("'.$id.'").after(\'<div id="247payupayment" class="checkout-form" style="padding:1px;display:none;"><div id="247payuErr" style="color:red"></div>'.$FormCode.'</div>\');
									loadPayuStatus();
									clearInterval(stIntIdPayu);
									/**
										when user is logged in and billing/shipping 
										address set show custom payment button 
									*/
									checkPayuPayBtnVisibility();
								}
							}
						}, 1000);';
				}else{
					$filecontent .= 'var payment_option = "'.$payment_option.'";$(document).ready(function() {
						var stIntIdPayu = setInterval(function() {
							if($(".checkout-step--payment").length > 0) {
								if($("#247payupayment").length == 0){
									$(".checkout-step--payment .checkout-view-header").after(\'<div id="247payupayment" class="checkout-form" style="padding:1px;display:none;"><div id="247payuErr" style="color:red"></div>'.$FormCode.'</div>\');
									loadPayuStatus();
									clearInterval(stIntIdPayu);
									/**
										when user is logged in and billing/shipping 
										address set show custom payment button 
									*/
									checkPayuPayBtnVisibility();
								}
							}
						}, 1000);';	
				}
				if(!empty($css_prop)){
					$filecontent .= '$("body").append("<style>'.preg_replace("/[\r\n]*/","",$css_prop).'</style>");';
				}
			}else{
				$filecontent .= 'var payment_option = "'.$payment_option.'";$(document).ready(function() {
						var stIntIdPayu = setInterval(function() {
							if($(".checkout-step--payment").length > 0) {
								if($("#247payupayment").length == 0){
									$(".checkout-step--payment .checkout-view-header").after(\'<div id="247payupayment" class="checkout-form" style="padding:1px;display:none;"><div id="247payuErr" style="color:red"></div>'.$FormCode.'</div>\');
									loadPayuStatus();
									clearInterval(stIntIdPayu);
									/**
										when user is logged in and billing/shipping 
										address set show custom payment button 
									*/
									checkPayuPayBtnVisibility();
								}
							}
						}, 1000);';	
			}
			$filecontent .= '$("body").on("click","button[data-test=\'step-edit-button\'], button[data-test=\'sign-out-link\']",function(e){
					//hide payu payment button
					$("#247payupayment").hide();
				});

				$("body").on("click", "button#checkout-customer-continue, button#checkout-shipping-continue, button#checkout-billing-continue", function() {
					checkPayuPayBtnVisibility();
				});
			});
			function payubillingAddressValdation(billingAddress){
				var errorCount = 0;
				if(typeof(billingAddress.firstName) != "undefined" && billingAddress.firstName !== null && billingAddress.firstName !== "") {
					
				}else{
					errorCount++;
				}
				if(typeof(billingAddress.lastName) != "undefined" && billingAddress.lastName !== null && billingAddress.lastName !== "") {
					
				}else{
					errorCount++;
				}
				if(typeof(billingAddress.address1) != "undefined" && billingAddress.address1 !== null && billingAddress.address1 !== "") {
					
				}else{
					errorCount++;
				}
				if(typeof(billingAddress.email) != "undefined" && billingAddress.email !== null && billingAddress.email !== "") {
					
				}else{
					errorCount++;
				}
				if(typeof(billingAddress.city) != "undefined" && billingAddress.city !== null && billingAddress.city !== "") {
					
				}else{
					errorCount++;
				}
				if(typeof(billingAddress.postalCode) != "undefined" && billingAddress.postalCode !== null && billingAddress.postalCode !== "") {
					
				}else{
					errorCount++;
				}
				if(typeof(billingAddress.country) != "undefined" && billingAddress.country !== null && billingAddress.country !== "") {
					
				}else{
					errorCount++;
				}
				
				return errorCount;
			}

			function payushippingAddressValdation(shippingAddress){
				var errorCount = 0;
				if(shippingAddress.length > 0){
					if(typeof(shippingAddress[0].shippingAddress) != "undefined" && shippingAddress[0].shippingAddress !== null && shippingAddress[0].shippingAddress !== "") {
						shippingAddress = shippingAddress[0].shippingAddress;
						if(typeof(shippingAddress.firstName) != "undefined" && shippingAddress.firstName !== null && shippingAddress.firstName !== "") {
							
						}else{
							errorCount++;
						}
						if(typeof(shippingAddress.lastName) != "undefined" && shippingAddress.lastName !== null && shippingAddress.lastName !== "") {
							
						}else{
							errorCount++;
						}
						if(typeof(shippingAddress.address1) != "undefined" && shippingAddress.address1 !== null && shippingAddress.address1 !== "") {
							
						}else{
							errorCount++;
						}
						if(typeof(shippingAddress.city) != "undefined" && shippingAddress.city !== null && shippingAddress.city !== "") {
							
						}else{
							errorCount++;
						}
						if(typeof(shippingAddress.postalCode) != "undefined" && shippingAddress.postalCode !== null && shippingAddress.postalCode !== "") {
							
						}else{
							errorCount++;
						}
						if(typeof(shippingAddress.country) != "undefined" && shippingAddress.country !== null && shippingAddress.country !== "") {
							
						}else{
							errorCount++;
						}
					}
				}else{
					errorCount++;
				}
				return errorCount;
			}
			function checkOnlyDownloadableProducts(cartData){
				var status = false;
				if(cartData != ""){
					if(cartData.physicalItems.length > 0 || cartData.customItems.length > 0){
						status = true;
					}
					else{
						if(cartData.digitalItems.length > 0){
							status = false;
						}
					}
				}
				return status;
			}
			var getUrlParameter = function getUrlParameter(sParam) {
				var sPageURL = window.location.search.substring(1),
					sURLVariables = sPageURL.split("&"),
					sParameterName,
					i;

				for (i = 0; i < sURLVariables.length; i++) {
					sParameterName = sURLVariables[i].split("=");

					if (sParameterName[0] === sParam) {
						return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
					}
				}
				return false;
			};
			function loadPayuStatus(){
				var key = getUrlParameter("payuinv");
				if(key != "undefined" && key != ""){
					$.ajax({
						type: "POST",
						dataType: "json",
						crossDomain: true,
						url: "'.getenv('app.baseURL').'payupay/getPaymentStatus",
						dataType: "json",
						data:{"authKey":key},
						success: function (res) {
							if(res.status){
								$("body #247payuErr").text(res.msg);
							}
						}
					});
				}
			}
			';
			$filecontent .= 'function checkPayuPayBtnVisibility() {
				var checkDownlProd = false;
				var key = $("body #247payukey").val();
				$.ajax({
					type: "GET",
					dataType: "json",
					url: "/api/storefront/cart",
					success: function (res) {
						if(res.length > 0){
							if(res[0]["id"] != undefined){
								var cartId = res[0]["id"];
								var cartCheck = res[0]["lineItems"];
								checkDownlProd = checkOnlyDownloadableProducts(cartCheck);
								if(cartId != ""){
									$.ajax({
										type: "GET",
										dataType: "json",
										url: "/api/storefront/checkouts/"+cartId,
										success: function (cartres) {
											var cartData = window.btoa(unescape(encodeURIComponent(JSON.stringify(cartres))));
											var billingAddress = "";
											var consignments = "";
											var bstatus = 0;
											var sstatus = 0;
											if(typeof(cartres.billingAddress) != "undefined" && cartres.billingAddress !== null) {
												billingAddress = cartres.billingAddress;
												bstatus = payubillingAddressValdation(billingAddress);
											}
											if(checkDownlProd){
												if(typeof(cartres.consignments) != "undefined" && cartres.consignments !== null) {
													consignments = cartres.consignments;
													sstatus = payushippingAddressValdation(consignments);
												}
											}

											if(bstatus ==0 && sstatus == 0) {

												//hide payu payment button
												$("#247payupayment").show();
											}


										}
									});
								}
							}
						}
					}

				});
			}
			$("body").on("submit","#payuPaymentForm",function(e){
				e.preventDefault();
				var text = "Please wait...";
				var current_effect = "bounce";
				var key = $("body #247payukey").val();
				$("#247payupayment").waitMe({
					effect: current_effect,
					text: text,
					bg: "rgba(255,255,255,0.7)",
					color: "#000",
					maxSize: "",
					waitTime: -1,
					source: "'.getenv('app.ASSETSPATH').'images/img.svg",
					textPos: "vertical",
					fontSize: "",
					onClose: function(el) {}
				});
				var checkDownlProd = false;
				$.ajax({
					type: "GET",
					dataType: "json",
					url: "/api/storefront/cart",
					success: function (res) {
						if(res.length > 0){
							if(res[0]["id"] != undefined){
								var cartId = res[0]["id"];
								var cartCheck = res[0]["lineItems"];
								var currency = res[0]["currency"]["code"];
								checkDownlProd = checkOnlyDownloadableProducts(cartCheck);
								if(cartId != "" && (currency == "INR")){
									$.ajax({
										type: "GET",
										dataType: "json",
										url: "/api/storefront/checkouts/"+cartId,
										success: function (cartres) {
											var billingAddress = "";
											var consignments = "";
											var bstatus = 0;
											var sstatus = 0;
											if(typeof(cartres.billingAddress) != "undefined" && cartres.billingAddress !== null) {
												billingAddress = cartres.billingAddress;
												bstatus = payubillingAddressValdation(billingAddress);
											}
											if(checkDownlProd){
												if(typeof(cartres.consignments) != "undefined" && cartres.consignments !== null) {
													consignments = cartres.consignments;
													sstatus = payushippingAddressValdation(consignments);
												}
											}
											if(bstatus ==0 && sstatus == 0 && parseFloat(cartres.grandTotal)>0){';
												if($payment_option == "CFO"){
													$filecontent .= '$.ajax({
														type: "POST",
														dataType: "json",
														crossDomain: true,
														url: "'.getenv('app.baseURL').'payupay/authentication",
														dataType: "json",
														data:{"authKey":key,"cartId":cartId},
														success: function (res) {
															//$("#247payupayment").waitMe("hide");
															if(res.status){
																window.location.href=res.url;
															}
														},error: function(){
															$("#247payupayment").waitMe("hide");
														}
													});';
												}else{
													$filecontent .= 'var ccnum = $("body #ccnum").val();
													var ccname = $("body #ccname").val();
													var ccexpmon = $("body #ccexpmon").val();
													var ccexpyr = $("body #ccexpyr").val();
													var ccvv = $("body #ccvv").val();
													var postData = {};
													postData["ccnum"] = ccnum;
													postData["ccname"] = ccname;
													postData["ccexpmon"] = ccexpmon;
													postData["ccexpyr"] = ccexpyr;
													postData["ccvv"] = ccvv;
													$.ajax({
														type: "POST",
														dataType: "json",
														crossDomain: true,
														url: "'.getenv('app.baseURL').'payupay/s2scheckout",
														dataType: "json",
														data:{"authKey":key,"cartId":cartId,"postData":btoa(JSON.stringify(postData))},
														success: function (res) {
															if(res.status){
																window.location.href=res.url;
															}else{
																$("#247payupayment").waitMe("hide");
																$("body #247payuErr").text(res.msg);
															}
														},error: function(){
															$("#247payupayment").waitMe("hide");
														}
													});';
												}
											$filecontent .= '}else{
												alert("Please Select Billing Address and Shipping Address");
												$("#247payupayment").waitMe("hide");
											}
										},error: function(){
											$("#247payupayment").waitMe("hide");
										}
									});
								}else{
									alert("PayU Payments accepts only INR currency");
								}
							}
						}
					},error: function(){
						$("#payuPaymentForm").waitMe("hide");
					}
				});
				
			});';
			$filename = 'custom_script.js';
			helper('filestream');
			$res = \FileStream::saveFile($filename,$filecontent,$folderPath);
		}
	}
}