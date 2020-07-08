$(document).ready(function() {
    searchCity(document.frmStateSearch);
});

(function() {
    var runningAjaxReq = false;
    var dv = '#listing';

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmStateSearchPaging;
        $(frm.page).val(page);
        searchCity(frm);
    }

    reloadList = function() {
        var frm = document.frmStateSearchPaging;
        searchCity(frm);
    };

    searchCity = function(form) {
        /*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        /*]*/

        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('Cities', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };

    addStateForm = function(id) {
        $.facebox(function() {
            stateForm(id);
        });
    }

    stateForm = function(id) {
        fcom.displayProcessing();

        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('Cities', 'form', [id]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //});
    };

    editStateFormNew = function(stateId) {
        $.facebox(function() {
            editStateForm(stateId);
        });
    };


    editStateForm = function(stateId) {
        fcom.displayProcessing();
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('Cities', 'form', [stateId]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //});
    };

    setupState = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Cities', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editStateLangForm(t.cityId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    }

    editStateLangForm = function(cityId, langId) {
        fcom.displayProcessing();
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('Cities', 'langForm', [cityId, langId]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //});
    };

    setupLangState = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Cities', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editStateLangForm(t.stateId, t.langId);
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
        var cityId = parseInt(obj.value);
        if (cityId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            //$.mbsmessage(langLbl.invalidRequest,true,'alert--danger');
            return false;
        }
        data = 'cityId=' + cityId;
        fcom.ajax(fcom.makeUrl('Cities', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
                $(obj).toggleClass("active");
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

    clearSearch = function() {
        document.frmSearch.reset();
        searchCity(document.frmSearch);
    };

    getStates = function(dv) {
        var countryId = $("#frmSearch select[name='country']").val();
        fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('Users','getStates',[countryId]),'',function(res){
			$(dv).empty();
			$(dv).append(res);
		});
	    $.systemMessage.close();
    }

    getStatesByCid = function(countryId, stateId, dv) {
        fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('Users','getStates',[countryId, stateId]),'',function(res){
			$(dv).empty();
			$(dv).append(res);
		});
	    $.systemMessage.close();
    }

    toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmStatesListing input[name='status']").val(status);
        $("#frmStatesListing").submit();
    };

})();
