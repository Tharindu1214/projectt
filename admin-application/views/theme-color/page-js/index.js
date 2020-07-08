$(document).ready(function(){
	searchThemeColor(document.frmThemeColorSearch);
});

(function() {
	var runningAjaxReq = false;
	var dv = '#listing';
	
	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmThemeColorSearchPaging;		
		$(frm.page).val(page);
		searchThemeColor(frm);
	}

	reloadList = function() {
		var frm = document.frmThemeColorSearchPaging;
		searchThemeColor(frm);
	};	
	

	redirectPreview= function(redirecrt){

		var url=	SITE_ROOT_URL +''+redirecrt;
        window.open(url,'_blank');
	}
	searchThemeColor = function(form){		
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('ThemeColor','search'),data,function(res){
			
			$(dv).html(res);			
		});
	};
	
	ThemeColorForm = function(id) {
		
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('ThemeColor', 'form', [id]), '', function(t) {
				
				$.facebox(t,'faceboxWidth');
				jscolor.installByClassName('jscolor');
				
			});
		});
	};

		editThemeColorFormNew = function(tColorId){
		$.facebox(function() {editThemeColorForm(tColorId);
		});
	};
	editThemeColorForm = function(tColorId){
		fcom.displayProcessing();
		//$.facebox(function() {
			fcom.ajax(fcom.makeUrl('ThemeColor', 'form', [tColorId]), '', function(t) {
				fcom.updateFaceboxContent(t);
				//$.facebox(t,'faceboxWidth');
				jscolor.installByClassName('jscolor');
			});
		//});
	};
	cloneForm = function(tColorId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('ThemeColor', 'cloneForm', [tColorId]), '', function(t) {
				$.facebox(t,'faceboxWidth');
				jscolor.installByClassName('jscolor');
			});
		});
	};
	
	setupThemeColor = function (frm){
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('ThemeColor', 'setup'), data, function(t) {			
			
			reloadList();
			if (t.langId>0) {
				editThemeColorLangForm(t.tColorId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	}
	
	editThemeColorLangForm = function(tColorId,langId){
		fcom.displayProcessing();
		//$.facebox(function() {
			fcom.ajax(fcom.makeUrl('ThemeColor', 'langForm', [tColorId,langId]), '', function(t) {
				//$.facebox(t,'faceboxWidth');
				fcom.updateFaceboxContent(t);
			});
		//});
	};
	
	setupLangThemeColor = function (frm){
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('ThemeColor', 'langSetup'), data, function(t) {			
		
			if (t.langId>0) {
				editThemeColorLangForm(t.tColorId, t.langId);
				return ;
			}			
			$(document).trigger('close.facebox');
		});
	};
	ActivateTheme  = function(tColorId){
		if(!confirm(langLbl.confirmActivate)){return;}
	
		if(tColorId < 1){
			$.mbsmessage(langLbl.invalidRequest,true,'alert--danger');
			return false;
		}
		data='tColorId='+tColorId;
		fcom.updateWithAjax(fcom.makeUrl('ThemeColor','activateThemeColor'),data,function(res){
					
				reloadList(); 
			
		});
	}
	toggleStatus = function(obj){
		
		if(!confirm(langLbl.confirmUpdate)){return;}
		var tColorId = parseInt(obj.id);
		if(tColorId < 1){
			$.mbsmessage(langLbl.invalidRequest,true,'alert--danger');
			return false;
		}
		data='tColorId='+tColorId;
		fcom.ajax(fcom.makeUrl('ThemeColor','changeStatus'),data,function(res){
		var ans =$.parseJSON(res);
			if(ans.status == 1){
				$(obj).toggleClass("active");
				setTimeout(function(){ reloadList(); }, 1000);
				fcom.displaySuccessMessage(ans.msg);				
			}else{
				fcom.displayErrorMessage(ans.msg);				
			}
		});
	};
	deleteTheme = function(tColorId){
		
		if(!confirm(langLbl.confirmDelete)){return;}
		
		if(tColorId < 1){
			$.mbsmessage(langLbl.invalidRequest,true,'alert--danger');
			return false;
		}
		data='tColorId='+tColorId;
		fcom.ajax(fcom.makeUrl('ThemeColor','deleteTheme'),data,function(res){
		var ans =$.parseJSON(res);
			if(ans.status == 1){ 
				fcom.displaySuccessMessage(ans.msg);							
				setTimeout(function(){ reloadList(); }, 1000);
			}else{
				fcom.displayErrorMessage(ans.msg);			
			}
		});
	};
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchThemeColor(document.frmSearch);
	};
})();	