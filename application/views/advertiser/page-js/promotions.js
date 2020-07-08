$(document).ready(function(){
	searchPromotions(document.frmPromotionSearch);
});
$(document).on('change','.banner-language-js',function(){;
// $(document).delegate('.banner-language-js','change',function(){
	var lang_id = $(this).val();
	var promotion_id = $("input[name='promotion_id']").val();
	var screen_id = $(".banner-screen-js").val();
	images(promotion_id,lang_id,screen_id);
});
$(document).on('change','.banner-screen-js',function(){;
// $(document).delegate('.banner-screen-js','change',function(){
	var screen_id = $(this).val();
	var promotion_id = $("input[name='promotion_id']").val();
	var lang_id = $(".banner-language-js").val();
	images(promotion_id,lang_id,screen_id);
});
$(document).on('blur',"input[name='promotion_budget']",function(){;
// $(document).delegate("input[name='promotion_budget']",'blur',function(){
	var frm = document.frmPromotion;
	var data = fcom.frmData(frm);
	fcom.ajax(fcom.makeUrl('Advertiser', 'checkValidPromotionBudget'), data, function(t) {
		var ans = $.parseJSON(t);
		if( ans.status == 0 ){
			$.mbsmessage( ans.msg,false,'alert--danger');
			return;
		}
		$.mbsmessage.close();
	});
});
$(document).on('change',"select[name='banner_blocation_id']",function(){
// $(document).delegate("select[name='banner_blocation_id']",'change',function(){
	$("input[name='promotion_budget']").trigger('blur');
});
(function() {
	//var dv = '#promotionForm';
	var dv = '#listing';
	//var litingDv = '#listing';

	goToSearchPage = function(page) {
		if(typeof page == undefined || page == null){
			page =1;
		}
		var frm = document.frmPromotionSearchPaging;
		$(frm.page).val(page);
		searchPromotions(frm);
	};

	reloadList = function() {
		var frm = document.frmPromotionSearchPaging;
		searchPromotions(frm);
		$('.formshowhide-js').show();
	};

	searchPromotions = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Advertiser', 'searchPromotions'),data, function(t) {
			$(dv).html(t);
		});
	};

	promotionForm = function(promotionId) {
		fcom.ajax(fcom.makeUrl('Advertiser', 'promotionForm', [ promotionId]), '', function(t) {
			$(dv).html(t);
			$('.formshowhide-js').hide();
		});
	};

	promotionLangForm = function(promotionId,langId){
		fcom.ajax(fcom.makeUrl('Advertiser', 'promotionLangForm', [ promotionId, langId ]), '', function(t) {
			$(dv).html(t);
		});
	};

	promotionMediaForm = function(promotionId){
		fcom.ajax(fcom.makeUrl('Advertiser', 'promotionMediaForm', [ promotionId ]), '', function(t) {
			$(dv).html(t);
			images(promotionId,0,$(".banner-screen-js").val());
		});
	};

	images = function(promotion_id,lang_id,screen_id){
		fcom.ajax(fcom.makeUrl('Advertiser', 'images', [promotion_id,lang_id,screen_id]), '', function(t) {
			$('#image-listing-js').html(t);
		});
	};

	setupPromotion = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Advertiser', 'setupPromotion'), data, function(t) {
			if(t.langId){
				promotionLangForm(t.promotionId, t.langId);
				return ;
			}
			//promotionForm(t.promotionId);
			return;
		});
	};

	setupPromotionLang = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Advertiser', 'setupPromotionLang'), data, function(t) {
			if(t.langId){
				promotionLangForm(t.promotionId, t.langId);
				return ;
			}else if(typeof t.noMediaTab == undefined || t.noMediaTab == null){
				promotionMediaForm(t.promotionId);
			}
			//promotionForm(t.promotionId);
			return;
		});
	};

	removePromotionBanner = function(promotionId,bannerId,langId,screen){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='promotionId='+promotionId+'&bannerId='+bannerId+'&langId='+langId+'&screen='+screen;
		fcom.updateWithAjax(fcom.makeUrl('Advertiser','removePromotionBanner'),data,function(res){
			images(promotionId,langId,screen);
		});
	};

	/* deletepromotionRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('Advertiser','deletePromotionRecord'),data,function(res){
			reloadList();
		});
	}; */

	clearPromotionSearch = function(){
		document.frmPromotionSearch.reset();
		document.frmPromotionSearch.active_promotion.value = '-1';
		searchPromotions(document.frmPromotionSearch);
	};

	viewWrieFrame = function(locationId){
		if(locationId){
			$.facebox(function() {
				fcom.ajax(fcom.makeUrl('Banner', 'locationFrames', [locationId]), '', function(t) {
					$.facebox(t,'faceboxWidth');
				});
			});
			fcom.resetFaceboxHeight();
		}else{
			alert(langLbl.selectLocation);
		}
	};
})();

$(document).on('click','.bannerFile-Js',function(){
	var node = this;
	$('#form-upload').remove();
	var promotionId = document.frmPromotionMedia.promotion_id.value;

	var promotionType = document.frmPromotionMedia.promotion_type.value;
	var langId = document.frmPromotionMedia.lang_id.value;
	var banner_screen = document.frmPromotionMedia.banner_screen.value;

	var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
	frm = frm.concat('<input type="file" name="file" />');
	frm = frm.concat('<input type="hidden" name="promotion_id" value="'+promotionId+'"/>');
	frm = frm.concat('<input type="hidden" name="lang_id" value="'+langId+'"/>');
	frm = frm.concat('<input type="hidden" name="promotion_type" value="'+promotionType+'"/>');
	frm = frm.concat('<input type="hidden" name="banner_screen" value="'+banner_screen+'"/>');
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
				url: fcom.makeUrl('Advertiser', 'promotionUpload',[promotionId]),
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
					if(ans.status == true){
						$.mbsmessage( ans.msg, '', 'alert--success');
					}else{
						$.mbsmessage( ans.msg, '', 'alert--danger');
					}
					$('#form-upload').remove();
					images(promotionId,langId,banner_screen);
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
	}, 500);
});
