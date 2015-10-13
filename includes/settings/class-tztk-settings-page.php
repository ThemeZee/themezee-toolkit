<?php
/***
 * TZTK Settings Page Class
 *
 * Adds the menu link in the backend and displays the settings page.
 *
 * @package ThemeZee Toolkit
 */
 

 /* Use class to avoid namespace collisions */
if ( ! class_exists('TZTK_Settings_Page') ) :

class TZTK_Settings_Page {

	/**
	 * Setup the Settings Page class
	 *
	 * @return void
	*/
	static function setup() {
		
		// Add settings page to addon tabs
		add_filter( 'themezee_addons_settings_tabs', array( __CLASS__, 'add_settings_page' ) );
		
		// Hook settings page to addon page
		add_action( 'themezee_addons_page_toolkit', array( __CLASS__, 'display_settings_page' ) );
		
	}

	/**
	 * Add settings page to tabs list on themezee add-on page
	 *
	 * @return void
	*/
	static function add_settings_page($tabs) {
			
		// Add Boilerplate Settings Page to Tabs List
		$tabs['toolkit']      = __( 'Toolkit', 'themezee-toolkit' );
		
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
		
		<div id="tztk-settings" class="tztk-settings-wrap">
			
			<h2><?php _e( 'Toolkit', 'themezee-toolkit' ); ?></h2><br/>
			<?php settings_errors(); ?>
			
			<form class="tztk-settings-form" method="post" action="options.php">
				<?php
					settings_fields('tztk_settings');
					do_settings_sections('tztk_settings');
					submit_button();
				?>
			</form>
			
		</div>
<?php
		echo ob_get_clean();
	}
	
}

// Run Settings Page Class
TZTK_Settings_Page::setup();

endif;