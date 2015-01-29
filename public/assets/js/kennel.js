function hide_kennel_sidebar()
{
    var kennel_show_sidebar = $('.kennel-show-sidebar');
    var content = $('#content');

    content.removeClass('col-md-6');
    content.addClass('col-md-8');

    $.each(kennel_show_sidebar, function() {
        $(this).collapse('show');
    });

    erase_cookie('dgshowhidekennelsidebar');
    create_cookie('dgshowhidekennelsidebar','hide',365);
}

function show_kennel_sidebar()
{
    var kennel_sidebar = $('#kennel-sidebar');
    var kennel_show_sidebar = $('.kennel-show-sidebar');
    var content = $('#content');

    $.each(kennel_show_sidebar, function() {
        $(this).collapse('hide');

        $(this).on('hidden.bs.collapse', function (ee) {
            ee.stopPropagation();
            content.removeClass('col-md-8');
            content.addClass('col-md-6');
            kennel_sidebar.collapse('show');
        });
    });


    kennel_sidebar.on('shown.bs.collapse', function (ee) {
        kennel_sidebar.removeAttr('style');
    });

    erase_cookie('dgshowhidekennelsidebar');
    create_cookie('dgshowhidekennelsidebar','show',365);
}

$(document).ready(function() {
    var content = $('#content');
    
    var kennel_sidebar = $('#kennel-sidebar');
    
    var kennel_show_sidebar = $('.kennel-show-sidebar');

    var checked_dogs = [];

    var cookie = read_cookie('dgshowhidekennelsidebar');

    if ( ! cookie) {
        create_cookie('dgshowhidekennelsidebar','show', 365);
    } else if (cookie == 'hide') {
        $('#kennel-sidebar').removeClass('in');

        hide_kennel_sidebar();
    }

    kennel_sidebar.on('hidden.bs.collapse', function (e) {
        e.stopPropagation();

        hide_kennel_sidebar(e);
    });

    $('#kennel-sidebar .panel-collapse').on('hidden.bs.collapse', function (e) {
        e.stopPropagation();
    });

    $.each(kennel_show_sidebar, function() {
        $(this).on('click', function (e) {
            e.stopPropagation();

            show_kennel_sidebar(e);
        });
    });


    $(".kennel_dog_checkbox").on('change', function (e) {
        var checkbox = $(this);

        var id = checkbox.attr('value');

        var key = checked_dogs.indexOf(id)

        if (checkbox.prop('checked')) {
            if (key == -1) {
                checked_dogs.push(id);
            }
        } else {
            if (key > -1) {
                checked_dogs.splice(key, 1);
            }
        }

        $('.selected-dog-ids').val(checked_dogs.toString());
    });

    $('[name="edit_tab"]').on('click', function (e) {
        var dropdown = $("#kennel-tab-settings-tab-id");
        var selected = dropdown.find(":selected");
        var id = selected.val();

        var active = $('.kennel-tab-settings-tab.active');
        var new_active = $('#kennel-tab-settings-tab-'+id+'-info');

        if (active != undefined) {
            active.addClass('hide');
            active.removeClass('active');
        }

        if (new_active != undefined) {
            new_active.addClass('active');
            new_active.removeClass('hide');
        }
    });
});