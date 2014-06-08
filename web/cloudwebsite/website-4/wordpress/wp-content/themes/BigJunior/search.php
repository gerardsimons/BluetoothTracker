<?php
/**
 * Search template
 */

get_header();
//Page title
px_title_bar();

$pageHeading = have_posts() ? sprintf(__("Results for '%s'", TEXTDOMAIN), $s) : __('No Results Found', TEXTDOMAIN);
?>
    <!--Content-->
    <div id="main" class="container container-vspace">
        <div class="row">
            <div class="span8">
                <h2><?php echo $pageHeading; ?></h2>
                <p><?php _e('You can start a new search by using the box below.', TEXTDOMAIN); ?></p>
                <br/>
                <?php get_search_form(); ?>
                <hr/>
                <?php get_template_part( 'templates/loop', 'search' );
                get_pagination();
                ?>
            </div>
            <div class="span3 offset1">
                <div class="sidebar widget-area"><?php dynamic_sidebar(); ?></div>
            </div>
        </div>
    </div>

<?php get_footer(); ?>