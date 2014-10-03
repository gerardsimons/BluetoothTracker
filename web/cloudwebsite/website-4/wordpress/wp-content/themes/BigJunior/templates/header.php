<?php
//Default header template
?>

<header class="header-default">

    <div class="container clearfix">
        <?php
        $logo = opt('logo') == "" ? path_combine(THEME_IMAGES_URI, "placeholders/logo.png") : opt('logo');
        ?>

        <div class="logo">
            <a href="<?php echo home_url(); ?>">
                <img src="<?php echo $logo; ?>" alt="Logo" />
            </a>
        </div>

        <nav class="navigation hidden-tablet hidden-phone">
            <?php
            wp_nav_menu(array(
                'container' =>'',
                'menu_class' => 'clearfix',
                'before'     => '<div class="background"></div>',
                'theme_location' => 'primary-nav',
                'walker'     => new Custom_Nav_Walker(),
                'fallback_cb' => false
            ));
            ?>
        </nav>

        <a class="navigation-button hidden-desktop" href="#">
            <span class="icon-paragraph-justify-2"></span>
        </a>

    </div>

</header>