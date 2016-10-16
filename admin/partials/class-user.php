<?php
/**
 * Admin: SettingsUser class
 *
 * @author Thoriq Firdaus <tfirdau@outlook.com>
 *
 * @package WPSocialManager
 * @subpackage Admin\User
 */

namespace XCo\WPSocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

/**
 * The class used for adding or customizing the "Your Profile" screen.
 *
 * @since 1.0.0
 */
class SettingsUser extends OptionUtilities {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * The unique identifier or prefix for database names.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $plugin_opts;

	/**
	 * The absolut URL path to the plugin directory.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $path_url;

	/**
	 * Constructor.
	 *
	 * Run Hooks, and iInitialize properties value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args {
	 *     An array of common arguments of the plugin.
	 *
	 *     @type string $plugin_name 	The unique identifier of this plugin.
	 *     @type string $plugin_opts 	The unique identifier or prefix for database names.
	 *     @type string $version 		The plugin version number.
	 * }
	 */
	public function __construct( array $args ) {

		$this->plugin_name = $args['plugin_name'];
		$this->plugin_opts = $args['plugin_opts'];
		$this->version = $args['version'];

		$this->path_url = trailingslashit( plugin_dir_url( dirname( __FILE__ ) ) );

		$this->hooks();
	}

	/**
	 * Run Filters and Actions required.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function hooks() {

		add_action( 'load-user-edit.php', array( $this, 'load_page' ), -30 );
		add_action( 'load-profile.php', array( $this, 'load_page' ), -30 );

		add_action( 'show_user_profile', array( $this, 'add_social_profiles' ), -30 );
		add_action( 'edit_user_profile', array( $this, 'add_social_profiles' ), -30 );
		add_action( 'personal_options_update', array( $this, 'save_social_profiles' ), -30 );
		add_action( 'edit_user_profile_update', array( $this, 'save_social_profiles' ), -30 );
		add_action( 'edit_user_profile_update', array( $this, 'save_social_profiles' ), -30 );
	}

	/**
	 * Print social inputs.
	 *
	 * A collection of additional text input types to allow user
	 * add their social profile usernames.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_User $user The WordPress user object.
	 */
	public function add_social_profiles( $user ) {

		$meta = get_the_author_meta( $this->plugin_opts, $user->ID );
		$profiles = self::get_social_profiles(); ?>

		<h2><?php echo esc_html__( 'Social Profiles', 'wp-social-manager' ); ?></h2>
		<p><?php echo esc_html__( 'Social profile or page connected to this user.', 'wp-social-manager' ); ?></p>
		<table class="form-table">

		<?php wp_nonce_field( 'wp_social_manager_user', 'wp_social_manager_social_profiles' ); ?>

		<?php foreach ( $profiles as $key => $data ) :

			$key   = sanitize_key( $key );
			$value = isset( $meta[ $key ] ) ? $meta[ $key ] : '';
			$label = isset( $data['label'] ) ? $data['label'] : '';
			$props = self::get_social_properties( $key ); ?>
			<tr>
				<th><label for="<?php echo esc_attr( "field-user-{$key}" ); ?>"><?php echo esc_html( $label ); ?></label></th>
				<td>
					<input type="text" name="<?php echo esc_attr( "{$this->plugin_opts}[{$key}]" ); ?>" id="<?php echo esc_attr( "field-user-{$key}" ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text account-profile-control code" data-url="<?php echo esc_attr( $props['url'] ); ?>">
					<?php if ( isset( $data['description'] ) && ! empty( $data['description'] ) ) : ?>
					<p class="description"><?php echo wp_kses_post( $data['description'] ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>

		</table>
	<?php

	}

	/**
	 * Save and update custom input in the "Profile" edit screen.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param integer $user_id  The user ID who being edited in the Profile edit screen.
	 */
	public function save_social_profiles( $user_id ) {

		if ( ! isset( $_POST['wp_social_manager_social_profiles'] ) ||
			 ! wp_verify_nonce( $_POST['wp_social_manager_social_profiles'], 'wp_social_manager_user' ) ) {
			wp_die( esc_html__( 'Bummer! you do not have the authority to save this inputs.', 'wp-social-manager' ) );
		}

		$profiles = (array) $_POST[ $this->plugin_opts ];

		foreach ( $profiles as $key => $value ) {
			$key = sanitize_key( $key );
			$profiles[ $key ] = sanitize_text_field( $value );
		}
		if ( current_user_can( 'edit_user' ) ) {
			update_user_meta( $user_id, $this->plugin_opts, $profiles );
		}
	}

	/**
	 * Load something on the screen.
	 *
	 * This is a method if we want to load typically like a stylesheet, scripts, and inline code
	 * when the "Your Profile" screen is viewed.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function load_page() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), -30 );
	}

	/**
	 * Enqueue scripts and stylesheet.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_scripts() {
		$file = 'preview-profile';
		wp_enqueue_script( "{$this->plugin_name}-{$file}", "{$this->path_url}js/{$file}.js", array( 'jquery', 'underscore', 'backbone' ), $this->version, true );
	}
}
