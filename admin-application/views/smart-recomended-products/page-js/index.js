$(document).ready(function(){
	searchRecomendedProducts(document.frmSearch);
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
		searchRecomendedProducts(frm);
	}

	reloadList = function() {
		var frm = document.frmSearchPaging;
		searchRecomendedProducts(frm);
	};
	
	searchRecomendedProducts = function( form ){
		var data = '';
		if ( form ) {
			data = fcom.frmData(form);
		}
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('SmartRecomendedProducts','Search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	var loadingDiv = '<div class="wait"><img src="' + siteConstants.webroot + 'images/loading.gif'  +'"/></div>';
	/* saveData = function( frm ){
		if ( !$(frm).validate() ) return;	
		var data = fcom.frmData( frm );
	} */
	saveData = function( spw_product_id, ele, fld_name ){
		$(".wait").remove();
		$( ele ).after( loadingDiv );
		var val = $(ele).val();
		if( $(ele).attr("name") == "spw_is_excluded" ){
			val = 0;
			if( $(ele).is(":checked") ){
				val = 1;
			}
		}
		var data = 'fld_name=' + fld_name + '&spw_product_id=' + spw_product_id + '&value='+val;
		fcom.updateWithAjax(fcom.makeUrl('SmartRecomendedProducts', 'update'), data, function(ans) {
			$(".wait").remove();
		});
		return false;
	}
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchRecomendedProducts(document.frmSearch);
	};
})();
