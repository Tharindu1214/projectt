$(document).ready(function(){
	inventoryUpdateForm();		
});

(function() {
	var runningAjaxReq = false;
	var dv = '#productInventory';
	var fileResult = '#fileResult';
	
	inventoryUpdateForm = function(){				
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Seller', 'InventoryUpdateForm'), '', function(t) {			
			$(dv).html(t);
		});
	};
	
})();

$(document).on('click','.csvFile-Js',function(){
	var node = this;
	$('#form-upload').remove();
	var lang_id = document.frmInventoryUpdate.lang_id.value;
	var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
	frm = frm.concat('<input type="file" name="file" />'); 
	frm = frm.concat('<input type="hidden" name="lang_id" value="'+lang_id+'">'); 
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
			url: fcom.makeUrl('Seller', 'updateInventory'),
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
				//$('.text-danger').remove();	
				/* $.systemMessage.close();				 */
				if(ans.status == true){
					$.mbsmessage(ans.msg,true,'alert--success');
					inventoryUpdateForm();
				} else {
					$.mbsmessage(ans.msg,true,'alert--danger');
				}
			},
			error: function( xhr, ajaxOptions, thrownError ) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
			});			
		}
	}, 500);
});