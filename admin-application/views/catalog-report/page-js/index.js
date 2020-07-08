$(document).ready(function(){
	searchCatalogReport( document.frmCatalogReportSearch );
	
	/* $('input[name=\'shop_name\']').autocomplete({
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
			$("input[name='shop_id']").val( item['value'] );
			$("input[name='shop_name']").val( item['label'] );
		}
	});
	
	$('input[name=\'brand_name\']').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('Brands', 'autoComplete'),
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
			$("input[name='brand_id']").val( item['value'] );
			$("input[name='brand_name']").val( item['label'] );
		}
	});
	
	$('input[name=\'shop_name\']').keyup(function(){
		if( $(this).val() == "" ){
			$("input[name='shop_id']").val(0);
		}
	});
	
	$('input[name=\'brand_name\']').keyup(function(){
		if( $(this).val() == "" ){
			$("input[name='brand_id']").val(0);
		}
	}); */
	
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';

	goToSearchPage = function(page) {
		if( typeof page == undefined || page == null ){
			page = 1;
		}
		var frm = document.frmCatalogReportSearchPaging;		
		$( frm.page ).val( page );
		searchCatalogReport( frm );
	};

	reloadList = function() {
		var frm = document.frmCatalogReportSearchPaging;
		searchCatalogReport(frm);
	};
	
	searchCatalogReport = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('CatalogReport','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	exportReport = function(dateFormat){
		document.frmCatalogReportSearch.action = fcom.makeUrl('CatalogReport','export');
		document.frmCatalogReportSearch.submit();		
	}
	
	clearSearch = function(){
		document.frmCatalogReportSearch.reset();
		searchCatalogReport(document.frmCatalogReportSearch);
	};
})();	