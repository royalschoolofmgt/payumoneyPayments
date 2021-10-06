<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.83.1">
    <title>Link Existing Account</title>

    <!-- Bootstrap core CSS -->
    <link href="<?= getenv('app.ASSETSPATH') ?>css/bootstrap.min.css" rel="stylesheet">

    <!-- font-awesome css-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.1/css/all.css" integrity="sha384-O8whS3fhG2OnA5Kas0Y9l3cfpmYjapjI0E4theH4iuMD+pLhbf6JI0jIMfYcK3yZ" crossorigin="anonymous">

    <link href="<?= getenv('app.ASSETSPATH') ?>css/style.css" rel="stylesheet">
	<link href="<?= getenv('app.ASSETSPATH') ?>css/custom.css" rel="stylesheet">
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
            <div class="col-sm-6 mx-auto col-6 my-4 text-center">
                <img width="120" src="<?= getenv('app.ASSETSPATH') ?>img/pay.png">
            </div>
            <div class="login-form pg-30">
              <div class="text-center py-3">
                <h3>Link Existing Account</h3>
              </div>
              <form class="form-horizontal" id="linkAccountForm" action="<?= getenv('app.baseURL') ?>onboarding/linkExistingMerchant" method="POST" >
				<div class="mb-3 input-height">
					<label class="form-label">Enter Merchant Key</label>
					<input type="text" name="merchant_key" class="form-control inputValidate" value="" required placeholder="Merchant Key" >
                </div>
				<div class="mb-3 input-height">
					<label class="form-label">Enter Merchant Salt</label>
					<input type="text" name="merchant_salt" class="form-control" value="" required placeholder="Merchant Salt" >
                </div>
				<div class="mb-3 input-height">
				    <label class="form-label">Enter Merchant ID(mid)</label>
					<input type="text" name="mid" class="form-control inputValidate" value="" required placeholder="Merchant ID" >
                </div>
				<center>
				<div class="mb-3 login-msg">
                  <small><b>How can I </b><a href="https://drive.google.com/file/d/1jm8ivibvKk61XkpYKSE5zVBK8WzNDEWy/view?usp=sharing" target="_blank" style="float:center;color:#3aae97;">get mid,merchant key & salt</a></small>
                </div></center>
				<button type="submit" class="btn submit-but d-block w-100 btn-lg">Link</button><br/>
				<a href="/" style="text-decoration: none;"><button type="button" class="btn submit-but d-block w-100 btn-lg">Home</button></a>
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
			$('body').on('submit','#linkAccountForm',function(e){
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
			});
			$("body ").on('keyup','.inputValidate',function (e) {
				var keyCode = e.keyCode || e.which;
				//Regex for Valid Characters i.e. Alphabets and Numbers.
				//var regex = /^[A-Za-z0-9-]+$/;
				var str = $(this).val();
				str = str.replace(/[^a-z0-9\s]/gi, '');
				$(this).val(str);
			});
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
			var error = getUrlParameter('error');
			if(error){
				$.toaster({ priority : "danger", title : "Error", message : "Please provide valid details" });
			}
		});
    </script>
  </body>
</html>
