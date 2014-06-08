<?php
$acc    = opt('style-accent-color');//Accent color
$accRgb = implode(', ', PxColor::HexToRgb($acc));
$pc     = opt('style-font-color');//Primary color
$hc     = opt('style-highlight-color');//Highlight color
$lc     = opt('style-link-color');//Link color
$lhc    = opt('style-link-hover-color');//Link hover color
//Fonts
$bodyFont = opt('font-body');
$navFont  = opt('font-navigation');
$headFont = opt('font-headings');
?>
body { color:<?php echo $pc; ?>; font-family:'<?php echo $bodyFont; ?>', sans-serif; }

/* Anchor */

a{ color:<?php echo $lc; ?>; }
a:hover{ color:<?php echo $lhc; ?>; }

/* Headings */
h1, h2, h3, h4, h5, h6{ font-family:'<?php echo $headFont; ?>', sans-serif; }

/* Navigation */

header .navigation { font-family:'<?php echo $navFont; ?>', sans-serif; }

/* quote */

blockquote{ background-color:<?php echo $acc; ?>; }

/* Text Selection */

::-moz-selection { background: <?php echo $hc; ?>; /* Firefox */ }
::selection { background: <?php echo $hc; ?>; /* Safari */ }

/* Forms */

form input[type="submit"]:hover,
form input[type="submit"]:active{ background-color:<?php echo $acc; ?>; }

.button{ background-color:<?php echo $acc; ?>; }

/* Colors */

.color-accent-background{ background-color:<?php echo $acc; ?>; }

.color-accent-foreground{ color:<?php echo $acc; ?>; }

/* CF7 */

span.wpcf7-not-valid-tip-no-ajax{ color: <?php echo $acc; ?>; }

div.wpcf7-validation-errors{ color: <?php echo $acc; ?>; }

/* Search Form */

.search-form input[type="submit"]{ background-color:<?php echo $acc; ?>; }

/* Header */

/* Navigation Button inside header */

header .navigation-button:hover{ color: <?php echo $acc; ?>; }

header .navigation > ul > li.current-menu-item > .background,
header .navigation > ul > li.current-menu-ancestor > .background{
    background-color: <?php echo $acc; ?>;
}

header .navigation li li:hover{ background-color: <?php echo $acc; ?>; }

/* Widgets */

.widget-area a:hover{ color:<?php echo $acc; ?>; }

/* Search */

.widget-area .search-form input[type="submit"]:hover{ background-color: <?php echo $acc; ?>; }

.tagcloud a:hover{ color:<?php echo $acc; ?>; }

.widget_bj_testimonials .name{ color: <?php echo $acc; ?>; }

footer .social-icons span:hover { color: <?php echo $acc; ?>; }

.footer-widgets .wpcf7 input[type="submit"]:hover{ background-color: <?php echo $acc; ?>; }

/* Shortcodes */

.iconbox.iconbox-circle:hover .icon,
.iconbox.iconbox-hex:hover .icon{ background-color: <?php echo $acc; ?>; }

.iconbox .glyph{ color: <?php echo $acc; ?>; }

.iconbox .more-link a:hover{ color: <?php echo $acc; ?>; }

/* Portfolio */

.portfolio-style1 .item-image-overlay{
    background-color: <?php echo $acc; ?>;
    background-color: <?php echo "rgba($accRgb, .7)"; ?>;
}

.portfolio-style1 .item-wrap:hover .item-meta
{
    border:1px solid <?php echo $acc; ?>;
    background-color: <?php echo $acc; ?>;
}

.portfolio-style2 .item-icon:hover { background-color: <?php echo $acc; ?>; }

.portfolio-list .filter a.current {
    color: <?php echo $acc; ?>;
    border-bottom-color: <?php echo $acc; ?>;
}

.team-member .name a:hover{ color:<?php echo $acc; ?>; }

.team-member .image-overlay-wrap {
    background-color: <?php echo $acc; ?>;
    background-color: <?php echo "rgba($accRgb, .8)"; ?>;
}

.team-member .icons a:hover{ color:<?php echo $acc; ?> }

.progressbar .progress-inner{ background-color: <?php echo $acc; ?>; }

.accordion .header:hover .title,
.toggle .header:hover .title,
.accordion .header:hover .tab-button,
.toggle .header:hover .tab-button{
    color:<?php echo $acc; ?>;
}

.post-slider .format-quote .item-media{ background-color: <?php echo $acc; ?>; }

.post-slider .media-date{ background-color: <?php echo $acc; ?>; }

.horizontal-tab .titles-container .pointer{ background-color: <?php echo $acc; ?>; }

.tabs .head .current,
.tabs .head li:hover{
    color:<?php echo $acc; ?>;
}

.tabs .head .current{ border-bottom-color: <?php echo $acc; ?>; }

/* Blog */

.archive .comments-link,
.blog .comments-link{ background-color:<?php echo $acc; ?>; }


/*==== Style Overrides ====*/

<?php eopt('additional-css'); ?>