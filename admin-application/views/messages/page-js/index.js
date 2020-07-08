$(document).ready(function() {
    searchMessages(document.frmSearch);

    $('input[name=\'message_by\']').autocomplete({
        'source': function(request, response) {
            $.ajax({
                url: fcom.makeUrl('Users', 'autoCompleteJson'),
                data: {
                    keyword: request,
                    fIsAjax: 1
                },
                dataType: 'json',
                type: 'post',
                success: function(json) {
                    response($.map(json, function(item) {
                        return {
                            label: item['name'] + '(' + item['username'] + ')',
                            value: item['id'],
                            name: item['username']
                        };
                    }));
                },
            });
        },
        'select': function(item) {
            $("input[name='message_by']").val(item['name']);
        }
    });

    $('input[name=\'message_to\']').autocomplete({
        'source': function(request, response) {
            $.ajax({
                url: fcom.makeUrl('Users', 'autoCompleteJson'),
                data: {
                    keyword: request,
                    fIsAjax: 1
                },
                dataType: 'json',
                type: 'post',
                success: function(json) {
                    response($.map(json, function(item) {
                        return {
                            label: item['name'] + '(' + item['username'] + ')',
                            value: item['id'],
                            name: item['username']
                        };
                    }));
                },
            });
        },
        'select': function(item) {
            $("input[name='message_to']").val(item['name']);
        }
    });

    $(document).on('click', 'ul.linksvertical li a.redirect--js', function(event) {
        event.stopPropagation();
    });

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
        searchMessages(frm);
    }

    reloadList = function() {
        var frm = document.frmSearchPaging;
        searchMessages(frm);
    }

    searchMessages = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $("#listing").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Messages', 'searchMessageThreads'), data, function(res) {
            $("#listing").html(res);
        });
    };

    clearSearch = function() {
        document.frmSearch.reset();
        searchMessages(document.frmSearch);
    };

})();
