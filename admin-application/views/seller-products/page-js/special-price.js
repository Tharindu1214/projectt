$(document).ready(function(){
    searchSpecialPriceProducts(document.frmSearch);
    $('.date_js').datepicker('option', {minDate: new Date()});
});
$(document).on('keyup', "input[name='product_name']", function(){
    var currObj = $(this);
    var parentForm = currObj.closest('form').attr('id');
    if('' != currObj.val()){
        currObj.siblings('ul.dropdown-menu').remove();
        currObj.autocomplete({'source': function(request, response) {
        		$.ajax({
        			url: fcom.makeUrl('SellerProducts', 'autoCompleteProducts'),
        			data: {keyword: request,fIsAjax:1,keyword:currObj.val()},
        			dataType: 'json',
        			type: 'post',
        			success: function(json) {
        				response($.map(json, function(item) {
        					return { label: item['name'], value: item['id']	};
        				}));
        			},
        		});
        	},
        	'select': function(item) {
                $("#"+parentForm+" input[name='splprice_selprod_id']").val(item['value']);
                currObj.val( (item['label']).replace(/<[^>]+>/g, ''));
        	}
        });
    }else{
        $("#"+parentForm+" input[name='splprice_selprod_id']").val('');
    }
});

$(document).on('keyup', "input[name='product_seller']", function(){
    var currObj = $(this);
    currObj.siblings('ul.dropdown-menu').remove();
    currObj.autocomplete({
        'source': function(request, response) {
            if( '' != request ){
                $.ajax({
                    url: fcom.makeUrl('Products', 'autoCompleteSellerJson'),
                    data: {keyword: request},
                    dataType: 'json',
                    type: 'post',
                    success: function(json) {
                        response($.map(json, function(item) {
                            var email = '';
                            if( null !== item['credential_email'] ){
                                email = ' ('+item['credential_email']+')';
                            }
                            return { label: item['credential_username'] + email,    value: item['credential_user_id']    };
                        }));
                    },
                });
            }else{
                $("input[name='product_seller_id']").val('');
            }
        },
        'select': function(item) {
            $("input[name='product_seller_id']").val( item['value'] );
            $("input[name='product_seller']").val( item['label'] );
        }
    });
});

$(document).on('click', 'table.splPriceList-js tr td .js--editCol', function(){
    $(this).hide();
    var input = $(this).siblings('input[type="text"]');
    var value = input.attr('value');
    input.removeClass('hide');
    input.val('').focus().val(value);
});

$(document).on('blur', ".js--splPriceCol.date_js", function(){
    var currObj = $(this);
    var oldValue = currObj.attr('data-oldval');
    showElement(currObj, oldValue);
});
$(document).on('change', ".js--splPriceCol.date_js", function(){
    updateValues($(this));
});

$(document).on('blur', ".js--splPriceCol:not(.date_js)", function(){
    updateValues($(this));
});

(function() {
	var dv = '#listing';
	searchSpecialPriceProducts = function(frm){

		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (frm) {
			data = fcom.frmData(frm);
		}
		/*]*/
		var dv = $('#listing');
		$(dv).html( fcom.getLoader() );

		fcom.ajax(fcom.makeUrl('SellerProducts','searchSpecialPriceProducts'),data,function(res){
			$("#listing").html(res);
            $('.date_js').datepicker('option', {minDate: new Date()});
		});
	};
    clearSearch = function(selProd_id){
       if (0 < selProd_id) {
           location.href = fcom.makeUrl('SellerProducts','specialPrice');
       } else {
           document.frmSearch.reset();
           document.frmSearch.product_seller_id.value = '';
           searchSpecialPriceProducts(document.frmSearch);
       }
    };
    goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSearchSpecialPricePaging;
		$(frm.page).val(page);
		searchSpecialPriceProducts(frm);
	}

	reloadList = function() {
		var frm = document.frmSearch;
		searchSpecialPriceProducts(frm);
	}
    deleteSellerProductSpecialPrice = function( splPrice_id ){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){
			return false;
		}
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'deleteSellerProductSpecialPrice'), 'splprice_id=' + splPrice_id, function(t) {
            $('form#frmSplPriceListing table tr#row-'+splPrice_id).remove();
            if (1 > $('form#frmSplPriceListing table tbody tr').length) {
                searchSpecialPriceProducts(document.frmSearch);
            }
		});
	}
    deleteSpecialPriceRows = function(){
        if (typeof $(".selectItem--js:checked").val() === 'undefined') {
	        $.systemMessage(langLbl.atleastOneRecord, 'alert--danger');
	        return false;
	    }
        var agree = confirm(langLbl.confirmDelete);
		if( !agree ){ return false; }
        var data = fcom.frmData(document.getElementById('frmSplPriceListing'));
        fcom.ajax(fcom.makeUrl('SellerProducts', 'deleteSpecialPriceRows'), data, function(t) {
            var ans = $.parseJSON(t);
			if( ans.status == 1 ){
				$.systemMessage(ans.msg, 'alert--success');
                $('.formActionBtn-js').addClass('formActions-css');
			} else {
                $.systemMessage(ans.msg, 'alert--danger');
			}
            searchSpecialPriceProducts(document.frmSearch);
        });
	};
    updateSpecialPriceRow = function(frm, selProd_id){
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'updateSpecialPriceRow'), data, function(t) {
            if(t.status == true){
                if (1 > frm.addMultiple.value || 0 < selProd_id) {
                    if (1 > selProd_id) {
                        frm.elements["splprice_selprod_id"].value = '';
                    }
                    frm.reset();
                }
                document.getElementById('frmSplPriceListing').reset()
                $('table.splPriceList-js tbody').prepend(t.data);
                $('.date_js').datepicker('option', {minDate: new Date()});
                if (0 < $('.noResult--js').length) {
                    $('.noResult--js').remove();
                }
            }
			$(document).trigger('close.facebox');
            if (0 < frm.addMultiple.value) {
                var splPriceRow = $("#"+frm.id).parent().parent();
                splPriceRow.siblings('.divider:first').remove();
                splPriceRow.remove();
            }
		});
		return false;
	};
    updateValues = function(currObj) {
        var value = currObj.val();
        var oldValue = currObj.attr('data-oldval');
        var displayOldValue = currObj.attr('data-displayoldval');
        displayOldValue = typeof displayOldValue == 'undefined' ? oldValue : displayOldValue;
        var attribute = currObj.attr('name');
        var id = currObj.data('id');
        var selProdId = currObj.data('selprodid');
        if ('splprice_price' == attribute) {
            value = parseFloat(value);
            oldValue = parseFloat(oldValue);
        }
        if ('' != value && value != oldValue) {
            var data = 'attribute='+attribute+"&splprice_id="+id+"&selProdId="+selProdId+"&value="+value;
            fcom.ajax(fcom.makeUrl('SellerProducts', 'updateSpecialPriceColValue'), data, function(t) {
                var ans = $.parseJSON(t);
                if( ans.status != 1 ){
                    $.systemMessage(ans.msg, 'alert--danger', true);
                    value = oldValue;
                    updatedValue = displayOldValue;
                } else {
                    updatedValue = ans.data.value;
                    currObj.attr('data-oldval', value);
                }
                currObj.attr('value', value);
                showElement(currObj, updatedValue);
            });
        } else {
            showElement(currObj);
            currObj.val(oldValue);
        }
    };
    showElement = function(currObj, value){
        var sibling = currObj.siblings('div');
        if ('' != value){
            sibling.text(value);
        }
        sibling.fadeIn();
        currObj.addClass('hide');
    };
})();
