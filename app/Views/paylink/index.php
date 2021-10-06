
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="Author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.83.1">
    <title>Create Payment Link</title>

    <!-- Bootstrap core CSS -->
    <link href="<?= getenv('app.ASSETSPATH') ?>css/bootstrap.min.css" rel="stylesheet">

    <!-- font-awesome css-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.1/css/all.css" integrity="sha384-O8whS3fhG2OnA5Kas0Y9l3cfpmYjapjI0E4theH4iuMD+pLhbf6JI0jIMfYcK3yZ" crossorigin="anonymous">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?= getenv('app.ASSETSPATH') ?>css/toaster/toaster.css">
	<link rel="stylesheet" href="<?= getenv('app.ASSETSPATH') ?>css/247payuloader.css">
  </head>
  <style>
    .accordion {
  background-color: #eee;
  color: #444;
  cursor: pointer;
  padding: 18px;
  width: 100%;
  text-align: left;
  border: none;
  outline: none;
  transition: 0.4s;
}

/* Add a background color to the button if it is clicked on (add the .active class with JS), and when you move the mouse over it (hover) 
.active, .accordion:hover {
  background-color: #ccc;
}*/

/* Style the accordion panel. Note: hidden by default */
.panel {
  padding: 0 18px;
  background-color: white;
  display: none;
  overflow: hidden;
}
button.accordion:after {
  content: '\002B';
    color: #ACC13E;
    font-weight: bold;
    float: right;
    margin-left: 5px;
    border: 1px solid #ACC13E;
    padding: 0px 9px;
    border-radius: 50px;
    font-size: 20px !important;
    /* margin-top: 10px; */
    line-height: 30px;
}
button.accordion.active:after {
    content: "\2212";
}
.form-control {
    color: #3F3F3F !important;
    background-color: rgba(143, 146, 161, 0.05) !important;
    border: 1px solid rgba(143, 146, 161, 0.05) !important;
    font-size:18px !important;
}
.form-control:focus {
    box-shadow:none !important;
     border:none !important;
}
.sub-head p{
    font-size:18px;
    color:#3F3F3F;
}
.form-check-input {
    border-radius: 2px !important;
}
.form-check-label {
    font-size:18px !important;
    color:#3F3F3F !important;
}
.round {
    border: 1px solid #3F3F3F;
    border-radius: 50px;
    padding: 2px 9px;
    margin-right: 15px;
    height: 50px;
    width: 50px;
}
.update {
    background: #A6C307;
    color: #FFF;
    border-radius: 25px;
    padding: 5px 30px;
}
.navbar-header{
    max-width: 1100px;
    width: 100%;
    padding-right: var(--bs-gutter-x,.75rem);
    padding-left: var(--bs-gutter-x,.75rem);
    margin-right: auto;
    margin-left: auto;
}
@media (min-width:1400px){
   .navbar-header{
    max-width: 1270px;
   }
}
.navbar-text .btn-text-green{
    border: 1px solid rgb(166,195,7);
    color: rgb(166,195,7);
    background-color:#fff;
    border-radius:20px;
    font-weight:500;
}
.navbar-text .btn-green{
    border:1px solid #A6C307;
    color:#fff;
    background-color:#A6C307;
    border-radius:20px;
}
.header-image img{
    width:70%;
    height:50%;
}
.btn.btn-lg{
    border-radius:20px;
}
@media (max-width:382px){
    .navbar-text .order-mobile{
        margin-top:15px;
    }
}
.main-content {
    padding: 50px 0;
    background: #F6F6F8;
}
.top-head p{
    color:#3F3F3F;
    font-size:22px;
}
.accordion{
    background: #FFFFFF;
    border: 1px solid #E9E9EB;
    font-size:22px;
    color:#3F3F3F;
}
@media (max-width:343px){
    .accordion{
        font-size:18px;
    }
    .top-head p {
       font-size: 18px;
    }
    button.accordion:after {
        margin-top:5px;
    }
    .round {
        padding: 0px 8px;
    }
}
  </style>
  <body>
    <div class="nav-section header-image p-3">
        <nav class="navbar navbar-light navbar-header p-lg-0">
        <a class="navbar-brand" href="#">
        <img src="<?= getenv('app.ASSETSPATH') ?>img/pay.png" alt="">
        </a>
        </nav>
    </div>
    <main class="main-content retail-merchant">
      <div class="container">
        <div class="row mx-1">
        <form class="form-horizontal" id="validateForm" action="<?= getenv('app.baseURL') ?>paylink/createPaymentLink" method="POST" >
		<div class="top-head mb-2 px-0">
            <p>Fill the details below to generate payment Invoice</p>
            </div>
			<button type="button" class="accordion mb-1"><span class="round" style="padding: 2px 10px;">1</span>Basic Details</button>
            <div class="panel mb-3 py-4">
                <div class="row">
                   <div class="col-md-3 mb-2">
                    <input class="form-control" type="number" id="adjustment" name="adjustment" placeholder="Adjustment Amount (Rs.)" required>
                   </div>
				   <div class="col-md-3 mb-2">
                    <input class="form-control" type="number" id="amount" name="amount" placeholder="Total Amount (Rs.)" required>
                   </div>
                   <div class="col-md-3 mb-2">
                    <select class="form-control" type="tel" id="viaEmail" name="viaEmail" placeholder="Invoice Via Email" required>
						<option value="">---Invoice Via Email---</option>
						<option value="true">Yes</option>
						<option value="false">No</option>
					</select>
                   </div>
				   <div class="col-md-3 mb-2">
                    <select class="form-control" type="tel" id="viaSms" name="viaSms" placeholder="Invoice Via Email" required>
						<option value="">---Invoice Via Sms---</option>
						<option value="true">Yes</option>
						<option value="false">No</option>
					</select>
                   </div>
                </div>
				<div class="row">
					<div class="col-md-3 mb-2">
						<input class="form-control" type="text" id="description" name="description" required placeholder="Invoice description" />
					</div>
					<div class="col-md-3 mb-2">
						<input class="form-control" type="date" id="expiryDate" name="expiryDate" required placeholder="Invoice expiry Date" />
					</div>
					<div class="col-md-3 mb-2">
						<input class="form-control" type="text" id="currency" name="currency" readonly required value="Curency(INR)" />
					</div>
					<div class="col-md-3 mb-2">
						<input class="form-control" type="number" id="maxPaymentsAllowed" name="maxPaymentsAllowed" required placeholder="Max Successfull Payments" />
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 mb-2">
						<select class="form-control" type="tel" id="isPartialPaymentAllowed" name="isPartialPaymentAllowed" placeholder="Invoice Via Email" required>
							<option value="true">---Partial Payment is allowed?---</option>
							<option value="true">Yes</option>
							<option value="false">No</option>
						</select>
					</div>
					<div class="col-md-6 mb-2">
						<input class="form-control" type="number" id="minAmountForCustomer" name="minAmountForCustomer" required placeholder="Minimum amount for the customer" />
					</div>
				</div>
            </div>
            <button type="button" class="accordion mb-1"><span class="round">2</span>Customer Details</button>
            <div class="panel mb-3 py-4">
				<div class="row">
					<div class="col-md-4 mb-2">
						<input class="form-control" type="text" id="name" name="name" placeholder="Customer Name" required>
					</div>
					<div class="col-md-4 mb-2">
						<input class="form-control" type="number" id="mobile" name="mobile" placeholder="Customer Mobile" required>
					</div>
					<div class="col-md-4 mb-2">
						<input class="form-control" type="email" id="email" name="email" placeholder="Customer Email" required>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4 mb-2">
					<input class="form-control" type="text" id="addressPin" name="addressPin" required placeholder="Pincode" aria-label="default input example">
				   </div>
				   <div class="col-md-4 mb-2">
					<input class="form-control" type="text" placeholder="City" id="addressCity" name="addressCity" required aria-label="default input example">
				   </div>
				   <div class="col-md-4 mb-2">
					<input class="form-control" type="text" id="addressState" name="addressState" required placeholder="State" aria-label="default input example">
				   </div>
				</div>
				<div class="row">
					<div class="col-md-4 mb-2">
					<input class="form-control" type="text" id="addressStreet1" name="addressStreet1" required placeholder="Strret 1 of User Addres" aria-label="default input example">
				   </div>
				   <div class="col-md-4 mb-2">
					<input class="form-control" type="text" placeholder="Street2 of the user's address" id="addressStreet2" name="addressStreet2" required aria-label="default input example">
				   </div>
				</div>
			</div>
        </div>
        <div class="row mt-3">
             <div class="col-md-12 text-end">
                <button type="submit" class="btn update" id="submitValidate" >Submit</button>
            </div>
        </div>
		</form>
      </div>
    </main>
    <script src="<?= getenv('app.ASSETSPATH') ?>js/jquery-min.js"></script>
    <script src="<?= getenv('app.ASSETSPATH') ?>js/bootstrap.min.js"></script>
    <script src="<?= getenv('app.ASSETSPATH') ?>js/bootstrap.bundle.min.js"></script>
    <script src="<?= getenv('app.ASSETSPATH') ?>js/jquery.validate.min.js"></script>
	<script src="<?= getenv('app.ASSETSPATH') ?>js/247payuloader.js"></script>
	<script src="<?= getenv('app.ASSETSPATH') ?>js/toaster/jquery.toaster.js"></script>
    <script>
		var acc = document.getElementsByClassName("accordion");
		var i;

		for (i = 0; i < acc.length; i++) {
			acc[i].addEventListener("click", function() {
			/* Toggle between adding and removing the "active" class,
			to highlight the button that controls the panel */
			this.classList.toggle("active");

			/* Toggle between hiding and showing the active panel */
			var panel = this.nextElementSibling;
			if (panel.style.display === "block") {
			  panel.style.display = "none";
			} else {
			  panel.style.display = "block";
			}
		  });
		}
    </script>
	<script type="text/javascript">
		var app_base_url = "<?= getenv('app.baseURL') ?>";
		$(document).ready(function() {
			$('body').on('click','#submitValidate',function(e){
				$(".accordion").addClass("active");
				$(".panel").show();
			});
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
					url: app_base_url + "paylink/createPaymentLink",
					//dataType: 'json',
					data:$('body #validateForm').serialize(),
					success: function (res) {
						var res = $.parseJSON(res);
						if(res.status){
							$("body").waitMe("hide");
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
