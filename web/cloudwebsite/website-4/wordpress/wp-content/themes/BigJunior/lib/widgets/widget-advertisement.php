<?php

include_once("twitter-timeline.php");

// Widget class
class PixFlow_Advertisement_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		THEME_SLUG . '_Advertisement', // Base ID
			THEME_SLUG . ' - Advertisement Widget', // Name
			array( 'description' => __( 'Displays your image ads', TEXTDOMAIN ) ) // Args
		);
		
		if ('widgets.php' == basename($_SERVER['PHP_SELF'])) {
			add_action( 'admin_print_scripts', array(&$this, 'add_admin_script') );
		}
	}
	
	function add_admin_script(){
		wp_enqueue_script( 'advertisement-widget', THEME_ADMIN_URI . '/scripts/ads-widget.js', array('jquery'));
	}	
	
	function widget( $args, $instance ) {
		extract( $args );

		// Our variables from the widget settings
		$title = apply_filters('widget_title', $instance['title'] );
		$count = $instance['count'];

		
		// Before widget (defined by theme functions file)
		echo $before_widget;

		// Display the widget title if one was input
		if ( $title )
			echo $before_title . $title . $after_title;

		if( $count > 0){
			for($i=1; $i<= $count; $i++){
			
				$image = isset($instance['ad_'.$i.'_image']) ? $instance['ad_'.$i.'_image'] : '';
				$link  = isset($instance['ad_'.$i.'_link']) ? $instance['ad_'.$i.'_link'] : '#';
				
				if(empty($image))
					$image = THEME_IMAGES_URI .'/ads_sample.png';
				
				
				echo '<a href="'.$link.'" rel="nofollow" target="_blank" alt="Advertisment" class="item"><img src="'.$image.'" alt="Advertisement"/></a>';
			}
			echo  '<div class="clearfix"></div>';
		}

		// After widget (defined by theme functions file)
		echo $after_widget;
	}

		
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = intval($new_instance['count']);
		
		if($instance['count'] > 10)
			$instance['count'] = 10;
		else if($instance['count'] < 1)
			$instance['count'] = 1;
		
		for($i=1;$i<=$instance['count'];$i++){
			$instance['ad_'.$i.'_image'] = strip_tags($new_instance['ad_'.$i.'_image']);
			$instance['ad_'.$i.'_link'] = strip_tags($new_instance['ad_'.$i.'_link']);
		}
		
		return $instance;
	}
		 
	function form( $instance ) {

		// Set up some default widget settings
		$defaults = array(
			'title' => 'Ads',
			'count' => '4'
		);
		
		$instance = wp_parse_args( (array) $instance, $defaults ); 
		
		$count = intval($instance['count']);
		
		for($i=1;$i<=10;$i++){
			$ad_image  = 'ad_'.$i.'_image';
			$$ad_image = isset($instance[$ad_image]) ? $instance[$ad_image] : '';
			$ad_link   = 'ad_'.$i.'_link';
			$$ad_link  = isset($instance[$ad_link]) ? $instance[$ad_link] : '';
		}
		
		?>

		
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', TEXTDOMAIN) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>

		<!-- Ad Count: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('How many advertisement to display?', TEXTDOMAIN) ?></label>
			<input id="<?php echo $this->get_field_id('count'); ?>" class="advertisement_count" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo $instance['count']; ?>" size="3" />
		</p>
		
		<p>
			<em><?php _e('Note: Please input FULL URL <br/>(e.g. <code>http://www.example.com</code>)', TEXTDOMAIN); ?></em>
		</p>
		
		<div class="advertisement_wrap">
		<?php for($i=1;$i<=10;$i++)
		{
		$ad_image = 'ad_'.$i.'_image';$ad_link = 'ad_'.$i.'_link'; ?>
			<div class="ad ad_<?php echo $i;?>" <?php if($i>$count){?>style="display:none"<?php }?> style="padding-bottom:30px">
				<p>
					<label for="<?php echo $this->get_field_id( $ad_image ); ?>"><?php printf('#%s Image URL:',$i);?></label>
					<input class="widefat" id="<?php echo $this->get_field_id( $ad_image ); ?>" name="<?php echo $this->get_field_name( $ad_image ); ?>" type="text" value="<?php echo $$ad_image; ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id( $ad_link ); ?>"><?php printf('#%s Link:',$i);?></label>
					<input class="widefat" id="<?php echo $this->get_field_id( $ad_link ); ?>" name="<?php echo $this->get_field_name( $ad_link ); ?>" type="text" value="<?php echo $$ad_link; ?>" />
				</p>
			</div>
		<?php
		}?>
		
			<em><?php _e('Image sizes should be exactly <br />125px * 125px', TEXTDOMAIN) ?></em>
		</div>
		
		<?php
		}
}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "PixFlow_Advertisement_Widget" );' ) );