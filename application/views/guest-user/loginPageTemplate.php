<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <?php
    $showSignUpLink = isset($showSignUpLink) ? $showSignUpLink : true;
    $onSubmitFunctionName = isset($onSubmitFunctionName) ? $onSubmitFunctionName : 'defaultSetUpLogin';
?> <?php
    //$frm->setRequiredStarPosition(Form::FORM_REQUIRED_STAR_POSITION_NONE);
    $loginFrm->setFormTagAttribute('class', 'form');
    $loginFrm->setValidatorJsObjectName('loginValObj');
    $loginFrm->setFormTagAttribute('action', CommonHelper::generateUrl('GuestUser', 'login'));
    $loginFrm->setFormTagAttribute('onsubmit', $onSubmitFunctionName . '(this, loginValObj); return(false);');
    $loginFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
    $loginFrm->developerTags['fld_default_col'] = 12;
    $fldforgot = $loginFrm->getField('forgot');
    $fldforgot->value='<a href="'.CommonHelper::generateUrl('GuestUser', 'forgotPasswordForm').'"
        class="link">'.Labels::getLabel('LBL_Forgot_Password?',$siteLangId).'</a>';
    $fldSubmit = $loginFrm->getField('btn_submit');
    $fldSubmit->addFieldTagAttribute('class','btn--block'); ?> <?php echo $loginFrm->getFormTag();    ?> <div class="row">
    <div class="col-md-12">
        <div class="field-set">
            <div class="field-wraper">
                <div class="field_cover"><?php echo $loginFrm->getFieldHtml('username'); ?></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="field-set">
            <div class="field-wraper">
                <div class="field_cover"><?php echo $loginFrm->getFieldHtml('password'); ?></div>
            </div>
        </div>
    </div>
</div>
<div class="row align-items-center">
    <div class="col-md-6 col-6">
        <div class="field-set">
            <div class="field-wraper">
                <div class="field_cover">
                    <label class="checkbox"> <?php
                                $fld = $loginFrm->getFieldHTML('remember_me');
                                $fld = str_replace("<label >","",$fld);
                                $fld = str_replace("</label>","",$fld);
                                echo $fld;
                            ?> <i class="input-helper"></i>
                    </label> <?php if($loginFrm->getField('remember_me')); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-6">
        <div class="field-set">
            <div class="forgot"><?php echo $loginFrm->getFieldHtml('forgot'); ?></div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="field-set">
            <div class="field-wraper">
                <div class="field_cover"><?php echo $loginFrm->getFieldHtml('btn_submit'); ?></div>
            </div>
        </div>
    </div>
</div>
</form> <?php echo $loginFrm->getExternalJS();?> <?php $facebookLogin  = (FatApp::getConfig('CONF_ENABLE_FACEBOOK_LOGIN', FatUtility::VAR_INT , 0) && FatApp::getConfig('CONF_FACEBOOK_APP_ID', FatUtility::VAR_STRING , ''))?true:false ;
    $googleLogin  =(FatApp::getConfig('CONF_ENABLE_GOOGLE_LOGIN', FatUtility::VAR_INT , 0)&& FatApp::getConfig('CONF_GOOGLEPLUS_CLIENT_ID', FatUtility::VAR_STRING , ''))?true:false ;
    if ($facebookLogin || $googleLogin ){?> <div class="or-divider"><span class="or"><?php echo Labels::getLabel('LBL_Or', $siteLangId); ?></span></div>
<div class="buttons-list">
    <ul> <?php if ($facebookLogin) { ?> <li><a href="javascript:void(0)" onclick="dofacebookInLoginForBuyerpopup()" class="btn btn--social btn--fb"><i class="icn"><img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/facebook.svg"></i></a></li>
        <?php } if ($googleLogin ) { ?> <li><a href="<?php echo CommonHelper::generateUrl('GuestUser', 'socialMediaLogin',array('google')); ?>" class="btn btn--social btn--gp"><i class="icn"><img
                        src="<?php echo CONF_WEBROOT_URL; ?>images/retina/google-plus.svg"></i></a></li> <?php }?> </ul>
</div> <?php } ?> <script>
    /*Facebook Login API JS SDK*/
    function dofacebookInLoginForBuyerpopup() {
        FB.getLoginStatus(function(response) {
            if (response.status === 'connected') {
                //user is authorized
                // getUserData();
            } else {
                //user is not authorized
            }
        });
        FB.login(function(response) {
            if (response.authResponse) {
                //user just authorized your app
                getUserData();
            }
        }, {
            scope: 'email,public_profile',
            return_scopes: true
        });
    }

    function getUserData() {
        FB.api('/me?fields=id,name,email, first_name, last_name', function(response) {
            response['type'] = <?php echo User::USER_TYPE_BUYER; ?>;
            fcom.updateWithAjax(fcom.makeUrl('GuestUser', 'loginFacebook'), response, function(t) {
                // console.log(response);
                location.href = t.url;
            });
        }, {
            scope: 'public_profile,email'
        });
    }
    window.fbAsyncInit = function() {
        //SDK loaded, initialize it
        FB.init({
            appId: '<?php echo FatApp::getConfig('CONF_FACEBOOK_APP_ID',FatUtility::VAR_STRING,'') ?>',
            xfbml: true,
            version: 'v2.2'
        });
    };
    //load the JavaScript SDK
    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = "https://connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
    /*Facebook Login API JS SDK*/
</script>
