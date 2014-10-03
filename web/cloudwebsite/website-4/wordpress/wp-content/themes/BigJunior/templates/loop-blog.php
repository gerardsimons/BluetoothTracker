<div class="posts">

    <?php
    while(have_posts()){ the_post();

        global $post;
        $format = get_post_format();

        if ( false === $format )
            $format = 'standard';
        ?>
        <div <?php post_class('clearfix'); ?> >
            <?php get_template_part( 'templates/loop', "blog-$format" ); ?>
        </div>
    <?php
    }
    ?>

</div>