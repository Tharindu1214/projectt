$(document).ready(function() {
    searchEmptyCartItems(document.frmEmptyCartItemSearch);
});

(function() {
    var currentPage = 1;
    var runningAjaxReq = false;

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmEmptyCartItemSearchPaging;
        $(frm.page).val(page);
        searchEmptyCartItems(frm);
    }

    reloadList = function() {
        var frm = document.frmEmptyCartItemSearchPaging;
        searchEmptyCartItems(frm);
    }

    searchEmptyCartItems = function(form) {
        /*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        /*]*/
        var dv = '#listing';
        $(dv).html('Loading....');

        fcom.ajax(fcom.makeUrl('EmptyCartItems', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };
    addEmptyCartItemForm = function(id) {
        $.facebox(function() {
            emptyCartItemForm(id);

        });
    };

    emptyCartItemForm = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('EmptyCartItems', 'form', [id]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t)
        });
    };

    setup = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('EmptyCartItems', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                emptyCartItemLangForm(t.emptycartitemId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    emptyCartItemLangForm = function(emptycartitemId, langId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('EmptyCartItems', 'langForm', [emptycartitemId, langId]), '', function(t) {
            //$.facebox(t);
            fcom.updateFaceboxContent(t);
        });
    };

    setupLang = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('EmptyCartItems', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                emptyCartItemLangForm(t.emptycartitemId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    deleteRecord = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('EmptyCartItems', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

    clearSearch = function() {
        document.frmEmptyCartItemSearch.reset();
        searchEmptyCartItems(document.frmEmptyCartItemSearch);
    };

    toggleStatus = function(e, obj, canEdit) {
        if (canEdit == 0) {
            e.preventDefault();
            return;
        }
        if (!confirm(langLbl.confirmUpdateStatus)) {
            e.preventDefault();
            return;
        }
        var emptycartitemId = parseInt(obj.value);
        if (emptycartitemId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            //$.mbsmessage(langLbl.invalidRequest,true,'alert--danger');
            return false;
        }
        data = 'emptycartitemId=' + emptycartitemId;
        fcom.ajax(fcom.makeUrl('EmptyCartItems', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
                //$.mbsmessage(ans.msg,true,'alert--success');
                $(obj).toggleClass("active");
            } else {
                fcom.displayErrorMessage(ans.msg);

                //$.mbsmessage(ans.msg,true,'alert--danger');
            }
        });
    };

	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmEmptyCartItemListing input[name='status']").val(status);
        $("#frmEmptyCartItemListing").submit();
    };

    deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmEmptyCartItemListing").attr("action",fcom.makeUrl('EmptyCartItems','deleteSelected')).submit();
    };

})();
