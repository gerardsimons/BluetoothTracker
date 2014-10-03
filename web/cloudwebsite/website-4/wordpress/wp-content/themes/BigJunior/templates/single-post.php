<div class="row">
    <div class="span8">
        <?php
        $format = get_post_format();

        if ( false === $format )
            $format = 'standard';

        get_template_part( 'templates/single', "post-$format" );
        ?>
    </div>
    <div class="span3 offset1">
        <div class="sidebar widget-area"><?php dynamic_sidebar(); ?></div>
    </div>
</div>