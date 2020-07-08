$(document).ready(function() {
    searchAffiliateCommission(document.frmAffiliateCommissionSearch);
});

(function() {
    var runningAjaxReq = false;
    var dv = '#listing';

    reloadList = function() {
        var frm = document.frmAffiliateCommissionSearch;
        searchAffiliateCommission(frm);
    };

    searchAffiliateCommission = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('AffiliateCommission', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };

    affiliateCommissionForm = function(afcommsetting_id) {
        $.facebox(function() {
            addCommissionForm(afcommsetting_id);
        });
    };

    addCommissionForm = function(afcommsetting_id) {
        fcom.ajax(fcom.makeUrl('AffiliateCommission', 'form', [afcommsetting_id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmAffiliateCommissionSearchPaging;
        $(frm.page).val(page);
        searchAffiliateCommission(frm);
    }

    setupAffiliateCommission = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('AffiliateCommission', 'setup'), data, function(t) {
            $.systemMessage.close();
            reloadList();
            $(document).trigger('close.facebox');
        });
    };

    deleteAffiliateCommission = function(afcommsetting_id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        var data = 'afcommsetting_id=' + afcommsetting_id;
        fcom.updateWithAjax(fcom.makeUrl('AffiliateCommission', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

	deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmAffCommListing").submit();
    };

    viewHistory = function(id) {
        csh_id = id;
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('AffiliateCommission', 'viewHistory', [csh_id]), '', function(t) {
                $.facebox(t, 'faceboxWidth');
            });
        });
    };

    goToHistoryPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmHistorySearchPaging;
        $(frm.page).val(page);
        data = fcom.frmData(frm);
        fcom.ajax(fcom.makeUrl('AffiliateCommission', 'viewHistory', [csh_id]), data, function(t) {
            $.facebox(t, 'faceboxWidth');
        });
    };

    clearSearch = function() {
        document.frmAffiliateCommissionSearch.reset();
        searchAffiliateCommission(document.frmAffiliateCommissionSearch);
    };

})();
