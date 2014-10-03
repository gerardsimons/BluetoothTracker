<?php

function px_register_menus() {
	register_nav_menu( 'primary-nav', __( 'Primary Navigation', TEXTDOMAIN ) );
    register_nav_menu( 'mobile-nav', __( 'Mobile Navigation', TEXTDOMAIN ) );
}

add_action( 'init', 'px_register_menus' );

function px_add_search_menu_item($items, $args)
{
	//disable search menu item
	return $items;
	
    if( 'primary-nav' != $args->theme_location )
        return $items;

    ob_start();
    ?>
    <li id="menu-item-search" class="menu-item menu-item-search">
        <a href="#"><span class="icon-search"></span></a>
        <div class="search-template">
            <?php get_search_form(); ?>
        </div>
    </li>
    <?php
    $items .= ob_get_clean();
    return $items;
}

add_filter('wp_nav_menu_items', 'px_add_search_menu_item', 10, 2);