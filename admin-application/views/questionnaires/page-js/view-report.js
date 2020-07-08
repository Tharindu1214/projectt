$(document).ready(function(){
	searchFeedbacks(document.frmFeedbackSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';
	
	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmFeedbackSearchPaging;		
		$(frm.page).val(page);
		searchFeedbacks(frm);
	};
	
	goToNextFeedbackQuestionPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmFeedbackQuestionSearchPaging;		
		$(frm.page).val(page);
		viewFeedback(frm.feedbackId.value,frm.page.value);
	};

	reloadList = function() {
		var frm = document.frmFeedbackSearchPaging;
		searchFeedbacks(frm);
	};
	
	searchFeedbacks = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}		
		$(dv).html();
		
		fcom.ajax(fcom.makeUrl('Questionnaires','searchFeedbacks'),data,function(res){
			$(dv).html(res);
		});
	};
	
	viewFeedback = function(feedbackId,page){
		page = page? page :1;
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Questionnaires', 'viewFeedback', [feedbackId,page]), '', function(t) {				
				$.facebox(t,'faceboxWidth');
			});
		});
	}
	
	clearSearch = function(){
		document.frmFeedbackSearch.reset();
		searchFeedbacks(document.frmFeedbackSearch);
	};

})();
