
/* ================= Dynamic Page Loading ================= */

// https://github.com/defunkt/jquery-pjax
$(document).pjax('a[data-pjax]', '#content-wrapper', {
    timeout: 2000
});
$(document).on('pjax:complete', function() {
    refreshForm();
});

/* ================= Common Data ================= */

var bsurl = $('body').attr("bsurl");
var adminRoute = $('body').attr("adminRoute");
var _token = $('input[name=_token]').val();

/* ================= Custom Methods ================= */

if(typeof String.prototype.hashCode !== 'function') {
    String.prototype.hashCode = function() {
        var hash = 0;
        if (this.length == 0) return hash;
        for (i = 0; i < this.length; i++) {
            char = this.charCodeAt(i);
            hash = ((hash<<5)-hash)+char;
            hash = hash & hash; // Convert to 32bit integer
        }
        return hash;
    };
}
if(typeof String.prototype.trim !== 'function') {
    String.prototype.trim = function() {
        return this.replace(/^\s+|\s+$/g, '');
    };
}
if (typeof String.prototype.startsWith != 'function') {
    // see below for better implementation!
    String.prototype.startsWith = function (str){
        return this.indexOf(str) == 0;
    };
}
if (typeof String.prototype.ucfirst != 'function') {
    // see below for better implementation!
    String.prototype.ucfirst = function (){
        return this.charAt(0).toUpperCase() + this.slice(1);
    };
}

if (typeof String.prototype.endsWith != 'function') {
    // see below for better implementation!
    String.prototype.endsWith = function (pattern){
        var d = this.length - pattern.length;
        return d >= 0 && this.lastIndexOf(pattern) === d;
    };
}
if (typeof Array.prototype.clear != 'function') {
    Array.prototype.clear = function() {
        this.splice(0, this.length);
    };
}
// Convert String MySQL Timestamp to JS Date Object 
if (typeof String.prototype.toDate != 'function') {
    String.prototype.toDate = function () {
        var t = this.split(/[- :]/);
        return new Date(Date.UTC(t[0], t[1]-1, t[2], t[3], t[4], t[5]));
    };
}
// Convert JS Date Object to MySQL Timestamp
function twoDigits(d) {
    if(0 <= d && d < 10) return "0" + d.toString();
    if(-10 < d && d < 0) return "-0" + (-1*d).toString();
    return d.toString();
}
if (typeof Date.prototype.toTS != 'function') {
    Date.prototype.toTS = function () {
        return this.getUTCFullYear() + "-" + twoDigits(1 + this.getUTCMonth()) + "-" + twoDigits(this.getUTCDate()) + " " + twoDigits(this.getUTCHours()) + ":" + twoDigits(this.getUTCMinutes()) + ":" + twoDigits(this.getUTCSeconds());
    };
}

function isset(value) {
    if(typeof value !== 'undefined' && value !== null) {
        return true;
    }
    return false;
}

function getFormDataJSON($form) {
    var unindexed_array = $form.serializeArray();
    var indexed_array = {};

    $.map(unindexed_array, function(n, i){
        if(n['name'].endsWith("[]")) {
            var key = n['name'].replace("[]", "");
            if(key in indexed_array) {
                indexed_array[key].push(n['value']);
            } else {
                indexed_array[key] = [];
                indexed_array[key].push(n['value']);
            }
        } else {
            indexed_array[n['name']] = n['value'];
        }
    });

    return JSON.stringify(indexed_array);
}

