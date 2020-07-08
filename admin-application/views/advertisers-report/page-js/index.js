$(document).ready(function(){
	searchAdvertisersReport( document.frmAdvertisersReportSearch );
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';

	goToSearchPage = function(page) {	
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmAdvertisersReportSearchPaging;		
		$(frm.page).val(page);
		searchAdvertisersReport(frm);
	};

	reloadList = function() {
		var frm = document.frmAdvertisersReportSearchPaging;
		searchAdvertisersReport(frm);
	};
	
	searchAdvertisersReport = function(form){				
		var data = '';
		if ( form ) {
			data = fcom.frmData(form);
		}
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('AdvertisersReport','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	exportReport = function(dateFormat){
		document.frmAdvertisersReportSearch.action = fcom.makeUrl('AdvertisersReport','export');
		document.frmAdvertisersReportSearch.submit();		
	}
	
	clearSearch = function(){
		document.frmAdvertisersReportSearch.reset();
		searchAdvertisersReport(document.frmAdvertisersReportSearch);
	};
})();	