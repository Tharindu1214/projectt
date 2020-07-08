$(document).ready(function(){
	searchSalesReport(document.frmSalesReportSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';

	goToSearchPage = function(page) {	
		if(typeof page == undefined || page == null){
			page =1;
		}
		var frm = document.frmSalesReportSearchPaging;		
		$(frm.page).val(page);
		searchSalesReport(frm);
	};

	reloadList = function() {
		var frm = document.frmSalesReportSearchPaging;
		searchSalesReport(frm);
	};
	
	searchSalesReport = function(form){				
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('SalesReport','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	clearSearch = function(){
		document.frmSalesReportSearch.reset();
		searchSalesReport(document.frmSalesReportSearch);
	};
})();	