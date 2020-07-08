$(document).ready(function(){
	searchOptions(document.frmOptionSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmOptionsSearchPaging;		
		$(frm.page).val(page);
		searchOptions(frm);
	}

	reloadList = function() {
		var frm = document.frmOptionsSearchPaging;
		searchOptions(frm);
	}

	addOptionForm = function(id) {
		var frm = document.frmOptionsSearchPaging;			
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Options', 'form', [id]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};

	setupOptions = function(frm) {
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Options', 'setup'), data, function(t) {			
			reloadList();
			if (t.langId>0) {
				addOptionLangForm(t.optionId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	addOptionLangForm = function(optionId, langId) {		
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Options', 'langForm', [optionId, langId]), '', function(t) {
				$.facebox(t);
			});
		});
	};
	
	setupOptionsLang=function(frm){ 
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('Options', 'langSetup'), data, function(t) {			
			reloadList();				
			if (t.langId>0) {
				addOptionLangForm(t.optionId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	searchOptions = function(form){		
		$("#optionListing").html('Loading....');
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		fcom.ajax(fcom.makeUrl('Options','search'),data,function(res){
			$("#optionListing").html(res);
		});
	};
	
	deleteOptionRecord=function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('Options','deleteRecord'),data,function(res){		
			reloadList();
		});
	};
	
	clearOptionSearch = function(){
		document.frmOptionSearch.reset();
		searchOptions(document.frmOptionSearch);
	};

})();
