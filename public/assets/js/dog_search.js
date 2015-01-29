$(function() {
    $("#search-dogs").off("submit").on("submit", function (e) {
        var form = $(this);
        var submit_btn = $("[name='search']");

        submit_btn.button("loading");
    });
    
    $('.range-bounds').tooltip();

    $(".progress .progress-bar").each(function(){
        var e=$(this);
        var t=e.attr("data-formatted");
        e.progressbar({
            display_text:"center",
            use_percentage:false,
            amount_format:function(e,i){
                return t;
            }
        });
    });
});