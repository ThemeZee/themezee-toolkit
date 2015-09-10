<?php
/***
 * Social Icons Widget
 *
 * Display the latest posts from a selected category in a boxed layout. 
 *
 * @package ThemeZee Widget Bundle
 */

class TZWB_Social_Icons_Widget extends WP_Widget {

	/**
	 * Widget Constructor
	 *
	 * @uses WP_Widget::__construct() Create Widget
	 * @return void
	 */
	function __construct() {
		
		parent::__construct(
			'tzwb-social-icons', // ID
			__( 'Social Icons (ThemeZee)', 'themezee-widget-bundle' ), // Name
			array( 'classname' => 'tzwb-social-icons', 'description' => __( 'Displays your Social Icons.', 'themezee-widget-bundle' ) ) // Args
		);
		
		// Delete Widget Cache on certain actions
		add_action( 'wp_update_nav_menu', array( $this, 'delete_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'delete_widget_cache' ) );
		
	}

	
	/**
	 * Set default settings of the widget
	 *
	 * @return array Default widget settings.
	 */
	private function default_settings() {
	
		$defaults = array(
			'title'				=> '',
			'menu'				=> 0,
			'style'				=> 'icons'
		);
		
		return $defaults;
		
	}

	
	/**
	 * Reset widget cache object
	 *
	 * @return void
	 */
	public function delete_widget_cache() {
		
		wp_cache_delete('tzwb_social_icons', 'widget');
		
	}
	
	
	/**
	 * Main Function to display the widget
	 * 
	 * @uses this->render()
	 * 
	 * @param array $args Parameters from widget area created with register_sidebar()
	 * @param array $instance Settings for this widget instance
	 * @return void
	 */
	function widget($args, $instance) {

		// Get Widget Object Cache
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'tzwb_social_icons', 'widget' );
		}
		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		// Display Widget from Cache if exists
		if ( isset( $cache[ $this->id ] ) ) {
			echo $cache[ $this->id ];
			return;
		}
		
		// Start Output Buffering
		ob_start();
		
		// Get Sidebar Arguments
		extract($args);
		
		// Get Widget Settings
		$defaults = $this->default_settings();
		extract( wp_parse_args( $instance, $defaults ) );
		
		// Add Widget Title Filter
		$widget_title = apply_filters('widget_title', $title, $instance, $this->id_base);
		
		// Output
		echo $before_widget;

		// Display Title
		if( !empty( $widget_title ) ) { echo $before_title . $widget_title . $after_title; }; ?>
			
		<div class="tzwb-content tzwb-clearfix">
			
			<?php echo $this->render($instance); ?>
			
		</div>
			
		<?php
		echo $after_widget;
		
		// Set Cache
		if ( ! $this->is_preview() ) {
			$cache[ $this->id ] = ob_get_flush();
			wp_cache_set( 'tzwb_social_icons', $cache, 'widget' );
		} else {
			ob_end_flush();
		}
	
	}
	
	
	/**
	 * Display the social icon menu
	 * 
	 * @see https://codex.wordpress.org/Function_Reference/wp_nav_menu WordPress Codex
	 * @param array $instance Settings for this widget instance
	 * @return void
	 */
	function render($instance) {
		
		// Get Widget Settings
		$defaults = $this->default_settings();
		extract( wp_parse_args( $instance, $defaults ) );
		
		// Check if there is a social_icons menu
		if( isset($menu) and $menu > 0 ) :
		
			// Set Social Menu Arguments
			$menu_args = array(
				'menu' => (int)$menu,
				'container' => false,
				'menu_class' => 'tzwb-social-icons-menu menu',
				'echo' => true,
				'fallback_cb' => '',
				'before' => '',
				'after' => '',
				'link_before' => '<span class="screen-reader-text">',
				'link_after' => '</span>',
				'depth' => 1
			);
			
			// Display Social Icons Menu
			wp_nav_menu( $menu_args );
			
		endif;
		
	}

	
	/**
	 * Update Widget Settings
	 *
	 * @param array $new_instance Form Input for this widget instance
	 * @param array $old_instance Old Settings for this widget instance
	 * @return array $instance New widget settings
	 */
	function update($new_instance, $old_instance) {

		$instance = $old_instance;
		$instance['title'] = esc_attr($new_instance['title']);
		$instance['menu'] = (int)$new_instance['menu'];
		$instance['style'] = esc_attr($new_instance['style']);
		
		$this->delete_widget_cache();
		
		return $instance;
	}

	
	/**
	 * Display Widget Settings Form in the Backend
	 *
	 * @param array $instance Settings for this widget instance
	 * @return void
	 */
	function form( $instance ) {
		
		// Get Widget Settings
		$defaults = $this->default_settings();
		extract( wp_parse_args( $instance, $defaults ) );
		?>
		
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'themezee-widget-bundle'); ?>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('menu'); ?>"><?php _e('Select Social Menu:', 'themezee-widget-bundle'); ?></label><br/>
			<select id="<?php echo $this->get_field_id('menu'); ?>" name="<?php echo $this->get_field_name('menu'); ?>">
				<option value="0" <?php selected($menu, 0, false); ?>> </option>
				<?php // Display Menu Select Options
					$nav_menus = wp_get_nav_menus(array('hide_empty' => true));
					
					foreach ( $nav_menus as $nav_menu ) :
						printf('<option value="%s" %s>%s</option>', $nav_menu->term_id, selected($menu, $nav_menu->term_id, false), $nav_menu->name);
					endforeach;
				?>
			</select>
		</p>

		<?php
	}
	
}