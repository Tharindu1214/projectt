$(document).ready(function() {
    searchPages(document.frmPagesSearch);
});

(function() {

    var currentPage = 1;
    var runningAjaxReq = false;

    pagesLayouts = function() {
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('ContentPages', 'layouts'), '', function(t) {
            $.facebox(t, 'faceboxWidth');
        });
        //});
    };

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmPagesSearchPaging;
        $(frm.page).val(page);
        searchPages(frm);
    }

    reloadList = function() {
        var frm = document.frmPagesSearchPaging;
        searchPages(frm);
    }

    searchPages = function(form) {
        var dv = '#pageListing';
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html('Loading....');
        fcom.ajax(fcom.makeUrl('ContentPages', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };

    addFormNew = function(id) {

        $.facebox(function() {
            addForm(id)
        });

    }
    addForm = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('ContentPages', 'form', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
            showLayout($("#cpage_layout"));
        });
    };

    setup = function(frm) {
        fcom.resetEditorInstance();
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ContentPages', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                addLangForm(t.pageId, t.langId, t.cpage_layout);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    addLangForm = function(pageId, langId, cpage_layout) {
        fcom.displayProcessing();
        fcom.resetEditorInstance();
        //    $.facebox(function() {
        fcom.ajax(fcom.makeUrl('ContentPages', 'langForm', [pageId, langId, cpage_layout]), '', function(t) {
            //    $.facebox(t);
            fcom.updateFaceboxContent(t);
            fcom.setEditorLayout(langId);
            var frm = $('#facebox form')[0];

            var validator = $(frm).validation({
                errordisplay: 3
            });

            $(frm).submit(function(e) {
                e.preventDefault();
                validator.validate();
                if (!validator.isValid()) return;
                /* if (validator.validate() == false) {
                    return ;
                } */
                var data = fcom.frmData(frm);
                fcom.updateWithAjax(fcom.makeUrl('ContentPages', 'langSetup'), data, function(t) {
                    fcom.resetEditorInstance();
                    reloadList();
                    if (t.langId > 0) {
                        addLangForm(t.pageId, t.langId, t.cpage_layout);
                        return;
                    }
                    $(document).trigger('close.facebox');
                });
            });
        });
        //});
    };

    setupLang = function(frm) {


        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ContentPages', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                addLangForm(t.pageId, t.langId, t.cpage_layout);
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
        fcom.updateWithAjax(fcom.makeUrl('ContentPages', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

    removeBgImage = function(cpageId, langId, cpageLayout) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('ContentPages', 'removeBgImage', [cpageId, langId]), '', function(t) {
            addLangForm(cpageId, langId, cpageLayout);
        });
    };

    clearSearch = function() {
        document.frmPagesSearch.reset();
        searchPages(document.frmPagesSearch);
    };

    showLayout = function(element) {
        if (element.val() != '') {
            $('#viewLayout-js').html('Loading...');
            fcom.ajax(fcom.makeUrl('ContentPages', 'cmsLayout', [element.val()]), '', function(t) {
                $('#viewLayout-js').html(t);
                setTimeout(function() {
                    fcom.resetFaceboxHeight();
                }, 100);
            });
        } else {
            $('#viewLayout-js').html('');
        }
    };

    deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmContentPgListing").attr("action",fcom.makeUrl('ContentPages','deleteSelected')).submit();
    };

})();

(function() {
    displayImageInFacebox = function(str) {
        $.facebox('<img width="800px;" src="' + str + '">');
    }
})();


$(document).on('click', '.bgImageFile-Js', function() {
    var node = this;
    $('#form-upload').remove();
    var formName = $(node).attr('data-frm');

    var lang_id = document.frmBlockLang.lang_id.value;
    var cpage_id = document.frmBlockLang.cpage_id.value;
    var cpage_layout = document.frmBlockLang.cpage_layout.value;

    var fileType = $(node).attr('data-file_type');

    var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
    frm = frm.concat('<input type="file" name="file" />');
    frm = frm.concat('<input type="hidden" name="file_type" value="' + fileType + '">');
    frm = frm.concat('<input type="hidden" name="cpage_id" value="' + cpage_id + '">');
    frm = frm.concat('<input type="hidden" name="lang_id" value="' + lang_id + '">');
    frm = frm.concat('<input type="hidden" name="cpage_layout" value="' + cpage_layout + '">');
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
                url: fcom.makeUrl('ContentPages', 'setUpBgImage'),
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
                    fcom.displaySuccessMessage(ans.msg);
                    /* addLangForm(ans.cpage_id, ans.lang_id, ans.cpage_layout); */
                    /* addForm(cpage_id); */
                    /* '<img src=""> <a href="javascript:void(0);" onclick="removeBgImage(1,1,1)" class="remove--img"><i class="ion-close-round"></i></a>';
                    fcom.makeUrl('Questionnaires', 'generateLink', [questionnaireId]);
                    generateUrl('cart', 'cart_summary');
                    */
                    $(".temp-hide").show();
                    var dt = new Date();
                    var time = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
                    $(".uploaded--image").html('<img src="' + fcom.makeUrl('image', 'cpageBackgroundImage', [ans.cpage_id, ans.lang_id, 'THUMB'], SITE_ROOT_URL) + '?' + time + '"> <a href="javascript:void(0);" onclick="removeBgImage(' + [ans.cpage_id, ans.lang_id, ans.cpage_layout] + ')" class="remove--img"><i class="ion-close-round"></i></a>');
                    fcom.displaySuccessMessage(ans.msg);
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }, 500);
});
