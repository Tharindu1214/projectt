$(document).ready(function () {
	$(document).on('keypress', 'input.zip-js', function (e) {
        var regex = new RegExp("^[a-zA-Z0-9]+$");
        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
        if (regex.test(str)) {
            return true;
        }

        e.preventDefault();
        return false;
    });

	/*$(document).on('keydown', 'input.phone-js', function(e) {
        var key = e.which || e.charCode || e.keyCode || 0;
        $phone = $(this);

        // Don't let them remove the starting '('
        if ($phone.val().length === 1 && (key === 8 || key === 46)) {
            $phone.val('(');
            return false;
        }
        // Reset if they highlight and type over first char.
        else if ($phone.val().charAt(0) !== '(') {
            $phone.val('(');
        }

        // Auto-format- do not expose the mask as the user begins to type
        if (key !== 8 && key !== 9) {
            if ($phone.val().length === 4) {
                $phone.val($phone.val() + ')');
            }
            if ($phone.val().length === 5) {
                $phone.val($phone.val() + ' ');
            }
            if ($phone.val().length === 9) {
                $phone.val($phone.val() + '-');
            }
        }

        // Allow numeric (and tab, backspace, delete, hyphen, space) keys only
        return (key == 8 ||
            key == 9 ||
            key == 46 ||
            key == 189 ||
            key == 32 ||
            (key >= 48 && key <= 57) ||
            (key >= 96 && key <= 105));
    });
    $(document).on('focus', 'input.phone-js', function() {
        $phone = $(this);
        if ($phone.val().length === 0) {
            $phone.val('(');
        } else {
            var val = $phone.val();
            $phone.val('').val(val); // Ensure cursor remains at the end
        }
    });
    $(document).on('blur', 'input.phone-js', function() {
        $phone = $(this);
        if ($phone.val() === '(') {
            $phone.val('');
        }
    });*/
});

