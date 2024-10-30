jQuery(function($) {
    var debug = 0;
    var crolog = function(a,b){ if ( debug == 1 ) console.log("lightbox - "+a, b || "");};

    var options = chroma_lightbox_public_vars.options;
    var img_sizes = chroma_lightbox_public_vars.img_sizes;
    var settings = options.settings;
    var gallery_active = settings["gallery-active"];
    var thumbs_active = settings["thumbs-active"];
    var frame_active = settings["frame-active"];
    var frame_width = settings["frame-width"];
    var frame_color = settings["frame-color"];
    var frame_opacity = settings["frame-opacity"];
    var frame_active_tn = settings["frame-active-tn"];
    var frame_width_tn = settings["frame-width-tn"];
    var frame_color_tn = settings["frame-color-tn"];
    var frame_opacity_tn = settings["frame-opacity-tn"];

    crolog("img_sizes", img_sizes);

    var img_height;
    var img_width;
    var drag_offset;
    var current_article;
    var img_links;
    var spinner;
    var plugin_path = chroma_lightbox_public_vars.plugin_path;
    var inputtype;
    var xxxx;
    var slideshow = {
        drag : 0,
        stop: 0,
    };

    var loading = 0;

    function hexToRgb(hex) {
        var bigint = parseInt(hex, 16);
        var r = (bigint >> 16) & 255;
        var g = (bigint >> 8) & 255;
        var b = bigint & 255;

        return r + "," + g + "," + b;
    }

    // apply style settings to lightbox
    var apply_settings = function(){
        $("#clb-exit-box").css({
            "background-color":settings["background-color"],
            "opacity":settings["background-opacity"],
        });
        $("#clb-thumbs-table a").css({
            "border-color":settings["frame-color"],
        });

        if (frame_active) {
            var hex_color = settings["frame-color"].replace(/\W/g, '');
            var dec_color = hexToRgb(hex_color);
            $("#clb-frame").css({
                "border-style":"solid",
                "border-color":"rgba("+dec_color+", "+frame_opacity+")",
                "border-width":frame_width+"px",
            });
        }

        if (frame_active_tn) {
            var hex_color_tn = settings["frame-color-tn"].replace(/\W/g, '');
            var dec_color_tn = hexToRgb(hex_color_tn);
            $("#clb-thumbs-table a").css({
                "border-style":"solid",
                "border-color":"rgba("+dec_color_tn+", "+frame_opacity_tn+")",
                "border-width":frame_width_tn+"px",
            });
            $("#clb-thumbs-table a.clb-thumb-active").css({
                "border-width":(parseInt(frame_width_tn)+3)+"px",
            });
        }


    };

    // add/remove gallery features
    var gallery_nav_update = function(){
        if (gallery_active) {
            $("#clb-button-prev, #clb-button-next").show();
            if (thumbs_active) {
                $("#clb-thumbs").show();
            } else {
                $("#clb-thumbs").hide();
            }
        } else {
            $("#clb-button-prev, #clb-button-next, #clb-thumbs").hide();
        }
    };

    // add active class to current image thumb
    var active_thumb_class = function(atag){
        var thumbs = $("#clb-thumbs a");
        thumbs.removeClass("clb-thumb-active");
        $("#clb-thumbs a[href='"+atag.attr('href')+"']").addClass("clb-thumb-active");
    };

    // dynamically create thumnails from post/page images
    var populate_thumbs = function(atag) {
        if (atag.parents("#clb-thumbs").length > 0) {
            active_thumb_class(atag);
            apply_settings();

            return;
        }
        var article;

        article = atag.parents(settings["wrapper-element"]).first();


        var links = article.find("img").parents("a");

        if (article.hasClass("clb-current-article")) {
            crolog("SAME");
        } else {
            crolog("NOT SAME");
            $(".clb-current-article").removeClass("clb-current-article");
            article.addClass("clb-current-article");
            current_article = article;
            $("#clb-thumbs-table").html("");

            if (links.length > 1) {

                gallery_nav_update();

                $.each(links, function() {

                    $("#clb-thumbs-table").prepend($(this)[0].outerHTML);
                    var added = $("#clb-thumbs-table a").first();
                    added.removeAttr("style");
                    added.find("*").not("img").remove();
                    var src = added.find("img").first().attr("src");
                    added.css({
                        "background-image" : "url("+src+")",
                    });

                });

            } else {
                $("#clb-button-prev, #clb-button-next, #clb-thumbs").hide();
            }





        }
        active_thumb_class(atag);
        apply_settings();
    };





    // create lightbox html
    var build_lightbox = function() {


        var url = plugin_path+"images/spinner.png?time=" + new Date().getTime();

        var s = "";

        s += "<div id='clb-container'>";
        s += "<div id='clb-exit-box'></div>";
        s += "<div id='clb-spinner'>";
        //s += "<img id='clb-spinner-img' src=''/>";
        s += "</div>";
        s += "<div id='clb-frame'>";
        s += "<div id='clb-main-image-crop'>";
        s += "<div id='clb-main-image-container'>";
        s += "<img id='clb-main-image' src=''>";
        s += "</div>";
        s += "</div>";
        s += "<div id='clb-caption'></div>";

        s += "<div id='clb-controls'>";
        s += "<div id='clb-button-prev' class='clb-button'></div>";
        s += "<div id='clb-button-next' class='clb-button'></div>";
        s += "<div id='clb-button-close' class='clb-button'></div>";
        s += "</div>";

        s += "</div>";
        s += "<div id='clb-thumbs'>";
        s += "<div id='clb-thumbs-table'>";
        s += "</div>";
        s += "</div>";

        s += "</div>";


        $("body").prepend(s);



        gallery_nav_update();

        spinner = $("#clb-spinner");


    };

    // dynamically adjust lightbox size and orientation on window change
    var clb_resize = function() {
        crolog("clb_resize run");
        var new_img_height = img_height;
        var new_img_width = img_width;
        var win_width = $(window).width() * 1;
        var win_height = $(window).height() * 1;
        //crolog("win_width", win_width);
        //crolog("win_height", win_height);
        //crolog("img_width", img_width);
        //crolog("img_height", img_height);


        if (img_height < win_height && img_width < win_width ) {
            crolog("image smaller than screen");
            $("#clb-frame").css({
                height:img_height,
                width:img_width
            });
            $("#clb-frame #clb-main-image").css({
                height:img_height,
                width:img_width
            });
        }

        if (img_height > win_height ) {
            crolog("image taller than screen");
            $("#clb-frame #clb-main-image").css({
                height:win_height,
                width:"auto"
            });
            new_img_height = $("#clb-frame #clb-main-image").height();
            new_img_width = $("#clb-frame #clb-main-image").width();
            $("#clb-frame").css({
                height:new_img_height,
                width:new_img_width
            });
        }

        if (new_img_width > win_width ) {
            crolog("image wider than screen");
            $("#clb-frame #clb-main-image").css({
                width:win_width,
                height:"auto"
            });
            new_img_height = $("#clb-frame #clb-main-image").height();
            new_img_width = $("#clb-frame #clb-main-image").width();
            $("#clb-frame").css({
                height:new_img_height,
                width:new_img_width
            });
        }

        var but = {
            prev : $("#clb-button-prev"),
            next : $("#clb-button-next"),
            close : $("#clb-button-close"),
        };



        // move next/previous buttons inside image frame if window becomes narrow
        if (new_img_width > win_width - ((but.prev.width()+frame_width) * 2)) {
            var os = but.prev.offset().left;
            but.prev.css({
                "left": 0,
            });
            but.next.css({
                "right": 0,
            });
            but.close.css({
                "right": 0,
            });
        } else {
            but.prev.css({
                "left": -(but.prev.width()+frame_width),
            });
            but.next.css({
                "right": -(but.prev.width()+frame_width),
            });
            but.close.css({
                "right": -(but.prev.width()+frame_width),

            });
        }


        $("#clb-frame").css({
            //opacity:1,
        });

    };

    var bind_img_load = function(){
        $("#clb-frame #clb-main-image" ).load(function(img_loaded){


            spinner.fadeOut(500);

            img_height = img_loaded.currentTarget.naturalHeight;
            img_width = img_loaded.currentTarget.naturalWidth;

            setTimeout(function(){
                clb_resize();
            },0);
            $("#clb-frame").css({
                opacity:1,
            },500);

            $("#clb-main-image").fadeIn(500);
            if ($("#clb-caption").text() !== "" ) {
                $("#clb-caption").fadeIn(1000);

            }

            loading = 0;
            //$(this).off( "load" );



        });
    };


    // reset slideshow.width variable
    var reset_slideshow = function(){
        slideshow.width = $("#clb-main-image-container").width();
    };

    $(document).ready(function () {

        crolog("LIGHTBOX ACTIVE.");

        build_lightbox();
        apply_settings();
        bind_img_load();



        if (gallery_active) {
            $(document).on("mousedown touchstart", "#clb-main-image-container", function(e){
                e.preventDefault();
                if (slideshow.stop === 1) return;
                crolog("slideshow - mouse down");
                reset_slideshow();
                slideshow.frame = $("#clb-main-image-container");

                //slideshow.temp = slideshow.frame.clone();
                //slideshow.temp.attr("id", "clb-temp-container");
                //slideshow.temp.find("img").attr("id", "clb-temp-image");
                //slideshow.temp.hide();
                //$("#clb-main-image-crop").append(slideshow.temp[0].outerHTML);
                slideshow.temp = $("#clb-main-image-crop").find("div").last();
                slideshow.temp = slideshow.frame;

                setTimeout(function(){
                    slideshow.frame.show();
                    slideshow.frame.css({
                        //opacity:0,
                    });
                },100);


                if (e.type == "mousedown") {
                    inputtype = "mouse";
                } else if (e.type == "touchstart") {
                    inputtype = "touch";
                }

                slideshow.drag = 1;
                slideshow.anchor = (inputtype == "mouse") ? e.screenX : e.originalEvent.touches[0].screenX;
                slideshow.offset = 0;
                slideshow.dir = 0;
                slideshow.stop = 1;



            });

            $(document).on("mouseup touchend", function(e){
                if (slideshow.drag == 1) {
                    e.preventDefault();
                    crolog("slideshow - mouse up");
                    slideshow.drag = 0;
                    slideshow.drop = e.screenX;
                    slideshow.frame.addClass("clb-slideshow-trans");






                    if (slideshow.offset > -10 && slideshow.offset < 10 ) {
                        slideshow.dir = 0;
                    }

                    if (slideshow.dir === 0) {
                        if (e.offsetX > slideshow.width/2) {
                            slideshow.dir = 1;
                            slideshow.offset = -100;
                        } else {
                            slideshow.dir = 2;
                            slideshow.offset = 100;

                        }

                    } else {
                        spinner.fadeIn(50);

                    }

                    if (slideshow.dir == 2) {

                        if (slideshow.offset > 0) {



                            slideshow.frame.css({
                                "transform":"translateX("+slideshow.width+"px)",
                            });
                            setTimeout(function(){
                                $("#clb-button-prev").trigger("click");

                            },700);


                        } else {
                            slideshow.frame.css({
                                "transform":"translateX(0px)",
                            });
                        }

                    } else if (slideshow.dir == 1) {
                        if (slideshow.offset < 0) {
                            slideshow.frame.css({
                                "transform":"translateX(-"+slideshow.width+"px)",
                            });
                            setTimeout(function(){
                                $("#clb-button-next").trigger("click");

                            },700);

                        } else {
                            slideshow.frame.css({
                                "transform":"translateX(0px)",
                            });
                        }
                    } else if (slideshow.dir === 0) {

                    }


                    setTimeout(function(){
                        //slideshow.temp.remove();
                        slideshow.stop = 0;
                        slideshow.frame.css({
                            "transform":"translateX(0px)",

                        });
                        slideshow.frame.removeClass("clb-slideshow-trans");

                    },700);



                }
            });

            $(document).on("mousemove touchmove", function(e){
                if (slideshow.drag == 1) {
                    e.preventDefault();
                    crolog("slideshow - moving");

                    slideshow.curpos = slideshow.offset;
                    slideshow.offset = e.screenX - slideshow.anchor;
                    slideshow.offset = (inputtype == "mouse") ? e.screenX - slideshow.anchor : e.originalEvent.touches[0].screenX - slideshow.anchor;

                    if (slideshow.curpos > slideshow.offset) {
                        slideshow.dir = 1;
                    } else if (slideshow.curpos < slideshow.offset) {
                        slideshow.dir = 2;
                    }

                    slideshow.curpos = slideshow.offset;


                    slideshow.temp.css({
                        "transform":"translateX("+slideshow.offset+"px)",
                        //"opacity":slideshow.offset/1000,
                    });
                }
            });
        }

        $(document).on("click", settings.target, function(e){
            var href = $(this).attr("href");
            var match = href.match(/jpg|gif|png|bmp/gi);

            // If link contains no images, then return
            if ($(this).children("img").length != 1 || !match ) return;

            e.preventDefault();

            if ( loading == 1 ) return;

            crolog(">>>>>>>>>>>>>>> Valid Image Clicked");

            loading = 1;

            spinner.fadeIn(50);

            var aclicked = $(this);

            populate_thumbs(aclicked);

            // If link clicked is lightbox thumbnail, redefine aclicked as matching document link
            if (aclicked.parents("#clb-thumbs").length > 0) {
                crolog("lightbox thumbnail clicked");
                aclicked = current_article.find("a[href='"+$(this).attr('href')+"']");
            }

            // Caption stuff
            var caption = aclicked.find(".clb-caption-text");
            crolog("caption",caption);
            if (caption.length > 0) {
                caption = caption.text();

            } else {
                caption = aclicked.parent().find(".wp-caption-text").html();
                var img = aclicked.find("img[aria-describedby]");
                if (img.length > 0) {
                    caption = $("#"+img.attr("aria-describedby")).html();
                }
            }


            $("#clb-caption").hide().empty().html(caption);



            $("#clb-container").fadeIn(250);
            $("#wrapper").css({
                filter: 'blur(3px)',
            });

            //$("#clb-frame #clb-main-image-container").html("<img id='clb-main-image' src=''><div class='clb-caption'>"+""+"</div>");
            $("#clb-main-image").hide();
            var alt;
            var src = $(this).children("img").attr("src");


            var myRegexp = /(.*)\/(.*)(?:-\d*x\d*)\.(jpeg|jpg|png|gif)/g;
            var matches = myRegexp.exec(src);
            crolog("matches", matches);

            src = src.replace(/-\d*x\d*\.(jpeg|jpg|png|gif)/g,".$1");
            src = matches[1] + "/" + matches[2] + "." + matches[3];
            src = href;


            var count = 0;

            $("#clb-frame #clb-main-image" ).attr("src", src);

        });

        $(document).on("click", ".clb-button", function(){
            crolog("button clicked");
            var cur_img = $(".clb-thumb-active");


            var id=$(this).attr("id");
            if (id=="clb-button-prev") {

                if (cur_img.prev().length > 0 ) {
                    cur_img = cur_img.prev();
                } else {
                    cur_img = $("#clb-thumbs img").last();
                }

            } else {

                if (cur_img.next().length > 0 ) {
                    cur_img = cur_img.next();
                } else {
                    cur_img = $("#clb-thumbs img").first();
                }

            }

            cur_img.trigger("click");


        });

        $(document).on("click", "#clb-exit-box, #clb-button-close", function(){
            $("#clb-container").fadeOut(250);
            $("#clb-frame").height(300);
            $("#clb-frame").width(300);
            $("#clb-frame").css({
                opacity:0,
            });
            $("#wrapper").css({
                filter: 'blur(0px)',
            });
        });

        $(window).on("resize", function(){
            clb_resize();
        });
    });

});
