//TODO rename functions etc.
(function( $ ) {
	'use strict';

	window.OctaviusReportBox = function(){
		/**
		 * top contents
		 */
		var $top_links = $("#octavius-top-links");
		var $loading = $("#octavius-loading");
		var $step = $("#oc-report-filter-step");
		var $event = $('#oc-report-filter-event');
		var $referrer = $('#oc-report-filter-referrer');
		var $content = $('#oc-report-filter-content');
		var $table = $('#oc-result-table');
		var $sendFilterButton = $('#oc-report-filter-send');

		var self = this;
		var socket = null;
		var oc = null;

		this.init = function(octavius){
			oc = octavius;
			socket = octavius.socket;

			this.init_top();
		}
		/**
		 * inits all for top content connection
		 */
		this.init_top = function(){
			/**
			 * top posts table socket event
			 */
			socket.on('update_custom_report', function(data){
				console.log(data);
				$top_links.empty();
				$table.css("display", "table");

				if(data.result.length > 0){

					for(var i = 0; i < data.result.length; i++){
						var _item = data.result[i];
						$top_links.append('<tr>'+
							'<td><a href="'+_item.content_url+'">'+_item.content_url+'</a></td>'+
							'<td>'+_item.summed_hits+'</td>'+
						'</tr>');
					}
				} else {
					$top_links.append('<tr><td colspan="2">Zu dieser Abfrage gibt es keine Ergebnisse.</td></tr>');
				}


				$loading.css("visibility", "hidden");
				$table.css("display", "table");

			});
			/**
			* Send Request for Data when clicked
			**/
			$sendFilterButton.on("click", function(){
				self.emit_get_top();
			});

		}
		var top_timeout = null;
		this.get_top = function(){
			clearTimeout(top_timeout);
			top_timeout = setTimeout(function(){
				self.emit_get_top();
			},5000);
		}
		var emit_get_top_timeout = null;
		this.emit_get_top = function(){
			$loading.css("visibility", "visible");
			if(!oc.admincore.is_ready){
				clearTimeout(emit_get_top_timeout);
				emit_get_top_timeout = setTimeout(function(){ self.emit_get_top(); }, 300);
				return;
			}
			socket.emit("get_custom_report", self.getValuesFromForm());
		}
		this.getValuesFromForm = function(){
			var data = {};
			if($content.val() != ""){
  			data.content_type = $content.val();
			}
			data.event_type = $event.val();
			var step = $step.val();
			var offset = 0;
			if(step.split("-").length > 1){
				offset = step.split("-")[1];
				step = step.split("-")[0];
			}
			data.step = step;
			data.offset = offset;
			if($referrer.val() != ""){
  			data.referrer = $referrer.val();
			}
			return data;
		}

	}
	octavius_admin.add_module(new OctaviusReportBox());



})(jQuery);