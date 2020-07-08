$(document).ready(function(){
	$(".div_tracking_number").hide();		
	
	$("select[name='op_status_id']").change(function(){
		var data = 'val='+$(this).val();
		fcom.ajax(fcom.makeUrl('Seller', 'checkIsShippingMode'), data, function(t) {			
			var response = $.parseJSON(t);
			if (response["shipping"]){
				$(".div_tracking_number").show();				
			}else{
				$(".div_tracking_number").hide();				
			}			
		});
	});
});

(function() {
	updateStatus = function(frm){
		if ( !$(frm).validate() ) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'changeOrderStatus'), data, function(t) {
			/* setTimeout(location.reload.bind(location), 1000); */
			setTimeout("pageRedirect("+t.op_id+")", 1000);
		});
	};	
})();

function pageRedirect(op_id) {
	window.location.replace(fcom.makeUrl('Seller', 'viewOrder',[op_id]));
}