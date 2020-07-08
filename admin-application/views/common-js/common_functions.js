$(document).ready(function() {

    if (/ip(hone|od)|ipad/i.test(navigator.userAgent)) {
        $("body").css("cursor", "pointer");
    }
    /* for left side */
    $('.menutrigger').click(function() {
        $(this).toggleClass("active");
        var el = $("body");
        if (el.hasClass('toggled-left')) el.removeClass("toggled-left");
        else el.addClass('toggled-left');
        return false;
    });
    $('body').click(function() {

        if ($('body').hasClass('toggled-left')) {
            $('.menutrigger').removeClass("active");
            $('body').removeClass('toggled-left');
        }
        $('html').css('overflow', '');


    });

    $(document).on('click', '.popup .content', function(event) {
        $('html').css('overflow', 'hidden');
    });

    $('.leftoverlay').click(function() {
        if ($('body').hasClass('toggled-left')) {
            $('.menutrigger').removeClass("active");
            $('body').removeClass('toggled-left');
        }
    });

    /* for right side */
    $('.sidetoggle a').click(function() {
        $(this).toggleClass("active");
        var el = $("body");
        if (el.hasClass('toggled-right')) el.removeClass("toggled-right");
        else el.addClass('toggled-right');
        return false;
    });

    $('body').click(function() {
        if ($('body').hasClass('toggled-right')) {
            $('body').removeClass('toggled-right');
        }
    });
    $('.rightoverlay').click(function() {
        if ($('body').hasClass('toggled-right')) {
            $('body').removeClass('toggled-right');
        }
    });
    $('.leftside, .rightside').click(function(e) {
        e.stopPropagation();
        //return false;
    });


    /* for top right menu */
    $('.searchtoggle').click(function() {
        $(this).toggleClass("active");
        $('.searchwrap').slideToggle();
    });

    $('.language').click(function() {
        $(this).toggleClass("active");
    });


    /* for left links */
    $('.leftmenu > li  > a').click(function() {
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            $(this).siblings('.leftmenu > li ul').slideUp();
        } else {
            $('.leftmenu > li > a').removeClass('active');
            $(this).addClass("active");
            $('.leftmenu > li ul').slideUp();
            $(this).siblings('.leftmenu > li ul').slideDown();
        }
    });

    (function() {
        var uri = (window.location.pathname).replace(/^\/|\/$/g, '');
        var parentCat = null;
        $('aside.leftside ul.leftmenu li').each(function() {
            if ($(this).hasClass('haschild')) {
                parentCat = $(this);
                $(this).find('ul li').each(function() {
                    var href = $(this).find('a').attr('href').replace(/^\/|\/$/g, '');
                    if (href === uri) {
                        $(this).addClass('active');
                        $(parentCat).children('a').trigger('click');
                    }
                });
            } else {
                var href = $(this).find('a').attr('href').replace(/^\/|\/$/g, '');
                if (href === uri) {
                    $(this).addClass('active');
                }
            }
        });
    })();

    /* for profile links */
    $('.profileinfo').click(function() {
        $(this).toggleClass("active");
        $('.profilelinkswrap').slideToggle("600");
    });

    /* for selection table */
    $('.table-select tr').click(function() {
        $(this).toggleClass("active");
    });



    /* for sort icon */
    $('.iconsort').click(function() {
        $(this).toggleClass("active");
    });



    /* notifications */
    $('.alertlink').click(function() {
        $(this).toggleClass("");
        var align = $(this).attr('data-align');
        var type = $(this).attr('data-type');
        if (align.length < 1) return false;
        if (typeof type != 'undefined' && type.length) {
            var el = $(".alert_position." + align + "." + type + ":first");
        } else {
            var el = $(".alert_position." + align + ":first");
        }
        if (el.hasClass('animated fadeInDown')) {
            hideAlertBox(el);
        } else {
            el.removeClass('fadeOutUp');
            el.addClass('animated fadeInDown');
            setTimeout(function() {
                if (el.hasClass('fadeInDown')) hideAlertBox(el);
            }, 10000);
        }
        return false;
    });
    var hideAlertBox = function(el) {
        el.addClass('animated fadeOutUp');
        el.removeClass('fadeInDown');
        setTimeout(function() {
            el.removeClass('animated fadeOutUp');
        }, 1000);
    };
    $('.alert_close').click(function() {
        var el = $(this).parents('.animated.fadeInDown:first');
        if (el.length < 1) return false;
        hideAlertBox(el);
        return false;
    });
    $('body').click(function() {
        if ($('.alert_position').hasClass('animated fadeInDown')) {
            $('.alert_position').removeClass('animated fadeInDown');
        }
    });



    /* for globally actions menus */
    $(document).on('click', '.droplink', function(event) {
        $(this).toggleClass("active");
        return false;
    });
    $('html').click(function() {
        if ($('.droplink').hasClass('active')) {
            $('.droplink').removeClass('active');
        }
    });
    /* $(document).delegate('ul.linksvertical li','click',function(){
			console.log($(this).closest('li.droplink'));
            if($(this).closest('li.droplink').hasClass('active')){
                $(this).closest('li.droplink').removeClass('active');
            }
        });  */
    $(document).on('click', '.droplink', function(event) {
        event.stopPropagation();
    });



    /* for sidetabs */
    $(".tab_content").hide(); //Hide all content
    $(".normaltabs li:first").addClass("active").show(); //Activate first tab
    $(".tab_content:first").show(); //Show first tab content

    //On Click Event
    $(".normaltabs li").click(function() {

        $(".normaltabs li").removeClass("active"); //Remove any "active" class
        $(this).addClass("active"); //Add "active" class to selected tab
        $(".tab_content").hide(); //Hide all tab content

        var activeTab = $(this).find("a").attr("href"); //Find the href attribute value to identify the active tab + content
        $(activeTab).fadeIn(); //Fade in the active ID content
        return false;
    });


    /* wave ripple effect */
    var parent, ink, d, x, y;
    $(".themebtn, .leftmenu > li > a, .actions > li > a, .leftlinks > li > a, .profilecover .profileinfo, .pagination li a, .circlebutton, .columlist li a").click(function(e) {
        parent = $(this);
        //create .ink element if it doesn't exist
        if (parent.find(".ink").length == 0)
            parent.prepend("<span class='ink'></span>");

        ink = parent.find(".ink");
        //incase of quick double clicks stop the previous animation
        ink.removeClass("animate");

        //set size of .ink
        if (!ink.height() && !ink.width()) {
            //use parent's width or height whichever is larger for the diameter to make a circle which can cover the entire element.
            d = Math.max(parent.outerWidth(), parent.outerHeight());
            ink.css({
                height: d,
                width: d
            });
        }

        //get click coordinates
        //logic = click coordinates relative to page - parent's position relative to page - half of self height/width to make it controllable from the center;
        x = e.pageX - parent.offset().left - ink.width() / 2;
        y = e.pageY - parent.offset().top - ink.height() / 2;

        //set the position and add class .animate
        ink.css({
            top: y + 'px',
            left: x + 'px'
        }).addClass("animate");
    })



    /* for common dialog box */
    /*$('.dialog_open').click(function() {
			var el = $(".dialog_box_wrap");
			if(el.hasClass('active')) el.removeClass("active");
			else el.addClass('active');
		});*/



    /* for forms elements */
    function floatLabel(inputType) {
        $(inputType).each(function() {
            var $this = $(this);
            var text_value = $(this).val();

            // on focus add class "active" to label
            $this.focus(function() {

                $this.closest('.field_control').addClass("active");
            });

            // on blur check field and remove class if needed
            $this.blur(function() {
                if ($this.val() === '' || $this.val() === 'blank') {
                    $this.closest('.field_control').removeClass('active');
                }
            });

            // Check input values on postback and add class "active" if value exists
            if (text_value != '') {
                $this.closest('.field_control').addClass("active");
            }

            // Automatically remove floatLabel class from select input on load
            /* $('select').closest('.field_control').removeClass('active');*/
        });

    }
    // Add a class of "floatLabel" to the input field
    floatLabel(".web_form input[type='text'], .web_form input[type='password'], .web_form input[type='email'], .web_form select, .web_form textarea, .web_form input[type='file']");


    /* for common tabs */
    $(".tabs_panel").hide();
    $('.tabs_panel_wrap').find(".tabs_panel:first").show();


    /* if in tab mode */
    $(".tabs_nav li a").click(function() {
        $(this).parents('.tabs_nav_container:first').find(".tabs_panel").hide();
        var activeTab = $(this).attr("rel");
        $("#" + activeTab).fadeIn();

        $(this).parents('.tabs_nav_container:first').find(".tabs_nav li a").removeClass("active");
        $(this).addClass("active");

        $(".togglehead").removeClass("active");
        $(".togglehead[rel^='" + activeTab + "']").addClass("active");

        if ($(this).attr('data-chart')) {

            if (layoutDirection != 'rtl') {
                $position = 'start';
            } else {
                $position = 'end';
            }
            if (activeTab == 'tabs_1') {
                callChart('monthlysales--js', $SalesChartKey, $SalesChartVal, $position);
            } else if (activeTab == 'tabs_2') {
                callChart('monthlysalesearnings--js', $SalesEarningsKey, $SalesEarningsVal, $position);
            } else if (activeTab == 'tabs_3') {
                callChart('monthly-signups--js', $signupsKey, $signupsVal, $position);
            } else if (activeTab == 'tabs_4') {
                callChart('monthly-affiliate-signups--js', $affiliateSignupsKey, $affiliateSignupsVal, $position);
            } else if (activeTab == 'tabs_5') {
                callChart('monthly-products--js', $productsKey, $productsVal, $position);
            }
        }
    });

    /* if in drawer mode */
    $(".togglehead").click(function() {
        $(this).parents('.tabs_panel_wrap:first').find(".tabs_panel").hide();
        var d_activeTab = $(this).attr("rel");
        $(window).scrollTop($(this).parents('.tabs_panel_wrap:first').offset().top - 50);
        if ($(this).hasClass("active")) {
            $(".togglehead").removeClass("active");
            $(this).parents('.tabs_nav_container:first').find(".tabs_nav li a").removeClass("active");
            return false;
        } else {
            $("#" + d_activeTab).fadeIn();
        }

        $(".togglehead").removeClass("active");
        $(this).addClass("active");

        $(this).parents('.tabs_nav_container:first').find(".tabs_nav li a").removeClass("active");
        $(".tabs_nav li a[rel^='" + d_activeTab + "']").addClass("active");

        if ($(this).attr('data-chart')) {

            if (layoutDirection != 'rtl') {
                $position = 'start';
            } else {
                $position = 'end';
            }
            if (d_activeTab == 'tabs_1') {
                callChart('monthlysales--js', $SalesChartKey, $SalesChartVal, $position);
            } else if (d_activeTab == 'tabs_2') {
                callChart('monthlysalesearnings--js', $SalesEarningsKey, $SalesEarningsVal, $position);
            } else if (d_activeTab == 'tabs_3') {
                callChart('monthly-signups--js', $signupsKey, $signupsVal, $position);
            } else if (d_activeTab == 'tabs_4') {
                callChart('monthly-affiliate-signups--js', $affiliateSignupsKey, $affiliateSignupsVal, $position);
            } else if (d_activeTab == 'tabs_5') {
                callChart('monthly-products--js', $productsKey, $productsVal, $position);
            }
        }
        return;
    });



    /* for Accordian */
    //Set default open/close settings
    $('.accordiancontent').hide(); //Hide/close all containers
    $('.accordians_container').find('.accordianhead:first').addClass('active').next().show(); //Add "active" class to first trigger, then show/open the immediate next container

    //On Click
    $('.accordianhead').click(function() {
        if ($(this).next().is(':hidden')) { //If immediate next container is closed...
            $(this).parents('.accordians_container:first').find('.accordianhead').removeClass('active').next().slideUp(); //Remove all .acc_trigger classes and slide up the immediate next container
            $(this).toggleClass('active').next().slideDown(); //Add .acc_trigger class to clicked trigger and slide down the immediate next container
        } else {
            $(this).toggleClass('active').next().slideUp()
        }
        return false; //Prevent the browser jump to the link anchor
    });


    /* for inbox table */
    /* $('.medialist > li').change(function() {
            $(this).toggleClass("selected");
        });
    */

    /* for right side */
    $('.medialist > li').change(function() {
        $(this).toggleClass("selected");
        var el = $("body");
        if (el.hasClass('selected')) el.removeClass("selected");
        else el.addClass('selected');
        return false;
    });

    $('body').click(function() {
        if ($('body').hasClass('selected')) {
            $('body').removeClass('selected');
        }
    });
    $('.containerwhite').click(function(e) {
        e.stopPropagation();
        //return false;
    });


    $('.backarrow').click(function() {
        $(this).removeClass("selected");
    });


    /* for reply container */
    $('.openreply').click(function() {
        $(this).toggleClass("active");
        $('.boxcontainer').slideToggle();
    });


    /* for expand all messages on message details page */
    $('.expandlink').click(function() {
        $(this).toggleClass("active");
        var el = $(".medialist > li");
        if (el.hasClass('bodycollapsed')) el.removeClass("bodycollapsed");
        else el.addClass('bodycollapsed');
        return false;
    });

    $('body').click(function() {
        if ($('.containerwhite').hasClass('bodycollapsed')) {
            $('.containerwhite').removeClass('bodycollapsed');
        }
    });
    $('.containerwhite').click(function(e) {
        e.stopPropagation();
        //return false;
    });


    /* for fixed/fluid layout */
    $('.iconmenus .switch').click(function() {
        $(this).toggleClass("active");
        var el = $("body");
        if (el.hasClass('switch_layout')) el.removeClass("switch_layout");
        else el.addClass('switch_layout');
    });

    $('.layout_switcher').click(function() {
        $('.layout_switcher').removeClass('active');
        $(this).addClass('active');
        var el = $("body");
        if (el.hasClass('switch_layout')) el.removeClass("switch_layout");
        else el.addClass('switch_layout');
    });



    /* for welcome message */
    function showContent() {
        setTimeout(function() {
            $('.welcome_msg').fadeIn(1000).addClass("animated bounceIn");
            setTimeout(function() {
                $('.welcome_msg').fadeOut(1000).removeClass("bounceIn").addClass('bounceOut');
            }, 5000);
        }, 1500);
    }
    window.onload = showContent;


    /* for search form toggle */
    $(document).on('click','.togglelink a, .section.searchform_filter .sectionhead',function(){
    /* $(document).delegate('.togglelink a, .section.searchform_filter .sectionhead', 'click', function() { */
        $(this).toggleClass("active");
        $('.togglewrap').slideToggle();
    });

    // swith theme dynamically
    var themeLayoutSwitcher = function(val, type) {

        $.systemMessage(langLbl.processing, 'alert--process');
        var data = 'fIsAjax=1&';
        if (type == 'layout') {
            data += 'layout=' + val;
        } else {
            data += 'color=' + val;
        }

        $.ajax({
            type: "POST",
            url: fcom.makeUrl('Profile', 'themeSetup'),
            data: data,
            success: function(json) {
                json = $.parseJSON(json);
                $.systemMessage(json.msg, 'alert--success');
            }
        });
    }

    $('.layoutToggle .switch-label').click(function() {
        if ($("body").hasClass('switch_layout')) {
            themeLayoutSwitcher(0, 'layout');
        } else {
            themeLayoutSwitcher(1, 'layout');
        }
    })
    $('.layoutToggle .switch-handle').click(function() {
        if ($("body").hasClass('switch_layout')) {
            themeLayoutSwitcher(0, 'layout');
        } else {
            themeLayoutSwitcher(1, 'layout');
        }
    })

    $(document).ready(function() {
        $('.system_message').hide();
        if ($('.system_message').find('.div_error').length > 0 || $('.system_message').find('.div_msg').length > 0) {
            $('.system_message').show();
        }
        $('.closeMsg').click(function() {
            $('.system_message').find('.div_error').remove();
            $('.system_message').find('.div_msg').remove();
            $('.system_message').hide();
        });
    });

    $(document).on('click', 'a.redirect--js', function(event) {
        event.stopPropagation();
    });


    // Autocomplete */
    (function($) {
        $.fn.autocomplete = function(option) {
            return this.each(function() {
                this.timer = null;
                this.items = new Array();

                $.extend(this, option);

                $(this).attr('autocomplete', 'off');

                // Focus
                $(this).on('focus', function() {
                    this.request();
                });

                // Blur
                $(this).on('blur', function() {
                    setTimeout(function(object) {
                        object.hide();
                    }, 200, this);
                });

                // Keydown
                $(this).on('keydown', function(event) {
                    switch (event.keyCode) {
                        case 27: // escape
                        case 9: // tab
                            this.hide();
                            break;
                        default:
                            this.request();
                            break;
                    }
                });

                // Click
                this.click = function(event) {
                    event.preventDefault();

                    value = $(event.target).parent().attr('data-value');

                    if (value && this.items[value]) {
                        this.select(this.items[value]);
                    }
                }

                // Show
                this.show = function() {
                    var pos = $(this).position();

                    $(this).siblings('ul.dropdown-menu').css({
                        top: pos.top + $(this).outerHeight(),
                        left: pos.left
                    });

                    $(this).siblings('ul.dropdown-menu').show();
                }

                // Hide
                this.hide = function() {
                    $(this).siblings('ul.dropdown-menu').hide();
                }

                // Request
                this.request = function() {
                    clearTimeout(this.timer);

                    this.timer = setTimeout(function(object) {

                        var txt_box_width = $(object).outerWidth();
                        $(object).siblings('ul.dropdown-menu').width(txt_box_width + 'px');

                        if ($(object).attr('name') == 'keyword') {
                            /* i.e header search form will enable autocomplete, if minimum characters are 3 */
                            if ($(object).val().length < 3) {
                                return;
                            }
                        }

                        object.source($(object).val(), $.proxy(object.response, object));
                    }, 200, this);
                }

                // Response
                this.response = function(json) {
                    html = '';

                    if (json.length) {
                        for (i = 0; i < json.length; i++) {
                            this.items[json[i]['value']] = json[i];
                        }

                        for (i = 0; i < json.length; i++) {
                            if (!json[i]['category']) {
                                html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
                            }
                        }

                        // Get all the ones with a categories
                        var category = new Array();

                        for (i = 0; i < json.length; i++) {
                            if (json[i]['category']) {
                                if (!category[json[i]['category']]) {
                                    category[json[i]['category']] = new Array();
                                    category[json[i]['category']]['name'] = json[i]['category'];
                                    category[json[i]['category']]['item'] = new Array();
                                }

                                category[json[i]['category']]['item'].push(json[i]);
                            }
                        }

                        for (i in category) {
                            html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

                            for (j = 0; j < category[i]['item'].length; j++) {
                                html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
                            }
                        }
                    }

                    if (html) {
                        this.show();
                    } else {
                        this.hide();
                    }

                    $(this).siblings('ul.dropdown-menu').html(html);
                }

                $(this).after('<ul class="dropdown-menu"></ul>');
                /* $(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this)); */
                $(this).siblings('ul.dropdown-menu').on('click', 'a', $.proxy(this.click, this));
            });
        }
    })(window.jQuery);

    $('.close').click(function() {
        $.systemMessage.close();
    })

    $(document).on("click", ".selectItem--js", function() {
        if ($(this).prop("checked") == false) {
            $(".selectAll-js").prop("checked", false);
        }
        if ($(".selectItem--js").length == $(".selectItem--js:checked").length) {
            $(".selectAll-js").prop("checked", true);
        }
    });

});

