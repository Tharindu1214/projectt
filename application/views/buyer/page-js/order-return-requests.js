$(document).ready(function(){
	searchOrderReturnRequests(document.frmOrderReturnRequest);
});
(function() {
	searchOrderReturnRequests = function(frm){
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = fcom.frmData(frm);
		/*]*/
		
		$("#returnOrderRequestsListing").html( fcom.getLoader() );
		
		fcom.ajax(fcom.makeUrl('Buyer','orderReturnRequestSearch'), data, function(res){
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