$(document).ready(function(){
	searchShops(document.frmShopSearch);
});
$(document).on('change','.logo-language-js',function(){
/* $(document).delegate('.logo-language-js','change',function(){ */
	var lang_id = $(this).val();
	var shop_id = document.frmShopLogo.shop_id.value;
	shopImages(shop_id,'logo',0,lang_id);
});
$(document).on('change','.banner-language-js',function(){
	var lang_id = $(this).val();
	var shop_id = document.frmShopBanner.shop_id.value;
	var slide_screen = $(".prefDimensions-js").val();
	shopImages(shop_id,'banner',slide_screen,lang_id);
});
$(document).on('change','.prefDimensions-js',function(){
	var slide_screen = $(this).val();
	var shop_id = document.frmShopBanner.shop_id.value;
	var lang_id = $(".banner-language-js").val();
	shopImages(shop_id,'banner',slide_screen,lang_id);
});
/*$(document).on('change','.bg-language-js',function(){
	var lang_id = $(this).val();
	var shop_id = document.frmShopBg.shop_id.value;
	shopImages(shop_id,'bg',lang_id);
});*/
$(document).on('change','.collection-language-js',function(){
	var lang_id = $(this).val();
	var scollection_id = document.frmCollectionMedia.scollection_id.value;
	var shop_id = document.frmCollectionMedia.shop_id.value;
	shopCollectionImages(shop_id, scollection_id, lang_id);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dvt = '#shopFormChildBlock';

	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmShopSearchPaging;
		$(frm.page).val(page);
		searchShops(frm);
	}

	reloadList = function() {
		var frm = document.frmShopSearchPaging;
		searchShops(frm);
	}

	reloadCollectionList = function(){
		shopCollections($("input[name='collection_shopId']").val());
	}

	addShopForm = function(id) {
		$.facebox(function() {
			shopForm(id);
		});
	};
	shopForm = function(id) {
		fcom.displayProcessing();
		var frm = document.frmShopSearchPaging;
		fcom.ajax(fcom.makeUrl('Shops', 'form', [id]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};

	setupShop = function(frm) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Shops', 'setup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				/* $.mbsmessage(t.msg,'','alert--success'); */
				addShopLangForm(t.shopId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	addShopLangForm = function(shopId, langId) {
			fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('Shops', 'langForm', [shopId, langId]), '', function(t) {
				fcom.updateFaceboxContent(t);
			});

	};

	setupShopLang=function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Shops', 'langSetup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				addShopLangForm(t.shopId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	searchShops = function(form){
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/

		$("#shopListing").html(fcom.getLoader());

		fcom.ajax(fcom.makeUrl('Shops','search'),data,function(res){

			$("#shopListing").html(res);
		});
	};

	clearShopSearch = function(){
		document.frmShopSearch.reset();
		searchShops(document.frmShopSearch);
	};

	getCountryStates = function(countryId, stateId, dv){
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('Shops','getStates',[countryId,stateId]),'',function(res){
			$(dv).empty();
			$(dv).append(res);
		});
	};

	shopMediaForm = function(shopId){
			fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('shops', 'media', [shopId]), '', function(t) {
				shopImages(shopId,'logo',1);
				shopImages(shopId,'banner',1);
				shopImages(shopId,'bg',1);
				fcom.updateFaceboxContent(t);
			});
	};

	shopImages = function(shopId,imageType,slide_screen,lang_id){
		fcom.ajax(fcom.makeUrl('shops', 'images', [shopId,imageType,lang_id,slide_screen]), '', function(t) {
			if(imageType=='logo') {
				$('#logo-image-listing').html(t);
			} else if(imageType=='banner') {
				$('#banner-image-listing').html(t);
			} else {
				$('#bg-image-listing').html(t);
			}
			fcom.resetFaceboxHeight();
		});
	};

	shopTemplates = function(shopId){
			fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('shops', 'shopTemplate', [shopId]), '', function(t) {
				fcom.updateFaceboxContent(t);
			});
	};

	setTemplate = function (shopId,ltemplateId){
		fcom.updateWithAjax(fcom.makeUrl('shops', 'setTemplate',[shopId,ltemplateId]), '', function(t) {
			shopTemplates(shopId);
			return ;
		});
	};

	/* shopCollectionProducts= function(shopId){
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('shops', 'shopCollection', [shopId]), '', function(t) {
			fcom.updateFaceboxContent(t);
			getShopCollectionGeneralForm(shopId);
		});
	}; */

	deleteShopCollection=function(shop_id, scollection_id){
		var agree = confirm( langLbl.confirmDelete );
		if( !agree ){
			return false;
		}
		fcom.ajax(fcom.makeUrl('shops','deleteShopCollection', [shop_id, scollection_id] ),'',function(res){
			searchShopCollections(shop_id);
		});
	};
	deleteSelectedCollection = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmCollectionsListing").attr("action",fcom.makeUrl('Shops','deleteSelectedCollections')).submit();
    };

	shopCollections= function(shopId){
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('shops', 'shopCollections', [shopId]), '', function(t) {
			fcom.updateFaceboxContent(t);
			searchShopCollections(shopId);
		});
	};

	searchShopCollections= function(shopId){
		$(dvt).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('shops', 'searchShopCollections', [shopId]), '', function(t) {
			$(dvt).html(t);
		});
	};

	getShopCollectionGeneralForm = function (shop_id, scollection_id){
		fcom.ajax(fcom.makeUrl('shops', 'shopCollectionGeneralForm', [shop_id, scollection_id]), '', function(t) {
			$(dvt).html(t);
		});
	};

	setupShopCollection = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('shops', 'setupShopCollection'), data, function(t) {
			if (t.langId>0) {
				editShopCollectionLangForm(t.shop_id, t.collection_id, t.langId);
				return ;
			}

		});
	}

	setupShopCollectionlangForm = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('shops', 'setupShopCollectionLang'), data, function(t) {
			if (t.langId>0) {
				editShopCollectionLangForm(t.shop_id, t.collection_id, t.langId);
				return ;
			}
		});

	}

	editShopCollectionLangForm = function(shop_id,scollection_id,langId){
		if (typeof(scollection_id) == "undefined" || scollection_id<0){
			return false;
		}
		if (typeof(langId) == "undefined" || langId<0){
			return false;
		}
		if (typeof(shop_id) == "undefined" || shop_id<0){

			return false;
		}
		fcom.ajax(fcom.makeUrl('shops', 'shopCollectionLangForm', [shop_id,scollection_id,langId]), '', function(t) {
			$(dvt).html(t);
		});
	};

	sellerCollectionProducts = function( scollection_id, shop_id ) {
		fcom.ajax(fcom.makeUrl('shops', 'sellerCollectionProductLinkFrm', [ scollection_id,shop_id ]), '', function(t) {
			$(dvt).html(t);
			bindAutoComplete();
		});
	};

	setUpSellerCollectionProductLinks = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('shops', 'setUpSellerCollectionProductLinks'), data, function(t) {
		});
	};

	collectionMediaForm = function (shop_id, scollection_id){
		$(dvt).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('shops', 'shopCollectionMediaForm', [shop_id, scollection_id]), '', function(t) {
			$(dvt).html(t);
			shopCollectionImages(shop_id, scollection_id);
		});
	};

	shopCollectionImages = function(shop_id, scollection_id, lang_id){
		fcom.ajax(fcom.makeUrl('shops', 'shopCollectionImages', [shop_id, scollection_id, lang_id]), '', function(t) {
			$('#imageListing').html(t);
		});
	};

	removeCollectionImage = function( shop_id, scollection_id, langId ){
		var agree = confirm( langLbl.confirmRemove );
		if( !agree ){
			return false;
		}
		fcom.updateWithAjax(fcom.makeUrl('shops', 'removeCollectionImage',[shop_id, scollection_id, langId]), '', function(t) {
			shopCollectionImages( shop_id, scollection_id, langId );
		});
	};

	deleteImage = function( fileId, shopId, imageType, langId, slide_screen){
		var agree = confirm( langLbl.confirmDeleteImage );
		if( !agree ){
			return false;
		}
		fcom.updateWithAjax(fcom.makeUrl('Shops', 'removeShopImage',[fileId,shopId,imageType,langId,slide_screen]), '', function(t) {
			shopImages( shopId, imageType, slide_screen, langId );
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
		var shopId = parseInt(obj.value);
		if(shopId < 1){
			fcom.displayErrorMessage(langLbl.invalidRequest);
			return false;
		}
		data='shopId='+shopId;
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('Shops','changeStatus'),data,function(res){
		var ans =$.parseJSON(res);
			if( ans.status == 1 ){
				$(obj).toggleClass("active");
				fcom.displaySuccessMessage(ans.msg);
				/* setTimeout(function(){
					reloadList();
				}, 1000); */
			} else {
				fcom.displayErrorMessage(ans.msg);
			}
		});
		$.systemMessage.close();
	};

	toggleCollectionStatus = function(e,obj,canEdit){
		if(canEdit == 0){
			e.preventDefault();
			return;
		}
		if(!confirm(langLbl.confirmUpdateStatus)){
			e.preventDefault();
			return;
		}
		var shopCollectionId = parseInt(obj.value);
		if(shopCollectionId < 1){
			fcom.displayErrorMessage(langLbl.invalidRequest);
			return false;
		}
		data='scollection_id='+shopCollectionId;
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('Shops','changeCollectionStatus'),data,function(res){
		var ans =$.parseJSON(res);
			if( ans.status == 1 ){
				$(obj).toggleClass("active");
				fcom.displaySuccessMessage(ans.msg);
				/* setTimeout(function(){
					reloadList();
				}, 1000); */
			} else {
				fcom.displayErrorMessage(ans.msg);
			}
		});
		$.systemMessage.close();
	};

	toggleBulkCollectionStatues = function(status){
		if(!confirm(langLbl.confirmUpdateStatus)){
			return false;
		}
		$("#frmCollectionsListing input[name='collection_status']").val(status);
		$("#frmCollectionsListing").submit();
	};

	toggleBulkStatues = function(status){
		if(!confirm(langLbl.confirmUpdateStatus)){
			return false;
		}
		$("#frmShopListing input[name='status']").val(status);
		$("#frmShopListing").submit();
	};

})();

