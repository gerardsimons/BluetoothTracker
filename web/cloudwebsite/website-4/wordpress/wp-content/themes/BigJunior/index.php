<?php
/**
 * Index template (blog/posts)
 */

get_header();
get_template_part( 'templates/head' );
$sidebarPos   = opt('sidebar-position');
$contentClass = 'span8';
$sidebarClass = 'span3';

if(1 == $sidebarPos)
    $contentClass .= ' offset1 float-right';
else
    $sidebarClass .= ' offset1';

if(is_home() && !is_front_page())
{
    $sidebar = get_meta('sidebar');
}
else
{
    $sidebar = 'main-sidebar';
}

?>
    <!--Content-->
    <div id="main" class="container container-vspace">
        <div class="row">
            <div class="<?php echo $contentClass; ?>">
                <?php get_template_part( 'templates/loop', 'blog' );
                if(USE_CUSTOM_PAGINATION)
                    get_pagination();
                else
                    paginate_links();
                ?>
            </div>
            <div class="<?php echo $sidebarClass; ?>"><div class="sidebar widget-area"><?php dynamic_sidebar($sidebar); ?></div></div>
        </div>
    </div>

<?php get_footer(); ?>