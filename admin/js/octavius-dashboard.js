(function( $ ) {
	'use strict';

	window.OctaviusDashboard = function(){
		var $top_links = $("#octavius-top-links");
		var $loading = $("#octavius-loading");
		var $step = $("#octavius-top-links-step");
		var $timestamp = $('#octavius-timestamp');
		var $limit = $('#octavius-limit');
		var edit_post_link = $("#edit-post-link-template").val();

		var self = this;
		var socket = null;
		var oc = null;
		
		this.init = function(octavius){
			oc = octavius;
			socket = octavius.socket;
			
			socket.on('update_top', function(data){
				$top_links.empty();

				for(var i = 0; i < data.result.length; i++){
					var _item = data.result[i];
					if( !isNaN(_item.content_id) && _item.content_id != "" && typeof titles_to_ids[_item.content_id] == typeof undefined ){
						titles_to_ids[_item.content_id] = null;
					} else if( typeof titles_to_path[_item.content_url] == typeof undefined ) {
						titles_to_path[_item.content_url] = null;
					}
					$top_links.append('<tr>'+
						'<td><a data-content_url="'+_item.content_url+'"" data-content_id="'+_item.content_id+'" '
						+'href="'+_item.content_url+'">'+_item.content_url+'</a></td>'+
						'<td>'+_item.hits+'</td>'+
					'</tr>');
					var stamp = _item.timestamp;
				}


				$loading.css("visibility", "hidden");

				if(data.step != "live"){
					var date = new Date(stamp);
				} else {
					var date = new Date();
					if($step.val() == "live"){
						self.get_top();
					}
				}

				self.display_titles();
				
				var date_string = date.getFullYear()+"/"+(date.getMonth()+1)+"/"+date.getDate();
				var time_string = date.getHours()+":"
								+((date.getMinutes() < 10)? "0"+date.getMinutes():date.getMinutes())
								+":"+((date.getSeconds() < 10)? "0"+date.getSeconds():date.getSeconds());
				$timestamp.text( date_string+" "+time_string );
				
			});
			$step.on("change", function(){
				$top_links.empty();
				self.emit_get_top();
			});
			this.emit_get_top();
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
			socket.emit("get_top",{step: $step.val(), limit: $limit.val() });
		}
		/**
		 * fetches new post titles if there are new ones
		 */
		var titles_to_ids = {};
		var title_update_timeout = null;
		this.update_titles_ids = function(){
			clearTimeout(title_update_timeout);
			title_update_timeout = setTimeout(function(){
				var new_titles = [];
				var request =  false;
				for(var cid in titles_to_ids){
					if(titles_to_ids[cid] == null && titles_to_ids[cid] !== false){
						new_titles.push(cid);
						request = true;
					}
				}
				/**
				 * if there are no new ones just start anohter round
				 */
				if(!request){
					self.update_titles_ids();
					return;
				}
				/**
				 * if there are new ones request them and then start new round
				 */
				$.ajax({
					url: ajaxurl+"?action=get_posts_titles",
					dataType: "json",
					method: "POST",
					data: {contents: new_titles,"type": "id"},
					success: function(data){
						if(!data.success){
							console.error(data);
						} else {
							for (var i = 0; i < data.result.length; i++) {
								var cid = data.result[i].content_id;
								var title = data.result[i].title;
								titles_to_ids[cid] = {};
								if(title != ""){
									titles_to_ids[cid].title = title;
								} else {
									titles_to_ids[cid].title = false;
								}					
							};
						}
						self.update_titles_ids();
						self.display_titles();
					}
				});
			}, 1000);
		}
		this.update_titles_ids();
		/**
		 * fetches new post titles if there are new ones
		 */
		var titles_to_path = {};
		var title_path_update_timeout = null;
		this.update_titles_path = function(){
			clearTimeout(title_path_update_timeout);
			title_path_update_timeout = setTimeout(function(){
				var new_titles = [];
				var request =  false;
				for(var path in titles_to_path){
					if(titles_to_path[path] == null && titles_to_path[path] !== false){
						new_titles.push(path);
						request = true;
					}
				}
				/**
				 * if there are no new ones just start anohter round
				 */
				if(!request){
					self.update_titles_path();
					return;
				}
				/**
				 * if there are new ones request them and then start new round
				 */
				$.ajax({
					url: ajaxurl+"?action=get_posts_titles",
					dataType: "json",
					method: "POST",
					data: {contents: new_titles, type: "path"},
					success: function(data){
						if(!data.success){
							console.error(data);
						} else {
							for (var i = 0; i < data.result.length; i++) {
								var path = data.result[i].path;
								var title = data.result[i].title;
								titles_to_path[path] = {};
								if(title != ""){
									titles_to_path[path] = data.result[i];
								} else {
									titles_to_path[path].title = false;
								}					
							};
						}
						self.update_titles_path();
						self.display_titles();
					},
				});
			}, 1000);
		}
		this.update_titles_path();
		/**
		 * displays post titles
		 */
		this.display_titles = function(){
			$top_links.find("a").each(function(index, element){
				var cid = element.getAttribute("data-content_id");
				var path = element.getAttribute("href");
				if(typeof titles_to_ids[cid] !== typeof undefined 
					&& titles_to_ids[cid] != null
					&& typeof titles_to_ids[cid].title === typeof ""){
					if(typeof titles_to_ids[cid].original === typeof undefined){
						titles_to_ids[cid].original = element.innerHTML;
					}
					element.setAttribute("title", titles_to_ids[cid].original );
					element.innerHTML = titles_to_ids[cid].title;
					element.href = edit_post_link+cid;
				} else if(typeof titles_to_path[path] !== typeof undefined 
					&& titles_to_path[path] != null 
					&& titles_to_path[path].title !== false){
					if(typeof titles_to_path[path].original === typeof undefined){
						titles_to_path[path].original = element.innerHTML;
					}
					element.setAttribute("title", titles_to_path[path].original );
					element.innerHTML = titles_to_path[path].title;
					if(titles_to_path[path].content_id != null){
						element.href = edit_post_link+ titles_to_path[path].content_id;
					}
				}
			});
		}
		
	}
	octavius_admin.add_module(new OctaviusDashboard());

	

})(jQuery);