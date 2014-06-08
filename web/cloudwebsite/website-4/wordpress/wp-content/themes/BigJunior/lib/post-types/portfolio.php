<?php

require_once('post-type.php');

class Portfolio extends PostType
{

    function __construct()
    {
        parent::__construct('portfolio');
    }

    function CreatePostType()
    {
        $labels = array(
            'name' => __( 'Portfolio', TEXTDOMAIN),
            'singular_name' => __( 'Portfolio', TEXTDOMAIN ),
            'add_new' => __('Add New', TEXTDOMAIN),
            'add_new_item' => __('Add New Portfolio', TEXTDOMAIN),
            'edit_item' => __('Edit Portfolio', TEXTDOMAIN),
            'new_item' => __('New Portfolio', TEXTDOMAIN),
            'view_item' => __('View Portfolio', TEXTDOMAIN),
            'search_items' => __('Search Portfolio', TEXTDOMAIN),
            'not_found' =>  __('No portfolios found', TEXTDOMAIN),
            'not_found_in_trash' => __('No portfolios found in Trash', TEXTDOMAIN),
            'parent_item_colon' => ''
        );

        $args = array(
            'labels' =>  $labels,
            'public' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_icon' => THEME_IMAGES_URI . '/gallery-icon.png',
            'rewrite' => array('slug' => __( 'portfolios', TEXTDOMAIN ), 'with_front' => true),
            'supports' => array('title',
                'editor',
                'thumbnail'
            )
        );

        register_post_type( $this->postType, $args );

        /* Register the corresponding taxonomy */

        register_taxonomy('skills', $this->postType,
            array("hierarchical" => true,
                "label" => __( "Skills", TEXTDOMAIN ),
                "singular_label" => __( "Skill",  TEXTDOMAIN ),
                "rewrite" => false//array('slug' => 'skill-type', 'hierarchical' => true)
            ));
    }

    function RegisterScripts()
    {
        wp_register_script('portfolio', THEME_LIB_URI . '/post-types/js/portfolio.js', array('jquery'), THEME_VERSION);

        parent::RegisterScripts();
    }

    function EnqueueScripts()
    {
        wp_enqueue_script('hoverIntent');
        wp_enqueue_script('jquery-easing');

        wp_enqueue_style('theme-admin');
        wp_enqueue_script('theme-admin');

        wp_enqueue_script('portfolio');
    }

    function OnProcessFieldForStore($post_id, $key, $settings)
    {
        //Process media field
        if($key != 'media')
            return false;

        $selectedOpt = $_POST[$key];


        switch($selectedOpt)
        {
            case "image":
            {
                //delete video meta
                delete_post_meta($post_id, "video-type");
                delete_post_meta($post_id, "video-id");

                $images = $_POST["image"];

                //Filter results
                $images = array_filter( array_map( 'trim', $images ), 'strlen' );
                //ReIndex
                $images = array_values($images);

                update_post_meta( $post_id, "image", $images );

                break;
            }
            case "video":
            {
                //Delete images
                delete_post_meta($post_id, "image");

                $videoType = $_POST["video-type"];
                $videoId   = $_POST["video-id"];

                update_post_meta( $post_id, "video-type", $videoType );
                update_post_meta( $post_id, "video-id", $videoId );

                break;
            }
            default:
            {
                //Delete all
                delete_post_meta($post_id, "video-type");
                delete_post_meta($post_id, "video-id");
                delete_post_meta($post_id, "image");

                break;
            }
        }

        return false;
    }

    protected function GetOptions()
    {
        $fields = array(
            'layout' => array(
                'type' => 'select',
                'options' => array(
                    'half' => __( "Split",  TEXTDOMAIN ),
                    'full' => __( "Full Width",  TEXTDOMAIN ),
                ),
            ),
            'media' => array(
                'type' => 'select',
                'options' => array(
                    'image' => __( "Image",  TEXTDOMAIN ),
                    'video' => __( "Video",  TEXTDOMAIN ),
                    'none'  => __( "None",   TEXTDOMAIN ),
                ),
            ),
            'image' => array(
                'type'  => 'upload',
                'title' => __('Portfolio Image', TEXTDOMAIN),
                'referer' => 'px-portfolio-image',
                'meta'  => array('array'=>true, 'dontsave'=>true),//This will indirectly get saved
            ),
            'video-type' => array(
                'type' => 'select',
                'options' => array(
                    'vimeo' => __( "Vimeo",  TEXTDOMAIN ),
                    'youtube' => __( "YouTube",  TEXTDOMAIN ),
                ),
            ),
            'video-id' => array(
                'type' => 'text',
                'placeholder' => __('Video ID', TEXTDOMAIN),
            ),//video id
        );

        //Option sections
        $options = array(
            'layout' => array(
                'title'   => __('Page Layout', TEXTDOMAIN),
                'tooltip' => __('Specify layout of the portfolio item (Split or Full-Width)', TEXTDOMAIN),
                'fields'  => array(
                    'layout' => $fields['layout']
                )
            ),//layout sec
            'media' => array(
                'title'   => __('Display Media Type', TEXTDOMAIN),
                'tooltip' => __('Specify what kind of media (Image(s), Video or Audio) you would like to be displayed in single portfolio. You can always use shortcodes to add other media types in content', TEXTDOMAIN),
                'fields'  => array(
                    'media' => $fields['media']
                )
            ),//media sec
            'image' => array(
                'title'   => __('Portfolio Images', TEXTDOMAIN),
                'tooltip' => __('Upload your portfolio Image(s) here. If you upload more than one image it will be shown as slider', TEXTDOMAIN),
                'fields'  => array(
                    'image' => $fields['image']
                )
            ),//images sec
            'video' => array(
                'title'   => __('Portfolio Video', TEXTDOMAIN),
                'tooltip' => __('Set Video ID of your portfolio here. Please refer to documentation for learning how to obtain your video ID. You could upload more info in the content area', TEXTDOMAIN),
                'fields'  => array(
                    'video-type' => $fields['video-type'],
                    'video-id' => $fields['video-id'],
                )
            ),//media sec
        );

        return array(
            array(
                'id' => 'portfolio_meta_box',
                'title' => __('Portfolio Options', TEXTDOMAIN),
                'context' => 'normal',
                'priority' => 'default',
                'options' => $options,
            )//Meta box
        );
    }
}

new Portfolio();