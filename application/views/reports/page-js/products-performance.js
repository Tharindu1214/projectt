$(document).ready(function(){
	topPerformingProducts();
});

(function() {
	var runningAjaxReq = false;
	var dv = '#listingDiv';

	goToMostWishListAddedProdSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page = 1;
		}
		var frm = document.frmMostWishListAddedProdSrchPaging;
		$( frm.page ).val( page );
		mostWishListAddedProducts(page);
	}

	goToTopPerformingProductsSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSrchProdPerformancePaging;
		$(frm.page).val(page);
		topPerformingProducts(frm);
	}

	topPerformingProducts = function(frm){
		if(typeof frm == undefined || frm == null){
			frm = document.frmProdPerformanceSrch;
		}
		$(dv).html(fcom.getLoader());
		var data = fcom.frmData(frm);
		fcom.ajax(fcom.makeUrl('Reports', 'searchProductsPerformance', [1]), data, function(t) {
			$('#performanceReportExport').attr('onClick', "exportProdPerformanceReport(1)");
			$(dv).html(t);
		});
	};

	badPerformingProducts = function(frm){
		$(dv).html( fcom.getLoader() );
		var data = fcom.frmData(frm);
		fcom.ajax(fcom.makeUrl('Reports', 'searchProductsPerformance'), data, function(t) {
			$('#performanceReportExport').attr('onClick', "exportProdPerformanceReport(0)");
			$(dv).html(t);
		});
	};

	mostWishListAddedProducts = function(page){
	/* 	$(dv).html( fcom.getLoader() );
		var data = fcom.frmData(frm);
		data += '&order_by=ASC'; */
		if(typeof page==undefined || page == null){
			page = 1;
		}
		var data = '&page='+page;
		fcom.ajax(fcom.makeUrl('Reports', 'searchMostWishListAddedProducts'), data, function(t) {
			$('#performanceReportExport').attr('onClick', 'exportMostFavProdReport()');
			$(dv).html(t);
		});
	};

	exportMostFavProdReport = function(){
		document.frmMostWishListAddedProdSrchPaging.action = fcom.makeUrl('Reports','exportMostWishListAddedProducts');
		document.frmMostWishListAddedProdSrchPaging.submit();
	};

	exportProdPerformanceReport = function( topPerformed ){
		/* if( orderBy == "ASC"){
			document.frmProdPerformanceSrch;
			//topPerformingProducts
		} else {

		} */
		document.frmSrchProdPerformancePaging.action = fcom.makeUrl('Reports','exportProductPerformance', [topPerformed] );
		document.frmSrchProdPerformancePaging.submit();
	}

})();
