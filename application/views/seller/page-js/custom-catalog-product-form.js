$(document).on('change','.option-js',function(){
	var option_id = $(this).val();
	var product_id = $('#frmCustomCatalogProductImage input[name=preq_id]').val();
	var lang_id = $('.language-js').val();
	productImages(product_id,option_id,lang_id);
});

$(document).on('change','.language-js',function(){
	var lang_id = $(this).val();
	var product_id = $('#frmCustomCatalogProductImage input[name=preq_id]').val();
	var option_id = $('.option-js').val();
	productImages(product_id,option_id,lang_id);
});

(function() {
	var dv = '#listing';
	var prodCatId = 0;
	var blockCount = 0;

	customCatalogProductCategoryForm = function(){
		fcom.ajax(fcom.makeUrl('Seller', 'customCatalogProductCategoryForm'), '', function(t) {
			$(dv).html(t);
			customCategoryListing(prodCatId,blockCount);
		});
	};

	customCatalogProductForm = function( id, prodcat_id ){
		$(dv).html( fcom.getLoader() );
		if(/* (typeof id == 'undefined' || id == 0 ) && */ (typeof prodcat_id == 'undefined' || prodcat_id == 0)){
			customCatalogProductCategoryForm( );
			return;
		}

		fcom.ajax(fcom.makeUrl('Seller', 'customCatalogGeneralForm', [ id , prodcat_id ]), '', function(t) {
			$(dv).html(t);
		});
	};

	customCategoryListing = function(prodCatId,section){
		$(section).parent().find('li').removeClass('is-active');
		$(section).addClass('is-active');
		var bcount = $(section).closest('.categoryblock-js').attr('rel');
		if(typeof bcount != 'undefined'){
			blockCount = bcount;
			blockCount = parseInt(blockCount)+1;
		}
		$(section).closest('.categoryblock-js').nextAll('div').remove();
		var data = "prodCatId="+prodCatId+"&blockCount="+blockCount;
		fcom.ajax(fcom.makeUrl('Seller', 'customCategoryListing'), data, function(t) {
			var ans = $.parseJSON(t);
			$.mbsmessage.close();
			if(ans.structure != ''){
				$('.slick-track').append(ans.structure);

				$('#categories-js .slick-prev').remove();
				$('#categories-js .slick-next').remove();
				$('.select-categories-slider-js').slick('reinit');
				if(blockCount > 2){
					$('.select-categories-slider-js').slick("slickNext");
				}
			}
			prodCatId = ans.prodcat_id;
		});
	};

	searchCategory = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.ajax(fcom.makeUrl('Seller', 'searchCategory'), (data), function(t) {
			$('#categories-js').hide();
			$('#categorySearchListing').html(t);
		});
	};

	categorySearchByCode = function(prodCatCode){
		frm = document.frmCustomCatalogProductCategoryForm;
		var data = fcom.frmData(frm);
		fcom.ajax(fcom.makeUrl('Seller', 'searchCategory',[prodCatCode]), (data), function(t) {
			$('#categories-js').hide();
			$('#categorySearchListing').html(t);
		});
	};

	clearCategorySearch = function(){
		window.location.reload();
	};

	setupCustomProduct = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupCustomCatalogProduct'), (data), function(t) {
			runningAjaxReq = false;
			$.mbsmessage.close();
			var addingNew = ($(frm.preq_id).val() == 0);
			customCatalogSellerProductForm(t.preq_id);
			fcom.scrollToTop(dv);
		});
	};

	customCatalogProductImages = function(preqId){
		var data = 'displayLinkNavigation=true';
		fcom.ajax(fcom.makeUrl('Seller', 'customCatalogProductImages', [preqId]), (data), function(t) {
			$(dv).html(t);
			productImages(preqId);
			fcom.scrollToTop(dv);
		});
	};

	productImages = function( preqId,option_id,lang_id ){
		if(typeof option_id == 'undefined'){
			option_id = 0;
		}
		if(typeof lang_id == 'undefined'){
			lang_id = 0;
		}
		fcom.ajax(fcom.makeUrl('Seller', 'customCatalogImages', [preqId,option_id,lang_id]), '', function(t) {
			$('#imageupload_div').html(t);
		});
	};

	customCatalogSellerProductForm = function(preq_id){
		fcom.ajax(fcom.makeUrl('Seller', 'customCatalogSellerProductForm', [ preq_id ]), '', function(t) {
			$(dv).html(t);
		});
	};

	setUpCustomSellerProduct = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpCustomSellerProduct'), (data), function(t) {
			runningAjaxReq = false;
			$.mbsmessage.close();
			if (t.lang_id > 0) {
				customCatalogSpecifications(t.preq_id);
			}
			return ;
		});
	};


	/*............CUSTOM CATALOG SPECIFICATION [............*/

	customCatalogSpecifications = function(preq_id){
		var buttonClick = 0;
		fcom.ajax(fcom.makeUrl('Seller', 'customCatalogSpecifications', [ preq_id ]), '', function(t) {
			$(dv).html(t);
			fcom.scrollToTop(dv);
		});
	};

	getCustomCatalogSpecificationForm = function(preqId,prodSpecId){
		if(typeof prodSpecId == 'undefined'){
			prodSpecId = 0;
		}
		buttonClick++;
		var SpecDiv = "#addSpecFields";
		fcom.ajax(fcom.makeUrl('seller','getCustomCatalogSpecificationForm', [preqId,prodSpecId,buttonClick]), '', function(t){
			$(SpecDiv).append(t);
		});
	};

	setupCustomCatalogSpecification = function(frm, preq_id, prodSpecId){
		if(typeof prodSpecId == 'undefined'){
			prodSpecId = 0;
		}
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupCustomCatalogSpecification',[preq_id, prodSpecId]), data , function(t){
			runningAjaxReq = false;
			$.mbsmessage.close();
			if (t.lang_id>0) {
				customCatalogProductLangForm(t.preqId, t.lang_id);
				return ;
			}else{
				customEanUpcForm(t.preqId);
			}
			fcom.scrollToTop(dv);
			return ;
		});
	};

	removeSpecDiv = function( currentDiv ){
		$('#specification'+currentDiv).remove();
		buttonClick--;

		/* $(".specification").each(function() {
			 str = $(this).attr('id');
			divNumber = str.replace('specification','');
		});
		 */
	};

	/* ] */


	customEanUpcForm = function(preq_id){
		fcom.ajax(fcom.makeUrl('Seller', 'customEanUpcForm', [ preq_id ]), '', function(t) {
			$(dv).html(t);
		});
	};

	validateEanUpcCode = function(upccode){
		var data = {code:upccode};
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'validateUpcCode'),(data), function(t) {
			runningAjaxReq = false;
			$.mbsmessage.close();
			return ;
		});
	};

	setupEanUpcCode = function(preq_id, frm){
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupEanUpcCode',[preq_id]), (data), function(t) {
			runningAjaxReq = false;
			$.mbsmessage.close();
			customCatalogProductImages(preq_id);
			return ;
		});
	};

	customCatalogProductLangForm = function(preq_id,lang_id){
		fcom.resetEditorInstance();
		fcom.ajax(fcom.makeUrl('Seller', 'customCatalogProductLangForm', [preq_id, lang_id]), '', function(t) {
			$(dv).html(t);
			var frm = $(dv+' form')[0];
			var validator = $(frm).validation({errordisplay: 3});
			$(frm).submit(function(e) {
				e.preventDefault();
				if (false === validator.validate() || false == validator.valid) {
					return false;
				}
				var data = fcom.frmData(frm);
				fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupCustomCatalogProductLangForm'), data, function(t) {
					runningAjaxReq = false;
					$.mbsmessage.close();
					fcom.resetEditorInstance();
					if (t.lang_id>0) {
						customCatalogProductLangForm(t.preq_id, t.lang_id);
					}else if (t.productOptions != null) {
						customEanUpcForm(t.preq_id);
					}else{
						customCatalogProductImages(t.preq_id);
					}

					fcom.scrollToTop(dv);
					return ;
				});
			});
		});
	};

	setupCustomCatalogProductImages = function (){
		var data = new FormData(  );
		$inputs = $('#frmCustomCatalogProductImage input[type=text],#frmCustomCatalogProductImage select,#frmCustomCatalogProductImage input[type=hidden]');
		$inputs.each(function() { data.append( this.name,$(this).val());});

		$.each( $('#prod_image')[0].files, function(i, file) {
				$('#imageupload_div').html(fcom.getLoader());
				data.append('prod_image', file);
				$.ajax({
					url : fcom.makeUrl('Seller', 'setupCustomCatalogProductImages'),
					type: "POST",
					data : data,
					processData: false,
					contentType: false,
					success: function(t){
						var ans = $.parseJSON(t);
						$.mbsmessage( ans.msg,true,'alert--success');
						//$.systemMessage( ans.msg );
						productImages( $('#frmCustomCatalogProductImage input[name=preq_id]').val(), $('.option').val(), $('.language').val() );
					},
					error: function(jqXHR, textStatus, errorThrown){
						alert("Error Occured.");
					}
				});
			});
	};

	deleteCustomProductImage = function( preqId, image_id ){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){ return false; }
		fcom.ajax( fcom.makeUrl( 'Seller', 'deleteCustomCatalogProductImage', [preqId, image_id] ), '' , function(t) {
			var ans = $.parseJSON(t);
			$.mbsmessage( ans.msg,true,'alert--success');
			if( ans.status == 0 ){
				return;
			}
			productImages( preqId, $('.option').val(), $('.language').val() );
		});
	};

	/* Product Brand  */
	addBrandReqForm = function(id) {
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('seller', 'addBrandReqForm', [id]), '', function(t) {
				$.facebox(t,'faceboxWidth medium-fb-width');
			});
		});
	};

	setupBrandReq = function(frm) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('seller', 'setupBrandReq'), data, function(t) {
			$.mbsmessage.close();

			if (t.langId>0) {
				addBrandReqLangForm(t.brandReqId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	addBrandReqLangForm = function(brandReqId, langId) {
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('seller', 'brandReqLangForm', [brandReqId, langId]), '', function(t) {
				$.facebox(t);
			});
		});
	};

	setupBrandReqLang = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('seller', 'brandReqLangSetup'), data, function(t) {

			if (t.langId>0) {
				addBrandReqLangForm(t.brandReqId, t.langId);
				return ;
			}
			if (t.openMediaForm)
			{
				brandMediaForm(t.brandReqId);
				return;
			}
			$(document).trigger('close.facebox');
		});
	};

	brandMediaForm = function(brandReqId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('seller', 'brandMediaForm', [brandReqId]), '', function(t) {
				$.facebox(t);
			});
		});
	};

	removeBrandLogo = function( brandReqId, langId ){
		if(!confirm(langLbl.confirmDelete)){return;}
		fcom.updateWithAjax(fcom.makeUrl('seller', 'removeBrandLogo',[brandReqId, langId]), '', function(t) {
			brandMediaForm( brandReqId );
			reloadList();
		});
	}

	checkUniqueBrandName = function(obj,$langId,$brandId){
		data ="brandName="+$(obj).val() + "&langId= "+$langId+ "&brandId= "+$brandId;
		fcom.ajax(fcom.makeUrl('Brands', 'checkUniqueBrandName'), data, function(t) {
			$.mbsmessage.close();
			$res = $.parseJSON(t);

				if($res.status==0){
					$(obj).val('');

					$alertType = 'alert--danger';

					$.mbsmessage($res.msg,true, $alertType);
				}

		});
	};
	/*Product Options*/
	searchOptions = function(form){
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		$("#optionListing").html(langLbl.processing);
		fcom.ajax(fcom.makeUrl('seller','searchOptions'),data,function(res){
			$("#optionListing").html(res);
		});
	};

	reloadOptionList = function() {
		var frm = document.frmOptionsSearchPaging;
		searchOptions(frm);
	};

	optionForm = function(optionId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller', 'optionForm', [optionId]), '', function(t) {
				try{
					res= jQuery.parseJSON(t);
					$.facebox(res.msg,'faceboxWidth');
				}catch (e){
					$.facebox(t,'faceboxWidth');
					addOptionForm(optionId);
					optionValueListing(optionId,false);
				}
			});
		});
		setTimeout(function(){ fcom.resetFaceboxHeight(); }, 700);
	};

	submitOptionForm = function(frm,fn){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupOptions'), data, function(t) {
			reloadOptionList();
			$.mbsmessage.close();
			if(t.optionId > 0){
				optionForm(t.optionId); return;
			}
			$(document).trigger('close.facebox');
		});
	};

	addOptionForm = function(optionId){
		var dv = $('#loadForm');
		fcom.ajax(fcom.makeUrl('Seller', 'addOptionForm', [optionId]), '', function(t) {
			dv.html(t);
		});
	};
	optionValueListing = function(optionId,resetHeight){
		if(typeof resetHeight==undefined || resetHeight == null){
			resetHeight = true;
		}
		if(optionId == 0 ) { $('#showHideContainer').addClass('hide'); return ;}
		var dv =$('#optionValueListing');
		dv.html('Loading....');
		var data = 'option_id='+optionId;
		fcom.ajax(fcom.makeUrl('OptionValues','search'),data,function(res){
			dv.html(res);
		});
		if(resetHeight){
			setTimeout(function(){ fcom.resetFaceboxHeight(); }, 500);
		}
	};

	optionValueForm = function (optionId,id){
		var dv = $('#loadForm');
		fcom.ajax(fcom.makeUrl('OptionValues', 'form', [optionId,id]), '', function(t) {
			dv.html(t);
			jscolor.installByClassName('jscolor');
		});
	};

	setUpOptionValues = function(frm) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('OptionValues', 'setup'), data, function(t) {
			$.mbsmessage.close();
			if (t.optionId > 0 ) {
				optionValueForm(t.optionId,0);
				optionValueListing(t.optionId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	deleteOptionValue = function(optionId,id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id+'&option_id='+optionId;
		fcom.updateWithAjax(fcom.makeUrl('OptionValues','deleteRecord'),data,function(res){
			$.mbsmessage.close();
			optionValueForm(optionId,0);
			optionValueListing(optionId);
		});
	};

	/* Product Tag  */
	addTagForm = function(id) {
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('seller', 'addTagsForm', [id]), '', function(t) {
				$.facebox(t,'faceboxWidth medium-fb-width');
			});
		});
	};

	setupTag = function(frm) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('seller', 'setupTag'), data, function(t) {
			$.mbsmessage.close();
			if (t.langId > 0) {
				addTagLangForm(t.tagId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	addTagLangForm = function(tagId, langId) {
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('seller', 'tagsLangForm', [tagId, langId]), '', function(t) {
				$.facebox(t);
			});
		});
	};

	setupTagLang = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('seller', 'tagLangSetup'), data, function(t) {
			$.mbsmessage.close();
			if (t.langId>0) {
				addTagLangForm(t.tagId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	/* Product shipping  */
	addShippingTab = function(id){
		var ShipDiv = "#tab_shipping";
		var e = document.getElementById("product_type");
		var type = e.options[e.selectedIndex].value;

		if(type == prodTypeDigital){
			$(ShipDiv).html('');
			$('.not-digital-js').hide();
			return;
		}else{
			$('.not-digital-js').show();
		}

		fcom.ajax(fcom.makeUrl('seller','getCustomCatalogShippingTab'),'preq_id='+id,function(t){
			try{
				res = jQuery.parseJSON(t);
				$.facebox(res.msg,'faceboxWidth');
			}catch (e){
				$(ShipDiv).html(t);
			}
		});
	};

	reloadList = function() {
		var frm = document.frmOptionsSearchPaging;
		searchOptions(frm);
	};

	shippingautocomplete = function(shipping_row) {
		$('input[name="product_shipping[' + shipping_row + '][country_name]"]').focusout(function() {
			setTimeout(function(){ $('.suggestions').hide(); }, 500);
		});

		$('input[name="product_shipping[' + shipping_row + '][company_name]"]').focusout(function() {
			setTimeout(function(){ $('.suggestions').hide(); }, 500);
		});

		$('input[name="product_shipping[' + shipping_row + '][processing_time]"]').focusout(function() {
			setTimeout(function(){ $('.suggestions').hide(); }, 500);
		});

		$('input[name="product_shipping[' + shipping_row + '][country_name]"]').autocomplete({
			'source': function(request, response) {
				$.ajax({
					url: fcom.makeUrl('seller', 'countries_autocomplete'),
					data: {keyword: request,fIsAjax:1,includeEverywhere:true},
					dataType: 'json',
					type: 'post',
					success: function(json) {
						response($.map(json, function(item) {
							return {
								label: item['name'] ,
								value: item['id']
							};
						}));
					},
				});
			},
			'select': function(item) {
				$('input[name="product_shipping[' + shipping_row + '][country_name]"]').val(item.label);
				$('input[name="product_shipping[' + shipping_row + '][country_id]"]').val(item.value);
			}
		});

		$('input[name="product_shipping[' + shipping_row + '][company_name]"]').autocomplete({
				'source': function(request, response) {
				$.ajax({
					url: fcom.makeUrl('seller', 'shippingCompanyAutocomplete'),
					data: {keyword: request,fIsAjax:1},
					dataType: 'json',
					type: 'post',
					success: function(json) {
						response($.map(json, function(item) {
							return {
								label: item['name'] ,
								value: item['id']
								};
						}));
					},
				});
			},
			'select': function(item) {
				$('input[name="product_shipping[' + shipping_row + '][company_name]"]').val(item.label);
				$('input[name="product_shipping[' + shipping_row + '][company_id]"]').val(item.value);
			}
		});

		$('input[name="product_shipping[' + shipping_row + '][processing_time]"]').autocomplete({
				'source': function(request, response) {
	 			$.ajax({
					url: fcom.makeUrl('seller', 'shippingMethodDurationAutocomplete'),
					data: {keyword: request,fIsAjax:1},
					dataType: 'json',
					type: 'post',
					success: function(json) {
						response($.map(json, function(item) {
							return {
								label: item['name']+'['+ item['duraion']+']' ,
								value: item['id']
							};
						}));
					},
				});
			},
			'select': function(item) {
				$('input[name="product_shipping[' + shipping_row + '][processing_time]"]').val(item.label);
				$('input[name="product_shipping[' + shipping_row + '][processing_time_id]"]').val(item.value);
			}
		});
	}
	/*  End of  Product shipping  */
})();

$(document).on('click','.uploadFile-Js',function(){
	var node = this;
	$('#form-upload').remove();
	/* var brandId = document.frmProdBrandLang.brand_id.value;
	var langId = document.frmProdBrandLang.lang_id.value; */

	var brandId = $(node).attr( 'data-brand_id' );
	var langId = document.frmBrandMedia.brand_lang_id.value;

	var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
	frm = frm.concat('<input type="file" name="file" />');
	frm = frm.concat('<input type="hidden" name="brand_id" value="' + brandId + '"/>');
	frm = frm.concat('<input type="hidden" name="lang_id" value="' + langId + '"/>');
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
				url: fcom.makeUrl('Seller', 'uploadLogo'),
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
						//$.mbsmessage(ans.msg);
						$('.text-danger').remove();
						$('#input-field').html(ans.msg);
						if( ans.status == true ){
							$('#input-field').removeClass('text-danger');
							$('#input-field').addClass('text-success');
							//brandLangForm( brandId, langId );
							brandMediaForm(ans.brandId);
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