function bindAutoComplete(){
	var shopId = document.frmLinks1.shop_id.value;
	$("input[name='scp_selprod_id']").autocomplete({'source': function(request, response) {
		$.ajax({
			url: fcom.makeUrl('Shops', 'autoCompleteProducts'),
			data: {keyword: request,fIsAjax:1,shopId:shopId},
			dataType: 'json',
			type: 'post',
			success: function(json) {
				response($.map(json, function(item) {
					return { label: item['name'] +'['+item['product_identifier'] +']',	value: item['id']	};
				}));
			},
		});
	},
	'select': function(item) {
		$('input[name=\'scp_selprod_id\']').val('');
		$('#selprod-products' + item['value']).remove();
		$('#selprod-products ul').append('<li id="selprod-products' + item['value'] + '"><i class=" icon ion-close-round"></i> ' +item['label'] + '<input type="hidden" name="product_ids[]" value="' + item['value'] + '" /></li>');
	}
	});
}

$(document).on('click','.shopFile-Js',function(){
	var node = this;
	$('#form-upload').remove();
	var frmName = $(node).attr('data-frm');
	var slide_screen = 0;
	if('frmShopLogo' == frmName){
		var langId = document.frmShopLogo.lang_id.value;
		var shopId = document.frmShopLogo.shop_id.value;
		var imageType = 'logo';
	}else if('frmShopBanner' == frmName){
		var langId = document.frmShopBanner.lang_id.value;
		var shopId = document.frmShopBanner.shop_id.value;
		slide_screen = document.frmShopBanner.slide_screen.value;
		var imageType = 'banner';
	}else {
		var langId = document.frmBackgroundImage.lang_id.value;
		var shopId = document.frmBackgroundImage.shop_id.value;
		var imageType = 'bg';
	}

	var fileType = $(node).attr('data-file_type');

	var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
	frm = frm.concat('<input type="file" name="file" />');
	frm = frm.concat('<input type="hidden" name="shop_id" value="'+shopId+'"/>');
	frm = frm.concat('<input type="hidden" name="slide_screen" value="'+slide_screen+'"/>');
	frm = frm.concat('<input type="hidden" name="file_type" value="'+fileType+'"></form>');
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
				url: fcom.makeUrl('Shops', 'uploadShopImages',[shopId, langId]),
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
						$('#input-field'+fileType).html(ans.msg);
						if(ans.status == true){
							$('#input-field'+fileType).removeClass('text-danger');
							$('#input-field'+fileType).addClass('text-success');
							$('#form-upload').remove();
							shopImages(ans.shopId,imageType,slide_screen,langId);
							fcom.displaySuccessMessage(ans.msg);
							//addShopLangForm(ans.shopId, langId);
						}else{
							$('#input-field'+fileType).removeClass('text-success');
							$('#input-field'+fileType).addClass('text-danger');
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

$(document).on('click','.shopCollection-Js',function(){
	var node = this;
	$('#form-upload').remove();
	var shop_id = document.frmCollectionMedia.shop_id.value;
	var scollection_id = document.frmCollectionMedia.scollection_id.value;
	var lang_id = document.frmCollectionMedia.lang_id.value;
	var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
	frm = frm.concat('<input type="file" name="file" />');
	frm = frm.concat('<input type="hidden" name="scollection_id" value="' + scollection_id + '">');
	frm = frm.concat('<input type="hidden" name="shop_id" value="' + shop_id + '">');
	frm = frm.concat('<input type="hidden" name="lang_id" value="' + lang_id + '">');
	frm = frm.concat('</form>');
	$('body').prepend(frm);
	$('#form-upload input[name=\'file\']').trigger('click');
	if ( typeof timer != 'undefined' ) {
		clearInterval(timer);
	}
	timer = setInterval(function() {
		if ($('#form-upload input[name=\'file\']').val() != '') {
			clearInterval(timer);
			$val = $(node).val();
			$.ajax({
				url: fcom.makeUrl('Shops', 'uploadCollectionImage'),
				type: 'post',
				dataType: 'json',
				data: new FormData($('#form-upload')[0]),
				cache: false,
				contentType: false,
				processData: false,
				beforeSend: function() {
					$(node).val('loading..');
				},
				complete: function() {
					$(node).val($val);
				},
				success: function(ans) {
					$.mbsmessage.close();
					$.systemMessage.close();
					//$.mbsmessage(ans.msg, true, 'alert--success');
					var dv = '#mediaResponse';
					$('.text-danger').remove();
					if( ans.status == true ){
						$.systemMessage( ans.msg,'alert--success');
						$(dv).removeClass('text-danger');
						$(dv).addClass('text-success');
						shopCollectionImages(shop_id, scollection_id, lang_id);
					} else {
						$.systemMessage(ans.msg,'alert--danger');
						$(dv).removeClass('text-success');
						$(dv).addClass('text-danger');
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
	}, 500);

});
