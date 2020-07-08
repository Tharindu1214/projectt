$(document).ready(function(){
	changePasswordForm();
	changeEmailForm();
});

(function() {
	var runningAjaxReq = false;
	var passdv = '#changePassFrmBlock';
	var emaildv = '#changeEmailFrmBlock';

	checkRunningAjax = function(){
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
	};

	changePasswordForm = function(){
		$(passdv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Account', 'changePasswordForm'), '', function(t) {
			$(passdv).html(t);
		});
	};

	changeEmailForm = function(){
		$(emaildv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Account', 'changeEmailForm'), '', function(t) {
			$(emaildv).html(t);
		});
	};

	updatePassword = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Account', 'updatePassword'), data, function(t) {
			changePasswordForm();
		});
	};

	updateEmail = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Account', 'updateEmail'), data, function(t) {
			changeEmailForm();
		});
	};

})();
