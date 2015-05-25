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
	 * Initialize the class and set its properties.
	 *
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

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
				$row = $wpdb->get_row('SELECT ID, post_title FROM '.$wpdb->prefix.'posts WHERE guid = "'.$site_url.$contents[$i].'"');
				if($row == null){
					$result[] = array("content_id"=>null, "title" => "", "path"=>$contents[$i], "guid" => $site_url.$contents[$i]);
				} else {
					$result[] = array("content_id"=> $row->ID, "title" => $row->post_title, "path" => $contents[$i]);
				}
			}
			
		}
		wp_send_json(array('success' => true, "result" => $result));
	}

}
