$(document).ready(function(){
	reloadList();	
});

(function() {	
	reloadList = function (){		
		$('#listing').html(fcom.getLoader());	
		var data = '';		
		fcom.ajax(fcom.makeUrl('Users','sellerFormFieldsList'),data,function(res){
			$('#listing').html(res);
		});	
	};
	
	addFormFields = function(id){
		fcom.updateFaceboxContent
		$.facebox(function() {
			formFileds(id);
		}); 
	};
	
	formFileds = function(id){
		fcom.displayProcessing();	
		fcom.ajax(fcom.makeUrl('Users', 'sellerApprovalForm',[id]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});		
	};
	
	setupFormFields = function(frm){
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Users', 'setupSellerForm'), data, function(t) {			
			reloadList();
			if (t.langId > 0) {
				addLangFormFields(t.sformfieldId, t.langId);
				return false;
			}	
			$(document).trigger('close.facebox');
		});
	};	
	
	addLangFormFields = function(sformfieldId, langId) {	
		fcom.displayProcessing();	
		fcom.ajax(fcom.makeUrl('Users', 'langSellerApprovalForm', [sformfieldId, langId]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};
	
	setupLangFormFields =  function (frm){ 
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('Users', 'setupSellerLangForm'), data, function(t) {			
			reloadList();				
			if (t.langId>0) {
				addLangFormFields(t.sformfieldId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};
	
	deleteFieldsRecord=function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.ajax(fcom.makeUrl('Users','deleteFormField'),data,function(res){		
			reloadList();
		});
	};
	
})();