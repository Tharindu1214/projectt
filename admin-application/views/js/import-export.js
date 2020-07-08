(function() {
    addExportForm = function(actionType) {
        $.facebox(function() {
            //getExportForm(actionType);
            exportForm(actionType);

        });
    };
    exportForm = function(actionType) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('ImportExport', 'exportForm', [actionType]), '', function(t) {
            fcom.updateFaceboxContent(t, 'faceboxWidth');
        });
    }
    exportData = function(frm, actionType) {
        if (!$(frm).validate()) return;
        document.frmImportExport.action = fcom.makeUrl('ImportExport', 'exportData', [actionType]);
        document.frmImportExport.submit();
    };

    exportMediaForm = function(actionType) {
        //	$.facebox(function() {
        fcom.ajax(fcom.makeUrl('ImportExport', 'exportMediaForm', [actionType]), '', function(t) {
            fcom.updateFaceboxContent(t, 'faceboxWidth');
        });
        //});
    };

    exportMedia = function(frm, actionType) {
        if (!$(frm).validate()) return;
        document.frmImportExport.action = fcom.makeUrl('ImportExport', 'exportMedia', [actionType]);
        document.frmImportExport.submit();
    };

    addImportForm = function(actionType) {
        $.facebox(function() {
            // importForm(actionType);
            getInstructions(actionType);
        });
    };
    importForm = function(actionType) {
        fcom.ajax(fcom.makeUrl('ImportExport', 'importForm', [actionType]), '', function(t) {
            fcom.updateFaceboxContent(t, 'faceboxWidth');
        });

    }
    getInstructions = function(actionType) {
        fcom.ajax(fcom.makeUrl('ImportExport', 'importInstructions', [actionType]), '', function(t) {
            fcom.updateFaceboxContent(t, 'faceboxWidth');
        });

    }
    importMediaForm = function(actionType) {
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('ImportExport', 'importMediaForm', [actionType]), '', function(t) {
            fcom.updateFaceboxContent(t, 'faceboxWidth');
        });
        //	});
    };

    importFile = function(method, actionType) {
        var data = new FormData();
        $inputs = $('#frmImportExport input[type=text],#frmImportExport select,#frmImportExport input[type=hidden]');
        $inputs.each(function() {
            data.append(this.name, $(this).val());
        });
        $.each($('#import_file')[0].files, function(i, file) {
            fcom.displayProcessing(langLbl.processing, ' ', true);
            $('#fileupload_div').html(fcom.getLoader());
            data.append('import_file', file);
            $.ajax({
                url: fcom.makeUrl('ImportExport', method, [actionType]),
                type: "POST",
                data: data,
                processData: false,
                contentType: false,
                success: function(t) {
                    try {
                        var ans = $.parseJSON(t);                        
                        if (ans.status == 1) {
                            reloadList();
                            $(document).trigger('close.facebox');
                            $(document).trigger('close.mbsmessage');
                            fcom.displaySuccessMessage(ans.msg);
                        } else {
                            $('#fileupload_div').html('');
                            $(document).trigger('close.mbsmessage');
                            fcom.displayErrorMessage(ans.msg);
                        }

                        if (typeof ans.CSVfileUrl !== 'undefined') {
                            location.href = ans.CSVfileUrl;
                        } else {
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }
                    } catch (exc) {
                        $(document).trigger('close.mbsmessage');
                        fcom.displayErrorMessage(t);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert("Error Occured.");
                }
            });
        });
    };

    showHideExtraFld = function(type, BY_ID_RANGE, BY_BATCHES) {
        if (type == BY_ID_RANGE) {
            $(".range_fld").show();
            $(".batch_fld").hide();
        } else if (type == BY_BATCHES) {
            $(".range_fld").hide();
            $(".batch_fld").show();
        } else {
            $(".range_fld").hide();
            $(".batch_fld").hide();
        }
    };

})();
