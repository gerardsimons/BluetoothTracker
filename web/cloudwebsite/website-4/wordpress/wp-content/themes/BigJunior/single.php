<?php
/**
 * Template for displaying all single posts.
 */

get_header();

get_template_part( 'templates/title' );
?>
    <!--Content-->
    <div id="main" class="container container-vspace">

        <?php while ( have_posts() ) { the_post(); ?>

            <?php get_template_part( 'templates/single', get_post_type() ); ?>

        <?php } // end of the loop. ?>

    </div>

<?php get_footer(); ?>