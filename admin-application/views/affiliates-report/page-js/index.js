$(document).ready(function(){
	searchAffiliatesReport(document.frmAffiliatesReportSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';

	goToSearchPage = function(page) {	
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmAffiliatesReportSearchPaging;		
		$(frm.page).val(page);
		searchAffiliatesReport(frm);
	};

	reloadList = function() {
		var frm = document.frmAffiliatesReportSearchPaging;
		searchAffiliatesReport(frm);
	};
	
	searchAffiliatesReport = function(form){				
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('AffiliatesReport','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	exportReport = function(dateFormat){
		document.frmAffiliatesReportSearch.action = fcom.makeUrl('AffiliatesReport','export');
		document.frmAffiliatesReportSearch.submit();		
	}
	
	clearSearch = function(){
		document.frmAffiliatesReportSearch.reset();
		searchAffiliatesReport(document.frmAffiliatesReportSearch);
	};
})();	