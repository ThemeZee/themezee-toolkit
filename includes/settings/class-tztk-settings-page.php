<?php
/***
 * TZWB Settings Page Class
 *
 * Adds the menu link in the backend and displays the settings page.
 *
 * @package ThemeZee Widget Bundle
 */
 

 /* Use class to avoid namespace collisions */
if ( ! class_exists('TZWB_Settings_Page') ) :

class TZWB_Settings_Page {

	/**
	 * Setup the Settings Page class
	 *
	 * @return void
	*/
	static function setup() {
		
		// Add settings page to addon tabs
		add_filter( 'themezee_addons_settings_tabs', array( __CLASS__, 'add_settings_page' ) );
		
		// Hook settings page to addon page
		add_action( 'themezee_addons_page_widgets', array( __CLASS__, 'display_settings_page' ) );
		
	}

	/**
	 * Add settings page to tabs list on themezee add-on page
	 *
	 * @return void
	*/
	static function add_settings_page($tabs) {
			
		// Add Boilerplate Settings Page to Tabs List
		$tabs['widgets']      = __( 'Widget Bundle', 'themezee-widget-bundle' );
		
		return $tabs;
		
	}
	
	/**
	 * Display settings page
	 *
	 * @return void
	*/
	static function display_settings_page() { 
	
		ob_start();
	?>
		
		<div id="tzwb-settings" class="tzwb-settings-wrap">
			
			<h2><?php _e( 'Widget Bundle', 'themezee-widget-bundle' ); ?></h2>
			<?php settings_errors(); ?>
			
			<form class="tzwb-settings-form" method="post" action="options.php">
				<?php
					settings_fields('tzwb_settings');
					do_settings_sections('tzwb_settings');
					submit_button();
				?>
			</form>
			
		</div>
<?php
		echo ob_get_clean();
	}
	
}

// Run Settings Page Class
TZWB_Settings_Page::setup();

endif;