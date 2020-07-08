$(document).ready(function(){
	listSubscriptionCartProducts();
});
(function() {
	listSubscriptionCartProducts = function(){
		$('#subsriptionCartList').html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('subscriptionCart','listing'),'',function(res){
			$("#subsriptionCartList").html(res);
		});
	};
	
	applyPromoCode  = function(frm){
		if( isUserLogged() == 0 ){
			loginPopUpBox();
			return false;
		}
		if (!$(frm).validate()) return;	
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('subscriptionCart','applyPromoCode'),data,function(res){
			listSubscriptionCartProducts();
		});
	 };
	 
	 removePromoCode  = function(){
		fcom.updateWithAjax(fcom.makeUrl('subscriptionCart','removePromoCode'),'',function(res){
			listSubscriptionCartProducts();
		});
	 };
})();