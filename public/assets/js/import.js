$(document).ready(function() {

	$("#regular-import").on('submit', function (e) {
	    var submit_btn = $("[name='import_dog']");
	    submit_btn.button("loading");
	});

});