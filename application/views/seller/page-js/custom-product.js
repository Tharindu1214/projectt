$(document).ready(function(){
	searchCustomProducts(document.frmSearchCustomProduct);
	var productOptions=[] ;	
});
$(document).on('change','.option',function(){
	var option_id = $(this).val();
	var product_id = $('#frmCustomProductImage input[name=product_id]').val();
	var lang_id = $('.language').val();
	productImages(product_id,option_id,lang_id);
});
$(document).on('change','.language',function(){
	var lang_id = $(this).val();
	var product_id = $('#frmCustomProductImage input[name=product_id]').val();
	var option_id = $('.option').val();
	productImages(product_id,option_id,lang_id);
});
(function() {
	var runningAjaxReq = false;
	
		var productId =0;
	checkRunningAjax = function(){
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
	};
	
	searchCustomProducts = function(frm){
		var dv = '#listing';
		checkRunningAjax();
		var data = fcom.frmData(frm);
		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Seller','searchCustomProduct'),data,function(res){
			runningAjaxReq = false;
			$(dv).html(res);
		});
	};
	
	goToCustomProductSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page = 1;
		}
		var frm = document.frmCustomProductSearchPaging;		
		$(frm.page).val(page);
		searchCustomProducts(frm);
	};
	
	reloadCustomProductList = function(){
		var frm = document.frmSearchCatalogProduct;
		searchCustomProducts(frm);
	};
	
	customProductImages = function( productId ){
		fcom.ajax(fcom.makeUrl('Seller', 'customProductImages', [productId]), '', function(t) {
			productImages(productId);
			$.facebox(t, 'faceboxWidth');
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
							$.mbsmessage( ans.msg,true,'alert--success');
						}else{
							$.mbsmessage( ans.msg,true,'alert--danger');
						}
						productImages( $('#frmCustomProductImage input[name=product_id]').val(), $('.option').val(), $('.language').val() );
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
			$.mbsmessage( ans.msg,true,'alert--success');
			if( ans.status == 0 ){
				return;
			}
			productImages( productId, $('.option').val(), $('.language').val() );
		});
	}
	
	clearSearch = function(){
		document.frmSearchCustomProduct.reset();
		searchCustomProducts(document.frmSearchCustomProduct);
	};
	
})();
    
	