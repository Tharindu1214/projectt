$(document).ready(function(){
	searchMessages(document.frmSearch);
});
(function() {
	var runningAjaxReq = false;

	searchMessages = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$("#listing").html('Loading....');
		fcom.ajax(fcom.makeUrl('Messages','searchMessages'),data,function(res){
			$("#listing").html(res);
		});
	};
	
	messageForm = function(messageId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Messages', 'form', [messageId]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};
	
	setupMessage  = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Messages', 'setupMessage'), data, function(t) {					
			searchMessages(document.frmSearch);
			$(document).trigger('close.facebox');
		});
	};
	
	deleteRecord=function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('Messages','deleteRecord'),data,function(res){		
			searchMessages(document.frmSearch);
		});
	};
	
})();