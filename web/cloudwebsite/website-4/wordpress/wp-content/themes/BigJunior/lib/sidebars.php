<?php

if ( !function_exists('register_sidebar') )
    return;

$defaults = array(
    'name' => __('Main Sidebar', TEXTDOMAIN),
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h4 class="widget-title">',
    'after_title' => '</h4><hr class="hr-medium" />',
);

//Main sidebar
register_sidebar(array_merge($defaults, array('id'=> 'main-sidebar')));
//Page sidebar
register_sidebar(array_merge($defaults, array('name'=>__('Page Sidebar', TEXTDOMAIN), 'id' => 'page-sidebar')));

//Footer widgets
$footerWidgets = opt('footer_widgets') == '' ? DEFAULT_FOOTER_WIDGETS : (int)opt('footer_widgets');

for($i=1; $i<=$footerWidgets; $i++)
{
    register_sidebar(array_merge($defaults, array(
        'id'   => "footer-widget-$i",
        'name' => "Footer Widget Area $i",
    )));
}

//Custom Sidebars
$sidebars = px_get_custom_sidebars();

foreach($sidebars as $key => $item)
{
	register_sidebar(array_merge($defaults, array(
		'id'   => $key,
		'name' => $item,
	)));
}
