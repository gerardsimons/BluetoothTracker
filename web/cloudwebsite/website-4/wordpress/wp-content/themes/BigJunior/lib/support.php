<?php

function add_image_size_retina($name, $width = 0, $height = 0, $crop = false)
{
    add_image_size($name, $width, $height, $crop);
    add_image_size("$name@2x", $width*2, $height*2, $crop);
}

/*-----------------------------------------------------------------------------------*/
/*	Configure WP2.9+ Thumbnails
/*-----------------------------------------------------------------------------------*/

if ( function_exists( 'add_theme_support' ) ) {
	add_theme_support( 'post-thumbnails' );

	//set_post_thumbnail_size( 480, 300, true );
    add_image_size_retina( 'post-thumbnail', 485, 300, true );
    add_image_size_retina( 'post-single', 770, 400, true );
    //Post slider thumbnail
    add_image_size_retina( 'post-slider-thumb', 230, 230, true);

    //Portfolio thumbnails style 1
    add_image_size_retina('portfolio-thumb2-style1', 583, 342, true);
    add_image_size_retina('portfolio-thumb3-style1', 388, 227, true);
    add_image_size_retina('portfolio-thumb4-style1', 290, 170, true);
    //Portfolio thumbnails style 2
    add_image_size_retina('portfolio-thumb2-style2', 580);
    add_image_size_retina('portfolio-thumb3-style2', 384);
    add_image_size_retina('portfolio-thumb4-style2', 285);
    //Portfolio single
    add_image_size_retina('portfolio-single', 1170, 430, true);//More suited for wide images
    add_image_size_retina('portfolio-single-split', 670, 700, true);
    add_image_size_retina('portfolio-related4', 270, 150, true);

    /* Blog Widget */
    add_image_size_retina('recent-widget', 75, 63, true);
}

/*-----------------------------------------------------------------------------------*/
/*	RSS Feeds
/*-----------------------------------------------------------------------------------*/

add_theme_support( 'automatic-feed-links' );

/*-----------------------------------------------------------------------------------*/
/*	Post Formats
/*-----------------------------------------------------------------------------------*/

add_theme_support( 'post-formats', array( 'quote', 'video', 'audio', 'gallery' ) );