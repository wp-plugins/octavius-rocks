<?php

class grid_octavius_live_box extends grid_list_box
{
	public function __construct()
	{
		$this->content=new StdClass();
		$this->content->limit = 5;
		$this->content->offset = 0;
		$this->content->category = "";
	}

	public function type()
	{
		return 'octavius_live';
	}
 
	public function build($editmode) {
		
		
		if($editmode) 
		{
			$this->content->cat_name = "";
			if(isset($this->content->category) && $this->content->category != ""){
				$query_args["cat"] = $this->content->category;
				$cat = get_category($this->content->category);
				$this->content->cat_name = $cat->name;
			}
			

			return $this->content;

		}
		else
		{	
			$api_key_id = "ph_octavius_api_key";
			$server_id = "ph_octavius_server";
			$port_id = "ph_octavius_port";

			$api_key = get_option($api_key_id,'');
			$server = get_option($server_id, '');
			$port = get_option($port_id, '');

			$url = $server;
			if($port != ''){
				$url.= ":".$port;
			}
			$url.= "/json/".$api_key."/getTop/live";

			$args = array();
			$args['method'] = 'GET';
			$args['headers'] = array(
				'accept' => 'application/json',
				'content-type' => 'application/json'
			);
			$response = wp_remote_request($url, $args);
			if(is_wp_error($response)){
				return '<p>Nothing found</p>';
			}

			

			$json = json_decode($response["body"]);

			$ids = array();
			foreach ($json->result as $entity) {
				$ids[] = $entity->content_id;
			}

			$query_args = array (
			  'post__in'      => $ids,
			  'post_status'   => 'publish',
			  'orderby'       => 'post__in',
			  'post_type'     => 'any',
			);
			if(isset($this->content->category) && $this->content->category != ""){
				$query_args["cat"] = $this->content->category;
			}
			if(isset($this->content->limit) && $this->content->limit != ""){
				$query_args["posts_per_page"] = $this->content->limit;
			}
			if(isset($this->content->offset) && $this->content->offset != ""){
				$query_args["offset"] = $this->content->offset;
			}
			
			/**
			 * get template from theme or stick to plugin template
			 */
			$template = dirname(__FILE__)."/../templates/top-list-item.tpl.php";
			if(is_file(get_template_directory()."/octavius/top-list-item.tpl.php")){
				$template = get_template_directory()."/octavius/top-list-item.tpl.php";
			}

			/**
			 * do the wp_quey
			 */
			$output = "";
			$the_query = new WP_Query( $query_args );
			if ( $the_query->have_posts() ) {
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					ob_start();
					include $template;
					$output.= ob_get_contents();
					ob_end_clean();
				}
				wp_reset_postdata();
			}

			return $output;
			
		}
	}

	public function contentStructure(){
		$args = array(
			'orderby' => 'name',
			'order' => 'ASC'
		);
		$categories = get_categories($args);
		$arr_categories = array();
		$arr_categories[] = array(
			"key" => "",
			"text" => t("All"),
		);
		foreach($categories as $category) { 
			$arr_categories[] = array(
				"key" => $category->cat_ID,
				"text" => t($category->name),
			);
		}
		$cs = array();
		$cs[] = array(
			"key" => "limit",
			"label" => t("Limit"),
			"type" => "number",
		);
		$cs[] = array(
			"key" => "offset",
			"label" => t("Offset"),
			"type" => "number",
		);
		$cs[] = array(
			'key' => 'category',
			'label' => t('Category'),
			'type' => 'select',
			'selections'=>$arr_categories,
		);
		
		return $cs;
	}
}
