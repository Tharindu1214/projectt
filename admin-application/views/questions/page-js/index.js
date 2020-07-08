$(document).ready(function(){
	searchQuestions(document.frmQuestionSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#questionListing';
	
	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmQuestionSearchPaging;		
		$(frm.page).val(page);
		searchQuestions(frm);
	};

	reloadList = function() {
		var frm = document.frmQuestionSearchPaging;
		searchQuestions(frm);
	};
	
	questionForm = function(qbankId, questionId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Questions', 'form', [qbankId, questionId]), '', function(t) {				
				$.facebox(t,'faceboxWidth');				
			});
		});
	};

	setupQuestion = function(frm) {
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Questions', 'setup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				questionLangForm(t.questionId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};
	
	questionLangForm =  function(questionId,langId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Questions', 'langForm', [questionId, langId]), '', function(t) {
				$.facebox(t);
			});
		});
	};	
	
	setupQuestionLang = function(frm){ 
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('Questions', 'setupLang'), data, function(t) {
			reloadList();				
			if (t.langId>0) {
				questionLangForm(t.questionId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};
	
	searchQuestions = function(form){				
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}		
		$(dv).html();
		
		fcom.ajax(fcom.makeUrl('Questions','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	deleteRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.ajax(fcom.makeUrl('Questions','deleteRecord'),data,function(res){		
			reloadList();
		});
	};
	
	clearSearch = function(){
		document.frmQuestionSearch.reset();
		searchQuestions(document.frmQuestionSearch);
	};

})();