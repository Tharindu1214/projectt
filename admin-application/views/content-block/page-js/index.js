$(document).ready(function(){
	searchBlocks(document.frmBlockSearch);
});

(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	reloadList = function() {
		var frm = document.frmBlockSearch;
		searchBlocks(frm);
	}

	searchBlocks = function(form){
		var dv = '#blockListing';
		var data = '';
		if (form){
			data = fcom.frmData(form);
		}
		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('ContentBlock','search'),data,function(res){
			$(dv).html(res);
		});
	};

	addBlockFormNew = function(id){

		$.facebox(function() { addBlockForm(id);
		});

	};
	addBlockForm = function(id) {
		fcom.displayProcessing();
		var frm = document.frmBlockSearch;
			fcom.ajax(fcom.makeUrl('ContentBlock', 'form', [id]), '', function(t) {
				fcom.updateFaceboxContent(t);
		});
	};

	setupBlock = function(frm) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('ContentBlock', 'setup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				addBlockLangForm(t.epageId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	addBlockLangForm = function(epageId, langId){
		fcom.displayProcessing();
		fcom.resetEditorInstance();
//		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('ContentBlock', 'langForm', [epageId, langId]), '', function(t) {
				//$.facebox(t);
				fcom.updateFaceboxContent(t);
				fcom.resetFaceboxHeight();

				fcom.setEditorLayout(langId);
				var frm = $('#facebox form')[0];
				var validator = $(frm).validation({errordisplay: 3});

				$(frm).submit(function(e) {
					e.preventDefault();
					if (validator.validate() == false) {
						return ;
					}
					var data = fcom.frmData(frm);
					fcom.updateWithAjax(fcom.makeUrl('ContentBlock', 'langSetup'), data, function(t) {
						fcom.resetEditorInstance();
						reloadList();
						if (t.langId>0) {
							addBlockLangForm(t.epageId, t.langId);
							return ;
						}
						$(document).trigger('close.facebox');
					});

				});
			});
		//});
	};

	setupBlockLang=function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('ContentBlock', 'langSetup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				addBlockLangForm(t.epageId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	resetToDefaultContent =  function(){
		var agree  = confirm(langLbl.confirmReplaceCurrentToDefault);
		if( !agree ){ return false; }
		oUtil.obj.insertHTML($("#editor_default_content").html());
		//oUtil.obj.putHTML( $("#editor_default_content").html() );
	};

	toggleStatus = function(e,obj,canEdit){
		if(canEdit == 0){
			e.preventDefault();
			return;
		}
		if(!confirm(langLbl.confirmUpdateStatus)){
			e.preventDefault();
			return;
		}
		var epageId = parseInt(obj.value);
		if( epageId < 1 ){

			fcom.displayErrorMessage(langLbl.invalidRequest);
			//$.mbsmessage(langLbl.invalidRequest,true,'alert--danger');
			return false;
		}
		data='epageId='+epageId;
		fcom.ajax(fcom.makeUrl('ContentBlock','changeStatus'),data,function(res){
		var ans = $.parseJSON(res);
			if( ans.status == 1 ){
				fcom.displaySuccessMessage(ans.msg);
				//$.mbsmessage(ans.msg,true,'alert--success');
				$(obj).toggleClass("active");
			}
			else{
				fcom.displayErrorMessage(ans.msg);

				//$.mbsmessage(ans.msg,true,'alert--danger');
			}
		});
	};

	removeBgImage = function( epage_id, langId, file_type ){
		if( !confirm(langLbl.confirmDeleteImage) ){ return; }
		fcom.updateWithAjax( fcom.makeUrl('ContentBlock', 'removeBgImage',[epage_id, langId, file_type]), '', function(t) {
			addBlockLangForm(epage_id, langId);
		});
	};

	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmContentBlockListing input[name='status']").val(status);
        $("#frmContentBlockListing").submit();
    };

})();

$(document).on('click','.bgImageFile-Js',function(){
	var node = this;
	$('#form-upload').remove();
	var formName = $(node).attr('data-frm');

	var lang_id = document.frmBlockLang.lang_id.value;
	var epage_id = document.frmBlockLang.epage_id.value;

	var file_type = $(node).attr('data-file_type');

	var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
	frm = frm.concat('<input type="file" name="file" />');
	frm = frm.concat('<input type="hidden" name="file_type" value="' + file_type + '">');
	frm = frm.concat('<input type="hidden" name="epage_id" value="' + epage_id + '">');
	frm = frm.concat('<input type="hidden" name="lang_id" value="' + lang_id + '">');
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
					url: fcom.makeUrl('ContentBlock', 'setUpBgImage'),
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
						fcom.displaySuccessMessage(ans.msg);
						$(".temp-hide").show();
						/* addBlockLangForm(ans.epage_id,ans.lang_id); */
						var dt = new Date();
						var time = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
						$(".uploaded--image").html('<img src="'+fcom.makeUrl('image', 'cblockBackgroundImage', [ans.epage_id,ans.lang_id,'THUMB',file_type], SITE_ROOT_URL)+'?'+time+'"> <a href="javascript:void(0);" onclick="removeBgImage('+[ans.epage_id,ans.lang_id,ans.file_type]+')" class="remove--img"><i class="ion-close-round"></i></a>');
						fcom.displaySuccessMessage(ans.msg);
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
		}
	}, 500);
});
