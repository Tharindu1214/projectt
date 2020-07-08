$(document).ready(function(){
	searchOrderReturnRequests(document.frmRequestSearch);
	
	$(document).on('click','ul.linksvertical li a.redirect--js',function(event){
		event.stopPropagation();
	});		
});
(function() {
	var currentPage = 1;
	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page = 1;
		}		
		var frm = document.frmOrderReturnRequestSearchPaging;		
		$(frm.page).val(page);
		searchOrderReturnRequests(frm);
	}
	
	searchOrderReturnRequests = function(form,page){
		if (!page) {
			page = currentPage;
		}
		currentPage = page;	
		var dv = $('#requestsListing');		
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		dv.html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('OrderReturnRequests','search'),data,function(res){
			dv.html(res);
		});
	};
	
	/* updateStatusForm = function(id){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('OrderReturnRequests', 'updateStatusForm', [id]), '', function(t) {				
				$.facebox(t,'faceboxWidth');
			});
		});
	}
	
	setupStatus = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('OrderReturnRequests', 'setupUpdateStatus'), data, function(t) {
			searchOrderCancellationRequests(document.frmRequestSearch);
			$(document).trigger('close.facebox');
		});
	} */
	
	clearOrderReturnRequestSearch = function(){
		document.frmRequestSearch.reset();
		searchOrderReturnRequests(document.frmRequestSearch);
	};
})();