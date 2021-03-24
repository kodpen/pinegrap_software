

$(document).ready(function () {


   

    $.fn.autogrow = function () {
        return this.each(function () {
            var textarea = this;

            $.fn.autogrow.resize(textarea);

            $(textarea)
                .focus(function () {
                    textarea.interval = setInterval(function () {
                        $.fn.autogrow.resize(textarea);
                    }, 500);
                })
                .blur(function () {
                    clearInterval(textarea.interval);
                });
        });
    };

    $.fn.autogrow.resize = function (textarea) {
        var lineHeight = parseInt($(textarea).css("line-height"), 10);

        var lines = textarea.value.split("\n");

        var columns = textarea.cols;

        var lineCount = 0;

        $.each(lines, function () {
            lineCount += Math.ceil(this.length / columns) || 1;
        });

        var height = lineHeight * (lineCount + 1);

        height = height;

        $(textarea).css("height", height);
    };

    $("input[type=password]")
        .not("#pin1,#pin2,#pin3,#pin4")
        .each(function () {
            $styles = $(this).attr("style");

            if ($styles) {
                $styles = 'style="' + $styles + '"';
            } else {
                $styles = "";
            }

            $placeholder = $(this).attr("placeholder");

            if (!$placeholder) {
                $(this).attr("placeholder", "*********");
            }

            $(this).after(
                '<div class="button_3d_secondary show_my_input_password no-select" ' +
                $styles +
                '><i class="material-icons">visibility</i></div><div class="button_3d_secondary hide_my_input_password always-hide no-select" ' +
                $styles +
                '><i class="material-icons">visibility_off</i></div>'
            );
        });

    $(".show_my_input_password").click(function () {
        $(this).addClass("always-hide");

        $(this).parent().find(".hide_my_input_password").removeClass("always-hide");

        $(this).parent().find("input[type=password]").get(0).type = "text";
    });

    $(".hide_my_input_password").click(function () {
        $(this).addClass("always-hide");

        $(this).parent().find(".show_my_input_password").removeClass("always-hide");

        $(this).parent().find("input[type=text]").get(0).type = "password";
    });

    $("textarea").autogrow();

    // find all pinegrap directory items contains .php

    if (
        (window.location.href.indexOf("/pinegrap/") &&
            window.location.href.indexOf(".php") > 0) > 0
    ) {
        (function ($) {
            $.fn.changeElementType = function (newType) {
                var attrs = {};

                if (!(this[0] && this[0].attributes)) return;

                $.each(this[0].attributes, function (idx, attr) {
                    attrs[attr.nodeName] = attr.nodeValue;
                });

                this.replaceWith(function () {
                    return $("<" + newType + "/>", attrs).append($(this).contents());
                });
            };
        })(jQuery);

        if ($(".nav").length) {
            $(".nav-wrapper").has("#menu.nav").insertAfter(".first_slot");
            $("#header .nav li a").each(function () {
                $(this).prop("title", $(this).text());
            });

            $(".account_more_trigger a").click(function () {
                $("#accountmenu").attr("style", "transform:translate(0%);");
            });

            $(document).mouseup(function (e) {
                var container = $("#accountmenu");
                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    $("#accountmenu").attr(
                        "style",
                        "transform:translate(0%,-202%);display:none"
                    );
                }
            });

            $(".nav ul li:not(.morenav-trigger)").clone().appendTo("#morenavmenu");

            $(".morenav-trigger").click(function () {
                var p_trigger = $(this).position();

                $("#morenavmenu").attr(
                    "style",
                    "transform:translate(0%);left:" + p_trigger.left + "px;"
                );
            });

            $(document).mouseup(function (e) {
                var container = $("#morenavmenu");

                var p_trigger = $(".morenav-trigger").position();

                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    $("#morenavmenu").attr(
                        "style",
                        "transform:translate(0%,-202%);left:" +
                        p_trigger.left +
                        "px;display:none"
                    );
                }
            });
        }
    }

    //resize tables

    var pressed = false;

    var start = undefined;

    var startX, startWidth;

    var reset_resizes = $("table.chart th:last-child");

    var exclusion_1 = $(
        "table.chart th:contains(Select),table.chart th:contains(Deselect)"
    );

    var exclusion_2 = $("table.chart th:last-child");

    $("table.chart th")
        .not(exclusion_1)
        .not(exclusion_2)
        .mousedown(function (e) {
            start = $(this);

            pressed = true;

            startX = e.pageX;

            startWidth = $(this).width();

            $(start).addClass("resizing");
        });

    $("table.chart th")
        .not(exclusion_1)
        .not(exclusion_2)
        .dblclick(function () {
            $("table.chart th").css("width", "150px");

            $(this).css("width", "90%");
        });

    reset_resizes.click(function () {
        $("table.chart th").css("width", "unset");
    });

    $(document)
        .not(exclusion_1)
        .not(exclusion_2)
        .mousemove(function (e) {
            if (pressed) {
                $(start).width(startWidth + (e.pageX - startX));
            }
        });

    $(document)
        .not(exclusion_1)
        .not(exclusion_2)
        .mouseup(function () {
            if (pressed) {
                $(start).removeClass("resizing");

                pressed = false;
            }
        });

    var checkbox_td = $("table:not(.chart) tr td").has("input[type=checkbox]");

    checkbox_td
        .parent('tr:not([style*="display: none"])')
        .attr("style", "flex-direction: row;");

    var table_chart = $("#content table.chart");

    table_chart.wrap('<div class="chart-wrapper"></div>');

    $("#content table.order_details").wrap(
        '<div class="order_table_responsive"></div>'
    );
});

