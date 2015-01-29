if ( ! window.console ) console = { log: function(){} };

$.ajaxSetup({
    headers : {
      'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
    }
});

var banner_state = localStorage.getItem("dgShowBanner");

if (banner_state === null || banner_state === 'show') {
    $('#masthead-wrapper').collapse();
}


// Global library
var dogGame = (function() {

    var characteristic_search = (function() {
        var settings = {
            counter: 0
        };

        var dropdown_html = "";

        var profiles_html = [];

        // Add characteristic button
        var add_characteristic_btn = $("#characteristics-add-characteristic");

        return {
            init: function(config) {
                // Merge config into settings
                $.extend( settings, config );

                this.set_delete_characteristic_trigger();
                this.change_characteristic_trigger();

                // Get the characteristics select box
                var jqxhr = $.post(Globals.root+"characteristics/dropdown",  $.proxy(this.get_dropdown, this))
                .fail(function(data ) {
                    console.log("Could not load characteristics dropdown.");
                });
            }, 

            get_dropdown: function(html) {
                dropdown_html = html;

                add_characteristic_btn.children(".loading").remove();

                add_characteristic_btn.attr("disabled", false);

                this.add_characteristic_click_trigger();

                return true;
            }, 

            set_delete_characteristic_trigger: function() {
                $(".characteristics-remove-characteristic").off("click").on("click", function (e) {
                    $(this).parent().remove();
                });
            }, 

            change_characteristic_trigger: function() {
                $("[id^='characteristics-select-']").off("change").on("change", function (e) {
                    var select = $(this);
                    var parent = select.parent();

                    var selected;
                    var old_counter;
                    var id;
                    var profile;
                    var profile_html;

                    parent.children(".characteristic-profile").remove();

                    selected = select.find(":selected");

                    id = selected.val();

                    if (id)
                    {
                        select.after('<p id="spinner-remove" class="text-center"><br /><i class="fa fa-spinner fa-spin"> </i><em>Loading...</em></p>');

                        old_counter = select.attr("id").replace("characteristics-select-", "");

                        // Check if the profiles exists
                        profile_html = profiles_html[id];

                        if (profile_html == undefined)
                        {
                            var jqxhr = $.post(Globals.root+"characteristics/profiles", {characteristic_id:id}, function(html) {
                                var profile_html = html;

                                profiles_html[id] = profile_html;

                                // Add the missing profiles
                                profile_html = profile_html.replace(/:counter_replace/g, old_counter);

                                profile_html = $(profile_html).removeAttr("id").removeClass("hide");

                                parent.append(profile_html);

                                $("#spinner-remove").remove();

                                return true;
                            })
                                .fail(function(data ) {
                                    console.log("Could not load characteristics profiles.");
                                });
                        }
                        else
                        {
                            // Add the missing profiles
                            profile_html = profile_html.replace(/:counter_replace/g, old_counter);

                            profile_html = $(profile_html).removeAttr("id").removeClass("hide");

                            parent.append(profile_html);

                            $("#spinner-remove").remove();

                            return true;
                        }
                    }
                });
            }, 

            add_characteristic_click_trigger: function() {
                add_characteristic_btn.off("click").on("click", $.proxy(this.add_characteristic, this));
            }, 

            add_characteristic: function(e) {
                var add_btn = add_characteristic_btn;
                var clone = $(dropdown_html);
                var select;
                var selected;

                var profile_html;

                clone.removeAttr("id").removeClass("hide").addClass("characteristic-wrapper clearfix");

                // Get the selected char
                select = clone.find("select");

                select.attr("id", "characteristics-select-"+settings.counter);

                select.attr("name", "ch["+settings.counter+"][id]");

                selected = select.find(":selected");

                add_btn.before(clone);

                this.set_delete_characteristic_trigger();

                this.change_characteristic_trigger();

                ++settings.counter;
            }
        }
    })();

    var custom_import = (function() {
        var settings = {
            counter: 0, 
            total_characteristics: 0, 
        };

        var dropdown_html = "";

        var profiles_html = [];

        var add_characteristic_btn = $("#import-custom-add-characteristic");
        var breed_dropdown = $("#import-custom-breed");

        return {
            init: function(config) {
                var breed;
                var that = this;

                // Merge config into settings
                $.extend( settings, config );

                this.set_delete_characteristic_trigger();
                this.change_characteristic_trigger();
                this.change_breed_trigger();
                this.change_sex_trigger();

                breed = breed_dropdown.val();

                // Get the characteristics select box
                var jqxhr = $.post(Globals.root+"characteristics/custom_import_dropdown", {breed:breed}, $.proxy(this.get_dropdown, this))
                    .fail(function(data ) {
                        console.log("Could not load characteristics dropdown.");
                    });


                $("#custom-import").on('submit', function (e) {
                    var form = $(this);
                    var datastring = form.serialize();
                    var submit_btn = $("[name='import_custom_dog']");

                    submit_btn.button("loading");

                    e.preventDefault();

                    // Do ajax call to validate form
                    var jqxhr = $.post(Globals.root+"imports/custom_import", datastring, function(data) {
                        // Display the errors if there were any
                        if (data.errors !== undefined) {
                            // Clear it
                            $("#import-custom-errors .modal-body").html("");

                            $.each(data.errors, function(name, value) {
                                $("#import-custom-errors .modal-body").append("<p>"+value+"</p>");
                            });

                            $("#import-custom-errors").modal("show");

                            submit_btn.button("reset");
                        } else if(data.redirect !== undefined){
                            // No errors, dog was made
                            window.location.replace(data.redirect);
                        }
                    }).fail(function(data ) {
                        console.log("Could not submit custom import form.");
                    });
                });

            }, 

            spinner: function(selector, status) {
                var to_disable = $(selector);
                var spinner = $(selector+" .spinner");

                if (status == 'loading') {
                    to_disable.attr("disabled", "disabled");
                    spinner.show();
                } else {
                    spinner.hide();
                    to_disable.attr("disabled", false);
                }
            }, 

            get_dropdown: function(html) {
                dropdown_html = html;

                this.add_characteristic_click_trigger();

                this.spinner("#custom-imports", "done");

                return true;
            }, 

            set_delete_characteristic_trigger: function() {
                $(".characteristics-remove-characteristic").off("click").on("click", function (e) {
                    $(this).parent().remove();

                    settings.total_characteristics--;

                    // Show the add button
                    if (settings.total_characteristics < 3) {
                        add_characteristic_btn.show();
                    }
                });
            }, 

            change_characteristic_trigger: function() {
                var that = this;

                $("[id^='characteristics-select-']").off("change").on("change", function (e) {
                    var select = $(this);
                    var parent = select.parent();
                    var sex = $("[name='custom_import_sex']:checked").val();
                    var breed = breed_dropdown.val();

                    var selected;
                    var characteristic;
                    var old_counter;
                    var profile;
                    var profile_html;

                    that.spinner("#custom-imports", "loading");

                    parent.children(".characteristic-profile").remove();

                    selected = select.find(":selected");
                    characteristic = selected.val();

                    if (characteristic)
                    {
                        old_counter = select.attr("id").replace("characteristics-select-", "");

                        // Check if the profiles exists
                        if (profiles_html[breed] === undefined) {
                            profiles_html[breed] = [];
                        }

                        profile_html = profiles_html[breed][characteristic];

                        if (profile_html == undefined)
                        {
                            var jqxhr = $.post(Globals.root+"characteristics/custom_import_profiles", {
                                characteristic:characteristic, breed:breed, sex:sex}, function(html) {
                                var profile_html = html;

                                profiles_html[breed][characteristic] = profile_html;

                                // Add the missing profiles
                                profile_html = profile_html.replace(/:counter_replace/g, old_counter);

                                profile_html = $(profile_html).removeAttr("id").removeClass("hide");

                                parent.append(profile_html);
                            
                                $('.slider-bounds').tooltip();

                                if (sex == 2) {
                                    $(".custom-female").hide();
                                    $(".custom-male").show();
                                } else {
                                    $(".custom-female").show();
                                    $(".custom-male").hide();
                                }

                                that.spinner("#custom-imports", "done");

                                return true;
                            })
                                .fail(function(data ) {
                                    console.log("Could not load characteristics profiles.");
                                });
                        }
                        else
                        {
                            // Add the missing profiles
                            profile_html = profile_html.replace(/:counter_replace/g, old_counter);

                            profile_html = $(profile_html).removeAttr("id").removeClass("hide");

                            parent.append(profile_html);

                            $('.slider-bounds').tooltip();

                            if (sex == 2) {
                                $(".custom-female").hide();
                                $(".custom-male").show();
                            } else {
                                $(".custom-female").show();
                                $(".custom-male").hide();
                            }

                            that.spinner("#custom-imports", "done");

                            return true;
                        }
                    }
                });
            }, 

            change_breed_trigger: function() {
                var that = this;

                breed_dropdown.off("change").on("change", function (e) {
                    that.spinner("#custom-imports", "loading");

                    var breed = breed_dropdown.val();

                    // Clear all of the characteristics
                    $(".characteristic-wrapper").each(function() {
                        $(this).remove();
                    });

                    // Reset the total characteristics
                    settings.total_characteristics = 0;

                    // Get the characteristics select box
                    var jqxhr = $.post(Globals.root+"characteristics/custom_import_dropdown", {breed:breed}, $.proxy(that.get_dropdown, that))
                        .fail(function(data ) {
                            console.log("Could not load characteristics dropdown.");
                        });

                    // Show the add button
                    add_characteristic_btn.show();

                    that.spinner("#custom-imports", "done");
                });
            }, 

            change_sex_trigger: function() {
                var that = this;

                $("[name='custom_import_sex']").off("change").on("change", function (e) {
                    that.spinner("#custom-imports", "loading");

                    var sex = $(this).val();

                    if (sex == 2) {
                        $(".custom-female").hide();
                        $(".custom-male").show();
                    } else {
                        $(".custom-female").show();
                        $(".custom-male").hide();
                    }

                    that.spinner("#custom-imports", "done");
                });
            }, 

            add_characteristic_click_trigger: function() {
                add_characteristic_btn.off("click").on("click", $.proxy(this.add_characteristic, this));
            }, 

            add_characteristic: function(e) {
                var that = this;
                var clone = $(dropdown_html);
                var select;
                var selected;

                var profile_html;

                that.spinner("#custom-imports", "loading");

                if (settings.total_characteristics < 3) {
                    clone.removeAttr("id").removeClass("hide").addClass("characteristic-wrapper clearfix");

                    // Get the selected char
                    select = clone.find("select");

                    select.attr("id", "characteristics-select-"+settings.counter);

                    select.attr("name", "ch["+settings.counter+"][id]");

                    selected = select.find(":selected");

                    add_characteristic_btn.before(clone);

                    this.set_delete_characteristic_trigger();

                    this.change_characteristic_trigger();

                    ++settings.counter;
                    ++settings.total_characteristics;

                    if (settings.total_characteristics >= 3) {
                        // Hide the add button
                        add_characteristic_btn.hide();
                    }
                }

                setTimeout(function(){
                    that.spinner("#custom-imports", "done");
                }, 250);
            }
        }
    })();

    return {
        characteristic_search: characteristic_search, 
        custom_import: custom_import
    }

})();


