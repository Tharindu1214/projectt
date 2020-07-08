$(document).ready(function(){
	searchTaxReport( document.frmTaxReportSearch );
	
	$('input[name=\'shop_name\']').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('Shops', 'autoComplete'),
				data: { keyword: request, fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'] ,	value: item['id']	};
					}));
				},
			});
		},
		'select': function(item) {
			$("input[name='op_shop_id']").val( item['value'] );
			$("input[name='shop_name']").val( item['label'] );
		}
	});
	
	$('input[name=\'user_name\']').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('Users', 'autoCompleteJson'),
				data: { keyword: request, fIsAjax:1, user_is_supplier: 1 },
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'] ,	value: item['id'] };
					}));
				},
			});
		},
		'select': function(item) {
			$("input[name='op_selprod_user_id']").val( item['value'] );
			$("input[name='user_name']").val( item['label'] );
		}
	});
	
	$('input[name=\'shop_name\']').keyup(function(){
		if( $(this).val() == "" ){
			$("input[name='op_shop_id']").val(0);
		}
	});
	
	$('input[name=\'user_name\']').keyup(function(){
		if( $(this).val() == "" ){
			$("input[name='op_selprod_user_id']").val(0);
		}
	});
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';

	goToSearchPage = function( page ) {
		if( typeof page == undefined || page == null ){
			page = 1;
		}
		var frm = document.frmTaxReportSearchPaging;		
		$( frm.page ).val( page );
		searchTaxReport( frm );
	};

	reloadList = function() {
		var frm = document.frmTaxReportSearchPaging;
		searchTaxReport(frm);
	};
	
	searchTaxReport = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('TaxReport','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	exportReport = function(dateFormat){
		document.frmTaxReportSearch.action = fcom.makeUrl('TaxReport','export');
		document.frmTaxReportSearch.submit();		
	}
	
	clearSearch = function(){
		document.frmTaxReportSearch.op_shop_id.value = 0;
		document.frmTaxReportSearch.op_selprod_user_id.value = 0;
		document.frmTaxReportSearch.reset();
		searchTaxReport(document.frmTaxReportSearch);
	};
})();	