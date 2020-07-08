$(document).ready(function(){
	searchUsersReport(document.frmUsersReportSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';

	goToSearchPage = function(page) {	
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmUsersReportSearchPaging;		
		$(frm.page).val(page);
		searchUsersReport(frm);
	};

	reloadList = function() {
		var frm = document.frmUsersReportSearchPaging;
		searchUsersReport(frm);
	};
	
	searchUsersReport = function(form){				
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('usersReport','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	exportReport = function(dateFormat){
		document.frmUsersReportSearch.action = fcom.makeUrl('usersReport','export');
		document.frmUsersReportSearch.submit();		
	}
	
	clearSearch = function(){
		document.frmUsersReportSearch.reset();
		searchUsersReport(document.frmUsersReportSearch);
	};
})();	