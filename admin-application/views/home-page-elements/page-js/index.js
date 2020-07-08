(function() {
	bannerLocation = function(blocationId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Banners', 'bannerLocation', [blocationId]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};
	
	setupLocation = function(frm){ 
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Banners', 'setupLocation'), data, function(t) {								
			reloadList();			
			$(document).trigger('close.facebox');
		});
	};
	
	
	updateStatusForm = function(epageId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('ContentBlock', 'updateStatusForm', [epageId]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};
	
	updateStatus = function(frm){ 
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('ContentBlock', 'updateStatus'), data, function(t) {			
			$(document).trigger('close.facebox');
		});
	};
})();