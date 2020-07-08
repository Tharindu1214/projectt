(function() {
	cancelReason = function(frm){		
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('SellerOrders', 'cancelReason'), data, function(t) {			
			window.location.href = fcom.makeUrl('SellerOrders');
		});
	};	
})();