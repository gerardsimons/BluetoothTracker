<?php get_template_part( 'templates/loop', "blog-meta" ); ?>
<div class="post-content">

    <?php
    //Post thumbnail
    if ( function_exists('has_post_thumbnail') && has_post_thumbnail() ) { ?>
    <div class="post-media">
        <a class="post-image" title="<?php echo esc_attr(get_the_title()); ?>" href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
    </div>
    <?php
    }
    the_content(__('Keep Reading &rarr;', TEXTDOMAIN));
    ?>
</div>