<?php
	$showSignUpLink = isset($showSignUpLink) ? $showSignUpLink : true;
	$onSubmitFunctionName = isset($onSubmitFunctionName) ? $onSubmitFunctionName : 'defaultSetUpLogin';
?>
<section>
	<h3><?php echo Labels::getLabel('LBL_Login',$siteLangId);?></h3>
	<div class="check-login-wrapper step__body">
		<div id="" class="tabz--checkout-login tabs--flat-js">
			<ul>
				<li class="is-active"><a href="#user-1"> <i class="icn"><svg class="svg">
								<use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tick" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tick"></use>
							</svg></i><?php echo Labels::getLabel('LBL_Existing_User', $siteLangId); ?> </a></li>
				<li><a href="#user-2"> <i class="icn"><svg class="svg">
								<use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tick" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tick"></use>
							</svg></i><?php echo Labels::getLabel('LBL_Guest_User', $siteLangId); ?> </a></li>
			</ul>
		</div>
		<div id="user-1" class="tabs-content tabs-content-js">
			<?php
			//$frm->setRequiredStarPosition(Form::FORM_REQUIRED_STAR_POSITION_NONE);
			$loginFrm->setFormTagAttribute('class', 'form form-checkout-login');
			$loginFrm->setFormTagAttribute('name', 'formLoginPage');
			$loginFrm->setFormTagAttribute('id', 'formLoginPage');
			$loginFrm->setValidatorJsObjectName('loginFormObj');

			$loginFrm->setFormTagAttribute('onsubmit','return '. $onSubmitFunctionName . '(this, loginFormObj);');
			$loginFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-12 col-xs-';
			$loginFrm->developerTags['fld_default_col'] = 12;
			$loginFrm->removeField($loginFrm->getField('remember_me'));
			$fldforgot = $loginFrm->getField('forgot');
			$fldforgot->value='<a href="'.CommonHelper::generateUrl('GuestUser', 'forgotPasswordForm').'"
			class="forgot">'.Labels::getLabel('LBL_Forgot_Password?',$siteLangId).'</a>';
			$fldSubmit = $loginFrm->getField('btn_submit');
			$fldSubmit->addFieldTagAttribute("class","btn--block");
			echo $loginFrm->getFormHtml();
			?>
		</div>
		<div id="user-2" class="tabs-content tabs-content-js">
			<?php
			$guestLoginFrm->setFormTagAttribute('class', 'form form-checkout-login');
			$guestLoginFrm->setFormTagAttribute('name', 'frmGuestLogin');
			$guestLoginFrm->setFormTagAttribute('id', 'frmGuestLogin');
			$guestLoginFrm->setValidatorJsObjectName('guestLoginFormObj');

			$guestLoginFrm->setFormTagAttribute('onsubmit','return guestUserLogin(this, guestLoginFormObj);');
			$guestLoginFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-12 col-xs-';
			$guestLoginFrm->developerTags['fld_default_col'] = 12;

			$fldSpace = $guestLoginFrm->getField('space');
			$fldSpace->value ='<a href="#" class="forgot">&nbsp;</a>';

			$fldSubmit = $guestLoginFrm->getField('btn_submit');
			$fldSubmit->addFieldTagAttribute("class","btn--block");
			echo $guestLoginFrm->getFormHtml(); ?>
		</div>
		<?php
		$facebookLogin  = (FatApp::getConfig('CONF_ENABLE_FACEBOOK_LOGIN', FatUtility::VAR_INT , 0) && FatApp::getConfig('CONF_FACEBOOK_APP_ID', FatUtility::VAR_STRING , ''))?true:false ;
		$googleLogin  =(FatApp::getConfig('CONF_ENABLE_GOOGLE_LOGIN', FatUtility::VAR_INT , 0)&& FatApp::getConfig('CONF_GOOGLEPLUS_CLIENT_ID', FatUtility::VAR_STRING , ''))?true:false ; if ($facebookLogin || $googleLogin ){?>
		<div class="row justify-content-center">
			<div class="col-lg-12 ">
				<div class=""><span class="or"><?php echo Labels::getLabel('LBL_Or', $siteLangId); ?></span></div>
				<div class="buttons-list buttons-list-checkout">
					<ul>
					<?php if ($facebookLogin) { ?>
						<li><a href="javascript:void(0)" onclick="dofacebookInLoginForBuyerpopup()" class="btn btn--social btn--fb"><i class="icn"><img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/facebook.svg"></i><?php echo Labels::getLabel('LBL_Login_With_Facebook',$siteLangId);?></a></li>
					<?php } if ($googleLogin ) { ?>
						<li><a href="<?php echo CommonHelper::generateUrl('GuestUser', 'socialMediaLogin',array('google')); ?>" class="btn btn--social btn--gp"><i class="icn"><img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/google-plus.svg"></i><?php echo Labels::getLabel('LBL_Login_With_Google',$siteLangId);?></a></li>
					<?php }?>
					</ul>
				</div>
			</div>
		</div>
		<?php }?>
		<div class="gap"></div>
		<div class="term">
			<?php if( $showSignUpLink ){ ?><p class="text--dark"> <a href="" class="text text--uppercase"></a></p><?php } ?>
			<h6><?php echo sprintf(Labels::getLabel('LBL_New_to',$siteLangId),FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId));?>? <a href="<?php echo CommonHelper::generateUrl('GuestUser', 'loginForm', array(applicationConstants::YES)); ?>" class="link"><?php echo Labels::getLabel('LBL_Sign_Up',$siteLangId);?></a></h6>
			<!-- <p>If this is your first time shopping with us, please enter an email address to use as your Newegg ID and create a password for your account. Your Newegg account allows you to conveniently place orders, create wishlists, check the status of your recent orders and much more.</p> -->
		</div>
	</div>
