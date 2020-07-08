$(document).ready(function(){
	searchShops(document.frmSearchShops);
});

(function() {
	var dv = '#listing';
	var currPage = 1;
	
	reloadListing = function(){
		searchShops(document.frmSearchShops);
	};
	
	searchShops = function(frm, append){
		if(typeof append == undefined || append == null){
			append = 0;
		}
		
		var data = fcom.frmData(frm);
		if( append == 1 ){
			$(dv).prepend(fcom.getLoader());
		} else {
			$(dv).html(fcom.getLoader());
		}
		
		fcom.updateWithAjax(fcom.makeUrl('Shops','search'), data, function(ans){
			$.mbsmessage.close();
			if( append == 1 ){
				$(document.frmSearchShopsPaging).remove();
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
		var frm = document.frmSearchShopsPaging;		
		$(frm.page).val(page);
		searchShops(frm,1);
	};
	
	unFavoriteShopFavorite = function(shopId,e){
		toggleShopFavorite(shopId);
		$(e).attr('onclick','markShopFavorite('+shopId+',this)');
		$(e).html(langLbl.favoriteToShop);
		//reloadListing();
	};
	
	markShopFavorite = function(shopId,e){
		toggleShopFavorite(shopId);
		$(e).attr('onclick','unFavoriteShopFavorite('+shopId+',this)');
		console.log(e);
				$(e).html(langLbl.unfavoriteToShop);
		//reloadListing();
	};
})();