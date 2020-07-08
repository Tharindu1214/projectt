$(document).ready(function(){
	searchBadCategoriesReport( document.frmBadCategoriesReportSearch );
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';
	
	searchBadCategoriesReport = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('TopCategoriesReport','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	exportReport = function(dateFormat){
		document.frmBadCategoriesReportSearch.action = fcom.makeUrl('TopCategoriesReport','export');
		document.frmBadCategoriesReportSearch.submit();		
	}
	
	clearSearch = function(){
		document.frmBadCategoriesReportSearch.reset();
		searchBadCategoriesReport(document.frmBadCategoriesReportSearch);
	};
})();	