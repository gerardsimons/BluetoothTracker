<?php

// Widget class
class PixFlow_Progress_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		THEME_SLUG . '_Progress', // Base ID
			THEME_SLUG . ' - Progress Widget', // Name
			array( 'description' => __( 'Displays 5 progress bars with a title', TEXTDOMAIN ) ) // Args
		);
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
        <div class="progress-list">
        <?php
        for($i=1; $i<=5; $i++)
        {
            $id = "title$i"; $progId = "progress$i";

            if(!strlen($instance[$id]))
                continue;

            ?>
            <div class="progressbar">
                <h4 class="title"><?php echo $instance[$id]; ?></h4>
                <div class="progress"><div class="progress-inner" style="width:<?php echo $instance[$progId]; ?>%"></div></div>
            </div>
            <?php
        }
        ?>
        </div>
        <?php
		// After widget (defined by theme functions file)
		echo $after_widget;
	}

		
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Strip tags to remove HTML (important for text inputs)
		$instance['title'] = strip_tags( $new_instance['title'] );

        for($i=1; $i<=5; $i++)
        {
            $id = "title$i"; $strId = "progress$i";

            $instance[$id] = trim(strip_tags( $new_instance[$id] ));
            $instance[$strId] = $new_instance[$strId];
        }

		return $instance;
	}
		 
	function form( $instance ) {

		// Set up some default widget settings
		$defaults = array(
			'title' => 'Skills',
		);

        for($i=1; $i<=5; $i++)
        {
            $defaults["title$i"] = '';
            $defaults["progress$i"] = '';
        }

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

        <!-- Widget Title: Text Input -->
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', TEXTDOMAIN) ?></label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
        </p>

        <?php for($i=1; $i<=5; $i++){ ?>
        <!-- Title: Text Input -->
		<p>
            <?php $id="title$i"; ?>
			<label for="<?php echo $this->get_field_id( $id ); ?>"><?php printf(__('Progress %d Title:', TEXTDOMAIN), $i); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( $id ); ?>" name="<?php echo $this->get_field_name( $id ); ?>" value="<?php echo $instance[$id]; ?>" />
		</p>

		<!-- Progress: Text Input -->
		<p>
            <?php $id="progress$i"; ?>
			<label for="<?php echo $this->get_field_id( $id ); ?>"><?php printf(__('Progress %d:', TEXTDOMAIN), $i); ?></label>
            <select id="<?php echo $this->get_field_id( $id ); ?>" name="<?php echo $this->get_field_name( $id ); ?>" class="widefat">
                <?php for($j=0; $j<=100; $j+=10){ ?>
                    <option <?php selected($instance[$id], $j);?> value="<?php echo $j ?>"><?php echo $j ?>%</option>
                <?php } ?>
            </select>
		</p>

        <?php }//end for(...) ?>

		<?php
		}
}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "PixFlow_Progress_Widget" );' ) );