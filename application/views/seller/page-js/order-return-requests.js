$(document).ready(function(){
	searchOrderReturnRequests(document.frmOrderReturnRequest);
});
(function() {
	searchOrderReturnRequests = function(frm){
		var data = fcom.frmData(frm);
		$("#returnOrderRequestsListing").html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Seller','orderReturnRequestSearch'), data, function(res){
			$("#returnOrderRequestsListing").html(res);
		}); 
	};
	
	goToOrderReturnRequestSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmOrderReturnRequestSrchPaging;		
		$(frm.page).val(page);
		searchOrderReturnRequests(frm);
	}
	
	clearOrderReturnRequestSearch = function(){
		document.frmOrderReturnRequest.reset();
		searchOrderReturnRequests(document.frmOrderReturnRequest);
	};
})();