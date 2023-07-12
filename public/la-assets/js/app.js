/* ================= Dynamic Page Loading ================= */

// https://github.com/defunkt/jquery-pjax
$(document).pjax("a[data-pjax]", "#content-wrapper", {
    timeout: 2000,
});
$(document).on("pjax:complete", function () {
    refreshForm();
});

/* ================= Common Data ================= */

var bsurl = $("body").attr("bsurl");
var adminRoute = $("body").attr("adminRoute");
var _token = $("input[name=_token]").val();

/* ================= Custom Methods ================= */

if (typeof String.prototype.hashCode !== "function") {
    String.prototype.hashCode = function () {
        var hash = 0;
        if (this.length == 0) return hash;
        for (i = 0; i < this.length; i++) {
            char = this.charCodeAt(i);
            hash = (hash << 5) - hash + char;
            hash = hash & hash; // Convert to 32bit integer
        }
        return hash;
    };
}
if (typeof String.prototype.trim !== "function") {
    String.prototype.trim = function () {
        return this.replace(/^\s+|\s+$/g, "");
    };
}
if (typeof String.prototype.startsWith != "function") {
    // see below for better implementation!
    String.prototype.startsWith = function (str) {
        return this.indexOf(str) == 0;
    };
}
if (typeof String.prototype.ucfirst != "function") {
    // see below for better implementation!
    String.prototype.ucfirst = function () {
        return this.charAt(0).toUpperCase() + this.slice(1);
    };
}

if (typeof String.prototype.endsWith != "function") {
    // see below for better implementation!
    String.prototype.endsWith = function (pattern) {
        var d = this.length - pattern.length;
        return d >= 0 && this.lastIndexOf(pattern) === d;
    };
}
if (typeof Array.prototype.clear != "function") {
    Array.prototype.clear = function () {
        this.splice(0, this.length);
    };
}
// Convert String MySQL Timestamp to JS Date Object
if (typeof String.prototype.toDate != "function") {
    String.prototype.toDate = function () {
        var t = this.split(/[- :]/);
        return new Date(Date.UTC(t[0], t[1] - 1, t[2], t[3], t[4], t[5]));
    };
}
// Convert JS Date Object to MySQL Timestamp
function twoDigits(d) {
    if (0 <= d && d < 10) return "0" + d.toString();
    if (-10 < d && d < 0) return "-0" + (-1 * d).toString();
    return d.toString();
}
if (typeof Date.prototype.toTS != "function") {
    Date.prototype.toTS = function () {
        return (
            this.getUTCFullYear() +
            "-" +
            twoDigits(1 + this.getUTCMonth()) +
            "-" +
            twoDigits(this.getUTCDate()) +
            " " +
            twoDigits(this.getUTCHours()) +
            ":" +
            twoDigits(this.getUTCMinutes()) +
            ":" +
            twoDigits(this.getUTCSeconds())
        );
    };
}

function isset(value) {
    if (typeof value !== "undefined" && value !== null) {
        return true;
    }
    return false;
}

function getFormDataJSON($form) {
    var unindexed_array = $form.serializeArray();
    var indexed_array = {};

    $.map(unindexed_array, function (n, i) {
        if (n["name"].endsWith("[]")) {
            var key = n["name"].replace("[]", "");
            if (key in indexed_array) {
                indexed_array[key].push(n["value"]);
            } else {
                indexed_array[key] = [];
                indexed_array[key].push(n["value"]);
            }
        } else {
            indexed_array[n["name"]] = n["value"];
        }
    });

    return JSON.stringify(indexed_array);
}

/* ================= Fancy Notifications ================= */
(function ($) {
    "use strict";
    var Notification = function (container, options) {
        var self = this;
        self.container = $(container);
        self.notification = $('<div class="pgn push-on-sidebar-open"></div>');
        self.options = $.extend(
            true,
            {},
            $.fn.pgNotification.defaults,
            options
        );
        if (
            !self.container.find(
                ".pgn-wrapper[data-position=" + this.options.position + "]"
            ).length
        ) {
            self.wrapper = $(
                '<div class="pgn-wrapper" data-position="' +
                    this.options.position +
                    '"></div>'
            );
            self.container.append(self.wrapper);
        } else {
            self.wrapper = $(
                ".pgn-wrapper[data-position=" + this.options.position + "]"
            );
        }
        self.alert = $('<div class="alert"></div>');
        self.alert.addClass("alert-" + self.options.type);
        if (self.options.style == "bar") {
            new BarNotification();
        } else if (self.options.style == "flip") {
            new FlipNotification();
        } else if (self.options.style == "circle") {
            new CircleNotification();
        } else if (self.options.style == "simple") {
            new SimpleNotification();
        } else {
            new SimpleNotification();
        }

        function SimpleNotification() {
            self.notification.addClass("pgn-simple");
            self.alert.append(self.options.message);
            if (self.options.showClose) {
                var close = $(
                    '<button type="button" class="close" data-dismiss="alert"></button>'
                )
                    .append('<span aria-hidden="true">&times;</span>')
                    .append('<span class="sr-only">Close</span>');
                self.alert.prepend(close);
            }
        }

        function BarNotification() {
            self.notification.addClass("pgn-bar");
            self.alert.append("<span>" + self.options.message + "</span>");
            self.alert.addClass("alert-" + self.options.type);
            if (self.options.showClose) {
                var close = $(
                    '<button type="button" class="close" data-dismiss="alert"></button>'
                )
                    .append('<span aria-hidden="true">&times;</span>')
                    .append('<span class="sr-only">Close</span>');
                self.alert.prepend(close);
            }
        }

        function CircleNotification() {
            self.notification.addClass("pgn-circle");
            var table = "<div>";
            if (self.options.thumbnail) {
                table +=
                    '<div class="pgn-thumbnail"><div>' +
                    self.options.thumbnail +
                    "</div></div>";
            }
            table += '<div class="pgn-message"><div>';
            if (self.options.title) {
                table += '<p class="bold">' + self.options.title + "</p>";
            }
            table += "<p>" + self.options.message + "</p></div></div>";
            table += "</div>";
            if (self.options.showClose) {
                table +=
                    '<button type="button" class="close" data-dismiss="alert">';
                table +=
                    '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>';
                table += "</button>";
            }
            self.alert.append(table);
            self.alert.after('<div class="clearfix"></div>');
        }

        function FlipNotification() {
            self.notification.addClass("pgn-flip");
            self.alert.append("<span>" + self.options.message + "</span>");
            if (self.options.showClose) {
                var close = $(
                    '<button type="button" class="close" data-dismiss="alert"></button>'
                )
                    .append('<span aria-hidden="true">&times;</span>')
                    .append('<span class="sr-only">Close</span>');
                self.alert.prepend(close);
            }
        }
        self.notification.append(self.alert);
        self.alert.on("closed.bs.alert", function () {
            self.notification.remove();
            self.options.onClosed();
        });
        return this;
    };
    Notification.VERSION = "1.0.0";
    Notification.prototype.show = function () {
        this.wrapper.prepend(this.notification);
        this.options.onShown();
        if (this.options.timeout != 0) {
            var _this = this;
            setTimeout(
                function () {
                    this.notification.fadeOut("slow", function () {
                        $(this).remove();
                        _this.options.onClosed();
                    });
                }.bind(this),
                this.options.timeout
            );
        }
    };
    $.fn.pgNotification = function (options) {
        return new Notification(this, options);
    };
    $.fn.pgNotification.defaults = {
        style: "simple",
        message: null,
        position: "top-right",
        type: "info",
        showClose: true,
        timeout: 4000,
        onShown: function () {},
        onClosed: function () {},
    };
})(window.jQuery);

// Basic Needed Fancy Notifications

