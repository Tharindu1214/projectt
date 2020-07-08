$(document).ready(function() {
    getForm(1);

    $(document).on("click", "#testMail-js", function() {
        fcom.ajax(fcom.makeUrl('Configurations', 'testEmail'), '', function(t) {
            var ans = $.parseJSON(t);
            if (ans.status == 1) {
                $.systemMessage(ans.msg, 'alert--success');
            } else {
                $.systemMessage(ans.msg, 'alert--danger');
            }
        });
    });

    $(document).on("change", "select[name='CONF_TIMEZONE']", function() {
        var timezone = $("select[name='CONF_TIMEZONE']").val();
        fcom.ajax(fcom.makeUrl('Configurations', 'displayDateTime'), 'time_zone=' + timezone , function(t) {
            var ans = $.parseJSON(t);
            $('#currentDate').html(ans.dateTime);
        });
    });

});

(function() {
    var currentPage = 1;
    var runningAjaxReq = false;
    var dv = '#frmBlock';
    getForm = function(frmType) {
        fcom.resetEditorInstance();
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Configurations', 'form', [frmType]), '', function(t) {
            $(dv).html(t);
        });
    };

    getLangForm = function(frmType, langId) {
        fcom.resetEditorInstance();
        $(dv).html(fcom.getLoader());
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Configurations', 'langForm', [frmType, langId]), '', function(t) {
            $(dv).html(t);
            fcom.setEditorLayout(langId);
            if (frmType == FORM_MEDIA) {
                $('input[name=btn_submit]').hide();
            }
            var frm = $(dv + ' form')[0];
            var validator = $(frm).validation({
                errordisplay: 3
            });
            $(frm).submit(function(e) {
                e.preventDefault();
                if (validator.validate() == false) {
                    return;
                }
                var data = fcom.frmData(frm);
                fcom.updateWithAjax(fcom.makeUrl('Configurations', 'setupLang'), data, function(t) {
                    runningAjaxReq = false;
                    fcom.resetEditorInstance();
                    if (t.langId > 0 && t.shopId > 0) {
                        shopLangForm(t.shopId, t.langId);
                        return;
                    }
                });
            });

        });
        $.systemMessage.close();
    }

    setup = function(frm) {
        if (!$(frm).validate()) {
            return;
        }
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'setup'), data, function(t) {
            if (t.langId > 0 && t.frmType > 0) {
                getLangForm(t.frmType, t.langId);
                return;
            }
            if (t.frmType > 0) {
                getForm(t.frmType);
                return;
            }
            $(document).trigger('close.facebox');
        });
    }

    setupLang = function(frm) {
        if (!$(frm).validate()) {
            return;
        }
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'setupLang'), data, function(t) {
            if (t.langId > 0 && t.frmType > 0) {
                getLangForm(t.frmType, t.langId);
                return;
            }
            if (t.frmType > 0) {
                getForm(t.frmType);
                return;
            }
            $(document).trigger('close.facebox');
        });
    }

    removeSiteAdminLogo = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeSiteAdminLogo', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeDesktopLogo = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeDesktopLogo', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeEmailLogo = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeEmailLogo', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeFavicon = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeFavicon', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeSocialFeedImage = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeSocialFeedImage', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removePaymentPageLogo = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removePaymentPageLogo', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeWatermarkImage = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeWatermarkImage', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeAppleTouchIcon = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeAppleTouchIcon', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeMobileLogo = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeMobileLogo', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeInvoiceLogo = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeInvoiceLogo', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeCollectionBgImage = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeCollectionBgImage', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeBrandCollectionBgImage = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeBrandCollectionBgImage', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    changedMessageAutoCloseSetting = function(val) {
        if (val == YES) {

        }
        if (val == NO) {
            $("input[name='CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES']").val(0);
        }
    };

    generalInstructions = function(frmType) {
        fcom.resetEditorInstance();
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Configurations', 'generalInstructions', [frmType]), '', function(t) {
            $(dv).html(t);
        });
    };

})();


form = function(form_type) {
    if (typeof form_type == undefined || form_type == null) {
        form_type = 1;
    }
    jQuery.ajax({
        type: "POST",
        data: {
            form: form_type,
            fIsAjax: 1
        },
        url: fcom.makeUrl("configurations", "form"),
        success: function(json) {
            json = $.parseJSON(json);
            if ("1" == json.status) {
                $("#tabs_0" + form_type).html(json.msg);
            } else {
                jsonErrorMessage(json.msg)
            }
        }
    });
}

submitForm = function(form, v) {
    $(form).ajaxSubmit({
        delegation: true,
        beforeSubmit: function() {
            v.validate();
            if (!v.isValid()) {
                return false;
            }
        },
        success: function(json) {
            json = $.parseJSON(json);

            if (json.status == "1") {
                jsonSuccessMessage(json.msg)

            } else {
                jsonErrorMessage(json.msg);
            }
        }
    });
    return false;
}

$(document).on('click', '.logoFiles-Js', function() {
    var node = this;
    $('#form-upload').remove();
    var fileType = $(node).attr('data-file_type');
    var lang_id = document.frmConfiguration.lang_id.value;
    var form_type = document.frmConfiguration.form_type.value;
    var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
    frm = frm.concat('<input type="file" name="file" />');
    frm = frm.concat('<input type="hidden" name="file_type" value="' + fileType + '">');
    frm = frm.concat('<input type="hidden" name="lang_id" value="' + lang_id + '">');
    frm = frm.concat('</form>');
    $('body').prepend(frm);
    $('#form-upload input[name=\'file\']').trigger('click');
    if (typeof timer != 'undefined') {
        clearInterval(timer);
    }
    timer = setInterval(function() {
        if ($('#form-upload input[name=\'file\']').val() != '') {
            clearInterval(timer);
            $val = $(node).val();
            $.ajax({
                url: fcom.makeUrl('Configurations', 'uploadMedia'),
                type: 'post',
                dataType: 'json',
                data: new FormData($('#form-upload')[0]),
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(node).val('Loading');
                },
                complete: function() {
                    $(node).val($val);
                },
                success: function(ans) {
                    if (!ans.status) {
                        $.systemMessage(ans.msg, 'alert--danger');
                        return;
                    }
                    $.systemMessage(ans.msg, 'alert--success');
                    getLangForm(form_type, lang_id);
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    if (xhr.responseText) {
                        $.systemMessage(xhr.responseText, 'alert--danger');
                        return;
                    }
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }, 500);
});
