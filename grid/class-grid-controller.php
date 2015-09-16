<?php

/**
 * All grid adaptions for octavius
 *
 *
 * @package    Octavius_Client
 * @subpackage Octavius_Client/grid
 * @author     PALASTHOTEL by Edward <eb@palasthotel.de>
 */
class Octavius_Grid_Controller {

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
	
	/**
	 * load grid box classes
	 */
	public function load_classes(){
		require "box-classes/grid-octavius-live-box.inc";
	}

	/**
	 * grid template folder
	 */
	public function templates_paths($paths){
		$paths[] = dirname(__FILE__)."/grid-templates/";
		return $paths;
	}

}
