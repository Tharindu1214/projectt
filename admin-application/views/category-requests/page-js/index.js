$(document).ready(function(){
	searchCategoryRequests(document.frmCategoryReqSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmCategoryReqSearchPaging;		
		$(frm.page).val(page);
		searchCategoryRequests(frm);
	}

	reloadList = function() {
		var frm = document.frmCategoryReqSearchPaging;
		searchCategoryRequests(frm);
	}

	addCategoryReqForm = function(id) {			
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('CategoryRequests', 'form', [id]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};

	setupCategoryReq = function(frm) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('CategoryRequests', 'setup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				addCategoryReqLangForm(t.categoryReqId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	addCategoryReqLangForm = function(sCategoryReqId, langId) {		
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('CategoryRequests', 'langForm', [sCategoryReqId, langId]), '', function(t) {
				$.facebox(t);
			});
		});
	};
	
	setupCategoryReqLang = function(frm){ 
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('CategoryRequests', 'langSetup'), data, function(t) {
			reloadList();				
			if (t.langId>0) {
				addCategoryReqLangForm(t.CategoryReqId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	searchCategoryRequests = function(form){		
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		$("#catListing").html('Loading....');
		
		fcom.ajax(fcom.makeUrl('CategoryRequests','search'),data,function(res){
			$("#catListing").html(res);
		});
	};
	
	deleteCategoryReqRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.ajax(fcom.makeUrl('CategoryRequests','deleteRecord'),data,function(res){		
			reloadList();
		});
	};
	
	clearCategoryRequestSearch = function(){
		document.frmCategoryReqSearch.reset();
		searchCategoryRequests(document.frmCategoryReqSearch);
	};
	
	showHideCommentBox = function(val){
		if(val == 2){
			$('#div_comments_box').removeClass('hide');
			//supplierRequestFormValidator['comments']={"required":true};	
		}else{
			$('#div_comments_box').addClass('hide');
			//supplierRequestFormValidator['comments']={"required":false};
		}		
	};
	
})();
