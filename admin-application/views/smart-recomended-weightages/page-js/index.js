$(document).ready(function(){
	searchWeightage(document.frmSearch);
});

(function() {
	var currentPage = 1;
	var dv = '#listing';
	
	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSearchPaging;		
		$(frm.page).val(page);
		searchWeightage(frm);
	};
	
	reloadList = function() {
		var frm = document.frmSearchPaging;
		searchWeightage(frm);
	};
	
	searchWeightage = function(form){
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('SmartRecomendedWeightages','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchWeightage(document.frmSearch);
	};
	
	updateWeightage = function(id,val){	
		var data = 'weightage='+val;
		fcom.updateWithAjax(fcom.makeUrl('SmartRecomendedWeightages', 'update',[id]), data, function(t) {
		});
	};
	
})();