function show_success(title, data) {
    // console.log(title, data);
    $("body")
        .pgNotification({
            style: "circle",
            title: title,
            message: data.message,
            position: "top-right",
            timeout: 2000,
            type: "success",
            thumbnail:
                '<i class="fa fa-check" style="font-size:45px;background-color:#fff;color:#10cfbc;width:50px;height:50px;padding-left:1px;padding-top:2px;"></i>',
        })
        .show("1000");
    setTimeout(function () {}, 500);
}

function show_failure(title, data) {
    // console.log(title, data);
    if (typeof data.message === "undefined" || data.message === null) {
        if (
            typeof data.responseJSON === "undefined" &&
            typeof data.responseJSON.exception === "undefined" &&
            data.responseJSON.exception === null
        ) {
            data.message = "Unknown Error: Please check Server Error Logs";
        } else {
            data.message = data.responseJSON.exception;
        }
    }
    $("body")
        .pgNotification({
            style: "circle",
            title: title,
            message: data.message,
            position: "top-right",
            timeout: 0,
            type: "danger",
            thumbnail:
                '<i class="fa fa-ban" style="font-size:45px;background-color:#fff;color:#f55754;width:48px;height:48px;padding-left:5px;padding-top:1px;"></i>',
        })
        .show("1000");
    setTimeout(function () {}, 500);
}

/* ================= locationpicker ================= */
function initLP() {
    $(".location-select").each(function (index) {
        $(this)
            .children(".lp-map")
            .locationpicker({
                location: {
                    latitude: $(this).find(".lp-lat").val(),
                    longitude: $(this).find(".lp-lng").val(),
                },
                zoom: 6,
                scrollwheel: false,
                inputBinding: {
                    latitudeInput: $(this).find(".lp-lat"),
                    longitudeInput: $(this).find(".lp-lng"),
                    radiusInput: $(this).find(".lp-radius"),
                    locationNameInput: $(this).find(".lp-address"),
                },
                enableAutocomplete: true,
                enableAutocompleteBlur: false,
                onchanged: function (currentLocation, radius, isMarkerDropped) {
                    var lat = "" + currentLocation.latitude;
                    var lng = "" + currentLocation.longitude;
                    $(this)
                        .parent()
                        .find(".lp-latlng")
                        .val(lat.substring(0, 10) + "," + lng.substring(0, 10));
                },
            });
        $(this)
            .find(".lp-lat")
            .on("change", function () {
                $closest = $(this).closest(".location-select");
                var value =
                    $closest.find(".lp-lat").val().substring(0, 10) +
                    "," +
                    $closest.find(".lp-lng").val().substring(0, 10);
                $(this)
                    .closest(".location-select")
                    .find(".lp-latlng")
                    .val(value);
            });
        $(this)
            .find(".lp-lng")
            .on("change", function () {
                $closest = $(this).closest(".location-select");
                var value =
                    $closest.find(".lp-lat").val().substring(0, 10) +
                    "," +
                    $closest.find(".lp-lng").val().substring(0, 10);
                $(this)
                    .closest(".location-select")
                    .find(".lp-latlng")
                    .val(value);
            });
    });
}
/* ================= Quick Add ================= */
var popup_field_name = 0;
var popup_module_id = 0;
var popup_vals = "";

function initQuickAdd() {
    $(".btn_quick_add").on("click", function () {
        popup_field_name = $(this).attr("field_name");
        popup_vals = $(this).attr("popup_vals");
        popup_module_id = $(this).attr("popup_module_id");

        if (popup_module_id != 0) {
            $.ajax({
                url:
                    bsurl +
                    "/" +
                    adminRoute +
                    "/quick_add_form/" +
                    popup_module_id,
                method: "GET",
                data: {
                    _token: _token,
                    field_name: popup_field_name,
                    popup_vals: popup_vals,
                },
                success: function (data) {
                    $("#modal_content").html(data);
                    $("#quick_add").modal("show");
                },
            });
        }
    });
}

function iniCheckListUpdate() {
    $(".value_checklist input").on("click", function () {
        list_title = $(this).attr("title");
        module_id = $(this).attr("module_id");
        row_id = $(this).attr("row_id");
        module_field_id = $(this).attr("module_field_id");

        if ($(this).is(":checked")) {
            list_vals = true;
        } else {
            list_vals = false;
        }

        $.ajax({
            url: bsurl + "/" + adminRoute + "/checklist_update",
            method: "POST",
            data: {
                _token: _token,
                list_title: list_title,
                list_vals: list_vals,
                module_id: module_id,
                row_id: row_id,
                module_field_id: module_field_id,
            },
            success: function (data) {
                console.log(data);
            },
        });
    });
}

function compareDates(d1, d2) {
    dateFirst = d1.split("/");
    dateSecond = d2.split("/");
    var date1 = new Date(
        parseInt(dateFirst[2]),
        parseInt(dateFirst[1]) - 1,
        parseInt(dateFirst[0])
    );
    var date2 = new Date(
        parseInt(dateSecond[2]),
        parseInt(dateSecond[1]) - 1,
        parseInt(dateSecond[0])
    );
    return date1 >= date2;
}

function dynalist($list) {
    $listElem = $list.prev();
    var arr = JSON.parse($listElem.val());
    $list.children().html("");
    if (arr.length > 0) {
        for (var index = 0; index < arr.length; index++) {
            var element = arr[index];
            $list.children().append("<li>" + element + "</li>");
        }
    } else {
        $list.children().append("<li><br></li>");
    }
}

