(function() {
	setupOrderCancelRequest = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Buyer', 'setupOrderCancelRequest'), data, function(t) {
			document.frmOrderCancel.reset();
			setTimeout(function() { window.location.href = fcom.makeUrl('Buyer', 'orderCancellationRequests'); }, 2000);
		});
	};
})();