/* ================= Fancy Notifications ================= */
(function($) {
    'use strict';
    var Notification = function(container, options) {
        var self = this;
        self.container = $(container);
        self.notification = $('<div class="pgn push-on-sidebar-open"></div>');
        self.options = $.extend(true, {}, $.fn.pgNotification.defaults, options);
        if (!self.container.find('.pgn-wrapper[data-position=' + this.options.position + ']').length) {
            self.wrapper = $('<div class="pgn-wrapper" data-position="' + this.options.position + '"></div>');
            self.container.append(self.wrapper);
        } else {
            self.wrapper = $('.pgn-wrapper[data-position=' + this.options.position + ']');
        }
        self.alert = $('<div class="alert"></div>');
        self.alert.addClass('alert-' + self.options.type);
        if (self.options.style == 'bar') {
            new BarNotification();
        } else if (self.options.style == 'flip') {
            new FlipNotification();
        } else if (self.options.style == 'circle') {
            new CircleNotification();
        } else if (self.options.style == 'simple') {
            new SimpleNotification();
        } else {
            new SimpleNotification();
        }

        function SimpleNotification() {
            self.notification.addClass('pgn-simple');
            self.alert.append(self.options.message);
            if (self.options.showClose) {
                var close = $('<button type="button" class="close" data-dismiss="alert"></button>').append('<span aria-hidden="true">&times;</span>').append('<span class="sr-only">Close</span>');
                self.alert.prepend(close);
            }
        }

        function BarNotification() {
            self.notification.addClass('pgn-bar');
            self.alert.append('<span>' + self.options.message + '</span>');
            self.alert.addClass('alert-' + self.options.type);
            if (self.options.showClose) {
                var close = $('<button type="button" class="close" data-dismiss="alert"></button>').append('<span aria-hidden="true">&times;</span>').append('<span class="sr-only">Close</span>');
                self.alert.prepend(close);
            }
        }

        function CircleNotification() {
            self.notification.addClass('pgn-circle');
            var table = '<div>';
            if (self.options.thumbnail) {
                table += '<div class="pgn-thumbnail"><div>' + self.options.thumbnail + '</div></div>';
            }
            table += '<div class="pgn-message"><div>';
            if (self.options.title) {
                table += '<p class="bold">' + self.options.title + '</p>';
            }
            table += '<p>' + self.options.message + '</p></div></div>';
            table += '</div>';
            if (self.options.showClose) {
                table += '<button type="button" class="close" data-dismiss="alert">';
                table += '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>';
                table += '</button>';
            }
            self.alert.append(table);
            self.alert.after('<div class="clearfix"></div>');
        }

        function FlipNotification() {
            self.notification.addClass('pgn-flip');
            self.alert.append("<span>" + self.options.message + "</span>");
            if (self.options.showClose) {
                var close = $('<button type="button" class="close" data-dismiss="alert"></button>').append('<span aria-hidden="true">&times;</span>').append('<span class="sr-only">Close</span>');
                self.alert.prepend(close);
            }
        }
        self.notification.append(self.alert);
        self.alert.on('closed.bs.alert', function() {
            self.notification.remove();
            self.options.onClosed();
        });
        return this;
    };
    Notification.VERSION = "1.0.0";
    Notification.prototype.show = function() {
        this.wrapper.prepend(this.notification);
        this.options.onShown();
        if (this.options.timeout != 0) {
            var _this = this;
            setTimeout(function() {
                this.notification.fadeOut("slow", function() {
                    $(this).remove();
                    _this.options.onClosed();
                });
            }.bind(this), this.options.timeout);
        }
    };
    $.fn.pgNotification = function(options) {
        return new Notification(this, options);
    };
    $.fn.pgNotification.defaults = {
        style: 'simple',
        message: null,
        position: 'top-right',
        type: 'info',
        showClose: true,
        timeout: 4000,
        onShown: function() {},
        onClosed: function() {}
    }
})(window.jQuery);

// Basic Needed Fancy Notifications

function show_success(title, data) {
    // console.log(title, data);
    $('body').pgNotification({
        style: 'circle',
        title: title,
        message: data.message,
        position: "top-right",
        timeout: 2000,
        type: "success",
        thumbnail: '<i class="fa fa-check" style="font-size:45px;background-color:#fff;color:#10cfbc;width:50px;height:50px;padding-left:1px;padding-top:2px;"></i>'
    }).show('1000');
    setTimeout(function(){
    }, 500);
};

function show_failure(title, data) {
    // console.log(title, data);
    if(typeof data.message === 'undefined' || data.message === null) {
        if(typeof data.responseJSON === 'undefined' && typeof data.responseJSON.exception === 'undefined' && data.responseJSON.exception === null) {
            data.message = "Unknown Error: Please check Server Error Logs";
        } else {
            data.message = data.responseJSON.exception;
        }
    }
    $('body').pgNotification({
        style: 'circle',
        title: title,
        message: data.message,
        position: "top-right",
        timeout: 0,
        type: "danger",
        thumbnail: '<i class="fa fa-ban" style="font-size:45px;background-color:#fff;color:#f55754;width:48px;height:48px;padding-left:5px;padding-top:1px;"></i>'
    }).show('1000');
    setTimeout(function(){
    }, 500);
};

