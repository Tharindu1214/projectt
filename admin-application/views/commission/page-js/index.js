$(document).ready(function() {
    searchCommission(document.frmCommissionSearch);
});

(function() {
    var runningAjaxReq = false;
    var dv = '#listing';

    reloadList = function() {
        var frm = document.frmCommissionSearch;
        searchCommission(frm);
    };

    searchCommission = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('Commission', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };


    editCommissionForm = function(commissionId) {
        $.facebox(function() {
            editForm(commissionId);
        });
    };

    editForm = function(commissionId) {
        fcom.ajax(fcom.makeUrl('Commission', 'form', [commissionId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupCommission = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Commission', 'setup'), data, function(t) {
            reloadList();
            $(document).trigger('close.facebox');
        });
    };

    deleteCommission = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('Commission', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

	deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
	        $("#frmCommissionListing").submit();
    };

    viewHistory = function(id) {
        csh_id = id;
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('Commission', 'viewHistory', [csh_id]), '', function(t) {
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
        fcom.ajax(fcom.makeUrl('Commission', 'viewHistory', [csh_id]), data, function(t) {
            $.facebox(t, 'faceboxWidth');
        });
    };

    clearSearch = function() {
        document.frmCommissionSearch.reset();
        searchCommission(document.frmCommissionSearch);
    };
    /*
    settingsForm = function (code){
    	$.facebox(function() {
    		fcom.ajax(fcom.makeUrl(code+'-settings'), '', function(t) {
    			$.facebox(t,'faceboxWidth');
    		});
    	});
    };
    /*
	settingsForm = function (code){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl(code+'-settings'), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};

	setupPaymentSettings = function (frm,code){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl(code+'-settings', 'setup'), data, function(t) {
			settingsForm(code);
		});
	}; */
    setupPaymentSettings = function (frm,code){
    	if (!$(frm).validate()) return;
    	var data = fcom.frmData(frm);
    	fcom.updateWithAjax(fcom.makeUrl(code+'-settings', 'setup'), data, function(t) {
    		settingsForm(code);
    	});
    };

})();
