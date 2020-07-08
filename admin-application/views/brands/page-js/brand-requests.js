$(document).ready(function(){
	searchProductBrands(document.frmSearch);

	$('input[name=\'user_name\']').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('Users', 'autoCompleteJson'),
				data: {keyword: request, fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'] +'(' + item['username'] + ')', value: item['id'], name: item['username']	};
					}));
				},
			});
		},
		'select': function(item) {
			$("input[name='user_id']").val( item['value'] );
			$("input[name='user_name']").val( item['name'] );
		}
	});
});
$(document).on('change','.language-js',function(){
/* $(document).delegate('.language-js','change',function(){ */
	var lang_id = $(this).val();
	var brand_id = $("input[id='id-js']").val();
	brandImages(brand_id, 'logo', lang_id);
});
$(document).delegate('.image-language-js','change',function(){
	var lang_id = $(this).val();
	var brand_id = $("input[id='id-js']").val();
	brandImages(brand_id, 'image', lang_id);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmBrandSearchPaging;
		$(frm.page).val(page);
		searchProductBrands(frm);
	}

	reloadList = function() {
		var frm = document.frmBrandSearchPaging;
		searchProductBrands(frm);
	}



	setupBrand = function(frm) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('brands', 'setupRequest'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				brandRequestLangForm(t.brandId, t.langId);
				return ;
			}
			if (t.openMediaForm)
			{
				brandRequestMediaForm(t.brandId);
				return;
			}
			/* $(document).trigger('close.facebox'); */
		});
	};

	brandRequestLangForm = function(brandId, langId) {
	fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('brands', 'requestLangForm', [brandId, langId]), '', function(t) {
				fcom.updateFaceboxContent(t);
			});
			};

	setupBrandLang=function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('brands', 'langSetup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				brandRequestLangForm(t.brandId, t.langId);
				return ;
			}
			if (t.openMediaForm)
			{
				brandRequestMediaForm(t.brandId);
				return;
			}
			/* $(document).trigger('close.facebox'); */
		});
	};

	searchProductBrands = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$("#listing").html('Loading....');
		fcom.ajax(fcom.makeUrl('brands','searchBrandRequests'),data,function(res){
			$("#listing").html(res);
		});
	};

    brandRequestMediaForm = function(brandId){
		fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Brands', 'requestMedia', [brandId]), '', function(t) {
            brandImages(brandId, 'logo');
            brandImages(brandId, 'image');
            fcom.updateFaceboxContent(t);
        });
	};

	brandImages = function(brandId, fileType, langId){
		fcom.ajax(fcom.makeUrl('Brands', 'images', [brandId, fileType, langId]), '', function(t) {
			if(fileType=='logo') {
				$('#logo-listing').html(t);
			} else {
				$('#image-listing').html(t);
			}
			fcom.resetFaceboxHeight();
		});
	};


	deleteRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.ajax(fcom.makeUrl('brands','deleteRecord'),data,function(res){
			reloadList();
		});
	};

	clearSearch = function(){
		document.frmSearch.reset();
		searchProductBrands(document.frmSearch);
	};

	deleteMedia = function( brandId, fileType, langId ){
		if(!confirm(langLbl.confirmDelete)){return;}
		fcom.updateWithAjax(fcom.makeUrl('brands', 'removeBrandMedia',[brandId, fileType, langId]), '', function(t) {
			brandImages(brandId,fileType,langId);
			reloadList();
		});
	};

	addBrandRequestForm= function(id){

		$.facebox(function() {brandRequestForm(id)

		});
	}
	brandRequestForm = function(id) {
		fcom.displayProcessing();
		var frm = document.frmBrandSearchPaging;
			fcom.ajax(fcom.makeUrl('brands', 'requestForm', [id]), '', function(t) {
				fcom.updateFaceboxContent(t);
		});
	};

	showHideCommentBox = function(val){
		if(val == 2){
			$('#div_comments_box').removeClass('hide');
		}else{
			$('#div_comments_box').addClass('hide');
		}
	};

})();

$(document).on('click','.uploadFile-Js',function(){
	var node = this;
	$('#form-upload').remove();

	var formName = $(node).attr('data-frm');
	if(formName == 'frmBrandImage'){
        var brandId = document.frmBrandImage.brand_id.value;
        var langId = document.frmBrandImage.lang_id.value;
        var imageType = 'image';
	}else{
		var brandId = document.frmBrandLogo.brand_id.value;
        var langId = document.frmBrandLogo.lang_id.value;
		var imageType = 'logo';
	}

    var fileType = $(node).attr('data-file_type');

	var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
	frm = frm.concat('<input type="file" name="file" />');
	frm = frm.concat('<input type="hidden" name="brand_id" value="' + brandId + '"/>');
	frm = frm.concat('<input type="hidden" name="lang_id" value="' + langId + '"/>');
    frm = frm.concat('<input type="hidden" name="file_type" value="' + fileType + '">');
	frm = frm.concat('</form>');
	$( 'body' ).prepend( frm );
	$('#form-upload input[name=\'file\']').trigger('click');
	if ( typeof timer != 'undefined' ) {
		clearInterval(timer);
	}
	timer = setInterval(function() {
		if ($('#form-upload input[name=\'file\']').val() != '') {
			clearInterval(timer);
			$val = $(node).val();
			$.ajax({
				url: fcom.makeUrl('Brands', 'uploadMedia'),
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
						$('#input-field').html(ans.msg);
						if( ans.status == true ){
							$('#input-field').removeClass('text-danger');
							$('#input-field').addClass('text-success');
							$('#form-upload').remove();
							brandImages(ans.brandId,imageType,langId);
						}else{
							$('#input-field').removeClass('text-success');
							$('#input-field').addClass('text-danger');
						}
						reloadList();
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
		}
	}, 500);
});
