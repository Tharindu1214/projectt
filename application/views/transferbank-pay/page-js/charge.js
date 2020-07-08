var confirmPayment = function(frm){
	var me = $(frm);
	if ( me.data('requestRunning') ) {
		return;
	}
	if (!me.validate()) return;
	var data = fcom.frmData(frm);
	var action = me.attr('action');
	fcom.ajax(action, data, function(t) {
		try{
			var json = $.parseJSON(t);
			var el = $('#ajax_message');
			if (json['error']) {
				el.html('<div class="alert alert--danger">'+json['error']+'<div>');
			}
			if (json['redirect']) {
				$(location).attr("href",json['redirect']);
			}
		}catch(exc){
			console.log(t);
		}
	});	
};