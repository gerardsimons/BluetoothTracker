<div class="row">
    <div class="span8">
        <?php
        get_template_part( 'templates/single', "post-standard" );
        ?>
    </div>
    <div class="span3 offset1">
        <div class="sidebar widget-area"><?php dynamic_sidebar(); ?></div>
    </div>
</div>