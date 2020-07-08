$(document).ready(function(){
	searchOrderReturnRequestMessages(document.frmOrderReturnRequestMsgsSrch);
});
(function() {
	searchOrderReturnRequestMessages = function(frm, append = 0){
		var dv = $("#messagesList");
		var data = fcom.frmData(frm);
		if( append == 1 ){
			$(dv).prepend(fcom.getLoader());
		} else {
			$(dv).html(fcom.getLoader());
		}
		fcom.updateWithAjax(fcom.makeUrl('Account','orderReturnRequestMessageSearch'), data, function(ans){
			$.mbsmessage.close();
			if( append == 1 ){
				$(dv).find('.loader-yk').remove();
				$(dv).prepend(ans.html);
			} else {
				$(dv).html( ans.html );
			}
            
            if ('' == $.trim(ans.html)) {
                $(".messageListBlock--js").hide();
            }
			/* for LoadMore[ */
			$("#loadMoreBtnDiv").html( ans.loadMoreBtnHtml );
			/* ] */
		});
	};
	
	goToOrderReturnRequestMessageSearchPage = function(page) {
		if( typeof page==undefined || page == null ){
			page = 1;
		}		
		var frm = document.frmOrderReturnRequestMsgsSrchPaging;		
		$(frm.page).val(page);
		$("form[name='frmOrderReturnRequestMsgsSrchPaging']").remove();
		searchOrderReturnRequestMessages(frm, 1);
	};
	
	setUpReturnOrderRequestMessage = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl( 'Seller', 'setUpReturnOrderRequestMessage'), data, function(t) {
			searchOrderReturnRequestMessages( document.frmOrderReturnRequestMsgsSrch );
			frm.reset();
		});
	}
})();