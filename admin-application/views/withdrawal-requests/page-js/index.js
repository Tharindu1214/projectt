$(document).ready(function(){
	searchListing(document.frmReqSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';
	
	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmReqSearchPaging;		
		$(frm.page).val(page);
		searchListing(frm);
	}

	reloadList = function() {
		var frm = document.frmReqSearchPaging;
		searchListing(frm);
	}

	searchListing = function(form){
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('WithdrawalRequests','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	updateStatus = function(id,status,statusName){
		data = 'id='+id+'&status='+status;
		if(confirm(langLbl.DoYouWantTo+' '+statusName+' '+langLbl.theRequest)){
			fcom.updateWithAjax(fcom.makeUrl('WithdrawalRequests', 'updateStatus'), data, function(t) {
				reloadList();
			});
		}
	};
	
	clearTagSearch = function(){
		document.frmReqSearch.reset();
		searchListing(document.frmReqSearch);
	};

})();