/* ================= checklist ================= */
function checklist() {
    $("body").on(
        "click",
        ".checklist .todo-list li .value_checklist .display",
        function () {
            $(this)
                .hide()
                .siblings(".checklist .todo-list li .value_checklist .edit")
                .show()
                .val($(this).text())
                .focus();
        }
    );

    $("body").on(
        "focusout",
        ".checklist .todo-list li .value_checklist .edit",
        function () {
            var field_name = $(this)
                .parents(".checklist")
                .find("input")
                .first()
                .attr("name");
            $(this)
                .hide()
                .siblings(".checklist .todo-list li .value_checklist .display")
                .show()
                .text($(this).val());
            btn_checklist_arr(field_name);
            save_checklist_by_view(field_name);
        }
    );

    $(".btn-checklist").on("click", function () {
        var field_name = $(this)
            .parents(".checklist")
            .find("input")
            .first()
            .attr("name");
        var count_list_max = $(this).attr("data-rule-maxcount");
        var count_list_min = $(this).attr("data-rule-mincount");
        var real_count = $(
            'input[name="' + field_name + '"] + > ul.todo-list li'
        ).length;
        if (isset(count_list_max)) {
            if (parseInt(count_list_max) > parseInt(real_count)) {
                $('input[name="' + field_name + '"] + > ul').append(
                    "<li>" +
                        '<span class="handle"><i class="fa fa-ellipsis-v" style="margin-right:3px"></i><i class="fa fa-ellipsis-v"></i></span>' +
                        '<span class="value_checklist">' +
                        '<input type="checkbox" value="false" name="checked" style="position:relative;top:2px;margin:0 2px">' +
                        '<span style="display:inline-block;"><span class="text display checklist_title" style="vertical-align:middle;">Untitled</span>' +
                        '<input type="text" class="edit form-control" style="display:none"/></span>' +
                        "</span>" +
                        '<div class="tools">' +
                        '<i class="fa fa-trash-o btn_checklist_remove"></i>' +
                        "</div>" +
                        "</li>"
                );
            }
        } else {
            $('input[name="' + field_name + '"] + > ul').append(
                "<li>" +
                    '<span class="handle"><i class="fa fa-ellipsis-v" style="margin-right:3px"></i><i class="fa fa-ellipsis-v"></i></span>' +
                    '<span class="value_checklist">' +
                    '<input type="checkbox" value="false" name="checked" style="position:relative;top:2px;margin:0 2px">' +
                    '<span style="display:inline-block;"><span class="text display checklist_title" style="vertical-align:middle;">Untitled</span>' +
                    '<input type="text" class="edit form-control" style="display:none"/></span>' +
                    "</span>" +
                    '<div class="tools">' +
                    '<i class="fa fa-trash-o btn_checklist_remove"></i>' +
                    "</div>" +
                    "</li>"
            );
        }
        var real_count = $(
            'input[name="' + field_name + '"] + > ul.todo-list li'
        ).length;
        if (
            isset(count_list_max) &&
            parseInt(count_list_max) == parseInt(real_count)
        ) {
            $(this).addClass("disabled");
        }
        if (isset(count_list_min)) {
            if (parseInt(count_list_min) >= parseInt(real_count)) {
                $(
                    'input[name="' +
                        field_name +
                        '"] + > ul.todo-list li .btn_checklist_remove'
                ).remove();
            } else {
                $(
                    'input[name="' +
                        field_name +
                        '"] + > ul.todo-list li .tools'
                ).html('<i class="fa fa-trash-o btn_checklist_remove"></i>');
            }
        }
    });

    $("body").on(
        "change",
        '.checklist .todo-list li input[name="checked"]',
        function () {
            var field_name = $(this)
                .parents(".checklist")
                .find("input")
                .first()
                .attr("name");
            if (this.checked) {
                $(this).val("true");
                $(this).closest("li").addClass("done");
            } else {
                $(this).val("false");
                $(this).closest("li").removeClass("done");
            }
            btn_checklist_arr(field_name);
            save_checklist_by_view(field_name);
        }
    );

    $("body").on(
        "click",
        ".checklist .todo-list li .btn_checklist_remove",
        function () {
            var field_name = $(this)
                .parents(".checklist")
                .find("input")
                .first()
                .attr("name");
            var count_list_min = $(this)
                .parents(".checklist")
                .find(".btn-checklist")
                .attr("data-rule-mincount");
            var count_list_max = $(this)
                .parents(".checklist")
                .find(".btn-checklist")
                .attr("data-rule-maxcount");
            var real_count = $(
                'input[name="' + field_name + '"] + > ul.todo-list li'
            ).length;
            if (isset(count_list_min)) {
                if (parseInt(count_list_min) < parseInt(real_count)) {
                    $(this).closest("li").remove();
                }
            } else {
                $(this).closest("li").remove();
            }

            if (
                isset(count_list_max) &&
                parseInt(count_list_max) <= parseInt(real_count)
            ) {
                $(".btn-checklist").removeClass("disabled");
            }

            var real_count = $(
                'input[name="' + field_name + '"] + > ul.todo-list li'
            ).length;
            if (isset(count_list_min)) {
                if (parseInt(count_list_min) >= parseInt(real_count)) {
                    $(
                        'input[name="' +
                            field_name +
                            '"] + > ul.todo-list li .btn_checklist_remove'
                    ).remove();
                } else {
                    $(
                        'input[name="' +
                            field_name +
                            '"] + > ul.todo-list li .tools'
                    ).html(
                        '<i class="fa fa-trash-o btn_checklist_remove"></i>'
                    );
                }
            }

            btn_checklist_arr(field_name);
            save_checklist_by_view(field_name);
        }
    );
}

function btn_checklist_arr(field_name) {
    var arr = [];

    var element = $(
        'input[name="' + field_name + '"] + > ul.todo-list li .value_checklist'
    );
    $.each(element, function (kay, value) {
        var object = {};
        if ($(this).find("span.checklist_title").text() != "Untitled") {
            object.checked = $(this).find("input").val();
            object.title = $(this).find("span.checklist_title").text();
            arr.push(object);
        }
    });
    $('.checklist input[name="' + field_name + '"]').val(JSON.stringify(arr));
}

function save_checklist_by_view(field_name) {
    var value = $(".checklist input[name='" + field_name + "']").val();
    var module_name_db = $(".checklist input[name='" + field_name + "']").attr(
        "module_name_db"
    );
    var row_id = $(".checklist input[name='" + field_name + "']").attr(
        "row_id"
    );
    var object = {};
    if (
        isset(module_name_db) &&
        module_name_db != "" &&
        isset(row_id) &&
        row_id != "" &&
        row_id != 0 &&
        row_id != "0"
    ) {
        object[field_name] = value;

        var data = JSON.stringify(object);

        // console.log("value = "+value+" module_name_db = "+module_name_db+" field_name = "+field_name+ " row_id = "+row_id);
        $.ajax({
            url: bsurl + "/" + adminRoute + "/" + module_name_db + "/" + row_id,
            method: "PUT",
            data: data,
            headers: {
                "X-CSRF-TOKEN": $("input[name=_token]").val(),
            },
            success: function (data) {
                console.log(data);
                $("body")
                    .pgNotification({
                        style: "circle",
                        title: "Checklist",
                        message: "Updated",
                        position: "top-right",
                        timeout: 0,
                        type: "success",
                        thumbnail:
                            '<i class="fa fa-check" style="font-size: 50px;background-color: #fff;color: #10cfbc;"></i>',
                    })
                    .show("1000");
                setTimeout(function () {}, 500);
            },
        });
    }
}

