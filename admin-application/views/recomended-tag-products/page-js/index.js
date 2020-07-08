$(document).ready(function(){
	searchRecommendeTagProducts(document.frmSearch);
});

(function() {
	var currentPage = 1;
	var dv = '#listing';
	
	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSearchPaging;		
		$(frm.page).val(page);
		searchRecommendeTagProducts(frm);
	}

	reloadList = function() {
		var frm = document.frmSearchPaging;
		searchRecommendeTagProducts(frm);
	};
	
	searchRecommendeTagProducts = function( form ){
		var data = '';
		if ( form ) {
			data = fcom.frmData(form);
		}
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('RecomendedTagProducts','Search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	var loadingDiv = '<div class="wait"><img src="' + siteConstants.webroot + 'images/loading.gif'  +'"/></div>';
	
	saveData = function(tagId,productId,form ){
		$(".wait").remove();
		var data = fcom.frmData(form);;
		var data = data+'&tag_id=' + tagId + '&product_id=' + productId ;
		fcom.updateWithAjax(fcom.makeUrl('RecomendedTagProducts', 'update'), data, function(ans) {
			$(".wait").remove();
		});
		return false;
	}
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchRecommendeTagProducts(document.frmSearch);
	};
})();
