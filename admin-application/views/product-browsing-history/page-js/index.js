$(document).ready(function(){
	searchBrowsingHistory(document.frmSearch);
});

(function() {
	var currentPage = 1;
	var dv = '#listing';
	var loadingDiv = '<div class="wait"><img src="' + siteConstants.webroot + 'images/loading.gif'  +'"/></div>';
	
	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSearchPaging;		
		$(frm.page).val(page);
		searchBrowsingHistory(frm);
	}

	reloadList = function() {
		var frm = document.frmSearchPaging;
		searchBrowsingHistory(frm);
	};
	
	searchBrowsingHistory = function( form ){
		var data = '';
		if ( form ) {
			data = fcom.frmData(form);
		}
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('ProductBrowsingHistory','Search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchRecomendedProducts(document.frmSearch);
	};
})();
