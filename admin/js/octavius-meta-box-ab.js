/*
 * Attaches the image uploader to the input field
 */
jQuery(document).ready(function($){
 
    // Instantiates the variable that holds the media library frame.
    var meta_image_frame;
 
    // Runs when the image button is clicked.
    $('.octavius-ab-image').click(function(e){
        var $this = $(this);
 
        // Prevents the default action from occuring.
        e.preventDefault();
 
        // If the frame already exists, re-open it.
        if ( meta_image_frame ) {
            meta_image_frame.open();
            return;
        }
 
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
            // $('#meta-image').val(media_attachment.url);
            $this.closest(".octavius-rocks-variant").find('.octavius-ab-image-id').val(media_attachment.id);
            $this.closest(".octavius-rocks-variant").find('img').attr("src", media_attachment.sizes.thumbnail.url);
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
        var $wrapper;
        var post_id;
        this.init = function(octavius){
            oc = octavius;
            socket = octavius.socket;
            $wrapper = $(".octavius-rocks-ab-results");
            post_id = $wrapper.attr("data-post-id");
            this.get_variants();
            socket.on("update_variants_hits", this.update_variants_hits);
        }
        this.get_variants = function(){
            if(!oc.admincore.is_ready){
                setTimeout(this.get_variants.bind(this), 100);
                return;
            }
            socket.emit("get_variants_hits", {content_id: post_id, event_type: "click" });
        }
        this.update_variants_hits =  function(data){
            var wrapper_width = $wrapper.outerWidth(true);
            console.log(data);
            if(data.overall <=0) return;
            var values = {};
            var offset = 0;
            $wrapper.empty();
            $.each(data.variants, function(_slug, _hits){
                var percent = _hits/data.overall;
                var percent_readable = Math.floor(percent*100);
                var width = Math.floor(percent*wrapper_width)+offset;
                var $div = $("<div></div>")
                .addClass("octavius-rocks-ab-result")
                .css("width", width+"px")
                .attr("title", _slug+" "+percent_readable+"% Hits: "+_hits)
                .prependTo($wrapper);
                offset = width;
            });
        }
    };
    octavius_admin.add_module(new OctaviusPostVariants());
});