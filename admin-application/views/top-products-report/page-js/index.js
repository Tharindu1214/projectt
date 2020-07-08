$(document).ready(function(){
	searchTopProductsReport( document.frmTopProductsReportSearch );
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';
	
	searchTopProductsReport = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('TopProductsReport','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	exportReport = function(dateFormat){
		document.frmTopProductsReportSearch.action = fcom.makeUrl('TopProductsReport','export');
		document.frmTopProductsReportSearch.submit();		
	}
	
	clearSearch = function(){
		document.frmTopProductsReportSearch.reset();
		searchTopProductsReport(document.frmTopProductsReportSearch);
	};
})();	