function refreshForm() {
    _token = $("input[name=_token]").val();

    /* ================= Toggle Switch - Checkbox ================= */
    $(".Switch:not(.Ajax)").click(function () {
        $(this).hasClass("On")
            ? ($(this).parent().find("input:checkbox").attr("checked", !0),
              $(this).removeClass("On").addClass("Off"))
            : ($(this).parent().find("input:checkbox").attr("checked", !1),
              $(this).removeClass("Off").addClass("On"));
    }),
        $(".Switch:not(.Ajax)").each(function () {
            $(this).parent().find("input:checkbox").length &&
                ($(this).parent().find("input:checkbox").hasClass("show") ||
                    $(this).parent().find("input:checkbox").hide(),
                $(this).parent().find("input:checkbox").is(":checked")
                    ? $(this).removeClass("On").addClass("Off")
                    : $(this).removeClass("Off").addClass("On"));
        });

    /* ================= HTML ================= */
    $(".htmlbox").each(function (index, elem) {
        $("#" + elem.id).summernote();
    });

    // $('#summernote_desc').summernote('insertImage', "http://localhost/laplus/public/files/jqft9yw3pqg0mquou2rz/laracabs.jpg?s=200", "test.jpg");

    /* ================= Default Select2 ================= */
    $("[rel=select2]").select2({});
    $("[rel=taginput]").select2({
        tags: true,
        tokenSeparators: [","],
    });

    // Null Value for Dropdown
    $(".null_dd").on("click", function (event) {
        var cb = $(this).find(".cb_null_dd");
        if (cb.is(":checked")) {
            cb.prop("checked", !1);
            cb.attr("checked", !1);
            $(this).parent().prev().find("select").select2("enable");
        } else {
            cb.prop("checked", !0);
            cb.attr("checked", !0);
            $(this).parent().prev().find("select").select2("enable", false);
        }
    });

    /* ================= dynalist ================= */

    $(".dynalist ul").keydown(function (e) {
        if (e.keyCode == 8) {
            if (
                $(this).children().length == 1 &&
                $(this).html() == "<li><br></li>"
            ) {
                e.preventDefault();
            }
        }
    });

    $(".dynalist ul").bind("paste", function (e) {
        e.preventDefault();
        if (e.originalEvent.clipboardData) {
            var text = e.originalEvent.clipboardData.getData("text/plain");
            text = text.split("\n");
            for (var i = 0; i < text.length; i++) {
                var line = text[i].trim();
                $(this).append("<li>" + line + "</li>");
            }
        }
    });

    $(".dynalist").each(function (index, $list) {
        dynalist($(this));
    });

    $("form").submit(function (e) {
        $(this)
            .find(".dynalist")
            .each(function (index, $list) {
                $list = $(this);
                $listElem = $list.prev();

                // Show error if out of count
                var field_name = $list.attr("id");
                var label = $list.attr("label");
                var min = $list.attr("data-rule-mincount");
                var max = $list.attr("data-rule-maxcount");

                if ($list.children().children().length < min) {
                    // alert(label + " : Please add atleast "+min+" list items");
                    if ($("#" + field_name + "-error").length > 0) {
                        $("#" + field_name + "-error").html(
                            "Please add atleast " + min + " list items."
                        );
                        $("#" + field_name + "-error").show();
                    } else {
                        $(this)
                            .parent()
                            .append(
                                '<label id="' +
                                    field_name +
                                    '-error" class="dlerror" for="' +
                                    field_name +
                                    '">Please add atleast ' +
                                    min +
                                    " list items.</label>"
                            );
                    }
                    e.preventDefault();
                    return true;
                } else if ($list.children().children().length > max) {
                    // alert(label + " : Please add only "+max+" list items");
                    if ($("#" + field_name + "-error").length > 0) {
                        $("#" + field_name + "-error").html(
                            "Please add only " + max + " list items."
                        );
                        $("#" + field_name + "-error").show();
                    } else {
                        $(this)
                            .parent()
                            .append(
                                '<label id="' +
                                    field_name +
                                    '-error" class="dlerror" for="' +
                                    field_name +
                                    '">Please add only ' +
                                    max +
                                    " list items.</label>"
                            );
                    }
                    e.preventDefault();
                    return true;
                } else {
                    $("#" + field_name + "-error").hide();
                }

                var arr = [];
                $list
                    .children()
                    .children()
                    .each(function (index, elem) {
                        arr.push(elem.innerText.trim());
                    });
                $listElem.val(JSON.stringify(arr));
            });
        return true;
    });

    /* ================= bootstrap-datetimepicker ================= */
    $(".input-group.date").datetimepicker({
        format: "DD/MM/YYYY",
    });

    $(".input-group.datetime").datetimepicker({
        format: "DD/MM/YYYY LT",
        sideBySide: true,
    });

    // Null Value for Date + Datetime
    $(".input-group-addon.null_date").on("click", function (event) {
        var cb = $(this).find(".cb_null_date");
        if (cb.is(":checked")) {
            cb.prop("checked", !0);
            cb.attr("checked", !0);
            $(this).parent().find('input[type="text"]').prop("readonly", !0);
        } else {
            cb.prop("checked", !1);
            cb.attr("checked", !1);
            $(this).parent().find('input[type="text"]').prop("readonly", !1);
        }
    });

    /* ================= duration ================= */
    $(".input-group.duration input").bind("input", function () {
        var field_name = $(this)
            .parents(".duration")
            .find("input")
            .first()
            .attr("name");
        var day = $(
            ".input-group.duration>input[name='" + field_name + "_days']"
        ).val();
        var hours = $(
            ".input-group.duration>input[name='" + field_name + "_hours']"
        ).val();
        var minute = $(
            ".input-group.duration>input[name='" + field_name + "_minutes']"
        ).val();

        if (isset(day)) {
            var day_min = parseInt(day) * 1440;
            var hours_min = parseInt(hours) * 60;
            var total_min =
                parseInt(day_min) + parseInt(hours_min) + parseInt(minute);
        } else {
            var hours_min = parseInt(hours) * 60;
            var total_min = parseInt(hours_min) + parseInt(minute);
        }

        $(".input-group.duration")
            .find('input[name="' + field_name + '"]')
            .filter(":first")
            .val(total_min);
    });

    /* ================= checklist ================= */
    checklist();

    /* ================= colorpicker ================= */

    $(".colorpicker")
        .colorpicker()
        .on("create", function (e) {
            if (e.currentTarget.value != "") {
                e.currentTarget.style.backgroundColor = e.currentTarget.value;
            }
        })
        .on("changeColor", function (e) {
            e.currentTarget.style.backgroundColor = e.color.toString("rgba");
        });

    /* ================= timepicker ================= */
    $("input.timepicker").timepicker({
        minuteStep: 15,
        showInpunts: false,
    });

    /* ================= locationpicker ================= */
    $("#AddModal").on("shown.bs.modal", function () {
        initLP();
    });

    initLP();

    /* ================= locationpicker ================= */
    initQuickAdd();

    /* ================= List update ================= */
    iniCheckListUpdate();

    /* ================= stickyTabs ================= */
    $(".nav-tabs").stickyTabs({
        selectorAttribute: "data-target",
        backToTop: true,
    });

    var activeTab = window.location.href.substring(
        window.location.href.indexOf("#") + 1
    );
    if (activeTab.length > 1 && !activeTab.includes("http")) {
        $('.nav-tabs a[href="#' + activeTab + '"]').tab("show");
    }

    if (typeof jQuery.validator !== "undefined") {
        /* ================= Validate Unique Fields ================= */

        jQuery.validator.addMethod(
            "data-rule-unique",
            function (value, element) {
                value = value.trim();

                var isAllowed = false;
                var field_id = element.getAttribute("field_id");
                var _token = $("input[name=_token_" + field_id + "]").val();
                var isEdit = element.getAttribute("isEdit");
                var row_id = element.getAttribute("row_id");

                if (value != "" && bsurl != "") {
                    $.ajax({
                        url:
                            bsurl +
                            "/" +
                            adminRoute +
                            "/check_unique_val/" +
                            field_id,
                        type: "POST",
                        async: false,
                        data: {
                            field_value: value,
                            _token: _token,
                            isEdit: isEdit,
                            row_id: row_id,
                        },
                        success: function (data) {
                            // console.log(data);
                            if (data.exists == true) {
                                isAllowed = false;
                            } else {
                                isAllowed = true;
                            }
                        },
                    });
                }
                return isAllowed;
            },
            "This value exists in database."
        );

        /* ================= Validate Min Date ================= */

        jQuery.validator.addMethod(
            "data-rule-mindate",
            function (value, element, param) {
                value = value.trim();
                return compareDates(
                    value,
                    element.getAttribute("data-rule-mindate")
                );
            },
            "Enter valid date"
        );

        jQuery.validator.setDefaults({
            ignore: ":hidden, .note-editable.panel-body, .dynalist ul",
        });
    }

    /* ================= Init File Manager ================= */
    $(".btn_upload_image").on("click", function () {
        showLAFM("image", $(this).attr("selecter"));
    });

    $(".btn_upload_summernote").on("click", function () {
        var sn_id = $(this)
            .parent()
            .parent()
            .parent()
            .prevAll(".htmlbox")
            .attr("id");
        showLAFM("summernote_image", sn_id);
    });

    $(".btn_upload_file").on("click", function () {
        showLAFM("file", $(this).attr("selecter"));
    });

    $(".btn_upload_files").on("click", function () {
        showLAFM("files", $(this).attr("selecter"));
    });

    $(".uploaded_image i.fa.fa-times").on("click", function () {
        $(this).parent().children("img").attr("src", "");
        $(this).parent().addClass("hide");
        $(this).parent().prev().removeClass("hide");
        $(this).parent().prev().prev().val("0");
    });

    $(".uploaded_file i.fa.fa-times").on("click", function (e) {
        $(this).parent().attr("href", "");
        $(this).parent().addClass("hide");
        $(this).parent().prev().removeClass("hide");
        $(this).parent().prev().prev().val("0");
        e.preventDefault();
    });

    $(".uploaded_file2 i.fa.fa-times").on("click", function (e) {
        var upload_id = $(this).parent().attr("upload_id");
        var $hiddenFIDs = $(this).parent().parent().prev();

        var hiddenFIDs = JSON.parse($hiddenFIDs.val());
        var hiddenFIDs2 = [];
        for (var key in hiddenFIDs) {
            if (hiddenFIDs.hasOwnProperty(key)) {
                var element = hiddenFIDs[key];
                if (element != upload_id) {
                    hiddenFIDs2.push(element);
                }
            }
        }
        $hiddenFIDs.val(JSON.stringify(hiddenFIDs2));
        $(this).parent().remove();
        e.preventDefault();
    });
}

