<?php
/**
 * Public: ViewPublic class
 *
 * @package WPSocialManager
 * @subpackage Public
 */

namespace XCo\WPSocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, enqueue the stylesheet and JavaScript,
 * and register custom API Routes using the built-in WP-API infrastructure.
 *
 * @since 1.0.0
 */
final class ViewPublic extends OutputHelpers {

	/**
	 * Common arguments passed in a Class or a function.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $args = array();

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $plugin_name = '';

	/**
	 * The absolut URL path to the plugin directory.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $path_url = '';

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $version = '';

	/**
	 * Theme support features.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var ThemeSupports
	 */
	protected $supports = null;

	/**
	 * Options required to define the public-facing fuctionalities.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var object
	 */
	protected $options = null;

	/**
	 * The Head class instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Head
	 */
	protected $wphead = null;

	/**
	 * The Buttons class instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Buttons
	 */
	protected $buttons = null;

	/**
	 * APIRoutes instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var APIRoutes
	 */
	private $routes = null;

	/**
	 * Constructor.
	 *
	 * Initialize the class, set its properties, load the dependencies,
	 * and run the WordPress Hooks to enqueue styles and JavaScripts
	 * in the public-facing side, and register custom API Routes
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array 		$args {
	 *     An array of common arguments of the plugin.
	 *
	 *     @type string $plugin_name    The unique identifier of this plugin.
	 *     @type string $plugin_opts    The unique identifier or prefix for database names.
	 *     @type string $version        The plugin version number.
	 * }
	 * @param ThemeSupports $supports 	The ThemeSupports instance.
	 */
	function __construct( array $args, ThemeSupports $supports ) {

		$this->args = $args;

		$this->plugin_name = $args['plugin_name'];
		$this->plugin_opts = $args['plugin_opts'];
		$this->version = $args['version'];

		$this->path_dir = plugin_dir_path( __FILE__ );
		$this->path_url = plugin_dir_url( __FILE__ );

		$this->supports = $supports;

		$this->requires();
		$this->hooks();
	}

	/**
	 * Load the required dependencies.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function requires() {

		require_once( $this->path_dir . 'partials/class-metas.php' );
		require_once( $this->path_dir . 'partials/class-wp-head.php' );
		require_once( $this->path_dir . 'partials/class-endpoints.php' );
		require_once( $this->path_dir . 'partials/class-buttons.php' );
		require_once( $this->path_dir . 'partials/class-api-routes.php' );
	}

	/**
	 * Run Filters and Actions required.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function hooks() {

		add_action( 'init', array( $this, 'setups' ) );
		add_action( 'init', array( $this, 'enqueue_styles' ) );
		add_action( 'init', array( $this, 'enqueue_scripts' ) );
		add_action( 'init', array( $this, 'api_routes_setups' ) );
	}

	/**
	 * Run the setups.
	 *
	 * The setups may involve running some Classes, Functions, and if necessary, WordPress Hooks
	 * that are required to render the public-facing side.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function setups() {

		$this->metas = new Metas( $this->args );
		$this->wphead = new WPHead( $this->args, $this->metas );
		$this->buttons = new Buttons( $this->args, $this->metas, $this->supports );

		$this->options = (object) array(
			'advanced' => get_option( "{$this->plugin_opts}_advanced" ),
			'modes' => get_option( "{$this->plugin_opts}_modes" ),
		);

		$this->register_scripts();
	}

	/**
	 * Register JavaScripts handles.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_scripts() {

		if ( $this->is_load_routes() ) {
			wp_register_script( $this->plugin_name, $this->path_url . 'js/app.js', array( 'jquery', 'underscore', 'backbone' ), $this->version, true );
		} else {
			wp_register_script( $this->plugin_name, $this->path_url . 'js/scripts.js', array( 'jquery' ), $this->version, true );
		}
	}

	/**
	 * Load the stylesheets for the public-facing side.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_styles() {

		if ( $this->is_load_stylesheet() ) {
			wp_enqueue_style( $this->plugin_name, $this->path_url . 'css/styles.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Load the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name );
	}

	/**
	 * Register custom WP-API Routes
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function api_routes_setups() {

		if ( $this->is_load_routes() ) {

			$routes = new APIRoutes( $this->args, $this->metas );

			add_filter( 'rest_api_init', array( $routes, 'register_routes' ) );
			add_action( 'wp_enqueue_scripts', array( $routes, 'localize_scripts' ) );
		}
	}

	/**
	 * Is the stylesheet should be loaded?
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return boolean 	Return 'false' if the Theme being set the 'stylesheet' to 'true',
	 * 					via the 'add_theme_support' function. It also return 'false' if the
	 * 					'Enable Stylesheet' is unchecked.
	 */
	protected function is_load_stylesheet() {

		if ( $this->supports->is_theme_support( 'stylesheet' ) ) {
			return false;
		}

		if ( isset( $this->options->advanced['enableStylesheet'] ) ) {
			$option = (bool) $this->options->advanced['enableStylesheet'];
			return $option;
		}

		return true;
	}

	/**
	 * Is the API Routes should be loaded?
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return boolean 	Return 'true' if the Buttons Mode is 'json',
	 * 					and 'false' if the Buttons Mode is 'html'.
	 */
	protected function is_load_routes() {

		if ( 'json' === $this->supports->is_theme_support( 'buttons-mode' ) ||
			 'json' === $this->options->modes['buttonsMode'] ) {
			return true;
		}

		return false;
	}
}
