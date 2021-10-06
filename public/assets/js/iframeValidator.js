$("body").on("submit","#payuPaymentIframeForm",function(e){
	e.preventDefault();
	var text = "Please wait...";
	var current_effect = "bounce";
	var key = $("body #247payukey").val();
	$("#payuPaymentIframeForm").waitMe({
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
								if(bstatus ==0 && sstatus == 0 && parseFloat(cartres.grandTotal)>0){';
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
										url: "https://payu.247commerce.co.uk/payupay/s2scheckout",
										dataType: "json",
										data:{"authKey":key,"cartId":cartId,"postData":btoa(JSON.stringify(postData))},
										success: function (res) {
											$("#payuPaymentIframeForm").waitMe("hide");
											if(res.status){
												window.location.href=res.url;
											}else{
												alert(res.msg);
											}
										},error: function(){
											$("#payuPaymentIframeForm").waitMe("hide");
										}
									});
								}else{
									alert("Please Select Billing Address and Shipping Address");
									$("#payuPaymentIframeForm").waitMe("hide");
								}
							},error: function(){
								$("#payuPaymentIframeForm").waitMe("hide");
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