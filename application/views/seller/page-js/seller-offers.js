$(document).ready(function(){
	searchOffers(document.frmOfferSrch);
});
(function() {

	searchOffers = function(frm){
		var data = fcom.frmData(frm);
		$("#listing").html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Seller','searchSellerOffers'), data, function(res){
			$("#listing").html(res);
		});
	};
})();