$(document).ajaxComplete(function() {
    if (0 < $("#facebox").length) {
        if ($("#facebox").is(":visible")) {
            $('html').addClass('pop-on');
        } else {
            $('html').removeClass('pop-on');
        }
        $("#facebox .close").on("click", function(){
            $("html").removeClass('pop-on');
        });
    }
});

function setSiteDefaultLang(langId) {
    fcom.displayProcessing();
    fcom.updateWithAjax(fcom.makeUrl('Home', 'setLanguage', [langId]), '', function(res) {
        document.location.reload();
    });
}

function getNotifications() {
    $("#notificationList").html(fcom.getLoader());

    fcom.ajax(fcom.makeUrl('Notifications', 'notificationList'), '', function(res) {
        $("#notificationList").html(res);
    });
}

function selectAll(obj) {
    $(".selectItem--js").each(function() {
        if (obj.prop("checked") == false) {
            $(this).prop("checked", false);
        } else {
            $(this).prop("checked", true);
        }
    });
}

function formAction(frm, callback) {
    if (typeof $(".selectItem--js:checked").val() === 'undefined') {
        $.systemMessage(langLbl.atleastOneRecord, 'alert--danger');
        return false;
    }

    $.systemMessage.loading();
    data = fcom.frmData(frm);

    fcom.updateWithAjax(frm.action, data, function(resp) {
        callback();
    });
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



})();
