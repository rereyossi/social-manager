<?php
/**
 * Public: Meta class
 *
 * @package SocialManager
 * @subpackage Public\Metas
 */

namespace NineCodes\SocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

use \DOMDocument;

/**
 * The class to generate meta data.
 *
 * @since 1.0.0
 */
final class Metas {

	/**
	 * The unique identifier or prefix for database names.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $option_slug;

	/**
	 * The options required to render the meta data.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var null
	 */
	protected $options;

	/**
	 * Constructor.
	 *
	 * Run the WordPress Hooks, add meta tags in the 'head' tag.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Plugin $plugin The Plugin class instance.
	 */
	function __construct( Plugin $plugin ) {

		$this->plugin = $plugin;
		$this->option_slug = $plugin->get_opts();
	}

	/**
	 * Utility method to check if the meta option is enabled.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return boolean True if meta is enabled, false if not.
	 */
	public function is_meta_enabled() {

		return (bool) $this->get_site_meta( 'enabled' );
	}

	/**
	 * Utility method to get the site meta data.
	 *
	 * This data is used for the homepage and sometimes
	 * acts as the default value of if specific meta data
	 * is not available for particular page.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param  string $which Meta array key.
	 * @return mixed
	 */
	public function get_site_meta( $which ) {

		if ( ! $which ) {
			return;
		}

		return $this->plugin->get_option( 'metas_site', $which );
	}

	/**
	 * Utility method to get the post meta data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param  integer $id    The post ID number.
	 * @param  string  $which The the meta array key.
	 * @return string|array
	 */
	public function get_post_meta( $id, $which ) {

		if ( ! $id || ! $which ) {
			return;
		}

		$post_meta = get_post_meta( $id, $this->option_slug, true );

		/**
		 * If the post_meta is empty it means the meta has not yet
		 * been created.
		 */
		if ( empty( $post_meta ) ) {
			return null;
		}

		return isset( $post_meta[ $which ] ) ? $post_meta[ $which ] : null;
	}

	/**
	 * The method to get the website name / brand.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The website name / brand
	 */
	public function get_site_name() {

		$name = $this->get_site_meta( 'name' );
		$name = $name ? $name : get_bloginfo( 'name' );

		return wp_kses( $name, array() );
	}

	/**
	 * The method to get the website title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The website title
	 */
	public function get_site_title() {
		return wp_kses( wp_get_document_title(), array() );
	}

	/**
	 * The method to get the website description.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The website description
	 */
	public function get_site_description() {

		$description = $this->get_site_meta( 'description' );
		$description = $description ? $description : get_bloginfo( 'description' );

		if ( is_archive() ) {
			$term_description = term_description();
			$description = $term_description ? $term_description : $description;
		}

		if ( is_author() ) {
			$author = get_queried_object();
			$description = get_the_author_meta( 'description', (int) $author->ID );
		}

		return wp_kses( trim( $description ), array() );
	}

	/**
	 * The method to get the website url.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The website url
	 */
	public function get_site_url() {
		$url = get_site_url();
		return esc_url( $url );
	}

	/**
	 * The method to get the website image.
	 *
	 * This image should represent the website.
	 * It generally could be a favicon, or a logo.
	 *
	 * In the author archive, the image retrieved will be the author
	 * Gravatar image profile.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array An array of image data (src, width, and height)
	 */
	public function get_site_image() {

		$attachment_id = $this->get_site_meta( 'image' );
		$attachment_id = $attachment_id ? $attachment_id : get_theme_mod( 'custom_logo' );

		if ( is_author() ) {

			$author = get_queried_object();
			$avatar = get_avatar_url( $author->ID, array( 'size' => 180 ) );
			return array(
					'src' => $avatar,
					'width' => 180,
					'height' => 180,
				);
		}

		if ( $attachment_id ) {

			list( $src, $width, $height ) = wp_get_attachment_image_src( $attachment_id, 'full', true );

			return array(
				'src' => esc_url( $src ),
				'width' => (int) $width,
				'height' => (int) $height,
			);
		}
	}

