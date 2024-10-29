<?php
/*
 * Plugin Name: AMP Recent Posts widget
 * Version: 1.3
 * Plugin URI: https://jaaadesign.nl/en/blog/amp-recent-posts-widget/
 * Description: Generates a list of AMP posts in the widget area.
 * Author: Nick van de Veerdonk
 * Author URI: https://jaaadesign.nl/
 */

function wp_amp_recent_posts_register_custom_widgets() {
	 
		
    register_widget( 'WP_Amp_Recent_Posts_Widget' );

}
add_action( 'widgets_init', 'wp_amp_recent_posts_register_custom_widgets' );


/**
 * Widget API: WP_Widget_Recent_Posts class
 *
 * @package WordPress
 * @subpackage Widgets
 * @since 4.4.0
 */

/**
 * Core class used to implement a Recent Posts widget.
 *
 * @since 2.8.0
 *
 * @see WP_Widget
 */
class WP_Amp_Recent_Posts_Widget extends WP_Widget {

	/**
	 * Sets up a new AMP Recent Posts widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'widget_amp_entries',
			'description' => __( 'Generates a list of AMP posts in the widget area.'),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'amp-posts', __( 'AMP Recent Posts widget' ), $widget_ops );
		$this->alt_option_name = 'widget_amp_entries';
	}

	/**
	 * Outputs the content for the current AMP Posts widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current AMP Posts widget instance.
	 */
	public function widget( $args, $instance ) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( '' );
		
//* JD add field		
$append = ( ! empty( $instance['append'] ) ) ? $instance['append'] : __( 'amp' );		

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		
		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5000;
		if ( ! $number )
			$number = 5000;
		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;

		/**
		 * Filter the arguments for the Recent Posts widget.
		 *
		 * @since 3.4.0
		 *
		 * @see WP_Query::get_posts()
		 *
		 * @param array $args An array of arguments used to retrieve the recent posts.
		 */
		$r = new WP_Query( apply_filters( 'widget_posts_args', array(
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => false
		) ) );

		if ($r->have_posts()) :
		?>
		<?php echo $args['before_widget']; ?>
		<?php if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		} ?>
		<ul>
		<?php while ( $r->have_posts() ) : $r->the_post(); ?>
			<li>
				<a href="<?php the_permalink(); ?><?php if ( $append ) {
			echo $append;
		} ?>/"><?php get_the_title() ? the_title() : the_ID(); ?></a>
			<?php if ( $show_date ) : ?>
				<span class="post-date"><br /><?php echo get_the_date(); ?></span>
			<?php endif; ?>
			</li>
		<?php endwhile; ?>
		</ul>
		<?php echo $args['after_widget']; ?>
		<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;
	}

	/**
	 * Handles updating the settings for the current Recent Posts widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		
//* JD add field	
$instance['append'] = sanitize_text_field( $new_instance['append'] );		
		
		$instance['number'] = (int) $new_instance['number'];
		$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		
		return $instance;
	}

	/**
	 * Outputs the settings form for the Recent Posts widget.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5000;
		$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
		
//* JD add field
$append     = isset( $instance['append'] ) ? esc_attr( $instance['append'] ) : '';		
		
?>

<p><label for="<?php echo $this->get_field_id( 'append' ); ?>"><?php _e( 'Append this text to URL to get the AMP version:' ); ?></label>
		<input placeholder="text only, no slashes" class="widefat" id="<?php echo $this->get_field_id( 'append' ); ?>" name="<?php echo $this->get_field_name( 'append' ); ?>" type="text" value="<?php echo $append; ?>" /></p><p style="font-size: 12px;margin:-0.5em 0 2em 0;">Depending on the plugin you use, in case of the AMP plugin by Automattic it's &#8217;amp&#8217;.<br />Ex: www.yoursite.com/somepost/<strong>amp</strong></em>.</p>

		
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( '
 Add widget title' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
					
		
		<p style="margin: 1.5em 0 1em 0;"><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts:' ); ?></label>
		<input class="tiny-text" style="width: 64px;" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="5" /></p>

		<p><label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?' ); ?>&nbsp;&nbsp;  </label><input class="checkbox" type="checkbox"<?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" /></p>
<?php
	}
}
