<div <?php post_class(); ?>>
    <?php get_template_part( 'templates/single', "post-meta" ); ?>
    <div class="post-content">

        <?php //Post thumbnail
        if ( function_exists('has_post_thumbnail') && has_post_thumbnail() ) { ?>
        <div class="post-media">
            <?php the_post_thumbnail('post-single'); ?>
        </div>
        <?php
        }

        the_content();
        wp_link_pages();
        ?>
    </div>
</div>
<?php comments_template('', true); ?>