<?php 

define('MCE_PATH', THEME_LIB . '/shortcodes');
define('MCE_URI', THEME_LIB_URI . '/shortcodes');

class MceWrapper
{
	function __construct()
	{
		$this->Initialize();
	}
	
	function Initialize()
	{
		add_action('admin_init', array( &$this, 'RegisterHead' ));
		add_action('init', array( &$this, 'InitEditor' ));
		//add_action('admin_print_scripts', array( &$this, 'Quicktags' ));
	}
	
	function RegisterHead($hook)
	{
		// css
		wp_enqueue_style( 'px-popup-font', 'http://fonts.googleapis.com/css?family=Ubuntu|Muli', false, '1.0', 'all' );
		wp_enqueue_style( 'px-popup', MCE_URI . '/css/popup.css', false, '1.0', 'all' );
        wp_enqueue_style( 'jquery.pxmodal', MCE_URI . '/css/jquery.pxmodal.css', false, THEME_VERSION );
        wp_enqueue_style( 'chosen', THEME_ADMIN_URI . '/css/chosen.css' );
        wp_enqueue_style( 'icomoon', THEME_CSS_URI . '/icomoon.css' );
        //wp_enqueue_style( 'colorpicker', THEME_ADMIN_URI . '/css/colorpicker.css' );

		// JavaScript
		wp_enqueue_script( 'px-popup', MCE_URI . '/scripts/popup.js', false, THEME_VERSION );
		wp_enqueue_script('colorpicker',THEME_ADMIN_URI.'/scripts/colorpicker.js', array('jquery'));
        wp_enqueue_script('chosen', THEME_ADMIN_URI  .'/scripts/chosen.jquery.min.js', array('jquery'));

        wp_enqueue_script( 'jquery.pxmodal', MCE_URI . '/scripts/jquery.pxmodal.js', false, THEME_VERSION, false );
	}
	
	function InitEditor()
	{
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return;
	
		if ( get_user_option('rich_editing') == 'true' )
		{
			add_filter( 'mce_external_plugins', array( &$this, 'AddPlugins' ) );
			add_filter( 'mce_buttons', array( &$this, 'RegisterButtons' ) );
		}
	}
	
	function AddPlugins( $plugin_array )
	{
		global $wp_version;
		
		$plugin = '';
		
		if(floatval($wp_version) >= 3.9)
			$plugin = '/plugin-mce4.js';
		else
			$plugin = '/plugin.js';
		
		$plugin_array['pxShortcodes'] = MCE_URI . $plugin;
		
		return $plugin_array;
	}
	
	function RegisterButtons( $buttons )
	{
		array_push( $buttons, "|", 'px_button' );
		return $buttons;
	}
	
}

//Run only on 'post' page
if(in_array($GLOBALS['pagenow'], array('post.php', 'post-new.php')))
	new MceWrapper();	// execute