$(document).ready(function(){
	changeEmailForm();		
});

(function() {
	var runningAjaxReq = false;
	var dv = '#changeEmailFrmBlock';
	
	checkRunningAjax = function(){
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
	};
	
	changeEmailForm = function(){				
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('GuestUser', 'changeEmailForm'), '', function(t) {			
			$(dv).html(t);
		});
	};
	
	updateEmail = function (frm){
		if (!$(frm).validate()) return;	
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('GuestUser', 'updateEmail'), data, function(t) {						
			changeEmailForm();			
		});	
	};
	
})();