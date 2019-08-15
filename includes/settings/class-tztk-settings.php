<?php
/***
 * TZTK Settings Class
 *
 * Registers all plugin settings with the WordPress Settings API.
 *
 * @link https://codex.wordpress.org/Settings_API
 * @package ThemeZee Toolkit
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Use class to avoid namespace collisions
if ( ! class_exists( 'TZTK_Settings' ) ) :

	class TZTK_Settings {
		/** Singleton *************************************************************/

		/**
		 * @var instance The one true TZTK_Settings instance
		 */
		private static $instance;

		/**
		 * @var options Plugin options array
		 */
		private $options;

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @return TZTK_Settings A single instance of this class.
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
		public function __construct() {

			// Register Settings
			add_action( 'admin_init', array( $this, 'register_settings' ) );

			// Merge Plugin Options Array from Database with Default Settings Array
			$this->options = wp_parse_args( get_option( 'tztk_settings', array() ), $this->default_settings() );
		}

		/**
		 * Get the value of a specific setting
		 *
		 * @return mixed
		*/
		public function get( $key, $default = false ) {
			$value = ! empty( $this->options[ $key ] ) ? $this->options[ $key ] : $default;
			return $value;
		}

		/**
		 * Get all settings
		 *
		 * @return array
		*/
		public function get_all() {
			return $this->options;
		}

		/**
		 * Retrieve default settings
		 *
		 * @return array
		*/
		public function default_settings() {

			$default_settings = array();

			foreach ( $this->get_registered_settings() as $key => $option ) :

				$default_settings[ $key ] = isset( $option['default'] ) ? $option['default'] : false;

			endforeach;

			return $default_settings;
		}

		/**
		 * Register all settings sections and fields
		 *
		 * @return void
		*/
		function register_settings() {

			// Make sure that options exist in database
			if ( false == get_option( 'tztk_settings' ) ) {
				add_option( 'tztk_settings' );
			}

			// Add Sections
			add_settings_section( 'tztk_settings_modules', esc_html__( 'Modules', 'themezee-toolkit' ), array( $this, 'module_section_intro' ), 'tztk_settings' );
			add_settings_section( 'tztk_settings_scripts', esc_html__( 'Header & Footer Scripts', 'themezee-toolkit' ), array( $this, 'scripts_section_intro' ), 'tztk_settings' );

			// Add Settings
			foreach ( $this->get_registered_settings() as $key => $option ) :

				$name    = isset( $option['name'] ) ? $option['name'] : '';
				$section = isset( $option['section'] ) ? $option['section'] : 'widgets';

				add_settings_field(
					'tztk_settings[' . $key . ']',
					$name,
					is_callable( array( $this, $option['type'] . '_callback' ) ) ? array( $this, $option['type'] . '_callback' ) : array( $this, 'missing_callback' ),
					'tztk_settings',
					'tztk_settings_' . $section,
					array(
						'id'      => $key,
						'name'    => isset( $option['name'] ) ? $option['name'] : null,
						'desc'    => ! empty( $option['desc'] ) ? $option['desc'] : '',
						'size'    => isset( $option['size'] ) ? $option['size'] : null,
						'max'     => isset( $option['max'] ) ? $option['max'] : null,
						'min'     => isset( $option['min'] ) ? $option['min'] : null,
						'step'    => isset( $option['step'] ) ? $option['step'] : null,
						'options' => isset( $option['options'] ) ? $option['options'] : '',
						'default' => isset( $option['default'] ) ? $option['default'] : '',
					)
				);

			endforeach;

			// Creates our settings in the options table
			register_setting( 'tztk_settings', 'tztk_settings', array( $this, 'sanitize_settings' ) );
		}

		/**
		 * Module Section Intro
		 *
		 * @return void
		*/
		function module_section_intro() {
			esc_html_e( 'Activate all the modules you want to use.', 'themezee-toolkit' );
		}

		/**
		 * Scripts Section Intro
		 *
		 * @return void
		*/
		function scripts_section_intro() {
			esc_html_e( 'Add your own code to the header or footer area of your theme.', 'themezee-toolkit' );
		}

		/**
		 * Sanitize the Plugin Settings
		 *
		 * @return array
		*/
		function sanitize_settings( $input = array() ) {

			if ( empty( $_POST['_wp_http_referer'] ) ) {
				return $input;
			}

			$saved = get_option( 'tztk_settings', array() );
			if ( ! is_array( $saved ) ) {
				$saved = array();
			}

			$settings = $this->get_registered_settings();
			$input    = $input ? $input : array();

			// Loop through each setting being saved and pass it through a sanitization filter.
			foreach ( $input as $key => $value ) :

				// Get the setting type (checkbox, select, etc).
				$type = isset( $settings[ $key ]['type'] ) ? $settings[ $key ]['type'] : false;

				if ( 'radio' === $type ) :

					$available_options = array_keys( $settings[ $key ]['options'] );
					$input[ $key ]     = in_array( $value, $available_options, true ) ? $value : $settings[ $key ]['default'];

				elseif ( 'textarea_html' === $type ) :

					if ( current_user_can( 'unfiltered_html' ) ) :
						$input[ $key ] = $value;
					else :
						$input[ $key ] = wp_kses_post( $value );
					endif;

				elseif ( 'checkbox' === $type ) :

					$input[ $key ] = $value; // Validate Checkboxes later.

				else :

					// Default Sanitization.
					$input[ $key ] = esc_html( $value );

				endif;

			endforeach;

			// Ensure a value is always passed for every checkbox.
			if ( ! empty( $settings ) ) :
				foreach ( $settings as $key => $setting ) :

					// Single checkbox.
					if ( isset( $settings[ $key ]['type'] ) && 'checkbox' == $settings[ $key ]['type'] ) :
						$input[ $key ] = ! empty( $input[ $key ] );
					endif;

				endforeach;
			endif;

			return array_merge( $saved, $input );
		}

		/**
		 * Retrieve the array of plugin settings
		 *
		 * @return array
		*/
		function get_registered_settings() {

			$settings = array(
				'widget_visibility' => array(
					'name'    => esc_html__( 'Widget Visibility', 'themezee-toolkit' ),
					'desc'    => esc_html__( 'Add "Visibility" tab to widget settings to set conditions where the widget should be displayed', 'themezee-toolkit' ),
					'section' => 'modules',
					'type'    => 'checkbox',
					'default' => false,
				),
				'infinite_scroll' => array(
					'name'    => esc_html__( 'Infinite Scroll', 'themezee-toolkit' ),
					'desc'    => esc_html__( 'Add support for infinite scrolling on the blog homepage and automatically load new posts on the same page', 'themezee-toolkit' ),
					'section' => 'modules',
					'type'    => 'checkbox',
					'default' => false,
				),
				'header_scripts' => array(
					'name'    => esc_html__( 'Header Scripts', 'themezee-toolkit' ),
					'desc'    => sprintf( esc_html__( 'These scripts will be printed to the %s section.', 'themezee-toolkit' ), '<code>&lt;head&gt;</code>' ),
					'section' => 'scripts',
					'type'    => 'textarea_html',
					'size'    => 'large',
				),
				'footer_scripts' => array(
					'name'    => esc_html__( 'Footer Scripts', 'themezee-toolkit' ),
					'desc'    => sprintf( esc_html__( 'These scripts will be printed above the %s tag.', 'themezee-toolkit' ), '<code>&lt;/body&gt;</code>' ),
					'section' => 'scripts',
					'type'    => 'textarea_html',
					'size'    => 'large',
				),
			);

			return apply_filters( 'tztk_settings', $settings );
		}

		/**
		 * Checkbox Callback
		 *
		 * Renders checkboxes.
		 *
		 * @param array $args Arguments passed by the setting
		 * @global $this->options Array of all the ThemeZee Toolkit Options
		 * @return void
		 */
		function checkbox_callback( $args ) {
			$checked = isset( $this->options[ $args['id'] ] ) ? checked( 1, $this->options[ $args['id'] ], false ) : '';
			$html    = '<input type="checkbox" id="tztk_settings[' . $args['id'] . ']" name="tztk_settings[' . $args['id'] . ']" value="1" ' . $checked . '/>';
			$html   .= '<label for="tztk_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

			echo $html;
		}

		/**
		 * Radio Callback
		 *
		 * Renders radio boxes.
		 *
		 * @param array $args Arguments passed by the setting.
		 * @global $this->options Array of all the ThemeZee Breadcrumbs Options
		 * @return void
		 */
		function radio_callback( $args ) {

			if ( ! empty( $args['options'] ) ) :
				foreach ( $args['options'] as $key => $option ) :
					$checked = false;

					if ( isset( $this->options[ $args['id'] ] ) && $this->options[ $args['id'] ] == $key ) {
						$checked = true;
					} elseif ( isset( $args['default'] ) && $args['default'] == $key && ! isset( $this->options[ $args['id'] ] ) ) {
						$checked = true;
					}

					echo '<input name="tzbc_settings[' . $args['id'] . ']"" id="tzbc_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked( true, $checked, false ) . '/>&nbsp;';
					echo '<label for="tzbc_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';

				endforeach;
			endif;
			echo '<p class="description">' . $args['desc'] . '</p>';
		}

		/**
		 * Textarea HTML Callback
		 *
		 * Renders textarea fields which allow HTML code.
		 *
		 * @param array $args Arguments passed by the setting.
		 * @global $this->options Array of all the ThemeZee Breadcrumbs Options
		 * @return void
		 */
		function textarea_html_callback( $args ) {

			if ( isset( $this->options[ $args['id'] ] ) ) {
				$value = $this->options[ $args['id'] ];
			} else {
				$value = isset( $args['default'] ) ? $args['default'] : '';
			}

			$size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
			$html  = '<textarea class="' . $size . '-text" cols="20" rows="5" id="tzbc_settings_' . $args['id'] . '" name="tzbc_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
			$html .= '<p class="description">' . $args['desc'] . '</p>';

			echo $html;
		}

		/**
		 * Missing Callback
		 *
		 * If a function is missing for settings callbacks alert the user.
		 *
		 * @since 1.3.1
		 * @param array $args Arguments passed by the setting
		 * @return void
		 */
		function missing_callback( $args ) {
			printf( esc_html__( 'The callback function used for the <strong>%s</strong> setting is missing.', 'themezee-toolkit' ), $args['id'] );
		}
	}

	// Run Setting Class.
	TZTK_Settings::instance();

endif;
