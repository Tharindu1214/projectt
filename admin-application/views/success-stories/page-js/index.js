$(document).ready(function(){
	searchStories(document.frmSearch);
});

(function() {
	var currentPage = 1;
	var dv = '#listing';
	
	goToSearchPage = function(page) {	
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmStoriesSearchPaging;		
		$(frm.page).val(page);
		searchStories(frm);
	};

	reloadList = function() {
		var frm = document.frmStoriesSearchPaging;
		searchStories(frm);
	};
	
	searchStories = function(form){
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		
		$(dv).html(fcom.getLoader());

		fcom.ajax(fcom.makeUrl('SuccessStories','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	storiesForm = function(id) {
		var frm = document.frmStoriesSearchPaging;			
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('SuccessStories', 'form', [id]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};
	
	setup = function(frm){
		if (!$(frm).validate()) return;	
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('SuccessStories', 'setup'), data, function(t) {
			reloadList();
			if (t.langId > 0) {
				storiesLangForm(t.sstoryId, t.langId);
				return ;
			}			
			$(document).trigger('close.facebox');
		});
	};
	
	storiesLangForm = function(sstoryId, langId) {		
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('SuccessStories', 'langForm', [sstoryId, langId]), '', function(t) {
				$.facebox(t);
			});
		});
	};
	
	setupLang = function(frm){ 
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('SuccessStories', 'langSetup'), data, function(t) {
			reloadList();				
			if (t.langId>0) {
				storiesLangForm(t.sstoryId, t.langId);
				return ;
			}			
			$(document).trigger('close.facebox');
		});
	};
	
	deleteRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.ajax(fcom.makeUrl('SuccessStories','deleteRecord'),data,function(res){		
			reloadList();
		});
	};
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchStories(document.frmSearch);
	};
})();	