function adjust_dog_rows() {
    var counter = 0;
    var row = 0;
    var cur_parent = 0;

    $('.tab-pane.active').each(function () {
        $(this).find('[data-dog-char-parent]').each(function () {
            var sub_cat = $(this);
            var parent_id = sub_cat.attr('data-dog-char-parent');

            if (cur_parent != parent_id) {
                counter = row = 0;

                cur_parent = parent_id;
            }

            row = (counter % 2) ? row : row + 1;

            // Check if this exists
            var match = $('[data-dog-char-parent='+parent_id+'][data-dog-char-row='+row+']');

            if (match[0] != undefined) {
                var sub_cat_body = sub_cat.find('.panel-body');

                var sub_cat_height = sub_cat_body.height();

                var matched_body = match.find('.panel-body');

                var matched_height = matched_body.height();

                if (matched_height >= sub_cat_height) {
                    sub_cat_body.height((matched_height)+'px');
                } else {
                    matched_body.height((sub_cat_height - 1)+'px'); // Subtract 1 because FF is dumb
                }
            }

            sub_cat.attr('data-dog-char-row', row);

            ++counter;
        });
    });
}

$(document).ready(function () {
    $('button').tooltip('hide');

    adjust_dog_rows();

    $('[data-test]').on('click', function(event) {
        var btn = $(this);

        if (confirm('Are you sure you want to perform this test?')) {
            var dog  = btn.attr('data-dog');
            var test = btn.attr('data-test');

            btn.button('loading');

            $.ajax({
                type: 'post',
                url: Globals.root+'dog/test/perform',
                data: {
                    dog: dog, 
                    test: test
                },
                dataType: 'json',
                async: true,
                cache: false,
                timeout: 50000, // Timeout in ms
                success: function(json) {
                    $('[data-test]').each(function() {
                        $(this).attr('disabled', 'disabled');

                        if ($(this).attr('data-test') == test) {
                            $(this).parent().html(json.rendered);

                            if (json.show_tutorial == true) {
                                // Reload the page
                                window.location.reload();
                            }
                        }
                    });

                    adjust_dog_rows();
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    btn.button('reset');
                    alert('Sorry, there was an issue with loading your test results. Please try again');
                }
            });
        } else {
            btn.button('reset');
        }
    });
});

$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    adjust_dog_rows();
});

$('[data-loading-text]').click(function () {
    var btn = $(this);
    btn.button('loading');
});