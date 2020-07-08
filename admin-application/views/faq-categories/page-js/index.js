$(document).ready(function(){
	searchFaqCategories(document.frmSearch);
});

(function() {
	var currentPage = 1;
	var dv = '#listing';

	goToSearchPage = function(page) {
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmFaqCatSearchPaging;
		$(frm.page).val(page);
		searchFaqCategories(frm);
	};

	redirectUrl= function(redirecrt){
		var url=	SITE_ROOT_URL +''+redirecrt;
		window.location=url;

	}
	reloadList = function() {
		var frm = document.frmFaqCatSearchPaging;
		searchFaqCategories(frm);
	};
	searchFaqCategories = function(form){
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/

		$(dv).html(fcom.getLoader());

		fcom.ajax(fcom.makeUrl('FaqCategories','search'),data,function(res){
			$(dv).html(res);
		});
	};
	faqToCmsForm = function() {
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('FaqCategories', 'faqToCmsForm'), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};

	setupFaqToCms = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('FaqCategories', 'setupFaqToCms'), data, function(t) {
			$(document).trigger('close.facebox');
		});
	};
	addFaqCatForm = function(id) {
		//var frm = document.frmFaqCatSearchPaging;
		$.facebox(function() { faqCatForm(id); });
	};

	faqCatForm = function(id) {
		fcom.displayProcessing();
		var frm = document.frmFaqCatSearchPaging;
	//	$.facebox(function() {
			fcom.ajax(fcom.makeUrl('FaqCategories', 'form', [id]), '', function(t) {
				//$.facebox(t,'faceboxWidth');
				fcom.updateFaceboxContent(t);
			});
		//});
	};
	setup = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('FaqCategories', 'setup'), data, function(t) {
			reloadList();
			if (t.langId > 0) {
				faqCatLangForm(t.catId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	faqCatLangForm = function(faqcatId, langId) {
		//$.facebox(function() {
			fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('FaqCategories', 'langForm', [faqcatId, langId]), '', function(t) {
				//$.facebox(t);
				fcom.updateFaceboxContent(t);
			});
		//});
	};

	setupLang = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('FaqCategories', 'langSetup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				faqCatLangForm(t.catId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	deleteRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('FaqCategories','deleteRecord'),data,function(res){
			reloadList();
		});
	};

	clearSearch = function(){
		document.frmSearch.reset();
		searchFaqCategories(document.frmSearch);
	};

	toggleStatus = function( e,obj,canEdit ){
		if(canEdit == 0){
			e.preventDefault();
			return;
		}
		if(!confirm(langLbl.confirmUpdateStatus)){
			e.preventDefault();
			return;
		}
		var faqcatId = parseInt(obj.value);
		if( faqcatId < 1 ){
			fcom.displayErrorMessage(langLbl.invalidRequest);
			//$.mbsmessage(langLbl.invalidRequest,true,'alert--danger');
			return false;
		}
		data = 'faqcatId='+faqcatId;
		fcom.ajax(fcom.makeUrl('FaqCategories','changeStatus'),data,function(res){
			var ans =$.parseJSON(res);
			if(ans.status == 1){
				$(obj).toggleClass("active");
				setTimeout(function(){ reloadList(); }, 1000);
				fcom.displaySuccessMessage(ans.msg);
				//$.mbsmessage(ans.msg,true,'alert--success');
			}else{
				fcom.displayErrorMessage(ans.msg);
				//$.mbsmessage(ans.msg,true,'alert--danger');
			}
		});
	};

	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmFaqCatListing input[name='status']").val(status);
        $("#frmFaqCatListing").submit();
    };

    deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmFaqCatListing").attr("action",fcom.makeUrl('FaqCategories','deleteSelected')).submit();
    };

})();