/* ================= locationpicker ================= */
function initLP() {
    $(".location-select").each(function( index ) {
        $(this).children('.lp-map').locationpicker({
            location: {latitude: $(this).find('.lp-lat').val(), longitude: $(this).find('.lp-lng').val()},
            zoom: 6,
            scrollwheel: false,
            inputBinding: {
                latitudeInput: $(this).find('.lp-lat'),
                longitudeInput: $(this).find('.lp-lng'),
                radiusInput: $(this).find('.lp-radius'),
                locationNameInput: $(this).find('.lp-address')
            },
            enableAutocomplete: true,
            enableAutocompleteBlur: false,
            onchanged: function (currentLocation, radius, isMarkerDropped) {
                var lat = ""+currentLocation.latitude;
                var lng = ""+currentLocation.longitude;
                $(this).parent().find('.lp-latlng').val(lat.substring(0, 10) + "," + lng.substring(0, 10));
            }
        });
        $(this).find('.lp-lat').on("change", function() {
            $closest = $(this).closest('.location-select');
            var value = $closest.find('.lp-lat').val().substring(0, 10) + "," + $closest.find('.lp-lng').val().substring(0, 10)
            $(this).closest('.location-select').find('.lp-latlng').val(value);
        });
        $(this).find('.lp-lng').on("change", function() {
            $closest = $(this).closest('.location-select');
            var value = $closest.find('.lp-lat').val().substring(0, 10) + "," + $closest.find('.lp-lng').val().substring(0, 10)
            $(this).closest('.location-select').find('.lp-latlng').val(value);
        });
    });
}
/* ================= Quick Add ================= */
var popup_field_name = 0;
var popup_module_id = 0;
var popup_vals = "";

function initQuickAdd() {
    $(".btn_quick_add").on('click', function () {
        popup_field_name = $(this).attr('field_name'); 
        popup_vals = $(this).attr('popup_vals');
        popup_module_id = $(this).attr('popup_module_id');

        if(popup_module_id != 0) {
			$.ajax({
                url: bsurl+"/"+adminRoute+"/quick_add_form/" + popup_module_id,
				method: 'GET',
				data: {
					"_token": _token,
					"field_name": popup_field_name,
					"popup_vals": popup_vals
				},
				success: function( data ) { 
					$('#modal_content').html(data);
					$('#quick_add').modal('show');
				}
			});
		}
    });
}

function iniCheckListUpdate() {
    $(".value_checklist input").on('click', function () {
        list_title = $(this).attr('title'); 
        module_id = $(this).attr('module_id'); 
        row_id = $(this).attr('row_id');
        module_field_id = $(this).attr('module_field_id');
        
        if($(this).is(":checked")) {
            list_vals = true;
        } else {
            list_vals = false;
        }

        $.ajax({
            url: bsurl+"/"+adminRoute+"/checklist_update",
            method: 'POST',
            data: {
                "_token": _token,
                "list_title": list_title,
                "list_vals": list_vals,
                "module_id": module_id,
                "row_id": row_id,
                "module_field_id": module_field_id,
            },
            success: function( data ) {
                console.log(data);
            }
        });
    });
}

function compareDates(d1, d2) {
    dateFirst = d1.split('/');
    dateSecond = d2.split('/');
    var date1 = new Date(parseInt(dateFirst[2]), parseInt(dateFirst[1])-1, parseInt(dateFirst[0]));
    var date2 = new Date(parseInt(dateSecond[2]), parseInt(dateSecond[1])-1, parseInt(dateSecond[0]));
    return date1 >= date2;
}

function dynalist($list) {
    $listElem = $list.prev();
    var arr = JSON.parse($listElem.val());
    $list.children().html('');
    if(arr.length > 0) {
        for (var index = 0; index < arr.length; index++) {
            var element = arr[index];
            $list.children().append('<li>'+element+'</li>');
        }
    } else {
        $list.children().append('<li><br></li>');
    }
}