$(window).load(function () {
    var path = window.location.href; // because the 'href' property of the DOM element is the absolute path

    $("#subnav a").each(function () {
        if (this.href === path) {
            $(this).addClass("active");
        }
    });

    var Subnav_Button = $("#subnav table a.active");
    if (Subnav_Button.length) {
        var Subnav_Button_POS = Subnav_Button.offset().left;
        $("#subnav").scrollLeft(Subnav_Button_POS - 23);
    }
});
$.fn.isHScrollable = function () {
    return this[0].scrollWidth > this[0].clientWidth;
};

$.fn.isVScrollable = function () {
    return this[0].scrollHeight > this[0].clientHeight;
};

$.fn.isScrollable = function () {
    return this[0].scrollWidth > this[0].clientWidth || this[0].scrollHeight > this[0].clientHeight;
};



// Bind to the resize event of the window object
$(window)
    .on("load resize", function () {
        var subnavWidth = $("#subnav").width();
        var scroll_subnav_menu_previous = $(".scroll_subnav_menu_previous");
        var scroll_subnav_menu_next = $(".scroll_subnav_menu_next");
        scroll_subnav_menu_previous.remove();
        scroll_subnav_menu_next.remove();
        if ($("#subnav table tbody").width() > subnavWidth) {
            $("#subnav").attr("style", "padding-left: 6px;white-space: nowrap;");

            $("#subnav").prepend(
                '<a class=" button_3d_secondary scroll_subnav_menu_next material-icons">keyboard_arrow_right</a>'
            );
            $("#subnav").prepend(
                '<a style="" class=" button_3d_secondary scroll_subnav_menu_previous material-icons">keyboard_arrow_left</a>'
            );
        } else {
            $("#subnav").attr("style", "");
        }

        var NEXTbtn = document.querySelector(".scroll_subnav_menu_previous");
        var PREVIOUSbtn = document.querySelector(".scroll_subnav_menu_next");
        if ($(".scroll_subnav_menu_previous").length > 0) {
            PREVIOUSbtn.onclick = function () {
                document.getElementById("subnav").scrollLeft += subnavWidth / 2;
            };
        }
        if ($(".scroll_subnav_menu_next").length > 0) {
            NEXTbtn.onclick = function () {
                document.getElementById("subnav").scrollLeft -= subnavWidth / 2;
            };
        }

        if ($(window).width() < 720) {
            
            $('body.welcome .widget .widget-content').each(function () {
                if ($(this).isScrollable()) {
                    $(this).next('.widget-content-mask').remove();
                    $(this).after('<div class="widget-content-mask"></div>');
                };
            });
            $('body.welcome .widget .widget-content-mask').click(function () {

                $(this).remove();
            });
        } else {
            $('body.welcome .widget .widget-content-mask').remove();

        }




    })
    .resize();
