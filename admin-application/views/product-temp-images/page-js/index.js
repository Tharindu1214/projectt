(function() {
    var currentPage = 1;
    var dv = '#listing';
    searchProductsTempImages = function(frm) {
        var data = '';
        if (frm) {
            data = fcom.frmData(frm);
        }

        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('ProductTempImages', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };
    clearSearch = function() {
        document.frmProductTempImages.reset();
        searchProductsTempImages(document.frmProductTempImages);
    };
    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmProductsTempImagesPaging;
        $(frm.page).val(page);
        searchProductsTempImages(frm);
    }
    editProductTempImage = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('ProductTempImages', 'form', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };
    updateProductTempImage = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ProductTempImages', 'update'), data, function(t) {
            searchProductsTempImages(document.frmProductTempImages);
            $(document).trigger('close.facebox');
        });
    };
})();
$(document).ready(function() {
    var frm = document.frmProductTempImages;
    searchProductsTempImages(frm);
});
