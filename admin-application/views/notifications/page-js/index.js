$(document).ready(function(){
	listNotifications();
	
	$(".check-all").on('click',function(){
		if($(this).prop('checked') == true){
			$('.check-record').prop('checked',true);
		}else{
			$('.check-record').prop('checked',false);
		}
	});
	
});
(function() {
	var currentPage = 1;
	
	goToSearchPage = function(page) {	
		if(typeof page == undefined || page == null){
			page = 1;
		}		
		var frm = document.frmSearchPaging;		
		$(frm.page).val(page);
		listNotifications(frm);
	};	
	
	listNotifications = function(form,page){
		if (!page) {
			page = currentPage;
		}
		currentPage = page;	
		
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
			
		$("#notificationListing").html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('Notifications','search'),data,function(res){
			$("#notificationListing").html(res);
		});
		$('.check-all').prop('checked', false);
	};

	deleteRecords = function(){
		var recordIdArr = [];
	
		$('.check-record').each(function(i, obj) {
			if($(this).prop('checked') == true){
				recordIdArr.push($(this).attr('rel'));
			}
		});
		
		if(recordIdArr.length < 1){
			return false;
		}
		
		if(!confirm(langLbl.confirmDelete)){return;}
		
		var data = 'record_ids='+recordIdArr;
			
		fcom.updateWithAjax(fcom.makeUrl('Notifications', 'deleteRecords'), data, function(t) {						
			reloadList();	
		});	
	};
	
	changeStatus = function(status){
		var recordIdArr = [];
		$('.check-record').each(function(i, obj) {
			if($(this).prop('checked') == true){
				recordIdArr.push($(this).attr('rel'));
			}
		});	
		if(recordIdArr.length < 1){
			return false;
		}	
		var data = 'record_ids='+recordIdArr+'&status='+status;
	
		fcom.updateWithAjax(fcom.makeUrl('Notifications', 'changeStatus'), data, function(t) {						
			reloadList();	
		});			
	};
	
	reloadList = function(){
		listNotifications();
	};
	
})();