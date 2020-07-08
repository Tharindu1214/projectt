<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$sharingFrm->addFormTagAttribute('class','form');
$sharingFrm->addFormTagAttribute('onsubmit','setUpMailAffiliateSharing(this);return false;');
$sharingFrm->developerTags['colClassPrefix'] = 'col-xs-12 col-md-';
$sharingFrm->developerTags['fld_default_col'] = 12;
?>
<?php $this->includeTemplate('_partial/affiliate/affiliateDashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
  <div class="content-wrapper content-space">
	<div class="content-header justify-content-between row mb-4">
		<div class="content-header-left col-md-auto">
			<?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
			<h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Sharing' , $siteLangId); ?></h2>
		</div>
	</div>
	<div class="content-body">
		<div class="row">
			<div class="col-md-12">
				<div class="cards">
					<div class="cards-header p-4"><h5 class="cards-title "><?php echo Labels::getLabel('LBL_Sharing_Information',$siteLangId);?></h5></div>
					
						<div class="row">						
							<div class="col-md-12">
								<p class="note"><?php echo Labels::getLabel('LBL_Affiliate_Sharing_information_text',$siteLangId)?><br/><strong><?php echo Labels::getLabel('LBL_You_may_copy_invitation_link_below',$siteLangId)?></strong></p>
								<div class="alert--gray"><?php echo $affiliateTrackingUrl; ?></div>
							</div>
						</div>
					
					<div class="cards-content pl-4 pr-4 ">
						<ul class="grid--onethird grid--onethird-large">
						  <?php if (!empty(FatApp::getConfig("CONF_FACEBOOK_APP_ID")) && !empty(FatApp::getConfig("CONF_FACEBOOK_APP_SECRET"))){?>
						  <li> <a id="facebook_btn" href="javascript:void(0);" class="box--share box--share-fb"> <i class="fa fa-facebook"></i>
							<h5><?php echo Labels::getLabel('L_Share_on',$siteLangId)?></h5>
							<h2><?php echo Labels::getLabel('L_Facebook',$siteLangId)?></h2>
							<p><?php echo sprintf(Labels::getLabel('L_Post_your_wall_facebook',$siteLangId),'<strong>'.Labels::getLabel('L_Facebook',$siteLangId).'</strong>')?></p>
							</a>
							<span class="ajax_message thanks-msg" id="fb_ajax"></span>
						  </li>
						  <?php } ?>
						  <?php if (!empty(FatApp::getConfig("CONF_TWITTER_API_KEY",FatUtility::VAR_STRING,'')) && !empty(FatApp::getConfig("CONF_TWITTER_API_SECRET",FatUtility::VAR_STRING,''))){ ?>
						  <li> <a class="box--share box--share-tw" id="twitter_btn" href="javascript:void(0);"> <i class="fa fa-twitter"></i>
							<h5><?php echo Labels::getLabel('L_Share_on',$siteLangId)?></h5>
							<h2><?php echo Labels::getLabel('L_Twitter',$siteLangId)?></h2>
							<p><?php echo sprintf(Labels::getLabel('L_Send_a_tweet_followers',$siteLangId),'<strong>'.Labels::getLabel('L_Tweet',$siteLangId).'</strong>')?></p>
							</a> <span class="ajax_message thanks-msg" id="twitter_ajax"></span>
							</li>
						  <?php } ?>
						  <li> <a class="showbutton box--share box--share-mail" href="javascript:void(0);"> <i class="fa fa-envelope"></i>
							<h5><?php echo Labels::getLabel('L_Share_on',$siteLangId)?></h5>
							<h2><?php echo Labels::getLabel('L_Email',$siteLangId)?></h2>
							<p><?php echo Labels::getLabel('L_Email',$siteLangId)?></strong> <?php echo Labels::getLabel('L_Your_friend_tell_them_about_yourself',$siteLangId)?></p>
							</a>
							<span class="ajax_message thanks-msg"></span>
							</li>
						</ul>
						<span class="gap"></span>
						<div style="display:none;" class="borderwrap showwrap">
							<h4><?php echo Labels::getLabel('L_Invite_friends_through_email',$siteLangId)?></h4>
							<?php echo $sharingFrm->getFormHtml(); ?>
							<span class="ajax_message" id="custom_ajax"></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
  </div>
</main>

<script type="text/javascript">
(function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=<?php echo FatApp::getConfig("CONF_FACEBOOK_APP_ID",FatUtility::VAR_STRING,''); ?>";
		fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));


