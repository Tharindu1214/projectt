$(document).ready(function(){
	searchMessages(document.frmMessageSrch);
});
(function() {
	var dv = '#messageListing';
	
	searchMessages = function(frm){
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = '';
		if (frm) {
			data = fcom.frmData(frm);
		}
		/*]*/
		$(dv).html( fcom.getLoader() );
		
		fcom.ajax(fcom.makeUrl('Account','messageSearch'), data, function(res){
			$(dv).html(res);
		}); 
	};
	
	goToMessageSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmMessageSrchPaging;		
		$(frm.page).val(page);
		searchMessages(frm);
	};
	
	clearSearch = function(){
		document.frmMessageSrch.reset();
		searchMessages(document.frmMessageSrch);
	};
	
})();	