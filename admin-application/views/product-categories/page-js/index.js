$(document).ready(function(){
	searchProductCategories(document.frmSearch);
});
$(document).on('change','.icon-language-js',function(){
	var lang_id = $(this).val();
	var prodcat_id = $("input[name='prodcat_id']").val();
	categoryImages(prodcat_id,'icon',0,lang_id);

});
$(document).on('change','.banner-language-js',function(){
	var lang_id = $(this).val();
	var prodcat_id = $("input[name='prodcat_id']").val();
	var slide_screen = $(".prefDimensions-js").val();
	categoryImages(prodcat_id,'banner',slide_screen,lang_id);
});
$(document).on('change','.prefDimensions-js',function(){
	var slide_screen = $(this).val();
	var prodcat_id = $("input[name='prodcat_id']").val();
	var lang_id = $(".banner-language-js").val();
	categoryImages(prodcat_id,'banner',slide_screen,lang_id);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmCatSearchPaging;
		$(frm.page).val(page);
		searchProductCategories(frm);
	}

	reloadList = function() {
		var frm = document.frmCatSearchPaging;
		searchProductCategories(frm);
	}

	addCategoryForm= function (id) {

	$.facebox(function(){categoryForm(id);});

	// body...
}
	categoryForm = function(id) {
		fcom.displayProcessing();
		var frm = document.frmCatSearchPaging;

		var parent=$(frm.prodcat_parent).val();
		fcom.displayProcessing();

		//var frm = document.frmCatSearchPaging;
		if(typeof parent==undefined || parent == null){
			parent =0;
		}

		fcom.ajax(fcom.makeUrl('ProductCategories', 'form', [id,parent]), '', function(t) {
			fcom.updateFaceboxContent(t);
			fcom.resetEditorInstance();
		});

	};

	categoryImages = function(prodCatId,imageType,slide_screen,lang_id){
		fcom.ajax(fcom.makeUrl('ProductCategories', 'images', [prodCatId,imageType,lang_id,slide_screen]), '', function(t) {
			if(imageType=='icon') {
				$('#icon-image-listing').html(t);
			} else if(imageType=='banner') {
				$('#banner-image-listing').html(t);
			}
			fcom.resetFaceboxHeight();
		});
	};

	setupCategory = function(frm) {
		if (!$(frm).validate()) return;
		var addingNew = ( $(frm.prodcat_id).val() == 0 );
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('ProductCategories', 'setup'), data, function(t) {
			reloadList();
			if ( t.langId > 0 ) {
				categoryLangForm(t.catId, t.langId);
				return ;
			}
			if ( addingNew ) {
				categoryLangForm(t.catId, t.langId);
				return ;
			}
			if ( t.openMediaForm ){
				categoryMediaForm( t.catId );
				return;
			}
			fcom.resetEditorInstance();
			$(document).trigger('close.facebox');
		});
	};

	categoryLangForm = function(catId, langId) {

		fcom.resetEditorInstance();
		//$.facebox(function() {
			fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('ProductCategories', 'langForm', [catId, langId]), '', function(t) {
				//fcom.updateFaceboxContent(t);
				//$.facebox(t);
				fcom.updateFaceboxContent(t);
				fcom.setEditorLayout(langId);
				var frm = $('#facebox form')[0];
				var validator = $(frm).validation({errordisplay: 3});
				$(frm).submit(function(e) {
					e.preventDefault();
					if (validator.validate() == false) {
						return ;
					}
					var data = fcom.frmData(frm);
					if (!$(frm).validate()) return;

					fcom.updateWithAjax(fcom.makeUrl('ProductCategories', 'langSetup'), data, function(t) {
						fcom.resetEditorInstance();
						reloadList();
						if (t.langId > 0) {
							categoryLangForm(t.catId, t.langId);
							return;
						}
						if (t.openMediaForm){
							categoryMediaForm(t.catId);
							return;
						}
						$(document).trigger('close.facebox');
					});

				});
			});
		//});
	};

	searchProductCategories = function(form){
		var data = '';
		if ( form ) {
			data = fcom.frmData(form);
		}

		$("#listing").html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('productCategories','search'),data,function(res){
			$("#listing").html(res);
		});
	};

	subcat_list=function(parent){
		var frm = document.frmCatSearchPaging;
		$(frm.prodcat_parent).val(parent);
		reloadList();
	};

	categoryMediaForm = function(prodCatId){
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('productCategories','mediaForm',[prodCatId]),'',function(t){
			categoryImages(prodCatId,'icon',1);
			categoryImages(prodCatId,'banner',1);
			fcom.updateFaceboxContent(t);
			setTimeout(  fcom.resetFaceboxHeight(),5000);
		});
	};

	deleteRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.ajax(fcom.makeUrl('productCategories','deleteRecord'),data,function(res){
			var ans = $.parseJSON(res);
			if( ans.status == 1 ){
				fcom.displaySuccessMessage(ans.msg);
				reloadList();
			} else {
				fcom.displayErrorMessage(ans.msg);
			}
		});
	};

	deleteImage = function(fileId, prodcatId, imageType, langId, slide_screen){
		if( !confirm(langLbl.confirmDeleteImage) ){ return; }
		fcom.updateWithAjax(fcom.makeUrl('productCategories', 'removeImage',[fileId,prodcatId,imageType,langId,slide_screen]), '', function(t) {
			categoryImages( prodcatId, imageType, slide_screen, langId );
		});
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
		var prodcatId = parseInt(obj.value);
		if( prodcatId < 1 ){
			fcom.displayErrorMessage(langLbl.invalidRequest);
			return false;
		}
		data='prodcatId='+prodcatId;
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('productCategories','changeStatus'),data,function(res){
		var ans = $.parseJSON(res);
			if( ans.status == 1 ){
				$(obj).toggleClass("active");

				fcom.displaySuccessMessage(ans.msg);
				/* setTimeout(function(){
					reloadList();
				}, 1000); */
			} else {
				alert("Danger");
				fcom.displa(ans.msg);
			}
		});
		$.systemMessage.close();
	};

	toggleBulkStatues = function(status){
		if(!confirm(langLbl.confirmUpdateStatus)){
			return false;
		}
		$("#frmProdCatListing input[name='status']").val(status);
		$("#frmProdCatListing").submit();
	};

	deleteSelected = function(){
		if(!confirm(langLbl.confirmDelete)){
			return false;
		}
		$("#frmProdCatListing").attr("action",fcom.makeUrl('ProductCategories','deleteSelected')).submit();
	};

	clearSearch = function(){
		document.frmSearch.reset();
		searchProductCategories(document.frmSearch);
	};

})();

