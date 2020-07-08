$(document).ready(function(){
	SearchFavoriteShops(document.frmSearchfavoriteShops);
});

(function() {
	var dv = '#listing';
	var currPage = 1;
	
	reloadListing = function(){
		SearchFavoriteShops(document.frmSearchfavoriteShops);
	};
	
	SearchFavoriteShops = function(frm, append){
		if(typeof append == undefined || append == null){
			append = 0;
		}
		
		var data = fcom.frmData(frm);
		if( append == 1 ){
			$(dv).prepend(fcom.getLoader());
		} else {
			$(dv).html(fcom.getLoader());
		}
		
			
		fcom.updateWithAjax(fcom.makeUrl('Custom','SearchFavoriteShops'), data, function(ans){
			$.mbsmessage.close();			
			if( append == 1 ){
				$(dv).find('.loader-yk').remove();
				$(dv).append(ans.html);
			} else {
				$(dv).html(ans.html);
			}
			$("#loadMoreBtnDiv").html( ans.loadMoreBtnHtml );
			$("#favShopCount").html( ans.totalRecords );
		}); 
	};
	
	goToLoadMore = function(page){
		if(typeof page == undefined || page == null){
			page = 1;
		}
		currPage = page;
		
		var frm = document.frmSearchfavoriteShopsPaging;		
		$(frm.page).val(page);
		SearchFavoriteShops(frm,1);
	};
	
	unFavoriteShopFavorite = function(shopId){
		toggleShopFavorite(shopId);
		reloadListing();
	};
	/* goToSearchPage = function(page){
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmSearchfavoriteShopsPaging;		
		$(frm.page).val(page);
		SearchFavoriteShops(frm);
	}; */
})();