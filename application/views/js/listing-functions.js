$(document).ready(function() {
    $(document).on('click', '#accordian li span.acc-trigger', function() {
        var link = $(this);
        var closest_ul = link.siblings("ul");

        if (link.hasClass("is--active")) {
            closest_ul.slideUp();
            link.removeClass("is--active");
        } else {
            closest_ul.slideDown();
            link.addClass("is--active");
        }
    });

    /* for left filters  */
    $('.link__filter').click(function() {
        $(this).toggleClass("active");
        var el = $("body");
        if (el.hasClass('filter__show')) el.removeClass("filter__show");
        else el.addClass('filter__show');
        return false;
    });

    $('body').click(function() {
        if ($('body').hasClass('filter__show')) {
            $('.link__filter').removeClass("active");
            $('body').removeClass('filter__show');
        }
    });

    $('.filter__overlay').click(function() {
        if ($('body').hasClass('filter__show')) {
            $('.link__filter').removeClass("active");
            $('body').removeClass('filter__show');
        }
    });

    $('.productFilters-js').click(function(e) {
        e.stopPropagation();
    });

    /* for mobile toggle */
    if ($(window).width() < 1025) {
        $(".productFilters-js").on("click", ".filter-head-js", function() {
            if ($(this).hasClass('active')) {
                $(this).removeClass('active');
                $(this).next().slideUp();
            } else {
                $(this).addClass('active');
                $(this).next().slideDown();
            }
        });
    }
});