// delete confirmation prompt
$("body").on("submit", "form", function () {
    if ($(this).find('input[name="_method"]').val() == "DELETE") {
        if (confirm("Are you sure you want to delete this?")) {
            return true;
        } else {
            return false;
        }
    }
});

/* ================= File Manager ================= */
var cntFiles = null;
var fm_dropzone = null;

function showLAFM(type, selector) {
    $("#image_selecter_origin_type").val(type);
    $("#image_selecter_origin").val(selector);

    $("#fm").modal("show");

    loadFMFiles();
}
function getLI(upload) {
    var image = "";
    if (
        $.inArray(upload.extension, ["jpg", "jpeg", "png", "gif", "bmp"]) > -1
    ) {
        image =
            '<img src="' +
            bsurl +
            "/files/" +
            upload.hash +
            "/" +
            upload.name +
            '?s=130">';
    } else {
        switch (upload.extension) {
            case "pdf":
                image = '<i class="fa fa-file-pdf-o"></i>';
                break;
            default:
                image = '<i class="fa fa-file-text-o"></i>';
                break;
        }
    }
    return (
        '<li><a class="fm_file_sel" data-toggle="tooltip" data-placement="top" title="' +
        upload.name +
        "\" upload='" +
        JSON.stringify(upload) +
        "'>" +
        image +
        "</a></li>"
    );
}
function loadFMFiles() {
    // load uploaded files
    $.ajax({
        dataType: "json",
        url: bsurl + "/" + adminRoute + "/uploaded_files",
        success: function (json) {
            console.log(json);
            cntFiles = json.uploads;
            $(".fm_file_selector ul").empty();
            if (cntFiles.length) {
                for (var index = 0; index < cntFiles.length; index++) {
                    var element = cntFiles[index];
                    var li = getLI(element);
                    $(".fm_file_selector ul").append(li);
                }
            } else {
                $(".fm_file_selector ul").html(
                    "<div class='text-center text-danger' style='margin-top:40px;'>No Files</div>"
                );
            }
        },
    });
}

function initFM() {
    // console.log("initFM");

    // $(".input-group.file input").on("blur", function() {
    //     if($(this).val().startsWith("http")) {
    //         $(this).next(".preview").css({
    //             "display": "block",
    //             "background-image": "url('"+$(this).val()+"')",
    //             "background-size": "cover"
    //         });
    //     } else {
    //         $(this).next(".preview").css({
    //             "display": "block",
    //             "background-image": "url('"+bsurl+"/"+$(this).val()+"')",
    //             "background-size": "cover"
    //         });
    //     }
    // });
    $("#fm input[type=search]").keyup(function () {
        var sstring = $(this).val().trim();
        console.log(sstring);
        if (sstring != "") {
            $(".fm_file_selector ul").empty();
            for (var index = 0; index < cntFiles.length; index++) {
                var upload = cntFiles[index];
                if (upload.name.toUpperCase().includes(sstring.toUpperCase())) {
                    $(".fm_file_selector ul").append(getLI(upload));
                }
            }
        } else {
            loadFMFiles();
        }
    });

    fm_dropzone = new Dropzone("#fm_dropzone", {
        maxFilesize: 2,
        acceptedFiles: "image/*,application/pdf",
        init: function () {
            this.on("complete", function (file) {
                this.removeFile(file);
            });
            this.on("success", function (file) {
                console.log("addedfile");
                console.log(file);
                loadFMFiles();
            });
        },
    });

    $("body").on("click", ".fm_file_sel", function () {
        type = $("#image_selecter_origin_type").val();
        upload = JSON.parse($(this).attr("upload"));
        console.log("upload sel: " + upload + " type: " + type);
        if (type == "image") {
            $hinput = $(
                "input[name=" + $("#image_selecter_origin").val() + "]"
            );
            $hinput.val(upload.id);

            $hinput.next("a").addClass("hide");
            $hinput.next("a").next(".uploaded_image").removeClass("hide");
            $hinput
                .next("a")
                .next(".uploaded_image")
                .children("img")
                .attr(
                    "src",
                    bsurl +
                        "/files/" +
                        upload.hash +
                        "/" +
                        upload.name +
                        "?s=150"
                );
        } else if (type == "summernote_image") {
            var img_url = bsurl + "/files/" + upload.hash + "/" + upload.name;
            $("#" + $("#image_selecter_origin").val()).summernote(
                "insertImage",
                img_url,
                upload.name
            );
        } else if (type == "file") {
            $hinput = $(
                "input[name=" + $("#image_selecter_origin").val() + "]"
            );
            $hinput.val(upload.id);

            $hinput.next("a").addClass("hide");
            $hinput.next("a").next(".uploaded_file").removeClass("hide");
            $hinput
                .next("a")
                .next(".uploaded_file")
                .attr(
                    "href",
                    bsurl + "/files/" + upload.hash + "/" + upload.name
                );
        } else if (type == "files") {
            $hinput = $(
                "input[name=" + $("#image_selecter_origin").val() + "]"
            );

            var hiddenFIDs = JSON.parse($hinput.val());
            // check if upload_id exists in array
            var upload_id_exists = false;
            for (var key in hiddenFIDs) {
                if (hiddenFIDs.hasOwnProperty(key)) {
                    var element = hiddenFIDs[key];
                    if (element == upload.id) {
                        upload_id_exists = true;
                    }
                }
            }
            if (!upload_id_exists) {
                hiddenFIDs.push(upload.id);
            }
            $hinput.val(JSON.stringify(hiddenFIDs));
            var fileImage = "";
            if (
                upload.extension == "jpg" ||
                upload.extension == "png" ||
                upload.extension == "gif" ||
                upload.extension == "jpeg"
            ) {
                fileImage =
                    "<img src='" +
                    bsurl +
                    "/files/" +
                    upload.hash +
                    "/" +
                    upload.name +
                    "?s=90'>";
            } else {
                fileImage = "<i class='fa fa-file-o'></i>";
            }
            $hinput
                .next("div.uploaded_files")
                .append(
                    "<a class='uploaded_file2' upload_id='" +
                        upload.id +
                        "' target='_blank' href='" +
                        bsurl +
                        "/files/" +
                        upload.hash +
                        "/" +
                        upload.name +
                        "'>" +
                        fileImage +
                        "<i title='Remove File' class='fa fa-times'></i></a>"
                );
        }
        $("#fm").modal("hide");
    });
}

$(document).ready(function () {
    refreshForm();

    if (typeof Dropzone !== "undefined") {
        initFM();
    } else {
        console.log("Dropzone is not defined");
    }
});
/*! LaraAdmin app.js
 * ================
 * Main JS application file for LaraAdmin v1. This file
 * should be included in all pages. It controls some layout
 * options and implements exclusive LaraAdmin plugins.
 *
 * @Author  Ganesh Bhosale
 * @Email   <hello@laraadmin.com>
 * @version 1.0
 * @Website: LaraAdmin <https://laraadmin.com>
 * @License: MIT
 * Please visit https://laraadmin.com/license for more information
 */

