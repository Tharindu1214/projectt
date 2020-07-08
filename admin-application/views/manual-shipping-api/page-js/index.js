$(document).ready(function(){
	searchManualShipping(document.frmManualShippingSearch);
});
(function(){
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';
	
	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmManualShippingSrchPaging;		
		$(frm.page).val(page);
		searchManualShipping(frm);
	};
	
	reloadList = function() {
		var frm = document.frmManualShippingSrchPaging;
		searchManualShipping(frm);
	};
	
	searchManualShipping = function(form){		
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('ManualShippingApi','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	manualShippingForm =  function (mshipapiId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('ManualShippingApi', 'form', [mshipapiId]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};
	
	setupManualShippingApi = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('ManualShippingApi', 'setup'), data, function(t) {						
			reloadList();
			if (t.langId>0) {
				manualShippingLangForm(t.mshipapiId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};
	
	manualShippingLangForm = function(mshipapiId, langId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('ManualShippingApi', 'langForm', [mshipapiId, langId]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};	
	
	setupManualShippingApiLang = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('ManualShippingApi', 'langSetup'), data, function(t) {	
			reloadList();				
			if (t.langId>0) {
				manualShippingLangForm(t.mshipapiId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};
	
	getCountryStates = function(countryId,stateId,dv){ 
		fcom.ajax(fcom.makeUrl('ManualShippingApi','getStates',[countryId,stateId]),'',function(res){
			$(dv).empty();
			$(dv).append(res);
		});
	};
	
	clearSearch = function(){		
		document.frmManualShippingSearch.reset();		
		searchManualShipping(document.frmManualShippingSearch);
	};
	
	deleteRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.ajax(fcom.makeUrl('ManualShippingApi','deleteRecord'),data,function(res){		
			reloadList();
		});
	};
	
})()