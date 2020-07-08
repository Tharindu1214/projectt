$(document).ready(function(){
	searchBadProductsReport( document.frmBadProductsReportSearch );
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';
	
	searchBadProductsReport = function(form){
		var data = '';
		if ( form ) {
			data = fcom.frmData(form);
		}
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('TopProductsReport','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	exportReport = function(dateFormat){
		document.frmBadProductsReportSearch.action = fcom.makeUrl('TopProductsReport','export');
		document.frmBadProductsReportSearch.submit();		
	}
	
	clearSearch = function(){
		document.frmBadProductsReportSearch.reset();
		searchBadProductsReport(document.frmBadProductsReportSearch);
	};
})();	