$(document).ready(function(){
	listMetaTags('default');
});

(function() {
	var runningAjaxReq = false;
	var dv = '#listing';
	
	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmMetaTagSearchPaging;		
		$(frm.page).val(page);
		searchMetaTag(frm);
	}

	reloadList = function() {
		var frm = document.frmMetaTagSearchPaging;
		searchMetaTag(frm);
	};	
	
	listMetaTags = function(metaType) {
		metaType = metaType||'';
		fcom.ajax(fcom.makeUrl('MetaTags','listMetaTags' ,[metaType]),'',function(res){
			$('#frmBlock').html(res);
			searchMetaTag(document.frmSearch);
		});
	};	

	searchMetaTag = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$(dv).html(fcom.getLoader());
		
			fcom.ajax(fcom.makeUrl('MetaTags','search'),data,function(res){
			$(dv).html(res);			
		});
	};
	addMetaTagForm = function(id , metaType, recordId) {
		
		$.facebox(function() { 	metaTagForm(id , metaType, recordId);
		});
	};
	
	metaTagForm = function(id, metaType, recordId) {
			fcom.displayProcessing();
			//$.facebox(function() {
			fcom.ajax(fcom.makeUrl('MetaTags', 'form', [id ,metaType, recordId]), '', function(t) {
				//$.facebox(t,'faceboxWidth');
				fcom.updateFaceboxContent(t);
			});
			//});
	};
	editMetaTagFormNew = function(id ,metaType, recordId){
			
			$.facebox(function() {editMetaTagForm(id ,metaType, recordId);});
	};
		

	editMetaTagForm = function(id ,metaType, recordId){
		fcom.displayProcessing();
		//$.facebox(function() {
			fcom.ajax(fcom.makeUrl('MetaTags', 'form', [id ,metaType, recordId]), '', function(t) {
				//$.facebox(t,'faceboxWidth');
				fcom.updateFaceboxContent(t);
			});
		//});
	};
	

	setupMetaTag = function (frm){
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('MetaTags', 'setup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				editMetaTagLangForm(t.metaId, t.langId, t.metaType);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	}
	
	editMetaTagLangForm = function(metaId,langId, metaType){
		fcom.displayProcessing();
	//	$.facebox(function() {
			fcom.ajax(fcom.makeUrl('MetaTags', 'langForm', [metaId,langId,metaType]), '', function(t) {
				//$.facebox(t,'faceboxWidth');
				fcom.updateFaceboxContent(t);
			});
		//});
	};
	
	setupLangMetaTag = function (frm , metaType){
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('MetaTags', 'langSetup'), data, function(t) {
			reloadList();			
			if (t.langId>0) {
				editMetaTagLangForm(t.metaId, t.langId,metaType);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};
	
	deleteRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='metaId='+id;
		fcom.updateWithAjax(fcom.makeUrl('MetaTags', 'deleteRecord'),data,function(res){		
			reloadList();
		});
	};
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchMetaTag(document.frmSearch);
	};
})();