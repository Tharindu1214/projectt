(function() {

	
	forgotPassword = function(frm, v) {
		if (!$(frm).validate()) { return; }
		if (!v.isValid()){
			/* $('ul.errorlist').each(function(){
				$(this).parents('.field_control:first').addClass('error');
			}); */
			return; 
		}
		var data = fcom.frmData(frm);				
		fcom.updateWithAjax(fcom.makeUrl("adminGuest", "forgotPassword"), data, function(t) {
			if(t.status){
				$.systemMessage(t.msg,'alert--success');
				frm.reset();
			}
			else
			{
				$.systemMessage(t.msg,'alert--danger');
			}
		});  
		if($(".g-recaptcha").html()){			
			grecaptcha.reset();
		}
		return false;
	}
})();
