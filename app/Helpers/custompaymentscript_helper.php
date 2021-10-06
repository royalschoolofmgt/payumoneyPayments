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
			
			$buttonCode = '<button type="submit" class="button button--action button--large button--slab optimizedCheckout-buttonPrimary">PayU PAYMENTS</button>';
			
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
					
				$FormCode = '<h4>PayU Payments</h4><form id="payuPaymentForm" name="payuPayment" ><input type="hidden" id="247payukey" value="'.base64_encode(json_encode($tokenData)).'" >Card Number : <input type="text" id="ccnum" class="form-input optimizedCheckout-form-input" name="ccnum" value="" required /><div class="card-year-cvv"> <div class="year-cvv">Exp Mon : <select id="ccexpmon" class="form-select optimizedCheckout-form-select" name="ccexpmon" required>'.$months.'</select></div> <div class="year-cvv">Exp Year : <select id="ccexpyr" class="form-select optimizedCheckout-form-select" name="ccexpyr" required>'.$years.'</select> </div><div class="cvv">CVV : <input type="text" class="form-input optimizedCheckout-form-input" id="ccvv" name="ccvv" value="" required /> <svg class="adyen-checkout__card__cvc__hint adyen-checkout__card__cvc__hint--back" width="27" height="18" viewBox="0 0 27 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M27 4.00001V3.37501C27 2.4799 26.6444 1.62146 26.0115 0.988518C25.3786 0.355581 24.5201 0 23.625 0H3.375C2.47989 0 1.62145 0.355581 0.988514 0.988518C0.355579 1.62146 0 2.4799 0 3.37501V4.00001H27Z" fill="#E6E9EB"></path><path d="M0 6.99994V14.6666C0 15.5507 0.355579 16.3985 0.988514 17.0237C1.62145 17.6488 2.47989 18 3.375 18H23.625C24.5201 18 25.3786 17.6488 26.0115 17.0237C26.6444 16.3985 27 15.5507 27 14.6666V6.99994H0Z" fill="#E6E9EB"></path><rect y="4.00012" width="27" height="3.00001" fill="#687282"></rect><path d="M4 11C4 10.4477 4.44772 10 5 10H21C22.1046 10 23 10.8954 23 12C23 13.1046 22.1046 14 21 14H5C4.44771 14 4 13.5523 4 13V11Z" fill="white"></path><rect class="adyen-checkout__card__cvc__hint__location" x="16.5" y="9.5" width="7" height="5" rx="2.5" stroke="#D10244"></rect></svg> </div></div>Name : <input type="text" class="form-input mb-3 optimizedCheckout-form-input" id="ccname" name="ccname" value="" required />'.$buttonCode.'</form>';
			}
			
			$folderPath = getenv('app.SCRIPSPATH').$sellerdb;
			$filecontent = '$("head").append("<script src=\"'.getenv('app.ASSETSPATH').'js/247payuloader.js\" ></script>");';
			$filecontent .= '$("head").append("<link rel=\"stylesheet\" type=\"text/css\" href=\"'.getenv('app.ASSETSPATH').'css/247payuloader.css\" />");';
			$filecontent .= '$("head").append("<link rel=\"stylesheet\" type=\"text/css\" href=\"'.getenv('app.ASSETSPATH').'css/hostedfields.css\" />");';
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
					setTimeout(checkPayuPayBtnVisibility, 2000);
				});
				$("body").on("click", "#applyRedeemableButton", function() {
					setTimeout(checkPayuPayBtnVisibility, 2000);
				});
				$("body").on("click", ".cart-priceItem-link", function() {
					setTimeout(checkPayuPayBtnVisibility, 2000);
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
															if(ccnum != "" && ccname != "" && ccexpmon != "" && ccexpyr != "" && ccvv != ""){
																$.ajax({
																	type: "POST",
																	url:  "'.getenv('app.baseURL').'settings/validateCardDetails",
																	//dataType: "json",
																	data:{"authKey":key,"cardNumber":ccnum},
																	success: function (res) {
																		var res = $.parseJSON(res);
																		if(res.status){
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
																			});
																		}else{
																			$("#247payupayment").waitMe("hide");
																			$("body #247payuErr").text(res.msg);
																			$("body #ccnum").focus();
																		}
																	},error: function(){
																		$("#247payupayment").waitMe("hide");
																	}
																});
															}else{
																$("#247payupayment").waitMe("hide");
																if(ccnum == ""){
																	$("body #ccnum").focus();
																}else if(ccexpmon == ""){
																	$("body #ccexpmon").focus();
																}else if(ccexpyr == ""){
																	$("body #ccexpyr").focus();
																}else if(ccvv == ""){
																	$("body #ccvv").focus();
																}else if(ccname == ""){
																	$("body #ccname").focus();
																}
															}';
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