<?php

include_once THEME_LIB . '/google-fonts.php';

function admin_get_defaults()
{
    static $values = array();

    if(count($values))
        return $values;

    //Extract key-value pairs from settings
    $settings = admin_get_form_settings();
    $panels   = $settings['panels'];

    foreach($panels as $panel)
    {
        foreach($panel['sections'] as $section)
        {
            foreach($section['fields'] as $fieldKey => $field)
            {
                $values[$fieldKey] = array_value('value', $field);
            }
        }
    }

    return $values;
}

function admin_get_color_option_attr($colors)
{
    $tmp = json_encode($colors);
    $tmp = esc_attr($tmp);
    return "data-colors=\"$tmp\"";
}

function admin_get_form_settings()
{
    static $settings = array();//Cache the settings

    if(count($settings))
        return $settings;

    $generalSettingsPanel = array(
        'title' => __('Appearance', TEXTDOMAIN),
        'sections' => array(

            'logo' => array(
                'title'   => __('Custom Logo', TEXTDOMAIN),
                'tooltip' => __('Specify custom logo url or upload new logo here', TEXTDOMAIN),
                'fields'  => array(
                    'logo' => array(
                        'type' => 'upload',
                        'title' => __('Upload Favicon', TEXTDOMAIN),
                        'referer' => 'px-settings-logo'
                    ),
                )
            ),//Logo sec
            'favicon' => array(
                'title'   => __('Custom Favicon', TEXTDOMAIN),
                'tooltip' => __('Specify custom favicon url or upload new icon here', TEXTDOMAIN),
                'fields'  => array(
                    'favicon' => array(
                        'type' => 'upload',
                        'title' => __('Upload Favicon', TEXTDOMAIN),
                        'referer' => 'px-settings-favicon'
                    ),
                )
            ),//Favicon sec
            'responsive-layout' => array(
                'title'   => __('Responsive Layout', TEXTDOMAIN),
                'tooltip' => __('Choose between fixed and responsive layout', TEXTDOMAIN),
                'fields'  => array(
                    'responsive-layout' => array(
                        'type'   => 'switch',
                        'state0' => __('Fixed', TEXTDOMAIN),
                        'state1' => __('Responsive', TEXTDOMAIN),
                        'value'  => 1
                    ),
                )
            ),//responsive-layout sec


        )
    );//$generalSettingsPanel

    $presetColors = array();

    $presetColors['default'] = admin_get_color_option_attr(
        array('style-accent-color'=>'#ff4c2f',
              'style-font-color'=>'#666666',
              'style-highlight-color'=>'#ff4c2f',
              'style-link-color'=>'#ff4c2f',
              'style-link-hover-color'=>'#333333'));

    $presetColors['red'] = admin_get_color_option_attr(
        array('style-accent-color'=>'#eb2130',
            'style-font-color'=>'#666666',
            'style-highlight-color'=>'#eb2130',
            'style-link-color'=>'#eb2130',
            'style-link-hover-color'=>'#333333'));

    $presetColors['orange'] = admin_get_color_option_attr(
        array('style-accent-color'=>'#fe4d2c',
            'style-font-color'=>'#666666',
            'style-highlight-color'=>'#fe4d2c',
            'style-link-color'=>'#fe4d2c',
            'style-link-hover-color'=>'#333333'));

    $presetColors['pink'] = admin_get_color_option_attr(
        array('style-accent-color'=>'#eb2071',
            'style-font-color'=>'#666666',
            'style-highlight-color'=>'#eb2071',
            'style-link-color'=>'#eb2071',
            'style-link-hover-color'=>'#333333'));

    $presetColors['yellow'] = admin_get_color_option_attr(
        array('style-accent-color'=>'#ffdb0d',
            'style-font-color'=>'#666666',
            'style-highlight-color'=>'#ffdb0d',
            'style-link-color'=>'#ffdb0d',
            'style-link-hover-color'=>'#333333'));

    $presetColors['green'] = admin_get_color_option_attr(
        array('style-accent-color'=>'#96d639',
            'style-font-color'=>'#666666',
            'style-highlight-color'=>'#96d639',
            'style-link-color'=>'#96d639',
            'style-link-hover-color'=>'#333333'));

    $presetColors['emerald'] = admin_get_color_option_attr(
        array('style-accent-color'=>'#4dac46',
            'style-font-color'=>'#666666',
            'style-highlight-color'=>'#4dac46',
            'style-link-color'=>'#4dac46',
            'style-link-hover-color'=>'#333333'));

    $presetColors['teal'] = admin_get_color_option_attr(
        array('style-accent-color'=>'#23d692',
            'style-font-color'=>'#666666',
            'style-highlight-color'=>'#23d692',
            'style-link-color'=>'#23d692',
            'style-link-hover-color'=>'#333333'));

    $presetColors['skyBlue'] = admin_get_color_option_attr(
        array('style-accent-color'=>'#45c1e5',
            'style-font-color'=>'#666666',
            'style-highlight-color'=>'#45c1e5',
            'style-link-color'=>'#45c1e5',
            'style-link-hover-color'=>'#333333'));

    $presetColors['blue'] = admin_get_color_option_attr(
        array('style-accent-color'=>'#073b87',
            'style-font-color'=>'#666666',
            'style-highlight-color'=>'#073b87',
            'style-link-color'=>'#073b87',
            'style-link-hover-color'=>'#333333'));

    $presetColors['purple'] = admin_get_color_option_attr(
        array('style-accent-color'=>'#423c6c',
            'style-font-color'=>'#666666',
            'style-highlight-color'=>'#423c6c',
            'style-link-color'=>'#423c6c',
            'style-link-hover-color'=>'#333333'));

    $presetColors['golden'] = admin_get_color_option_attr(
        array('style-accent-color'=>'#dbbe7c',
            'style-font-color'=>'#666666',
            'style-highlight-color'=>'#dbbe7c',
            'style-link-color'=>'#dbbe7c',
            'style-link-hover-color'=>'#333333'));

    $appearancePanel = array(
        'title' => __('Colors', TEXTDOMAIN),
        'sections' => array(

            'theme-style' => array(
                'title'   => __('Preset Color', TEXTDOMAIN),
                'tooltip' => __('Choose a preset theme accent color or choose individual colors from below color pickers', TEXTDOMAIN),
                'fields'  => array(
                    'style-preset-color' => array(
                        'type'   => 'select',
                        'options'=> array('default' => 'Default Theme Colors', 'red' => 'Red', 'orange' => 'Orange', 'pink' => 'Pink', 'yellow' => 'Yellow', 'green' => 'Green', 'emerald' => 'Emerald', 'teal' => 'Teal', 'skyBlue' => 'Sky Blue', 'blue' => 'Blue', 'golden' => 'Golden',),
                        'option-attributes' => $presetColors
                    ),
                )
            ),//theme-style sec
            'accent-color' => array(
                'title'   => __('Accent color', TEXTDOMAIN),
                'tooltip' => __('Accent color for page elements', TEXTDOMAIN),
                'fields'  => array(
                    'style-accent-color' => array(
                        'type'   => 'color',
                        'label'  => __('Choose', TEXTDOMAIN),
                        'value'  => '#ff4c2f'
                    ),
                )
            ),//accent-color sec
            'primary-color' => array(
                'title'   => __('Content color', TEXTDOMAIN),
                'tooltip' => __('Primary font and content elements color', TEXTDOMAIN),
                'fields'  => array(
                    'style-font-color' => array(
                        'type'   => 'color',
                        'label'  => __('Choose', TEXTDOMAIN),
                        'value'  => '#666666'
                    ),
                )
            ),//primary-color sec
            'highlight-color' => array(
                'title'   => __('Highlight color', TEXTDOMAIN),
                'tooltip' => __('Color for highlighted elements', TEXTDOMAIN),
                'fields'  => array(
                    'style-highlight-color' => array(
                        'type'   => 'color',
                        'label'  => __('Choose', TEXTDOMAIN),
                        'value'  => '#ff4c2f'
                    ),
                )
            ),//highlight-color sec
            'link-color' => array(
                'title'   => __('Link Color', TEXTDOMAIN),
                'tooltip' => __('Choose link color and hover color', TEXTDOMAIN),
                'fields'  => array(
                    'style-link-color' => array(
                        'type'   => 'color',
                        'label'  => __('Normal Color', TEXTDOMAIN),
                        'value'  => '#ff4c2f'
                    ),
                    'style-link-hover-color' => array(
                        'type'   => 'color',
                        'label'  => __('Hover Color', TEXTDOMAIN),
                        'value'  => '#333333'
                    ),
                )
            ),//link-color sec

        )
    );//$themeStylePanel

    $gf = new GoogleFonts(path_combine(THEME_LIB, 'googlefonts.json'));
    $fontNames = $gf->GetFontNames();

    $fontsPanel = array(
        'title' => __('Fonts', TEXTDOMAIN),
        'sections' => array(

            'font-body' => array(
                'title'   => __('Body Font', TEXTDOMAIN),
                'tooltip' => __('Select your desired font name for contents', TEXTDOMAIN),
                'fields'  => array(
                    'font-body' => array(
                        'type'   => 'select',
                        'options'=> $fontNames,
                        'value'  => 'Open Sans'
                    ),
                )
            ),
            'font-navigation' => array(
                'title'   => __('Navigation Font', TEXTDOMAIN),
                'tooltip' => __('Select your desired font name for header navigation area', TEXTDOMAIN),
                'fields'  => array(
                    'font-navigation' => array(
                        'type'   => 'select',
                        'options'=> $fontNames,
                        'value'  => 'Open Sans'
                    ),
                )
            ),
            'font-headings' => array(
                'title'   => __('Headings Font', TEXTDOMAIN),
                'tooltip' => __('Select your desired font name for headings and titles', TEXTDOMAIN),
                'fields'  => array(
                    'font-headings' => array(
                        'type'   => 'select',
                        'options'=> $fontNames,
                        'value'  => 'Open Sans'
                    ),
                )
            )
        )

    );//$fontsPanel

    $sidebarPanel = array(
        'title' => __('Sidebars', TEXTDOMAIN),
        'sections' => array(
            'custom-sidebar' => array(
                'title'   => __('Custom Sidebar', TEXTDOMAIN),
                'tooltip' => __('Add custom sidebar that can be used in pages. You could customize sidebar widgets in widgets panel', TEXTDOMAIN),
                'fields'  => array(
                    'custom_sidebars' => array(
                        'type' => 'csv',
                        'placeholder' => __('Enter a sidebar name', TEXTDOMAIN),
                    ),
                )
            ),//custom-sidebar sec
            'sidebar-position' => array(
                'title'   => __('Page Sidebar Position', TEXTDOMAIN),
                'tooltip' => __('Choose default sidebar position for pages that has sidebar, you can override this option in page settings', TEXTDOMAIN),
                'fields'  => array(
                    'sidebar-position' => array(
                        'type' => 'visual-select',
                        'options' => array(/*'none'=>0,*/ 'left-side'=>1, 'right-side'=>2),
                        'class' => 'page-sidebar',
                        'value' => 2,
                    ),
                )
            ),//sidebar-position sec
        )
    );//$sidebarPanel

    $socialSettingsPanel = array(
        'title' => __('Social', TEXTDOMAIN),
        'sections' => array(
            'socials' => array(
                'title'   => __('Social Network URLs', TEXTDOMAIN),
                'tooltip' => __('Enter your social network addresses in respective fields. You can clear fields to hide icons from the website user interface', TEXTDOMAIN),
                'fields'  => array(
                    'social_facebook_url' => array(
                        'type' => 'text',
                        'label' => __('Facebook', TEXTDOMAIN),
                    ),//Facebook
                    'social_twitter_url' => array(
                        'type' => 'text',
                        'label' => __('Twitter', TEXTDOMAIN),
                    ),//twitter
                    'social_vimeo_url' => array(
                        'type' => 'text',
                        'label' => __('Vimeo', TEXTDOMAIN),
                    ),//vimeo
                    'social_youtube_url' => array(
                        'type' => 'text',
                        'label' => __('YouTube', TEXTDOMAIN),
                    ),//youtube
                    'social_googleplus_url' => array(
                        'type' => 'text',
                        'label' => __('Google+', TEXTDOMAIN),
                    ),//Google+
                    'social_dribbble_url' => array(
                        'type' => 'text',
                        'label' => __('Dribbble', TEXTDOMAIN),
                    ),//dribbble
                    'social_tumblr_url' => array(
                        'type' => 'text',
                        'label' => __('Tumblr', TEXTDOMAIN),
                    ),//Tumblr
                    'social_linkedin_url' => array(
                        'type' => 'text',
                        'label' => __('LinkedIn', TEXTDOMAIN),
                    ),//LinkedIn
                    'social_flickr_url' => array(
                        'type' => 'text',
                        'label' => __('Flickr', TEXTDOMAIN),
                    ),//flickr
                    'social_forrst_url' => array(
                        'type' => 'text',
                        'label' => __('Forrst', TEXTDOMAIN),
                    ),//forrst
                    'social_github_url' => array(
                        'type' => 'text',
                        'label' => __('GitHub', TEXTDOMAIN),
                    ),//GitHub
                    'social_lastfm_url' => array(
                        'type' => 'text',
                        'label' => __('Last.fm', TEXTDOMAIN),
                    ),//Last.fm
                    'social_paypal_url' => array(
                        'type' => 'text',
                        'label' => __('PayPal', TEXTDOMAIN),
                    ),//Paypal
                    'social_rss_url' => array(
                        'type' => 'text',
                        'label' => __('RSS Feed', TEXTDOMAIN),
                        'value' => get_bloginfo('rss2_url'),
                    ),//rss
                    'social_skype_url' => array(
                        'type' => 'text',
                        'label' => __('Skype', TEXTDOMAIN),
                    ),//skype
                    'social_wordpress_url' => array(
                        'type' => 'text',
                        'label' => __('WordPress', TEXTDOMAIN),
                    ),//wordpress
                    'social_yahoo_url' => array(
                        'type' => 'text',
                        'label' => __('Yahoo', TEXTDOMAIN),
                    ),//yahoo
                    'social_deviantart_url' => array(
                        'type' => 'text',
                        'label' => __('deviantART', TEXTDOMAIN),
                    ),//DeviantArt
                    'social_steam_url' => array(
                        'type' => 'text',
                        'label' => __('Steam', TEXTDOMAIN),
                    ),//Steam
                    'social_reddit_url' => array(
                        'type' => 'text',
                        'label' => __('reddit', TEXTDOMAIN),
                    ),//reddit
                    'social_stumbleupon_url' => array(
                        'type' => 'text',
                        'label' => __('StumbleUpon', TEXTDOMAIN),
                    ),//StumbleUpon
                    'social_pinterest_url' => array(
                        'type' => 'text',
                        'label' => __('Pinterest', TEXTDOMAIN),
                    ),//Pinterest
                    'social_xing_url' => array(
                        'type' => 'text',
                        'label' => __('XING', TEXTDOMAIN),
                    ),//XING
                    'social_blogger_url' => array(
                        'type' => 'text',
                        'label' => __('Blogger', TEXTDOMAIN),
                    ),//Blogger
                    'social_soundcloud_url' => array(
                        'type' => 'text',
                        'label' => __('SoundCloud', TEXTDOMAIN),
                    ),//SoundCloud
                    'social_delicious_url' => array(
                        'type' => 'text',
                        'label' => __('Delicious', TEXTDOMAIN),
                    ),//delicious
                    'social_foursquare_url' => array(
                        'type' => 'text',
                        'label' => __('Foursquare', TEXTDOMAIN),
                    ),//Foursquare
                )
            ),//Favicon sec
        ),
    );

    $footerSettingsPanel = array(
        'title' => __('Footer Settings', TEXTDOMAIN),
        'sections' => array(
            'widget-areas' => array(
                'title'   => __('Widget Areas', TEXTDOMAIN),
                'tooltip' => __('How many widget areas you like to have in the footer', TEXTDOMAIN),
                'fields'  => array(
                    'footer_widgets' => array(
                        'type' => 'visual-select',
                        'options' => array('zero' => 0, 'one'=>1, 'two'=>2, 'three'=>3, 'four'=>4),
                        'class' => 'footer-widgets',
                        'value' => 3,
                    ),
                )
            ),//widget-areas sec
            'logo-footer' => array(
                'title'   => __('Footer Logo', TEXTDOMAIN),
                'tooltip' => __('Specify custom logo url for footer or upload new logo here', TEXTDOMAIN),
                'fields'  => array(
                    'logo-footer' => array(
                        'type' => 'upload',
                        'title' => __('Upload Footer Logo', TEXTDOMAIN),
                        'referer' => 'px-settings-logo'
                    ),
                )
            ),//Footer logo sec
            'copyright-message' => array(
                'title'   => __('Copyright Message', TEXTDOMAIN),
                'tooltip' => __('Enter footer copyright text. ', TEXTDOMAIN),
                'fields'  => array(
                    'footer-copyright' => array(
                        'type' => 'text',
                        'label' => __('Copyright Text', TEXTDOMAIN),
                        'value' => '&copy; 2013 PixFlow is proudly powered by <a href="http://wordpress.org">WordPress</a> | Built With BigJunior Theme'
                    ),//footer_copyright sec
                )
            ),//widget-areas sec

        ),
    );

    $extraSettingsPanel = array(
        'title' => __('Additional Scripts', TEXTDOMAIN),
        'sections' => array(

            'additional-js' => array(
                'title'   => __('Additional JavaScript', TEXTDOMAIN),
                'tooltip' => __('Enter custom JavaScript code such as Google Analytics code here. Please note that you should not include &lt;script&gt; tags in your scripts.', TEXTDOMAIN),
                'fields'  => array(
                    'additional-js' => array(
                        'type' => 'textarea'
                    ),
                )
            ),//additional-js sec
            'additional-css' => array(
                'title'   => __('Additional CSS', TEXTDOMAIN),
                'tooltip' => __('Enter custom CSS code such as style overrides here. Please note that you should not include &lt;style&gt; tags in your css code.', TEXTDOMAIN),
                'fields'  => array(
                    'additional-css' => array(
                        'type' => 'textarea'
                    ),
                )
            ),//additional-js sec

        ),
    );

    $apiSettingsPanel = array(
        'title' => __('API Keys', TEXTDOMAIN),
        'sections' => array(

            'google-api' => array(
                'title'   => __('Google API Key', TEXTDOMAIN),
                'tooltip' => __('Google API key for services such as Google Maps. Click <a href="https://developers.google.com/maps/documentation/javascript/tutorial#api_key" target="_blank">here</a> for more information on how to obtain Google API key.', TEXTDOMAIN),
                'fields'  => array(
                    'google-api-key' => array(
                        'type' => 'text'
                    ),
                )
            ),//additional-js sec


        ),
    );

    $importExportSettingsPanel = array(
        'title' => __('Import Dummy Data', TEXTDOMAIN),
        'sections' => array(

            'import-dummy-data' => array(
                'title'   => __('Import Posts, Pages and Categories', TEXTDOMAIN),
                'tooltip' => __('If you are new to WordPress or have problems creating posts or pages that look like the theme preview you can import dummy posts and pages here that will definitely help to understand how those tasks are done.', TEXTDOMAIN),
                'fields'  => array(
                    'import-dummy-data' => array(
                        'type'   => 'switch',
                        'state0' => __('Don\'t Import', TEXTDOMAIN),
                        'state1' => __('Import', TEXTDOMAIN),
                        'value'  => 0
                    ),
                )
            ),//import-dummy-data sec

        ),
    );

    $panels = array(
        'general'    => $generalSettingsPanel,
        'appearance' => $appearancePanel,
        'fonts'      => $fontsPanel,
        'social'     => $socialSettingsPanel,
        'footer'     => $footerSettingsPanel,
        'sidebar'    => $sidebarPanel,
        'extra'      => $extraSettingsPanel,
        'api'        => $apiSettingsPanel,
        'data'      => $importExportSettingsPanel,
    );

    $tabs = array(
        'general'    => array( 'text' => __('General Settings', TEXTDOMAIN), 'panel' => 'general'),
        'appearance' => array( 'text' => __('Appearance', TEXTDOMAIN), 'panel' => 'appearance'),
        'fonts'      => array( 'text' => __('Fonts', TEXTDOMAIN), 'panel'  => 'fonts'),
        'footer'     => array( 'text' => __('Footer', TEXTDOMAIN), 'panel'  => 'footer'),
        'sidebar'    => array( 'text' => __('Sidebar', TEXTDOMAIN), 'panel' => 'sidebar'),
        'social'     => array( 'text' => __('Social', TEXTDOMAIN),  'panel' => 'social'),
        'extra'      => array( 'text' => __('Additional Scripts', TEXTDOMAIN),  'panel' => 'extra'),
        'api'        => array( 'text' => __('API Keys', TEXTDOMAIN),  'panel' => 'api'),
        'data'       => array( 'text' => __('Dummy Data', TEXTDOMAIN),  'panel' => 'data'),
    );

    $tabGroups = array(
        'theme-settings' => array( 'text' => __('Theme Settings', TEXTDOMAIN), 'tabs' => array('general', 'appearance', 'fonts', 'sidebar', 'footer', 'social') ),
        'other-settings' => array( 'text' => __('Other Settings', TEXTDOMAIN), 'tabs' => array('extra', 'api', 'data') )
    );

    $settings = array(
        'document-url' => 'http://demo.sacredpixel.com/documents/bigjunior',
        'support-url'  => 'http://support.pixflow.net/',
        'tabs-title'   => __('Theme Options', TEXTDOMAIN),
        'tab-groups'   => $tabGroups,
        'tabs'         => $tabs,
        'panels'       => $panels,
    );

    return $settings;
}