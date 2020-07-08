<style type="text/css">
	.bg-modal {
		background: url(./images/bg-dot.png) repeat 0 0, url(./images/floating-layer.png) no-repeat center bottom;
		position: relative;
		padding-bottom: 50px;
	}

	.bg-modal:after {
		background: url(./images/floating-layer.png) no-repeat center bottom;
		position: absolute;
		bottom: 0;
		left: 0;
		right: 0;
		height: 131px;
		width: 100%;
		content: "";
		z-index: -1;
	}


	.pop-logo {
		margin: 1rem auto;
		text-align: center;
		max-width: 180px;
	}

	.pop-logo img {
		display: inline-block;
	}

	.illus {
		margin: 15px auto;
		min-height: 110px;
	}

	.illus img {
		display: inline-block;
	}

	#facebox .content.faceboxWidth.requestdemo {
		padding: 0;
		max-width: 700px;
		min-width: 300px;
		min-height: 150px;
	}
</style>

<div class="modal-body bg-modal">
	<div class="pop-logo"><img src="<?php echo CONF_WEBROOT_URL; ?>image/site-logo/1" alt=""></div>
	<div class="row get-started-wrapper">
		<div class="col-md-6 text-center cms">
			<div class="illus mb-2"><img src="<?php echo CONF_WEBROOT_URL; ?>images/get-started.png" alt=""></div>
			<p>I Really Liked The Features Of Yo!Kart And Want To Discuss My Project</p>
			<a href="https://www.yo-kart.com/contact-us.html" target="_blank" class="btn btn--primary-border btn--sm">Get Started</a>
		</div>
		<div class="col-md-6 text-center cms">
			<div class="illus mb-2"><img src="<?php echo CONF_WEBROOT_URL; ?>images/free-demo.png" alt=""></div>
			<p>I Want To Learn More About The Product And Need A Personalized Live Demo</p>
			<a href="https://www.yo-kart.com/request-demo.html" target="_blank" class="btn btn--primary-border btn--sm">Book A Free Demo</a>
		</div>
	</div>

	<div class="text-center mt-4">
		<a href="https://www.yo-kart.com/multivendor-marketplace-packages.html" target="_blank" class="btn btn--primary">View Packages</a></div>

</div>