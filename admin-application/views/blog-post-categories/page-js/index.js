$(document).ready(function() {
    searchBlogPostCategories(document.frmSearch);
});
(function() {
    var currentPage = 1;
    var runningAjaxReq = false;

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmCatSearchPaging;
        $(frm.page).val(page);
        searchBlogPostCategories(frm);
    }

    reloadList = function() {
        var frm = document.frmCatSearchPaging;
        searchBlogPostCategories(frm);
    }
    addCategoryForm = function(id) {
        $.facebox(function() {
            categoryForm(id);
        });
    };


    categoryForm = function(id) {
        fcom.displayProcessing();
        var frm = document.frmCatSearchPaging;
        var parent = $(frm.bpcategory_parent).val();
        if (typeof parent == undefined || parent == null) {
            parent = 0;
        }
        fcom.ajax(fcom.makeUrl('BlogPostCategories', 'form', [id, parent]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupCategory = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('BlogPostCategories', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                categoryLangForm(t.catId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    categoryLangForm = function(catId, langId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('BlogPostCategories', 'langForm', [catId, langId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupCategoryLang = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('BlogPostCategories', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                categoryLangForm(t.catId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    searchBlogPostCategories = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $("#listing").html('Loading....');
        fcom.ajax(fcom.makeUrl('BlogPostCategories', 'search'), data, function(res) {
            $("#listing").html(res);
        });
    };

    subcat_list = function(parent) {
        var frm = document.frmCatSearchPaging;
        $(frm.bpcategory_parent).val(parent);
        reloadList();
    };

    categoryMediaForm = function(prodCatId) {
        fcom.ajax(fcom.makeUrl('BlogPostCategories', 'mediaForm', [prodCatId]), '', function(t) {
            $.facebox(t);
        });
    };

    deleteRecord = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('BlogPostCategories', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

    clearSearch = function() {
        document.frmSearch.reset();
        searchBlogPostCategories(document.frmSearch);
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
        var bpcategoryId = parseInt(obj.value);
        if (bpcategoryId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'bpcategoryId=' + bpcategoryId;
        fcom.ajax(fcom.makeUrl('BlogPostCategories', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);

            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
                $(obj).toggleClass("active");
                setTimeout(function() {
                    reloadList();
                }, 1000);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmBlogPostCatListing input[name='status']").val(status);
        $("#frmBlogPostCatListing").submit();
    };

    deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmBlogPostCatListing").attr("action",fcom.makeUrl('BlogPostCategories','deleteSelected')).submit();
    };

})();
