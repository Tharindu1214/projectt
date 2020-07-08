$(document).ready(function(){
	searchShopReport(shopId);
});

(function() {
	var runningAjaxReq = false;
	var dv = '#listing';
	
	reloadList = function() {
		searchShopReport();
	};	
	
	searchShopReport = function(shopId){		
		$(dv).html(fcom.getLoader());
		
		var data='shopId='+shopId;
		
		fcom.ajax(fcom.makeUrl('ShopReports','search'),data,function(res){
			$(dv).html(res);			
		});
	};
	
	deleteRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='sreportId='+id;
		fcom.ajax(fcom.makeUrl('ShopReports', 'deleteRecord'),data,function(res){		
			reloadList();
		});
	};
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchShopReport(document.frmSearch);
	};
})();	