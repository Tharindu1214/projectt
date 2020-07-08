$(document).ready(function(){
	searchFaqs(document.frmSearch);
});

(function() {
	var currentPage = 1;
	var dv = '#listing';
	
	goToSearchPage = function(page) {	
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmFaqsSearchPaging;		
		$(frm.page).val(page);
		searchFaqs(frm);
	};
	redirectUrl= function(redirecrt){
		var url=	SITE_ROOT_URL +''+redirecrt;
		window.location=url;

	}

	reloadList = function() {
		var frm = document.frmFaqsSearchPaging;
		searchFaqs(frm);
	};
	
	searchFaqs = function(form){		
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('Faq','search'),data,function(res){
			$(dv).html(res);
		});
	};
	addFaqForm = function(catId,id) {
		//var frm = document.frmFaqsSearchPaging;			
		$.facebox(function() {faqForm(catId,id);
		});
	};
	
	faqForm = function(catId,id) {
		fcom.displayProcessing();
		var frm = document.frmFaqsSearchPaging;			
		fcom.ajax(fcom.makeUrl('Faq', 'form', [catId,id]), '', function(t) {
				//$.facebox(t,'faceboxWidth');
				fcom.updateFaceboxContent(t);
			});
	};
	
	setup = function(frm){
		if (!$(frm).validate()) return;	
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Faq', 'setup'), data, function(t) {
			reloadList();
			if (t.langId > 0) {
				faqLangForm(t.catId,t.faqId, t.langId);
				return ;
			}			
			$(document).trigger('close.facebox');
		});
	};
	
	faqLangForm = function(faqcatId, faqId, langId) {		
		//$.facebox(function() {
			fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('Faq', 'langForm', [faqcatId, faqId, langId]), '', function(t) {
				//$.facebox(t);
				fcom.updateFaceboxContent(t);

			});
		//});
	};
	
	setupLang = function(frm){ 
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('Faq', 'langSetup'), data, function(t) {
			reloadList();				
			if (t.langId>0) {
				faqLangForm(t.catId,t.faqId, t.langId);
				return ;
			}			
			$(document).trigger('close.facebox');
		});
	};
	
	deleteRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('Faq','deleteRecord'),data,function(res){		
			reloadList();
		});
	};
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchFaqs(document.frmSearch);
	};
})();	