$(document).ready(function(){
	searchOrders(document.frmOrderSrch);
});

(function() {
	
	var runningAjaxReq = false;
		
	checkRunningAjax = function(){
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
	};
	
	searchOrders = function(frm){
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = fcom.frmData(frm);
		/*]*/
		
		$("#ordersListing").html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Seller','orderSearchListing'), data, function(res){
			$("#ordersListing").html(res);
		}); 
	};
	
	
	toggleAutoRenewal = function(){
	
		checkRunningAjax();
		
		fcom.updateWithAjax(fcom.makeUrl('Seller','toggleAutoRenewalSubscription'),'',function(res){
			runningAjaxReq = false;
			
			if(res.autoRenew){
				$(".switch-button").addClass('is--active');
			}else{
				$(".switch-button").removeClass('is--active');
			}
			
		});
	};
	$(document).on('click','.auto-renew-js',function(){
	
		toggleAutoRenewal();
	});
	
	goToOrderSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmOrderSrchPaging;		
		$(frm.page).val(page);
		searchOrders(frm);
	};
	
	clearSearch = function(){
		document.frmOrderSrch.reset();
		searchOrders(document.frmOrderSrch);
	};
	
})();