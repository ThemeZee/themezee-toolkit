<?php
/*
Plugin Name: ThemeZee Toolkit
Plugin URI: http://themezee.com/addons/widget-bundle/
Description: Includes several new custom sidebar widgets to show your best content and information.
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
		
		// Define Plugin Name
		define( 'TZTK_NAME', 'ThemeZee Toolkit');

		// Define Version Number
		define( 'TZTK_VERSION', '1.0' );
		
		// Define Plugin Name
		define( 'TZTK_PRODUCT_ID', 41305);

		// Define Update API URL
		define( 'TZTK_STORE_API_URL', 'https://themezee.com' ); 

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

		// Enqueue Frontend Widget Styles
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );
		
		// Include Widget Visibility Class if Jetpack is not active
		add_action( 'init',  array( __CLASS__, 'widget_visibility_class' ), 11 );
		
		// Add Toolkit Box to Add-on Overview Page
		add_action('themezee_addons_overview_page', array( __CLASS__, 'addon_overview_page' ) );
		
	}

	
	/**
	 * Enqueue Toolkit Styles
	 *
	 * @return void
	 */
	static function enqueue_styles() {
		
		// Return early if theme handles styling
		if ( current_theme_supports( 'themezee-toolkit' ) ) :
			return;
		endif;
		
		// Enqueue BCW Plugin Stylesheet
		wp_enqueue_style('themezee-toolkit', TZTK_PLUGIN_URL . '/assets/css/themezee-toolkit.css', array(), TZTK_VERSION );
		
	}
	
	
	/* Enqueue Widget Visibility Class */
	static function widget_visibility_class() {
		
		// Do not run when Jetpack is active
		if ( class_exists( 'Jetpack_Widget_Conditions' ) )
			return;
		
		// Get Plugin Options
		$options = TZTK_Settings::instance();
		
		// Include Widget Visibility class
		if( $options->get('widget_visibility') == true ) :
			require TZTK_PLUGIN_DIR . '/includes/class-tztk-widget-visibility.php';
		endif;
		
	}
	
	
	/**
	 * Add widget bundle box to addon overview admin page
	 *
	 * @return void
	 */
	static function addon_overview_page() { 
	
		$plugin_data = get_plugin_data( __FILE__ );
		
		?>

		<dl><dt><h4><?php echo esc_html( $plugin_data['Name'] ); ?> <?php echo esc_html( $plugin_data['Version'] ); ?></h4></dt>
			<dd>
				<p>
					<?php echo wp_kses_post( $plugin_data['Description'] ); ?><br/>
				</p>
				<p>
					<a href="<?php echo admin_url( 'admin.php?page=themezee-add-ons&tab=widgets' ); ?>" class="button button-primary"><?php _e('Plugin Settings', 'themezee-toolkit'); ?></a> 
					<a href="<?php echo admin_url( 'plugins.php?s=ThemeZee+Widget+Bundle' ); ?>" class="button button-secondary"><?php _e('Deactivate', 'themezee-toolkit'); ?></a>
				</p>
				
			</dd>
		</dl>
		
		<?php
	}
	
}

// Run Plugin
ThemeZee_Toolkit::setup();

endif;