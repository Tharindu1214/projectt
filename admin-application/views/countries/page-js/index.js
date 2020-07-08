$(document).ready(function() {
    searchCountry(document.frmCountrySearch);
});

(function() {
    var runningAjaxReq = false;
    var dv = '#listing';

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmCountrySearchPaging;
        $(frm.page).val(page);
        searchCountry(frm);
    }

    reloadList = function() {
        var frm = document.frmCountrySearchPaging;
        searchCountry(frm);
    };

    searchCountry = function(form) {
        /*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        /*]*/
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('Countries', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };
    
    addCountryForm = function(id) {
        $.facebox(function() {
            countryForm(id);
        });

    };

    countryForm = function(id) {
        fcom.displayProcessing();
        ///$.facebox(function() {
        fcom.ajax(fcom.makeUrl('Countries', 'form', [id]), '', function(t) {
            $.facebox(t, 'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //});
    };

    editCountryFormNew = function(countryId) {
        $.facebox(function() {
            editCountryForm(countryId);
        });
    };

    editCountryForm = function(countryId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Countries', 'form', [countryId]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
    };

    setupCountry = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Countries', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editCountryLangForm(t.countryId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    editCountryLangForm = function(countryId, langId) {
        fcom.displayProcessing();
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('Countries', 'langForm', [countryId, langId]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //});
    };

    setupLangCountry = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Countries', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editCountryLangForm(t.countryId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
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
        var countryId = parseInt(obj.value);
        if (countryId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'countryId=' + countryId;
        fcom.ajax(fcom.makeUrl('Countries', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);

            if (ans.status == 1) {

                $.fcom.displaySuccessMessage(ans.msg);
                $(obj).toggleClass("active");
            }
        });
    };

    toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmCountryListing input[name='status']").val(status);
        $("#frmCountryListing").submit();
    };

    clearSearch = function() {
        document.frmSearch.reset();
        searchCountry(document.frmSearch);
    };
})();
