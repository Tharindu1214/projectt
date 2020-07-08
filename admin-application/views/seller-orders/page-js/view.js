$(document).ready(function(){
	
	$(".div_tracking_number").hide();		
	
	$("select[name='op_status_id']").change(function(){
		var data = 'val='+$(this).val();
		fcom.ajax(fcom.makeUrl('SellerOrders', 'checkIsShippingMode'), data, function(t) {			
			var response = $.parseJSON(t);
			if (response["shipping"]){
				$(".div_tracking_number").show();				
			}else{
				$(".div_tracking_number").hide();				
			}			
		});
	});
	
	$(document).on('click','ul.linksvertical li a.redirect--js',function(event){
		event.stopPropagation();
	});		
	
});
function pageRedirect(op_id) {
	window.location.replace(fcom.makeUrl('SellerOrders', 'view',[op_id]));
}
(function() {
	updateStatus = function(frm){		
		if (!$(frm).validate()) return;
		var op_id = $(frm.op_id).val();		
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('SellerOrders', 'changeOrderStatus'), data, function(t) {				
			/* window.location.href = fcom.makeUrl('SellerOrders', 'view',[op_id]); */
			setTimeout("pageRedirect("+op_id+")", 1000);
		});
	};	
	
	updateShippingCompany = function(frm){
		var data = fcom.frmData(frm);	
		var op_id = $(frm.op_id).val();				
		if (!$(frm).validate()) return;
		fcom.updateWithAjax(fcom.makeUrl('SellerOrders', 'updateShippingCompany'), data, function(t) {
			/* window.location.href = fcom.makeUrl('SellerOrders', 'view',[op_id]); */
			setTimeout("pageRedirect("+op_id+")", 1000);
		});
	};
})();