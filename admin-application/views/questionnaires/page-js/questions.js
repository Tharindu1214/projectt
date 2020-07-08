
$(document).ready(function(){
	searchLinkedQuestions(document.frmLinkedQuestionsSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#linkedQuestionsListing';
	
	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmQuestionsSearchPaging;		
		$(frm.page).val(page);
		searchLinkedQuestions(frm);
	};

	goToNextQuestionToLinkPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmQuestionToLinkSearchPaging;		
		$(frm.page).val(page);
		searchQuestionsToLink(frm);
	};

	reloadList = function() {
		var frm = document.frmQuestionsSearchPaging;
		searchLinkedQuestions(frm);
	};
	
	linkQuestionsForm = function(questionnaireId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Questionnaires', 'linkQuestionsForm', [questionnaireId]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};
	
	setupQuestionToQuestionnaire = function(frm) {
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
	
	searchQuestionsToLink = function(form){
		var form = (form) ? form : document.frmLinkQuestions;
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}		
		
		fcom.ajax(fcom.makeUrl('Questionnaires','searchQuestionsToLink'),data,function(res){
			$('#listQuestionsInQbank').html(res);
		});
	};
	
	searchLinkedQuestions = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}		
		$(dv).html();
		
		fcom.ajax(fcom.makeUrl('Questionnaires','searchLinkedQuestions'),data,function(res){
			$(dv).html(res);
		});
	};
	
	addQuestion = function(questionnaireId,questionId){
		var data='questionnaireId='+questionnaireId+'&questionId='+questionId;
		fcom.ajax(fcom.makeUrl('Questionnaires','addQuestion'),data,function(res){
			searchQuestionsToLink();
			searchLinkedQuestions(document.frmLinkedQuestionsSearch);
		});
	};
	
	removeQuestion = function(questionnaireId,questionId){
		if(!confirm(langLbl.confirmRemove)){return;}
		var data='questionnaireId='+questionnaireId+'&questionId='+questionId;
		fcom.ajax(fcom.makeUrl('Questionnaires','removeQuestion'),data,function(res){
			searchLinkedQuestions(document.frmLinkedQuestionsSearch);
		});
	};
	
	clearSearch = function(){
		document.frmQuestionnaireSearch.reset();
		searchLinkedQuestions(document.frmQuestionnaireSearch);
	};

})();