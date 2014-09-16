<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>" />
    <?php if(opt('responsive-layout')){ ?>
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1" />
	<?php } ?>

	<title><?php bloginfo('name'); wp_title(' - ', true, 'left'); ?></title>
	
	<?php if(opt('favicon') != ""){ ?>
	<link rel="shortcut icon" href="<?php eopt('favicon'); ?>"  />
	<?php } ?>
	
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
	<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php bloginfo('atom_url'); ?>" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	
	<!--[if lt IE 9]><script src="<?php echo path_combine(THEME_JS_URI , 'html5shiv.js') ?>"></script><![endif]-->

	<!-- Theme Hook -->
	<?php
	//súper smerige oplossing: bah bah bah
	ob_start();
    wp_head();
	$head = ob_get_clean();
	echo str_replace("Open Sans", "BrandonGrotesque-Regular", $head);
	?>
	<!-- Custom CSS -->
    
    <script type="text/javascript" src="<?php echo path_combine(THEME_JS_URI, 'jquery.smooth-scroll.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo path_combine(THEME_JS_URI, 'jquery.smoothscrolling.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo path_combine(THEME_JS_URI, 'jquery.longpagemenu.js'); ?>"></script>
    <link rel="stylesheet" href="<?php echo path_combine(THEME_CSS_URI, 'jquery.longpagemenu.css'); ?>">
</head>
<body <?php body_class('no-js'); ?>>
<?php
do_action('px_body_start');
//Because it pushes the entire content to a side, it should be placed outside of layout element
get_template_part( 'templates/navigation-mobile' ); ?>
<div class="layout">
    <div class="wrap">
    <!--Header-->
	<?php
    do_action('px_before_header');
    get_template_part( 'templates/header' );
    ?>
	<!--End Header-->