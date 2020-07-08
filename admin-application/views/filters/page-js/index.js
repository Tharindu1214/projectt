$(document).ready(function(){
	searchListing(document.frmSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSearchPaging;		
		$(frm.page).val(page);
		searchListing(frm);
	}

	reloadList = function() {
		var frm = document.frmSearchPaging;
		searchListing(frm);
	}

	addForm = function(fgId,id) {
		var frm = document.frmSearchPaging;		
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('filters', 'form', [fgId,id]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};

	setUp = function(frm) {
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('filters', 'setup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				langForm(t.filterId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	langForm = function(filterId, langId) {		
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('filters', 'langForm', [filterId, langId]), '', function(t) {
				$.facebox(t);
			});
		});
	};
	
	setUpLang=function(frm){ 
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('filters', 'langSetup'), data, function(t) {
			reloadList();				
			if (t.langId>0) {
				langForm(t.filterId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	searchListing = function(form){		
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$("#listing").html('Loading....');
		fcom.ajax(fcom.makeUrl('filters','search'),data,function(res){
			$("#listing").html(res);
		});
	};
	
	deleteRecord=function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('filters','deleteRecord'),data,function(res){
			reloadList();
		});
	};
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchListing(document.frmSearch);
	};

})();
