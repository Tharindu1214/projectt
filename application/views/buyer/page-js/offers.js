$(document).ready(function(){
	searchOffers(document.frmOfferSrch);
});
(function() {
	
	searchOffers = function(frm){
		var data = fcom.frmData(frm);
		$("#listing").html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Buyer','searchOffers'), data, function(res){
			$("#listing").html(res);
		}); 
	};
})();