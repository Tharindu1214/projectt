$(document).ready(function(){
	searchProducts(document.frmSearch);

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
(function() {
	var currentProdId = 0;
	var currentPage = 1;
	var dv = '#listing';
	searchProducts = function(frm){

		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (frm) {
			data = fcom.frmData(frm);
		}
		/*]*/
		var dv = $('#listing');
		$(dv).html( fcom.getLoader() );

		fcom.ajax(fcom.makeUrl('SellerProducts','sellerProducts'),data,function(res){
			$("#listing").html(res);
		});
	};
	addSellerProductForm = function(product_id, selprod_id){
		/* if( !product_id && selprod_id == 0 ){
			return;
		} */
		$.facebox(function() {	sellerProductForm(product_id, selprod_id); });
	};

	sellerProductForm = function(product_id, selprod_id){
		/* if( !product_id && selprod_id == 0 ){
			return;
		} */
		fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProductForm', [ product_id, selprod_id]), '', function(t) {
				fcom.updateFaceboxContent(t);
			});
	};

	setUpSellerProduct = function(frm){
		if (!$(frm).validate()) return;

		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setUpSellerProduct'), data, function(t) {
			if(t.selprod_id > 0){
				$(frm.splprice_selprod_id).val(t.selprod_id);
			}
			if(t.langId > 0){
				sellerProductLangForm(t.selprod_id,t.langId);
			}
		});
	};

	sellerProductLangForm = function( selprod_id, lang_id ){
		fcom.resetEditorInstance();
		fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProductLangForm', [ selprod_id, lang_id ]), '', function(t) {
				fcom.updateFaceboxContent(t);
				fcom.setEditorLayout(lang_id);
			});
	};

	sellerProductDelete=function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts','sellerProductDelete'),data,function(res){
			reloadList();
		});
	};

	setUpSellerProductLang = function(frm){
		if (!$(frm).validate()) return;

		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setUpSellerProductLang'), data, function(t) {
			if(t.selprod_id > 0){
				$(frm.splprice_selprod_id).val(t.selprod_id);
			}
			if(t.langId > 0){
				sellerProductLangForm(t.selprod_id,t.langId);
			}
		});
	};
	addSellerProductSpecialPrices = function( selprod_id ){
		$.facebox(function() { sellerProductSpecialPrices(selprod_id);});
	};


	sellerProductSpecialPrices = function( selprod_id ){
		fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProductSpecialPrices', [ selprod_id ]), '', function(t) {
				fcom.updateFaceboxContent(t);
			});

	};

	sellerProductSpecialPriceForm = function( selprod_id, splprice_id ){
		if(typeof splprice_id==undefined || splprice_id == null){
			splprice_id = 0;
		}
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProductSpecialPriceForm', [selprod_id, splprice_id ]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};

	setUpSellerProductSpecialPrice = function(frm){
		if (!$(frm).validate()) return;

		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setUpSellerProductSpecialPrice'), data, function(t) {
			sellerProductSpecialPrices( $(frm.splprice_selprod_id).val() );
			// $(document).trigger('close.facebox');
		});
		return false;
	};

	deleteSellerProductSpecialPrice = function( splprice_id ){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){ return false; }
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'deleteSellerProductSpecialPrice'), 'splprice_id=' + splprice_id, function(t) {
			// sellerProductSpecialPrices( t.selprod_id );
			$(document).trigger('close.facebox');
		});
	};

	sellerProductVolumeDiscounts = function( selprod_id ){
		fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProductVolumeDiscounts', [ selprod_id ]), '', function(t) {
				fcom.updateFaceboxContent(t);
			});
	};

	sellerProductVolumeDiscountForm = function( selprod_id, voldiscount_id ){
		if( typeof voldiscount_id == undefined || voldiscount_id == null ){
			voldiscount_id = 0;
		}
		$.facebox(function() {
			fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProductVolumeDiscountForm', [selprod_id, voldiscount_id ]), '', function(t) {
				fcom.updateFaceboxContent(t);
			});
		});
	};

	setUpSellerProductVolumeDiscount = function( frm ){
		if (!$(frm).validate()) return;

		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setUpSellerProductVolumeDiscount'), data, function(t) {
			sellerProductVolumeDiscounts( $(frm.voldiscount_selprod_id).val() );
			// $(document).trigger('close.facebox');
		});
		return false;
	};

	deleteSellerProductVolumeDiscount = function( voldiscount_id ){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){ return false; }
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'deleteSellerProductVolumeDiscount'), 'voldiscount_id=' + voldiscount_id, function(t) {
			sellerProductVolumeDiscounts( t.selprod_id );
			$(document).trigger('close.facebox');
		});
	}

	cancelForm = function(frm){
		searchProducts(document.frmSearch);
		$(document).trigger('close.facebox');
	};

	productSeo = function (selprod_id){
		fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('SellerProducts', 'productSeo', [ selprod_id ]), '', function(t) {
				fcom.updateFaceboxContent(t);
				getProductSeoGeneralForm(selprod_id);
			});
	};

	getProductSeoGeneralForm = function (selprod_id){
				fcom.displayProcessing();

			fcom.ajax(fcom.makeUrl('SellerProducts', 'productSeoGeneralForm'), 'selprod_id='+selprod_id, function(t) {
				fcom.updateFaceboxContent(t);
			});

	}

	setupProductMetaTag = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setupProdMeta'), data, function(t) {

			if (t.langId>0) {
				editProductMetaTagLangForm(t.metaId, t.langId, t.metaType);
				return ;
			}

		});
	}

	setupProductLangMetaTag = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setupProdMetaLang'), data, function(t) {

			if (t.langId>0) {
				editProductMetaTagLangForm(t.metaId, t.langId, t.metaType);
				return ;
			}



		});

	}

	editProductMetaTagLangForm = function(metaId,langId, metaType){
		fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('SellerProducts', 'productSeoLangForm', [metaId,langId,metaType]), '', function(t) {
				fcom.updateFaceboxContent(t);
			});
	};

	sellerProductLinkFrm = function( selprod_id ) {
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProductLinkFrm', [ selprod_id ]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	}

	setUpSellerProductLinks = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setupSellerProductLinks'), data, function(t) {
		});
	}

	sellerProductDownloadFrm = function( selprod_id, type ) {
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProductDownloadFrm', [ selprod_id, type ]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};

	setUpSellerProductDownloads = function (type){
		selprod_id = $('#frmDownload input[name=selprod_id]').val();
		download_type = $("select[name='download_type']").val();

		if(download_type == type) {
			var data = new FormData();
			$inputs = $('#frmDownload input[type=text],#frmDownload input[type=textarea],#frmDownload select,#frmDownload input[type=hidden]');
			$inputs.each(function() { data.append( this.name,$(this).val());});

			$.each( $('#downloadable_file')[0].files, function(i, file) {
				data.append('downloadable_file', file);
				$.ajax({
					url : fcom.makeUrl('SellerProducts', 'uploadDigitalFile'),
					type: "POST",
					data : data,
					processData: false,
					contentType: false,
					success: function(t){
						var ans = $.parseJSON(t);
						if( ans.status == 0 ){
							$.systemMessage( ans.msg,'alert alert--danger' );
							return;
						}
						$.systemMessage( ans.msg,'alert alert--success' );
						sellerProductDownloadFrm(selprod_id, download_type);
					},
					error: function(jqXHR, textStatus, errorThrown){
						alert("Error Occurred.");
					}
				});
			});
		}else{
			var data = fcom.frmData(document.frmDownload);
			if (!$('#frmDownload').validate()) return;
			fcom.ajax(fcom.makeUrl('SellerProducts', 'uploadDigitalFile'), data, function(t) {
				var ans = $.parseJSON(t);
				if( ans.status == 0 ){
					$.systemMessage( ans.msg,'alert alert--danger' );
					return;
				}
				$.systemMessage( ans.msg,'alert alert--success' );
				sellerProductDownloadFrm(selprod_id, download_type);
			});
		}
	};

	deleteDigitalFile = function(selprod_id,afile_id){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){ return false; }
		fcom.ajax( fcom.makeUrl( 'SellerProducts', 'deleteDigitalFile', [selprod_id, afile_id] ), '' , function(t) {
			var ans = $.parseJSON(t);
			if( ans.status == 1 ){
				fcom.displaySuccessMessage(ans.msg);
			} else {
				fcom.displayErrorMessage(ans.msg);
			}
			sellerProductDownloadFrm( selprod_id, 0 );
		});
	};

	linkPoliciesForm = function(product_id , selprod_id,ppoint_type){
			fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('SellerProducts', 'linkPoliciesForm', [ product_id, selprod_id, ppoint_type]), '', function(t) {
				fcom.updateFaceboxContent(t);
				searchPoliciesToLink();


			});

	};

	searchPoliciesToLink = function(form){
		var form = (form) ? form : document.frmLinkWarrantyPolicies;
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}

		fcom.ajax(fcom.makeUrl('SellerProducts','searchPoliciesToLink'),data,function(res){
			$('#listPolicies').html(res);	fcom.resetFaceboxHeight();
		});
	};

	addPolicyPoint = function(selprod_id,ppoint_id){
		var data='selprod_id='+selprod_id+'&ppoint_id='+ppoint_id;

		fcom.ajax(fcom.makeUrl('SellerProducts','addPolicyPoint'),data,function(res){
			searchPoliciesToLink();
		});
	};

	removePolicyPoint = function(selprod_id,ppoint_id){
		var data='selprod_id='+selprod_id+'&ppoint_id='+ppoint_id;
		fcom.ajax(fcom.makeUrl('SellerProducts','removePolicyPoint'),data,function(res){
			searchPoliciesToLink();
		});
	};

	goToNextPolicyToLinkPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmPolicyToLinkSearchPaging;
		$(frm.page).val(page);
		searchPoliciesToLink(frm);
	};

	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmProductSearchPaging;
		$(frm.page).val(page);
		searchProducts(frm);
	}

	reloadList = function() {
		var frm = document.frmSearch;
		searchProducts(frm);
	}


	productAttributeGroupForm = function( ){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('SellerProducts', 'productAttributeGroupForm'), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	}

	setupProduct = function(frm) {
		if (!$(frm).validate()) return;
		var addingNew = ($(frm.product_id).val() == 0);
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setUpSellerProduct'), data, function(t) {
			reloadList();
			if (addingNew) {
				productLangForm(t.product_id, t.lang_id);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	setupProductLang = function(frm){
		if ( !$(frm).validate() ) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setUpSellerProductLang'), data, function(t) {
			reloadList();
			if (t.lang_id>0) {
				productLangForm(t.product_id, t.lang_id);
				return ;
			}
			$(document).trigger('close.facebox');
			return ;
		});
		return;
	};

	clearSearch = function(){
		document.frmSearch.reset();
		document.frmSearch.user_id.value = '';
		searchProducts(document.frmSearch);
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
		var selprodId = parseInt(obj.value);
		if( selprodId < 1 ){
			fcom.displayErrorMessage(langLbl.invalidRequest);
			return false;
		}
		data='selprodId='+selprodId;
		fcom.ajax(fcom.makeUrl('SellerProducts','changeStatus'),data,function(res){
		var ans = $.parseJSON(res);
			if( ans.status == 1 ){
				$(obj).toggleClass("active");
				fcom.displaySuccessMessage(ans.msg);
			} else {
				fcom.displayErrorMessage(ans.msg);
			}
		});
	};

	toggleBulkStatues = function(status){
		if(!confirm(langLbl.confirmUpdateStatus)){
			return false;
		}
		$("#frmSelProdListing input[name='status']").val(status);
		$("#frmSelProdListing").submit();
	};

	deleteSelected = function(){
		if(!confirm(langLbl.confirmDelete)){
			return false;
		}
		$("#frmSelProdListing").attr("action",fcom.makeUrl('SellerProducts','deleteSelected')).submit();
	};
    addSpecialPrice = function(){
		if (typeof $(".selectItem--js:checked").val() === 'undefined') {
	        $.systemMessage(langLbl.atleastOneRecord, 'alert--danger');
	        return false;
	    }
		$("#frmSelProdListing").attr({'action': fcom.makeUrl('SellerProducts','specialPrice'), 'target':"_blank"}).removeAttr('onsubmit').submit();
		searchProducts(document.frmSearch);
	};

	addVolumeDiscount = function(){
		if (typeof $(".selectItem--js:checked").val() === 'undefined') {
	        $.systemMessage(langLbl.atleastOneRecord, 'alert--danger');
	        return false;
	    }
		$("#frmSelProdListing").attr({'action': fcom.makeUrl('SellerProducts','volumeDiscount'), 'target':"_blank"}).removeAttr('onsubmit').submit();
		searchProducts(document.frmSearch);
	};
})();
