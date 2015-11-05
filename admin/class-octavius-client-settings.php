<?php

/**
 * The settings page
 *
 */
class Octavius_Client_Settings {

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
	 * variants store of ab testing
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
	public function menu_pages(){
		add_submenu_page( 'options-general.php', 'Octavius Rocks', 'Octavius Rocks', 'manage_options', "octavius-rocks", array($this, "render_octavius_settings"));
	}

	/**
	 *  renders settings page for octavius
	 */
	public function render_octavius_settings()
	{
		$current = (isset($_GET["tab"]))? $_GET["tab"]:"server";
		require dirname(__FILE__)."/partials/octavius-settings-tabs.php";

		switch ($current) {
			case 'ab':
				$this->render_ab_settings();
				break;
			default:
				$this->render_server_settings();
				break;
		}
	}
	/**
	 * general server settings
	 */
	private function render_server_settings(){
		// TODO tab for server settings
		$api_key_id = "ph_octavius_api_key";
		$server_id = "ph_octavius_server";
		
		if( isset($_POST[$api_key_id]) && isset($_POST[$server_id]) ){
			update_option($api_key_id, sanitize_text_field($_POST[$api_key_id]) );
			update_option($server_id, sanitize_text_field($_POST[$server_id]) );
		}

		$api_key = get_option($api_key_id, '');
		$server = get_option($server_id, '');

		require dirname(__FILE__)."/partials/octavius-settings-server.php";
	}
	/**
	 * ab test settings
	 */
	private function render_ab_settings(){
		/**
		 * save if needed
		 */
		if(isset($_POST['submit'])){
			/**
			 * update ab settings
			 */
			$enabled = 0;
			if(isset($_POST["octavius_rocks_ab_enabled"])){
				$enabled = intval($_POST["octavius_rocks_ab_enabled"]);
			}
			if($enabled < 1){
				$enabled = 0;
			} else {
				$enabled = 1;
			}
			$this->variants->enabled($enabled);
		}
		if(isset($_POST) && isset($_POST["octavius_rocks"]) && is_array($_POST["octavius_rocks"])){
			$oc = $_POST["octavius_rocks"];
			$values = array();
			for($i = 0; $i < count($oc); $i++){
				$variant = $oc[$i];
				/**
				 * valid slug and name?
				 */
				$slug = sanitize_text_field($variant["slug"]);
				if($slug == '') continue;
				$name = sanitize_text_field($variant["name"]);
				if($name == '') continue;
				/**
				 * delete?
				 */
				$delete = 0;
				if(isset($variant["delete"])){
					$delete = intval($variant["delete"]);
				}
				if($delete) continue;
				/**
				 * add to variants
				 */
				$values[$slug] = $name;
			}
			$this->variants->save($values);
		}
		/**
		 * render settings page
		 */
		$ab_enabled = $this->variants->enabled();
		$all = $this->variants->get();
		require dirname(__FILE__)."/partials/octavius-settings-ab.php";
	}

}
