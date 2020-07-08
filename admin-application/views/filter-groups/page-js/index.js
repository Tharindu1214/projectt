$(document).ready(function(){
	searchFilterGroups(document.frmSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmFilterGroupSearchPaging;		
		$(frm.page).val(page);
		searchFilterGroups(frm);
	}

	reloadList = function() {
		var frm = document.frmFilterGroupSearchPaging;
		searchFilterGroups(frm);
	}

	filterGroupForm = function(id) {
		var frm = document.frmFilterGroupSearchPaging;			
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('filterGroups', 'form', [id]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};

	setupFilterGroup = function(frm) {
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('filterGroups', 'setup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				filterGroupLangForm(t.filterGroupId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	filterGroupLangForm = function(filterGroupId, langId) {		
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('filterGroups', 'langForm', [filterGroupId, langId]), '', function(t) {
				$.facebox(t);
			});
		});
	};
	
	setupFilterGroupLang=function(frm){ 
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('filterGroups', 'langSetup'), data, function(t) {
			reloadList();				
			if (t.langId>0) {
				filterGroupLangForm(t.filterGroupId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	searchFilterGroups = function(form){		
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		$("#listing").html('Loading....');
		
		fcom.ajax(fcom.makeUrl('filterGroups','search'),data,function(res){
			$("#listing").html(res);
		});
	};
	
	deleteRecord=function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('filterGroups','deleteRecord'),data,function(res){
			reloadList();
		});
	};
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchFilterGroups(document.frmSearch);
	};

})();
