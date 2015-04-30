<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Octavius_Client
 * @subpackage Octavius_Client/admin
 * @author     PALASTHOTEL by Edward <eb@palasthotel.de>
 */
class Octavius_Client_Admin {

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
	public function menu_pages(){
		add_submenu_page( 'options-general.php', 'Octavius 2.0', 'Octavius 2.0', 'manage_options', $this->plugin_name, array($this, "render_octavius_settings"));
	}

	/**
	 *  renders settings page for octavius
	 */
	public function render_octavius_settings()
	{

		$api_key_id = "ph_octavius_api_key";
		$server_id = "ph_octavius_server";
		$port_id = "ph_octavius_port";
		
		if( isset($_POST[$api_key_id]) && isset($_POST[$server_id]) && isset($_POST[$port_id])){
			update_option($api_key_id, sanitize_text_field($_POST[$api_key_id]) );
			update_option($server_id, sanitize_text_field($_POST[$server_id]) );
			update_option($port_id, sanitize_text_field($_POST[$port_id]) );
		}

		$api_key = get_option($api_key_id, '');
		$server = get_option($server_id, '');
		$port = get_option($port_id, '');

		require dirname(__FILE__)."/partials/octavius-client-admin-settings-display.php";
	}

	public function render_js_vars(){
		// $tooltip = file_get_contents(dirname(__FILE__)."/partials/octavius-client-admin-tooltip-display.html")
		
	}
	/**
	 * add evaluating script
	 */
	public function add_script(){
		if ( is_user_logged_in() && !is_admin() ) {
			wp_enqueue_style( 'octavius-evaluate-css', plugin_dir_url( __FILE__ ) . 'css/octavius-client-admin.css', array(), '1.0', 'all' );
			wp_enqueue_script( 'socketio', plugin_dir_url( __FILE__ ) . 'js/socket.io-1.3.5.js', array('jquery'), '1.3.5', true );
			wp_enqueue_script( 'octavius-evaluate', plugin_dir_url( __FILE__ ) . 'js/octavius-client-admin.js', array('socketio'), '1.0', true );
		}
	}

	public function add_admin_bar_button()
	{
		if ( is_user_logged_in() && !is_admin() ) {

			global $wp_admin_bar;

			$wp_admin_bar->add_menu( array(
				'id'     => 'octavius',
				'parent' => 'top-secondary',
				'title'  => apply_filters( 'octavius_bar_title', __( 'Octavius', 'Octavius_Client' ) ),
				'meta'   => array( 'rel' => 'octavius-toggle-button' ),
			) );
		}
	}

}
