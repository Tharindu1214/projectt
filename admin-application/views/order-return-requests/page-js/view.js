$(document).ready(function(){
	searchOrderReturnRequestMessages(document.frmOrderReturnRequestMsgsSrch);
	
	$(document).on('click','ul.linksvertical li a.redirect--js',function(event){
		event.stopPropagation();
	});	
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
		fcom.updateWithAjax(fcom.makeUrl('OrderReturnRequests','messageSearch'), data, function(ans){
			$.systemMessage.close();
			if( append == 1 ){
				$(dv).find('.loader-Js').remove();
				$(dv).prepend(ans.html);
			} else {
				$(dv).html( ans.html );
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
		fcom.updateWithAjax(fcom.makeUrl( 'orderReturnRequests', 'setUpReturnOrderRequestMessage'), data, function(t) {
			searchOrderReturnRequestMessages( document.frmOrderReturnRequestMsgsSrch );
			fcom.scrollToTop('#frmArea');
			frm.reset();
		});
	};
	
	setupStatus = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('OrderReturnRequests', 'setupUpdateStatus'), data, function(t) {
			window.location.reload();
		});
	};
})();