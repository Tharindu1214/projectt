$(document).ready(function() {
    searchBlogPosts(document.frmSearch);
});
$(document).on('change', '.language-js', function() {
    /* $(document).delegate('.language-js','change',function(){ */
    var lang_id = $(this).val();
    var post_id = $("input[name='post_id']").val();
    images(post_id, lang_id);
});
(function() {
    var currentPage = 1;
    var runningAjaxReq = false;

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmSearchPaging;
        $(frm.page).val(page);
        searchBlogPosts(frm);
    }

    reloadList = function() {
        var frm = document.frmSearchPaging;
        searchBlogPosts(frm);
    }
    addBlogPostForm = function(id) {
        $.facebox(function() {
            blogPostForm(id);
        });
    };
blogPostForm = function(id) {
        fcom.displayProcessing();
        fcom.resetEditorInstance();
        var frm = document.frmSearchPaging;

        if (typeof parent == undefined || parent == null) {
            parent = 0;
        }
        fcom.ajax(fcom.makeUrl('BlogPosts', 'form', [id, parent]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setup = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('BlogPosts', 'setup'), data, function(t) {
            reloadList();
            if (t.openLinksForm) {
                linksForm(t.postId);
                return;
            }
            if (t.langId > 0) {
                langForm(t.postId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    setupPostCategories = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('BlogPosts', 'setupCategories'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                langForm(t.postId, t.langId);
                return;
            }

            $(document).trigger('close.facebox');
        });
    };

    langForm = function(postId, langId) {
        fcom.displayProcessing();
        fcom.resetEditorInstance();
        fcom.ajax(fcom.makeUrl('BlogPosts', 'langForm', [postId, langId]), '', function(t) {
            fcom.updateFaceboxContent(t);
            fcom.setEditorLayout(langId);
            var frm = $('#facebox form')[0];
            var validator = $(frm).validation({
                errordisplay: 3
            });
            $(frm).submit(function(e) {
                e.preventDefault();
                if (validator.validate() == false) {
                    return;
                }
                var data = fcom.frmData(frm);
                fcom.updateWithAjax(fcom.makeUrl('BlogPosts', 'langSetup'), data, function(t) {
                    fcom.resetEditorInstance();
                    reloadList();
                    if (t.langId > 0) {
                        langForm(t.postId, t.langId);
                        return;
                    }
                    if (t.openImagesTab) {
                        postImages(t.postId);
                        return;
                    }

                    $(document).trigger('close.facebox');
                });

            });
        });
    };

    searchBlogPosts = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $("#listing").html('Loading....');
        fcom.ajax(fcom.makeUrl('BlogPosts', 'search'), data, function(res) {
            $("#listing").html(res);
        });
    };

    linksForm = function(id) {
        fcom.displayProcessing();
        fcom.resetEditorInstance();
        fcom.ajax(fcom.makeUrl('BlogPosts', 'linksForm', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    }

    deleteRecord = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('BlogPosts', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

    clearSearch = function() {
        document.frmSearch.reset();
        searchBlogPosts(document.frmSearch);
    };

    postImages = function(post_id) {
        fcom.resetEditorInstance();
        fcom.ajax(fcom.makeUrl('BlogPosts', 'imagesForm', [post_id]), '', function(t) {
            images(post_id);
            $.facebox(t, 'faceboxWidth');
        });
    };

    images = function(post_id, lang_id) {
        fcom.ajax(fcom.makeUrl('BlogPosts', 'images', [post_id, lang_id]), '', function(t) {
            $('#image-listing').html(t);
            fcom.resetFaceboxHeight();
        });
    };

    deleteImage = function(post_id, afile_id, lang_id) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }
        fcom.ajax(fcom.makeUrl('BlogPosts', 'deleteImage', [post_id, afile_id, lang_id]), '', function(t) {
            var ans = $.parseJSON(t);
            if (ans.status == 0) {
                fcom.displayErrorMessage(ans.msg);
                return;
            } else {
                fcom.displaySuccessMessage(ans.msg);
            }
            images(post_id, lang_id);
        });
    }

	deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmBlogPostListing").attr("action",fcom.makeUrl('BlogPosts','deleteSelected')).submit();
    };

})();

$(document).on('click', '.blogFile-Js', function() {
    var node = this;
    $('#form-upload').remove();
    var frmName = $(node).attr('data-frm');
    if ('frmBlogPostImage' == frmName) {
        var langId = document.frmBlogPostImage.lang_id.value;
        var postId = document.frmBlogPostImage.post_id.value;
    }
    var fileType = $(node).attr('data-file_type');

    var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
    frm = frm.concat('<input type="file" name="file" />');
    frm = frm.concat('<input type="hidden" name="post_id" value="' + postId + '"/>');
    frm = frm.concat('<input type="hidden" name="file_type" value="' + fileType + '"></form>');
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
                url: fcom.makeUrl('BlogPosts', 'uploadBlogPostImages', [postId, langId]),
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
                success: function(t) {
                    if (t.status == 1) {
                        fcom.displaySuccessMessage(t.msg);
                    } else {
                        fcom.displayErrorMessage(t.msg);
                    }
                    $('#form-upload').remove();
                    images(postId, langId);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert("Error Occured.");
                }
            });
        }
    }, 500);
});
