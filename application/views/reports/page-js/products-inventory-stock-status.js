$(document).ready(function(){
	searchProductsInventoryStockStatus(document.frmProductInventoryStockStatusSrch);
});

(function() {
	var runningAjaxReq = false;
	var dv = '#listingDiv';
	
	searchProductsInventoryStockStatus = function(frm){
		$(dv).html( fcom.getLoader() );
		var data = fcom.frmData(frm);
		fcom.ajax(fcom.makeUrl('Reports', 'searchProductsInventoryStockStatus'), data, function(t) {			
			$(dv).html(t);
		});
	};
	
	goToProductsInventoryStockStatusPage = function(page) {
		if(typeof page==undefined || page == null){
			page = 1;
		}		
		var frm = document.frmProductInventoryStockStatusSrchPaging;		
		$( frm.page ).val( page );
		searchProductsInventoryStockStatus( frm );
	}
	
	clearSearch = function(){
		document.frmProductInventoryStockStatusSrch.reset();
		searchProductsInventoryStockStatus( document.frmProductInventoryStockStatusSrch );
	};
	
	exportProductsInventoryStockStatusReport = function(){
		document.frmProductInventorySrchPaging.action = fcom.makeUrl('Reports','exportProductsInventoryStockStatusReport');
		document.frmProductInventorySrchPaging.submit();
	};
})();