//Make sure jQuery has been loaded before app.js
if (typeof jQuery === "undefined") {
    throw new Error("LaraAdmin requires jQuery");
}

/* AdminLTE
 *
 * @type Object
 * @description $.AdminLTE is the main object for the template's app.
 *              It's used for implementing functions and options related
 *              to the template. Keeping everything wrapped in an object
 *              prevents conflict with other plugins and is a better
 *              way to organize our code.
 */
$.AdminLTE = {};

/* --------------------
 * - AdminLTE Options -
 * --------------------
 * Modify these options to suit your implementation
 */
$.AdminLTE.options = {
    //Add slimscroll to navbar menus
    //This requires you to load the slimscroll plugin
    //in every page before app.js
    navbarMenuSlimscroll: true,
    navbarMenuSlimscrollWidth: "3px", //The width of the scroll bar
    navbarMenuHeight: "200px", //The height of the inner menu
    //General animation speed for JS animated elements such as box collapse/expand and
    //sidebar treeview slide up/down. This options accepts an integer as milliseconds,
    //'fast', 'normal', or 'slow'
    animationSpeed: 500,
    //Sidebar push menu toggle button selector
    sidebarToggleSelector: "[data-toggle='offcanvas']",
    //Activate sidebar push menu
    sidebarPushMenu: true,
    //Activate sidebar slimscroll if the fixed layout is set (requires SlimScroll Plugin)
    sidebarSlimScroll: true,
    //Enable sidebar expand on hover effect for sidebar mini
    //This option is forced to true if both the fixed layout and sidebar mini
    //are used together
    sidebarExpandOnHover: false,
    //BoxRefresh Plugin
    enableBoxRefresh: true,
    //Bootstrap.js tooltip
    enableBSToppltip: true,
    BSTooltipSelector: "[data-toggle='tooltip']",
    //Enable Fast Click. Fastclick.js creates a more
    //native touch experience with touch devices. If you
    //choose to enable the plugin, make sure you load the script
    //before AdminLTE's app.js
    enableFastclick: false,
    //Control Sidebar Options
    enableControlSidebar: true,
    controlSidebarOptions: {
        //Which button should trigger the open/close event
        toggleBtnSelector: "[data-toggle='control-sidebar']",
        //The sidebar selector
        selector: ".control-sidebar",
        //Enable slide over content
        slide: true,
    },
    //Box Widget Plugin. Enable this plugin
    //to allow boxes to be collapsed and/or removed
    enableBoxWidget: true,
    //Box Widget plugin options
    boxWidgetOptions: {
        boxWidgetIcons: {
            //Collapse icon
            collapse: "fa-minus",
            //Open icon
            open: "fa-plus",
            //Remove icon
            remove: "fa-times",
        },
        boxWidgetSelectors: {
            //Remove button selector
            remove: '[data-widget="remove"]',
            //Collapse button selector
            collapse: '[data-widget="collapse"]',
        },
    },
    //Direct Chat plugin options
    directChat: {
        //Enable direct chat by default
        enable: true,
        //The button to open and close the chat contacts pane
        contactToggleSelector: '[data-widget="chat-pane-toggle"]',
    },
    //Define the set of colors to use globally around the website
    colors: {
        lightBlue: "#3c8dbc",
        red: "#f56954",
        green: "#00a65a",
        aqua: "#00c0ef",
        yellow: "#f39c12",
        blue: "#0073b7",
        navy: "#001F3F",
        teal: "#39CCCC",
        olive: "#3D9970",
        lime: "#01FF70",
        orange: "#FF851B",
        fuchsia: "#F012BE",
        purple: "#8E24AA",
        maroon: "#D81B60",
        black: "#222222",
        gray: "#d2d6de",
    },
    //The standard screen sizes that bootstrap uses.
    //If you change these in the variables.less file, change
    //them here too.
    screenSizes: {
        xs: 480,
        sm: 768,
        md: 992,
        lg: 1200,
    },
};

/* ------------------
 * - Implementation -
 * ------------------
 * The next block of code implements AdminLTE's
 * functions and plugins as specified by the
 * options above.
 */
$(function () {
    "use strict";

    //Fix for IE page transitions
    $("body").removeClass("hold-transition");

    //Extend options if external options exist
    if (typeof AdminLTEOptions !== "undefined") {
        $.extend(true, $.AdminLTE.options, AdminLTEOptions);
    }

    //Easy access to options
    var o = $.AdminLTE.options;

    //Set up the object
    _init();

    //Activate the layout maker
    $.AdminLTE.layout.activate();

    //Enable sidebar tree view controls
    $.AdminLTE.tree(".sidebar");

    //Enable control sidebar
    if (o.enableControlSidebar) {
        $.AdminLTE.controlSidebar.activate();
    }

    //Add slimscroll to navbar dropdown
    if (o.navbarMenuSlimscroll && typeof $.fn.slimscroll != "undefined") {
        $(".navbar .menu")
            .slimscroll({
                height: o.navbarMenuHeight,
                alwaysVisible: false,
                size: o.navbarMenuSlimscrollWidth,
            })
            .css("width", "100%");
    }

    //Activate sidebar push menu
    if (o.sidebarPushMenu) {
        $.AdminLTE.pushMenu.activate(o.sidebarToggleSelector);
    }

    //Activate Bootstrap tooltip
    if (o.enableBSToppltip) {
        $("body").tooltip({
            selector: o.BSTooltipSelector,
        });
    }

    //Activate box widget
    if (o.enableBoxWidget) {
        $.AdminLTE.boxWidget.activate();
    }

    //Activate fast click
    if (o.enableFastclick && typeof FastClick != "undefined") {
        FastClick.attach(document.body);
    }

    //Activate direct chat widget
    if (o.directChat.enable) {
        $(document).on(
            "click",
            o.directChat.contactToggleSelector,
            function () {
                var box = $(this).parents(".direct-chat").first();
                box.toggleClass("direct-chat-contacts-open");
            }
        );
    }

    /*
     * INITIALIZE BUTTON TOGGLE
     * ------------------------
     */
    $('.btn-group[data-toggle="btn-toggle"]').each(function () {
        var group = $(this);
        $(this)
            .find(".btn")
            .on("click", function (e) {
                group.find(".btn.active").removeClass("active");
                $(this).addClass("active");
                e.preventDefault();
            });
    });
});

/* ----------------------------------
 * - Initialize the AdminLTE Object -
 * ----------------------------------
 * All AdminLTE functions are implemented below.
 */