function check_off($checkbox, source, include_disabled) {
    if (include_disabled || ! $checkbox.is(":disabled")) {
        $checkbox.prop('checked', source.checked);

        $checkbox.triggerHandler("change");
    }
}

function check_all(source, name, include_disabled) {
    var $checkboxes;
    var total_names, len;
    var i, j;

    if (name instanceof Array) {
        total_names = name.length;

        for (i = 0; i < total_names; ++i) {
            $checkboxes = $(name[i]);

            if ($checkboxes == undefined) {
                len = $checkboxes.length;

                for(i = 0; i < len; ++i) {
                    check_off($checkboxes[i], source, include_disabled);
                }
            }
        }
    }
    else
    {
        $checkboxes = $(name);

        if ($checkboxes != undefined) {;
            $.each($checkboxes, function(key, value) {
                check_off($(value), source, include_disabled);
            });
        }
    }
}

function create_cookie(name,value,days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
}

function read_cookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function erase_cookie(name) {
    create_cookie(name,"",-1);
}




$("#spectrum-sidebar-chat").spectrum({
    color: "#000", 
    preferredFormat: "hex6",
    className: 'sp-bootstrap', 
    showButtons: false, 
    showInput: true, 
    showInitial: true,
    showPalette: true,
    showSelectionPalette: true,
    palette: [ ],
    localStorageKey: "spectrum.chat", // Any Spectrum with the same string will share selection
    change: function(color) {
        $("#spectrum-sidebar-chat").attr('value', color.toHex());
        $("#sidebar-chat-message").css('color', color.toHexString());
    }, 
    move: function(color) {
        $("#sidebar-chat-message").css('color', color.toHexString());
    }
});