$(document).on('click','.catFile-Js',function(){
	var node = this;
	$('#form-upload').remove();
	var formName = $(node).attr('data-frm');
	var slide_screen = 0;
	if(formName == 'frmCategoryImage'){
		var lang_id = document.frmCategoryImage.lang_id.value;
		var prodcat_id = document.frmCategoryImage.prodcat_id.value;
	}else if(formName == 'frmCategoryIcon'){
		var lang_id = document.frmCategoryIcon.lang_id.value;
		var prodcat_id = document.frmCategoryIcon.prodcat_id.value;
		var imageType = 'icon';
	}else{
		var lang_id = document.frmCategoryBanner.lang_id.value;
		var prodcat_id = document.frmCategoryBanner.prodcat_id.value;
		slide_screen = document.frmCategoryBanner.slide_screen.value;
		var imageType = 'banner';
	}

	var fileType = $(node).attr('data-file_type');

	var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
	frm = frm.concat('<input type="file" name="file" />');
	frm = frm.concat('<input type="hidden" name="file_type" value="' + fileType + '">');
	frm = frm.concat('<input type="hidden" name="prodcat_id" value="' + prodcat_id + '">');
	frm = frm.concat('<input type="hidden" name="lang_id" value="' + lang_id + '">');
	frm = frm.concat('<input type="hidden" name="slide_screen" value="' + slide_screen + '">');
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
				url: fcom.makeUrl('ProductCategories', 'setUpCatImages'),
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
					fcom.resetFaceboxHeight();

				},
				success: function(ans) {
						if(ans.status == 1){
							fcom.displaySuccessMessage(ans.msg);
							$('#form-upload').remove();
							categoryImages(prodcat_id,imageType,slide_screen,lang_id);
						}else{
							fcom.displayErrorMessage(ans.msg);
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
		}
	}, 500);
});
