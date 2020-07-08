(function() {
	oPWalletTransactions = function( op_id ){
		$.facebox( function() {
			fcom.ajax(fcom.makeUrl('shippingCompanyUsers', 'userWalletTransactions'), '&op_id='+op_id, function(t) {
				fcom.updateFaceboxContent(t);
			});
		});
	};
})();