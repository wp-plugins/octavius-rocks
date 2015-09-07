<?php

/**
 *
 *
 * @wordpress-plugin
 * Plugin Name:       Octavius Rocks
 * Plugin URI:        http://www.palasthotel.de
 * Description:       Tacking click paths
 * Version:           1.3.5
 * Author:            PALASTHOTEL by Edward
 * Author URI:        http://www.palasthotel.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       octavius-client
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-octavius-client-activator.php
 */
function activate_octavius_client() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-octavius-client-activator.php';
	Octavius_Client_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-octavius-client-deactivator.php
 */
function deactivate_octavius_client() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-octavius-client-deactivator.php';
	Octavius_Client_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_octavius_client' );
register_deactivation_hook( __FILE__, 'deactivate_octavius_client' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-octavius-client.php';

/**
 * run octavius client
 */
function run_octavius_client() {
	$plugin = new Octavius_Client();
	$plugin->run();
}
run_octavius_client();

/**
 * public method to build data attribute
 */
function octavius_client_data_builder($assoc_array){
	$datas = array();
	if(is_user_logged_in() && is_admin()){
		$datas[] = "data-evaluate-octavius='true'";
	}
	$assoc_array = apply_filters('octavius_rocks_datas', $assoc_array);
	foreach ($assoc_array as $key => $value) {
		$datas[] = "data-octavius-".$key."='".$value."'";
	}
	return ' '.implode(" ", $datas).' ';
}
