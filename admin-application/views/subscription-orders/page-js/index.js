$(document).ready(function(){
	searchSubscriptionOrders(document.frmSubscriptionOrderSearch);
	
	$('input[name=\'buyer\']').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('Users', 'autoCompleteJson'),
				data: {keyword: request, user_is_buyer: 1, fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['credential_email']+' ('+item['username']+')' ,	value: item['id']	};
					}));
				},
			});
		},
		'select': function(item) {
			$("input[name='user_id']").val( item['value'] );
			$("input[name='buyer']").val( item['label'] );
		}
	});
	
	$('input[name=\'buyer\']').keyup(function(){
		if( $(this).val() == "" ){
			$("input[name='user_id']").val( "" );
		}
	});
	
	$(document).on('click','ul.linksvertical li a.redirect--js',function(event){
		event.stopPropagation();
	});		

});
(function() {
	var currentPage = 1;
	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page = 1;
		}		
		var frm = document.frmSubscriptionOrderSearchPaging;		
		$(frm.page).val(page);
		searchSubscriptionOrders(frm);
	}
	
	searchSubscriptionOrders = function(form,page){
		if (!page) {
			page = currentPage;
		}
		currentPage = page;	
		var dv = $('#SubscriptionOrdersListing');		
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		dv.html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('SubscriptionOrders','search'),data,function(res){
			dv.html(res);
		});
	};
	
	cancelOrder = function (id){
		if(!confirm(langLbl.confirmCancelOrder)){return;}		
		fcom.ajax(fcom.makeUrl('cs','cancel',[id]),'',function(res){		
			reloadSubscriptionOrderList();
		});
	};
		
	reloadOrderList = function() {
		searchSubscriptionOrders(document.frmSubscriptionOrderSearchPaging, currentPage);
	};
	
	clearOrderSearch = function(){
		document.frmSubscriptionOrderSearch.reset();
		searchSubscriptionOrders(document.frmSubscriptionOrderSearch);
	};
})();