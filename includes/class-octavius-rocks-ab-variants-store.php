<?php

/**
 * The admin-specific functionality of the plugin.
 */
class Octavius_Rocks_Ab_Variants_Store {
	/**
	 * all registered variants
	 */
	public function get(){
		return get_option("_octavius_rocks_ab_variants", array());
	}
	public function save($variants){
		update_option("_octavius_rocks_ab_variants", $variants);
	}
	public function save_post_metas($post_id, $slug, $title, $attachment_id, $excerpt){
		update_post_meta($post_id, "_octavius_rocks_".$slug."_title", sanitize_text_field($title) );
		update_post_meta($post_id, "_octavius_rocks_".$slug."_attachment_id", intval($attachment_id) );
		update_post_meta($post_id, "_octavius_rocks_".$slug."_excerpt", sanitize_text_field($excerpt) );	
	}
	public function get_post_metas($post_id, $slug){
		return (object)array(
			"title" => get_post_meta($post_id, "_octavius_rocks_".$slug."_title", true ),
			"attachment_id" => get_post_meta($post_id, "_octavius_rocks_".$slug."_attachment_id", true ),
			"excerpt" => get_post_meta($post_id, "_octavius_rocks_".$slug."_excerpt", true ),
		);
	}
	public function get_variants_values($post_id){
		$variants = $this->get();
		$result = array();
		foreach ($variants as $slug => $name) {
			$result[$slug] = $this->get_post_metas($post_id, $slug);
		}
		return $result;
	}
	/**
	 * for locked variant on post
	 */
	public function set_variant($post_id, $slug){
		if($slug == ""){
			return delete_post_meta($post_id,"_octavius_rocks_variant");
		} else {
			return update_post_meta($post_id,"_octavius_rocks_variant",$slug);
		}
	}
	public function get_variant($post_id){
		return get_post_meta($post_id,"_octavius_rocks_variant",true);
	}
	/**
	 * is octavius enabled?
	 */
	public function enabled( $enabled = null ){
		if($enabled !== null){
			update_option("_octavius_rocks_ab_enabled", $enabled);
		}
		return get_option('_octavius_rocks_ab_enabled', false);
	}
}
