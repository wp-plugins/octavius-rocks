<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 */
class Octavius_Client {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 * 
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 * 
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 * 
	 */
	public function __construct() {

		$this->plugin_name = 'octavius-client';
		$this->version = '1.3.7';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_settings_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_grid_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 * 
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-octavius-client-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-octavius-client-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-octavius-client-admin.php';

		/**
		 * The class responsible for defining all ajax actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-octavius-ajax.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-octavius-client-public.php';

		/**
		 * The class responsible for defining all actions for grid
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'grid/class-grid-controller.php';

		/**
		 * The settings class
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-octavius-client-settings.php';

		/**
		 * variants store
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-octavius-rocks-ab-variants-store.php';

		$this->variants = new Octavius_Rocks_Ab_Variants_Store();
		$this->loader = new Octavius_Client_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 */
	private function set_locale() {

		$plugin_i18n = new Octavius_Client_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Octavius_Client_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_admin->variants = $this->variants;
		$ajax = new Octavius_Ajax( $this->get_plugin_name(), $this->get_version() );
		$ajax->variants = $this->variants;
		
		/**
		 * add scripts
		 */
		$this->loader->add_action('wp_enqueue_scripts', $plugin_admin, 'add_script');
		/**
		 * dont aggregate scripts
		 */
		$this->loader->add_filter('ph_aggregator_ignore', $plugin_admin, 'aggregator_ignore');

		/**
		 * dashboard widgets
		 */
		$this->loader->add_action('wp_dashboard_setup', $plugin_admin, 'dashboard_setup');

		/**
		 * post title ajax call
		 */
		$this->loader->add_action('wp_ajax_get_posts_titles', $ajax, 'get_posts_titles');

		/**
		 * set variant to post meta
		 */
		$this->loader->add_action('wp_ajax_set_post_ab_variant', $ajax, 'set_post_ab_variant');

		/**
		 * post ab info call
		 */
		$this->loader->add_action('wp_ajax_get_ab_info', $ajax, 'get_ab_info');
		
		/**
		 * get all post ids from not chosen ab posts
		 */
		$this->loader->add_action('wp_ajax_get_ab_posts_not_chosen', $ajax, 'get_ab_posts_not_chosen');

		/**
		 * admin bar button
		 */
		$this->loader->add_action( 'admin_bar_menu', $plugin_admin, 'add_admin_bar_button', 999 );
		
		/**
		 * Hooks for adding admin scripts 
		 */
		$this->loader->add_action('octavius_client_admin_add_scripts',  $plugin_admin, 'add_admin_scripts');
		$this->loader->add_action('admin_head', $plugin_admin, 'render_octavius_js_base');
		//$this->loader->add_action('wp_head', $plugin_admin, 'render_octavius_js_base'); TODO is already in frontend theme

		/**
		 * if there is an alternate teaser variante render meta box
		 */
		if( $this->variants->enabled() && count($this->variants->get()) > 0){
			$this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_meta_box_ab');
			$this->loader->add_action('save_post', $plugin_admin, 'save_meta_box_ab');
		}

	}

	/**
	 * Register all of the hooks related to settings menu
	 */
	private function define_settings_hooks() {

		$plugin_settings = new Octavius_Client_Settings( $this->get_plugin_name(), $this->get_version() );
		$plugin_settings->variants = $this->variants;

		/**
		 * registers all menu pages
		 */
		$this->loader->add_action( 'admin_menu', $plugin_settings, 'menu_pages' );	

	}

	/**
	 * Register all of the hooks related to the public-facing functionality of the plugin.
	 * 
	 */
	private function define_public_hooks() {

		$plugin_public = new Octavius_Client_Public( $this->get_plugin_name(), $this->get_version() );
		$plugin_public->variants = $this->variants;

		$this->loader->add_action( 'wp_footer', $plugin_public, 'render_script', 100 );

		/**
		 * renders the page title for the given url
		 */
		$this->loader->add_action('wp', $plugin_public, 'show_url_info');

		/**
		 * add variants to post object
		 */
		$this->loader->add_action( 'the_post', $plugin_public, 'add_variants_to_post' );

	}

	/**
	 * register all grid hooks
	 * 
	 */
	private function define_grid_hooks() {

		$plugin_grid = new Octavius_Grid_Controller( $this->get_plugin_name(), $this->get_version() );
		$plugin_grid->variants = $this->variants;

		$this->loader->add_action( 'grid_load_classes', $plugin_grid, 'load_classes');
		$this->loader->add_filter('grid_templates_paths', $plugin_grid, 'templates_paths');

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 * 
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 * 
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 * 
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 * 
	 */
	public function get_version() {
		return $this->version;
	}

}
