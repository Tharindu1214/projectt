$(document).ready(function(){
	searchOptionValueListing(document.frmSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSearchOptionValuePaging;		
		$(frm.page).val(page);
		searchOptionValueListing(frm);
	}

	reloadList = function() {
		var frm = document.frmSearchOptionValuePaging;
		searchOptionValueListing(frm);
	}

	addOptionValueForm = function(optionId,id) {
		var frm = document.frmSearchOptionValuePaging;		
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('OptionValues', 'form', [optionId,id]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};

	setUpOptionValues = function(frm) {
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('OptionValues', 'setup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				optionValueLangForm(t.optionValueId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	optionValueLangForm = function(optionValueId, langId) {		
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('OptionValues', 'langForm', [optionValueId, langId]), '', function(t) {
				$.facebox(t);
			});
		});
	};
	
	setUpOptionValueLang=function(frm){ 
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('OptionValues', 'langSetup'), data, function(t) {
			reloadList();				
			if (t.langId>0) {
				optionValueLangForm(t.optionValueId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	searchOptionValueListing = function(form){		
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$("#optionValueListing").html('Loading....');
		fcom.ajax(fcom.makeUrl('OptionValues','search'),data,function(res){
			$("#optionValueListing").html(res);
		});
	};
	
	deleteOptionValueRecord=function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.ajax(fcom.makeUrl('OptionValues','deleteRecord'),data,function(res){		
			reloadList();
		});
	};
	
	clearOptionValueSearch = function(){
		document.frmSearch.reset();
		searchOptionValueListing(document.frmSearch);
	};

})();
