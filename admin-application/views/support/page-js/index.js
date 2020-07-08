(function() {
	reportIssue = function( frm ){
		if (!$(frm).validate()) return;	
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Support', 'reportIssue'), data, function(t) {
			frm.reset();
		});	
	}
})();