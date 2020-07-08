$(document).ready(function() {
    searchShippingDurations(document.frmshipDurationSearch);
});
(function() {
    var currentPage = 1;
    var runningAjaxReq = false;
    var dv = '#listing';

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmShipDurationSrchPaging;
        $(frm.page).val(page);
        searchShippingDurations(frm);
    };

    reloadList = function() {
        var frm = document.frmShipDurationSrchPaging;
        searchShippingDurations(frm);
    };

    searchShippingDurations = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('ShippingDurations', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };

    shippingDurationForm = function(sdurationId) {
        $.facebox(function() {
            addShippingDuration(sdurationId);
        });
    };

    addShippingDuration = function(sdurationId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('ShippingDurations', 'form', [sdurationId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupShippingDuration = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ShippingDurations', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                shippingDurationLangForm(t.sdurationId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    shippingDurationLangForm = function(sdurationId, langId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('ShippingDurations', 'langForm', [sdurationId, langId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupShippingDurationLang = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ShippingDurations', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                shippingDurationLangForm(t.sdurationId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    clearSearch = function() {
        document.frmshipDurationSearch.reset();
        searchShippingDurations(document.frmshipDurationSearch);
    };

    deleteRecord = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('ShippingDurations', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

	deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmShpDurationListing").attr("action",fcom.makeUrl('ShippingDurations','deleteSelected')).submit();
    };
})()
