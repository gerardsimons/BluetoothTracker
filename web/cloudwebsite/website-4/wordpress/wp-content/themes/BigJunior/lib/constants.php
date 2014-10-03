<?php
/** @var $theme WP_Theme */
$theme = wp_get_theme();

define('THEME_NAME',	$theme->Name);
define('THEME_NAME_SEO', strtolower(str_replace(" ", "_", THEME_NAME)));
define('THEME_AUTHOR',	$theme->Author);
define('THEME_VERSION',	$theme->Version);
define('OPTIONS_KEY', "theme_". THEME_NAME_SEO ."_options");

/**************************************************
Theme Defaults
**************************************************/

define('DEFAULT_FOOTER_WIDGETS', 3);
define('USE_CUSTOM_PAGINATION', 1);//Theme-check plugin requirement
define('USE_COMMENT_REPLY_SCRIPT', 0);//Theme-check plugin requirement