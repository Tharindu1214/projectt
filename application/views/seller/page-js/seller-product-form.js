$(document).ready(function(){
	sellerProductForm(product_id,selprod_id);
});

$(document).on('change','.selprodoption_optionvalue_id',function(){
	var frm = document.frmSellerProduct;
	var data = fcom.frmData(frm);
	fcom.ajax(fcom.makeUrl('Seller', 'checkSellProdAvailableForUser'), data, function(t) {
		var ans = $.parseJSON(t);
		if( ans.status == 0 ){
			$.mbsmessage( ans.msg,false,'alert--danger');
			return;
		}
		$.mbsmessage.close();
	});
});

(function() {
	var runningAjaxReq = false;
	var runningAjaxMsg = 'some requests already running or this stucked into runningAjaxReq variable value, so try to relaod the page and update the same to WebMaster. ';
	//var dv = '#sellerProductsForm';
	var dv = '#listing';

	checkRunningAjax = function(){
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
	};

	loadSellerProducts = function(frm){
		sellerProducts($( frm.product_id ).val());
	};


	sellerProductForm = function(product_id,selprod_id) {
		$(dv).html(fcom.getLoader());
		var dv = '#listing';
			fcom.ajax(fcom.makeUrl('Seller', 'sellerProductGeneralForm', [ product_id, selprod_id ]), '', function(t) {
				runningAjaxReq = false;
				$(dv).html(t);
			});
	};

	setUpSellerProduct = function(frm){

		if (!$(frm).validate()) return;

		runningAjaxReq = true;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpSellerProduct'), data, function(t) {
			runningAjaxReq = false;
			if(t.selprod_id > 0){
				$(frm.splprice_selprod_id).val(t.selprod_id);
			}
			sellerProductLangForm(t.langId,t.selprod_id);
		});
	};

	sellerProductLangForm = function( langId, selprod_id){
		/* alert('hi'); */

		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Seller', 'sellerProductLangForm', [ langId, selprod_id ]), '', function(t) {
			$(dv).html(t);
			fcom.setEditorLayout(langId);
			fcom.setEditorLayout(langId);
		});
	};

	setUpSellerProductLang = function(frm){
		if (!$(frm).validate()) return;
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpSellerProductLang'), data, function(t) {
			runningAjaxReq = false;

			// $.mbsmessage.close();
			if(t.selprod_id > 0){
				$(frm.splprice_selprod_id).val(t.selprod_id);
			}
			if(t.langId > 0){
				sellerProductLangForm(t.langId,t.selprod_id);
				return;
			}
			linkPoliciesForm(t.product_id,t.selprod_id,1);
		});
	};

	sellerProductSpecialPrices = function( selprod_id ){
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Seller', 'sellerProductSpecialPrices', [ selprod_id ]), '', function(t) {
			$(dv).html(t);
			$(document).trigger('close.facebox');
		});
	};

	sellerProductSpecialPriceForm = function( selprod_id, splprice_id ){
		if(typeof splprice_id==undefined || splprice_id == null){
			splprice_id = 0;
		}
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller', 'sellerProductSpecialPriceForm', [selprod_id, splprice_id ]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});

		/* $(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Seller', 'sellerProductSpecialPriceForm', [ selprod_id, splprice_id ]), '', function(t) {
			$(dv).html(t);
		}); */
	};

	setUpSellerProductSpecialPrice = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpSellerProductSpecialPrice'), data, function(t) {
			$.mbsmessage.close();
			sellerProductSpecialPrices( $(frm.splprice_selprod_id).val() );
			$(document).trigger('close.facebox');
		});
		return false;
	};

	deleteSellerProductSpecialPrice = function( splprice_id ){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){
			return false;
		}
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'deleteSellerProductSpecialPrice'), 'splprice_id=' + splprice_id, function(t) {
			$.mbsmessage.close();
			sellerProductSpecialPrices( t.selprod_id );
			$(document).trigger('close.facebox');
		});
	};

	sellerProductVolumeDiscounts = function( selprod_id ){
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Seller', 'sellerProductVolumeDiscounts', [ selprod_id ]), '', function(t) {
			$(dv).html(t);
			$(document).trigger('close.facebox');
		});
	};

	sellerProductVolumeDiscountForm = function( selprod_id, voldiscount_id ){
		if( typeof voldiscount_id == undefined || voldiscount_id == null ){
			voldiscount_id = 0;
		}
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller', 'sellerProductVolumeDiscountForm', [ selprod_id, voldiscount_id ]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};

	setUpSellerProductVolumeDiscount = function( frm ){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpSellerProductVolumeDiscount'), data, function(t) {
			sellerProductVolumeDiscounts( $(frm.voldiscount_selprod_id).val() );
			$.systemMessage.close();
			$(document).trigger('close.facebox');
		});
	};

	deleteSellerProductVolumeDiscount = function( voldiscount_id ){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){
			return false;
		}
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'deleteSellerProductVolumeDiscount'), 'voldiscount_id=' + voldiscount_id, function(t) {
			sellerProductVolumeDiscounts( t.selprod_id );
			$(document).trigger('close.facebox');
		});
	}

	cancelForm = function(frm){
		window.location.href = fcom.makeUrl('seller','products');
	};

	productSeo = function (selprod_id){
		$(dv).html(fcom.getLoader());
		getProductSeoGeneralForm(selprod_id);
		/*fcom.ajax(fcom.makeUrl('Seller', 'productSeo', [ selprod_id ]), '', function(t) {
			$(dv).html(t);
			getProductSeoGeneralForm(selprod_id);
		});*/
	};

	getProductSeoGeneralForm = function (selprod_id){

		fcom.ajax(fcom.makeUrl('Seller', 'productSeoGeneralForm'), 'selprod_id='+selprod_id, function(t) {

			$(dv).html(t);
		});
	}

	setupProductMetaTag = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('seller', 'setupProdMeta'), data, function(t) {
			$.mbsmessage.close();
			editProductMetaTagLangForm(t.metaId, t.langId, t.metaType);
		});
	}

	setupProductLangMetaTag = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('seller', 'setupProdMetaLang'), data, function(t) {
			$.mbsmessage.close();

			if (t.langId > 0) {
				editProductMetaTagLangForm(t.metaId, t.langId, t.metaType);
				return ;
			}

		});

	}

	editProductMetaTagLangForm = function(metaId,langId, metaType){
			fcom.ajax(fcom.makeUrl('seller', 'productSeoLangForm', [metaId,langId,metaType]), '', function(t) {
				$(dv).html(t);
			});

	};

	sellerProductLinkFrm = function( selprod_id ) {
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Seller', 'sellerProductLinkFrm', [ selprod_id ]), '', function(t) {
			$(dv).html(t);
		});
	};

	setUpSellerProductLinks = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupSellerProductLinks'), data, function(t) {
			// $.mbsmessage.close();
			runningAjaxReq = false;
			$(document).trigger('close.facebox');

		});
	};

	sellerProductDownloadFrm = function( selprod_id, type ) {
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Seller', 'sellerProductDownloadFrm', [ selprod_id,type ]), '', function(t) {
			$(dv).html(t);
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
				$(dv).html(fcom.getLoader());
				data.append('downloadable_file', file);
				$.ajax({
					url : fcom.makeUrl('Seller', 'uploadDigitalFile'),
					type: "POST",
					data : data,
					processData: false,
					contentType: false,
					success: function(t){
						var ans = $.parseJSON(t);
						if( ans.status == 0 ){
							$.mbsmessage( ans.msg,true,'alert--danger' );
							sellerProductDownloadFrm(selprod_id, download_type);
							return;
						}
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
			$(dv).html(fcom.getLoader());
			fcom.ajax(fcom.makeUrl('Seller', 'uploadDigitalFile'), data, function(t) {
				var ans = $.parseJSON(t);
				if( ans.status == 0 ){
					$.mbsmessage( ans.msg,true,'alert--danger' );
					return;
				}
				$.systemMessage( ans.msg,'alert--success' );
				sellerProductDownloadFrm(selprod_id, download_type);
			});
		}
	};

	deleteDigitalFile = function(selprod_id,afile_id){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){ return false; }
		fcom.updateWithAjax(fcom.makeUrl('seller', 'deleteDigitalFile', [selprod_id, afile_id]), '', function(t) {
			sellerProductDownloadFrm( selprod_id, 0 );
		});
	}

	linkPoliciesForm = function(product_id,selprod_id,ppoint_type){
		fcom.ajax(fcom.makeUrl('Seller', 'linkPoliciesForm', [product_id,selprod_id,ppoint_type]), '', function(t) {
			$(dv).html(t);
			searchPoliciesToLink();
		});
	};

	searchPoliciesToLink = function(form){
		var form = (form) ? form : document.frmLinkWarrantyPolicies;
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}

		fcom.ajax(fcom.makeUrl('Seller','searchPoliciesToLink'),data,function(res){
			$('#listPolicies').html(res);
		});
	};

	addPolicyPoint = function(selprod_id,ppoint_id){
		var data='selprod_id='+selprod_id+'&ppoint_id='+ppoint_id;

		fcom.ajax(fcom.makeUrl('Seller','addPolicyPoint'),data,function(res){
			searchPoliciesToLink();
		});
	};

	removePolicyPoint = function(selprod_id,ppoint_id){
		var data='selprod_id='+selprod_id+'&ppoint_id='+ppoint_id;
		fcom.ajax(fcom.makeUrl('Seller','removePolicyPoint'),data,function(res){
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

	updateDiscountString = function(){
		var splprice_display_list_price = 0;
		var splprice_display_dis_val = 0;
		var splprice_display_dis_type = 0;

		splprice_display_list_price = $("input[name='splprice_display_list_price']").val();
		if( splprice_display_list_price == '' || typeof splprice_display_list_price == undefined ){
			splprice_display_list_price = 0;
		}

		splprice_display_dis_val = $("input[name='splprice_display_dis_val']").val();
		if( splprice_display_dis_val == '' || typeof splprice_display_dis_val == undefined ){
			splprice_display_dis_val = 0;
		}

		splprice_display_dis_type = $("select[name='splprice_display_dis_type']").val();
		if( splprice_display_dis_type == 0 || typeof splprice_display_dis_type == undefined || typeof splprice_display_dis_type == '' ){
			splprice_display_dis_type = FLAT;
		}
		var data = 'splprice_display_list_price='+splprice_display_list_price+'&splprice_display_dis_val='+splprice_display_dis_val+'&splprice_display_dis_type='+splprice_display_dis_type;
		$("#special-price-discounted-string").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Seller','getSpecialPriceDiscountString'),data,function(res){
			$("#special-price-discounted-string").html( res );
		});
	}

})();

/* $(document).on('click','.digitalFile-Js',function(){
	var node = this;
	$('#form-upload').remove();
	var frmName = $(node).attr('data-frm');
	var lang_id = document.frmDownload.lang_id.value;
	var selprod_id = document.frmDownload.selprod_id.value;
	//var afile_name = document.frmDownload.afile_name.value;

	var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
	frm = frm.concat('<input type="file" name="file" />');
	frm = frm.concat('<input type="hidden" name="selprod_id" value="'+selprod_id+'">');
	//frm = frm.concat('<input type="hidden" name="afile_name" value="'+afile_name+'">');
	frm = frm.concat('<input type="hidden" name="lang_id" value="'+lang_id+'"></form>');
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
				url: fcom.makeUrl('Seller', 'uploadDigitalFile'),
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
					$.mbsmessage.close();
					$.systemMessage.close();
					$('.text-danger').remove();
					//$('#input-field'+fileType).html(ans.msg);
					if(ans.status == true){
						$.mbsmessage( ans.msg,'','alert--success');
						$('#form-upload').remove();
						sellerProductDownloadFrm(selprod_id);
					}else{
						$.mbsmessage(ans.msg,'','alert--danger');
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
	}, 500);
}); */
