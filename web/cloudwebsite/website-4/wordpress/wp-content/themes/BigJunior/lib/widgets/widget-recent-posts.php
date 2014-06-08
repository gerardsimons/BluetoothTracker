<?php

// Widget class
class PixFlow_Recent_Posts_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		THEME_SLUG . '_Recent_Posts', // Base ID
			THEME_SLUG . ' - Recent Posts Widget', // Name
			array( 'description' => __( 'Displays your recent posts', TEXTDOMAIN ) ) // Args
		);
	}
	
	function widget( $args, $instance ) {
		extract( $args );

		// Our variables from the widget settings
		$title      = apply_filters('widget_title', $instance['title'] );
		$postcount  = $instance['count'];

		// Before widget (defined by theme functions file)
		echo $before_widget;

		// Display the widget title if one was input
		if ( $title )
			echo $before_title . $title . $after_title;

        //Exclude quote post formats
		$query = new WP_Query(array(
            'posts_per_page' => $postcount,
            'tax_query' => array(
                array(
                    'taxonomy' => 'post_format',
                    'field'    => 'slug',
                    'terms'    => array( 'post-format-quote' ),
                    'operator' => 'NOT IN',
                )
            )
        ));

		
		if( $query->have_posts()) {
		?>
		<div class="item-list">
			<?php while ($query->have_posts()) { $query->the_post();  ?>
				<div class="item clearfix">
                    <a href="<?php the_permalink(); ?>" class="item-image">
                        <?php if ( function_exists('has_post_thumbnail') && has_post_thumbnail() )
                        the_post_thumbnail('recent-widget');
                        ?>
                    </a>
                    <div class="item-info">
					    <a href="<?php the_permalink(); ?>" class="item-title"><?php the_title(); ?></a>
                        <span class="item-date"><?php the_time(get_option('date_format')); ?></span>
                    </div>
				</div>
			<?php }  ?>
		</div>
		<?php
		}//If have posts
		
		wp_reset_query();
		
		// After widget (defined by theme functions file)
		echo $after_widget;
	}

		
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Strip tags to remove HTML (important for text inputs)
		$instance['title'] = strip_tags( $new_instance['title'] );

        $count = intval($new_instance['count']);
        $count = max(min($count, 10), 1);

        $instance['count'] = $count;

		return $instance;
	}
		 
	function form( $instance ) {

		// Set up some default widget settings
		$defaults = array(
			'title' => 'My Recent Posts',
			'count' => '3'
		);
		
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', TEXTDOMAIN) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>

		<!-- Post Count: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e('Number Of Posts:', TEXTDOMAIN) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" value="<?php echo $instance['count']; ?>" />
		</p>

		<?php
		}
}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "PixFlow_Recent_Posts_Widget" );' ) );