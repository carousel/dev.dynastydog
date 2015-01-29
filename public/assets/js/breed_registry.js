$(function() {
    $("#search-breed-registry").off("submit").on("submit", function (e) {
        var form = $(this);
        var submit_btn = $("[name='search']");

        submit_btn.button("loading");
    });
});