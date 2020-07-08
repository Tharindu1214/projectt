$(document).ready(function() {
    searchGateway(document.frmGatewaySearch);
});

(function() {
	var runningAjaxReq = false;
	var dv = '#pMethodListing';

	reloadList = function() {
		var frm = document.frmGatewaySearch;
		searchGateway(frm);
	};

	searchGateway = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$(dv).html(fcom.getLoader());

		fcom.ajax(fcom.makeUrl('PaymentMethods','search'),data,function(res){
			$(dv).html(res);
		});
	};

	editGatewayForm = function(pMethodId){
		$.facebox(function() {
			gatewayForm(pMethodId);
		});
	};

	gatewayForm = function(pMethodId){
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('PaymentMethods', 'form', [pMethodId]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	}


	setupGateway = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('PaymentMethods', 'setup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				editGatewayLangForm(t.pMethodId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	}

	editGatewayLangForm = function(pMethodId,langId){
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('PaymentMethods', 'langForm', [pMethodId,langId]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};

	setupLangGateway = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('PaymentMethods', 'langSetup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				editGatewayLangForm(t.pMethodId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	settingsForm = function (code){
		$.facebox(function() {
			editSettingForm(code);
		});
	};

	editSettingForm = function (code){
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl(code+'-settings'), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};

	setupPaymentSettings = function (frm,code){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl(code+'-settings', 'setup'), data, function(t) {
			$(document).trigger('close.facebox');
		});
	};

	toggleStatus = function( obj ){
		if( !confirm(langLbl.confirmUpdateStatus) ){ return; }
		var pmethodId = parseInt(obj.id);
		if( pmethodId < 1 ){
			fcom.displayErrorMessage(langLbl.invalidRequest);
			return false;
		}
		data = 'pmethodId='+pmethodId;
		fcom.ajax(fcom.makeUrl('PaymentMethods','changeStatus'),data,function(res){
			var ans =$.parseJSON(res);
			if(ans.status == 1){
				fcom.displaySuccessMessage(ans.msg);
				$(obj).toggleClass("active");
				setTimeout(function(){ reloadList(); }, 1000);
			}else{
				fcom.displayErrorMessage(ans.msg);
			}
		});
	};

})();

$(document).on('click','.uploadFile-Js',function(){
	var node = this;
	$('#form-upload').remove();
	var pmethod_id = $(node).attr('data-pmethod_id');
	var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
	frm = frm.concat('<input type="file" name="file" />');
	frm = frm.concat('<input type="hidden" name="pmethod_id" value="'+pmethod_id+'"/>');
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
				url: fcom.makeUrl('PaymentMethods', 'uploadIcon',[$('#form-upload input[name=\'pmethod_id\']').val()]),
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
						$('.text-danger').remove();
						$('#gateway_icon').html(ans.msg);
						if(ans.status == true){
							$('#gateway_icon').removeClass('text-danger');
							$('#gateway_icon').addClass('text-success');
							//editGatewayForm(ans.pmethodId);
						}else{
							$('#gateway_icon').removeClass('text-success');
							$('#gateway_icon').addClass('text-danger');
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
		}
	}, 500);
});
