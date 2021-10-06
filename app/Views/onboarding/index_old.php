<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.83.1">
    <title>Create Account</title>

    <!-- Bootstrap core CSS -->
    <link href="<?= getenv('app.ASSETSPATH') ?>css/bootstrap.min.css" rel="stylesheet">

    <!-- font-awesome css-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.1/css/all.css" integrity="sha384-O8whS3fhG2OnA5Kas0Y9l3cfpmYjapjI0E4theH4iuMD+pLhbf6JI0jIMfYcK3yZ" crossorigin="anonymous">

    <link href="<?= getenv('app.ASSETSPATH') ?>css/style.css" rel="stylesheet">
	<link rel="stylesheet" href="<?= getenv('app.ASSETSPATH') ?>css/toaster/toaster.css">
	<link rel="stylesheet" href="<?= getenv('app.ASSETSPATH') ?>css/247payuloader.css">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">

  </head>
  <body>
    <main class="main-content retail-merchant py-4">
      <div class="container">
        <div class="row">
          <div class="col-12 col-md-8 col-lg-6 offset-lg-3 offset-md-2">
            <div class="col-sm-6 mx-auto col-6 my-4">
                <img class="mx-sm-5" src="<?= getenv('app.ASSETSPATH') ?>img/pay.png">
            </div>
            <div class="login-form pg-30">
              <div class="text-center py-3">
                <h3>Getting Started</h3>
                <h5>Create New Account</h5>
              </div>
              <form class="form-horizontal" id="validateForm" action="<?= getenv('app.baseURL') ?>onboarding/createMerchant" method="POST" >
                <h2>Profile Information</h2>
				Business name : 
				<div class="mb-3 input-group input-height">
                  <input type="text" class="form-control" id="display_name" name="display_name" placeholder="Business name" required >
                </div>
				Email:
                <div class="mb-3 input-height" id="show_hide_password">
                  <input type="email" class="form-control" id="email" name="email" placeholder="Email" required >
                </div>
				Phone number
				<div class="mb-3 input-height">
                  <input type="tel" class="form-control" id="mobile" name="mobile" placeholder="Phone number" required >
                </div>
				<h2>Business Information</h2>
				<p>Business Details</p>
				Registered name:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="registered_name" name="registered_name" placeholder="Registered name" required >
                </div>
				Business entity:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="business_entity_type" name="business_entity_type" placeholder="Business entity" required >
                </div>
				Business category:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="business_category" name="business_category" placeholder="Business category" required >
                </div>
				Business subcategory:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="business_subcategory" name="business_subcategory" placeholder="Business subcategory" required >
                </div>
				Monthly expected sales:
				<div class="mb-3 input-height">
                  <input type="number" class="form-control" id="monthly_expected_volume" name="monthly_expected_volume" placeholder="In Rs" required >
                </div>
				GST number(Optional):
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="gst_number" name="gst_number" placeholder="GST number" >
                </div>
				<h2>Pan Details</h2>
				Pan name:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="pancard_name" name="pancard_name" placeholder="Pan name" required >
                </div>
				Pan number:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="pan" name="pan" placeholder="Pan number" required >
                </div>
				<h2>Registration Address</h2>
				Address line:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="addr_line1" name="addr_line1" placeholder="Address Line" required >
                </div>
				Pincode:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="pin" name="pin" placeholder="Pincode" required >
                </div>
				City:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="city" name="city" placeholder="City" required >
                </div>
				State:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="state" name="state" placeholder="State" required >
                </div>
				<h2>Operating Address</h2>
				<input type="checkbox" id="sameAddress" />
				Address line:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="operating_addr_line1" name="operating_addr_line1" placeholder="Address Line" required >
                </div>
				Pincode:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="operating_pin" name="operating_pin" placeholder="Pincode" required >
                </div>
				City:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="operating_city" name="operating_city" placeholder="City" required >
                </div>
				State:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="operating_state" name="operating_state" placeholder="State" required >
                </div>
				<h2>Bank Information</h2>
				Name:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" placeholder="Account holder name" required >
                </div>
				Account number:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="account_no" name="account_no" placeholder="Account number" required >
                </div>
				IFSC:
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" placeholder="IFSC code" required >
                </div>
                <button type="submit" class="btn submit-but d-block w-100 btn-lg">Submit</button>
              </form>
            </div>
          </div>        
        </div>
      </div>
    </main>
    <script src="<?= getenv('app.ASSETSPATH') ?>js/jquery-min.js"></script>
    <script src="<?= getenv('app.ASSETSPATH') ?>js/bootstrap.min.js"></script>
    <script src="<?= getenv('app.ASSETSPATH') ?>js/bootstrap.bundle.min.js"></script>
	<script src="<?= getenv('app.ASSETSPATH') ?>js/247payuloader.js"></script>
	<script src="<?= getenv('app.ASSETSPATH') ?>js/toaster/jquery.toaster.js"></script>
    <script type="text/javascript">
		var app_base_url = "<?= getenv('app.baseURL') ?>";
		$(document).ready(function() {
			var text = "Please wait...";
			var current_effect = "bounce";
			$('body').on('submit','#validateForm',function(e){
				e.preventDefault();
				$("body").waitMe({
					effect: current_effect,
					text: text,
					bg: "rgba(255,255,255,0.7)",
					color: "#000",
					maxSize: "",
					waitTime: -1,
					source: "images/img.svg",
					textPos: "vertical",
					fontSize: "",
					onClose: function(el) {}
				});
				$.ajax({
					type: 'POST',
					url: app_base_url + "onboarding/createMerchant",
					//dataType: 'json',
					data:$('body #validateForm').serialize(),
					success: function (res) {
						var res = $.parseJSON(res);
						if(res.status){
							//$("body").waitMe("hide");
							$.toaster({ priority : "success", title : "Success", message : "Merchant Created Successfully, Please wait." });
							window.location.href = app_base_url+'onboarding/linkMerchant/'+res.mid;
						}else{
							$("body").waitMe("hide");
							$.toaster({ priority : "danger", title : "Error", message : res.msg });
						}
					}
				});
			});
		});
    </script>
  </body>
</html>
