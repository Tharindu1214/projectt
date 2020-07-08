$(document).ready(function(){
	searchOrderReturnRequests(document.frmOrderReturnRequest);
});
(function() {
	searchOrderReturnRequests = function(frm){
		var data = fcom.frmData(frm);
		$("#shippingSettingsListing").html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Seller','shippingSettingsListing'), data, function(res){
			$("#shippingSettingsListing").html(res);
		}); 
	};
	
	goToOrderReturnRequestSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmOrderReturnRequestSrchPaging;		
		$(frm.page).val(page);
		searchOrderReturnRequests(frm);
	}
	
	clearOrderReturnRequestSearch = function(){
		document.frmOrderReturnRequest.reset();
		searchOrderReturnRequests(document.frmOrderReturnRequest);
	};

	deleteShippingSettings  = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.ajax(fcom.makeUrl('seller','deleteShippingSetting'),data,function(t){
			$res = $.parseJSON(t);
			if($res.status == 0){
				$.mbsmessage($res.msg, true, 'alert--danger');
			}else{
				$.mbsmessage($res.msg, true, 'alert--success');
			}
			reloadList();
		});
	}

	reloadList = function() {
		searchOrderReturnRequests(document.frmOrderReturnRequest);
	}

	optionForm = function(optionId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller','importShippingSettings',[optionId]), '', function(t) {
				try{
					res= jQuery.parseJSON(t);
					$.facebox(res.msg,'faceboxWidth');
				}catch (e){
					$.facebox(t,'faceboxWidth');
				}
				fcom.resetFaceboxHeight();
			});
		});
	}

	importShippingSettingsFile = function(method, actionType) {
        var data = new FormData();
        $inputs = $('#frmImportExport input[type=text],#frmImportExport select,#frmImportExport input[type=hidden]');
        $inputs.each(function() {
            data.append(this.name, $(this).val());
        });
        if ($('#import_file')[0].files.length == 0) {
			console.log('no file selected');
            $.mbsmessage(langLbl.selectFile, false, 'alert--danger');
        }
        $.each($('#import_file')[0].files, function(i, file) {
            $.mbsmessage(langLbl.processing, false, 'alert--process');
            $('#fileupload_div').html(fcom.getLoader());
            data.append('import_file', file);
            $.ajax({
                url: fcom.makeUrl('Seller',method,[actionType]),
                type: "POST",
                data: data,
                processData: false,
                contentType: false,
                success: function(t) {
                    try {
                        var ans = $.parseJSON(t);
						
                        if (ans.status == 1 || ans.status == true) {
                            $(document).trigger('close.facebox');
                            $(document).trigger('close.mbsmessage');
                            $.systemMessage(ans.msg, 'alert--success');
                        } else {
                            $('#fileupload_div').html('');
                            $(document).trigger('close.mbsmessage');
                            $.systemMessage(ans.msg, 'alert--danger');
                        }

                        if (typeof ans.CSVfileUrl !== 'undefined') {
                            location.href = ans.CSVfileUrl;
                        }
                    } catch (exc) {
                        $(document).trigger('close.mbsmessage');
                        $.systemMessage(exc.message, 'alert--danger');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert("Error Occured.");
                }
            });
        });
    };
          

})();