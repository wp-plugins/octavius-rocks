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
        this.init = function(octavius){
            oc = octavius;
            socket = octavius.socket;
            $metabox = $("#octavius_rocks_ab_results");
            $wrapper = $metabox.find(".octavius-rocks-ab-results");
            post_id = $wrapper.attr("data-post-id");
            $metabox.on("click",".octavius-rocks-refresh", this.refresh.bind(this) );
            $metabox.on("change",".octavius-rocks-select",this.refresh.bind(this));
            this.get_variants();
            socket.on("update_variants_hits", this.update_variants_hits);
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
            $.each(data.variants, function(_slug, _hits){
                var percent = (_hits/data.overall)*100;
                var right = 100-(percent+offset);
                var percent_readable = Math.floor(percent);
                var $div = $("<div></div>")
                    .addClass("octavius-rocks-ab-result")
                    .css("left", offset+"%")
                    .css("right", right+"%")
                    .attr("title", _slug+" "+percent_readable+"% Hits: "+_hits)
                    .prependTo($wrapper);
                var $percent = $("<span></span>")
                    .addClass("octavius-rocks-ab-result-values")
                    .html(_slug+"<br>"+percent_readable+"% Hits: "+_hits)
                    .appendTo($div);
                offset = offset+percent;
            });
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