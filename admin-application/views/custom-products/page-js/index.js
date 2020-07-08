$(document).ready(function(){
	searchListing(document.frmCustomProdReqSrch);	
});
$(document).on('change', '.option-js',function(){
/* $(document).delegate('.option-js','change',function(){ */
	var option_id = $(this).val();
	var preq_id = $('#imageFrm input[name=preq_id]').val();
	var lang_id = $('.language-js').val();
	productImages(preq_id,option_id,lang_id);
});
$(document).on('change', '.language-js',function(){
/* $(document).delegate('.language-js','change',function(){ */
	var lang_id = $(this).val();
	var preq_id = $('#imageFrm input[name=preq_id]').val();
	var option_id = $('.option-js').val();
	productImages(preq_id,option_id,lang_id);
});
(function() {
	var currentPage = 1;
	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page = 1;
		}		
		var frm = document.frmCustomProdReqSrch;				
		searchListing(frm,page);
	};
	
	reloadList = function() {
		searchListing(document.frmCustomProdReqSrchPaging, currentPage);
	};
	
	searchListing = function(form,page){
		if (!page) {
			page = currentPage;
		}
		currentPage = page;	
		var dv = $('#listing');		
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$(dv).html( fcom.getLoader() );	
		data = data+'&page='+currentPage;
		fcom.ajax(fcom.makeUrl('CustomProducts','search'),data,function(t){
			dv.html(t);
		});
	};
	
	goToCustomCatalogProductSearchPage = function(page) {
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmCustomProdReqSrchPaging;		
		$(frm.page).val(page);
		searchListing(frm, page);
	};
	
	clearSearch = function(){
		document.frmCustomProdReqSrch.reset();
		searchListing(document.frmCustomProdReqSrch);
	};
	
	addProductForm = function(preqId){
		$.facebox(function() {productForm(preqId );});
	}
	
	productForm =  function(preqId){
		fcom.displayProcessing();		
		fcom.resetEditorInstance();
		fcom.ajax(fcom.makeUrl('CustomProducts', 'form', [ preqId]), '', function(t) {
				fcom.updateFaceboxContent(t,'faceboxWidth product-setup-width');
		});		
	};
	
	setupProduct = function(frm){
		if ( !$(frm).validate() ) return;		
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'setup'), data, function(t) {				
			reloadList();				
			if (t.preq_id > 0) {
				sellerProductForm(t.preq_id);
				return ;
			}
			$(document).trigger('close.facebox');
			return ;
		});
		return;
	};
	
	sellerProductForm = function(preqId){		
		fcom.ajax(fcom.makeUrl('CustomProducts', 'sellerProductForm', [preqId]), '', function(t) {
			fcom.updateFaceboxContent(t, 'faceboxWidth');		
		});
	};
		
	setupSellerProduct = function(frm){
		if ( !$(frm).validate() ) return;		
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'setupSellerProduct'), data, function(t) {				
			reloadList();				
			/* if (t.lang_id > 0) {
				productLangForm(t.preq_id, t.lang_id);
				return ;
			} */
			customCatalogSpecifications(t.preq_id);
			/* $(document).trigger('close.facebox'); */
			return ;
		});
		return;
	};
	
	
	/*............CUSTOM CATALOG SPECIFICATION [............*/
	
	customCatalogSpecifications = function(preq_id){
		var buttonClick = 0;
		fcom.ajax(fcom.makeUrl('CustomProducts', 'specificationForm', [ preq_id ]), '', function(t) {
			fcom.updateFaceboxContent(t, 'faceboxWidth');
		});
	};
	
	getCustomCatalogSpecificationForm = function(preqId,prodSpecId=0){
		buttonClick++;
		var SpecDiv = "#addSpecFields";
		fcom.ajax(fcom.makeUrl('CustomProducts','getSpecificationForm', [preqId,prodSpecId,buttonClick]), '', function(t){
			$(SpecDiv).append(t);
		});
	};
	
	setupCustomCatalogSpecification = function(frm, preq_id, prodSpecId=0){
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'setupSpecification',[preq_id, prodSpecId]), data , function(t){
			runningAjaxReq = false;
			$.mbsmessage.close();
			if (t.lang_id>0) {
				productLangForm(t.preqId, t.lang_id);
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
	};
	
	/* ] */
	
	
	customEanUpcForm = function(preq_id){
		fcom.ajax(fcom.makeUrl('CustomProducts', 'customEanUpcForm', [ preq_id ]), '', function(t) {
			fcom.updateFaceboxContent(t, 'faceboxWidth');			
		});
	};
	
	validateEanUpcCode = function(upccode){
		var data = {code:upccode};
		fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'validateUpcCode'),(data), function(t) {
			runningAjaxReq = false;
			$.mbsmessage.close();								
			return ;
		});
	};
	
	setupEanUpcCode = function(preq_id, frm){
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'setupEanUpcCode',[preq_id]), (data), function(t) {
			runningAjaxReq = false;
			$.mbsmessage.close();			
			if (t.preq_id > 0) {
				updateStatusForm(t.preq_id);
				return ;
			}		
			return ;
		});	
	};
	
	productLangForm = function(preq_id, lang_id) {
		fcom.displayProcessing();		
		fcom.resetEditorInstance();
		/* $.facebox(function() { */
			fcom.ajax(fcom.makeUrl('CustomProducts', 'langForm', [preq_id, lang_id]), '', function(t) {
				fcom.updateFaceboxContent(t);
				fcom.setEditorLayout(lang_id);					
				var frm = $('#facebox form')[0];
				var validator = $(frm).validation({errordisplay: 3});
				$(frm).submit(function(e) {
					e.preventDefault();
					
					validator.validate();
					if (!validator.isValid()) return;					
					var data = fcom.frmData(frm);
					fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'langSetup'), data, function(t) {
						fcom.resetEditorInstance();
						reloadList();
						if (t.lang_id>0) {
							productLangForm(t.preq_id, t.lang_id);					
							return ;
						}
						if (t.productOptions != null && t.productOptions != '') {
							customEanUpcForm(t.preq_id);
							return ;
						}
						updateStatusForm(t.preq_id);
						return ;
					});
				});
			});
		/* }); */
	};
	
	
	productImagesForm = function( preq_id ){
		fcom.ajax(fcom.makeUrl('CustomProducts', 'imagesForm', [ preq_id ]), '', function(t) {
			productImages(preq_id);
			$.facebox(t, 'faceboxWidth');
		});
	};
	
	productImages = function(  preq_id ,option_id,lang_id ){
		if(typeof option_id == 'undefined'){
			option_id = 0;
		}
		if(typeof lang_id == 'undefined'){
			lang_id = 0;
		}
		
		fcom.ajax(fcom.makeUrl('CustomProducts', 'images', [ preq_id ,option_id,lang_id]), '', function(t) {
			$('#imageupload_div').html(t);
			fcom.resetFaceboxHeight();
		});
	};	
	
	submitImageUploadForm = function ( ){		
		var data = new FormData(  );
		$inputs = $('#imageFrm input[type=text],#imageFrm select,#imageFrm input[type=hidden]');
		$inputs.each(function() { data.append( this.name,$(this).val());});		
		var preq_id = $('#imageFrm input[name="preq_id"]').val();
		$.each( $('#prod_image')[0].files, function(i, file) {
				$('#imageupload_div').html(fcom.getLoader());
				data.append('prod_image', file);
				$.ajax({
					url : fcom.makeUrl('CustomProducts', 'uploadProductImages'),
					type: "POST",
					data : data,
					processData: false,
					contentType: false,
					success: function(t){
						try{
							var ans = $.parseJSON(t);
							productImages( $('#imageFrm input[name=preq_id]').val(), $('.option-js').val(), $('.language-js').val() );
							if( ans.status == 1 ){
								fcom.displaySuccessMessage( ans.msg);
							}else {
								fcom.displayErrorMessage( ans.msg );
							}
						}
						catch(exc){
							productImages( $('#imageFrm input[name=preq_id]').val(), $('.option-js').val(), $('.language-js').val() );
							fcom.displayErrorMessage(t);
						}
					},
					error: function(jqXHR, textStatus, errorThrown){
						alert("Error Occured.");
					}
				});
			});
	};
	
	deleteImage = function( preq_id, image_id ){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){ return false; }
		fcom.ajax( fcom.makeUrl( 'CustomProducts', 'deleteImage', [preq_id, image_id] ), '' , function(t) {
			var ans = $.parseJSON(t);
			if( ans.status == 0 ){
				fcom.displayErrorMessage( ans.msg);
				return;
			}else{
				fcom.displaySuccessMessage( ans.msg);
			}
			productImages( preq_id, $('.option-js').val(), $('.language-js').val() );
		});
	}
	
	updateStatusForm = function(id){
		fcom.resetEditorInstance();
		fcom.ajax(fcom.makeUrl('CustomProducts', 'updateStatusForm', [id]), '', function(t) {
			$.facebox(t, 'faceboxWidth');
		});
	};
	
	updateStatus = function(frm){
		if ( !$(frm).validate() ) return;		
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'updateStatus'), data, function(t) {		
			reloadList();							
			$(document).trigger('close.facebox');
			return ;
		});
		return;
	};
	
	showHideCommentBox = function(val){
		if(val == 2){
			$('#div_comments_box').removeClass('hide');			
		}else{
			$('#div_comments_box').addClass('hide');			
		}		
	};
	
	
	/* Product shipping  */
	addShippingTab = function(id,prodTypeDigital){ 
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
		fcom.ajax(fcom.makeUrl('CustomProducts','getShippingTab'),'preq_id='+id,function(t){
			try{
				res= jQuery.parseJSON(t);
			//	$.facebox(res.msg,'faceboxWidth');
			}catch (e){
				$(ShipDiv).html(t);
			}			
		});
	}
	
	shippingautocomplete = function(shipping_row) {
		$('input[name=\'product_shipping[' + shipping_row + '][country_name]\']').focusout(function() {
			setTimeout(function(){ $('.suggestions').hide(); }, 500); 
		});
		
		$('input[name=\'product_shipping[' + shipping_row + '][company_name]\']').focusout(function() {
			setTimeout(function(){ $('.suggestions').hide(); }, 500);
		});
		
		$('input[name=\'product_shipping[' + shipping_row + '][processing_time]\']').focusout(function() {
			setTimeout(function(){ $('.suggestions').hide(); }, 500);
		});
		
		$('input[name=\'product_shipping[' + shipping_row + '][country_name]\']').autocomplete({
			'source': function(request, response) {
				$.ajax({
					url: fcom.makeUrl('products', 'countries_autocomplete'),
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
				$('input[name=\'product_shipping[' + shipping_row + '][country_name]\']').val(item.label);
				$('input[name=\'product_shipping[' + shipping_row + '][country_id]\']').val(item.value);
			}
		});
		
		$('input[name=\'product_shipping[' + shipping_row + '][company_name]\']').autocomplete({
				'source': function(request, response) {
				$.ajax({
					url: fcom.makeUrl('products', 'shippingCompanyAutocomplete'),
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
				$('input[name=\'product_shipping[' + shipping_row + '][company_name]\']').val(item.label);
				$('input[name=\'product_shipping[' + shipping_row + '][company_id]\']').val(item.value);
			}
		});
		
		$('input[name=\'product_shipping[' + shipping_row + '][processing_time]\']').autocomplete({
				'source': function(request, response) {
				$.ajax({
					url: fcom.makeUrl('products', 'shippingMethodDurationAutocomplete'),
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
				$('input[name=\'product_shipping[' + shipping_row + '][processing_time]\']').val(item.label);
				$('input[name=\'product_shipping[' + shipping_row + '][processing_time_id]\']').val(item.value);
			}
		});
	}
	/*  End of  Product shipping  */
})();	