/* ================= checklist ================= */
function checklist() {
    
    $("body").on("click", ".checklist .todo-list li .value_checklist .display", function() {
        $(this).hide().siblings(".checklist .todo-list li .value_checklist .edit").show().val($(this).text()).focus();
    });

    $("body").on("focusout", ".checklist .todo-list li .value_checklist .edit", function() {
        var field_name = $(this).parents('.checklist').find('input').first().attr('name');
        $(this).hide().siblings(".checklist .todo-list li .value_checklist .display").show().text($(this).val());
        btn_checklist_arr(field_name);
        save_checklist_by_view(field_name);
    });

    $('.btn-checklist').on('click', function() {
        var field_name = $(this).parents('.checklist').find('input').first().attr('name');
        var count_list_max = $(this).attr('data-rule-maxcount');
        var count_list_min = $(this).attr('data-rule-mincount');
        var real_count = $('input[name="'+field_name+'"] + > ul.todo-list li').length;
        if(isset(count_list_max)) {
            if(parseInt(count_list_max) > parseInt(real_count)) {
                $('input[name="'+field_name+'"] + > ul').append('<li>'+
                        '<span class="handle"><i class="fa fa-ellipsis-v" style="margin-right:3px"></i><i class="fa fa-ellipsis-v"></i></span>'+
                        '<span class="value_checklist">'+
                            '<input type="checkbox" value="false" name="checked" style="position:relative;top:2px;margin:0 2px">'+
                            '<span style="display:inline-block;"><span class="text display checklist_title" style="vertical-align:middle;">Untitled</span>'+
                            '<input type="text" class="edit form-control" style="display:none"/></span>'+
                        '</span>'+
                        '<div class="tools">'+
                            '<i class="fa fa-trash-o btn_checklist_remove"></i>'+
                        '</div>'+
                    '</li>'
                );
            }
        } else {
            $('input[name="'+field_name+'"] + > ul').append('<li>'+
                    '<span class="handle"><i class="fa fa-ellipsis-v" style="margin-right:3px"></i><i class="fa fa-ellipsis-v"></i></span>'+
                    '<span class="value_checklist">'+
                        '<input type="checkbox" value="false" name="checked" style="position:relative;top:2px;margin:0 2px">'+
                        '<span style="display:inline-block;"><span class="text display checklist_title" style="vertical-align:middle;">Untitled</span>'+
                        '<input type="text" class="edit form-control" style="display:none"/></span>'+
                    '</span>'+
                    '<div class="tools">'+
                        '<i class="fa fa-trash-o btn_checklist_remove"></i>'+
                    '</div>'+
                '</li>'
            );
        }
        var real_count = $('input[name="'+field_name+'"] + > ul.todo-list li').length;
        if(isset(count_list_max) && (parseInt(count_list_max) == parseInt(real_count))) {
            $(this).addClass('disabled');
        }
        if(isset(count_list_min)) {
            if(parseInt(count_list_min) >= parseInt(real_count)) {
                $('input[name="'+field_name+'"] + > ul.todo-list li .btn_checklist_remove').remove();
            } else {
                $('input[name="'+field_name+'"] + > ul.todo-list li .tools').html('<i class="fa fa-trash-o btn_checklist_remove"></i>');
            }
        }
    });

    $("body").on('change', '.checklist .todo-list li input[name="checked"]', function() {
        var field_name = $(this).parents('.checklist').find('input').first().attr('name');
        if(this.checked) {
            $(this).val("true");
            $(this).closest('li').addClass("done");
        } else {
            $(this).val("false");
            $(this).closest('li').removeClass("done");
        }
        btn_checklist_arr(field_name);
        save_checklist_by_view(field_name);
    });

    $("body").on('click', '.checklist .todo-list li .btn_checklist_remove', function() {
        var field_name = $(this).parents('.checklist').find('input').first().attr('name');
        var count_list_min = $(this).parents('.checklist').find('.btn-checklist').attr('data-rule-mincount');
        var count_list_max = $(this).parents('.checklist').find('.btn-checklist').attr('data-rule-maxcount');
        var real_count = $('input[name="'+field_name+'"] + > ul.todo-list li').length;
        if(isset(count_list_min)) {
            if(parseInt(count_list_min) < parseInt(real_count)) {
                $(this).closest('li').remove();
            }
        } else {
            $(this).closest('li').remove();
        }

        if(isset(count_list_max) && (parseInt(count_list_max) <= parseInt(real_count))) {
            $('.btn-checklist').removeClass('disabled');
        }

        var real_count = $('input[name="'+field_name+'"] + > ul.todo-list li').length;
        if(isset(count_list_min)) {
            if(parseInt(count_list_min) >= parseInt(real_count)) {
                $('input[name="'+field_name+'"] + > ul.todo-list li .btn_checklist_remove').remove();
            } else {
                $('input[name="'+field_name+'"] + > ul.todo-list li .tools').html('<i class="fa fa-trash-o btn_checklist_remove"></i>');
            }
        }

        btn_checklist_arr(field_name);
        save_checklist_by_view(field_name);
    });
}

