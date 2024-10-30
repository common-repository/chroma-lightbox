jQuery(function ($) {
    var debug = 0;
    var crolog = function(a,b){ if ( debug == 1 ) console.log("lightbox - "+a, b || "");};

    var options = chroma_lightbox_settings_vars.options;
    var settings = options.settings;

    crolog("Settings", settings);

    var dependencies = function(){
        $("[data_dep]").each(function(){
            var dep = $(this).attr("data_dep");
            var parent = $("#"+dep);
            var type = $(this).attr("type");
            if (parent.attr( "checked" )) {
                if (type == "checkbox") {
                    $(this).prop("disabled", false);
                } else if (type == "text") {
                    $(this).prop("readonly", false);
                }

                $(this).parents("tr").first().css({"opacity":1,"height":"5px"});
                crolog("checked");

            } else {
                crolog("not checked");
                if (type == "checkbox") {
                    $(this).prop("disabled", true);
                } else if (type == "text") {
                    $(this).prop("readonly", true);
                }
                $(this).parents("tr").first().css({"opacity":0.5});

            }
        });
    };

    $(document).ready(function () {

        $('#background-color, #frame-color, #frame-color-tn').wpColorPicker();

        $("input[type=checkbox]").change(function(){
            dependencies();
        });

        dependencies();

    }); // doc redy close

} );
