<?php

get_header();
get_template_part('templates/head');
//Get the sidebar option
$sidebar = get_meta('sidebar');
$sidebarPos = opt('sidebar-position');

?>
<!--Content-->
<div id="main" class="container container-vspace">

    <?php
        if($sidebar == 'no-sidebar' )
            get_template_part('templates/loop-page');
        else{
            $contentClass = 'span8';
            $sidebarClass = 'span3';

            if(1 == $sidebarPos)
                $contentClass .= ' offset1 float-right';
            else
                $sidebarClass .= ' offset1';
    ?>
        <div class="row">
            <div class="<?php echo $contentClass; ?>"><?php get_template_part('templates/loop-page'); ?></div>
            <div class="<?php echo $sidebarClass; ?>"><div class="sidebar widget-area"><?php dynamic_sidebar($sidebar); ?></div></div>
        </div>
    <?php } ?>
</div>

<?php get_footer(); ?>