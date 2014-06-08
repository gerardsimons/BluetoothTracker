<?php
/**
 * Archive template
 */

get_header();

//Page title
px_title_bar();
?>
    <!--Content-->
    <div id="main" class="container container-vspace">
        <div class="row">
            <div class="span8">
                <?php get_template_part( 'templates/loop', 'blog' );
                get_pagination();
                ?>
            </div>
            <div class="span3 offset1">
                <div class="sidebar widget-area"><?php dynamic_sidebar(); ?></div>
            </div>
        </div>
    </div>

<?php get_footer(); ?>