$(document).ready(function() {
    "use strict";
    $.widget('custom.chat', {
        options: {
            speed: 10000
        },
        interval: 0, 
        _create: function() {
            var that = this;

            $('[name="chat_message"]').on('keydown', function(event) {
                if(event.keyCode == 13 && !event.shiftKey){
                    event.preventDefault();
                }
            });
            
            $('[name="chat_message"]').on('keyup', function(event) {
                if(event.keyCode == 13 && !event.shiftKey){
                    that._chat();
                }
            });
            
            $('[name="chat_submit"]').on('click', function(event) {
                that._chat();
            });

            this._update();

            this.interval = setInterval(function() {
                that._update();
            }, this.options.speed);

            /*$(window).on('blur', function(){
                that.pause();
                return true;
            });

            $(window).on('focus', function(){
                // that.resume();
                return true;
            });*/
        },
        reset: function() {
            clearInterval(this.interval);
            this._create();

            var d = new Date();
            console.log(d.toUTCString() + ': Reset chat.');
        }, 
        /*pause: function() {
            clearInterval(this.interval);
            this.interval = 0;

            var d = new Date();
            console.log(d.toUTCString() + ': Pause chat.');
        }, 
        resume: function() {
            var that = this;

            // Update automatically
            that._update();

            this.interval = setInterval(function() {
                that._update();
            }, this.options.speed);
            
            var d = new Date();
            console.log(d.toUTCString() + ': Restart chat.');
        }, */
        destroy: function() {
            this.element.text('');
            this.pause();
            $.Widget.prototype.destroy.call( this );
        },
        _chat: function () {
            var that = this;
            var $button = $('[name="chat_submit"]');
            var $message_input = $('[name="chat_message"]');
            var body = $message_input.val();
            var hex = $('[name="chat_color"]').val();
            if (body.length > 0) {
                $message_input.attr('disabled', 'disabled');
                $button.attr('disabled', 'disabled');
                $.ajax({
                    type: 'post',
                    url: Globals.root+'chat/create',
                    data: {
                        body: body, 
                        hex: hex
                    },
                    dataType: 'json',
                    async: true,
                    cache: false,
                    timeout: 50000, // Timeout in ms
                    success: function(data) {
                        $message_input.val('');

                        that._update();
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        var d = new Date();
                        // Messages caught by purifier will be counted as successful
                        console.log(d.toUTCString() + ': Could not post chat message.');
                    }, 
                    complete: function () {
                        // Regardless of outcome, enable the button again
                        $message_input
                            .attr('disabled', false)
                            .focus();

                        $button.attr('disabled', false);
                    }
                });
            }
        }, 
        _deleteMessage: function($message) {
            var that = this;
            var id = $message.attr('data-id');
            $.ajax({
                type: 'post',
                url: Globals.root+'chat/delete',
                data: {
                    id: id
                },
                dataType: 'json',
                async: true,
                cache: false,
                timeout: 50000, // Timeout in ms
                success: function(data) {
                    $message.closest('.chat_message').fadeOut(400, function() {
                        that._update();
                    });
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {}
            });
        }, 
        _update: function() {
            var that = this;
            $.ajax({
                type: 'get',
                url: Globals.root+'chat',
                data: {},
                dataType: 'html',
                async: true,
                cache: false,
                timeout: 50000, // Timeout in ms
                success: function(data) {
                    var d = new Date();
                    that.element.html(data);
                    $('[data-dismiss="chat_message"]').on("click", function() {
                        that._deleteMessage($(this));
                    });
                    console.log(d.toUTCString() + ': Updated chat messages.');
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    var d = new Date();
                    console.log(d.toUTCString() + ': Could not update chat messages.');
                    setTimeout(
                        function() {
                            that._update();
                        }, // Try again
                        15000 // ... After 15 seconds
                    );
                }
            });
        }
    });

    // var chat = $('#sidebar-chat').chat();

    $('#toggle-masthead').on('click', function(event) {
        var $masthead = $('#masthead-wrapper');
        var new_state = $masthead.hasClass('in') ? 'hide' : 'show';

        localStorage.setItem("dgShowBanner", new_state);
    });

    $('.progress-transitional .progress-bar').progressbar();  // bootstrap 3
});

function adjust_heights() {
    var $content = $('#content > .panel > .panel-body');
    var $sidebar = $('#main-sidebar');

    var $kennel_show_sidebar = $('.kennel-show-sidebar-bar');

    var $sidebar_chat = $('#sidebar-chat');

    $content.removeAttr('style');
    $sidebar_chat.removeAttr('style');

    var content_orig_height = $content.outerHeight(true);
    var sidebar_orig_height = $sidebar.outerHeight(true);

    if (sidebar_orig_height > content_orig_height) {
        $content.css('min-height', sidebar_orig_height+'px');
        $kennel_show_sidebar.css({'min-height':sidebar_orig_height+'px', 'height':sidebar_orig_height+'px'});

    } else {
        $sidebar_chat.css('min-height', ($sidebar_chat.innerHeight() + (content_orig_height - sidebar_orig_height))+'px');
        $kennel_show_sidebar.css({'min-height':content_orig_height+'px', 'height':content_orig_height+'px'});
    }
}

$(window).load(function () {
    adjust_heights();

    var $turns_in = $('#sidebar-next-turn-in');
    var val = $turns_in.text();
    var $turns_left = $('#sidebar-turns-left');
    var turns_left_val = $turns_left.text();
    var interval;

    if (turns_left_val < 5) {
        interval = setInterval(function() {
            var minutes, seconds;

            if (val <= 0) {
                if (val == 0) {
                    if (turns_left_val < 5) {
                        $turns_left.text(++turns_left_val);
                    }

                    if (turns_left_val >= 5) {
                        $('#sidebar-next-turn-in-wrapper').remove();
                        clearInterval(interval);
                    }
                }

                val = 30 * 60;
            } else {
                --val;
            }

            minutes = Math.floor(val / 60);
            seconds = val - minutes * 60;

            if (seconds < 10) {
                seconds = '0'+seconds;
            }

            $turns_in.text(minutes+':'+seconds);
        }, 1000);
    }
});

$(document).ready(function () {
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var id = $(e.target).attr('href');

        $(id).find('.progress .progress-bar').each(function () {
            var $this = $(this);
            var $parent = $(this).parent();
   

            $this.children('.progressbar-front-text').each(function () {
                var $front_text = $(this);

                parent_size = $parent.css('width');
                $front_text.css({width: parent_size});
            });
            
        });

        adjust_heights();
    });

    $('.modal').on('shown.bs.modal', function (e) {
        var $modal = $(e.target);

        $modal.find('.progress .progress-bar').each(function () {
            var $this = $(this);
            var $parent = $(this).parent();
   
            $this.children('.progressbar-front-text').each(function () {
                var $front_text = $(this);

                parent_size = $parent.css('width');
                $front_text.css({width: parent_size});
            });
            
        });
    });

    $('.collapse').on('shown.bs.collapse', function (e) {
        adjust_heights();
    });

    $('.collapse').on('hidden.bs.collapse', function (e) {
        adjust_heights();
    });

    $('.advance-turn-button').on('click', function () {
        $(this).button('loading');
    });

});

//Tooltip
$('a').tooltip('hide');

//Popover
$('button').popover('hide');
