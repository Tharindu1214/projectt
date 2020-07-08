$(document).ready(function(){
	searchOrderCancellationRequests(document.frmRequestSearch);
});
(function() {
	var currentPage = 1;
	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page = 1;
		}		
		var frm = document.frmOrderCancellationRequestSearchPaging;
		$(frm.page).val(page);
		searchOrderCancellationRequests(frm);
	}
	
	searchOrderCancellationRequests = function(form,page){
		if (!page) {
			page = currentPage;
		}
		currentPage = page;	
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		var dv = $('#requestsListing');
		dv.html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('OrderCancellationRequests','search'),data,function(res){
			dv.html(res);
		});
	};
	
	updateStatusForm = function(id){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('OrderCancellationRequests', 'updateStatusForm', [id]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};
	
	setupStatus = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('OrderCancellationRequests', 'setupUpdateStatus'), data, function(t) {
			searchOrderCancellationRequests(document.frmRequestSearch);
			$(document).trigger('close.facebox');
		});
	};
	
	clearOrderCancellationRequestSearch = function(){
		document.frmRequestSearch.reset();
		searchOrderCancellationRequests(document.frmRequestSearch);
	};
})();