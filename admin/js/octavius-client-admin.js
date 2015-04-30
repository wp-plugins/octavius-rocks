(function( $ ) {
	'use strict';

	/**
	 * evaluation object
	 */
	var OctaviusEvaluation  =  function(octavius){
		/**
		 * save selectors and config for quick access
		 */
		var oc = octavius;
		var config = this.config = octavius.config;
		var selectors = this.selectors =  octavius.selectors;
		var $targets = $(selectors.target+", [data-evaluate-octavius=true]");
		
		/**
		 * register octavius and listen to connection
		 */
		var socket = Octavius.socket = io(config.service);
		socket.on('connected', function (data) {
			socket.emit('init',{ api_key: oc.api_key });
		});
		/**
		 * get hits for elements
		 * 
		 */
		var is_ready = false;
		socket.on("ready", function(data){
			is_ready = true;
		});
		/**
		 * if we get disconnected from octavius
		 */
		socket.on('disconnect', function(info){
			console.log("Connection lost");
			console.log([info]);
			is_ready = false;
		});
		/**
		 * start timeouts for hits
		 */
		function get_hits(){
			setTimeout(function(){
				if(!is_ready){
					get_hits();
					return;
				}
				if(!is_on) return;
				var entities = [];
				$.each( $targets, function(index, element){
					entities[index] = Octavius.build_entity(element);
				});
				socket.emit("get_hits", {entities: entities} );
			}, 2000);
		}
		/**
		 * if server reports new hits
		 */
		socket.on("update_hits", function(data){
			// var hits = data.hits;
			for (var i = data.hits.length - 1; i >= 0; i--) {
				do_update_element(data.hits[i].index, data.hits[i].hits);
			};
			get_hits();
		});
		/**
		 * update data for tracked element
		 */
		function do_update_element(index, hits){
			var $element = $($targets.get(index)).find(".octavius-evaluate-tooltip-content");
			if(parseInt($element.text()) != parseInt(hits)){
				do_update_highlight($element);
			}
			$element.text(hits);
		}
		/**
		 * animate highlight after update
		 */
		function do_update_highlight($element){
			 $element.removeClass("highlighted");
			 setTimeout(function(){
			 	$element.addClass("highlighted");
			 },200);
		}
		/**
		 * init html to targets that show the stats
		 */
		function add_targets_html(){
			var $targets = $(selectors.target+", [data-evaluate-octavius=true]");
			$targets.addClass("octavius-evaluate");
			$targets.each(function(index,element){
				if($(element).parents("#wpadminbar").length > 0) return true;
				var tool_markup = '<div class="octavius-evaluate-tooltip">'
									+ '<div class="octavius-evaluate-tooltip-content"></div>'
								+ '</div>';
				element.innerHTML = tool_markup+element.innerHTML;
			});
		}
		/**
		 * removes all html injected by octavius
		 */
		function remove_targets_html(){
			$(".octavius-evaluate-tooltip").remove();
		}
		/**
		 * on, off and whats the status?
		 */
		var is_on = false;
		this.on = function(){
			is_on = true;
			add_targets_html();
			get_hits();
		}
		this.off = function(){
			is_on = false;
			remove_targets_html();
		}
		this.isOn = function(){
			return is_on;
		}

	}
	/**
	 * adminbar button listener and de/activation
	 */
	var evaluation =  null;
	$( "body" ).on( "click", '#wp-admin-bar-octavius', function(e) {
		e.preventDefault();
		/**
		 * does octavius exists?
		 */
		if(typeof Octavius === typeof undefined){
			throw "There is no Octavius Object";
		}
		/**
		 * do we have already an instance of evaluation?
		 */
		if(evaluation == null){
			evaluation = new OctaviusEvaluation(Octavius);
		}
		/**
		 * switch evaluation on and off
		 */
		if(evaluation.isOn()){
			evaluation.off();
		} else {
			evaluation.on();
		}
	} );

	

})( jQuery );

