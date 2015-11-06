//TODO clean up top contents functionality
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
		var $button_template = $("#octavius-rocks-ab-button-template").attr('style', '');
		$button_template.hide();

		var self = this;
		var socket = null;
		var oc = null;

		var theDate = new Date('09-10-2015');//daten when rendered was tracked on zett TODO

		this.init = function(octavius){
			oc = octavius;
			socket = octavius.socket;

			this.init_ab();
		}
		/**
		 * inits ab variant table connection
		 */
		this.init_ab = function(){
			/**
			 * upate table on socket event
			 */
			socket.on('update_ab_top_reports', function(data){
				if(data.error){ //if no results found
					$results_ab.empty();
					$loading_ab.css("visibility", "hidden");
					$results_ab.append("Momentan keine Posts für die Auswertung verfügbar.");
					return;
				}
				var content_ids = [];
				var ids_map = {};
				var content_id = "";
				for( var i = 0; i < data.length; i++){
					var result = data[i];
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
						var countListItems = 0;
						for( var i = 0; i < _data.result.length; i++){
							var result = _data.result[i];
							var releaseDate = new Date(result.date);
							if(theDate > releaseDate) continue; //cannot calculate signigicance for old posts
							var ob = ids_map[result.content_id];
							if(ob.significance.error) continue; //hide results with not calculatable significance
							var $tr = $("<tr></tr>");
							$tr.append("<td><a href='/wp-admin/post.php?post="
								+ob.content_id+"&action=edit'>"+result.title+
								"</a></td>");

							//chose variant by highest conversion rate
							var winner_variant_slug = 'standard';
							var winner_variant_conversionrate = ob.conversion_rates.standard;

							//TODO show significance
							if(ob.significance['95'] || ob.significance['99']){ //only give possibility to chose other than standard when is significantly better
								for(var conversion_rate_slug in ob.conversion_rates){
									if(ob.conversion_rates[conversion_rate_slug] > winner_variant_conversionrate){
										winner_variant_slug = conversion_rate_slug;
										winner_variant_conversionrate = ob.conversion_rates[conversion_rate_slug];
									}
								}
							}
							var info = winner_variant_slug+"  <small>["+winner_variant_conversionrate+"%]</small>";
							$tr.append("<td>"+info+"</td>");
							var $button = $button_template.clone();
							$button.show();
							var $td = $("<td></td>")
							.addClass("octavius-rocks-variant-submit")
							.attr("data-pid",result.content_id)
							.attr("data-slug",winner_variant_slug);
							$tr.append($td.append($button));
							$results_ab.append($tr);
							countListItems++;
						}
						if(countListItems < 1){ //if no items added to list show errormessage
							$results_ab.empty();
							$loading_ab.css("visibility", "hidden");
							$results_ab.append("<tr><td colspan='3'>Keine signifikanten Ergebnisse.</td></tr>");
							return;
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
				//TODO working?
				var pid = $info.attr("data-pid");
				var slug = $info.attr("data-slug");
				console.log([$info,pid,slug]);
				$.ajax({
					url: ajaxurl+"?action=set_post_ab_variant",
					dataType: "json",
					method: "POST",
					data: {pid: pid, variant_slug: slug},
					success: function(_data){
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

			// get all ids from posts that have no chosen variant
			$.ajax({
				url: ajaxurl+"?action=get_ab_posts_not_chosen",
				method: "POST",
				success: function(_data){
					socket.emit("get_ab_top_reports",{	limit: $limit_ab.val(), content_ids: _data });
				},
				error: function(a,b,c){
					console.log([a,b,c]);
				}
			});
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