$(document).ready(function(){
	searchQuestionnaires(document.frmQuestionnaireSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#questionnaireListing';
	
	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmQuestionnaireSearchPaging;		
		$(frm.page).val(page);
		searchQuestionnaires(frm);
	};

	reloadList = function() {
		var frm = document.frmQuestionnaireSearchPaging;
		searchQuestionnaires(frm);
	};
	
	questionnaireForm = function(questionnaireId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Questionnaires', 'form', [questionnaireId]), '', function(t) {				
				$.facebox(t,'faceboxWidth');				
			});
		});
	};

	setupQuestionnaire = function(frm) {
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Questionnaires', 'setup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				questionnaireLangForm(t.questionnaireId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};
	
	questionnaireLangForm =  function(questionnaireId,langId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Questionnaires', 'langForm', [questionnaireId, langId]), '', function(t) {
				$.facebox(t);
			});
		});
	};	
	
	setupQuestionnaireLang = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);		
		fcom.updateWithAjax(fcom.makeUrl('Questionnaires', 'setupLang'), data, function(t) {
			reloadList();				
			if (t.langId>0) {
				questionnaireLangForm(t.questionnaireId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};
	
	generateLink = function(questionnaireId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Questionnaires', 'generateLink', [questionnaireId]), '', function(t) {
				$.facebox(t,'faceboxWidth');
				SelectText('selectme');
			});
		});
	};

	searchQuestionnaires = function(form){				
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}		
		$(dv).html();
		
		fcom.ajax(fcom.makeUrl('Questionnaires','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	deleteRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.ajax(fcom.makeUrl('Questionnaires','deleteRecord'),data,function(res){		
			reloadList();
		});
	};
	
	clearSearch = function(){
		document.frmQuestionnaireSearch.reset();
		searchQuestionnaires(document.frmQuestionnaireSearch);
	};

})();
