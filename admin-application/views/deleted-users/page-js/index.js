$(document).ready(function(){
	
	searchDeletedUsers(document.frmDeletedUserSearch);
	
	$(document).on('click',function(){
		$('.autoSuggest').empty();
	});
	
	$('input[name=\'keyword\']').autocomplete({
		'source': function(request, response) {		
			$.ajax({
				url: fcom.makeUrl('Users', 'autoCompleteJson'),
				data: {keyword: request,deletedUser:true, fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'] +'(' + item['username'] + ')', value: item['id'], name: item['username']	};
					}));
				},
			});
		},
		'select': function(item) {
			$("input[name='user_id']").val( item['value'] );
			$("input[name='keyword']").val( item['name'] );
		}
	});
	
	$('input[name=\'keyword\']').keyup(function(){
		$('input[name=\'user_id\']').val('');
	});
	
	//redirect user to login page
	$(document).on('click','ul.linksvertical li a.redirect--js',function(event){
		event.stopPropagation();
	});	
	
});

(function() {
	var currentPage = 1;
		
	goToSearchPage = function(page) {	
		if(typeof page == undefined || page == null){
			page = 1;
		}		
		var frm = document.frmDeletedUserSearchPaging;		
		$(frm.page).val(page);
		searchDeletedUsers(frm);
	};
		
	searchDeletedUsers = function(form,page){ 
		if (!page) {
			page = currentPage;
		}
		currentPage = page;	
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/	
		
		$("#userListing").html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('deletedUsers','search'),data,function(res){ 
			$("#userListing").html(res);
		});
	};
		
	reloadUserList = function() {
		searchDeletedUsers(document.frmDeletedUserSearchPaging, currentPage);
	};
	
	clearDeletedUserSearch = function(){ 
		document.frmDeletedUserSearch.reset();
		document.frmDeletedUserSearch.user_id.value = '';
		searchDeletedUsers( document.frmDeletedUserSearch );
	};
	
	fillSuggetion = function(v) {
		$('#keyword').val(v);
		$('.autoSuggest').hide();
	};		

	userListing = function(){
		document.location.href = fcom.makeUrl('Users');
	};	

	restoreUser = function(userId){
		if(!confirm(langLbl.confirmRestore)){return;}
		var data = 'user_id='+userId;
		fcom.updateWithAjax(fcom.makeUrl('DeletedUsers', 'restore'), data, function(t) {						
			reloadUserList();			
		});
	};		
})();