$(document).ready(function(){
	searchProductsInventory(document.frmProductInventorySrch);
});

(function() {
	var runningAjaxReq = false;
	var dv = '#listingDiv';
	
	searchProductsInventory = function(frm){
		$(dv).html( fcom.getLoader() );
		var data = fcom.frmData(frm);
		fcom.ajax(fcom.makeUrl('Reports', 'searchProductsInventory'), data, function(t) {			
			$(dv).html(t);
		});
	};
	
	goToProductsInventorySearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page = 1;
		}		
		var frm = document.frmProductInventorySrchPaging;		
		$( frm.page ).val( page );
		searchProductsInventory( frm );
	}
	
	clearSearch = function(){
		document.frmProductInventorySrch.reset();
		searchProductsInventory(document.frmProductInventorySrch);
	};
	
	exportProductsInventoryReport = function(){
		document.frmProductInventorySrchPaging.action = fcom.makeUrl('Reports','exportProductsInventoryReport');
		document.frmProductInventorySrchPaging.submit();
	};
})();