$(document).ready(function() {
    searchTax(document.frmTaxSearch);
});

(function() {
    var currentPage = 1;
    var runningAjaxReq = false;
    var dv = '#taxListing';

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmTaxSearchPaging;
        $(frm.page).val(page);
        searchTax(frm);
    };

    reloadList = function() {
        var frm = document.frmTaxSearchPaging;
        searchTax(frm);
    };

    searchTax = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Tax', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };

    addTaxForm = function(id) {
        $.facebox(function() {
            taxForm(id);
        });
    };

    taxForm = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Tax', 'form', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupTax = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Tax', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                addTaxLangForm(t.taxcatId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    addTaxLangForm = function(taxcatId, langId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Tax', 'langForm', [taxcatId, langId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupTaxLang = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Tax', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                addTaxLangForm(t.taxcatId, t.langId);
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
        fcom.updateWithAjax(fcom.makeUrl('Tax', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

    clearSearch = function() {
        document.frmTaxSearch.reset();
        searchTax(document.frmTaxSearch);
    };

    toggleStatus = function(obj) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return;
        }
        var taxcatId = parseInt(obj.id);
        if (taxcatId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'taxcatId=' + taxcatId;
        fcom.ajax(fcom.makeUrl('Tax', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
                $(obj).toggleClass("active");
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmTaxListing input[name='status']").val(status);
        $("#frmTaxListing").submit();
    };

	deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmTaxListing").attr("action",fcom.makeUrl('Tax','deleteSelected')).submit();
    };

})();