(function($) {
    var screenHeight = $(window).height() - 100;
    window.onresize = function(event) {
        var screenHeight = $(window).height() - 100;
    };

    $.extend(fcom, {

        waitAndRedirect: function(msg, url, time) {
            var time = time || 3000;
            var url = url || fcom.makeUrl();
            $.systemMessage(msg);
            setTimeout(function() {
                location.href = url;
            }, time);
        },

        scrollToTop: function(obj) {
            if (typeof obj == undefined || obj == null) {
                $('html, body').animate({
                    scrollTop: $('html, body').offset().top - 100
                }, 'slow');
            } else {
                $('html, body').animate({
                    scrollTop: $(obj).offset().top - 100
                }, 'slow');
            }
        },

        resetEditorInstance: function() {
            if (typeof oUtil != 'undefined') {

                var editors = oUtil.arrEditor;

                for (x in editors) {
                    eval('delete window.' + editors[x]);
                }
                oUtil.arrEditor = [];
            }
        },

        setEditorLayout: function(lang_id) {
            var editors = oUtil.arrEditor;
            layout = langLbl['language' + lang_id];
            for (x in editors) {
                $('#idContent' + editors[x]).contents().find("body").css('direction', layout);
            }
        },

        resetFaceboxHeight: function() {
            $('html').css('overflow', 'hidden');
            facebocxHeight = screenHeight;
            var fbContentHeight = parseInt($('#facebox .content').height()) + parseInt(100);
            $('#facebox .content').css('max-height', facebocxHeight - 50 + 'px');
            if (fbContentHeight >= screenHeight) {
                $('#facebox .content').css('overflow-y', 'scroll');
                $('#facebox .content').css('display', 'block');
            } else {
                $('#facebox .content').css('max-height', '');
                $('#facebox .content').css('overflow', '');
            }
        },

        getLoader: function() {
            return '<div class="circularLoader"><svg class="circular" height="30" width="30"><circle class="path" cx="25" cy="25.2" r="19.9" fill="none" stroke-width="6" stroke-miterlimit="10"></circle> </svg> </div>';
        },

        updateFaceboxContent: function(t, cls) {
            if (typeof cls == 'undefined' || cls == 'undefined') {
                cls = '';
            }
            $.facebox(t, cls);
            $.systemMessage.close();
            fcom.resetFaceboxHeight();
        },
        displayProcessing: function(msg, cls, autoclose) {
            if (typeof msg == 'undefined' || msg == 'undefined') {
                msg = langLbl.processing;
            }
            $.systemMessage(msg, 'alert--process', autoclose);
        },
        displaySuccessMessage: function(msg, cls, autoclose) {
            if (typeof cls == 'undefined' || cls == 'undefined') {
                cls = 'alert--success';
            }
            $.systemMessage(msg, cls, autoclose);
        },
        displayErrorMessage: function(msg, cls, autoclose) {
            if (typeof cls == 'undefined' || cls == 'undefined') {
                cls = 'alert--danger';
            }
            $.systemMessage(msg, cls, autoclose);
        }
    });

    $(document).bind('reveal.facebox', function() {
        fcom.resetFaceboxHeight();
    });

    $(window).on("orientationchange", function() {
        fcom.resetFaceboxHeight();
    });

    $(document).bind('loading.facebox', function() {

        $('#facebox .content').addClass('fbminwidth');
    });

    $(document).bind('afterClose.facebox', fcom.resetEditorInstance);
    $(document).bind('afterClose.facebox', function() {
        $('html').css('overflow', '')
    });

    $.systemMessage = function(data, cls, autoClose) {
        if (typeof autoClose == 'undefined' || autoClose == 'undefined') {
            autoClose = false;
        } else {
            autoClose = true;
        }
        initialize();
        $.systemMessage.loading();
        $.systemMessage.fillSysMessage(data, cls, autoClose);
    }
    $.extend($.systemMessage, {
        settings: {
            closeimage: siteConstants.webroot + 'images/facebox/close.gif',
        },
        loading: function() {
            $('.alert').show();
        },
        fillSysMessage: function(data, cls, autoClose) {
            $('.alert').removeClass('alert--success');
            $('.alert').removeClass('alert--danger');
            $('.alert').removeClass('alert--process');

            if (cls) $('.system_message').addClass(cls);
            $('.system_message .content').html(data);
            $('.system_message').fadeIn();

            if (!autoClose && CONF_AUTO_CLOSE_SYSTEM_MESSAGES == 1) {
                var time = CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES * 1000;
                setTimeout(function() {
                    $.systemMessage.close();
                }, time);
            }
            /* setTimeout(function() {
            	$('.system_message').hide('fade', {}, 500)
            }, 5000); */
        },
        close: function() {
            $(document).trigger('close.sysmsgcontent');
        },
    });

    function initialize() {
        $('.alert .close').click($.systemMessage.close);
    }

    $(document).bind('close.sysmsgcontent', function() {
        $('.alert').fadeOut();
    });

    $.facebox.settings.loadingImage = SITE_ROOT_URL + 'img/facebox/loading.gif';
    $.facebox.settings.closeImage = SITE_ROOT_URL + 'img/facebox/closelabel.png';

    if ($.datepicker) {

        var old_goToToday = $.datepicker._gotoToday
        $.datepicker._gotoToday = function(id) {
            old_goToToday.call(this, id);
            this._selectDate(id);
            $(id).blur();
            return;
        }
    }


    refreshCaptcha = function(elem) {
        $(elem).attr('src', siteConstants.webroot + 'helper/captcha?sid=' + Math.random());
    }

    clearCache = function() {
        $.systemMessage(langLbl.processing, 'alert--process');
        fcom.ajax(fcom.makeUrl('Home', 'clear'), '', function(t) {
            window.location.reload();
        });
    }

    SelectText = function(element) {
        var doc = document,
            text = doc.getElementById(element),
            range, selection;
        if (doc.body.createTextRange) {
            range = document.body.createTextRange();
            range.moveToElementText(text);
            range.select();
        } else if (window.getSelection) {
            selection = window.getSelection();
            range = document.createRange();
            range.selectNodeContents(text);
            selection.removeAllRanges();
            selection.addRange(range);
        }
    }
    getSlugUrl = function(obj, str, extra, pos) {
        if (pos == undefined)
            pos = 'pre';
        var str = str.toString().toLowerCase()
            .replace(/\s+/g, '-') // Replace spaces with -
            .replace(/[^\w\-\/]+/g, '') // Remove all non-word chars
            .replace(/\-\-+/g, '-') // Replace multiple - with single -
            .replace(/^-+/, '') // Trim - from start of text
            .replace(/-+$/, '');
        if (extra && pos == 'pre') {
            str = extra + '/' + str;
        }
        if (extra && pos == 'post') {
            str = str + '/' + extra;
        }

        $(obj).next().html(SITE_ROOT_URL + str);

    };

    redirectfunc = function(url, id, nid, newTab) {
        newTab = (typeof newTab != "undefined") ? newTab : true;
        if (nid > 0) {
            $.systemMessage(langLbl.processing, 'alert--process');
            markRead(nid, url, id);
        } else {
            var target = (newTab) ? ' target="_blank" ' : ' ';
            var form = '<input type="hidden" name="id" value="' + id + '">';
            $('<form' + target + 'action="' + url + '" method="POST">' + form + '</form>').appendTo($(document.body)).submit();
        }
    };

    markRead = function(nid, url, id) {
        if (nid.length < 1) {
            return false;
        }
        var data = 'record_ids=' + nid + '&status=' + 1 + '&markread=1';
        fcom.updateWithAjax(fcom.makeUrl('Notifications', 'changeStatus'), data, function(t) {
            var form = '<input type="hidden" name="id" value="' + id + '">';
            $('<form action="' + url + '" method="POST">' + form + '</form>').appendTo($(document.body)).submit();
        });
    };

    /* $(document).click(function(event) {
    	$('ul.dropdown-menu').hide();
    }); */
})(jQuery);

