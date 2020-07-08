/* $(document).ready(function(){
	customProductForm(productId, productCatId);
	var productOptions=[] ;
}); */
(function() {
	var runningAjaxReq = false;

	checkRunningAjax = function(){
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
	};

	var dv = '#listing';
	var prodCatId = 0;
	var blockCount = 0;

	customProductForm = function( productId, prodcat_id ){
		$(dv).html( fcom.getLoader() );
		if( (typeof productId == 'undefined' || productId == 0) && (typeof prodcat_id == 'undefined' || prodcat_id == 0) ){
			customCatalogProductCategoryForm( );
			return;
		}

		fcom.ajax(fcom.makeUrl('Seller', 'customProductGeneralForm', [ productId, prodcat_id ]), '', function(t) {
			$(dv).html(t);
		});
	};

	customCatalogProductCategoryForm = function(){
		fcom.ajax(fcom.makeUrl('Seller', 'customCatalogProductCategoryForm'), '', function(t) {
			$(dv).html(t);
			customCategoryListing(prodCatId,blockCount);
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
				if( $('.box-categories ul').length == 1){
					$('.slick-next').css('pointer-events', 'none');
					$('.slick-next').addClass('slick-disabled');
				}
			}
			prodCatId = ans.prodcat_id;
		});
	};

	customCatalogProductForm = function( id, prodcat_id ){
		$(dv).html( fcom.getLoader() );
		if( typeof id == 'undefined' || id == 0 && (typeof prodcat_id == 'undefined' || prodcat_id == 0)){
			customCatalogProductCategoryForm( );
			return;
		}

		fcom.ajax(fcom.makeUrl('Seller', 'customProductGeneralForm', [ id , prodcat_id ]), '', function(t) {
			$(dv).html(t);
		});
	};

	searchCategory = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.ajax(fcom.makeUrl('Seller', 'searchCategory'), (data), function(t) {
            //console.log('called');
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


	/* customProductForm = function( productId ){
		fcom.resetEditorInstance();
		fcom.ajax(fcom.makeUrl('Seller', 'customProductGeneralForm', [ productId ]), '', function(t) {
			$(dv).html(t);
		});
	}; */

	setupCustomProduct = function(frm){
		if (!$(frm).validate()) return;

		addingNew = ($(frm.product_id).val() == 0);
		$(frm.product_options).val(productOptions);
		var data = fcom.frmData(frm);

		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupCustomProduct'), (data), function(t) {

			$.mbsmessage.close();

			if (addingNew) {
				customProductLangForm(t.product_id, t.lang_id);
				/* localStorage.setItem("productId", t.product_id); */
				return ;
			}
			productId =  t.product_id;
			addShippingTab(t.product_id,t.product_type);
		});
	};

	customProductLangForm = function(productId, lang_id){
		fcom.resetEditorInstance();
			fcom.ajax(fcom.makeUrl('Seller', 'customProductLangForm', [productId, lang_id]), '', function(t) {
				$(dv).html(t);
				fcom.setEditorLayout(lang_id);

			});
	};

	productOptionsForm = function( id ){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller', 'customProductOptionsForm', [id]), '', function(t) {
				fcom.updateFaceboxContent(t,'faceboxWidth');
				reloadProductOptions(id);
			});
		});
	};

	optionForm = function(optionId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller', 'optionForm', [optionId]), '', function(t) {
				try{
					res = jQuery.parseJSON(t);
					$.facebox(res.msg,'faceboxWidth');
				}catch (e){
					$.facebox(t,'faceboxWidth');
					addOptionForm(optionId);
					optionValueListing(optionId);
				}
			});
		});
		fcom.resetFaceboxHeight();
	};

	addOptionForm = function(optionId){
		var dv = $('#loadForm');
		fcom.ajax(fcom.makeUrl('Seller', 'addOptionForm', [optionId]), '', function(t) {
			dv.html(t);
		});
	};

	optionValueListing = function(optionId){
		if(optionId == 0 ) { $('#showHideContainer').addClass('hide'); return ;}
		var dv =$('#optionValueListing');
		dv.html('Loading....');
		var data = 'option_id='+optionId;
		fcom.ajax(fcom.makeUrl('OptionValues','search'),data,function(res){
			dv.html(res);
		});
		fcom.resetFaceboxHeight();
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
	}

	optionValueSearchPage = function(page){
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSearchOptionValuePaging;
		$(frm.page).val(page);
		searchOptionValueListing(frm);
	};

	searchOptionValueListing = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$("#optionValueListing").html('Loading....');
		fcom.ajax(fcom.makeUrl('OptionValues','search'),data,function(res){
			$("#optionValueListing").html(res);
		});
	};

	showHideValues = function(obj){

		var type =obj.value;
		var data ='optionType='+type;
		fcom.ajax(fcom.makeUrl('Options','canSetValue'),data,function(t){
			var res = $.parseJSON(t);
			if(res.hideBox == true){
				$('#showHideContainer').addClass('hide'); return ;
			}
			$('#showHideContainer').removeClass('hide');
		});
	};

	submitOptionForm=function(frm,fn){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupOptions'), data, function(t) {
			reloadList();
			$.mbsmessage.close();
			if(t.optionId > 0){
				optionForm(t.optionId); return;
			}
			$(document).trigger('close.facebox');
		});
	};

	searchOptions = function(form){
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		$("#optionListing").html('Loading....');

		fcom.ajax(fcom.makeUrl('seller','searchOptions'),data,function(res){
			$("#optionListing").html(res);
		});
	};

	deleteOptionRecord=function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.ajax(fcom.makeUrl('seller','deleteSellerOption'),data,function(t){
			var ans= jQuery.parseJSON(t);
			if(ans.status!=1){
				$.mbsmessage(ans.msg, true, 'alert--danger');
			}
			$.mbsmessage(ans.msg, true, 'alert--success');
			reloadList();

		});
	};

	reloadList = function() {
		var frm = document.frmOptionsSearchPaging;
		searchOptions(frm);
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
		fcom.ajax(fcom.makeUrl('seller','getShippingTab'),'product_id='+id,function(t){
			try{
					res= jQuery.parseJSON(t);
					$.facebox(res.msg,'faceboxWidth');
				}catch (e){

					$(ShipDiv).html(t);
				}

		});
	}

	optionForm = function(optionId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller', 'importProductShippingRate', [optionId]), '', function(t) {
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
	
	importProductShippingRateFile = function(method, actionType) {
        var data = new FormData();
        $inputs = $('#frmImportExport input[type=text],#frmImportExport select,#frmImportExport input[type=hidden]');
        $inputs.each(function() {
            data.append(this.name, $(this).val());
        });
        if ($('#import_file')[0].files.length == 0) {
            $.mbsmessage(langLbl.selectFile, false, 'alert--danger');
        }
        $.each($('#import_file')[0].files, function(i, file) {
            $.mbsmessage(langLbl.processing, false, 'alert--process');
            $('#fileupload_div').html(fcom.getLoader());
            data.append('import_file', file);
            $.ajax({
                url: fcom.makeUrl('Seller', method, [actionType]),
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
                            if ('importData' == method) {
                                importForm(actionType);
                            } else {
                                importMediaForm(actionType);
                            }
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


	shippingautocomplete = function(shipping_row) {
		$('input[name="product_shipping[' + shipping_row + '][city_name]"]').focusout(function() {
			    setTimeout(function(){ $('.suggestions').hide(); }, 500);
		});

		$('input[name="product_shipping[' + shipping_row + '][company_name]"]').focusout(function() {
			    setTimeout(function(){ $('.suggestions').hide(); }, 500);
		});

		$('input[name="product_shipping[' + shipping_row + '][processing_time]"]').focusout(function() {
			    setTimeout(function(){ $('.suggestions').hide(); }, 500);
		});
		$('input[name="product_shipping[' + shipping_row + '][city_name]"]').autocomplete({
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
				$('input[name="product_shipping[' + shipping_row + '][city_name]"]').val(item.label);
				$('input[name="product_shipping[' + shipping_row + '][city_id]"]').val(item.value);
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
/* Custom Product Options */
	sellerCustomProductOptions = function(id){
		var dv = '#listing';
			fcom.ajax(fcom.makeUrl('Seller', 'customProductOptions', [id]), '', function(t) {
				$(dv).html(t);
				reloadProductOptions(id);

			});

	}

	updateProductOption = function (product_id, option_id){
		fcom.ajax(fcom.makeUrl('Seller', 'updateProductOption'), 'product_id='+product_id+'&option_id='+option_id, function(t) {
			$.mbsmessage.close();
			reloadProductOptions(product_id);
		});
	}

	removeProductOption = function( product_id,option_id){
		fcom.ajax(fcom.makeUrl('Seller', 'checkOptionLinkedToInventory'), 'product_id='+product_id+'&option_id='+option_id, function(t) {
			ans = jQuery.parseJSON(t);
			if( ans.status != true ){
				var agree = alert(ans.msg);
                return false;
			}
			fcom.ajax(fcom.makeUrl('Seller', 'removeProductOption'), 'product_id='+product_id+'&option_id='+option_id, function(t) {
                var ans = $.parseJSON(t);
                if( ans.status == 0 ){
                    return;
                }
                $.mbsmessage(ans.msg, true, 'alert--success');
                reloadProductOptions(product_id);
			});
		});
	};

	reloadProductOptions = function( productId){

		$("#product_options_list").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('seller', 'ProductOptions', [productId]),'', function(t) {
			$("#product_options_list").html(t);
		});
	}

	/* Custom Product Options */

	/* Custom product Specifications */

	sellerCustomProductSpecifications = function(id){
		fcom.ajax(fcom.makeUrl('Seller', 'customProductSpecifications', [id]), '', function(t) {
			var dv = '#listing';
			$(dv).html(t);
			reloadProductSpecifications(id);



		});
	}
	reloadProductSpecifications = function( productId){

		$("#product_specifications_list").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('seller', 'ProductSpecifications', [productId]),'', function(t) {
			try{
					res= jQuery.parseJSON(t);

					$("#product_specifications_list").html(res.msg);
				}catch (e){

					$("#product_specifications_list").html(t);
				}

		});
	}
	addProdSpec = function(productId,prodSpecId){
		fcom.ajax(fcom.makeUrl('seller', 'prodSpecForm', [productId]),'prodSpecId='+prodSpecId, function(t) {
			$.facebox(t,'faceboxWidth');

		});
	}
	deleteProdSpec = function(productId,prodSpecId){
		fcom.updateWithAjax(fcom.makeUrl('seller', 'deleteProdSpec', [productId]),'prodSpecId='+prodSpecId, function(t) {
			sellerCustomProductSpecifications(productId);
			reloadProductSpecifications(productId);

		});
	}

	submitSpecificationForm = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupProductSpecifications'), data, function(t) {
			$.mbsmessage.close();
			sellerCustomProductSpecifications(t.productId);
			reloadProductSpecifications(t.productId);
			if(t.productId > 0){
				if(t.prodSpecId < 1)
				{
					addProdSpec(t.productId);
				}else{
					$(document).trigger('close.facebox');
				}
				(t.productId); return;
			}

		});
		return false;
	}
	customProductLinks = function( product_id ){
			fcom.resetEditorInstance();
		fcom.ajax(fcom.makeUrl('Seller', 'customProductLinks', [product_id]), '', function(t) {
			$(dv).html(t);
			reloadProductLinks(product_id);
			});
	}

	updateProductLink = function (product_id, option_id){
	//reloadProductLinks(product_id);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'updateProductLink'), 'product_id='+product_id+'&option_id='+option_id, function(t) {
			reloadProductLinks(product_id);
		});
	}

	removeProductCategory = function(product_id, option_id){
		var agree = confirm(langLbl.confirmDeleteOption);
		if(!agree){ return false; }
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'removeProductCategory'), 'product_id='+product_id+'&option_id='+option_id, function(t) {
			reloadProductLinks(product_id);
		});
	};

	reloadProductLinks = function( product_id ){
		$("#product_links_list").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Seller', 'productLinks', [product_id]), '', function(t) {
			$("#product_links_list").html(t);
		});
	}

	setupProductLinks = function(frm){
		$('input[name="product_category"]').val($('input[name="list_category"]').val());
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupProductLinks'), data, function(t) {
			$(document).trigger('close.facebox');
		});
	}

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
			reloadList();
			if (t.langId>0) {
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
			reloadList();
			if (t.langId>0) {
				addTagLangForm(t.tagId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};
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
	}

	brandMediaForm = function(brandId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('seller', 'brandMediaForm', [brandId]), '', function(t) {
				$.facebox(t);
			});
		});
	};


	removeBrandLogo = function( brandId, langId ){
		if(!confirm(langLbl.confirmDelete)){return;}
		fcom.updateWithAjax(fcom.makeUrl('seller', 'removeBrandLogo',[brandId, langId]), '', function(t) {
			brandMediaForm( brandId );
			reloadList();
		});
	}

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