function btn_checklist_arr(field_name) {
    var arr = [];
    
    var element = $('input[name="'+field_name+'"] + > ul.todo-list li .value_checklist');
    $.each( element , function (kay, value) {
        var object = {};
        if($(this).find('span.checklist_title').text() != "Untitled") {
            object.checked = $(this).find('input').val();
            object.title = $(this).find('span.checklist_title').text();
            arr.push(object);
        }
    });
    $('.checklist input[name="'+field_name+'"]').val(JSON.stringify(arr));
}

function save_checklist_by_view(field_name) {
    var value = $(".checklist input[name='"+field_name+"']").val();
    var module_name_db = $(".checklist input[name='"+field_name+"']").attr("module_name_db");
    var row_id = $(".checklist input[name='"+field_name+"']").attr("row_id");
    var object = {};
    if((isset(module_name_db) && module_name_db != "") && (isset(row_id) && row_id != "" && row_id != 0 && row_id != '0')) {
        object[field_name] = value;

        var data = JSON.stringify(object);
        
        // console.log("value = "+value+" module_name_db = "+module_name_db+" field_name = "+field_name+ " row_id = "+row_id);
        $.ajax({
            url: bsurl+"/"+adminRoute+"/"+module_name_db+"/"+row_id,
            method: "PUT",
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('input[name=_token]').val()
            },
            success: function(data) {
                console.log(data);
                $('body').pgNotification({
                    style: 'circle',
                    title: "Checklist",
                    message: "Updated",
                    position: "top-right",
                    timeout: 0,
                    type: "success",
                    thumbnail: '<i class="fa fa-check" style="font-size: 50px;background-color: #fff;color: #10cfbc;"></i>'
                }).show('1000');
                setTimeout(function(){
                }, 500);
            },
        });
    }
}