function getSlickSliderSettings(slidesToShow, slidesToScroll, layoutDirection) {
    slidesToShow = (typeof slidesToShow != "undefined") ? parseInt(slidesToShow) : 4;
    slidesToScroll = (typeof slidesToScroll != "undefined") ? parseInt(slidesToScroll) : 1;
    layoutDirection = (typeof layoutDirection != "undefined") ? layoutDirection : 'ltr';

    if (layoutDirection == 'rtl') {
        return {
            slidesToShow: slidesToShow,
            slidesToScroll: slidesToScroll,
            infinite: false,
            arrows: true,
            rtl: true,
            prevArrow: '<a data-role="none" class="slick-prev" aria-label="previous"></a>',
            nextArrow: '<a data-role="none" class="slick-next" aria-label="next"></a>',
            responsive: [{
                    breakpoint: 1050,
                    settings: {
                        slidesToShow: slidesToShow - 1,
                    }
                },
                {
                    breakpoint: 990,
                    settings: {
                        slidesToShow: 3,
                    }
                },
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 2,
                    }
                },
                {
                    breakpoint: 400,
                    settings: {
                        slidesToShow: 1,
                    }
                }
            ]
        }
    } else {
        return {
            slidesToShow: slidesToShow,
            slidesToScroll: slidesToScroll,
            infinite: false,
            arrows: true,
            prevArrow: '<a data-role="none" class="slick-prev" aria-label="previous"></a>',
            nextArrow: '<a data-role="none" class="slick-next" aria-label="next"></a>',
            responsive: [{
                    breakpoint: 1050,
                    settings: {
                        slidesToShow: slidesToShow - 1,
                    }
                },
                {
                    breakpoint: 990,
                    settings: {
                        slidesToShow: 3,
                    }
                },
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 2,
                    }
                },
                {
                    breakpoint: 400,
                    settings: {
                        slidesToShow: 1,
                    }
                }
            ]
        }
    }
}
(function() {

    Slugify = function(str, str_val_id, is_slugify) {
        var str = str.toString().toLowerCase()
            .replace(/\s+/g, '-') // Replace spaces with -
            .replace(/[^\w\-]+/g, '') // Remove all non-word chars
            .replace(/\-\-+/g, '-') // Replace multiple - with single -
            .replace(/^-+/, '') // Trim - from start of text
            .replace(/-+$/, '');
        if ($("#" + is_slugify).val() == 0)
            $("#" + str_val_id).val(str);
    };

    callChart = function(dv, $labels, $series, $position) {


        new Chartist.Bar('#' + dv, {

            labels: $labels,

            series: [$series],



        }, {

            stackBars: false,

            axisY: {
                position: $position,
                labelInterpolationFnc: function(value) {

                    return (value / 1000) + 'k';

                }

            }

        }).on('draw', function(data) {

            if (data.type === 'bar') {

                data.element.attr({

                    style: 'stroke-width: 25px'

                });

            }

        });

    }

    $(document).on('click', ".group__head-js", function() {
        if ($(this).parents('.group-js').hasClass('is-active')) {
            $(this).siblings('.group__body-js').slideUp();
            $('.group-js').removeClass('is-active');
        } else {
            $('.group-js').removeClass('is-active');
            $(this).parents('.group-js').addClass('is-active');
            $('.group__body-js').slideUp();
            $(this).siblings('.group__body-js').slideDown();
        }
    });

    if ($(window).width() < 767) {
        $('html').removeClass('sticky-demo-header');
    }

})();
