function reviewAbuse(reviewId){
	if(reviewId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Reviews', 'reviewAbuse', [reviewId]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	}
}

function setupReviewAbuse(frm){
	if (!$(frm).validate()) return;
	var data = fcom.frmData(frm);
	fcom.updateWithAjax(fcom.makeUrl('Reviews', 'setupReviewAbuse'), data, function(t) {
		$(document).trigger('close.facebox');
	});
	return false;
}

(function() {

	markReviewHelpful = function(reviewId , isHelpful){
		if( isUserLogged() == 0 ){
			loginPopUpBox();
			return false;
		}
		isHelpful = (isHelpful) ? isHelpful : 0;
		var data = 'reviewId='+reviewId+'&isHelpful=' + isHelpful;
		fcom.updateWithAjax(fcom.makeUrl('Reviews','markHelpful'), data, function(ans){
			 // $.mbsmessage.close();
		});
	}

})();
