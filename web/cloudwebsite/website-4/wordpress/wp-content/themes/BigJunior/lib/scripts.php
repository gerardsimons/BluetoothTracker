<?php
require_once THEME_LIB . '/google-fonts.php';

function px_theme_scripts()
{
	//Register google fonts
    px_theme_fonts();

    //Register Styles
	wp_enqueue_style('style', get_stylesheet_uri(), false, THEME_VERSION);

    if(opt('responsive-layout'))
    {
        wp_enqueue_style('responsive-style', path_combine(THEME_CSS_URI, 'responsive.css'), false, THEME_VERSION);
    }

    //TF requirement (we have our own reply script for gods sake!)
    if(USE_COMMENT_REPLY_SCRIPT && is_singular())
        wp_enqueue_script( "comment-reply" );

    //Add style overrides
    ob_start();
    include(path_combine(THEME_CSS, 'styles-inline.php'));
    wp_add_inline_style('style', ob_get_clean());

    //Include jQuery
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'px_theme_scripts');


function px_register_raw_scripts()
{
    $data  = 'var theme_uri = ' . json_encode(array('css'=> THEME_CSS_URI, 'img'=>THEME_IMAGES_URI)) . ";\n";
    $data .= 'var gkey = "' . opt('google-api-key') . '";';
    $themeScriptName = defined('WP_DEBUG') && WP_DEBUG ? 'theme-dev' : 'theme';

    ?>
    <script type="text/javascript"><?php echo $data; ?></script>
    <script data-main="<?php echo path_combine(THEME_JS_URI , $themeScriptName); ?>" src="<?php echo path_combine(THEME_JS_URI , 'require.js'); ?>" ></script>
    <?php if(opt('additional-js') != ''){ ?>
    <script type="text/javascript"><?php eopt('additional-js'); ?></script>
    <?php
    }
}

add_action('wp_footer', 'px_register_raw_scripts', 100);

function px_add_editor_styles()
{
    add_editor_style();
}

add_action( 'init', 'px_add_editor_styles' );

function px_theme_fonts()
{
    $fontBody     = opt('font-body');
    $fontNav      = opt('font-navigation');
    $fontHeading  = opt('font-headings');

    //Fix for setup problem (shouldn't happen after the update, just for old setups)
    if('' == $fontBody && '' == $fontNav && '' == $fontHeading)
        $fontBody = $fontNav = $fontHeading = '';

    $fonts        = array($fontBody, $fontNav, $fontHeading);
    $fonts        = array_filter($fonts);//remove empty elements

    $fontVariants = array(array(300,400,700), array(300), array(400,700));//Suggested variants if available
    $fontList     = array();
    $fontReq      = 'http://fonts.googleapis.com/css?family=';
    $gf           = new GoogleFonts(path_combine(THEME_LIB, 'googlefonts.json'));

    //Build font list
    foreach($fonts as $key => $font)
    {
        $duplicate = false;
        //Search for duplicate
        foreach($fontList as &$item)
        {
            if($font == $item['font'])
            {
                $duplicate = true;
                $item['variants'] = array_unique(array_merge($item['variants'], $fontVariants[$key]));
                break;
            }
        }

        //Add
        if(!$duplicate)
            $fontList[] = array('font'=>$font, 'variants'=>$fontVariants[$key]);
    }

    $temp=array();
    foreach($fontList as $item)
    {
        $font = $gf->GetFontByName($item['font']);

        if(null==$font)
            continue;

        $variants = array();
        foreach($item['variants'] as $variant)
        {
            //Check if font object has the variant
            if(in_array($variant, $font->variants))
            {
                $variants[] = $variant;
            }
            else if(400 == $variant && in_array('regular', $font->variants))
            {
                $variants[] = $variant;
            }
        }

        $query = preg_replace('/ /', '+', $item['font']);

        if(count($variants))
            $query .= ':' . implode(',', $variants);

        $temp[] = $query;
    }

    if(count($temp))
    {
        $fontReq .= implode('|', $temp);
        wp_enqueue_style('fonts', $fontReq);
    }
}

//JS Flag Trick
function px_add_js_support_script()
{
    ?>
    <script type="text/javascript">
        document.body.className = document.body.className.replace('no-js','js-enabled');
    </script>
    <?php
}

add_Action('px_body_start', 'px_add_js_support_script');