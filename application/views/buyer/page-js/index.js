$(document).ready(function(){
	personalInfo();
	searchOrders(document.frmOrderSrch);
	
	/******** for tooltip ****************/ 

	$('.info--tooltip-js').hover(function(){
		$(this).toggleClass("is-active");
		return false; 
	},function(){
		$(this).toggleClass("is-active");
		return false; 
	});
});
(function() {
	var tabListing = "#tabListing";
	
	searchOrders = function(frm){
		var data = fcom.frmData(frm);
		$("#ordersListing").html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Buyer','orderSearchListing'), data, function(res){
			$("#ordersListing").html(res);
		}); 
	};
	
	personalInfo = function(el){
		$(tabListing).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Account','personalInfo'), '', function(res){
			$(tabListing).html(res);
			$(el).parent().siblings().removeClass('is-active');
			$(el).parent().addClass('is-active');
		});
	};
	
	bankInfo = function(el){
		$(tabListing).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Account','bankInfo'), '', function(res){
			$(tabListing).html(res);
			$(el).parent().siblings().removeClass('is-active');
			$(el).parent().addClass('is-active');
		});
	};
	
	returnAddress = function(el){
		$(tabListing).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Account','returnAddress'), '', function(res){
			$(tabListing).html(res);
			$(el).parent().siblings().removeClass('is-active');
			$(el).parent().addClass('is-active');
		});
	};
})();	