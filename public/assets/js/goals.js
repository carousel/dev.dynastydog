$(function () {
    var progress=$("#indiv-ch-next-level .progress-bar");
    var label=progress.attr("data-label");
    progress.progressbar({
        display_text:"center",
        use_percentage:false,
        amount_format:function(e,i){
            return (label !==undefined ? label : e);
        }
    });
	
    $("#cc-start-date").datetimepicker();

    $("#cc-end-date").datetimepicker({
        pickTime: false
    });

	$("[name='edit_personal_goal']").on('click', function(e) {
	    var edit = $(this);
	    var parent = edit.parent();
	    var text = parent.children("[id^='personal-goal-body-text-']");
	    var input = parent.children("[id^='personal-goal-body-input-']");
	    var save = parent.children("[name='save_personal_goal']");
	    var cancel = parent.children("[name='cancel_edit_personal_goal']");

	    edit.addClass('hide');
	    text.addClass('hide');
	    input.removeClass('hide');
	    cancel.removeClass('hide');
	    save.removeClass('hide');
	});

	$("[name='cancel_edit_personal_goal']").on('click', function(e) {
	    var cancel = $(this);
	    var parent = cancel.parent();
	    var text = parent.children("[id^='personal-goal-body-text-']");
	    var input = parent.children("[id^='personal-goal-body-input-']");
	    var save = parent.children("[name='save_personal_goal']");
	    var edit = parent.children("[name='edit_personal_goal']");

	    edit.removeClass('hide');
	    text.removeClass('hide');
	    input.addClass('hide');
	    cancel.addClass('hide');
	    save.addClass('hide');

	    input.val(text.text());
	});

});