</section>

<script>
/*Facebook Login API JS SDK*/

	function dofacebookInLoginForBuyerpopup()
	{
		FB.getLoginStatus(function(response) {
			if (response.status === 'connected') {
				//user is authorized
				getUserData();
			} else {
				//user is not authorized
			}
		});

		FB.login(function(response) {
			if (response.authResponse) {
				//user just authorized your app
					getUserData();
			}
		}, {scope: 'email,public_profile', return_scopes: true});
	}

	function getUserData()
	{
		FB.api('/me?fields=id,name,email, first_name, last_name', function(response) {
			response['type'] = <?php echo User::USER_TYPE_BUYER; ?>;
			fcom.updateWithAjax(fcom.makeUrl('GuestUser', 'loginFacebook'), response, function(t) {
				location.href = t.url;
			});
		}, {scope: 'public_profile,email'});
	}

	window.fbAsyncInit = function() {
		//SDK loaded, initialize it
		FB.init({
			appId      : '<?php echo FatApp::getConfig('CONF_FACEBOOK_APP_ID',FatUtility::VAR_STRING,'') ?>',
			xfbml      : true,
			version    : 'v2.2'
		});
	};

	//load the JavaScript SDK
	(function(d, s, id){
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) {return;}
		js = d.createElement(s); js.id = id;
		js.src = "https://connect.facebook.net/en_US/sdk.js";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));

	/*Facebook Login API JS SDK*/
	
	/*Tabs*/
	$(document).ready(function () {
		$(".tabs-content-js").hide();
		$(".tabs--flat-js li:first").addClass("is-active").show();
		$(".tabs-content-js:first").show();
		$(".tabs--flat-js li").click(function () {
			$(".tabs--flat-js li").removeClass("is-active");
			$(this).addClass("is-active");
			$(".tabs-content-js").hide();
			var activeTab = $(this).find("a").attr("href");
			$(activeTab).fadeIn();
			return false;
			setSlider();
		});
	});
</script>
