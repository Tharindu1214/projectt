$(document).ready(function(){
	searchTopCategoriesReport( document.frmTopCategoriesReportSearch );
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';
	
	searchTopCategoriesReport = function(form){
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
		document.frmTopCategoriesReportSearch.action = fcom.makeUrl('TopCategoriesReport','export');
		document.frmTopCategoriesReportSearch.submit();		
	}
	
	clearSearch = function(){
		document.frmTopCategoriesReportSearch.reset();
		searchTopCategoriesReport(document.frmTopCategoriesReportSearch);
	};
})();	