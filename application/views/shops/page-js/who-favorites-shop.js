$(document).ready(function(){
	searchListing(document.frmsearchWhoFavouriteShop);		
});
(function() {
	var runningAjaxReq = false;
	var dv = '#shopFavListing';
	var currPage = 1;
	
	reloadListing = function(){
		searchListing(document.frmsearchWhoFavouriteShop);
	};
	
	searchListing = function(frm, append){				
		/* $(dv).html( fcom.getLoader() );
		var data = fcom.frmData(frm);
		fcom.ajax(fcom.makeUrl('Shops','searchWhoFavouriteShop'),data,function(res){
			runningAjaxReq = false;
			$(dv).html(res);
		}); */	
		if(typeof append == undefined || append == null){
			append = 0;
		}
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = fcom.frmData(frm);
		/*]*/
		
		if( append == 1 ){
			$(dv).prepend(fcom.getLoader());
		} else {
			$(dv).html(fcom.getLoader());
		}
		
		fcom.updateWithAjax(fcom.makeUrl('Shops','searchWhoFavouriteShop'), data, function(ans){
			$.mbsmessage.close();			
			if( append == 1 ){ 
				$(dv).find('.loader-yk').remove();
				$(dv).append(ans.html);
			} else {
				$(dv).html(ans.html);
			}
			$("#loadMoreBtnDiv").html( ans.loadMoreBtnHtml );			
		}); 
	};
	
	goToLoadMore = function(page){
		if(typeof page == undefined || page == null){
			page = 1;
		}
		currPage = page;
		
		var frm = document.frmSearchWhoFavouriteShopPaging;		
		$(frm.page).val(page);
		searchListing(frm,1);
	};
	
	/* goToSearchPage = function(page){
		if(typeof page==undefined || page == null){
			page = 1;
		}
		var frm = document.frmSearchWhoFavouriteShopPaging;		
		$(frm.page).val(page);
		searchListing(frm);
	} */
	
})();