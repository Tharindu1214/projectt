$(document).ready(function(){
	searchCatalogProducts(document.frmSearchCatalogProduct);
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
		
		fcom.ajax(fcom.makeUrl('sellerProducts','searchCatalogProduct'),data,function(res){
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
	clearSearch = function(){
		document.frmSearchCatalogProduct.reset();
		searchCatalogProducts(document.frmSearchCatalogProduct);
	};
	/* sellerProducts = function(product_id){
		/* if product id is not passed, then it will become or will fetch custom products of that seller. *//*
		if( typeof product_id == undefined || product_id == null ){
			product_id = 0;
		}
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('sellerProducts', 'sellerProducts', [ product_id ]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	} */
	
	/* sellerProductForm = function(product_id, selprod_id){
		$("#sellerProductsForm").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('sellerProducts', 'sellerProductForm', [ product_id, selprod_id ]), '', function(t) {
			$("#sellerProductsForm").html(t);
		});
	}
	
	setUpSellerProduct = function(frm){
		if (!$(frm).validate()) return;
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('sellerProducts', 'setUpSellerProduct'), data, function(t) {
			runningAjaxReq = false;
			$("#sellerProductsForm").html('');
			sellerProducts( $( frm.selprod_product_id ).val() );
		});
	} */
	
	/* sellerProductSpecialPrices = function( selprod_id ){
		$("#sellerProductsForm").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('sellerProducts', 'sellerProductSpecialPrices', [ selprod_id ]), '', function(t) {
			$("#sellerProductsForm").html(t);
		});
	}
	
	sellerProductSpecialPriceForm = function( selprod_id, splprice_id ){
		if(typeof splprice_id==undefined || splprice_id == null){
			splprice_id = 0;
		}
		$("#sellerProductsForm").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('sellerProducts', 'sellerProductSpecialPriceForm', [ selprod_id, splprice_id ]), '', function(t) {
			$("#sellerProductsForm").html(t);
		});
	}
	
	setUpSellerProductSpecialPrice = function(frm){
		if (!$(frm).validate()) return;
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('sellerProducts', 'setUpSellerProductSpecialPrice'), data, function(t) {
			runningAjaxReq = false;
			sellerProductSpecialPrices( $(frm.splprice_selprod_id).val() );
		});
		return false;
	}
	
	deleteSellerProductSpecialPrice = function( splprice_id ){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){
			return false;
		}
		fcom.updateWithAjax(fcom.makeUrl('sellerProducts', 'deleteSellerProductSpecialPrice'), 'splprice_id=' + splprice_id, function(t) {
			sellerProductSpecialPrices( t.selprod_id );
		});
	} */
	
	/* searchCustomProducts = function(frm){
		if( runningAjaxReq == true ){
			console.log("Running Previous request, please try after some time or relaod the page."); 
			/* TODO: not shown in alert, due to language *//*
			return;
		}
		runningAjaxReq = true;
		
		var data = fcom.frmData(frm);
		$('#listing').html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('sellerProducts','searchCustomProduct'),data,function(res){
			runningAjaxReq = false;
			$("#listing").html(res);
		});
	}; */
	
	/* goToCustomProductSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page = 1;
		}
		var frm = document.frmCustomProductSearchPaging;		
		$(frm.page).val(page);
		searchCustomProducts(frm);
	} */
	
	/* customProductForm = function( product_id ){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('sellerProducts', 'customProductForm', [ product_id ]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	} */
	
	/* setupCustomProduct = function(frm){
		if (!$(frm).validate()) return;
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
		var addingNew = ($(frm.product_id).val() == 0);
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('sellerProducts', 'setupCustomProduct'), data, function(t) {
			runningAjaxReq = false;
			reloadCustomProductList();
			if (addingNew) {
				customProductLangForm(t.product_id, t.lang_id);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	}
	
	customProductLangForm = function(product_id, lang_id){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('sellerProducts', 'customProductLangForm', [product_id, lang_id]), '', function(t) {
				$.facebox(t);
			});
		});
	}
	
	setupCustomProductLang = function(frm){
		if ( !$(frm).validate() ) return;
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('sellerProducts', 'setupCustomProductLang'), data, function(t) {
			runningAjaxReq = false;
			reloadCustomProductList();				
			if (t.lang_id>0) {
				customProductLangForm(t.product_id, t.lang_id);
				return ;
			}
			$(document).trigger('close.facebox');
			return ;
		});
		return;
	};
	
	reloadCustomProductList = function(){
		var frm = document.frmSearchCatalogProduct;
		searchCustomProducts(frm);
	};
	
	customProductImages = function( product_id ){
		fcom.ajax(fcom.makeUrl('sellerProducts', 'customProductImages', [product_id]), '', function(t) {
			$.facebox(t, 'faceboxWidth');
		});
	};
	
	setupCustomProductImages = function ( ){		
		var data = new FormData(  );
		$inputs = $('#frmCustomProductImage input[type=text],#frmCustomProductImage select,#frmCustomProductImage input[type=hidden]');
		$inputs.each(function() { data.append( this.name,$(this).val());});		
		
		$.each( $('#prod_image')[0].files, function(i, file) {
				$('#imageupload_div').html(fcom.getLoader());
				data.append('prod_image', file);
				$.ajax({
					url : fcom.makeUrl('sellerProducts', 'setupCustomProductImages'),
					type: "POST",
					data : data,
					processData: false,
					contentType: false,
					success: function(t){
						var ans = $.parseJSON(t);
						$.systemMessage( ans.msg,'alert--danger' );
						customProductImages( $('#frmCustomProductImage input[name=product_id]').val() );
					},
					error: function(jqXHR, textStatus, errorThrown){
						alert("Error Occured.");
					}
				});
			});
	};
	
	deleteCustomProductImage = function( product_id, image_id ){
		var agree = confirm("Do you want to delete record?");
		if( !agree ){ return false; }
		fcom.ajax( fcom.makeUrl( 'sellerProducts', 'deleteCustomProductImage', [product_id, image_id] ), '' , function(t) {
			var ans = $.parseJSON(t);
			$.systemMessage( ans.msg );
			if( ans.status == 0 ){
				return;
			}
			customProductImages( product_id );
		});
	} */
})();