function _init() {
    "use strict";
    /* Layout
     * ======
     * Fixes the layout height in case min-height fails.
     *
     * @type Object
     * @usage $.AdminLTE.layout.activate()
     *        $.AdminLTE.layout.fix()
     *        $.AdminLTE.layout.fixSidebar()
     */
    $.AdminLTE.layout = {
        activate: function () {
            var _this = this;
            _this.fix();
            _this.fixSidebar();
            $(window, ".wrapper").resize(function () {
                _this.fix();
                _this.fixSidebar();
            });
        },
        fix: function () {
            //Get window height and the wrapper height
            var neg =
                $(".main-header").outerHeight() +
                $(".main-footer").outerHeight();
            var window_height = $(window).height();
            var sidebar_height = $(".sidebar").height();
            //Set the min-height of the content and sidebar based on the
            //the height of the document.
            if ($("body").hasClass("fixed")) {
                $(".content-wrapper, .right-side").css(
                    "min-height",
                    window_height - $(".main-footer").outerHeight()
                );
            } else {
                var postSetWidth;
                if (window_height >= sidebar_height) {
                    $(".content-wrapper, .right-side").css(
                        "min-height",
                        window_height - neg
                    );
                    postSetWidth = window_height - neg;
                } else {
                    $(".content-wrapper, .right-side").css(
                        "min-height",
                        sidebar_height
                    );
                    postSetWidth = sidebar_height;
                }

                //Fix for the control sidebar height
                var controlSidebar = $(
                    $.AdminLTE.options.controlSidebarOptions.selector
                );
                if (typeof controlSidebar !== "undefined") {
                    if (controlSidebar.height() > postSetWidth)
                        $(".content-wrapper, .right-side").css(
                            "min-height",
                            controlSidebar.height()
                        );
                }
            }
        },
        fixSidebar: function () {
            //Make sure the body tag has the .fixed class
            if (!$("body").hasClass("fixed")) {
                if (typeof $.fn.slimScroll != "undefined") {
                    $(".sidebar").slimScroll({ destroy: true }).height("auto");
                }
                return;
            } else if (
                typeof $.fn.slimScroll == "undefined" &&
                window.console
            ) {
                window.console.error(
                    "Error: the fixed layout requires the slimscroll plugin!"
                );
            }
            //Enable slimscroll for fixed layout
            if ($.AdminLTE.options.sidebarSlimScroll) {
                if (typeof $.fn.slimScroll != "undefined") {
                    //Destroy if it exists
                    $(".sidebar").slimScroll({ destroy: true }).height("auto");
                    //Add slimscroll
                    $(".sidebar").slimscroll({
                        height:
                            $(window).height() -
                            $(".main-header").height() +
                            "px",
                        color: "rgba(0,0,0,0.2)",
                        size: "3px",
                    });
                }
            }
        },
    };

    /* PushMenu()
     * ==========
     * Adds the push menu functionality to the sidebar.
     *
     * @type Function
     * @usage: $.AdminLTE.pushMenu("[data-toggle='offcanvas']")
     */
    $.AdminLTE.pushMenu = {
        activate: function (toggleBtn) {
            //Get the screen sizes
            var screenSizes = $.AdminLTE.options.screenSizes;

            //Enable sidebar toggle
            $(document).on("click", toggleBtn, function (e) {
                e.preventDefault();

                //Enable sidebar push menu
                if ($(window).width() > screenSizes.sm - 1) {
                    if ($("body").hasClass("sidebar-collapse")) {
                        $("body")
                            .removeClass("sidebar-collapse")
                            .trigger("expanded.pushMenu");
                    } else {
                        $("body")
                            .addClass("sidebar-collapse")
                            .trigger("collapsed.pushMenu");
                    }
                }
                //Handle sidebar push menu for small screens
                else {
                    if ($("body").hasClass("sidebar-open")) {
                        $("body")
                            .removeClass("sidebar-open")
                            .removeClass("sidebar-collapse")
                            .trigger("collapsed.pushMenu");
                    } else {
                        $("body")
                            .addClass("sidebar-open")
                            .trigger("expanded.pushMenu");
                    }
                }
            });

            $(".content-wrapper").click(function () {
                //Enable hide menu when clicking on the content-wrapper on small screens
                if (
                    $(window).width() <= screenSizes.sm - 1 &&
                    $("body").hasClass("sidebar-open")
                ) {
                    $("body").removeClass("sidebar-open");
                }
            });

            //Enable expand on hover for sidebar mini
            if (
                $.AdminLTE.options.sidebarExpandOnHover ||
                ($("body").hasClass("fixed") &&
                    $("body").hasClass("sidebar-mini"))
            ) {
                this.expandOnHover();
            }
        },
        expandOnHover: function () {
            var _this = this;
            var screenWidth = $.AdminLTE.options.screenSizes.sm - 1;
            //Expand sidebar on hover
            $(".main-sidebar").hover(
                function () {
                    if (
                        $("body").hasClass("sidebar-mini") &&
                        $("body").hasClass("sidebar-collapse") &&
                        $(window).width() > screenWidth
                    ) {
                        _this.expand();
                    }
                },
                function () {
                    if (
                        $("body").hasClass("sidebar-mini") &&
                        $("body").hasClass("sidebar-expanded-on-hover") &&
                        $(window).width() > screenWidth
                    ) {
                        _this.collapse();
                    }
                }
            );
        },
        expand: function () {
            $("body")
                .removeClass("sidebar-collapse")
                .addClass("sidebar-expanded-on-hover");
        },
        collapse: function () {
            if ($("body").hasClass("sidebar-expanded-on-hover")) {
                $("body")
                    .removeClass("sidebar-expanded-on-hover")
                    .addClass("sidebar-collapse");
            }
        },
    };

    /* Tree()
     * ======
     * Converts the sidebar into a multilevel
     * tree view menu.
     *
     * @type Function
     * @Usage: $.AdminLTE.tree('.sidebar')
     */
    $.AdminLTE.tree = function (menu) {
        var _this = this;
        var animationSpeed = $.AdminLTE.options.animationSpeed;
        $(document)
            .off("click", menu + " li a")
            .on("click", menu + " li a", function (e) {
                //Get the clicked link and the next element
                var $this = $(this);
                var checkElement = $this.next();

                //Check if the next element is a menu and is visible
                if (
                    checkElement.is(".treeview-menu") &&
                    checkElement.is(":visible") &&
                    !$("body").hasClass("sidebar-collapse")
                ) {
                    //Close the menu
                    checkElement.slideUp(animationSpeed, function () {
                        checkElement.removeClass("menu-open");
                        //Fix the layout in case the sidebar stretches over the height of the window
                        //_this.layout.fix();
                    });
                    checkElement.parent("li").removeClass("active");
                }
                //If the menu is not visible
                else if (
                    checkElement.is(".treeview-menu") &&
                    !checkElement.is(":visible")
                ) {
                    //Get the parent menu
                    var parent = $this.parents("ul").first();
                    //Close all open menus within the parent
                    var ul = parent.find("ul:visible").slideUp(animationSpeed);
                    //Remove the menu-open class from the parent
                    ul.removeClass("menu-open");
                    //Get the parent li
                    var parent_li = $this.parent("li");

                    //Open the target menu and add the menu-open class
                    checkElement.slideDown(animationSpeed, function () {
                        //Add the class active to the parent li
                        checkElement.addClass("menu-open");
                        parent.find("li.active").removeClass("active");
                        parent_li.addClass("active");
                        //Fix the layout in case the sidebar stretches over the height of the window
                        _this.layout.fix();
                    });
                }
                //if this isn't a link, prevent the page from being redirected
                if (checkElement.is(".treeview-menu")) {
                    e.preventDefault();
                }
            });
    };

    /* ControlSidebar
     * ==============
     * Adds functionality to the right sidebar
     *
     * @type Object
     * @usage $.AdminLTE.controlSidebar.activate(options)
     */
    $.AdminLTE.controlSidebar = {
        //instantiate the object
        activate: function () {
            //Get the object
            var _this = this;
            //Update options
            var o = $.AdminLTE.options.controlSidebarOptions;
            //Get the sidebar
            var sidebar = $(o.selector);
            //The toggle button
            var btn = $(o.toggleBtnSelector);

            //Listen to the click event
            btn.on("click", function (e) {
                e.preventDefault();
                //If the sidebar is not open
                if (
                    !sidebar.hasClass("control-sidebar-open") &&
                    !$("body").hasClass("control-sidebar-open")
                ) {
                    //Open the sidebar
                    _this.open(sidebar, o.slide);
                } else {
                    _this.close(sidebar, o.slide);
                }
            });

            //If the body has a boxed layout, fix the sidebar bg position
            var bg = $(".control-sidebar-bg");
            _this._fix(bg);

            //If the body has a fixed layout, make the control sidebar fixed
            if ($("body").hasClass("fixed")) {
                _this._fixForFixed(sidebar);
            } else {
                //If the content height is less than the sidebar's height, force max height
                if (
                    $(".content-wrapper, .right-side").height() <
                    sidebar.height()
                ) {
                    _this._fixForContent(sidebar);
                }
            }
        },
        //Open the control sidebar
        open: function (sidebar, slide) {
            //Slide over content
            if (slide) {
                sidebar.addClass("control-sidebar-open");
            } else {
                //Push the content by adding the open class to the body instead
                //of the sidebar itself
                $("body").addClass("control-sidebar-open");
            }
        },
        //Close the control sidebar
        close: function (sidebar, slide) {
            if (slide) {
                sidebar.removeClass("control-sidebar-open");
            } else {
                $("body").removeClass("control-sidebar-open");
            }
        },
        _fix: function (sidebar) {
            var _this = this;
            if ($("body").hasClass("layout-boxed")) {
                sidebar.css("position", "absolute");
                sidebar.height($(".wrapper").height());
                if (_this.hasBindedResize) {
                    return;
                }
                $(window).resize(function () {
                    _this._fix(sidebar);
                });
                _this.hasBindedResize = true;
            } else {
                sidebar.css({
                    position: "fixed",
                    height: "auto",
                });
            }
        },
        _fixForFixed: function (sidebar) {
            sidebar.css({
                position: "fixed",
                "max-height": "100%",
                overflow: "auto",
                "padding-bottom": "50px",
            });
        },
        _fixForContent: function (sidebar) {
            $(".content-wrapper, .right-side").css(
                "min-height",
                sidebar.height()
            );
        },
    };

    /* BoxWidget
     * =========
     * BoxWidget is a plugin to handle collapsing and
     * removing boxes from the screen.
     *
     * @type Object
     * @usage $.AdminLTE.boxWidget.activate()
     *        Set all your options in the main $.AdminLTE.options object
     */
    $.AdminLTE.boxWidget = {
        selectors: $.AdminLTE.options.boxWidgetOptions.boxWidgetSelectors,
        icons: $.AdminLTE.options.boxWidgetOptions.boxWidgetIcons,
        animationSpeed: $.AdminLTE.options.animationSpeed,
        activate: function (_box) {
            var _this = this;
            if (!_box) {
                _box = document; // activate all boxes per default
            }
            //Listen for collapse event triggers
            $(_box).on("click", _this.selectors.collapse, function (e) {
                e.preventDefault();
                _this.collapse($(this));
            });

            //Listen for remove event triggers
            $(_box).on("click", _this.selectors.remove, function (e) {
                e.preventDefault();
                _this.remove($(this));
            });
        },
        collapse: function (element) {
            var _this = this;
            //Find the box parent
            var box = element.parents(".box").first();
            //Find the body and the footer
            var box_content = box.find(
                "> .box-body, > .box-footer, > form  >.box-body, > form > .box-footer"
            );
            if (!box.hasClass("collapsed-box")) {
                //Convert minus into plus
                element
                    .children(":first")
                    .removeClass(_this.icons.collapse)
                    .addClass(_this.icons.open);
                //Hide the content
                box_content.slideUp(_this.animationSpeed, function () {
                    box.addClass("collapsed-box");
                });
            } else {
                //Convert plus into minus
                element
                    .children(":first")
                    .removeClass(_this.icons.open)
                    .addClass(_this.icons.collapse);
                //Show the content
                box_content.slideDown(_this.animationSpeed, function () {
                    box.removeClass("collapsed-box");
                });
            }
        },
        remove: function (element) {
            //Find the box parent
            var box = element.parents(".box").first();
            box.slideUp(this.animationSpeed);
        },
    };
}

