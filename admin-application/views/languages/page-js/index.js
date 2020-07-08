$(document).ready(function(){
	searchLanguage(document.frmLanguageSearch);
});

(function() {
	var runningAjaxReq = false;
	var dv = '#listing';
	
	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmLanguageSearchPaging;		
		$(frm.page).val(page);
		searchLanguage(frm);
	}

	reloadList = function() {
		var frm = document.frmLanguageSearchPaging;
		searchLanguage(frm);
	};	
	
	searchLanguage = function(form){		
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('Languages','search'),data,function(res){
			$(dv).html(res);			
		});
	};
	
	languageForm = function(id) {
		
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Languages', 'form', [id]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};
	
	setupLanguage = function (frm){
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Languages', 'setup'), data, function(t) {
			reloadList();
			$(document).trigger('close.facebox');
		});
	}
	
	editLanguageForm = function(languageId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Languages', 'form', [languageId]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};
	
	mediaForm = function (languageId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Languages', 'media', [languageId]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	}
	
	setImage = function(flag,languageId)
	{
		var languageId = parseInt(languageId);
		if(languageId < 1){
			$.mbsmessage(langLbl.invalidRequest,true,'alert--danger');
			return false;
		}
		data='languageId='+languageId+'&flag='+flag;
		fcom.ajax(fcom.makeUrl('Languages','updateImage'),data,function(res){
		var ans =$.parseJSON(res);
			$.mbsmessage(ans.msg,true,'alert--success');
			mediaForm(languageId);
			
		});
	}
	
	
	toggleStatus = function(e,obj){
		if(!confirm(langLbl.confirmUpdateStatus)){
			e.preventDefault();
			return;
		}
		var languageId = parseInt(obj.id);
		if(languageId < 1){
			$.mbsmessage(langLbl.invalidRequest,true,'alert--danger');
			return false;
		}
		data='languageId='+languageId;
		fcom.ajax(fcom.makeUrl('Languages','changeStatus'),data,function(res){
		var ans =$.parseJSON(res);
			if( ans.status == 1 ){
				$.mbsmessage(ans.msg,true,'alert--success');
				$(obj).toggleClass("active");
			}else{
				$.mbsmessage(ans.msg,true,'alert--danger');
			}
		});
	};
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchLanguage(document.frmSearch);
	};
})();	