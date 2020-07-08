$(document).ready(function(){
	searchOrders(document.frmOrderSrch);
});

(function() {
	searchOrders = function(frm){
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = fcom.frmData(frm);
		/*]*/
		
		$("#ordersListing").html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Buyer','orderSearchListing'), data, function(res){
			$("#ordersListing").html(res);
		});
	};
	
	addItemsToCart = function(orderId){
		fcom.ajax(fcom.makeUrl('Buyer','addItemsToCart',[orderId]), '', function(ans){
			window.location = fcom.makeUrl('Cart');
			return true;
			/* if( ans.status ){
				window.location = fcom.makeUrl('Cart');
				return true;
			}
			return false; */
		});
	};
	
	goToOrderSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmOrderSrchPaging;
		$(frm.page).val(page);
		searchOrders(frm);
	};
	
	clearSearch = function(){
		document.frmOrderSrch.reset();
		searchOrders(document.frmOrderSrch);
	};
	
})();