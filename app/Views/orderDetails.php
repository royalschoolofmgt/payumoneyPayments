<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="Author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.83.1">
    <title>Order Details</title>

    <!-- Bootstrap core CSS -->
    <link href="<?= getenv('app.ASSETSPATH') ?>css/bootstrap.min.css" rel="stylesheet">

    <!-- font-awesome css-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.1/css/all.css" integrity="sha384-O8whS3fhG2OnA5Kas0Y9l3cfpmYjapjI0E4theH4iuMD+pLhbf6JI0jIMfYcK3yZ" crossorigin="anonymous">

    <link href="<?= getenv('app.ASSETSPATH') ?>css/custom.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="<?= getenv('app.ASSETSPATH') ?>css/datatable/jquery.dataTables.min.css">
	<link rel="stylesheet" href="<?= getenv('app.ASSETSPATH') ?>css/toaster/toaster.css">
	<link rel="stylesheet" href="<?= getenv('app.ASSETSPATH') ?>css/247payuloader.css">
  </head>
  <body>
	<?php include('template/header.php');?>
    <main class="main-content">
      <div class="container">
        <div class="row mb-2">
            <div class="col-md-6 col-sm-6 col-5">
                <h5>Order Details &nbsp;<img src="<?= getenv('app.ASSETSPATH') ?>img/refresh.svg" id="refreshButton" style="height:3%;width:3%"></h5>
            </div>
            <div class="col-md-6 text-end col-sm-6 col-7 back-button">
                <a href="<?= getenv('app.baseURL') ?>home/dashboard" ><h5><i class="fas fa-arrow-left me-2"></i>Back to dashboard</h5></a>
            </div>
        </div>
        <div class="row top-bar order-srch border-top border-right">
          <div class="col-md-12 col-sm-8 col-8 top-search">
		  <div class="input-group ">
            <span class="input-group-text" id="basic-addon1"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control search-input rounded-end" id="exampleInputEmail1" placeholder="Search">
          </div>
		  </div>
        </div>
        <div class="table-responsive bg-white  border border-top-0">
            <table class="table" id="orderdetails_dashboard">
				<thead class="cf">
                  <tr class="header" id="table_columns">
                    <th><div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="flexCheckChecked">
                        </div>
                    </th>
                    <th>Payment Number</th>
                    <th>Payment type</th>
                    <th>Payment Status</th>
                    <th>Currency</th>
                    <th>Total</th>
                    <th>Amount Paid</th>
                    <th>Created Date</th>
                    <th>Actions</th>
                  </tr>
				</thead>
                <tbody id="table_data_rows">
									  
				</tbody>
            </table>
        </div>
      </div>
    </main>
    <script src="<?= getenv('app.ASSETSPATH') ?>js/jquery-min.js"></script>
    <script src="<?= getenv('app.ASSETSPATH') ?>js/bootstrap.min.js"></script>
    <script src="<?= getenv('app.ASSETSPATH') ?>js/bootstrap.bundle.min.js"></script>
	<script type="text/javascript" charset="utf8" src="<?= getenv('app.ASSETSPATH') ?>js/datatable/jquery.dataTables.min.js"></script>
	<script type="text/javascript" charset="utf8" src="<?= getenv('app.ASSETSPATH') ?>js/datatable/datatable-responsive.js"></script>
     <script src="<?= getenv('app.ASSETSPATH') ?>js/order-details.js?v=1.00"></script>
	 <script src="<?= getenv('app.ASSETSPATH') ?>js/247payuloader.js"></script>
	<script src="<?= getenv('app.ASSETSPATH') ?>js/toaster/jquery.toaster.js"></script>
    <script>
		var text = "Please wait...";
		var current_effect = "bounce";
		var app_base_url = "<?= getenv('app.baseURL') ?>";
		$(document).ready(function(){
			X247OrderDetails.main_data('home/orderdetailsprocessing','orderdetails_dashboard');
		});
		$('body').on('click','#refreshButton',function(){
			$("body").waitMe({
				effect: current_effect,
				text: text,
				bg: "rgba(255,255,255,0.7)",
				color: "#000",
				maxSize: "",
				waitTime: -1,
				source: "img/img.svg",
				textPos: "vertical",
				fontSize: "",
				onClose: function(el) {}
			});
			var url = app_base_url+'home/orderDetails';
			window.location.href = url;
		});
	</script>
  </body>
</html>