function facebook_redirect(response_token){
	FB.ui( {
		method: 'feed',
		name: "<?php echo sprintf(FatApp::getConfig("CONF_SOCIAL_FEED_FACEBOOK_POST_TITLE_$siteLangId",FatUtility::VAR_STRING,''),FatApp::getConfig("CONF_WEBSITE_NAME_$siteLangId"))?>",
		link: "<?php echo $affiliateTrackingUrl?>",
		picture: "<?php echo CommonHelper::generateFullUrl('image', 'socialFeed',array($siteLangId ,''),"/")?>",
		caption: "<?php echo sprintf(FatApp::getConfig("CONF_SOCIAL_FEED_FACEBOOK_POST_CAPTION_$siteLangId",FatUtility::VAR_STRING,''),FatApp::getConfig("CONF_WEBSITE_NAME_$siteLangId"))?>",
		description: "<?php echo str_replace(array("\n","\r","\r\n"),' ',sprintf(FatApp::getConfig("CONF_SOCIAL_FEED_FACEBOOK_POST_DESCRIPTION_".$siteLangId,FatUtility::VAR_STRING,''),FatApp::getConfig("CONF_WEBSITE_NAME_".$siteLangId)))?>",

	},
	function( response ) {
		if ( response !== null && typeof response.post_id !== 'undefined' ) {
			$.mbsmessage(langLbl.thanksForSharing, true, 'alert--success');
			/* $("#fb_ajax").html(langLbl.thanksForSharing); */
		}
	});
}
function twitter_shared(name){
	$.mbsmessage(langLbl.thanksForSharing, true, 'alert--success');
	/* $("#twitter_ajax").html(langLbl.thanksForSharing); */
}
</script>

<?php
$_SESSION["TWITTER_URL"]=CommonHelper::generateFullUrl('Affiliate','twitterCallback',array(),'',false);
$twitteroauth = new TwitterOAuth(FatApp::getConfig("CONF_TWITTER_API_KEY"), FatApp::getConfig("CONF_TWITTER_API_SECRET"));
$get_twitter_url=$_SESSION["TWITTER_URL"];
$request_token = $twitteroauth->getRequestToken($get_twitter_url);
$_SESSION['oauth_token'] = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
if ($twitteroauth->http_code == 200) {
	$url = $twitteroauth->getAuthorizeURL($request_token['oauth_token']);
	?>
	<script type="text/javascript">
	var newwindow;
	var intId;
	function twitter_login(){
		var  screenX    = typeof window.screenX != 'undefined' ? window.screenX : window.screenLeft,
			 screenY    = typeof window.screenY != 'undefined' ? window.screenY : window.screenTop,
			 outerWidth = typeof window.outerWidth != 'undefined' ? window.outerWidth : document.body.clientWidth,
			 outerHeight = typeof window.outerHeight != 'undefined' ? window.outerHeight : (document.body.clientHeight - 22),
			 width    = 800,
			 height   = 600,
			 left     = parseInt(screenX + ((outerWidth - width) / 2), 10),
			 top      = parseInt(screenY + ((outerHeight - height) / 2.5), 10),
			 features = (
				'width=' + width +
				',height=' + height +
				',left=' + left +
				',top=' + top
			  );
		newwindow=window.open('<?php echo $url; ?>','Login_by_twitter',features);

	   if (window.focus) {newwindow.focus()}
	  return false;
	}
	</script>
	<?php
}
