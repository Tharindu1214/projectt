$(document).ready(function(){
	searchOrderCancellationRequests(document.frmOrderCancellationRequest);
});
(function() {
	searchOrderCancellationRequests = function(frm){
		var data = fcom.frmData(frm);
		$("#cancelOrderRequestsListing").html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Seller','orderCancellationRequestSearch'), data, function(res){
			$("#cancelOrderRequestsListing").html(res);
		}); 
	};
	
	goToOrderCancelRequestSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmOrderCancellationRequestSrchPaging;		
		$(frm.page).val(page);
		searchOrderCancellationRequests(frm);
	}
	
	clearOrderCancelRequestSearch = function(){
		document.frmOrderCancellationRequest.reset();
		searchOrderCancellationRequests(document.frmOrderCancellationRequest);
	};
})();