

var subscription = {
	add: function( spPlanId , isRedirectToCart ){
		if(spPlanId==0){
			window.location.href= fcom.makeUrl('seller','packages');
		}
		isRedirectToCart = (typeof(isRedirectToCart) != 'undefined') ? true : false;
	
		var data = 'spplan_id=' + spPlanId;
		
		fcom.updateWithAjax(fcom.makeUrl('SubscriptionCart','add'), data ,function(ans){
			if(isRedirectToCart){
				window.location=fcom.makeUrl('SubscriptionCheckout');
			}
			setTimeout(function () {
				$.mbsmessage.close();
			}, 3000);
			
			
		});
	},
	remove: function (key){
		if(confirm( langLbl.confirmRemove )){
		
			var data = 'key=' + key ;
			fcom.updateWithAjax(fcom.makeUrl('SubscriptionCart','remove'), data ,function(ans){
				if( ans.status ){
					window.location.href= fcom.makeUrl('seller','packages');
				}
				$.mbsmessage.close();
			});
		}
	},
	
	
};