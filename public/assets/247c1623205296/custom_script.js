$("head").append("<script src=\"http://localhost/payu/public/assets/js/247payuloader.js\" ></script>");$("head").append("<link rel=\"stylesheet\" type=\"text/css\" href=\"http://localhost/payu/public/assets/css/247payuloader.css\" />");var payment_option = "CFS";$(document).ready(function() {
				var stIntIdPayu = setInterval(function() {
					if($(".checkout-step--payment").length > 0) {
						if($("#247payupayment").length == 0){
							$(".checkout-step--payment .checkout-view-header").after('<div id="247payupayment" class="checkout-form" style="padding:1px;display:none;"><div id="247payuErr" style="color:red"></div><form id="payuPaymentForm" name="payuPayment" ><input type="hidden" id="247payukey" value="eyJlbWFpbF9pZCI6ImJpZ2lAMjQ3Y29tbWVyY2UuY28udWsiLCJrZXkiOiIxIn0=" >Card Number : <input type="text" id="ccnum" class="form-input optimizedCheckout-form-input" name="ccnum" value="" /> Exp Mon : <input type="text" id="ccexpmon" class="form-input optimizedCheckout-form-input" ame="ccexpmon" value="" /> Exp Year : <input type="text" class="form-input optimizedCheckout-form-input" id="ccexpyr" name="ccexpyr" value="" /> CVV : <input type="text" class="form-input optimizedCheckout-form-input" id="ccvv" name="ccvv" value="" /> Name : <input type="text" class="form-input optimizedCheckout-form-input" id="ccname" name="ccname" value="" /><button type="submit" class="button button--action button--large button--slab optimizedCheckout-buttonPrimary" style="background-color: #424242;border-color: #424242;color: #fff;">PayU PAYMENTS</button></form></div>');
							loadPayuStatus();
							clearInterval(stIntIdPayu);
							/**
								when user is logged in and billing/shipping 
								address set show custom payment button 
							*/
							checkPayuPayBtnVisibility();
						}
					}
				}, 1000);$("body").on("click","button[data-test='step-edit-button'], button[data-test='sign-out-link']",function(e){
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
						url: "http://localhost/payu/payupay/getPaymentStatus",
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
			function checkPayuPayBtnVisibility() {
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
					source: "http://localhost/payu/public/assets/images/img.svg",
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
								checkDownlProd = checkOnlyDownloadableProducts(cartCheck);
								if(cartId != ""){
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
											if(bstatus ==0 && sstatus == 0 && parseFloat(cartres.grandTotal)>0){
												if(payment_option == "CFO"){
													$.ajax({
														type: "POST",
														dataType: "json",
														crossDomain: true,
														url: "http://localhost/payu/payupay/authentication",
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
													});
												}else{
													var ccnum = $("body #ccnum").val();
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
														url: "http://localhost/payu/payupay/s2scheckout",
														dataType: "json",
														data:{"authKey":key,"cartId":cartId,"postData":btoa(JSON.stringify(postData))},
														success: function (res) {
															$("#247payupayment").waitMe("hide");
															if(res.status){
																window.location.href=res.url;
															}
														},error: function(){
															$("#247payupayment").waitMe("hide");
														}
													});
												}
											}else{
												alert("Please Select Billing Address and Shipping Address");
												$("#247payupayment").waitMe("hide");
											}
										},error: function(){
											$("#247payupayment").waitMe("hide");
										}
									});
								}
							}
						}
					},error: function(){
						$("#payuPaymentForm").waitMe("hide");
					}
				});
				
			});