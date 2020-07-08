$(document).ready(function(){
	searchThreadMessages(document.frmMessageSrch);
});
(function() {
	var dv = '#messageListing';
	var currPage = 1;
	
	searchThreadMessages = function(frm, append ){
		if(typeof append == undefined || append == null){
			append = 0;
		}
		
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = fcom.frmData(frm);
		/*]*/
		if( append == 1 ){
			$(dv).find('.loader-yk').remove();
		} else {
			$(dv).html(fcom.getLoader());
		}
		
		fcom.updateWithAjax(fcom.makeUrl('Account','threadMessageSearch'), data, function(ans){
			$.mbsmessage.close();
			if( append == 1 ){
				$(dv).find('.loader-yk').remove();
				$(dv).prepend(ans.html);
			} else {
				$(dv).html(ans.html);
			}
			$("#loadMoreBtnDiv").html( ans.loadMoreBtnHtml );
		}); 
	};
	
	goToLoadPrevious = function(page) {
		if(typeof page == undefined || page == null){
			page = 1;
		}
		currPage = page;
		var frm = document.frmMessageSrch;		
		$(frm.page).val(page);
		searchThreadMessages(frm,1);
		$(dv).find('.loader-yk').remove();
	};	
	
	sendMessage = function(frm){
		if (!$(frm).validate()) return;	
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Account', 'sendMessage'), data, function(t) {					
			document.frmMessageSrch.reset();
			$(frm.message_text).val('');
			$(document.frmMessageSrch.page).val(0);
			currPage = 1;
			searchThreadMessages(document.frmMessageSrch);						
		});		
	};
	
})();