<?php
/*
Template Name: Full Width Page (Advanced)
*/
get_header();
get_template_part('templates/head');
$fwa     = get_meta('footer-widget-area');
if($fwa == 2)
    $GLOBALS['px_footer_widget_area'] = false;
?>
    <!--Content-->
    <div id="main" class="full-width">
        <?php get_template_part('templates/loop-page'); ?>
    </div>
<?php get_footer(); ?>