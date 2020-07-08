var submitBtnNode = null;
(function($){
	try{
		var $ajax = true;
		$(window).on('load',function(){
			$(document).on('click', '.amazon-submit', function(){
				if(typeof orderRefId != typeof undefined && typeof orderId != typeof undefined){
					if(orderRefId != '' && orderId != ''){
						if($ajax){
							$ajax = false;
							submitBtnNode = $(this);
							submitBtnNode.text(submitBtnNode.data('processing-txt'));
							
							fcom.ajax(fcom.makeUrl('AmazonPay', 'doPayment', [orderId]), 'amazon_order_reference_id='+orderRefId, function(data){
								submitBtnNode.text(submitBtnNode.data('ready-txt'));
								$ajax = true;
								var jsonObj = $.parseJSON(data);
								if(jsonObj.hasOwnProperty('status') && jsonObj.hasOwnProperty('msg')){
									if(jsonObj.status == 0 && jsonObj.msg != ''){
										
										var txt = '';
										$.each(jsonObj.msg, function(key, errorObj){
											if(errorObj.hasOwnProperty('Error')){
												if(errorObj.Error.hasOwnProperty('Message')){
													txt += '<p>'+errorObj.Error.Message+'</p>';
												}
											}
										});
										
										if(txt != ''){
											if($('.payment-from').find('.error-wrap.error').length > 0){
												$('.payment-from .error-wrap.error').html(txt);
											}else{
												$('.payment-from').prepend('<div class="error-wrap error">'+txt+'</div>');
											}
										}
										
									}else if(jsonObj.status == 1 && jsonObj.msg != ''){
										
										if(typeof redirectAfterSuccess != typeof undefined){
											window.location.href = redirectAfterSuccess;
										}
										
									}
								}
							});
						}
					}
				}
				return false;
			});
		});
	} catch(e) {
		console.log(e.message);
		console.log(data);
		logout();
	}
})(jQuery);