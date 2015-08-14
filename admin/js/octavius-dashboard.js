(function( $ ) {
	'use strict';

	window.OctaviusDashboard = function(){
		/**
		 * ab significant posts
		 */
		var $limit_ab = $("#octavius-rocks-ab-limit");
		var $type_ab = $("#octavius-rocks-ab-type");
		var $loading_ab = $("#octavius-loading-ab");
		var $results_ab = $("#octavius-ab-results");
		var $button_template = $("#octavius-rocks-ab-button-template");
		$button_template.hide();
		/**
		 * top contents
		 */
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

			this.init_top();
			this.init_ab();
		}
		/**
		 * inits ab variant table connection
		 */
		this.init_ab = function(){
			/**
			 * upate table on socket event
			 */
			socket.on('update_ab_top_contents', function(data){
				console.log(data);
				// TODO: send all content ids to backend and check if already done
				// TODO: display all over limit and hide all closed
				var content_ids = [];
				var ids_map = {};
				var content_id = "";
				for( var i = 0; i < data.result.length; i++){
					var result = data.result[i];
					if(content_id == result.content_id) continue;
					content_id = result.content_id;
					content_ids.push(result.content_id);
					ids_map[content_id] = result;

				}
				// get infos from wordpress instance
				$.ajax({
					url: ajaxurl+"?action=get_ab_info",
					dataType: "json",
					method: "POST",
					data: {ids: content_ids},
					success: function(_data){
						$results_ab.empty();
						$loading_ab.css("visibility", "hidden");
						console.log(_data);
						if(!_data.success){
							console.error(_data);
						} else {
							for( var i = 0; i < _data.result.length; i++){
								var result = _data.result[i];
								if(result.locked) continue;
								var ob = ids_map[result.content_id];
								var $tr = $("<tr></tr>");
								$tr.append("<td><a href='/wp-admin/post.php?post="
									+ob.content_id+"&action=edit'>"+result.title+
									"</a></td>");
								$tr.append("<td>"+ob.variant+"  <small>["+ob.hits+" hits]</small></td>");
								var $button = $button_template.clone();
								$button.show();
								var $td = $("<td></td>")
								.addClass("octavius-rocks-variant-submit")
								.attr("data-pid",result.content_id)
								.attr("data-slug",ob.variant);
								$tr.append($td.append($button));
								$results_ab.append($tr);
							}
						}
					},
					error: function(a,b,c){
						console.log([a,b,c]);
					}
				});				
				
			});
			this.get_ab_significant_contents();
			/**
			 * submit button event handler
			 */
			$results_ab.on("click", "[type=submit]", function(){
				var $info = $(this).closest(".octavius-rocks-variant-submit");
				var pid = $info.attr("data-pid");
				var slug = $info.attr("data-slug");
				console.log([$info,pid,slug]); 
				$.ajax({
					url: ajaxurl+"?action=set_post_ab_variant",
					dataType: "json",
					method: "POST",
					data: {pid: pid, variant_slug: slug},
					success: function(_data){
						console.log(_data);
						if(_data.success){
							$info.closest("tr").remove();
						}
					},
					error: function(a,b,c){
						console.log([a,b,c]);
					}
				});
			});
		}
		var ab_timeout = null;
		this.get_ab_significant_contents = function(){
			clearTimeout(ab_timeout);
			ab_timeout = setTimeout(function(){
				self.emit_get_ab_significant_contents();
			}, 300)
		}
		this.emit_get_ab_significant_contents = function(){
			$loading_ab.css("visibility", "visible");
			if(!oc.admincore.is_ready){
				this.get_ab_significant_contents();
				return;
			}
			var filters = [];
			if($type_ab.val() != ""){
				filters.push({ name: "event_type", value: $type_ab.val() });
			}
			
			socket.emit("get_ab_top_contents",{	limit: $limit_ab.val(), filters: filters });
		}
		/**
		 * inits all for top content connection
		 */
		this.init_top = function(){
			/**
			 * top posts table socket event
			 */
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
			/**
			 * on change listener for top posts
			 */
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