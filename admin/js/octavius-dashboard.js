(function( $ ) {
	'use strict';

	window.OctaviusDashboard = function(){
		var $top_links = $("#octavius-top-links");
		var $step = $("#octavius-top-links-step");
		var $timestamp = $('#octavius-timestamp');
		var $limit = $('#octavius-limit');

		var self = this;
		var socket = null;
		var oc = null;
		this.init = function(octavius){
			oc = octavius;
			socket = octavius.socket;
			
			this.get_top();
			
			socket.on('update_top', function(data){
				$top_links.empty();

				for(var i = 0; i < data.result.length; i++){
					var _item = data.result[i];
					$top_links.append('<tr>'+
						'<td><a href="'+_item.content_url+'">'+_item.content_url+'</a></td>'+
						'<td>'+_item.hits+'</td>'+
					'</tr>');
					var stamp = _item.timestamp;
				}
				if(data.step != "live"){
					var date = new Date(stamp);
				} else {
					var date = new Date();
					if($step.val() == "live"){
						self.get_top();
					}
				}
				
				var date_string = date.getFullYear()+"/"+date.getMonth()+"/"+date.getDay();
				var time_string = date.getHours()+":"+((date.getMinutes() < 10)? "0"+date.getMinutes():date.getMinutes());
				$timestamp.text( date_string+" "+time_string );

			});
			$step.on("change", this.get_top.bind(this));
		}
		var top_interval = null;
		this.get_top = function(){
			var self =  this;
			clearInterval(top_interval);
			top_interval = setTimeout(function(){
				if(!oc.admincore.is_ready) self.get_top();
				socket.emit("get_top",{step: $step.val(), limit: $limit.val() });
			},1000);
			
		}
		
	}
	octavius_admin.add_module(new OctaviusDashboard());

	

})(jQuery);