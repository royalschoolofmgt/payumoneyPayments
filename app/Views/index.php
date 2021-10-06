<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.83.1">
    <title>Login</title>

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
                <h3>Getting Started</h3>
              </div>
              <form class="form-horizontal" id="validateForm" action="<?= getenv('app.baseURL') ?>settings/updatePaymetDetails" method="POST" >
                <!--<div class="mb-3 input-group input-height">
                  <input type="text" class="form-control" id="merchant_key" name="merchant_key" placeholder="Merchant Key">
                </div>
                <div class="mb-3 input-height" id="show_hide_password">
                  <input type="text" class="form-control" id="merchant_salt" name="merchant_salt" placeholder="Merchant Salt">
                </div>
				<div class="mb-3 input-height">
                  <input type="text" class="form-control" id="auth_header" name="auth_header" placeholder="Auth Header">
                </div>
                <div class="mb-3 input-group login-msg">
                  <small><b>How can I get my</b><a href="#"> Merchant Key, Merchant Salt & Auth Header ?</a></small>
                </div>
                <button type="submit" class="btn submit-but d-block w-100 btn-lg">Submit</button>-->
                <a href="<?= getenv('app.baseURL').'onboarding' ?>" style="text-decoration:none;"><button type="button" class="btn submit-but d-block w-100 btn-lg">Create New Merchant</button></a>
                <br/><a style="text-decoration:none;" href="<?= getenv('app.baseURL').'onboarding/linkExistingAccount' ?>" ><button type="button" class="btn submit-but d-block w-100 btn-lg">Link Existing Account</button></a>
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
      $(document).ready(function() {
		var text = "Please wait...";
			var current_effect = "bounce";
			$('body').on('submit','#validateForm',function(e){
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
      });
    </script>
  </body>
</html>
