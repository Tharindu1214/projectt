$(document).ready(function(){
	searchBatches( document.frmBatchSearch );
});

(function() {
	var runningAjaxReq = false;
	searchBatches = function( frm ){
		$('#listing').html(fcom.getLoader());
		var data = fcom.frmData(document.frmBatchSearch);
		fcom.ajax(fcom.makeUrl( 'BatchProducts', 'search'), data, function(t) {
			$('#listing').html(t);
		});
	}
	
	batchForm = function( prodgroup_id ){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('BatchProducts', 'form', [prodgroup_id]), '', function(t) {
				$.facebox( t,'faceboxWidth');
			});
		});
	}
	
	batchLangForm =  function( prodgroup_id, lang_id ){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('BatchProducts', 'langForm', [prodgroup_id, lang_id]), '', function(t) {
				$.facebox( t,'faceboxWidth');
			});
		});
	}
	
	setUpBatch = function(frm){
		if (!$(frm).validate()) return;
		if( runningAjaxReq == true ){
			return;
		}
		runningAjaxReq = true;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('BatchProducts', 'setUpBatch'), data, function(t) {
			runningAjaxReq = false;
			searchBatches(frm);
			if ( t.lang_id > 0 ) {
				batchLangForm( t.prodgroup_id, t.lang_id );
				return ;
			}
			if( t.openMediaForm ){
				batchMediaForm( t.prodgroup_id );
				return;
			}
			$(document).trigger('close.facebox');
			
			return;
			
		});
		return false;
	}
	
	setUpLangBatch = function( frm ){
		if (!$(frm).validate()) return;
		if( runningAjaxReq == true ){
			return;
		}
		runningAjaxReq = true;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('BatchProducts', 'setUpLangBatch'), data, function(t) {
			runningAjaxReq = false;
			if ( t.lang_id > 0 ) {
				batchLangForm( t.prodgroup_id, t.lang_id );
				return ;
			}
			if( t.openMediaForm ){
				batchMediaForm( t.prodgroup_id );
				return;
			}
			$(document).trigger('close.facebox');
			searchBatches(frm);
			return;
		});
		return false;
	}
	
	batchProductsForm = function( prodgroup_id ){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('BatchProducts', 'batchProductsForm', [prodgroup_id]), '', function(t) {
				$.facebox( t,'faceboxWidth');
				reloadBatchProducts( prodgroup_id );
			});
		});
	}
	
	reloadBatchProducts = function( prodgroup_id ){
		$("#productsList").html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('BatchProducts', 'loadBatchProducts', [prodgroup_id]), '', function(t) {
			$("#productsList").html(t);
		});
	}
	
	updateProductToGroup = function( prodgroup_id, selprod_id ){
		fcom.updateWithAjax(fcom.makeUrl('BatchProducts', 'updateProductToGroup'), 'prodgroup_id='+prodgroup_id+'&selprod_id='+selprod_id, function(t) {
			//$.mbsmessage.close();
			reloadBatchProducts( prodgroup_id );
		});
	}
	
	removeProductFromGroup = function( prodgroup_id, selprod_id ){
		var agree = confirm(langLbl.confirmRemove);
		if( !agree ){ return false; }
		fcom.updateWithAjax(fcom.makeUrl('BatchProducts', 'removeProductToGroup'), 'prodgroup_id='+prodgroup_id+'&selprod_id='+selprod_id, function(t) {
			//$.mbsmessage.close();
			reloadBatchProducts( prodgroup_id );
		});
	}
	
	setMainProductFromGroup = function( prodgroup_id, selprod_id ){
		var agree = confirm(langLbl.setMainProduct);
		if( !agree ){ return false; }
		fcom.updateWithAjax(fcom.makeUrl('BatchProducts', 'setMainProductFromGroup'), 'prodgroup_id='+prodgroup_id+'&selprod_id='+selprod_id, function(t) {
			//$.mbsmessage.close();
			reloadBatchProducts( prodgroup_id );
		});
	}
	
	batchMediaForm = function( prodgroup_id ){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('BatchProducts', 'batchMediaForm', [prodgroup_id]), '', function(t) {
				$.facebox( t,'faceboxWidth');
				//reloadBatchProducts( prodgroup_id );
			});
		});
	}
	
	removeBatchImage = function(prodgroup_id, lang_id){
		if( !confirm( langLbl.confirmRemove ) ){ return; }
		data = 'prodgroup_id=' + prodgroup_id + '&lang_id=' + lang_id;
		fcom.updateWithAjax(fcom.makeUrl('BatchProducts','removeBatchImage'),data,function(res){
			batchMediaForm(prodgroup_id);
		});
	}
})();



$(document).on('click','.prodgroup-Js',function(){
	var node = this;
	$('#form-upload').remove();
	var fileType = $(node).attr('data-file_type');
	var prodgroup_id = $(node).attr("data-prodgroup_id");
	var lang_id = document.frmBatchMedia.lang_id.value;
	var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
	frm = frm.concat('<input type="file" name="file" />'); 
	frm = frm.concat('<input type="hidden" name="lang_id" value="'+lang_id+'">'); 
	frm = frm.concat('<input type="hidden" name="prodgroup_id" value="'+prodgroup_id+'">'); 
	frm = frm.concat('</form>'); 
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
				url: fcom.makeUrl('BatchProducts', 'uploadBatchImage'),
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
					$.mbsmessage(ans.msg, true, 'alert--success');
					$('.text-danger').remove();
					$('#input-field'+fileType).html(ans.msg);						
					if(ans.status == true){
						$('#input-field'+fileType).removeClass('text-danger');
						$('#input-field'+fileType).addClass('text-success');
						batchMediaForm( prodgroup_id );
					}else{
						$('#input-field'+fileType).removeClass('text-success');
						$('#input-field'+fileType).addClass('text-danger');
					}
					
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
				});			
		}
	}, 500);
});