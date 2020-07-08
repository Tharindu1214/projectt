$(document).ready(function(){
	searchCustomCatalogProducts(document.frmSearchCustomCatalogProducts);
});

$(document).on('change','.option-js',function(){
	var option_id = $(this).val();
	var product_id = $('#frmCustomCatalogProductImage input[name=preq_id]').val();
	var lang_id = $('.language-js').val();
	productImages(product_id,option_id,lang_id);
});

(function() {
	var runningAjaxReq = false;
	var dv = '#listing';

	checkRunningAjax = function(){
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
	};

	searchCustomCatalogProducts = function(frm){
		checkRunningAjax();
		var data = fcom.frmData(frm);
		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Seller','searchCustomCatalogProducts'),data,function(res){
			runningAjaxReq = false;
			$(dv).html(res);
		});
	};

	goToCustomCatalogProductSearchPage = function(page) {
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmSearchCustomCatalogProducts;
		$(frm.page).val(page);
		searchCustomCatalogProducts(frm);
	};

	productInstructions = function( type ){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller', 'productTooltipInstruction', [type]), '', function(t) {
				$.facebox(t,'faceboxWidth catalog-bg');
			});
		});
	};

	clearSearch = function(){
		document.frmSearchCustomCatalogProducts.reset();
		searchCustomCatalogProducts(document.frmSearchCustomCatalogProducts);
	};

	customCatalogProductImages = function(preqId){
		fcom.ajax(fcom.makeUrl('Seller', 'customCatalogProductImages', [preqId]), '', function(t) {
			productImages(preqId);
			$.facebox(t, 'faceboxWidth');
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

	deleteCustomProductImage = function( preqId, image_id ){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){ return false; }
		fcom.ajax( fcom.makeUrl( 'Seller', 'deleteCustomCatalogProductImage', [preqId, image_id] ), '' , function(t) {
			var ans = $.parseJSON(t);
			if( ans.status == 0 ){
				$.mbsmessage(ans.msg, true, 'alert--danger');
				return;
			}
			$.mbsmessage(ans.msg, true, 'alert--success');
			productImages( preqId, $('.option').val(), $('.language').val() );
		});
	}

	setupCustomCatalogProductImages = function ( ){
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
						$.mbsmessage(ans.msg, true, 'alert--success');
						productImages( $('#frmCustomCatalogProductImage input[name=preq_id]').val(), $('.option').val(), $('.language').val() );
					},
					error: function(jqXHR, textStatus, errorThrown){
						alert("Error Occured.");
					}
				});
			});
	};
})();
