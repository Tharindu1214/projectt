$(document).ready(function(){
	searchDiscountCouponsReport( document.frmDiscountCouponsReportSearch );
	
	$('input[name=\'keyword\']').autocomplete({
		'source': function(request, response) {	
			$.ajax({
				url: fcom.makeUrl('DiscountCouponsReport', 'autoCompleteJson'),
				data: {keyword: request, fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['code'], value: item['id'], name: item['code']	};
					}));
				},
			});
		},
		'select': function(item) {
			$("input[name='coupon_id']").val( item['value'] );
			$("input[name='keyword']").val( item['name'] );
		}
	});
	
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';
	
	goToSearchPage = function(page) {	
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmDiscountCouponsReportSearch;		
		$(frm.page).val(page);
		searchDiscountCouponsReport(frm);
	};
	
	searchDiscountCouponsReport = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('DiscountCouponsReport','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	exportReport = function(dateFormat){
		document.frmDiscountCouponsReportSearch.action = fcom.makeUrl('DiscountCouponsReport','export');
		document.frmDiscountCouponsReportSearch.submit();		
	}
	
	clearSearch = function(){
		document.frmDiscountCouponsReportSearch.reset();
		searchDiscountCouponsReport(document.frmDiscountCouponsReportSearch);
	};
})();	