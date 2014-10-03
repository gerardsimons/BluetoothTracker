<?php

/* Progressbar */

$pxScTemplate['progressbar'] = array(
	'shortcode' => '[progressbar {attr}/]',
    'flags'     => 'preview',//Has preview

    'fields' => array(
		'title' => array(
			'type'  => 'text',
			'title' => __('Title of the progress', TEXTDOMAIN),
			'desc'  => __('Add title for your progress bar', TEXTDOMAIN),
            'flags' => 'attribute'//CSV
		),
        'percent' => array(
            'type' => 'text',
            'title' => __('Progress done', TEXTDOMAIN),
            'desc'  => __('Enter how much progress is done in percent', TEXTDOMAIN),
            'flags' => 'attribute'//CSV
        ),
        'color' => array(
            'type' => 'text',
            'title' => __('Color', TEXTDOMAIN),
            'desc'  => __('Color of the progress bar, if empty, defaults to theme accent color (<a href="http://hslpicker.com/" target="_blank">Color Picker</a>)', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
	)
);

/* Portfolio */

$portfolioTerms = get_terms('skills');
$portfolioSlugs = array();

foreach($portfolioTerms as $term)
    $portfolioSlugs[$term->slug] = $term->name;

$pxScTemplate['portfolio'] = array(
    'shortcode' => '[portfolio {attr}/]',
    'flags'     => 'preview',//Has preview

    'fields' => array(
        'columns' => array(
            'type' => 'select',
            'title' => __('Number of columns', TEXTDOMAIN),
            'desc'  => __('Select number of portfolio grid columns', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('2' => 2, '3' => 3, '4' => 4),
            'option-flags' => array('3' => 'default'),
        ),
        'items' => array(
            'type' => 'text',
            'title' => __('Number of items', TEXTDOMAIN),
            'desc'  => __('Number of portfolio items per page', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove',//CSV
        ),
        'skills' => array(
            'type' => 'multiselect',
            'title' => __('Filter skills', TEXTDOMAIN),
            'desc'  => __('Only show items that has specific skills', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove',//CSV
            'options' => $portfolioSlugs,
        ),
        'style' => array(
            'type' => 'select',
            'title' => __('Grid Style', TEXTDOMAIN),
            'desc'  => __('Choose between regular grid and uncropped images grid', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('style1' => 'Regular grid', 'style2' => 'Irregular images grid'),
            'option-flags' => array('style1' => 'default'),
        ),
        'pagination' => array(
            'type' => 'select',
            'title' => __('Pagination', TEXTDOMAIN),
            'desc'  => __('You can show or hide pagination feature here', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('show' => 'Show Pagination', 'hide' => 'Hide Pagination'),
            'option-flags' => array('show' => 'default'),
        ),
    )
);

/* Separator */

$pxScTemplate['separator'] = array(
    'shortcode' => '[separator {attr}/]',
    'flags'     => 'preview',//Has preview

    'fields' => array(
        'size' => array(
            'type' => 'select',
            'title' => __('Separator size', TEXTDOMAIN),
            'desc'  => __('Select size of the separator', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('full' => 'Full Width', 'small' => 'Small', 'small-center' => 'Small Center', 'medium' => 'Medium', 'medium-center' => 'Medium Center'),
            'option-flags' => array('full' => 'default'),
        ),
        'margin' => array(
            'type' => 'select',
            'title' => __('Vertical Spacing', TEXTDOMAIN),
            'desc'  => __('Select size of vertical space', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('default' => 'Default', 'small' => 'Small', 'medium' => 'Medium'),
            'option-flags' => array('default' => 'default'),
        ),
    )
);

/* Separator with title */

$pxScTemplate['title_separator'] = array(
    'shortcode' => '[title_separator {attr}/]',
    'flags'     => 'preview',//Has preview

    'fields' => array(
        'title' => array(
            'type' => 'text',
            'title' => __('Title Text', TEXTDOMAIN),
            'desc'  => __('Enter the title text for your separator', TEXTDOMAIN),
            'flags' => 'attribute'//CSV
        ),
        'style' => array(
            'type' => 'select',
            'title' => __('Separator style', TEXTDOMAIN),
            'desc'  => __('Select separator title position', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('left' => 'Left', 'center' => 'Center'),
            'option-flags' => array('left' => 'default'),
        ),
    )
);

/* Team member */

$pxScTemplate['team_member'] = array(
    'shortcode' => '[team_member {attr}][/team_member]',
    'flags'     => 'preview',//Has preview

    'fields' => array(
        'name' => array(
            'type' => 'text',
            'title' => __('Name', TEXTDOMAIN),
            'desc'  => __('Name of the team member', TEXTDOMAIN),
            'flags' => 'attribute'//CSV
        ),
        'title' => array(
            'type' => 'text',
            'title' => __('Job Title', TEXTDOMAIN),
            'desc'  => __('Team member\'s job title', TEXTDOMAIN),
            'flags' => 'attribute'//CSV
        ),
        'image' => array(
            'type' => 'text',
            'title' => __('Image Address', TEXTDOMAIN),
            'desc'  => __('Optional URL of the person\'s image', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove',//CSV
        ),
        'url' => array(
            'type' => 'text',
            'title' => __('Link', TEXTDOMAIN),
            'desc'  => __('Optional url to another web page', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'target' => array(
            'type' => 'select',
            'title' => __('Link\'s target', TEXTDOMAIN),
            'desc'  => __('Open the link in the same page or a blank browser window', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('_self' => 'Same window', '_blank' => 'New window'),
            'option-flags' => array('_self' => 'default'),
        ),
        'description' => array(
            'type' => 'textarea',
            'title' => __('Description', TEXTDOMAIN),
            'desc'  => __('Small description text about the person', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
        ),
    )
);

/* Team member icon */

$pxScTemplate['team_icon'] = array(
    'shortcode' => '[team_icon {attr}/]',

    'fields' => array(
        'title' => array(
            'type'  => 'text',
            'title' => __('Title', TEXTDOMAIN),
            'desc'  => __('Icon title text', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'url' => array(
            'type' => 'text',
            'title' => __('Link', TEXTDOMAIN),
            'desc'  => __('Optional url to another web page', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'target' => array(
            'type' => 'select',
            'title' => __('Link\'s target', TEXTDOMAIN),
            'desc'  => __('Open the link in the same page or a blank browser window', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('_self' => 'Same window', '_blank' => 'New window'),
            'option-flags' => array('_self' => 'default'),
        ),
        'icon' => array(
            'type'  => 'icon',
            'title' => __('Choose an icon', TEXTDOMAIN),
            'desc'  => __('Select an icon for team member', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
        ),
    )
);

/* Carousel item */

$pxScTemplate['carousel_item'] = array(
    'shortcode' => '[carousel_item {attr}/]',

    'fields' => array(
        'image' => array(
            'type' => 'text',
            'title' => __('Image Address', TEXTDOMAIN),
            'desc'  => __('URL of the image', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
        ),
        'title' => array(
            'type'  => 'text',
            'title' => __('Title', TEXTDOMAIN),
            'desc'  => __('Item title text', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'url' => array(
            'type' => 'text',
            'title' => __('Link', TEXTDOMAIN),
            'desc'  => __('Optional url to another web page', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'target' => array(
            'type' => 'select',
            'title' => __('Link\'s target', TEXTDOMAIN),
            'desc'  => __('Open the link in the same tab or a blank browser tab', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('_self' => 'Open in same window', '_blank' => 'Open in new window'),
            'option-flags' => array('_self' => 'default'),
        ),
    )
);

/* Accordion tab */

$pxScTemplate['accordion_tab'] = array(
    'shortcode' => '[accordion_tab {attr}]{content}[/accordion_tab]',

    'fields' => array(
        'title' => array(
            'type'  => 'text',
            'title' => __('Title', TEXTDOMAIN),
            'desc'  => __('Tab title text', TEXTDOMAIN),
            'flags' => 'attribute'//CSV
        ),
        'keepopen' => array(
            'type' => 'select',
            'title' => __('Keep Open', TEXTDOMAIN),
            'desc'  => __('Keep the tab content open', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('' => 'No, don\'t show content', 'yes' => 'Yes, show contents'),
            'option-flags' => array('' => 'default'),
        ),
        'content' => array(
            'type'  => 'textarea',
            'title' => __('Content', TEXTDOMAIN),
            'desc'  => __('Enter some description for your tab', TEXTDOMAIN),
        ),
    )
);

/* Toggle tab */

$pxScTemplate['toggle_tab'] = $pxScTemplate['accordion_tab'];
$pxScTemplate['toggle_tab']['shortcode'] = '[toggle_tab {attr}]{content}[/toggle_tab]';


/* Post Slider */

$postTerms = get_terms('category');
$postSlugs = array();

foreach($postTerms as $term)
    $postSlugs[$term->slug] = $term->name;

$pxScTemplate['post_slider'] = array(
    'shortcode' => '[post_slider {attr}/]',
    'flags'     => 'preview',//Has preview

    'fields' => array(
        'items' => array(
            'type' => 'text',
            'title' => __('Number of items', TEXTDOMAIN),
            'desc'  => __('Number of posts to show (optional)', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove',//CSV
        ),
        'include_categories' => array(
            'type' => 'multiselect',
            'title' => __('Filter categories', TEXTDOMAIN),
            'desc'  => __('Only show items that are in specific category', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove',//CSV
            'options' => $postSlugs,
        ),
    )
);

/* Portfolio Slider */

$pxScTemplate['portfolio_slider'] = array(
    'shortcode' => '[portfolio_slider {attr}/]',
    'flags'     => 'preview',//Has preview

    'fields' => array(
        'items' => array(
            'type' => 'text',
            'title' => __('Number of items', TEXTDOMAIN),
            'desc'  => __('Number of items to show (optional)', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove',//CSV
        ),
        'include_categories' => array(
            'type' => 'multiselect',
            'title' => __('Filter skills', TEXTDOMAIN),
            'desc'  => __('Only show items that has specific skills', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove',//CSV
            'options' => $portfolioSlugs,
        ),
    )
);

/* Testimonials */

$pxScTemplate['testimonial'] = array(
    'shortcode' => '[testimonial {attr}/]',
    'flags'     => 'preview',//Has preview

    'fields' => array(
        'name' => array(
            'type'  => 'text',
            'title' => __('Name', TEXTDOMAIN),
            'desc'  => __('Enter name of the person', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove',//CSV
        ),
        'image' => array(
            'type' => 'text',
            'title' => __('Image Address', TEXTDOMAIN),
            'desc'  => __('URL of the person\'s image', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove',//CSV
        ),
        'comment' => array(
            'type' => 'textarea',
            'title' => __('Comment', TEXTDOMAIN),
            'desc'  => __('Enter person\'s comment here', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
        ),
        'style' => array(
            'type'  => 'select',
            'title' => __('Style', TEXTDOMAIN),
            'desc'  => __('Choose between testimonial styles', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('style1' => 'Style 1', 'style2' => 'Style 2'),
            'option-flags' => array('style1' => 'default'),
        ),
        'skin' => array(
            'type'  => 'select',
            'title' => __('Skin', TEXTDOMAIN),
            'desc'  => __('Choose between dark and light skins', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('dark' => 'Dark', 'light' => 'Light'),
            'option-flags' => array('dark' => 'default'),
        ),
        'background' => array(
            'type'  => 'select',
            'title' => __('Has Background', TEXTDOMAIN),
            'desc'  => __('Show or hide testimonial\'s background color', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('no' => 'No', 'yes' => 'Yes'),
            'option-flags' => array('no' => 'default'),
        ),
    )
);

/* Iconbox Hex/Circle */

$pxScTemplate['iconbox_shape'] = array(
    'shortcode' => '[iconbox_shape {attr}]{content}[/iconbox_shape]',

    'fields' => array(
        'title' => array(
            'type'  => 'text',
            'title' => __('Title', TEXTDOMAIN),
            'desc'  => __('Enter title text', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'shape' => array(
            'type'  => 'select',
            'title' => __('Background Shape', TEXTDOMAIN),
            'desc'  => __('Select icon\'s background shape', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('hex' => 'Hexagon', 'circle' => 'Circle'),
            'option-flags' => array('hex' => 'default'),
        ),
        'content' => array(
            'type'  => 'textarea',
            'title' => __('Content', TEXTDOMAIN),
            'desc'  => __('Enter some description for your IconBox', TEXTDOMAIN),
        ),
        'icon' => array(
            'type'  => 'icon',
            'title' => __('Choose an icon', TEXTDOMAIN),
            'desc'  => __('Select an icon for the top of the box', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
        ),
        'icon_color' => array(
            'type'  => 'text',
            'title' => __('Icon Color', TEXTDOMAIN),
            'desc'  => __('Enter optional icon color (e.g #CCCCCC or gray, <a href="http://hslpicker.com/" target="_blank">Color Picker</a>)', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'shape_color' => array(
            'type'  => 'text',
            'title' => __('Shape Color', TEXTDOMAIN),
            'desc'  => __('Enter optional background shape color (e.g #CCCCCC or gray, <a href="http://hslpicker.com/" target="_blank">Color Picker</a>)', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'title_color' => array(
            'type'  => 'text',
            'title' => __('Title Color', TEXTDOMAIN),
            'desc'  => __('Enter optional title text color (e.g #CCCCCC or gray, <a href="http://hslpicker.com/" target="_blank">Color Picker</a>)', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'text_color' => array(
            'type'  => 'text',
            'title' => __('Text Color', TEXTDOMAIN),
            'desc'  => __('Enter optional content text color (e.g #CCCCCC or gray, <a href="http://hslpicker.com/" target="_blank">Color Picker</a>)', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
    )
);

/* Iconbox */

$pxScTemplate['iconbox'] = array(
    'shortcode' => '[iconbox {attr}]{content}[/iconbox]',

    'fields' => array(
        'title' => array(
            'type'  => 'text',
            'title' => __('Title', TEXTDOMAIN),
            'desc'  => __('Enter title text', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'icon_position' => array(
            'type'  => 'select',
            'title' => __('Icon Position', TEXTDOMAIN),
            'desc'  => __('Select icon\'s position (left or top of the box)', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('left' => 'Left', 'top' => 'Top'),
            'option-flags' => array('left' => 'default'),
        ),
        'url' => array(
            'type' => 'text',
            'title' => __('Link', TEXTDOMAIN),
            'desc'  => __('Optional url to another web page', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'url_text' => array(
            'type' => 'text',
            'title' => __('Link Text', TEXTDOMAIN),
            'desc'  => __('Enter link\'s text', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'target' => array(
            'type'  => 'select',
            'title' => __('Link\'s target', TEXTDOMAIN),
            'desc'  => __('Open the link in the same tab or a blank browser tab', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('_self' => 'Open in same window', '_blank' => 'Open in new window'),
            'option-flags' => array('_self' => 'default'),
        ),
        'content' => array(
            'type'  => 'textarea',
            'title' => __('Content', TEXTDOMAIN),
            'desc'  => __('Enter some description for your IconBox', TEXTDOMAIN),
        ),
        'icon' => array(
            'type'  => 'icon',
            'title' => __('Choose an icon', TEXTDOMAIN),
            'desc'  => __('Select an icon for the top of the box', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
        ),
        'icon_color' => array(
            'type'  => 'text',
            'title' => __('Icon Color', TEXTDOMAIN),
            'desc'  => __('Enter optional icon color (e.g #CCCCCC or gray, <a href="http://hslpicker.com/" target="_blank">Color Picker</a>)', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'title_color' => array(
            'type'  => 'text',
            'title' => __('Title Color', TEXTDOMAIN),
            'desc'  => __('Enter optional title text color (e.g #CCCCCC or gray, <a href="http://hslpicker.com/" target="_blank">Color Picker</a>)', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'text_color' => array(
            'type'  => 'text',
            'title' => __('Text Color', TEXTDOMAIN),
            'desc'  => __('Enter optional content text color (e.g #CCCCCC or gray, <a href="http://hslpicker.com/" target="_blank">Color Picker</a>)', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
    )
);

/* Parallax */

$pxScTemplate['parallax'] = array(
    'shortcode' => '[parallax {attr}][/parallax]',
    'flags'     => 'preview',//Has preview

    'fields' => array(
        'image' => array(
            'type'  => 'text',
            'title' => __('Background Image', TEXTDOMAIN),
            'desc'  => __('Enter url of container\'s background image', TEXTDOMAIN),
            'flags' => 'attribute'//CSV
        ),
        'speed' => array(
            'type'  => 'text',
            'title' => __('Scroll Speed', TEXTDOMAIN),
            'desc'  => __('Scroll speed multiplier ( eg 0.1 )', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'height' => array(
            'type'  => 'text',
            'title' => __('Height', TEXTDOMAIN),
            'desc'  => __('Enter height of the container in pixels ( eg 200 )', TEXTDOMAIN),
            'flags' => 'attribute'//CSV
        ),
        'x_position' => array(
            'type'   => 'text',
            'title'  => __('Vertical Position', TEXTDOMAIN),
            'desc'   => __('Optional background vertical position in percent ( eg 50% )', TEXTDOMAIN),
            'flags'  => 'attribute,empty-remove'//CSV
        ),
        'title' => array(
            'type'  => 'text',
            'title' => __('Title', TEXTDOMAIN),
            'desc'  => __('Optional title text', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'subtitle' => array(
            'type'  => 'text',
            'title' => __('Subtitle', TEXTDOMAIN),
            'desc'  => __('Optional subtitle text', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'title_animation' => array(
            'type'  => 'select',
            'title' => __('Title Animation', TEXTDOMAIN),
            'desc'  => __('Select title animation', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('from-top' => __('Enter from top', TEXTDOMAIN), 'from-bottom' => __('Enter from bottom', TEXTDOMAIN), 'from-left' => __('Enter from left', TEXTDOMAIN), 'from-right' => __('Enter from right', TEXTDOMAIN),),
            'option-flags' => array('from-top' => 'default'),
        ),
        'title_animation_time' => array(
            'type'  => 'text',
            'title' => __('Title Animation Duration', TEXTDOMAIN),
            'desc'  => __('Optional duration of title animation in seconds ( eg 1.5 , default value is one second )', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
    )
);

/* Sidebar */

$sidebars = array('Main Sidebar' => __('Main Sidebar', TEXTDOMAIN),
    'Page Sidebar' => __('Page Sidebar', TEXTDOMAIN),
);

if(opt('custom_sidebars') != '')
{
    $arr = explode(',', opt('custom_sidebars'));

    foreach($arr as $bar)
        $sidebars[$bar] = str_replace('%666', ',', $bar);
}

$pxScTemplate['sidebar'] = array(
    'shortcode' => '[sidebar {attr}/]',

    'fields' => array(
        'name' => array(
            'type'  => 'select',
            'title' => __('Sidebar', TEXTDOMAIN),
            'desc'  => __('Select a sidebar from the list', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => $sidebars,
            'option-flags' => array('Main Sidebar' => 'default'),
        ),
    )
);

/* Button */

$pxScTemplate['button'] = array(
    'shortcode' => '[button {attr}/]',
    'flags'     => 'preview',//Has preview

    'fields' => array(
        'text' => array(
            'type'  => 'text',
            'title' => __('Text', TEXTDOMAIN),
            'desc'  => __('Button text', TEXTDOMAIN),
            'flags' => 'attribute'//CSV
        ),
        'title' => array(
            'type'  => 'text',
            'title' => __('Title', TEXTDOMAIN),
            'desc'  => __('Button title text', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'url' => array(
            'type' => 'text',
            'title' => __('Link', TEXTDOMAIN),
            'desc'  => __('URL to another web page', TEXTDOMAIN),
            'flags' => 'attribute'//CSV
        ),
        'target' => array(
            'type' => 'select',
            'title' => __('Link\'s target', TEXTDOMAIN),
            'desc'  => __('Open the link in the same page or a blank browser window', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('_self' => 'Same window', '_blank' => 'New window'),
            'option-flags' => array('_self' => 'default'),
        ),
        'size' => array(
            'type' => 'select',
            'title' => __('Size', TEXTDOMAIN),
            'desc'  => __('Choose between three button sizes', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('standard' => 'Standard', 'small' => 'Small', 'large' => 'Large'),
            'option-flags' => array('standard' => 'default'),
        ),
        'style' => array(
            'type' => 'select',
            'title' => __('Style', TEXTDOMAIN),
            'desc'  => __('Choose between button styles', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('style1' => 'Style 1', 'style2' => 'Style 2',),
            'option-flags' => array('style1' => 'default'),
        ),
        'text_color' => array(
            'type'  => 'text',
            'title' => __('Text Color', TEXTDOMAIN),
            'desc'  => __('Select optional button text color (<a href="http://hslpicker.com/" target="_blank">Color Picker</a>)', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'button_color' => array(
            'type'  => 'text',
            'title' => __('Button Color', TEXTDOMAIN),
            'desc'  => __('Select optional button color (<a href="http://hslpicker.com/" target="_blank">Color Picker</a>)', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
    )
);

/* LayerSlider */

$pxScTemplate['layerslider'] = array(
    'shortcode' => '[layerslider {attr}/]',

    'fields' => array(
        'id' => array(
            'type'  => 'select',
            'title' => __('Slider', TEXTDOMAIN),
            'desc'  => __('Select a slider that is created in LayerSlider WP panel', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => GetLayerSliderSlides(),
            'option-flags' => array('no-slider' => 'default'),
        ),
    )
);

/* Contact Form 7 */

$pxScTemplate['cf7'] = array(
    'shortcode' => '[contact-form-7 {attr}/]',

    'fields' => array(
        'id' => array(
            'type'  => 'select',
            'title' => __('Form', TEXTDOMAIN),
            'desc'  => __('Select a form that is created in "Contact" panel', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => GetContactForm7Forms(),
            'option-flags' => array('no-form' => 'default'),
        ),
    )
);


/* Google Map */

$gmapZoom = array();

for($i=1; $i<=19;$i++)
    $gmapZoom[$i] = $i;

$pxScTemplate['gmap'] = array(
    'shortcode' => '[gmap {attr}][/gmap]',

    'fields' => array(
        'address' => array(
            'type'  => 'text',
            'title' => __('Address', TEXTDOMAIN),
            'desc'  => __('Specify an address on the map or use latitude and longitude fields below', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'lat' => array(
            'type'  => 'text',
            'title' => __('Latitude', TEXTDOMAIN),
            'desc'  => __('Enter Latitude here, if you enter this value you must enter longitude as well', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'lng' => array(
            'type'  => 'text',
            'title' => __('Longitude', TEXTDOMAIN),
            'desc'  => __('Enter Longitude here, if you enter this value you must enter latitude as well', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'zoom' => array(
            'type'  => 'select',
            'title' => __('Zoom', TEXTDOMAIN),
            'desc'  => __('Select a zoom level', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => $gmapZoom,
        ),
        'controls' => array(
            'type'  => 'select',
            'title' => __('Map Controls', TEXTDOMAIN),
            'desc'  => __('Should map controls be visible or hidden', TEXTDOMAIN),
            'flags' => 'attribute',//CSV
            'options' => array('show'=>'Visible', 'hidden'=>'Hidden'),
            'option-flags' => array('show' => 'default'),
        ),
        'height' => array(
            'type'  => 'text',
            'title' => __('Map Height', TEXTDOMAIN),
            'desc'  => __('Optional map height in pixels. Default value is 300', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
    )
);

/* Google Map Marker */

$pxScTemplate['gmap_marker'] = array(
    'shortcode' => '[gmap_marker {attr}/]',

    'fields' => array(
        'address' => array(
            'type'  => 'text',
            'title' => __('Address', TEXTDOMAIN),
            'desc'  => __('Specify an address on the map or use latitude and longitude fields below', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'lat' => array(
            'type'  => 'text',
            'title' => __('Latitude', TEXTDOMAIN),
            'desc'  => __('Enter Latitude here, if you enter this value you must enter longitude as well', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'lng' => array(
            'type'  => 'text',
            'title' => __('Longitude', TEXTDOMAIN),
            'desc'  => __('Enter Longitude here, if you enter this value you must enter latitude as well', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
        'icon' => array(
            'type'  => 'text',
            'title' => __('Icon', TEXTDOMAIN),
            'desc'  => __('Optional marker icon url', TEXTDOMAIN),
            'flags' => 'attribute,empty-remove'//CSV
        ),
    )
);
