<?php
if (have_posts()) {
    while (have_posts()) { the_post();
        ?>
        <div id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
            <?php the_content(); ?>
        </div>
    <?php
    }//While have_posts
}//If have_posts