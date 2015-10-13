<?php
/*
Plugin Name: ThemeZee Toolkit
Plugin URI: http://themezee.com/addons/toolkit/
Description: The ThemeZee Toolkit is a collection of useful small plugins and features, neatly bundled into a single plugin. It includes modules for Widget Visibility, Header & Footer Scripts, Custom CSS and a lot more.
Author: ThemeZee
Author URI: http://themezee.com/
Version: 1.0
Text Domain: themezee-toolkit
Domain Path: /languages/
License: GPL v3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

ThemeZee Toolkit
Copyright(C) 2015, ThemeZee.com - support@themezee.com

*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Use class to avoid namespace collisions
if ( ! class_exists('ThemeZee_Toolkit') ) :


/**
 * Main ThemeZee_Toolkit Class
 *
 * @package ThemeZee Toolkit
 */
class ThemeZee_Toolkit {

	/**
	 * Call all Functions to setup the Plugin
	 *
	 * @uses ThemeZee_Toolkit::constants() Setup the constants needed
	 * @uses ThemeZee_Toolkit::includes() Include the required files
	 * @uses ThemeZee_Toolkit::setup_actions() Setup the hooks and actions
	 * @return void
	 */
	static function setup() {
	
		// Setup Constants
		self::constants();
		
		// Setup Translation
		load_plugin_textdomain( 'themezee-toolkit', false, TZTK_PLUGIN_DIR . '/languages/' );
	
		// Include Files
		self::includes();
		
		// Setup Action Hooks
		self::setup_actions();
		
	}
	
	
	/**
	 * Setup plugin constants
	 *
	 * @return void
	 */
	static function constants() {
		
		// Define Version Number
		define( 'TZTK_VERSION', '1.0' );
		
		// Plugin Folder Path
		define( 'TZTK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

		// Plugin Folder URL
		define( 'TZTK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

		// Plugin Root File
		define( 'TZTK_PLUGIN_FILE', __FILE__ );
		
	}
	
	
	/**
	 * Include required files
	 *
	 * @return void
	 */
	static function includes() {

		// Include Admin Classes
		require_once TZTK_PLUGIN_DIR . '/includes/class-themezee-addons-page.php';
		
		// Include Settings Classes
		require_once TZTK_PLUGIN_DIR . '/includes/settings/class-tztk-settings.php';
		require_once TZTK_PLUGIN_DIR . '/includes/settings/class-tztk-settings-page.php';
		
	}
	
	
	/**
	 * Setup Action Hooks
	 *
	 * @see https://codex.wordpress.org/Function_Reference/add_action WordPress Codex
	 * @return void
	 */
	static function setup_actions() {

		// Include active modules
		add_action( 'init',  array( __CLASS__, 'modules' ), 11 );
		
		// Add Header and Footer Scripts in Frontend
        add_action( 'wp_head', array( __CLASS__, 'header_scripts' ) );
		add_action( 'wp_footer', array( __CLASS__, 'footer_scripts' ) );
		
		// Add Toolkit Box to Add-on Overview Page
		add_action( 'themezee_addons_overview_page', array( __CLASS__, 'addon_overview_page' ) );
		
	}

	
	/**
	 * Include active Modules
	 *
	 * @return void
	 */
	static function modules() {
		
		// Get Plugin Options
		$options = TZTK_Settings::instance();
		
		// Include Widget Visibility class unless it is already activated with Jetpack
		if( true == $options->get('widget_visibility') and ! class_exists( 'Jetpack_Widget_Conditions' ) ) :
			
			require TZTK_PLUGIN_DIR . '/includes/class-tztk-widget-visibility.php';
		
		endif;
		
		// Include Widget Visibility class unless it is already activated with Jetpack
		if( true == $options->get('widget_visibility') and ! class_exists( 'Jetpack_Carousel' ) ) :
			
			require TZTK_PLUGIN_DIR . '/carousel/class-tztk-gallery-carousel.php';
		
		endif;
		
	}
	
	
	/**
	 * Output Scripts in Header
	 *
	 * @return void
	 */
	static function header_scripts() {
		
		self::output_scripts( 'header_scripts' );
		
	}
	
	
	/**
	 * Output Scripts in Footer
	 *
	 * @return void
	 */
	static function footer_scripts() {
		
		self::output_scripts( 'footer_scripts' );
		
	}
	

	/**
	 * Output Scripts from Database
	 *
	 * @param string $setting Name of the setting
	 * @return void
	 */
	static function output_scripts( $setting ) {
		
		// Ignore admin, feed, robots and trackbacks
		if ( is_admin() or is_feed() or is_robots() or is_trackback() ) :
			return;
		endif;
		
		// Get Plugin Options
		$options = TZTK_Settings::instance();
		
		// Set Scripts
		$scripts = trim( $options->get( $setting ) );
		
		// Output Scripts
		if( $scripts <> '' ) :
			
			echo stripslashes( $scripts );
		
		endif;
		
	}
	
	
	/**
	 * Add Toolkit box to addon overview admin page
	 *
	 * @return void
	 */
	static function addon_overview_page() { 
	
		$plugin_data = get_plugin_data( __FILE__ );
		
		?>

		<dl>
			<dt>
				<h4><?php echo esc_html( $plugin_data['Name'] ); ?></h4>
				<span><?php printf( __( 'Version %s', 'themezee-toolkit'),  esc_html( $plugin_data['Version'] ) ); ?></span>
			</dt>
			<dd>
				<p><?php echo wp_kses_post( $plugin_data['Description'] ); ?><br/></p>
				<a href="<?php echo admin_url( 'admin.php?page=themezee-addons&tab=toolkit' ); ?>" class="button button-primary"><?php _e( 'Plugin Settings', 'themezee-toolkit' ); ?></a> 
				<a href="<?php echo esc_url( 'http://themezee.com/docs/toolkit/'); ?>" class="button button-secondary" target="_blank"><?php _e( 'View Documentation', 'themezee-toolkit' ); ?></a>
			</dd>
		</dl>
		
		<?php
	}
	
}

// Run Plugin
ThemeZee_Toolkit::setup();

endif;