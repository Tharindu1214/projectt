/* var sendPayment = function(frm){
	if (!$(frm).validate()) return;
	var data = fcom.frmData(frm);
	var action = $(frm).attr('action');
	fcom.ajax(action, data, function(t) {
		debugger;
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
 */
(function($){
	var _this			= false;
	var _subText 		= false;
	$(document).ready(function() {
		$(window).on('load',function(){
			try{
				
				if(typeof publishable_key != typeof undefined){
					// this identifies your website in the createToken call below
					Stripe.setPublishableKey(publishable_key);
					function stripeResponseHandler(status, response) {
						
						$submit = true;
						if(_this && _subText){
							_this.find('input[type=submit]').val(_subText);
						}
						
						if (response.error) {
							$("#frmPaymentForm").prepend('<div class="alert alert--danger">'+response.error.message+'</div>');
						} else {
							
							var form$ = $("#frmPaymentForm");
							// token contains id, last4, and card type
							var token = response['id'];
							// insert the token into the form so it gets submitted to the server
							form$.append("<input type='hidden' name='stripeToken' value='" + token + "' />");
									// and submit
							form$.get(0).submit();
							
						}
						
					}
					$submit = true;
					$("#frmPaymentForm").submit(function(event) {
						
						$('.alert--danger').remove();
						
						_this			= $(this);
						var _numberWrap 	= $('#cc_number');
						var _cvvWrap	 	= $('#cc_cvv');
						var _expMonthWrap 	= $('#cc_expire_date_month');
						var _expYearWrap 	= $('#cc_expire_date_year');
						_subText 		= _this.find('input[type=submit]').val();
						
						
						if($submit && _numberWrap.length > 0 && _cvvWrap.length > 0 && _expMonthWrap.length > 0 && _expYearWrap.length > 0 ){
							
							var _numberValue 	= _numberWrap.val().trim();
							var _cvvValue 		= _cvvWrap.val().trim();
							var _expMonthValue 	= _expMonthWrap.val().trim();
							var _expYearValue 	= _expYearWrap.val().trim();
							
							if( _numberValue != '' && _cvvValue != '' && _expMonthValue != '' && _expYearValue != '' ){
								$submit = false;
								_subText = _this.find('input[type=submit]').val();
								_this.find('input[type=submit]').val(_this.find('input[type=submit]').data('processing-text'));
								
								Stripe.createToken({
									number: _numberValue,
									cvc: _cvvValue,
									exp_month: _expMonthValue,
									exp_year: _expYearValue
								}, stripeResponseHandler);
							}
							
						}
						return $submit; // submit from callback
					});
					
				}
				
			}catch(e){
				console.log(e.message);
			}
		});
		
		$("#cc_number" ).keydown(function() {
			var obj = $(this);
			var cc = obj.val();
			obj.attr('class','p-cards');
			if(cc != ''){
				var card_type = getCardType(cc).toLowerCase();
				obj.addClass('p-cards ' + card_type );	
				/* var data="cc="+cc;
				fcom.ajax(fcom.makeUrl('AuthorizeAimPay', 'checkCardType'), data, function(t){
					var ans = $.parseJSON(t);
					var card_type = ans.card_type.toLowerCase();
					obj.addClass('type-bg p-cards ' + card_type );
				}); */
			}
		});
		
	});
})(jQuery);