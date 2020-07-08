$(document).ready(function(){
	searchCategoryBanners(document.frmSearchCatBanners);
});

(function() {
	var runningAjaxReq = false;
	var dv = '#listing';
	
	searchCategoryBanners = function(frm){ 		
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = fcom.frmData(frm);
		/*]*/
		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Seller','searchCategoryBanners'),data,function(res){			
			$(dv).html(res);
		});
	};
	
	reloadList = function(){
		searchCategoryBanners(document.frmCategoryBannerSrchPaging);
	};
	
	goToCategoryBannerSrchPage = function(page){
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmCategoryBannerSrchPaging;		
		$(frm.page).val(page);
		searchCategoryBanners(frm);
	};
	
	addCategoryBanner = function(prodCatId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller', 'addCategoryBanner',[prodCatId]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};	
	
	removeBanner = function (prodCatId){
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'removeCategoryBanner',[prodCatId]), '', function(t) {
			reloadList();
			addCategoryBanner(prodCatId);
		});
	};
	
})();	

$(document).on('click','.catFile-Js',function(){
	var node = this;
	$('#form-upload').remove();
	var prodcat_id = $(node).attr('data-prodcat_id');
	var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
	frm = frm.concat('<input type="file" name="file" />'); 
	frm = frm.concat('<input type="hidden" name="prodcat_id" value="'+prodcat_id+'"></form>'); 
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
				url: fcom.makeUrl('Seller', 'uploadCategoryBanner'),
				type: 'post',
				dataType: 'json',
				data: new FormData($('#form-upload')[0]),
				cache: false,
				contentType: false,
				processData: false,
				beforeSend: function() {
					$(node).val('loading..');
				},
				complete: function() {
					$(node).val($val);
				},
				success: function(ans) {	
						var dv = '#mediaResponse';						
						$('.text-danger').remove();
						$(dv).html(ans.msg);						
						if(ans.status == true){
							$(dv).removeClass('text-danger');
							$(dv).addClass('text-success');
							reloadList();
							addCategoryBanner(ans.prodCatId);	
						}else{
							$(dv).removeClass('text-success');
							$(dv).addClass('text-danger');
						}												
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});			
		}
	}, 500);
});