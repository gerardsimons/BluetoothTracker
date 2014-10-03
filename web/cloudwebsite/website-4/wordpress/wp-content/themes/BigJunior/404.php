<?php
/**
 * 404 (Page not found) template
 */

get_header();
$title = __("Error 404", TEXTDOMAIN);
//Page title
px_title_bar($title);
?>
    <!--Content-->
    <div id="main" class="container container-vspace">
        <div class="row">
            <div class="span8">
                <strong style="font-size:50px;"><?php _e('404 ERROR', TEXTDOMAIN); ?></strong>
		<p style="font-size:21px;"><?php _e('PAGE NOT FOUND', TEXTDOMAIN); ?></p>
		<hr />
                <p style="font-size:15px"><?php _e('Sorry, the page you are looking for is not available. You can use the search box below if you like.', TEXTDOMAIN); ?></p>
                <br/>
                <?php get_search_form(); ?>
            </div>
            <div class="span3 offset1">
                <div class="sidebar widget-area"><?php dynamic_sidebar(); ?></div>
            </div>
        </div>
    </div>

<?php get_footer(); ?>