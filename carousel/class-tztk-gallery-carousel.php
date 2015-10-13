<?php

/*
Plugin Name: Jetpack Carousel
Plugin URL: http://wordpress.com/
Description: Transform your standard image galleries into an immersive full-screen experience.
Version: 0.1
Author: Automattic

Released under the GPL v.2 license.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

// Use class to avoid namespace collisions
if ( ! class_exists( 'TZTK_Gallery_Carousel' ) ) :

class TZTK_Gallery_Carousel {
	/** Singleton *************************************************************/
	
	private static $instance;
	
	public $prebuilt_widths = array( 370, 700, 1000, 1200, 1400, 2000 );

	public $first_run = true;

	public $in_gallery = false;

	
	/**
     * Creates or returns an instance of this class.
     *
     * @return TZTK_Gallery_Carousel A single instance of this class.
     */
	public static function instance() {
 
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
 
        return self::$instance;
    }
	
	
	/**
	 * Plugin Setup
	 *
	 * @return void
	*/
	function __construct() {
		if ( $this->maybe_disable_jp_carousel() )
			return;

		if ( ! is_admin() ) {
		
			// If on front-end, do the Carousel thang.
			/**
			 * Filter the array of default prebuilt widths used in Carousel.
			 *
			 * @since 1.6.0
			 *
			 * @param array $this->prebuilt_widths Array of default widths.
			 */
			$this->prebuilt_widths = apply_filters( 'jp_carousel_widths', $this->prebuilt_widths );
			add_filter( 'post_gallery', array( $this, 'enqueue_assets' ), 1000, 2 ); // load later than other callbacks hooked it
			add_filter( 'post_gallery', array( $this, 'set_in_gallery' ), -1000 );
			add_filter( 'gallery_style', array( $this, 'add_data_to_container' ) );
			add_filter( 'wp_get_attachment_image_attributes', array( $this, 'add_data_to_images' ), 10, 2 );
		}

	}

	function maybe_disable_jp_carousel() {
		/**
		 * Allow third-party plugins or themes to disable Carousel.
		 *
		 * @since 1.6.0
		 *
		 * @param bool false Should Carousel be disabled? Default to fase.
		 */
		return apply_filters( 'jp_carousel_maybe_disable', false );
	}

	
	function display_bail_message( $output= '' ) {
		// Displays a message on top of gallery if carousel has bailed
		$message = '<div class="jp-carousel-msg"><p>';
		$message .= __( 'Gallery Carousel has been disabled, because another plugin or your theme is overriding the [gallery] shortcode.', 'themezee-toolkit' );
		$message .= '</p></div>';
		// put before gallery output
		$output = $message . $output;
		return $output;
	}

	function enqueue_assets( $output ) {
		if (
			! empty( $output ) &&
			/**
			 * Allow third-party plugins or themes to force-enable Carousel.
			 *
			 * @since 1.9.0
			 *
			 * @param bool false Should we force enable Carousel? Default to false.
			 */
			! apply_filters( 'jp_carousel_force_enable', false )
		) {
			// Bail because someone is overriding the [gallery] shortcode.
			remove_filter( 'gallery_style', array( $this, 'add_data_to_container' ) );
			remove_filter( 'wp_get_attachment_image_attributes', array( $this, 'add_data_to_images' ) );
			// Display message that carousel has bailed, if user is super_admin
			if ( is_super_admin() ) {
				add_filter( 'post_gallery', array( $this, 'display_bail_message' ) );
			}
			return $output;
		}

		/**
		 * Fires when thumbnails are shown in Carousel.
		 *
		 * @since 1.6.0
		 **/
		do_action( 'jp_carousel_thumbnails_shown' );

		if ( $this->first_run ) {
			wp_enqueue_script( 'jetpack-carousel', plugins_url( 'jetpack-carousel.js', __FILE__ ), array( 'jquery.spin' ), TZTK_VERSION, true );

			// Note: using  home_url() instead of admin_url() for ajaxurl to be sure  to get same domain on wpcom when using mapped domains (also works on self-hosted)
			// Also: not hardcoding path since there is no guarantee site is running on site root in self-hosted context.
			$localize_strings = array(
				'widths'               => $this->prebuilt_widths,
				'lang'                 => strtolower( substr( get_locale(), 0, 2 ) ),
				'nonce'                => wp_create_nonce( 'carousel_nonce' ),
				'download_original'    => sprintf( __( 'View full size <span class="photo-size">%1$s<span class="photo-size-times">&times;</span>%2$s</span>', 'themezee-toolkit' ), '{0}', '{1}' ),
			);

			/**
			 * Filter the strings passed to the Carousel's js file.
			 *
			 * @since 1.6.0
			 *
			 * @param array $localize_strings Array of strings passed to the Jetpack js file.
			 */
			$localize_strings = apply_filters( 'jp_carousel_localize_strings', $localize_strings );
			wp_localize_script( 'jetpack-carousel', 'jetpackCarouselStrings', $localize_strings );
			if( is_rtl() ) {
				wp_enqueue_style( 'jetpack-carousel', plugins_url( '/rtl/jetpack-carousel-rtl.css', __FILE__ ), array(), TZTK_VERSION );
			} else {
				wp_enqueue_style( 'jetpack-carousel', plugins_url( 'jetpack-carousel.css', __FILE__ ), array(), TZTK_VERSION );
			}

			/**
			 * Fires after carousel assets are enqueued for the first time.
			 * Allows for adding additional assets to the carousel page.
			 *
			 * @since 1.6.0
			 *
			 * @param bool $first_run First load if Carousel on the page.
			 * @param array $localized_strings Array of strings passed to the Jetpack js file.
			 */
			do_action( 'jp_carousel_enqueue_assets', $this->first_run, $localize_strings );

			$this->first_run = false;
		}

		return $output;
	}

	function set_in_gallery( $output ) {
		$this->in_gallery = true;
		return $output;
	}

	function add_data_to_images( $attr, $attachment = null ) {

		// not in a gallery?
		if ( ! $this->in_gallery ) {
			return $attr;
		}

		$attachment_id   = intval( $attachment->ID );
		$orig_file       = wp_get_attachment_image_src( $attachment_id, 'full' );
		$orig_file       = isset( $orig_file[0] ) ? $orig_file[0] : wp_get_attachment_url( $attachment_id );
		$meta            = wp_get_attachment_metadata( $attachment_id );
		$size            = isset( $meta['width'] ) ? intval( $meta['width'] ) . ',' . intval( $meta['height'] ) : '';

		 /*
		 * Note: Cannot generate a filename from the width and height wp_get_attachment_image_src() returns because
		 * it takes the $content_width global variable themes can set in consideration, therefore returning sizes
		 * which when used to generate a filename will likely result in a 404 on the image.
		 * $content_width has no filter we could temporarily de-register, run wp_get_attachment_image_src(), then
		 * re-register. So using returned file URL instead, which we can define the sizes from through filename
		 * parsing in the JS, as this is a failsafe file reference.
		 *
		 * EG with Twenty Eleven activated:
		 * array(4) { [0]=> string(82) "http://vanillawpinstall.blah/wp-content/uploads/2012/06/IMG_3534-1024x764.jpg" [1]=> int(584) [2]=> int(435) [3]=> bool(true) }
		 *
		 * EG with Twenty Ten activated:
		 * array(4) { [0]=> string(82) "http://vanillawpinstall.blah/wp-content/uploads/2012/06/IMG_3534-1024x764.jpg" [1]=> int(640) [2]=> int(477) [3]=> bool(true) }
		 */

		$medium_file_info = wp_get_attachment_image_src( $attachment_id, 'medium' );
		$medium_file      = isset( $medium_file_info[0] ) ? $medium_file_info[0] : '';

		$large_file_info  = wp_get_attachment_image_src( $attachment_id, 'large' );
		$large_file       = isset( $large_file_info[0] ) ? $large_file_info[0] : '';

		$attachment       = get_post( $attachment_id );
		$attachment_title = wptexturize( $attachment->post_title );
		$attachment_desc  = wpautop( wptexturize( $attachment->post_content ) );

		$attr['data-attachment-id']     = $attachment_id;
		$attr['data-orig-file']         = esc_attr( $orig_file );
		$attr['data-orig-size']         = $size;
		$attr['data-image-title']       = esc_attr( $attachment_title );
		$attr['data-image-description'] = esc_attr( $attachment_desc );
		$attr['data-medium-file']       = esc_attr( $medium_file );
		$attr['data-large-file']        = esc_attr( $large_file );

		return $attr;
	}

	function add_data_to_container( $html ) {
		global $post;

		if ( isset( $post ) ) {

			$extra_data = array(
				'data-carousel-extra' => array(
					'permalink' => get_permalink( $post->ID )
					)
				);

			foreach ( (array) $extra_data as $data_key => $data_values ) {
				$html = str_replace( '<div ', '<div ' . esc_attr( $data_key ) . "='" . json_encode( $data_values ) . "' ", $html );
			}
		}

		return $html;
	}
}

// Run Gallery Carousel Class
TZTK_Gallery_Carousel::instance();

endif;