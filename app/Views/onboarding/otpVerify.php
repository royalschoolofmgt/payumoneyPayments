<?php
$iserror = 0;
if(isset($error)){
	$iserror = $error;
}
$error_message = 'Invalid OTP. ';
//print_r($otp_status);exit;
if(isset($iserror)){
	if(isset($otp_status['data']['errors'])){
		if(isset($otp_status['data']['errors']['code'][0])){
			$error_message = $otp_status['data']['errors']['code'][0].'. ';
		}
		if(isset($otp_status['data']['messages']['code'])){
			$error_message .= $otp_status['data']['messages']['code'].'. ';
		}
		if(isset($otp_status['data']['messages']['user'])){
			$error_message .= $otp_status['data']['messages']['user'];
		}
	}
}
?>
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
            <div class="col-sm-6 mx-auto col-6 my-4">
                <img class="mx-sm-5" src="<?= getenv('app.ASSETSPATH') ?>img/pay.png">
            </div>
            <div class="login-form pg-30">
              <div class="text-center py-3">
                <h3>OTP Verify</h3>
              </div>
              <form class="form-horizontal" id="linkAccountForm" action="<?= getenv('app.baseURL') ?>onboarding/verifyOTP" method="POST" >
			  
				<input type="hidden" name="mid" value="<?= $data['merchant']['mid'] ?>">
				<div class="mb-3 ">
				    <label class="form-label">Enter Otp</label>
					<input type="number" class="form-control" name="otp" required placeholder="" autofocus />
				</div>
                <button type="submit" class="btn submit-but d-block w-100 btn-lg">Verify</button><br/>
                <button type="button" class="btn submit-but d-block w-100 btn-lg" id="resendOTP">Resend</button><br/>
				<a href="/" style="text-decoration:none;"><button type="button" class="btn submit-but d-block w-100 btn-lg">Skip</button></a>
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
		var mid = "<?= $data['merchant']['mid'] ?>";
		$(document).ready(function() {
			var text = "Please wait...";
			var current_effect = "bounce";
			$('body').on('click','#resendOTP',function(e){
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
					url: app_base_url + "onboarding/resendOTP",
					dataType: 'json',
					data:{mid:mid},
					success: function (res) {
						//var res = $.parseJSON(res);
						if(res.status){
							$("body").waitMe("hide");
							$.toaster({ priority : "success", title : "Success", message : 'OTP sent successfully' });
						}else{
							$("body").waitMe("hide");
							$.toaster({ priority : "danger", title : "Error", message : res.msg });
						}
					}
				});
			});
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
			var error = "<?= $iserror ?>";
			if(error == "1"){
				$.toaster({ priority : "danger", title : "Error", message : '<?= $error_message ?>' });
			}
		});
    </script>
  </body>
</html>
