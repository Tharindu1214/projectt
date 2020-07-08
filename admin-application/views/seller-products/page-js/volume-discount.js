$(document).ready(function(){
    searchVolumeDiscountProducts(document.frmSearch);
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
        		$("#"+parentForm+" input[name='voldiscount_selprod_id']").val(item['value']);
                currObj.val((item['label']).replace(/<[^>]+>/g, ''));
        	}
        });
    }else{
        $("#"+parentForm+" input[name='voldiscount_selprod_id']").val('');
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


$(document).on('click', 'table.volDiscountList-js tr td .js--editCol', function(){
    $(this).addClass('hide');
    var input = $(this).siblings('input[type="text"]');
    var value = input.val();
    input.removeClass('hide');
    input.val('').focus().val(value);
});

$(document).on('blur', ".js--volDiscountCol", function(){
    var currObj = $(this);
    var value = currObj.val();
    var oldValue = currObj.attr('data-oldval');
    var attribute = currObj.attr('name');
    var id = currObj.data('id');
    var selProdId = currObj.data('selprodid');
    if ('' != value && parseFloat(value) != parseFloat(oldValue)) {
        var data = 'attribute='+attribute+"&voldiscount_id="+id+"&selProdId="+selProdId+"&value="+value;
        fcom.ajax(fcom.makeUrl('SellerProducts', 'updateVolumeDiscountColValue'), data, function(t) {
            var ans = $.parseJSON(t);
            if( ans.status != 1 ){
                $.systemMessage(ans.msg, 'alert--danger');
                value = updatedValue = oldValue;
            } else {
                updatedValue = ans.data.value;
                currObj.attr('data-oldval', value);
            }
            currObj.val(value);
            showElement(currObj, updatedValue);
        });
    } else {
        showElement(currObj);
        currObj.val(oldValue);
    }
    return false;
});
(function() {
	var dv = '#listing';
	searchVolumeDiscountProducts = function(frm){

		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (frm) {
			data = fcom.frmData(frm);
		}
		/*]*/
		var dv = $('#listing');
		$(dv).html( fcom.getLoader() );

		fcom.ajax(fcom.makeUrl('SellerProducts','searchVolumeDiscountProducts'),data,function(res){
			$("#listing").html(res);
		});
	};
    clearSearch = function(selProd_id){
        if (0 < selProd_id) {
            location.href = fcom.makeUrl('SellerProducts','volumeDiscount');
        } else {
    		document.frmSearch.reset();
            document.frmSearch.product_seller_id.value = '';
    		searchVolumeDiscountProducts(document.frmSearch);
        }
	};
    goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSearchVolumeDiscountPaging;
		$(frm.page).val(page);
		searchVolumeDiscountProducts(frm);
	}

	reloadList = function() {
		var frm = document.frmSearch;
		searchVolumeDiscountProducts(frm);
	}
    deleteSellerProductVolumeDiscount = function( voldiscount_id ){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){
			return false;
		}
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'deleteSellerProductVolumeDiscount'), 'voldiscount_id=' + voldiscount_id, function(t) {
            $('form#frmVolDiscountListing table tr#row-'+voldiscount_id).remove();
            if (1 > $('form#frmVolDiscountListing table tbody tr').length) {
                searchVolumeDiscountProducts(document.frmSearch);
            }
		});
	}
    deleteVolumeDiscountRows = function(){
        if (typeof $(".selectItem--js:checked").val() === 'undefined') {
	        $.systemMessage(langLbl.atleastOneRecord, 'alert--danger');
	        return false;
	    }
        var agree = confirm(langLbl.confirmDelete);
		if( !agree ){ return false; }
        var data = fcom.frmData(document.getElementById('frmVolDiscountListing'));
        fcom.ajax(fcom.makeUrl('SellerProducts', 'deleteVolumeDiscountArr'), data, function(t) {
            var ans = $.parseJSON(t);
			if( ans.status == 1 ){
				$.systemMessage(ans.msg, 'alert--success');
                $('.formActionBtn-js').addClass('formActions-css');
			} else {
                $.systemMessage(ans.msg, 'alert--danger');
			}
            searchVolumeDiscountProducts(document.frmSearch);
        });
	};
    updateVolumeDiscountRow = function(frm, selProd_id){
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'updateVolumeDiscountRow'), data, function(t) {
            if(t.status == true){
                if (1 > frm.addMultiple.value || 0 < selProd_id) {
                    if (1 > selProd_id) {
                        frm.elements["voldiscount_selprod_id"].value = '';
                    }
                    frm.reset();
                }
                document.getElementById('frmVolDiscountListing').reset()
                $('table.volDiscountList-js tbody').prepend(t.data);
                if (0 < $('.noResult--js').length) {
                    $('.noResult--js').remove();
                }
            }
			$(document).trigger('close.facebox');
            if (0 < frm.addMultiple.value) {
                var volDisRow = $("#"+frm.id).parent().parent();
                volDisRow.siblings('.divider:first').remove();
                volDisRow.remove();
            }
		});
		return false;
	};

    showElement = function(currObj, value){
        var sibling = currObj.siblings('div');
        if ('' != value){
            sibling.text(value);
        }
        sibling.removeClass('hide');
        currObj.addClass('hide');
    };
})();
