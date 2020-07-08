$(document).ready(function() {
    searchTestimonial(document.frmTestimonialSearch);
});

(function() {
    var runningAjaxReq = false;
    var dv = '#listing';

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmTestimonialSearchPaging;
        $(frm.page).val(page);
        searchTestimonial(frm);
    }

    reloadList = function() {
        searchTestimonial();
    };

    searchTestimonial = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('Testimonials', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };
    addTestimonialForm = function(id) {

        $.facebox(function() {
            testimonialForm(id);
        });
    };

    testimonialForm = function(id) {
        fcom.displayProcessing();
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('Testimonials', 'form', [id]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //});
    };
    editTestimonialFormNew = function(testimonialId) {
        $.facebox(function() {
            editTestimonialForm(testimonialId);
        });
    };

    editTestimonialForm = function(testimonialId) {
        fcom.displayProcessing();
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('Testimonials', 'form', [testimonialId]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //});
    };

    setupTestimonial = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Testimonials', 'setup'), data, function(t) {
            //$.mbsmessage.close();
            reloadList();
            if (t.langId > 0) {
                editTestimonialLangForm(t.testimonialId, t.langId);
                return;
            }
            if (t.openMediaForm) {
                testimonialMediaForm(t.testimonialId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    }

    editTestimonialLangForm = function(testimonialId, langId) {
        fcom.displayProcessing();
        //	$.facebox(function() {
        fcom.ajax(fcom.makeUrl('Testimonials', 'langForm', [testimonialId, langId]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //});
    };

    setupLangTestimonial = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Testimonials', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editTestimonialLangForm(t.testimonialId, t.langId);
                return;
            }
            if (t.openMediaForm) {
                testimonialMediaForm(t.testimonialId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    deleteRecord = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'testimonialId=' + id;
        fcom.updateWithAjax(fcom.makeUrl('Testimonials', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

	deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmTestimonialListing").attr("action",fcom.makeUrl('Testimonials','deleteSelected')).submit();
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
        var testimonialId = parseInt(obj.value);
        if (testimonialId < 1) {

            //$.mbsmessage(langLbl.invalidRequest,true,'alert--danger');
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'testimonialId=' + testimonialId;
        fcom.ajax(fcom.makeUrl('Testimonials', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                $(obj).toggleClass("active");
                fcom.displaySuccessMessage(ans.msg);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmTestimonialListing input[name='status']").val(status);
        $("#frmTestimonialListing").submit();
    };

    clearSearch = function() {
        document.frmSearch.reset();
        searchTestimonial(document.frmSearch);
    };


    testimonialMediaForm = function(testimonialId) {
        //$.facebox(function() {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Testimonials', 'media', [testimonialId]), '', function(t) {
            //$.facebox(t);
            fcom.updateFaceboxContent(t);
        });
        //});
    };

    removeTestimonialImage = function(testimonialId, langId) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Testimonials', 'removeTestimonialImage', [testimonialId, langId]), '', function(t) {
            testimonialMediaForm(testimonialId);
        });
    }
})();


$(document).on('click', '.uploadFile-Js', function() {
    var node = this;
    $('#form-upload').remove();
    /* var brandId = document.frmProdBrandLang.brand_id.value;
    var langId = document.frmProdBrandLang.lang_id.value; */

    var testimonialId = $(node).attr('data-testimonial_id');
    var langId = 0;

    var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
    frm = frm.concat('<input type="file" name="file" />');
    frm = frm.concat('<input type="hidden" name="testimonialId" value="' + testimonialId + '"/>');
    frm = frm.concat('<input type="hidden" name="lang_id" value="' + langId + '"/>');
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
                url: fcom.makeUrl('Testimonials', 'uploadTestimonialMedia'),
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
                    $('.text-danger').remove();
                    $('#input-field').html(ans.msg);
                    if (!ans.status) {
                        fcom.displayErrorMessage(ans.msg);
                        return;
                    }
                    fcom.displaySuccessMessage(ans.msg);
                    testimonialMediaForm(ans.testimonialId);
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }, 500);
});
