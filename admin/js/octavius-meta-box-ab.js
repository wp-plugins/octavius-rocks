/*
 * Attaches the image uploader to the input field
 */
jQuery(document).ready(function($){
 
    // Instantiates the variable that holds the media library frame.
    var meta_image_frame;
 
    // Runs when the image button is clicked.
    $('.octavius-rocks-variants').on("click", ".octavius-ab-image", function(e){

        var $item = $(this).closest(".octavius-rocks-variant");
        var $img_id = $item.find('.octavius-ab-image-id');
        var $img = $item.find('img');

 
        // Prevents the default action from occuring.
        e.preventDefault();

        // Sets up the media library frame
        meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
            title: "Teaser image",
            button: { text:  'OK' },
            library: { type: 'image' }
        });

        // Runs when an image is selected.
        meta_image_frame.on('select', function(){
 
            // Grabs the attachment selection and creates a JSON representation of the model.
            var media_attachment = meta_image_frame.state().get('selection').first().toJSON();
            // Sends the attachment URL to our custom image input field.
            $img_id.val(media_attachment.id);
            $img.attr("src", media_attachment.sizes.thumbnail.url);
        });
 
        // Opens the media library frame.
        meta_image_frame.open();
    });


    /**
     * octavius admin socket plugin
     */
    var OctaviusPostVariants = function(){
        var oc;
        var socket;
        var $metabox;
        var $wrapper;
        var post_id;
        var variants = {};
        var $report_wrapper;
        this.init = function(octavius){
            oc = octavius;
            socket = octavius.socket;
            $(".octavius-rocks-variant-label").each(function(i,e){
                variants[e.getAttribute("data-slug")] = e.getAttribute("data-name");
            });
            $metabox = $("#octavius_rocks_ab_results");
            $wrapper = $metabox.find(".octavius-rocks-ab-results");
            $report_wrapper = $metabox.find('.octavius-rocks-ab-report');
            post_id = $wrapper.attr("data-post-id");
            $metabox.on("click",".octavius-rocks-refresh", this.refresh.bind(this) );
            $metabox.on("change",".octavius-rocks-select",this.refresh.bind(this));
            $wrapper.on("click", ".octavius-rocks-ab-result", this.select_variant);
            this.get_variants();
            socket.on("update_variants_hits", this.update_variants_hits);
            //get ab report 
            //TODO only for posts younger than render tracking
            socket.on("update_get_ab_report", this.update_ab_report);
            this.get_ab_report();
        }
        this.select_variant = function(e){
            var $this = $(this);
            function ajax(value, success){
                $.ajax({
                    url: ajaxurl+"?action=set_post_ab_variant",
                    dataType: "json",
                    method: "POST",
                    data: {pid: post_id, variant_slug: value},
                    success: success,
                    error: function(a,b,c){
                        console.log([a,b,c]);
                    }
                });
            }
            if($this.hasClass("octavius-rocks-variant-selected")){
                ajax("",function(_data){
                    if(_data.success){
                        $this.removeClass("octavius-rocks-variant-selected");
                    }
                });
                return; 
            } 
            $this.siblings().removeClass("octavius-rocks-variant-selected");
            ajax($this.attr("data-slug"),function(_data){
                if(_data.success){
                    $this.addClass("octavius-rocks-variant-selected");
                }
            });
            
        }
        this.get_variants = function(){
            if(!oc.admincore.is_ready){
                setTimeout(this.get_variants.bind(this), 100);
                return;
            }
            
            var data = {content_id:post_id, filters:[]};
            /**
             * event type filter
             */
            var event_type = this.get_event_type();
            if(typeof event_type != typeof undefined
                && event_type != ""){
                data.filters.push({name:"event_type", value:event_type});
            }
            /**
             * referrer filter
             */
            var referrer = this.get_referrer();
            if(typeof referrer != typeof undefined
                && referrer != ""){
                data.filters.push({name:"referer_domain",value:referrer});
            }
            socket.emit("get_variants_hits", data);
        }
        this.update_variants_hits =  function(data){
            var wrapper_width = $wrapper.outerWidth(true);
            $wrapper.empty();
            if(data.overall <=0){
                $wrapper.append("<p>No data found</p>");
               return; 
            } 
            var values = {};
            var offset = 0;
            var selected_slug = $wrapper.data("selected-slug");
            $.each(data.variants, function(_slug, _hits){
                var percent = (_hits/data.overall)*100;
                var right = 100-(percent+offset);
                var percent_readable = Math.floor(percent);
                var title = _slug;
                var selected_class = "";
                if(selected_slug == _slug){
                    selected_class = " octavius-rocks-variant-selected";
                }
                if(typeof variants[_slug] != typeof undefined) title = variants[_slug];
                var $div = $("<div></div>")
                    .addClass("octavius-rocks-ab-result"+selected_class)
                    .css("left", offset+"%")
                    .css("right", right+"%")
                    .attr("title", title+" "+percent_readable+"% Hits: "+_hits)
                    .attr("data-slug", _slug)
                    .prependTo($wrapper);
                var $percent = $("<span></span>")
                    .addClass("octavius-rocks-ab-result-values")
                    .html(title+"<br>"+percent_readable+"% Hits: "+_hits)
                    .appendTo($div);
                offset = offset+percent;
            });
        }
        //check if post is newer than date when render tracking was enabled
        this.is_rendered_tracked = function(releaseDateString){
	        var theDateString = '09-10-2015';
	        var theDate = new Date(theDateString);
			var releaseDate = new Date(releaseDateString);
			if(theDate <= releaseDate){
				return true;
			}
	        return false;
        }
        this.show_too_old_notice = function(){
	        $report_wrapper.html("Dieser Eintrag ist zu alt. Es liegen keine Daten zur Berechnung der Signifikanz vor.");
        }
        this.get_ab_report = function(){
	        //TODO use one class doing this. Use class in octavius_grid
	        //check if post is released after the day rendering was tracked
	        if(!this.is_rendered_tracked($report_wrapper.attr("data-octavius-postdate"))){
		        this.show_too_old_notice();
				return;
		    }
	        if(!oc.admincore.is_ready){
                setTimeout(this.get_ab_report.bind(this), 100);
                return;
            }
            socket.emit("get_ab_report", {content_id:post_id});
        }
        this.update_ab_report =  function(data){
            //build html
            var html = "<h2>AB-Test Ergebnisse</h2>";
		    html += "<strong>Rendered</strong>";
		    for(var variant in data.rendered_total){
			    var number = data.rendered_total[variant];
			    html += "<br>"+variant+": "+number;
		    }
		    html += "<br><br><strong>Clicked</strong>";
		    for(var variant in data.clicked_total){
			    var number = data.clicked_total[variant];
			    html += "<br>"+variant+": "+number;
		    }
		    html += "<br><br><strong>Conversion Rate</strong>";
		    for(var variant in data.rendered_total){
			    var numberRendered = data.rendered_total[variant];
			    var numberClicked = data.clicked_total[variant];
			    var conversionRate = "No Data available";
			    if(!isNaN(numberClicked) && !isNaN(numberRendered) && numberRendered > 0){
			    	var conversionRate = numberClicked/numberRendered*100+"%";
			    }
			    html += "<br>"+variant+": "+conversionRate;
		    }
		    //significance
		    if(data.significance){
			    html += "<br><br><strong>Signifikanz</strong>";
			    if(data.significance.error){
				    html += "<br>Keine Aussage über die Signifikanz möglich (Datengrundlage zu gering).";
			    }
			    if(data.significance['95']){
				     html += "<br>Die Daten sind statistisch signifikant.";
			    }
			    if(data.significance['99']){
				     html += "<br>Die Daten sind statistisch höchst signifikant.";
			    }
			    if(data.significance['not']){
				     html += "<br>Die Daten sind statistisch nicht signifikant.";
			    }
		    }
		    //add html to dom
		    $report_wrapper.html(html);
            
        }
        this.get_event_type = function(){
            return $metabox.find(".octavius-rocks-select-event-type").val();
        }
        this.get_referrer = function(){
            return $metabox.find(".octavius-rocks-select-referrer").val();
        }
        this.refresh = function(){
            this.get_variants();
        }
    };
    octavius_admin.add_module(new OctaviusPostVariants());
});