var facebookScope = "email";
$(document).ready(function(){
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
	personalInfo();
});

(function() {
	var tabListing = "#tabListing";

	personalInfo = function(el){
		$(tabListing).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Account','personalInfo'), '', function(res){
			$(tabListing).html(res);
			$(el).parent().siblings().removeClass('is-active');
			$(el).parent().addClass('is-active');
		});
	};

	copy = function(obj){
		var copyText = obj.attr('title');
		document.addEventListener('copy', function(e) {
			e.clipboardData.setData('text/plain', copyText);
			e.preventDefault();
		}, true);
		document.execCommand('copy');
		alert('copied text: ' + copyText);
	}

	addressInfo = function( el ){
		$(tabListing).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Affiliate','addressInfo'), '', function(res){
			$(tabListing).html(res);
			$(el).parent().siblings().removeClass('is-active');
			$(el).parent().addClass('is-active');
		});
	};

	setUpMailAffiliateSharing = function( frm ) {
		if ( !$(frm).validate() ) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax( fcom.makeUrl('Affiliate', 'setUpMailAffiliateSharing'), data, function(t) {
			frm.reset();
		});
	};

	fbSubmit = function(){
		FB.login(checkLoginStatus, {scope:'email'});
	};

	checkLoginStatus = function(response) {
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
	}
})();