function refreshForm() {
    _token = $('input[name=_token]').val();

    /* ================= Toggle Switch - Checkbox ================= */
    $(".Switch:not(.Ajax)").click(function() {
        $(this).hasClass("On") ? ($(this).parent().find("input:checkbox").attr("checked", !0), $(this).removeClass("On").addClass("Off")) : ($(this).parent().find("input:checkbox").attr("checked", !1), $(this).removeClass("Off").addClass("On"))
    }), $(".Switch:not(.Ajax)").each(function() {
        $(this).parent().find("input:checkbox").length && ($(this).parent().find("input:checkbox").hasClass("show") || $(this).parent().find("input:checkbox").hide(), $(this).parent().find("input:checkbox").is(":checked") ? $(this).removeClass("On").addClass("Off") : $(this).removeClass("Off").addClass("On"))
    });
    
    /* ================= HTML ================= */
    $(".htmlbox").each(function(index, elem) {
        $("#"+elem.id).summernote();
    });
    
    // $('#summernote_desc').summernote('insertImage', "http://localhost/laplus/public/files/jqft9yw3pqg0mquou2rz/laracabs.jpg?s=200", "test.jpg");
    
    /* ================= Default Select2 ================= */
    $("[rel=select2]").select2({
        
    });
    $("[rel=taginput]").select2({
        tags: true,
        tokenSeparators: [',']
    });
    
    // Null Value for Dropdown
    $(".null_dd").on("click", function(event) {
        var cb = $(this).find('.cb_null_dd');
        if(cb.is(":checked")) {
            cb.prop("checked", !1);
            cb.attr("checked", !1);
            $(this).parent().prev().find('select').select2("enable");
        } else {
            cb.prop("checked", !0);
            cb.attr("checked", !0);
            $(this).parent().prev().find('select').select2("enable", false);
        }
    });

    /* ================= dynalist ================= */

    $(".dynalist ul").keydown(function(e) {
        if(e.keyCode == 8) {
            if($(this).children().length == 1 && $(this).html() == "<li><br></li>") {
                e.preventDefault();
            }
        }
    });
    
    $(".dynalist ul").bind("paste", function(e) {
		e.preventDefault();
        if(e.originalEvent.clipboardData) {
			var text = e.originalEvent.clipboardData.getData("text/plain");
            text = text.split("\n");
            for (var i = 0; i < text.length; i++) {
                var line = text[i].trim();
                $(this).append("<li>" + line + "</li>");
            }
		}
	});

    $('.dynalist').each(function(index, $list) {
        dynalist($(this));
    });

    $('form').submit(function(e) {
        $(this).find('.dynalist').each(function(index, $list) {
            $list = $(this);
            $listElem = $list.prev();

            // Show error if out of count
            var field_name = $list.attr('id');
            var label = $list.attr('label');
            var min = $list.attr('data-rule-mincount');
            var max = $list.attr('data-rule-maxcount');

            if($list.children().children().length < min) {
                // alert(label + " : Please add atleast "+min+" list items");
                if($('#'+field_name+'-error').length > 0) {
                    $('#'+field_name+'-error').html('Please add atleast '+min+' list items.');
                    $('#'+field_name+'-error').show();
                } else {
                    $(this).parent().append('<label id="'+field_name+'-error" class="dlerror" for="'+field_name+'">Please add atleast '+min+' list items.</label>');
                }
                e.preventDefault();
                return true;
            } else if($list.children().children().length > max) {
                // alert(label + " : Please add only "+max+" list items");
                if($('#'+field_name+'-error').length > 0) {
                    $('#'+field_name+'-error').html('Please add only '+max+' list items.');
                    $('#'+field_name+'-error').show();
                } else {
                    $(this).parent().append('<label id="'+field_name+'-error" class="dlerror" for="'+field_name+'">Please add only '+max+' list items.</label>');
                }
                e.preventDefault();
                return true;
            } else {
                $('#'+field_name+'-error').hide();
            }

            var arr = [];
            $list.children().children().each(function(index, elem) {
                arr.push(elem.innerText.trim());
            });
            $listElem.val(JSON.stringify(arr));
        });
        return true;
    })
    
    /* ================= bootstrap-datetimepicker ================= */
    $(".input-group.date").datetimepicker({
        format: 'DD/MM/YYYY'
    });

    $(".input-group.datetime").datetimepicker({
        format: 'DD/MM/YYYY LT',
        sideBySide: true
    });

    // Null Value for Date + Datetime
    $(".input-group-addon.null_date").on("click", function(event) { 
        var cb = $(this).find('.cb_null_date');
        if(cb.is(":checked")) {
            cb.prop("checked", !0);
            cb.attr("checked", !0);
            $(this).parent().find('input[type="text"]').prop('readonly', !0);
        } else {
            cb.prop("checked", !1);
            cb.attr("checked", !1);
            $(this).parent().find('input[type="text"]').prop('readonly', !1);
        }
    });

    /* ================= duration ================= */
    $(".input-group.duration input").bind('input', function() {
        var field_name = $(this).parents('.duration').find('input').first().attr('name');
        var day = $(".input-group.duration>input[name='"+field_name+"_days']").val();
        var hours = $(".input-group.duration>input[name='"+field_name+"_hours']").val();
        var minute = $(".input-group.duration>input[name='"+field_name+"_minutes']").val();

        if(isset(day)) {
            var day_min = parseInt(day) * 1440;
            var hours_min = parseInt(hours) * 60;
            var total_min = parseInt(day_min) + parseInt(hours_min) + parseInt(minute);
        } else {
            var hours_min = parseInt(hours) * 60;
            var total_min = parseInt(hours_min) + parseInt(minute);
        } 

        $('.input-group.duration').find('input[name="'+field_name+'"]').filter(':first').val(total_min);
    });

    /* ================= checklist ================= */
    checklist();

    /* ================= colorpicker ================= */

    $(".colorpicker").colorpicker().on('create', function (e) {
        if(e.currentTarget.value != "") {
            e.currentTarget.style.backgroundColor = e.currentTarget.value;
        }
    }).on('changeColor', function (e) {
        e.currentTarget.style.backgroundColor = e.color.toString('rgba');
    });

    /* ================= timepicker ================= */
    $('input.timepicker').timepicker({
            minuteStep: 15,
            showInpunts: false
    });

    /* ================= locationpicker ================= */
    $('#AddModal').on('shown.bs.modal', function () { 
        initLP();
    });

    initLP();

    /* ================= locationpicker ================= */
    initQuickAdd();

    /* ================= List update ================= */
    iniCheckListUpdate();
    
    /* ================= stickyTabs ================= */
    $('.nav-tabs').stickyTabs({ 
        selectorAttribute: "data-target",
        backToTop: true
    });

    var activeTab = window.location.href.substring(window.location.href.indexOf("#") + 1);
    if(activeTab.length > 1 && !activeTab.includes("http")) {
        $('.nav-tabs a[href="#'+activeTab+'"]').tab('show');
    }
    
    if (typeof jQuery.validator !== "undefined") {

        /* ================= Validate Unique Fields ================= */

        jQuery.validator.addMethod("data-rule-unique", function(value, element) {
            value = value.trim();
            
            var isAllowed = false;
            var field_id = element.getAttribute('field_id');
            var _token = $("input[name=_token_"+field_id+"]").val();
            var isEdit = element.getAttribute('isEdit');
            var row_id = element.getAttribute('row_id');
            
            if(value != '' && bsurl != "") {
                $.ajax({
                    url: bsurl+"/"+adminRoute+"/check_unique_val/"+field_id,
                    type:"POST",
                    async: false,
                    data:{
                        'field_value': value,
                        '_token': _token,
                        'isEdit': isEdit,
                        'row_id': row_id
                    },
                    success: function(data) {
                        // console.log(data);
                        if(data.exists == true) {
                            isAllowed = false;
                        } else {
                            isAllowed = true;
                        }
                    }
                });
            }
            return isAllowed;
        }, 'This value exists in database.');

        /* ================= Validate Min Date ================= */

        jQuery.validator.addMethod("data-rule-mindate", function(value, element, param) {
            value = value.trim();
            return compareDates(value, element.getAttribute('data-rule-mindate'));
        }, 'Enter valid date');

        jQuery.validator.setDefaults({
            ignore: ":hidden, .note-editable.panel-body, .dynalist ul"
        });
    }

    /* ================= Init File Manager ================= */
    $(".btn_upload_image").on("click", function() {
        showLAFM("image", $(this).attr("selecter"));
    });

    $(".btn_upload_summernote").on("click", function() {
        var sn_id = $(this).parent().parent().parent().prevAll(".htmlbox").attr("id");
        showLAFM("summernote_image", sn_id);
    });

    $(".btn_upload_file").on("click", function() {
        showLAFM("file", $(this).attr("selecter"));
    });

    $(".btn_upload_files").on("click", function() {
        showLAFM("files", $(this).attr("selecter"));
    });

    $(".uploaded_image i.fa.fa-times").on("click", function() {
        $(this).parent().children("img").attr("src", "");
        $(this).parent().addClass("hide");
        $(this).parent().prev().removeClass("hide");
        $(this).parent().prev().prev().val("0");
    });

    $(".uploaded_file i.fa.fa-times").on("click", function(e) {
        $(this).parent().attr("href", "");
        $(this).parent().addClass("hide");
        $(this).parent().prev().removeClass("hide");
        $(this).parent().prev().prev().val("0");
        e.preventDefault();
    });

    $(".uploaded_file2 i.fa.fa-times").on("click", function(e) {
        var upload_id = $(this).parent().attr("upload_id");
        var $hiddenFIDs = $(this).parent().parent().prev();
        
        var hiddenFIDs = JSON.parse($hiddenFIDs.val());
        var hiddenFIDs2 = [];
        for (var key in hiddenFIDs) {
            if (hiddenFIDs.hasOwnProperty(key)) {
                var element = hiddenFIDs[key];
                if(element != upload_id) {
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
$('body').on('submit', 'form', function() { 
    if($(this).find('input[name="_method"]').val() == "DELETE") {
        if (confirm('Are you sure you want to delete this?')) {
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
    
    $("#fm").modal('show');
    
    loadFMFiles();
}
function getLI(upload) {
    var image = '';
    if($.inArray(upload.extension, ["jpg", "jpeg", "png", "gif", "bmp"]) > -1) {
        image = '<img src="'+bsurl+'/files/'+upload.hash+'/'+upload.name+'?s=130">';
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
    return '<li><a class="fm_file_sel" data-toggle="tooltip" data-placement="top" title="'+upload.name+'" upload=\''+JSON.stringify(upload)+'\'>'+image+'</a></li>';
}
function loadFMFiles() {
    // load uploaded files
    $.ajax({
        dataType: 'json',
        url: bsurl+"/"+adminRoute+"/uploaded_files",
        success: function ( json ) {
            console.log(json);
            cntFiles = json.uploads;
            $(".fm_file_selector ul").empty();
            if(cntFiles.length) {
                for (var index = 0; index < cntFiles.length; index++) {
                    var element = cntFiles[index];
                    var li = getLI(element);
                    $(".fm_file_selector ul").append(li);
                }
            } else {
                $(".fm_file_selector ul").html("<div class='text-center text-danger' style='margin-top:40px;'>No Files</div>");
            }
        }
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
        if(sstring != "") {
            $(".fm_file_selector ul").empty();
            for (var index = 0; index < cntFiles.length; index++) {
                var upload = cntFiles[index];
                if(upload.name.toUpperCase().includes(sstring.toUpperCase())) {
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
        init: function() {
            this.on("complete", function(file) {
                this.removeFile(file);
            });
            this.on("success", function(file) {
                console.log("addedfile");
                console.log(file);
                loadFMFiles();
            });
        }
    });
    
    $("body").on("click", ".fm_file_sel", function() {
        type = $("#image_selecter_origin_type").val();
        upload = JSON.parse($(this).attr("upload"));
        console.log("upload sel: "+upload+" type: "+type);
        if(type == "image") {
            $hinput = $("input[name="+$("#image_selecter_origin").val()+"]");
            $hinput.val(upload.id);

            $hinput.next("a").addClass("hide");
            $hinput.next("a").next(".uploaded_image").removeClass("hide");
            $hinput.next("a").next(".uploaded_image").children("img").attr("src", bsurl+'/files/'+upload.hash+'/'+upload.name+"?s=150");
        } else if(type == "summernote_image") {
            var img_url = bsurl+'/files/'+upload.hash+'/'+upload.name;
            $('#' + $("#image_selecter_origin").val()).summernote('insertImage', img_url, upload.name);
        } else if(type == "file") {
            $hinput = $("input[name="+$("#image_selecter_origin").val()+"]");
            $hinput.val(upload.id);

            $hinput.next("a").addClass("hide");
            $hinput.next("a").next(".uploaded_file").removeClass("hide");
            $hinput.next("a").next(".uploaded_file").attr("href", bsurl+'/files/'+upload.hash+'/'+upload.name);
        } else if(type == "files") {
            $hinput = $("input[name="+$("#image_selecter_origin").val()+"]");
            
            var hiddenFIDs = JSON.parse($hinput.val());
            // check if upload_id exists in array
            var upload_id_exists = false;
            for (var key in hiddenFIDs) {
                if (hiddenFIDs.hasOwnProperty(key)) {
                    var element = hiddenFIDs[key];
                    if(element == upload.id) {
                        upload_id_exists = true;
                    }
                }
            }
            if(!upload_id_exists) {
                hiddenFIDs.push(upload.id);
            }
            $hinput.val(JSON.stringify(hiddenFIDs));
            var fileImage = "";
            if(upload.extension == "jpg" || upload.extension == "png" || upload.extension == "gif" || upload.extension == "jpeg") {
                fileImage = "<img src='"+bsurl+"/files/"+upload.hash+"/"+upload.name+"?s=90'>";
            } else {
                fileImage = "<i class='fa fa-file-o'></i>";
            }
            $hinput.next("div.uploaded_files").append("<a class='uploaded_file2' upload_id='"+upload.id+"' target='_blank' href='"+bsurl+"/files/"+upload.hash+"/"+upload.name+"'>"+fileImage+"<i title='Remove File' class='fa fa-times'></i></a>");
        }
        $("#fm").modal('hide');
    });
}

$(document).ready(function() {
    refreshForm();

    if (typeof Dropzone !== "undefined") {
        initFM();
    } else {
        console.log("Dropzone is not defined");
    }
});