$("document").ready(function(){
	searchSavedSearchList();
});

(function() {
	var dv = '#SearchesListingDiv';
	searchSavedSearchList = function(page){
		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('SavedProductsSearch','search'), 'page=' + page, function(res){
			$(dv).html(res);
		}); 
	};
	
	
	deleteSavedSearch = function( pssearch_id ){
		var agree = confirm( langLbl.confirmDelete );
		if( !agree ){ return false; };
		fcom.updateWithAjax(fcom.makeUrl('SavedProductsSearch', 'deleteSavedSearch'), 'pssearch_id=' + pssearch_id, function(ans) {
			if( ans.status ){
				searchSavedSearchList();
			}
		});
	};
	
	proceedToSearchPage = function( pssearch_id ){
		fcom.updateWithAjax(fcom.makeUrl('Account', 'updateSearchdate'), 'pssearch_id=' + pssearch_id, function(ans) {
			if( ans.status ){
				searchSavedSearchList();
			}
		});
	};
	
	goToSearchPage = function(page){
		if(typeof page==undefined || page == null){
			page =1;
		}
		searchSavedSearchList(page);
	}
	
})();