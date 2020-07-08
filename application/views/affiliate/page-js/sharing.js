var facebookScope = "email";
$("document").ready(function(){
	$('.showbutton').click(function() {
		$(this).toggleClass("active");
		$('.showwrap').slideToggle("600");
	});
	
	$("#facebook_btn").click(function(event) {
		event.preventDefault();
		fbSubmit();
	});
	
	$("#twitter_btn").click(function(event) {
		event.preventDefault();
		twitter_login();
	});
	
});


(function() {
	setUpMailAffiliateSharing = function( frm ) {
		if ( !$(frm).validate() ) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax( fcom.makeUrl('Affiliate', 'setUpMailAffiliateSharing'), data, function(t) {
			frm.reset();
		});
	};
	
	fbSubmit = function(){
		FB.getLoginStatus(function(response) {
			if (response.status === 'connected') {
				facebook_redirect(response);
			} else if (response.status === 'not_authorized') {
				FB.login(function(response) {
					facebook_redirect(response);
				}, {
					scope : facebookScope
				});
			} else {
				FB.login(function(response) {
					if (response.authResponse) {
						facebook_redirect(response);
					}
				}, {
					scope : facebookScope
				});
			}
		});
	};
	
})();	