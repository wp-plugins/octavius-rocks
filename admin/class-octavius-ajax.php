<?php

/**
 * The ajax calls
 *
 *
 * @package    Octavius_Client
 * @subpackage Octavius_Client/admin
 * @author     PALASTHOTEL by Edward <eb@palasthotel.de>
 */
class Octavius_Ajax {

	/**
	 * The ID of this plugin.
	 *
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 */
	private $version;
	/**
	 * variants store
	 *
	 */
	public $variants;

	/**
	 * Initialize the class and set its properties.
	 *
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function get_ab_info(){
		$result = (object)array("success"=>false,"msg" => "");
		/**
		 * if post contents is not valid
		 */
		if( !isset($_POST["ids"]) || !is_array($_POST["ids"])){
			$result->msg = "No ids found";
			wp_send_json($result);
		}
		/**
		 * get all titles
		 */
		global $wpdb;
		$site_url = get_site_url();
		$contents = $_POST["ids"];
		$result = array();
		for ($i=0; $i < count($contents) ; $i++) {
			$pid = intval($contents[$i]);
			$variant = $this->variants->get_variant($pid);
			if($variant == null || $variant == ""){
				$result[] = array(
					"content_id" => $pid,
					"title"=>get_the_title($pid),
					"date"=>get_the_date("m-d-Y", $pid),
					"locked" => false,
				);
			} else {
				$result[] = array(
					"content_id" => $pid,
					"locked" => true,
				);
			}
		}
		wp_send_json(array('success' => true, "result" => $result));
	}
	
	public function get_ab_posts_not_chosen(){
		$post_ids = array();
				
		//get all posts that have no chosen variant
		$args = array(
			'meta_query' => array(
			    array(
			     'key' => '_octavius_rocks_variant',
			     'compare' => 'NOT EXISTS'
			    ),
			)
		);
		// The Query
		$the_query = new WP_Query( $args );
		// The Loop
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				array_push($post_ids, get_the_ID());
			}
		}
		/* Restore original Post Data */
		wp_reset_postdata();
		
		wp_send_json($post_ids);
	}

	public function get_posts_titles(){
		$result = (object)array("success"=>false,"msg" => "");
		/**
		 * if post contents is not valid
		 */
		if( !isset($_POST["contents"]) || !is_array($_POST["contents"])){
			$result->msg = "No contents found";
			wp_send_json($result);
		}
		if(!isset($_POST["type"]) || ( $_POST["type"] != "id" && $_POST["type"] != "path"  ) ){
			$result->msg = "No valid type";
			wp_send_json($result);
		}
		$type = $_POST["type"];

		/**
		 * get all titles
		 */
		global $wpdb;
		$site_url = get_site_url();
		$contents = $_POST["contents"];
		$result = array();
		for ($i=0; $i < count($contents) ; $i++) {
			if($type == "id"){
				$title = get_the_title($contents[$i]);
				$result[] = array("content_id"=>$contents[$i], "title" => $title);
			} else if($type == "path"){
				$args['headers'] = array(
					'Authorization' => 'Basic ' . base64_encode( $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW'] ),
				);
				$args['timeout'] = 20;
				$response = wp_remote_request( $site_url.$contents[$i]."?octavius=info", $args );

				if(is_wp_error($response)){
					$result[] = array("content_id"=>null, "title" => "", "path"=>$contents[$i], "guid" => $site_url.$contents[$i]);
				} else {
					$res = json_decode($response["body"]);
					$title = "";
					if(isset($res->title)){
						$title = $res->title;
					}
					$id = null;
					if(isset($res->ID)){
						$id = $res->ID;
					}
					$result[] = array("content_id"=>$id, "title" => $title, "path"=>$contents[$i], "guid" => $site_url.$contents[$i]);
				}
			}
			
		}
		wp_send_json(array('success' => true, "result" => $result));
	}

	public function set_post_ab_variant(){
		$result = (object)array("success"=>false,"msg" => "");
		/**
		 * if post contents is not valid
		 */
		if( !isset($_POST["pid"]) || !isset($_POST["variant_slug"])){
			$result->msg = "No post id or slug found";
			wp_send_json($result);
		}
		/**
		 * save or delete post meta 
		 */
		$pid = intval($_POST["pid"]);
		$slug = sanitize_text_field($_POST["variant_slug"]);
		$success = $this->variants->set_variant($pid, $slug);
		/**
		 * 
		 */
		wp_send_json(array('success' => $success, "result" => array($pid,$slug)));
	}

}
