jQuery(document).ready(function( $ ) {
    $('.panel-nav .icon').on('click', function(e){
        e.preventDefault();
        if ( $(this).parent().hasClass('responsive') ) {
            $('.panel-nav').removeClass('responsive')
        } else {
            $('.panel-nav').addClass('responsive')
        }
    })
    $('.form-field p:not(.hint), span.city').on('click', function(){
        var $this = $(this)
        $(".message").addClass('show').html("Content has been copied to your clipboard!");
        var $temp = $("<input>");;
        $("body").append($temp);
        $temp.val($(this).html()).select();
        document.execCommand("copy");
        $temp.remove();

        setTimeout(function() {
          $(".message").removeClass('show');
        }, 2500);
    });

    var extraSub = 0;
    $(".add-sub-keyword").on("click", function(e){
        e.preventDefault();
        var mainsub = $(".main-sub-keyword").html();
        var extraSub = $('.keyword').length + 1;
        var tempHtml = mainsub; 

        $(".extra-keywords").append('<div class="form-group keyword" id="extra-' + extraSub + '">' + tempHtml + '</div>');
        $("#extra-" + extraSub + " span").html(extraSub);
        $("#extra-" + extraSub + " label").attr('for', 'webnotik_keywords_subpages' + extraSub);
        $("#extra-" + extraSub + " input").attr('id', 'webnotik_keywords_subpages' + extraSub);
        $("#extra-" + extraSub + " input").attr('value', '');
        $("#extra-" + extraSub + " .k-main input").attr('value', 'City Name');
        $("#extra-" + extraSub + " .k-main input").attr('placeholder', 'Enter City Name');

        $(".main-sub-keyword").attr("data-new", extraSub - 1); //temporary deduct one.

        subKeywordRecount();
    });
    $(".extra-keywords").on("click", ".delete-cp", function(e){
        e.preventDefault();
        $(this).closest(".keyword").remove();
    })

    function subKeywordRecount() {
        var eSub = 2;
        $(".extra-keywords .keyword").each(function(){
            $(this).attr('id', 'extra-' + eSub);
            $("#extra-" + eSub + " span").html(eSub);
            $("#extra-" + eSub + " label").attr('for', 'webnotik_keywords_subpages' + eSub);
            $("#extra-" + eSub + " input").attr('id', 'webnotik_keywords_subpages' + eSub);
            eSub++;
        });
    }

    if( $(".wda_color_picker").length ){
        var params = { 
            change: function(e, ui) {
              $(e).closest(".form-field").find(".color_holder").attr("value", ui.color.toString());
              console.log("found!");
            },
          }
        $( '.wda_color_picker' ).wpColorPicker(params);
    }
	$("#get-cp").on("click", function(e) {
        e.preventDefault();
		console.log("get_city_pages");
		var data = {
            action: 'get_city_pages',
            focus_key: 'We Buy Houses'
        };
        
        var json_count = 0;
        $.getJSON( get_city_pages_data.ajaxurl, data, function( json ) {
        	$(".message").html("").attr("class", '').addClass("message");
            if ( json.success ) {
                json_data = json["data"]
                console.log(json_data)
                $.each(json_data, function(i, item) {
                	json_count++;
				    var city1 = $(".main-sub-keyword .k-main input").val()
                    if(city1 == "") {
                        $(".main-sub-keyword .k-main input").attr('value', item.PageName);
                        $(".main-sub-keyword .k-value input").attr('value', item.PageURL);
                    } else {
                    	if( $("#extra-" + json_count).length ) {
                    		//do nathing
                    	} else {
                    		$(".add-sub-keyword").trigger("click");
                    	}
                        $("#extra-" + json_count + " .k-main input").attr('value', item.PageName);
                        $("#extra-" + json_count + " .k-value input").attr('value', item.PageURL);
                    }
                    $(".message").addClass("success").append("Added City #" + (i + 1) + ": " + item.PageName + " <br>");
				});
            } else {
            	console.log(json.data);
            }
            console.log("json is still playing...")
        } );
	})

    $(".clone-cp").on("click", function(e) {
        e.preventDefault();
        $this = $(this);
        gUrl = $(this).closest(".keyword").find(".k-value input").val();
        gTitle = $(this).closest(".keyword").find(".k-main input").val();
        $target = $(this).closest('.keyword').attr('id');



        var data = {
            action: 'clone_city_page',
            given_url: gUrl,
            given_title: gTitle
        };

        if($target == "") {
            rei_message_show("The target url of this city should not be empty", "error");
        } else {
            $.getJSON( get_city_pages_data.ajaxurl, data, function( json ) {
                $(".message").html("").attr("class", '').addClass("message");
                if ( json.success ) {
                    json_data = json["data"];
                    console.log(json_data);
                    rei_message_show("Successfully clone city page. See new city details below", "success");

                    //let's create a new city page field
                    $(".add-sub-keyword").trigger( "click" ); 
                    //lets display the data
                    get_extra_id = $(".main-sub-keyword").data("new");
                    $("#extra-" + get_extra_id + " .k-main input").val(json.data["post_title"])
                    $("#extra-" + get_extra_id + " .k-value input").val(json.data["post_name"])
                } else {
                    
                    var json_data = json.data;
                    $.each(json_data, function(i, item) {
                        rei_message_show("See console log", "error");
                    }); 
                }
            } );

            console.log(gUrl);
        }
        
    });

    $(".verify-cp").on("click", function(e) {
        e.preventDefault();

        var url = $(this).closest('.input-group').find('input').attr('value');
        console.log(url); 
        window.open( 'http://www.google.com/search?q="' + url + '"', '_blank');
    })

    $(".visit-cp").on("click", function(e) {
        e.preventDefault();

        var url = $(this).closest('.input-group').find('input').attr('value');
        console.log(url); 
        window.open( url , '_blank');
    })

	$(".rename-cp").on("click", function(e) {
		e.preventDefault();
        $this = $(this);
		gUrl = $(this).closest(".keyword").find(".k-value input").val();
		gTitle = $(this).closest(".keyword").find(".k-main input").val();
		$target = $(this).closest('.keyword').attr('id');

		var data = {
            action: 'rename_city_pages',
            given_url: gUrl,
            given_title: gTitle
        };
        $.getJSON( get_city_pages_data.ajaxurl, data, function( json ) {
        	$(".message").html("").attr("class", '').addClass("message");
            if ( json.success ) {
            	json_data = json["data"];
            	console.log(json_data);
                rei_message_show("Successfully renamed. New URL: " + json.data["post_name"], "success");

                $this.closest(".keyword").find(".k-value input").val(json.data["post_name"])
            } else {
            	
            	var json_data = json.data;
            	$.each(json_data, function(i, item) {
                    rei_message_show("Looks like the URL is not valid. Please check if the url is correct. Additional info: " + item, "error");
            	}); 
            }
        } );

        console.log(gUrl);
	})

    $("#save-styles").on("click", function(e) {
        e.preventDefault();
        var data = {
            action: 'generate_new_rei_style'
        }
        $.getJSON( get_city_pages_data.ajaxurl, data, function( json ) {
            console.log(json.data);
            rei_message_show("Successfully created a stylesheet base on branding configurations.", "success");
        } );
    })

    function rei_message_show(msg, extra_class) {
        $(".message").attr("class", "").addClass("message show " + extra_class).html(msg);

        setTimeout(function() {
          $(".message").removeClass('show');
        }, 2500);
    }

    $(".map-try").on("click", function(e) {
        var ckey = $("#city_keyword").val();
        $(".map-try-wrapper").html('<iframe src="https://www.google.com/maps/embed?pb=Kent,+OH" width="100%" height="300" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>');
    });
});