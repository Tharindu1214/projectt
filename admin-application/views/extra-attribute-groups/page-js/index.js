$(document).ready(function(){
	searchExtraAttributeGroups(document.frmSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmExtraAttributeGroupSearchPaging;		
		$(frm.page).val(page);
		searchExtraAttributeGroups(frm);
	}

	reloadList = function() {
		var frm = document.frmExtraAttributeGroupSearchPaging;
		searchExtraAttributeGroups(frm);
	}

	extraAttributeGroupForm = function(id) {
		var frm = document.frmExtraAttributeGroupSearchPaging;			
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('extraAttributeGroups', 'form', [id]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};

	setupExtraAttributeGroup = function(frm) {
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('extraAttributeGroups', 'setup'), data, function(t) {
			reloadList();
			if (t.lang_id > 0) {
				extraAttributeGroupLangForm(t.eattrgroup_id, t.lang_id);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	extraAttributeGroupLangForm = function( eattrgroup_id, lang_id ) {		
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('extraAttributeGroups', 'langForm', [eattrgroup_id, lang_id]), '', function(t) {
				$.facebox(t);
			});
		});
	};
	
	setupExtraAttributeGroupLang = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('extraAttributeGroups', 'langSetup'), data, function(t) {
			reloadList();				
			if ( t.lang_id > 0 ) {
				extraAttributeGroupLangForm(t.eattrgroup_id, t.lang_id);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	searchExtraAttributeGroups = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$("#listing").html('Loading....');
		fcom.ajax(fcom.makeUrl('extraAttributeGroups','search'),data,function(res){
			$("#listing").html(res);
		});
	};
	
	deleteRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('extraAttributeGroups','deleteRecord'),data,function(res){
			reloadList();
		});
	};
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchExtraAttributeGroups(document.frmSearch);
	};

})();
