<?php 

require_once('admin-base.php');

//Extended admin class
class Admin extends ThemeAdmin
{
	function Save_Options()
	{
		//Check for import dummy data option
		if( array_key_exists('import-dummy-data', $_POST) &&
		    $_POST['import-dummy-data'] == '1')
		{
			//Don't save anything just Import data
			$this->ImportDummyData();
			
			echo 'OK';
			die();
		}
		
		parent::Save_Options();
	}
	
	function ImportDummyData()
	{
        if(!class_exists( 'WP_Import' ))
        {
            //Try to use custom version of the plugin
            require_once THEME_INCLUDES . '/wordpress-importer/wordpress-importer.php';
        }

		$wp_import = new WP_Import();
		//$wp_import->fetch_attachments = true;
		ob_start();
		$wp_import->import(THEME_ADMIN.'/bigjuniorwordpresstheme.wordpress.2013-10-19.xml');
		ob_end_clean();//Prevents sending output to client
	}
	
	function Enqueue_Scripts()
	{
		wp_enqueue_script('jquery');  
		wp_enqueue_script('thickbox');  
		wp_enqueue_style('thickbox');  
		wp_enqueue_script('media-upload');
		wp_enqueue_script('hoverIntent');
		wp_enqueue_script('jquery-easing');
		wp_enqueue_style('nouislider');
		wp_enqueue_script('nouislider');
		wp_enqueue_style('colorpicker0');
		wp_enqueue_script('colorpicker0');
		wp_enqueue_style('chosen');
		wp_enqueue_script('chosen');
		wp_enqueue_style('theme-admin-css');
		wp_enqueue_script('theme-admin-script');

        wp_enqueue_script('theme-admin-options', THEME_ADMIN_URI . '/scripts/options-panel.js');
	}
}

new Admin();