<?php
$slider  = '';
$title   = __('All Posts', TEXTDOMAIN);
$mSlider = get_meta('slider');

//Layerslider
if( 'no-slider' != $mSlider )
{
    $slider = $mSlider;
}

//Page title
px_title_bar($title);

//Slider
if('' != $slider && function_exists('layerslider'))
{
    layerslider($slider);
}