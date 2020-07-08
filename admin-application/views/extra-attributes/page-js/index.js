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

	addForm = function(eattrgroup_id,id) {
		var frm = document.frmSearchPaging;		
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('ExtraAttributes', 'form', [eattrgroup_id,id]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};

	setUp = function(frm) {
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('ExtraAttributes', 'setup'), data, function(t) {
			reloadList();
			if ( t.lang_id > 0) {
				langForm(t.eattribute_id, t.lang_id);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	langForm = function( eattribute_id, lang_id ) {
		$.facebox(function() {
			fcom.ajax( fcom.makeUrl('ExtraAttributes', 'langForm', [eattribute_id, lang_id]), '', function(t) {
				$.facebox(t);
			});
		});
	};
	
	setUpLang=function(frm){ 
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('ExtraAttributes', 'langSetup'), data, function(t) {
			reloadList();				
			if ( t.lang_id > 0 ) {
				langForm(t.eattribute_id, t.lang_id);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	searchListing = function(form){
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		$("#listing").html('Loading....');
		
		fcom.ajax(fcom.makeUrl('ExtraAttributes','search'),data,function(res){
			$("#listing").html(res);
		});
	};
	
	deleteRecord=function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('ExtraAttributes','deleteRecord'),data,function(res){
			reloadList();
		});
	};
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchListing(document.frmSearch);
	};

})();
