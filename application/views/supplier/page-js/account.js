$(document).ready(function(){
	sellerRegistrationForm();
});

(function() {
	var dv = '#regFrmBlock';

	sellerRegistrationForm = function(){
		$(dv).html( fcom.getLoader() );
		var frm = document.frmSellerAccount;
		var data = fcom.frmData(frm);
		fcom.ajax(fcom.makeUrl('Supplier', 'form'), data, function(t) {
			$(dv).html(t);
		});
	};

	register = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
        $.mbsmessage(langLbl.processing, false, 'alert--process alert');
		fcom.updateWithAjax(fcom.makeUrl('Supplier', 'register'), data, function(t) {
			//$.mbsmessage.close();
			if(t.userId > 0){
				profileActivationForm();
			}
		});
	};

	profileActivationForm = function(){
		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Supplier', 'profileActivationForm'), '', function(t) {
			$(dv).html(t);
		});
	}

	setupSupplierApproval = function (frm){
		if (!$(frm).validate()){
			return;
		}
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Supplier', 'setupSupplierApproval'), data, function(t) {
			//$.mbsmessage.close();
			if(t.userId > 0){
				profileConfirmation();
			}
		});
	};

	profileConfirmation = function(){
		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Supplier', 'profileConfirmation'), '', function(t) {
			$(dv).html(t);
			//fcom.scrollToTop(dv);
			window.scrollTo(0,0);
		});
	};
})();

$(document).on('click','.fileType-Js',function(){
	var node = this;
	$('#form-upload').remove();
	var fieldId = $(node).attr('data-field_id');
	var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
	frm = frm.concat('<input type="file" name="file" />');
	frm = frm.concat('<input type="hidden" name="field_id" value="'+fieldId+'"></form>');
	$('body').prepend(frm);
	$('#form-upload input[name=\'file\']').trigger('click');
	if (typeof timer != 'undefined') {
		clearInterval(timer);
	}
	timer = setInterval(function() {
		if ($('#form-upload input[name=\'file\']').val() != '') {
			clearInterval(timer);
			$val = $(node).val();
			$.ajax({
				url: fcom.makeUrl('Supplier', 'uploadSupplierFormImages'),
				type: 'post',
				dataType: 'json',
				data: new FormData($('#form-upload')[0]),
				cache: false,
				contentType: false,
				processData: false,
				beforeSend: function() {
					$(node).val('Loading');
				},
				complete: function() {
					$(node).val($val);
				},
				success: function(ans) {
                        /* $('.text-danger').remove(); */
						$('#input-sformfield'+fieldId).html(ans.msg);
						$('#sformfield_'+fieldId).val(ans.file);						
						if(ans.status == true){
							$('#input-sformfield'+fieldId).removeClass('text-danger');
							$('#input-sformfield'+fieldId).addClass('text-success');
						}else{
							$('#input-sformfield'+fieldId).removeClass('text-success');
							$('#input-sformfield'+fieldId).addClass('text-danger');
						}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
				});
		}
	}, 500);
});
