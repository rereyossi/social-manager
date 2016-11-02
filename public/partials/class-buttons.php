<?php
/**
 * Public: Buttons Class
 *
 * @package SocialManager
 * @subpackage Public\Buttons
 */

namespace NineCodes\SocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

use \DOMDocument;

/**
 * The Class that define the social buttons output.
 *
 * @since 1.0.0
 */
abstract class Buttons {

	/**
	 * The Plugin class instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * The Metas class instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Metas
	 */
	protected $metas;

	/**
	 * The Endpoints class instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Endpoints
	 */
	protected $endpoints;

	/**
	 * The ThemeSupports class instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var ThemeSupports
	 */
	protected $theme_supports;

	/**
	 * The WordPress post ID.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var integer
	 */
	protected $post_id;

	/**
	 * The plugin unique identifier.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $plugin_slug;

	/**
	 * The plugin option name.
	 * Sometimes used for meta key.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $option_slug;

	/**
	 * The button mode, 'json' or 'html'.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $mode;

	/**
	 * Constructor: Initialize the Buttons Class
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Endpoints $endpoints The Endpoints class instance.
	 */
	function __construct( Endpoints $endpoints ) {

		$this->endpoints = $endpoints;

		$this->metas = $endpoints->metas;
		$this->plugin = $endpoints->plugin;

		$this->plugin_slug = $endpoints->plugin->get_slug();
		$this->option_slug = $endpoints->plugin->get_opts();
		$this->theme_supports = $endpoints->plugin->get_theme_supports();

		$this->hooks();
	}

	/**
	 * Run Filters and Actions required.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return void
	 */
	protected function hooks() {

		add_action( 'wp_head', array( $this, 'setups' ), -30 );
		add_action( 'wp_footer', array( $this, 'buttons_tmpl' ), -30 );
	}

	/**
	 * Setup the buttons.
	 *
	 * The setups may involve running some Classes, Functions,
	 * and sometimes WordPress Hooks that are required to render
	 * the social buttons.
	 *
	 * Get the current WordPress post ID.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return void
	 */
	public function setups() {

		if ( is_singular() ) {
			$this->post_id = get_the_id();
		}
	}

	/**
	 * The buttons template script to use in JSON mode.
	 *
	 * @return void
	 */
	public function buttons_tmpl() {}

	/**
	 * Determine and generate the buttons item view.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $view The button view key (`icon`, `icon-text`, `text`).
	 * @param array  $args 	{
	 *     The button attributes.
	 *
	 *     @type string $site 	The site unique key (e.g. `facebook`, `twitter`, etc.).
	 *     @type string $icon 	The respective site icon.
	 *     @type string $label 	The site label / text.
	 * }
	 * @param array  $context The button attributes such as the 'site' name, button label,
	 *                        and the button icon.
	 * @return string The formatted HTML list element to display the button.
	 */
	protected function button_view( $view, $context, array $args ) {

		if ( ! $view || ! $context || empty( $args ) ) {
			return '';
		}

		$args = wp_parse_args( $args, array(
			'prefix'   => '',
			'site'     => '',
			'icon'     => '',
			'label'    => '',
			'endpoint' => '',
		) );

		if ( in_array( '', $args, true ) ) {
			return;
		}

		$prefix = $args['prefix'];
		$site = $args['site'];
		$icon = $args['icon'];
		$label = $args['label'];
		$endpoint = $args['endpoint'];

		$templates = array(
			'icon' => "<a class='{$prefix}-buttons__item item-{$site}' href='{$endpoint}' target='_blank' role='button' rel='nofollow'>{$icon}</a>",
			'text' => "<a class='{$prefix}-buttons__item item-{$site}' href='{$endpoint}' target='_blank' role='button' rel='nofollow'>{$label}</a>",
			'icon-text' => "<a class='{$prefix}-buttons__item item-{$site}' href='{$endpoint}' target='_blank' role='button' rel='nofollow'><span class='{$prefix}-buttons__item-icon'>{$icon}</span><span class='{$prefix}-buttons__item-text'>{$label}</span></a>",
		);

		$allowed_html = wp_kses_allowed_html( 'post' );
		$allowed_html['svg'] = array(
			'xmlns' => true,
			'viewbox' => true,
		);
		$allowed_html['path'] = array(
			'd' => true,
		);
		$allowed_html['use'] = array(
			'xlink:href' => true,
		);

		return isset( $templates[ $view ] ) ? wp_kses( $templates[ $view ], $allowed_html ) : '';
	}

	/**
	 * The function utility to get the button icon.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $site The site key e.g. 'facebook', 'twitter', etc.
	 * @return string The icon in SVG format.
	 */
	protected function get_button_icon( $site ) {
		return Helpers::get_social_icons( $site );
	}

	/**
	 * The function utility to get the button label (text)
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $site The button site key.
	 * @param string $context The button context, 'content' or 'image'.
	 * @return null|string Return null, if the context is incorrect or the key is unset.
	 */
	protected function get_button_label( $site, $context ) {

		if ( in_array( $context, array( 'content', 'image' ), true ) ) {
			$buttons = Options::button_sites( $context );
			return isset( $buttons[ $site ] ) ? $buttons[ $site ] : null;
		}

		return null;
	}

	/**
	 * The function utility to get the button mode.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string Whether JSON of HTML
	 */
	protected function get_buttons_mode() {

		if ( null !== $this->mode && in_array( $this->mode, array( 'html', 'json' ), true ) ) {
			return $this->mode;
		}

		$buttons_mode = $this->plugin->get_option( 'modes', 'buttons_mode' );

		if ( 'json' === $this->theme_supports->is( 'buttons-mode' ) ||
			 'json' === $buttons_mode ) {
			return 'json';
		}

		if ( 'html' === $this->theme_supports->is( 'buttons-mode' ) ||
			 'html' === $buttons_mode ) {
			return 'html';
		}
	}

	/**
	 * The function utility to get the attribute prefix
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string The attribute prefix.
	 */
	protected function get_button_attr_prefix() {
		return Helpers::get_attr_prefix();
	}

	/**
	 * The function utility to check if the content is rendered in AMP endpoint.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return boolean
	 */
	protected function in_amp() {

		return function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ? true : false;
	}
}
