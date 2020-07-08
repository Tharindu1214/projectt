
$(document).on('change','.option-js',function(){
	var option_id = $(this).val();
	var product_id = $('#frmCustomProductImage input[name=product_id]').val();
	var lang_id = $('.language-js').val();
	productImages(product_id,option_id,lang_id);
});

$(document).on('change','.language-js',function(){
	var lang_id = $(this).val();
	var product_id = $('#frmCustomProductImage input[name=product_id]').val();
	var option_id = $('.option-js').val();
	productImages(product_id,option_id,lang_id);
});

(function() {
	var runningAjaxMsg = 'some requests already running or this stucked into runningAjaxReq variable value, so try to relaod the page and update the same to WebMaster. ';
	var runningAjaxReq = false;
	var dv = '#listing';

	checkRunningAjax = function(){
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
	};

	searchCatalogProducts = function(frm){
		checkRunningAjax();
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = fcom.frmData(frm);
		/*]*/
		$(dv).html( fcom.getLoader() );

		fcom.ajax(fcom.makeUrl('Seller','searchCatalogProduct'),data,function(res){
			runningAjaxReq = false;
			$(dv).html(res);
		});
	};

	goToCatalogProductSearchPage = function(page){
		if(typeof page==undefined || page == null){
			page = 1;
		}
		var frm = document.frmCatalogProductSearchPaging;
		$(frm.page).val(page);
		searchCatalogProducts(frm);
	}

	productInstructions = function( type ){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller', 'productTooltipInstruction', [type]), '', function(t) {
				$.facebox(t,'faceboxWidth catalog-bg');
			});
		});
	};

	setShippedBySeller = function(product_id){
		var data = 'shippedBy=seller&product_id='+product_id;
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpshippedBy'), data, function(t) {
            var frm = document.frmCatalogProductSearchPaging;
			$(document.frmSearchCatalogProduct.page).val($(frm.page).val());
            searchCatalogProducts(document.frmSearchCatalogProduct);
		});
	};

	setShippedByAdmin = function(product_id){
		var data = 'shippedBy=admin&product_id='+product_id;
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpshippedBy'), data, function(t) {
            var frm = document.frmCatalogProductSearchPaging;
			$(document.frmSearchCatalogProduct.page).val($(frm.page).val());
			searchCatalogProducts(document.frmSearchCatalogProduct);
		});
	};

	sellerShippingForm = function(productId){
		$(dv).html( fcom.getLoader() );

		fcom.ajax(fcom.makeUrl('Seller','sellerShippingForm',[productId]),'',function(res){
			runningAjaxReq = false;
			$(dv).html(res);
		});
	}

	addShippingTab = function(id,sellerId){
		ShipDiv = "#tab_shipping";
		fcom.ajax(fcom.makeUrl('seller','getShippingTab'),'product_id='+id,function(t){
			try{
					res= jQuery.parseJSON(t);
					$.facebox(res.msg,'faceboxWidth');
				}catch (e){

					$(ShipDiv).html(t);
				}

		});
	}

	setupSellerShipping = function(frm){
		if (!$(frm).validate()) return;
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
				var data = fcom.frmData(frm);

		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupSellerShipping'), (data), function(t) {
			runningAjaxReq = false;



			productId =  t.product_id;
			searchCatalogProducts();

		});
	}

	clearSearch = function(){
		document.frmSearchCatalogProduct.reset();
		searchCatalogProducts(document.frmSearchCatalogProduct);
	};

	customProductImages = function( productId ){
		fcom.ajax(fcom.makeUrl('Seller', 'customProductImages', [productId]), '', function(t) {
			productImages(productId);
			$.facebox(t, 'faceboxWidth');
			fcom.resetFaceboxHeight();
		});
	};

	productImages = function( product_id,option_id,lang_id ){
		fcom.ajax(fcom.makeUrl('Seller', 'images', [product_id,option_id,lang_id]), '', function(t) {
			$('#imageupload_div').html(t);
		});
	};

	setupCustomProductImages = function ( ){
		/* if ($.browser.msie && parseInt($.browser.version, 10) === 8 || $.browser.msie && parseInt($.browser.version, 10) === 9) {
			$('#frmCustomProductImage').removeAttr('onsubmit')
			$('#frmCustomProductImage').submit(); return true;
		} */
		var data = new FormData(  );
		$inputs = $('#frmCustomProductImage input[type=text],#frmCustomProductImage select,#frmCustomProductImage input[type=hidden]');
		$inputs.each(function() { data.append( this.name,$(this).val());});

		$.each( $('#prod_image')[0].files, function(i, file) {
				$('#imageupload_div').html(fcom.getLoader());
				data.append('prod_image', file);
				$.ajax({
					url : fcom.makeUrl('Seller', 'setupCustomProductImages'),
					type: "POST",
					data : data,
					processData: false,
					contentType: false,
					success: function(t){
						var ans = $.parseJSON(t);
						if(ans.status == 1){
							$.mbsmessage(ans.msg, true, 'alert--success');
						}else{
							$.mbsmessage(ans.msg, true, 'alert--danger');
						}
						productImages( $('#frmCustomProductImage input[name=product_id]').val(), $('.option').val(), $('.language').val() );
						$('#prod_image').val('');
					},
					error: function(jqXHR, textStatus, errorThrown){
						alert("Error Occured.");
					}
				});
			});
	};

	deleteCustomProductImage = function( productId, image_id ){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){ return false; }
		fcom.ajax( fcom.makeUrl( 'Seller', 'deleteCustomProductImage', [productId, image_id] ), '' , function(t) {
			var ans = $.parseJSON(t);
			$.mbsmessage(ans.msg, true, 'alert--success');
			if( ans.status == 0 ){
				return;
			}
			productImages( productId, $('.option').val(), $('.language').val() );
		});
	}

	checkIfAvailableForInventory = function(product_id) {
		fcom.ajax(fcom.makeUrl('Seller','checkIfAvailableForInventory', [product_id]), '', function(t){
			$res = $.parseJSON(t);
			if($res.status==0){
				$.mbsmessage($res.msg, true, 'alert--danger');
				return false;
			}
			window.location.href = fcom.makeUrl('Seller' ,'sellerProductForm' , [product_id]);
		});
	}

	catalogInfo = function(product_id) {
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller','catalogInfo',[product_id]), '', function(t){
				$.facebox(t,'faceboxWidth catalogInfo');
			});
		});
	}

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
})();
