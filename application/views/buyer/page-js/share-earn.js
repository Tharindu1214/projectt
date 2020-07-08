var facebookScope = "email";
jQuery.fn.reset = function () {
  $(this).each (function() { this.reset(); });
}
$(document).ready(function(){

	$("#twitter_btn").click(function(event) {
		event.preventDefault();
		twitter_login();
	});

	$("#facebook_btn").click(function(event) {
		event.preventDefault();
		fbSubmit();
	});

	$("#facebook_btn2").click(function(event){
		event.preventDefault();
		fbSubmit2();
	});

	$('.showbutton').click(function() {
		$(this).toggleClass("active");
		$('.showwrap').slideToggle("600");
	});

	$( 'form[rel=action]' ).submit(function( event ) {
		event.preventDefault();
		var me=$(this);
		var frm=this;
		v = me.attr('validator');
		window[v].validate();
		if (!window[v].isValid()) return;
		var data = getFrmData(frm);
		callAjax($(this).attr('action'),data,function(response){
			var ans = parseJsonData(response);
			if (ans.status==true){
				$("#frmCustomShare").reset();
				$("#custom_ajax").html(ans.message);
			}
	})
	return false;
	});
});

(function() {
	sendMailShareEarn = function( frm ) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Buyer', 'sendMailShareEarn'), data, function(t) {
			frm.reset();
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

})();


function fbSubmit2(){
	alert("called 1");
	FB.getLoginStatus(function(response) {
		if (response.status === 'connected') {
			alert("connected");
			//facebook_redirect(response);
		} else {
			alert("not connected");
		}
	});
	alert("called 2");
}

function fbSubmit() {
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

					//$(window.parent.document).find("#facebook_btn2").trigger("click");;

					/* window.parent.$('#facebook_btn2').trigger('click'); */
					//window.parent.document.getElementById("facebook_btn2").onClick();
					//window.parent.fbSubmit();
					facebook_redirect(response);
				}
			}, {
				scope : facebookScope
			});
		}
	});
}
