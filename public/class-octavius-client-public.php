<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Octavius_Client
 * @subpackage Octavius_Client/public
 * @author     PALASTHOTEL by Edward <eb@palasthotel.de>
 */
class Octavius_Client_Public {

	/**
	 * The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
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

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 * 
	 */
	public function render_script() {

		$api_key_id = "ph_octavius_api_key";
		$server_id = "ph_octavius_server";
		$port_id = "ph_octavius_port";

		$api_key = get_option($api_key_id,'');
		$server = get_option($server_id, '');
		$port = get_option($port_id, '');

		?>
		
		<style type="text/css">#octavius-needed-pixel{height: 0px;}</style>
		<?php 
		$url = strtok($_SERVER["REQUEST_URI"],'?');
		$pid = get_the_ID();
		$service_url = $server.":".$port."/hit/oc-found/".$api_key."/".$pid."?url=".$url;
		?>
		<!--<img id="octavius-needed-pixel" src="<?php echo $service_url; ?>" />-->
		
		<?php

		?>
		<script type="text/javascript">
		window.OctaviusInit = function(octavius){
			octavius.config.service = "<?php echo $server; ?>:<?php echo $port; ?>";
			octavius.api_key = "<?php echo $api_key; ?>";
		};
		window.OctaviusOverwrites = function(octavius){
			octavius.get_parent_id = function(){
				return "<?php echo $this->get_content_id(); ?>";
			};
			octavius.get_pagetype = function(){
				return "<?php echo $this->get_pagetype(); ?>";
			};
		};
		(function(d){
			var js, id = 'octavius-script', ref = d.getElementsByTagName('script')[0];
			if (d.getElementById(id)) {return;}
			js = d.createElement('script'); js.id = id; js.async = true;
			js.src = "<?php echo $server; ?>:<?php echo $port; ?>/files/octavius.client.v1.0.js";
			ref.parentNode.insertBefore(js, ref);
		}(document));
		</script>
		<?php
	}
	/**
	 * return pagetype of page
	 */
	private function get_pagetype(){
		global $wp_query, $wpdb;
        if ( is_single() ) {
        	if ( isset( $post->post_type ) ) {
        		return $post->post_type;
        	}
        } elseif ( is_archive() ) {

			if ( is_post_type_archive() ) {
				$post_type = get_query_var( 'post_type' );
				if ( is_array( $post_type ) ){
					$post_type = reset( $post_type );
				}
				return 'archive-' . sanitize_html_class( $post_type );
			} else if ( is_author() ) {
				return 'author';
			} elseif ( is_category() ) {
				return 'category';
			} else if( is_tag() ){
				return 'tag';
			} else if( is_tax() ){
				return 'tax';
			}
        } else if(is_home() || is_front_page()){
        	return 'home';
        }
        return '';
	}
	/**
	 * return id of page
	 * 
	 */
	private function get_content_id(){
		global $wp_query;
		if ( is_single() || is_page() ) {
			return $wp_query->get_queried_object_id();
		} elseif ( is_archive() ) {
			if ( is_author() ) {
				$author = $wp_query->get_queried_object();
				return $author->ID;
			} elseif ( is_category() ) {
				$cat = $wp_query->get_queried_object();
				if ( isset( $cat->term_id ) ) {
					return $cat->term_id;
				}
			} elseif ( is_tag() || is_tax() ) {
				$term = $wp_query->get_queried_object();
				if ( isset( $term->term_id ) ) {
					return $term->term_id;
				}
			}
        }
        return '';
	}
	/**
	 * show url info
	 */
	public function show_url_info(){		
		if(isset($_GET['octavius']) and $_GET['octavius']=="info"){
			$info = (object) array();
			if( is_single() || is_page() ){
				$info->title = get_the_title();
				$info->ID = get_the_ID();
			} else if( is_category() || is_tag() ) {
				if(is_category()){
					$info->title = "Category: ";
				} else if(is_tag()){
					$info->title = "Tag: ";
				}
				ob_start();
				wp_title();
				$info->title .= ob_get_contents();
				ob_end_clean();
			} else if( is_home() || is_front_page() ){
	        	$info->title = 'Home';
	        }
			wp_send_json($info);		
			die;		
			
		}
	}

}
