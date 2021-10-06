<div class="nav-section header-image p-3">
    <nav class="navbar navbar-light navbar-header p-lg-0">
        <a class="navbar-brand" href="#">
            <!--img src="<?= getenv('app.ASSETSPATH') ?>img/pay.png" alt=""-->
			<img src="https://payu.in/assets/landingPages/images/logo.svg" alt="PayU">
        </a>
        <div class="navbar-text"> 
            <a href="<?= getenv('app.baseURL') ?>settings/customButton" ><button class="btn btn-text-green" type="button">Custom Payment Button</button></a>
            <a href="<?= getenv('app.baseURL') ?>home/orderDetails" ><button class="btn btn-green order-mobile" type="button">Order Details</button></a>
        </div>
    </nav>
</div>