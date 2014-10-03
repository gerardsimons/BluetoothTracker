<nav class="navigation-mobile">
    <span class="icon-close navigation-close"></span>
    <?php
    wp_nav_menu(array(
        'container' =>'',
        'theme_location' => 'mobile-nav',
        'fallback_cb' => false,
        'walker'      => new Custom_Nav_Walker('menu-item-mobile'),
    ));
    ?>
</nav>