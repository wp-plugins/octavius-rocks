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
	
	/**
	 * add evaluating script
	 */
	public function add_script(){
		if ( is_user_logged_in() ) {
			wp_enqueue_style( 'octavius_evaluate_css', plugin_dir_url( __FILE__ ) . 'css/octavius-client-admin.css', array(), '1.0', 'all' );
			wp_enqueue_script( 'octavius-socketio', plugin_dir_url( __FILE__ ) . 'js/socket.io-1.3.5.js', array(), '1.3.5', true );
			wp_enqueue_script( 'octavius-evaluate', plugin_dir_url( __FILE__ ) . 'js/octavius-client-admin.js', array(), '1.0', true );
		}
	}
	/**
	 * ignore octavius scripts from ph-aggregator
	 */
	public function aggregator_ignore($ignores){
		$ignores[] = 'octavius-socketio';
		$ignores[] = 'octavius-evaluate';
		return $ignores;
	}

	/**
	 * add octavius button to adminbar in frontend
	 */
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
	/**
	 * wordpres dashboard setup
	 */
	public function dashboard_setup(){
		wp_add_dashboard_widget(
			'octavius_top_clicks',    			// Widget slug.
			'Top clicks',         					// Title.
			array($this, 'render_top_clicks'),	// Display function
			array($this, 'control_top_clicks') 		// Controll Function
		);
	}
	private function dashboardOptions($options = null){
		if($options != null){
			var_dump("SAVE");
			return update_option('octavius_dashboard_widget_options', $options);
		} else {
			return get_option('octavius_dashboard_widget_options', array("number" => 5) );
		}
	}
	public function render_top_clicks(){

		$api_key_id = "ph_octavius_api_key";
		$server_id = "ph_octavius_server";
		$port_id = "ph_octavius_port";

		$api_key = get_option($api_key_id,'');
		$server = get_option($server_id, '');
		$port = get_option($port_id, '');

		?>
		<script type="text/javascript">
		window.OctaviusInit = function(octavius){
			octavius.config.service = "<?php echo $server; ?>:<?php echo $port; ?>";
			octavius.api_key = "<?php echo $api_key; ?>";
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
		$options = $this->dashboardOptions();
		$limit = $options["number"];
		wp_enqueue_script( 'octavius-socketio', plugin_dir_url( __FILE__ ) . 'js/socket.io-1.3.5.js', array(), '1.3.5', true );
		wp_enqueue_script( 'octavius-admin-core', plugin_dir_url( __FILE__ ) . 'js/octavius-admin-core.js', array(), '1.0', true );
		wp_enqueue_script( 'octavius-admin-dashboard', plugin_dir_url( __FILE__ ) . 'js/octavius-dashboard.js', array(), '1.0', true );

		// require_once dirname(__FILE__)."/../classes/Octavius_Top_Links_Table.php";
		// $wp_list_table = new Octavius_Top_Links_Table();
		// $wp_list_table->prepare_items($options["number"]);
		// $wp_list_table->display();
		
		// TODO: enqueue js
		include dirname(__FILE__)."/partials/octavius-dashboard-top-clicks.php";

	}
	public function control_top_clicks(){
		$widget_options = $this->dashboardOptions();
		if(isset($_POST['widget-octavius-top-clicks'])){
			$number = intval($_POST['widget-octavius-top-clicks']);
			$widget_options['number'] = $number;
			$this->dashboardOptions($widget_options);
		}

		echo '<p><label for="octavius-top-number">' . __('Number of top contents to show:'). '</label>';
		echo '<input id="octavius-top-number" name="widget-octavius-top-clicks" type="text" value="' . $widget_options["number"] . '" size="3" /></p>';
	}

}
