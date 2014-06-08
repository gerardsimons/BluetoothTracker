<?php

/*
 * Class Name: Testimonials Widget
 * Description: Widget that shows testimonials or other similar data
 * Version: 1.0
 * Author: Mohsen Heydari
 * Author URI: http://devmash.net
 */

// Widget class
class PixFlow_Testimonials_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		THEME_SLUG . '_Testimonials', // Base ID
			THEME_SLUG . ' - Testimonials Widget', // Name
			array( 'description' => __( 'Testimonials widget', TEXTDOMAIN ), ), // Args
            array('width' => 430 )//Control ops
		);

        add_action( "admin_enqueue_scripts", array(&$this, 'load_admin_scripts')  );
	}

    function load_admin_scripts($hook)
    {
        if('widgets.php' != $hook)
            return;

        wp_enqueue_script('widget-testimonials', path_combine(THEME_LIB_URI, 'widgets','js', 'widget-testimonials.js'), array('jquery'));
        wp_enqueue_style('widget-testimonials', path_combine(THEME_LIB_URI, 'widgets','css', 'widget-testimonials.css'));
    }
	
	function widget( $args, $instance ) {
		extract( $args );

		// Our variables from the widget settings
		$title      = apply_filters('widget_title', $instance['title'] );

		// Before widget (defined by theme functions file)
		echo $before_widget;

		// Display the widget title if one was input
		if ( $title )
			echo $before_title . $title . $after_title;
        ?>
        <div class="testimonials-container">
            <div class="testimonials-controls">
                <a href="#" class="previous"><?php _e('Previous', TEXTDOMAIN); ?></a>
                <span class="separator"></span>
                <a href="#" class="next"><?php _e('Next', TEXTDOMAIN); ?></a>
            </div>
            <ul>
                <?php for($i=0; $i<$instance['count']; $i++){ ?>
                    <li>
                        <h4 class="name"><?php echo $instance["name$i"]; ?></h4>
                        <blockquote><span class="begin"></span><?php echo $instance["text$i"]; ?><span class="end"></span></blockquote>
                    </li>
                <?php
                }?>
            </ul>
        </div>
        <?php
		// After widget (defined by theme functions file)
		echo $after_widget;
	}


	function update( $new_instance, $old_instance ) {
		// Strip tags to remove HTML (important for text inputs)
		$instance['title'] = strip_tags( $new_instance['title'] );
        $instance['count'] = $new_instance['count'];

        for($i=0; $i<$instance['count']; $i++)
        {
            $nameKey       = "name$i";
            $textKey       = "text$i";
            $instance[$nameKey] = strip_tags($new_instance[$nameKey]);
            $instance[$textKey] = strip_tags($new_instance[$textKey]);
        }

		return $instance;
	}

	function form( $instance ) {

		// Set up some default widget settings
        $defaults = array(
			'title' => 'Testimonials',
            'count' => 1,
            "name0" => '',
            "text0" => '',
		);

		$instance = wp_parse_args( (array)$instance, $defaults ); ?>

        <!-- Widget Title: Text Input -->
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', TEXTDOMAIN); ?></label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
        </p>
        <?php for($i=0; $i<$instance['count']; $i++){
            $nameKey       = "name$i";
            $textKey       = "text$i";
            $nameFieldId   = $this->get_field_id( $nameKey );
            $nameFieldName = $this->get_field_name( $nameKey );
            $textFieldId   = $this->get_field_id( $textKey );
            $textFieldName = $this->get_field_name( $textKey );
        ?>

        <div class="testimonial-group" data-names="<?php echo $this->get_field_name( 'namereplace' ); ?>,<?php echo $this->get_field_name( 'textreplace' ); ?>">
            <div class="testimonial-controls">
                <a href="#" class="testimonial-remove-btn"><?php _e('Remove', TEXTDOMAIN); ?></a>
            </div>
            <p>
                <label for="<?php echo $nameFieldId; ?>"><?php _e('Name:', TEXTDOMAIN) ?></label>
                <input type="text" class="widefat" id="<?php echo $nameFieldId; ?>" name="<?php echo $nameFieldName; ?>" value="<?php echo $instance[$nameKey]; ?>" />
            </p>
            <label for="<?php echo $textFieldId; ?>"><?php _e('Text:', TEXTDOMAIN) ?></label>
            <textarea id="<?php echo $textFieldId; ?>" class="widefat" name="<?php echo $textFieldName; ?>" cols="20" rows="16"><?php echo $instance[$textKey]; ?></textarea>
        </div>

        <?php
        }
        ?>

        <input type="hidden" name="<?php echo $this->get_field_name( 'count' ); ?>" value="<?php echo $instance['count']; ?>" />
        <a href="#" class="testimonial-add"><?php _e('Add Testimonial', TEXTDOMAIN); ?></a>
		<?php
		}
}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "PixFlow_Testimonials_Widget" );' ) );