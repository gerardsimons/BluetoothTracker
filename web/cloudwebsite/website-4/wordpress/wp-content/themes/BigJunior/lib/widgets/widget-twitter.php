<?php

/*
 * Class Name: Twitter Widget
 * Description: Widget that renders recent user's twitter timeline
 * Version: 1.0
 * Author: Mohsen Heydari
 * Author URI: http://devmash.net
 */


require_once(THEME_LIB . '/twitter-timeline.php');
require_once(THEME_LIB . '/filecache.php');

// Widget class
class PixFlow_Twitter_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		THEME_SLUG . '_Twitter', // Base ID
			THEME_SLUG . ' - Twitter Widget', // Name
			array( 'description' => __( 'Displays your recent tweets', TEXTDOMAIN ) ) // Args
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
		<ul class="twitter-recent-list">
        <?php
        $scrName = $instance['screen_name'];
        $twitter = new Twitter_Timeline();
        $cache   = new FileCache(array('directory'=> path_combine(THEME_CACHE , 'twitter'), 'cache_time'=>60));

        if( ($timeline = $cache->GetCache($scrName)) != false)
        {
            echo $timeline;
        }
        else
        {
            $timeline = $twitter->TheTimeline(array(
                'consumer_key'    => $instance['consumer_key'],
                'consumer_secret' => $instance['consumer_secret'],
                'user_token'      => $instance['user_token'],
                'user_secret'     => $instance['user_secret'],
                'count'           => $instance['postcount'],
                'echo'            => false
            ));

            $cache->Save($scrName, $timeline);
            echo $timeline;
            // Display Recent Tweets
        }
		?>
		</ul>
		<hr class="hr-twitter hr-small" />
		<a href="http://twitter.com/<?php echo $scrName; ?>" class="join"><?php echo $instance['followText']; ?></a>
		<?php

		// After widget (defined by theme functions file)
		echo $after_widget;
	}

		
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = $new_instance['title'];

		$instance['screen_name'] = trim($new_instance['screen_name']);
        $instance['consumer_key']    = trim($new_instance['consumer_key']);
        $instance['consumer_secret'] = trim($new_instance['consumer_secret']);
        $instance['user_token']  = trim($new_instance['user_token']);
        $instance['user_secret'] = trim($new_instance['user_secret']);

        $count = intval($new_instance['postcount']);
        $count = max(min($count, 200), 1);

		$instance['postcount']   = $count;
		$instance['followText']  = $new_instance['followText'];


		return $instance;
	}
		 
	function form( $instance ) {

		// Set up some default widget settings
		$defaults = array(
			'title' => 'Recent Tweets',
			'screen_name' => 'pixflow',
            'consumer_key'    => '',
            'consumer_secret' => '',
            'user_token'      => '',
            'user_secret'     => '',
			'postcount'       => '2',
			'followText'      => 'Follow Us'
		);
		
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', TEXTDOMAIN) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>

		<!-- Twitter ID: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'screen_name' ); ?>"><?php _e('Twitter Screen Name:', TEXTDOMAIN) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'screen_name' ); ?>" name="<?php echo $this->get_field_name( 'screen_name' ); ?>" value="<?php echo $instance['screen_name']; ?>" />
		</p>

        <!-- Twitter consumer key: Text Input -->
        <p>
            <label for="<?php echo $this->get_field_id( 'consumer_key' ); ?>"><?php _e('Consumer Key:', TEXTDOMAIN) ?></label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'consumer_key' ); ?>" name="<?php echo $this->get_field_name( 'consumer_key' ); ?>" value="<?php echo $instance['consumer_key']; ?>" />
        </p>

        <!-- Twitter consumer secret: Text Input -->
        <p>
            <label for="<?php echo $this->get_field_id( 'consumer_secret' ); ?>"><?php _e('Consumer Secret:', TEXTDOMAIN) ?></label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'consumer_secret' ); ?>" name="<?php echo $this->get_field_name( 'consumer_secret' ); ?>" value="<?php echo $instance['consumer_secret']; ?>" />
        </p>

        <!-- Twitter user token: Text Input -->
        <p>
            <label for="<?php echo $this->get_field_id( 'user_token' ); ?>"><?php _e('User Token:', TEXTDOMAIN) ?></label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'user_token' ); ?>" name="<?php echo $this->get_field_name( 'user_token' ); ?>" value="<?php echo $instance['user_token']; ?>" />
        </p>

        <!-- Twitter user secret: Text Input -->
        <p>
            <label for="<?php echo $this->get_field_id( 'user_secret' ); ?>"><?php _e('User Secret:', TEXTDOMAIN) ?></label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'user_secret' ); ?>" name="<?php echo $this->get_field_name( 'user_secret' ); ?>" value="<?php echo $instance['user_secret']; ?>" />
        </p>

		<!-- Post Count: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'postcount' ); ?>"><?php _e('Number Of Tweets:', TEXTDOMAIN) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'postcount' ); ?>" name="<?php echo $this->get_field_name( 'postcount' ); ?>" value="<?php echo $instance['postcount']; ?>" />
		</p>
		
		<!-- Follow Text: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'followText' ); ?>"><?php _e('Follow Text e.g Follow @pixflow', TEXTDOMAIN) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'followText' ); ?>" name="<?php echo $this->get_field_name( 'followText' ); ?>" value="<?php echo $instance['followText']; ?>" />
		</p>
			
		<?php
		}
}

// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "PixFlow_Twitter_Widget" );' ) );