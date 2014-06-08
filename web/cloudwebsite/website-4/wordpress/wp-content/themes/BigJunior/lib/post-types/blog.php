<?php

require_once('post-type.php');

class Blog extends PostType
{
    function __construct()
    {
        parent::__construct('post');
    }

    function RegisterScripts()
    {
        wp_register_script('blog', THEME_LIB_URI . '/post-types/js/blog.js', array('jquery'), THEME_VERSION);
        parent::RegisterScripts();
    }

    function EnqueueScripts()
    {
        wp_enqueue_script('hoverIntent');
        wp_enqueue_script('jquery-easing');

        wp_enqueue_style('theme-admin');
        wp_enqueue_script('theme-admin');

        wp_enqueue_script('blog');
    }

    function OnProcessFieldForStore($post_id, $key, $settings)
    {
        //Process gallery field
        if($key != 'gallery')
            return false;

        $images = $_POST["gallery"];

        //Filter results
        $images = array_filter( array_map( 'trim', $images ), 'strlen' );
        //ReIndex
        $images = array_values($images);

        update_post_meta( $post_id, "gallery", $images );

        return true;
    }

    protected function GetOptions()
    {
        $fields = array(
            'video-url' => array(
                'type' => 'text',
                'placeholder' => __('Video URL', TEXTDOMAIN),
            ),//video url
            'audio-url' => array(
                'type' => 'text',
                'placeholder' => __('Audio URL', TEXTDOMAIN),
            ),//video url
            'gallery' => array(
                'type'  => 'upload',
                'title' => __('Gallery Image', TEXTDOMAIN),
                'referer' => 'px-post-gallery-image',
                'meta'  => array('array'=>true),
            ),//gallery image
        );

        //Option sections
        $options = array(
            'video' => array(
                'title'   => __('Post Video', TEXTDOMAIN),
                'tooltip' => __('You can enter video urls hosted in YouTube or Vimeo', TEXTDOMAIN),
                'fields'  => array(
                    'video-url' => $fields['video-url'],
                )
            ),//Video sec
            'audio' => array(
                'title'   => __('Post Audio', TEXTDOMAIN),
                'tooltip' => __('You can enter audio url hosted in SoundCloud', TEXTDOMAIN),
                'fields'  => array(
                    'audio-url' => $fields['audio-url'],
                )
            ),//Audio sec
            'gallery' => array(
                'title'   => __('Post Gallery', TEXTDOMAIN),
                'tooltip' => __('Upload your gallery images here', TEXTDOMAIN),
                'fields'  => array(
                    'gallery' => $fields['gallery'],
                )
            ),//Gallery sec
        );

        return array(
            array(
                'id' => 'blog_meta_box',
                'title' => __('Settings', TEXTDOMAIN),
                'context' => 'normal',
                'priority' => 'default',
                'options' => $options,
            )//Meta box
        );
    }
}

new Blog();