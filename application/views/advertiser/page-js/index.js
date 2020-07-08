(function() {
	setUpWalletRecharge = function( frm ){
		if (!$(frm).validate()) return;	
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Account', 'setUpWalletRecharge'), data, function(t) {
			if(t.redirectUrl){
				window.location = t.redirectUrl;
			}
		});	
	}
})();