/* ------------------
 * - Custom Plugins -
 * ------------------
 * All custom plugins are defined below.
 */

/*
 * BOX REFRESH BUTTON
 * ------------------
 * This is a custom plugin to use with the component BOX. It allows you to add
 * a refresh button to the box. It converts the box's state to a loading state.
 *
 * @type plugin
 * @usage $("#box-widget").boxRefresh( options );
 */
(function ($) {
    "use strict";

    $.fn.boxRefresh = function (options) {
        // Render options
        var settings = $.extend(
            {
                //Refresh button selector
                trigger: ".refresh-btn",
                //File source to be loaded (e.g: ajax/src.php)
                source: "",
                //Callbacks
                onLoadStart: function (box) {
                    return box;
                }, //Right after the button has been clicked
                onLoadDone: function (box) {
                    return box;
                }, //When the source has been loaded
            },
            options
        );

        //The overlay
        var overlay = $(
            '<div class="overlay"><div class="fa fa-refresh fa-spin"></div></div>'
        );

        return this.each(function () {
            //if a source is specified
            if (settings.source === "") {
                if (window.console) {
                    window.console.log(
                        "Please specify a source first - boxRefresh()"
                    );
                }
                return;
            }
            //the box
            var box = $(this);
            //the button
            var rBtn = box.find(settings.trigger).first();

            //On trigger click
            rBtn.on("click", function (e) {
                e.preventDefault();
                //Add loading overlay
                start(box);

                //Perform ajax call
                box.find(".box-body").load(settings.source, function () {
                    done(box);
                });
            });
        });

        function start(box) {
            //Add overlay and loading img
            box.append(overlay);

            settings.onLoadStart.call(box);
        }

        function done(box) {
            //Remove overlay and loading img
            box.find(overlay).remove();

            settings.onLoadDone.call(box);
        }
    };
})(jQuery);

/*
 * EXPLICIT BOX CONTROLS
 * -----------------------
 * This is a custom plugin to use with the component BOX. It allows you to activate
 * a box inserted in the DOM after the app.js was loaded, toggle and remove box.
 *
 * @type plugin
 * @usage $("#box-widget").activateBox();
 * @usage $("#box-widget").toggleBox();
 * @usage $("#box-widget").removeBox();
 */
(function ($) {
    "use strict";

    $.fn.activateBox = function () {
        $.AdminLTE.boxWidget.activate(this);
    };

    $.fn.toggleBox = function () {
        var button = $($.AdminLTE.boxWidget.selectors.collapse, this);
        $.AdminLTE.boxWidget.collapse(button);
    };

    $.fn.removeBox = function () {
        var button = $($.AdminLTE.boxWidget.selectors.remove, this);
        $.AdminLTE.boxWidget.remove(button);
    };
})(jQuery);

/*
 * TODO LIST CUSTOM PLUGIN
 * -----------------------
 * This plugin depends on iCheck plugin for checkbox and radio inputs
 *
 * @type plugin
 * @usage $("#todo-widget").todolist( options );
 */
(function ($) {
    "use strict";

    $.fn.todolist = function (options) {
        // Render options
        var settings = $.extend(
            {
                //When the user checks the input
                onCheck: function (ele) {
                    return ele;
                },
                //When the user unchecks the input
                onUncheck: function (ele) {
                    return ele;
                },
            },
            options
        );

        return this.each(function () {
            if (typeof $.fn.iCheck != "undefined") {
                $("input", this).on("ifChecked", function () {
                    var ele = $(this).parents("li").first();
                    ele.toggleClass("done");
                    settings.onCheck.call(ele);
                });

                $("input", this).on("ifUnchecked", function () {
                    var ele = $(this).parents("li").first();
                    ele.toggleClass("done");
                    settings.onUncheck.call(ele);
                });
            } else {
                $("input", this).on("change", function () {
                    var ele = $(this).parents("li").first();
                    ele.toggleClass("done");
                    if ($("input", ele).is(":checked")) {
                        settings.onCheck.call(ele);
                    } else {
                        settings.onUncheck.call(ele);
                    }
                });
            }
        });
    };
})(jQuery);