	/**
	 * The method to get the "post" title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param integer $id The post ID.
	 * @return string The "post" title
	 */
	public function get_post_title( $id ) {

		if ( $this->is_meta_enabled() ) {
			$title = $this->get_post_meta( $id, 'post_title' );
		}

		if ( ! $title || empty( $title ) ) {
			$post = get_post( $id );
			$title = apply_filters( 'the_title', $post->post_title );
		}

		return wp_kses( $title, array() );
	}

	/**
	 * The method to get the "post" description.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param integer $id The post ID.
	 * @return string The "post" description
	 */
	public function get_post_description( $id ) {

		if ( $this->is_meta_enabled() ) {
			$description = $this->get_post_meta( $id, 'post_excerpt' );
		}

		if ( ! $description || empty( $description ) ) {

			$post = get_post( $id );
			$description = $post->post_excerpt;

			if ( empty( $post->post_excerpt ) ) {
				$description = wp_trim_words( $post->post_content, 30, '...' );
			}
		}

		return wp_kses( $description, array() );
	}

	/**
	 * The method to get the "post" image.
	 *
	 * This method will try to retrieve image from a number of sources,
	 * and return the image data based on the following priority order:
	 * 1. Post Meta Image
	 * 2. Post Featured Image
	 * 3. Post Content First Image
	 * 4. Site Meta Image
	 * 5. Site Custom Logo (Customizer)
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param  integer $id The post ID.
	 * @return array      The image data consisting of the image source, width, and height
	 */
	public function get_post_image( $id ) {

		$attachment_id = null;

		if ( $this->is_meta_enabled() ) {
			$attachment_id = $this->get_post_meta( $id, 'post_thumbnail' ); // Post Meta Image.
		}

		if ( ! $attachment_id ) {
			$attachment_id = get_post_thumbnail_id( $id ); // Post Featured Image.
		}

		if ( $attachment_id ) {

			list( $src, $width, $height ) = wp_get_attachment_image_src( $attachment_id, 'full', true );

			return array(
				'src' => esc_url( $src ),
				'width' => absint( $width ),
				'height' => absint( $height ),
			);
		}

		$post = get_post( $id );

		libxml_use_internal_errors( true );

		$dom = new DOMDocument();
		$dom->loadHTML( mb_convert_encoding( $post->post_content, 'HTML-ENTITIES', 'UTF-8' ) );
		$images = $dom->getElementsByTagName( 'img' );

		if ( 0 !== $images->length ) {

			$src = $images->item( 0 )->getAttribute( 'src' );
			$image = getimagesize( $images->item( 0 )->getAttribute( 'src' ) );

			if ( $image ) {

				list( $width, $height ) = $image;

				return array(
					'src' => esc_url( $src ),
					'width' => absint( $width ),
					'height' => absint( $height ),
				);
			}
		}

		if ( $this->is_meta_enabled() ) {

			$site_image = $this->get_site_image(); // Site Meta Image.

			if ( is_array( $site_image ) && ! empty( $site_image ) ) {
				return $site_image;
			}
		}

		$site_logo = (int) get_theme_mod( 'custom_logo' ); // Site Custom Logo.

		if ( $site_logo ) {

			list( $src, $width, $height ) = wp_get_attachment_image_src( $site_logo, 'full', true );

			return array(
				'src' => esc_url( $src ),
				'width' => absint( $width ),
				'height' => absint( $height ),
			);
		}
	}

	/**
	 * The method to get the "post" url / permalink.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param integer $id The post ID.
	 * @return string The "post" url / permalink
	 */
	public function get_post_url( $id ) {
		return esc_url( get_permalink( $id ) );
	}

	/**
	 * The method to get the "post" author.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param  integer $id The post ID.
	 * @return array {
	 *     @type string $display_name 	The author name.
	 *     @type string $profiles 		An array of social media profiles associated
	 *           						with the author.
	 * }
	 */
	public function get_post_author( $id ) {

		$post = get_post( $id );
		$name = get_the_author_meta( 'display_name', $post->post_author );
		$profiles = get_the_author_meta( $this->option_slug, $post->post_author );

		return array(
			'display_name' => $name,
			'profiles' => $profiles,
		);
	}
}
