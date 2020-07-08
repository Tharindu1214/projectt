	
$(document).ready(function(){
	searchAnalytics(document.frmPromotionAnalyticsSearch);
});

(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#ppcListing';
	

	searchAnalytics = function(form){			
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('Advertiser','searchAnalyticsData'),data,function(res){
			$(dv).html(res);
		});
	};
	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmPromotionAnalyticsSearch;		
		$(frm.page).val(page);
		searchAnalytics(frm);
	};
	clearPromotionSearch = function(){
		document.frmPromotionAnalyticsSearch.reset();
		searchAnalytics(document.frmPromotionAnalyticsSearch);
	}
})();