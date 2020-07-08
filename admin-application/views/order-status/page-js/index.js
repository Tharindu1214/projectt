$(document).ready(function() {
    searchOrderStatus(document.frmOrderStatusSearch);
});

(function() {
    var runningAjaxReq = false;
    var dv = '#listing';

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmOrderStatusSearchPaging;
        $(frm.page).val(page);
        searchOrderStatus(frm);
    }

    reloadList = function() {
        var frm = document.frmOrderStatusSearchPaging;
        searchOrderStatus(frm);
    };

    searchOrderStatus = function(form) {
        /*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        /*]*/
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('OrderStatus', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };

    orderStatusForm = function(id) {

        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('OrderStatus', 'form', [id]), '', function(t) {
                $.facebox(t, 'faceboxWidth');
            });
        });
    };

    editOrderStatusForm = function(orderStatusId) {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('OrderStatus', 'form', [orderStatusId]), '', function(t) {
                $.facebox(t, 'faceboxWidth');
            });
        });
    };

    setupOrderStatus = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('OrderStatus', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editOrderStatusLangForm(t.orderStatusId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    }

    editOrderStatusLangForm = function(orderStatusId, langId) {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('OrderStatus', 'langForm', [orderStatusId, langId]), '', function(t) {
                $.facebox(t, 'faceboxWidth');
            });
        });
    };

    setupLangOrderStatus = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('OrderStatus', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editOrderStatusLangForm(t.orderStatusId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    toggleStatus = function(obj) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return;
        }
        var orderStatusId = parseInt(obj.id);
        if (orderStatusId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'orderStatusId=' + orderStatusId;
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('OrderStatus', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                $(obj).toggleClass("active");
                fcom.displaySuccessMessage(ans.msg);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmOrderStatusListing input[name='status']").val(status);
        $("#frmOrderStatusListing").submit();
    };

    clearSearch = function() {
        document.frmSearch.reset();
        searchOrderStatus(document.frmSearch);
    };
})();
