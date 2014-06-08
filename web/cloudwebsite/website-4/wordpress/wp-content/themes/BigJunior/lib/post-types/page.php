<?php

require_once('post-type.php');

class Page extends PostType
{
    function __construct()
    {
        parent::__construct('page');
    }

    function RegisterScripts()
    {
        wp_register_script('page', THEME_LIB_URI . '/post-types/js/page.js', array('jquery'), THEME_VERSION);
        parent::RegisterScripts();
    }

    function EnqueueScripts()
    {
        wp_enqueue_script('hoverIntent');
        wp_enqueue_script('jquery-easing');

        wp_enqueue_script('nouislider');
        wp_enqueue_style('nouislider');

        wp_enqueue_style('theme-admin');
        wp_enqueue_script('theme-admin');

        wp_enqueue_script('page');
    }

    private function GetSidebars()
    {
        $sidebars = array('no-sidebar' => '' , 'main-sidebar' => __('Default Sidebar', TEXTDOMAIN), 'page-sidebar' => __('Default Page Sidebar', TEXTDOMAIN));
        $sidebars = array_merge($sidebars, px_get_custom_sidebars());

        return $sidebars;
    }

    protected function GetOptions()
    {
        $fields = array(
            'title-bar-switch' => array(
                'type' => 'select',
                'options' => array('1'=>__('Show title bar', TEXTDOMAIN), '0'=>__('Don\'t show title bar', TEXTDOMAIN)),
            ),
            'title-text' => array(
                'type' => 'text',
                'placeholder' => __('Override title text', TEXTDOMAIN),
            ),
            'sidebar' => array(
                'type' => 'select',
                'options' => $this->GetSidebars(),
            ),
            'slider' => array(
                'type' => 'select',
                'options' => GetLayerSliderSlides(),
            ),
            'footer-widget-area' => array(
                'type' => 'select',
                'options' => array('1'=> __('Show', TEXTDOMAIN), '2'=> __('Don\'t Show')),
            ),
        );

        //Option sections
        $options = array(
            'title-bar' => array(
                'title'   => __('Title Bar', TEXTDOMAIN),
                'tooltip' => __('Page title bar (the bar under the page header) settings', TEXTDOMAIN),
                'fields'  => array(
                    'title-bar'  => $fields['title-bar-switch'],
                    'title-text' => $fields['title-text'],
                )
            ),//Title bar sec
            'sidebar' => array(
                'title'   => __('Sidebar', TEXTDOMAIN),
                'tooltip' => __('Page sidebar settings', TEXTDOMAIN),
                'fields'  => array(
                    'sidebar' => $fields['sidebar'],
                )
            ),//Sidebar sec
            'layerslider' => array(
                'title'   => __('LayerSlider ', TEXTDOMAIN),
                'tooltip' => __('Select which slider you would like to show under the page header', TEXTDOMAIN),
                'fields'  => array(
                    'slider' => $fields['slider'],
                )
            ),//slider sec
            'footer-widget-area' => array(
                'title'   => __('Footer Widgets Area', TEXTDOMAIN),
                'tooltip' => __('Show or hide the footer widgets area. This setting overrides admin panel settings', TEXTDOMAIN),
                'fields'  => array(
                    'footer-widget-area' => $fields['footer-widget-area'],
                )
            ),//slider sec
        );

        return array(
            array(
                'id' => 'blog_meta_box',
                'title' => __('Page Settings', TEXTDOMAIN),
                'context' => 'normal',
                'priority' => 'high',
                'options' => $options,
            )//Meta box 0
        );
    }
}

new Page();