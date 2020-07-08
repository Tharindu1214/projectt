$(document).ready(function(){
	searchSentEmails(document.sentEmailSrchForm);
});

(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	searchSentEmails = function(frm) {
		if(runningAjaxReq == true){
			return;
		}
		runningAjaxReq = true;
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (frm) {
			data = fcom.frmData(frm);
		}
		/*]*/
		
		var dv = $('#emails-list');
		$(dv).html(langLbl.processing);
		
		fcom.ajax(fcom.makeUrl('SentEmails', 'search'), data, function(res) {
			dv.html(res);
			runningAjaxReq = false;
		});
	};
	
	goToSearchPage = function( page ) {
		if( typeof page == undefined || page == null ){
			page = 1;
		}
		var frm = document.frmSentEmailSearchPaging;		
		$(frm.page).val(page);
		searchSentEmails(frm);
	}
	
	/* searchProductCategories = function(form){
		//$.mbsmessage('Please wait...');
		$("#listing").html('Loading....');
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		fcom.ajax(fcom.makeUrl('productCategories','search'),data,function(res){
			$("#listing").html(res);
		});
	}; */
	
	listPage = function(page) {
		searchSentEmails(document.sentEmailSrchForm, page);
	};
	
	reloadProgramsList = function() {
		document.sentEmailSrchForm.reset();
		setTimeout(function(){
			searchSentEmails(document.sentEmailSrchForm, 1);
		}, 500);
		
	}
	
})();