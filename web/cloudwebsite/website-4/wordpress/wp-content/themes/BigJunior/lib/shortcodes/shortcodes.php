<?php
/*-----------------------------------------------------------------------------------

	Theme Shortcodes

-----------------------------------------------------------------------------------*/

require_once(THEME_LIB . '/portfolio-walker.php');

/*-----------------------------------------------------------------------------------*/
/*	Shortcode forms ajax handler
/*-----------------------------------------------------------------------------------*/

function px_sc_popup()
{
    include('forms.php');
    die();
}

add_action('wp_ajax_px_sc_popup', 'px_sc_popup');

/*-----------------------------------------------------------------------------------*/
/*	Shortcode helpers
/*-----------------------------------------------------------------------------------*/

function px_sc_unautop($content, $names = '[\w\d_]+')
{
    if(is_string($names))
        $names = array($names);

    $ret = $content;

    foreach($names as $name)
    {
        // opening tag
        $ret = preg_replace("/(<p>)?(\[{$name}[^\]]*\])(<\/p>)?/", '\\2', $ret);

        // closing tag
        $ret = preg_replace("/(<p>)?(\[\/$name\])(<\/p>)?/", '\\2', $ret);
    }

    return $ret;
}

function px_sc_unautop_container($content)
{
    //No strings except shortcodes allowed at the beginning and end of the container
    $ret = preg_replace("/^[^\[]*|[^\]]*$/", '', $content);
    return $ret;
}

//Cleans any chars between two similar shortcodes
function px_sc_clean_between($content, $sc)
{
    $ret = preg_replace("/(\[\/$sc\])[^\[]*(\[{$sc}[^\]\/]*\])/", '\\1\\2', $content);
    return $ret;
}

//Remove p tags for specified shortcodes
function px_sc_content_filter($content) {

    $ret = px_sc_unautop($content, array('row','span\d{1,2}',
        'container','separator', 'title', 'progressbar', 'accordion_tab',
        'horizontal_tab', 'horizontal_tab_group', 'iconbox', 'iconbox_shape', 'parallax', 'tab', 'team_member',
        'contact-form-7', 'post_slider', 'portfolio_slider', 'testimonial', 'testimonial_group',
        'layerslider', 'portfolio', 'tab_group', 'accordion', 'section_alt'));
    return $ret;
}

add_filter("the_content", "px_sc_content_filter");

/*-----------------------------------------------------------------------------------*/
/*	Section, Container, Row, Column and Offset Shortcodes
/*-----------------------------------------------------------------------------------*/

/* Alternate BG Section */

function px_sc_alt_section($atts, $content = null)
{
    extract(shortcode_atts(array(
        'background_color'   => ''
    ), $atts));

    $class = 'section';
    $style = '';

    if('' == $background_color)
        $class .= ' color-alt-main-background';
    else
        $style = "style=\"background-color:$background_color\"";

    return '<div class="'.$class.'" '.$style.'>' . do_shortcode($content) . '</div>';
}

add_shortcode('section_alt', 'px_sc_alt_section');

/* Container */

function px_sc_container( $atts, $content = null ) {
    extract(shortcode_atts(array(
        'vertical_padding'   => ''
    ), $atts));

    $vertical_padding = '' != $vertical_padding ? 'container-vspace' : '';

    return '<div class="container '.$vertical_padding.'">' . do_shortcode($content) . '</div>';
}

add_shortcode('container', 'px_sc_container');

/* Row */

function px_sc_row( $atts, $content = null ) {
    $GLOBALS['px_sc_spans'] = array();//Set/Reset
    do_shortcode($content);
    $spans = $GLOBALS['px_sc_spans'];

    return '<div class="row">' . implode("\n", $spans) . '</div>';
}

add_shortcode('row', 'px_sc_row');

/* One Twelve Column */

function px_sc_span1( $atts, $content = null ) {
	extract(shortcode_atts(array(
		'offset'   => ''
    ), $atts));

    $GLOBALS['px_sc_spans'][] = "<div class=\"span1 offset$offset\">" . do_shortcode($content) . "</div>";
}

add_shortcode('span1', 'px_sc_span1');

/* Two Twelve Column */

function px_sc_span2( $atts, $content = null ) {
   	extract(shortcode_atts(array(
		'offset'   => ''
    ), $atts));

    $GLOBALS['px_sc_spans'][] = "<div class=\"span2 offset$offset\">" . do_shortcode($content) . "</div>";
}

add_shortcode('span2', 'px_sc_span2');

/* Three Twelve Column */

function px_sc_span3( $atts, $content = null ) {
    extract(shortcode_atts(array(
		'offset'   => ''
    ), $atts));

    $GLOBALS['px_sc_spans'][] = "<div class=\"span3 offset$offset\">" . do_shortcode($content) . "</div>";
}

add_shortcode('span3', 'px_sc_span3');

/* Four Twelve Column */

function px_sc_span4( $atts, $content = null ) {
    extract(shortcode_atts(array(
		'offset'   => ''
    ), $atts));

    $GLOBALS['px_sc_spans'][] = "<div class=\"span4 offset$offset\">" . do_shortcode($content) . "</div>";
}

add_shortcode('span4', 'px_sc_span4');

/* Five Twelve Column */

function px_sc_span5( $atts, $content = null ) {
    extract(shortcode_atts(array(
		'offset'   => ''
    ), $atts));

    $GLOBALS['px_sc_spans'][] = "<div class=\"span5 offset$offset\">" . do_shortcode($content) . "</div>";
}

add_shortcode('span5', 'px_sc_span5');

/* Six Twelve Column */

function px_sc_span6( $atts, $content = null ) {
    extract(shortcode_atts(array(
		'offset'   => ''
    ), $atts));

    $GLOBALS['px_sc_spans'][] = "<div class=\"span6 offset$offset\">" . do_shortcode($content) . "</div>";
}

add_shortcode('span6', 'px_sc_span6');

/* Seven Twelve Column */

function px_sc_span7( $atts, $content = null ) {
    extract(shortcode_atts(array(
		'offset'   => ''
    ), $atts));

    $GLOBALS['px_sc_spans'][] = "<div class=\"span7 offset$offset\">" . do_shortcode($content) . "</div>";
}

add_shortcode('span7', 'px_sc_span7');

/* Eight Twelve Column */

function px_sc_span8( $atts, $content = null ) {
    extract(shortcode_atts(array(
		'offset'   => ''
    ), $atts));

    $GLOBALS['px_sc_spans'][] = "<div class=\"span8 offset$offset\">" . do_shortcode($content) . "</div>";
}

add_shortcode('span8', 'px_sc_span8');

/* Nine Twelve Column */

function px_sc_span9( $atts, $content = null ) {
    extract(shortcode_atts(array(
		'offset'   => ''
    ), $atts));

    $GLOBALS['px_sc_spans'][] = "<div class=\"span9 offset$offset\">" . do_shortcode($content) . "</div>";
}

add_shortcode('span9', 'px_sc_span9');

/* Ten Twelve Column */

function px_sc_span10( $atts, $content = null ) {
    extract(shortcode_atts(array(
		'offset'   => ''
    ), $atts));

    $GLOBALS['px_sc_spans'][] = "<div class=\"span10 offset$offset\">" . do_shortcode($content) . "</div>";
}

add_shortcode('span10', 'px_sc_span10');

/* Eleven Twelve Column */

function px_sc_span11( $atts, $content = null ) {
    extract(shortcode_atts(array(
		'offset'   => ''
    ), $atts));

    $GLOBALS['px_sc_spans'][] = "<div class=\"span11 offset$offset\">" . do_shortcode($content) . "</div>";
}

add_shortcode('span11', 'px_sc_span11');

/* Twelve Twelve Column */

function px_sc_span12( $atts, $content = null ) {
    extract(shortcode_atts(array(
		'offset'   => ''
    ), $atts));

    $GLOBALS['px_sc_spans'][] = "<div class=\"span12 offset$offset\">" . do_shortcode($content) . "</div>";
}

add_shortcode('span12', 'px_sc_span12');

/*-----------------------------------------------------------------------------------*/
/*	Portfolio Listing
/*-----------------------------------------------------------------------------------*/

function px_sc_portfolio( $atts, $content = null ) {
    extract(shortcode_atts(array(
        'columns' => 3,
        'items'  => -1,
        'skills' => '',
        'style'  => 'style1',
        'pagination' => 'show'
    ), $atts));

    //Get the Page ID
    $pid = 0;
    if(is_page())
        $pid = get_the_ID();

    //Check the style
    if('style1' != $style && 'style2' != $style)
        $style = 'style1';

    //Bound
    $columns = min(max($columns, 2), 4);
    $items   = max($items, -1);

    //Convert slugs to IDs
    $catArr  = slugs_to_ids(explode(',', $skills), 'skills');

    //Show category filter either:
    //1) There is no category filter assigned
    //2) Number of categories are more than one
    $catList = '';

    if(count($catArr) == 0 || count($catArr) > 1)
    {
        $listCatsArgs = array('title_li' => '', 'taxonomy' => 'skills', 'walker' => new Portfolio_Walker(), 'echo' => 0, 'include' => implode(',', $catArr));
        //$catSep  = '<li class="separator">/</li>';
        $catList = '<li><a class="current" data-filter="*" href="#">'.__('All', TEXTDOMAIN)."</a></li>";
        $catList .= wp_list_categories($listCatsArgs);
        //$catList = remove_last_occurrence($catList, $catSep);
    }

    //Build post query
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    $queryArgs = array(
        'post_type'      => 'portfolio',
        'posts_per_page' => $items,
        'paged'          => $paged
    );

    //Taxonomy filter
    if(count($catArr))
    {
        $queryArgs['tax_query'] =  array(
            // Note: tax_query expects an array of arrays!
            array(
                'taxonomy' => 'skills',
                'field'    => 'id',
                'terms'    => $catArr
            ));
    }

    $query = new WP_Query($queryArgs);

    ob_start();
?>
    <div class="portfolio-list portfolio-<?php echo $style; ?>">
        <?php if(strlen($catList)){ ?>
        <div class="filter">
            <ul>
                <?php echo $catList; ?>
            </ul>
        </div>
        <?php } ?>
        <div class="isotope <?php echo "col$columns" ?> clearfix">
        <?php while ($query->have_posts()) { $query->the_post();
            $terms = get_the_terms( get_the_ID(), 'skills' );

            if('style1' == $style)
                px_sc_portfolio_style1_item($terms, $columns, $pid);
            else
                px_sc_portfolio_style2_item($terms, $columns, $pid);

         } ?>
        </div>
        <?php
        if('show' == $pagination)
        {
            get_pagination($query);
        }
        ?>
    </div>
<?php
    wp_reset_query();

    return ob_get_clean();
}

add_shortcode('portfolio', 'px_sc_portfolio');

function px_sc_portfolio_style1_item($terms, $columns, $pageID)
{
    if(0 != $pageID)
        $permalink = add_query_arg( 'pnt', $pageID, get_permalink() );
    else
        $permalink = get_permalink();

    ?>

    <div class="item <?php if($terms) { foreach ($terms as $term) { echo "term-$term->term_id "; } } ?>">
        <div class="item-wrap">
            <div class="item-image">
                <?php
                $thumbSize = "portfolio-thumb$columns-style1";

                if ( function_exists('has_post_thumbnail') && has_post_thumbnail() )
                    the_post_thumbnail($thumbSize);
                ?>
                <div class="item-image-overlay"></div>
                <a href="<?php echo $permalink; ?>" class="item-image-link"></a>
            </div>

            <div class="item-meta">
                <h3 class="item-title"><a href="<?php echo $permalink; ?>"><?php the_title(); ?></a></h3>
                <span class="item-category"><?php
                    $termNames = array();
                    if($terms)
                        foreach ($terms as $term)
                            $termNames[] = $term->name;

                    echo implode(', ', $termNames);
                    ?>
                </span>
            </div>
        </div>
    </div>

    <?php
}

function px_sc_portfolio_style2_item($terms, $columns, $pageID){

    if(0 != $pageID)
        $permalink = add_query_arg( 'pnt', $pageID, get_permalink() );
    else
        $permalink = get_permalink();

    ?>
    <div class="item <?php if($terms) { foreach ($terms as $term) { echo "term-$term->term_id "; } } ?>">
        <div class="item-wrap">
            <div class="item-image">
                <?php
                $thumbSize = "portfolio-thumb$columns-style2";
                $thumbId   = get_post_thumbnail_id();
                $atch      = wp_get_attachment_image_src( $thumbId, 'large' );
                $thumbSrc  = $atch != false ? $atch[0] : '';

                if ( function_exists('has_post_thumbnail') && has_post_thumbnail() )
                    the_post_thumbnail($thumbSize);
                ?>
                <div class="item-image-overlay"></div>
                <a href="<?php echo $permalink; ?>" class="item-icon item-view-project-icon" title="<?php _e('More Details', TEXTDOMAIN) ?>"><span class="icon-file-3"></span></a>
                <a href="<?php echo $thumbSrc; ?>" class="item-icon item-view-image-icon" title="<?php the_title() ?>"><span class="icon-search"></span></a>
            </div>

            <div class="item-meta">
                <h3 class="item-title"><a href="<?php echo $permalink; ?>"><?php the_title(); ?></a></h3>
                <hr class="hr-center" />
                <span class="item-category"><?php
                    $termNames = array();
                    if($terms)
                        foreach ($terms as $term)
                            $termNames[] = $term->name;

                    echo implode(', ', $termNames);
                    ?>
                </span>
            </div>
        </div>
    </div>

    <?php
}

/*-----------------------------------------------------------------------------------*/
/*	Separators
/*-----------------------------------------------------------------------------------*/

function px_sc_separator( $atts, $content = null ) {
    extract(shortcode_atts(array(
        'size'   => '',  // small, small-center, medium, medium-center
        'margin' => '',//small, medium
    ), $atts));

    $class='';

    switch($size)
    {
        case 'small':
            $class = 'hr-small';
            break;
        case 'small-center':
            $class = 'hr-small hr-center';
            break;
        case 'medium':
            $class = 'hr-medium';
            break;
        case 'medium-center':
            $class = 'hr-medium hr-center';
            break;
    }

    switch($margin)
    {
        case 'small':
            $class .= ' hr-margin-small';
            break;
        case 'medium':
            $class .= ' hr-margin-medium';
            break;
    }

    return '<hr class="'.$class.'" />';
}

add_shortcode('separator', 'px_sc_separator');

/*-----------------------------------------------------------------------------------*/
/*	Title with horizontal line
/*-----------------------------------------------------------------------------------*/

function px_sc_title( $atts, $content = null ) {
    extract(shortcode_atts(array(
        'style'   => '',//center
        'title'   => '',
    ), $atts));

    $class = $style == 'center' ? 'hr-title-center' : 'hr-title';

    ob_start();
    ?>
    <div class="<?php echo $class; ?>">
        <div></div>
        <div class="title"><h3><?php echo $title; ?></h3></div>
        <div></div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('title_separator', 'px_sc_title');

/*-----------------------------------------------------------------------------------*/
/*	Team Member
/*-----------------------------------------------------------------------------------*/

function px_sc_team_member( $atts, $content = null ) {
    $descDefaultText = 'We start by figuring out tay to the most creative, logical possible.';

    extract(shortcode_atts(array(
        'name'   => 'John Doe',
        'title'  => 'CEO',
        'url'    => '',
        'target' => '',
        'description'  => $descDefaultText,
        'image'  => '',
    ), $atts));

    if(strlen($target))
        $target = "target=\"$target\"";

    ob_start();
    ?>
        <div class="team-member">
            <?php if('' != $image){ ?>
            <div class="image">
                <img src="<?php echo $image; ?>" alt="<?php echo esc_attr($name); ?>" />
                <?php if('' != $url){ ?>
                <div class="image-overlay">
                    <div class="image-overlay-wrap">
                        <div class="overlay">
                            <span class="overlay-icon"></span>
                            <a class="overlay-link" href="<?php echo $url; ?>" <?php echo $target; ?>></a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
            <h3 class="name">
                <?php if('' != $url){ ?>
                <a href="<?php echo $url; ?>" target="<?php echo $target; ?>"><?php echo $name; ?></a>
                <?php
                } else {
                    echo $name;
                } ?>
            </h3>
            <span class="job-title"><?php echo $title; ?></span>
            <p class="description"><?php echo $description; ?></p>
            <?php
            $GLOBALS['px_sc_team_icon'] = array();
            do_shortcode($content);
            $icons = $GLOBALS['px_sc_team_icon'];

            if(count($icons))
            {?>
                <ul class="icons">
                    <?php echo implode("\n", $icons); ?>
                </ul>
            <?php
            }
            ?>
        </div>
    <?php
    return ob_get_clean();
}

add_shortcode('team_member', 'px_sc_team_member');

function px_sc_team_icon($atts, $content = null)
{
    extract(shortcode_atts(array(
        'title'   => '',
        'url'     => '#',
        'icon'    => 'evil-2',
        'target'  => '',
    ), $atts));

    if(strlen($target))
        $target = "target=\"$target\"";

    ob_start();
    ?>
    <li>
        <a href="<?php echo $url; ?>" title="<?php echo $title; ?>" <?php echo $target; ?>>
            <span class="icon-<?php echo $icon; ?>" ></span>
        </a>
    </li>
    <?php
    $GLOBALS['px_sc_team_icon'][] = ob_get_clean();
}

add_shortcode('team_icon', 'px_sc_team_icon');

/*-----------------------------------------------------------------------------------*/
/*	Image carousel
/*-----------------------------------------------------------------------------------*/

function px_sc_carousel($atts, $content=null)
{
    extract(shortcode_atts(array(
        'items_visible' => '4',
    ), $atts));

    $GLOBALS['px_sc_carousel_item'] = array();
    do_shortcode($content);
    $items = $GLOBALS['px_sc_carousel_item'];

    ob_start();
    if(count($items))
    {
        ?>
        <ul class="image-carousel" data-items="<?php echo $items_visible; ?>">
            <?php echo implode("\n", $items); ?>
        </ul>
        <?php
    }
    return ob_get_clean();
}

add_shortcode( 'carousel', 'px_sc_carousel' );

function px_sc_carousel_item($atts, $content=null)
{
    extract(shortcode_atts(array(
        'title'   => '',
        'image'   => '',
        'url'     => '',
        'target'  => '',
    ), $atts));

    ob_start();
    if('' == $url)
    {
    ?>
    <li><img src="<?php echo $image; ?>" alt="<?php echo $title; ?>" /></li>
    <?php
    }
    else
    {
    ?>
    <li><a href="<?php echo $url; ?>" title="<?php echo $title; ?>" target="<?php echo $target; ?>"><img src="<?php echo $image; ?>" /></a></li>
    <?php
    }
    $GLOBALS['px_sc_carousel_item'][] = ob_get_clean();
}

add_shortcode('carousel_item', 'px_sc_carousel_item');

/*-----------------------------------------------------------------------------------*/
/*	Progress
/*-----------------------------------------------------------------------------------*/

function px_sc_progressbar($atts, $content=null)
{
    extract(shortcode_atts(array(
        'title'   => '',
        'percent' => '75',
        'color'   =>'',
        'animate' => 'no',
    ), $atts));

    //Sanitize user input
    $percent = intval($percent);
    $percent = max(min($percent, 100), 0);

    $style = "width:$percent%;";

	if('' != $color)
		$style .= "background-color:$color;";

	if('' != $title)
		$title = "$title : $percent%";

    $class = array('progressbar');

    if('yes' == $animate)
        $class[] = 'animate';

    ob_start();
    ?>
    <div class="<?php echo implode(' ', $class); ?>">
        <h4 class="title"><?php echo $title; ?></h4>
        <div class="progress"><div class="progress-inner" style="<?php echo $style; ?>"></div></div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('progressbar', 'px_sc_progressbar');

/*-----------------------------------------------------------------------------------*/
/*	Accordion & Toggle
/*-----------------------------------------------------------------------------------*/

function px_sc_accordion($atts, $content=null)
{
    return px_sc_accordion_content('accordion', $atts, $content);
}

add_shortcode('accordion', 'px_sc_accordion');

function px_sc_toggle($atts, $content=null)
{
    return px_sc_accordion_content('toggle', $atts, $content);
}

add_shortcode('toggle', 'px_sc_toggle');

function px_sc_accordion_content($type='accordion',$atts, $content=null)
{
    $GLOBALS['px_sc_accordion_tab'] = array();
    do_shortcode($content);
    $items = $GLOBALS['px_sc_accordion_tab'];

    ob_start();
    ?>
    <div class="<?php echo $type; ?>">
        <?php echo implode("\n", $items); ?>
    </div>
    <?php
    return ob_get_clean();
}

function px_sc_accordion_tab($atts, $content=null)
{
    px_sc_accordion_tab_content('accordion', $atts, $content);
}

add_shortcode('accordion_tab', 'px_sc_accordion_tab');

function px_sc_toggle_tab($atts, $content=null)
{
    px_sc_accordion_tab_content('toggle', $atts, $content);
}

add_shortcode('toggle_tab', 'px_sc_toggle_tab');

function px_sc_accordion_tab_content($type='accordion', $atts, $content=null)
{
    extract(shortcode_atts(array(
        'title'   => 'Accordion Tab',
        'keepopen' => ''
    ), $atts));

    if($type != 'accordion' && !array_key_exists('title', $atts))
        $title = 'Toggle Tab';

    $tabClass = $keepopen != '' ? 'keep-open' : '';

    ob_start();
    ?>
    <div class="tab <?php echo $tabClass; ?>">
        <div class="header clearfix">
            <div class="tab-button"><span class="icon-minus"></span></div>
            <h4 class="title"><?php echo $title; ?></h4>
        </div>
        <div class="body">
            <?php echo do_shortcode($content); ?>
        </div>
    </div>
    <?php
    $GLOBALS['px_sc_accordion_tab'][] = ob_get_clean();
}

/*-----------------------------------------------------------------------------------*/
/*	Posts slider
/*-----------------------------------------------------------------------------------*/

function px_sc_post_slider($atts, $content=null)
{
    extract(shortcode_atts(array(
        'include_categories' => '',
        //'columns' => '2',
        'items' => -1
    ), $atts));

    //Bound
    //$columns = min(max($columns, 1), 2);
    $items   = max($items, -1);

    //Convert slugs to IDs
    $catArr  = slugs_to_ids(explode(',', $include_categories), 'category');

    $queryArgs = array(
        'posts_per_page' => $items,
    );

    //Taxonomy filter
    if(count($catArr))
    {
        $queryArgs['tax_query'] =  array(
            // Note: tax_query expects an array of arrays!
            array(
                'taxonomy' => 'category',
                'field'    => 'id',
                'terms'    => $catArr
            ));
    }

    $query = new WP_Query($queryArgs);

    ob_start();

    if( $query->have_posts()) {
    ?>
    <div class="post-slider">
        <div class="slider-head">
            <div class="slider-nav clearfix">
                <a class="nav-prev" href="#">&lt;</a>
                <span class="nav-separator"></span>
                <a class="nav-next" href="#">&gt;</a>
            </div>
        </div>
        <div class="slider-wrap">
            <ul class="slider">
                <?php
                while ($query->have_posts()) { $query->the_post();
                    $format = get_post_format();
                    if ( false === $format ) $format = 'standard';
                    $hasThumbnail = function_exists('has_post_thumbnail') && has_post_thumbnail() && 'quote' != $format;
                    $iconClass = '';

                    switch($format)
                    {
                        case 'standard':
                            $iconClass = 'icon-libreoffice';
                            break;
                        case 'video':
                            $iconClass = 'icon-youtube-2';
                            break;
                        case 'audio':
                            $iconClass = 'icon-music';
                            break;
                        case 'quote':
                            $iconClass = 'icon-quotes-left';
                            break;
                        case 'gallery':
                            $iconClass = 'icon-images';
                            break;
                    }
                ?>
                <li class="item format-<?php echo $format; ?> <?php echo $hasThumbnail ? 'thumbnail' : ''; ?>">
                    <div class="item-media">
                        <?php
                        if($hasThumbnail){
                            the_post_thumbnail("post-slider-thumb");
                        }//If thumbnail available

                        if('standard' != $format || !$hasThumbnail)
                        {
                        ?>
                        <span class="<?php echo $iconClass; ?>"></span>
                        <?php
                        }//Post icon
                        if('quote' != $format){ ?>
                        <div class="media-date">
                            <span class="day"><?php the_time('d'); ?></span>
                            <span class="month"><?php the_time('M'); ?></span>
                        </div>
                        <a href="<?php the_permalink(); ?>" class="overlay-link"></a>
                        <?php }//if is not quote format ?>
                    </div>
                    <div class="post-container">
                        <?php if('quote' != $format) { ?>
                        <h3 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <span class="comments-count"><?php comments_number(__('No Comments', TEXTDOMAIN), __('1 Comment', TEXTDOMAIN), __('% Comments', TEXTDOMAIN) ); ?></span>
                        <hr class="hr-small" />
                        <?php echo excerpt(27);
                        }//if not quote
                        else{
                        ?>
                        <h3 class="title"><?php the_title(); ?></h3>
                        <span class="comments-count"><?php _e('Said:', TEXTDOMAIN); ?></span>
                        <hr class="hr-small" />
                        <blockquote><span class="begin"></span><?php echo excerpt(40);  ?><span class="end"></span></blockquote>
                        <?php }//If quote ?>
                    </div>
                </li>
                <?php }//while have posts ?>
            </ul>
        </div>
    </div>
    <?php
    }//If have posts
    wp_reset_query();

    return ob_get_clean();
}

add_shortcode('post_slider', 'px_sc_post_slider');

/*-----------------------------------------------------------------------------------*/
/*	Portfolio slider
/*-----------------------------------------------------------------------------------*/

function px_sc_portfolio_slider($atts, $content=null)
{
    extract(shortcode_atts(array(
        'include_categories' => '',
        //'columns' => '2',
        'items' => -1
    ), $atts));

    //Bound
    //$columns = min(max($columns, 1), 2);
    $items   = max($items, -1);

    //Convert slugs to IDs
    $catArr  = slugs_to_ids(explode(',', $include_categories), 'skills');

    $queryArgs = array(
        'post_type'      => 'portfolio',
        'posts_per_page' => $items,
    );

    //Taxonomy filter
    if(count($catArr))
    {
        $queryArgs['tax_query'] =  array(
            // Note: tax_query expects an array of arrays!
            array(
                'taxonomy' => 'skills',
                'field'    => 'id',
                'terms'    => $catArr
            ));
    }

    $query = new WP_Query($queryArgs);

    ob_start();

    if( $query->have_posts()) {
        ?>
        <div class="post-slider">
            <div class="slider-head">
                <div class="slider-nav clearfix">
                    <a class="nav-prev" href="#">&lt;</a>
                    <span class="nav-separator"></span>
                    <a class="nav-next" href="#">&gt;</a>
                </div>
            </div>
            <div class="slider-wrap">
                <ul class="slider">
                    <?php
                    while ($query->have_posts()) { $query->the_post();
                        $hasThumbnail = function_exists('has_post_thumbnail') && has_post_thumbnail();
                        $iconClass = 'icon-images';

                        ?>
                        <li class="item <?php echo $hasThumbnail ? 'thumbnail' : ''; ?>">
                            <div class="item-media">
                                <?php
                                if($hasThumbnail){
                                    the_post_thumbnail("post-slider-thumb");
                                }//If thumbnail available

                                if(!$hasThumbnail)
                                {
                                    ?>
                                    <span class="<?php echo $iconClass; ?>"></span>
                                <?php
                                }//Post icon
                                ?>
                            </div>
                            <div class="post-container">
                                <h3 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <span class="comments-count"><?php echo implode_post_terms('skills'); ?></span>
                                <hr class="hr-small" />
                                <?php echo excerpt(27); ?>
                            </div>
                        </li>
                    <?php }//while have posts ?>
                </ul>
            </div>
        </div>
    <?php
    }//If have posts
    wp_reset_query();

    return ob_get_clean();
}

add_shortcode('portfolio_slider', 'px_sc_portfolio_slider');

/*-----------------------------------------------------------------------------------*/
/*	Horizontal tab
/*-----------------------------------------------------------------------------------*/

function px_sc_horizontal_tab_group($atts, $content=null)
{
    extract(shortcode_atts(array(
        'title_color'   => '',
    ), $atts));

    //Create counter
    if(array_key_exists('px_sc_horizontal_tab_group', $GLOBALS))
        $GLOBALS['px_sc_horizontal_tab_group']++;
    else
        $GLOBALS['px_sc_horizontal_tab_group'] = 1;

    $id = $GLOBALS['px_sc_horizontal_tab_group'];
    $id = "horizontal-tab$id";

    $GLOBALS['px_sc_horizontal_tab'] = array();
    do_shortcode($content);
    $items = $GLOBALS['px_sc_horizontal_tab'];

    ob_start();

    if('' != $title_color)
    {
    ?>
    <style type="text/css">
        <?php echo "#$id"; ?> .titles-container{
            border-right: 1px solid <?php echo $title_color; ?>;
        }
        <?php echo "#$id"; ?> .titles li{
            color: <?php echo $title_color; ?>;
        }
    </style>
    <?php
    }//if title color is set ?>
    <div id="<?php echo $id; ?>" class="horizontal-tab clearfix">
        <div class="titles-container">
            <ul class="titles">
            <?php foreach($items as $item){ ?>
                <li><?php echo $item[0]; ?></li>
            <?php } ?>
            </ul>
            <div class="pointer"></div>
        </div>
        <ul class="tabs-container">
        <?php foreach($items as $item) {?>
            <li class="tab">
                <?php echo $item[1]; ?>
            </li>
        <?php } ?>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('horizontal_tab_group', 'px_sc_horizontal_tab_group');

function px_sc_horizontal_tab($atts, $content=null)
{
    $tabNumber = count($GLOBALS['px_sc_horizontal_tab']) + 1;

    extract(shortcode_atts(array(
        'title'   => "Tab $tabNumber",
    ), $atts));

    $GLOBALS['px_sc_horizontal_tab'][] = array($title, do_shortcode($content));
}

add_shortcode('horizontal_tab', 'px_sc_horizontal_tab');

/*-----------------------------------------------------------------------------------*/
/*	Testimonials
/*-----------------------------------------------------------------------------------*/

function px_sc_testimonial_group($atts, $content=null)
{
    $GLOBALS['px_sc_testimonial'] = array();
    do_shortcode($content);
    $items = $GLOBALS['px_sc_testimonial'];

    ob_start();
    ?>
    <div class="testimonials">
        <div class="item-list">
        <?php echo implode("\n", $items); ?>
        </div>
        <div class="testimonials-controls">
            <a href="#" class="previous"><?php _e('Previous', TEXTDOMAIN); ?></a>
            <span class="separator"></span>
            <a href="#" class="next"><?php _e('Next', TEXTDOMAIN); ?></a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('testimonial_group', 'px_sc_testimonial_group');

function px_sc_testimonial($atts, $content=null)
{
    extract(shortcode_atts(array(
        'name'       => 'Joone Doyee',
        'image'      => '',
        'comment'    => '',
        'style'      => 'style1',
        'background' => 'no',
        'skin'       => 'dark',
        'animate'    => 'no',
    ), $atts));

    $hasImage = '' != $image;
    $class    = array("clearfix", "testimonial");

    if('style2' == $style)
        $class[] = "testimonial2";

    if($hasImage)
        $class[] = "has-image";

    if('yes' == $background)
        $class[] = "has-background";

    if('light' == $skin)
        $class[] = "skin-light";

    if('yes' == $animate)
        $class[] = "animate";

    ob_start();
    ?>
    <div class="<?php echo implode(' ', $class); ?>">
        <?php if($hasImage){ ?>
            <div class="image">
                <img src="<?php echo $image; ?>" alt="<?php echo esc_attr($name); ?>" />
            </div>
        <?php } ?>
        <div class="quote">
            <blockquote><span class="begin"></span><?php echo $comment; ?><span class="end"></span></blockquote>
            <hr />
            <h4 class="name"><?php echo $name; ?></h4>
        </div>
    </div>
    <?php
    $ret = ob_get_clean();
    $GLOBALS['px_sc_testimonial'][] = $ret;
    return $ret;
}

add_shortcode('testimonial', 'px_sc_testimonial');

/*-----------------------------------------------------------------------------------*/
/*	Icon-Box
/*-----------------------------------------------------------------------------------*/

function px_sc_iconbox_shape($atts, $content=null)
{
    extract(shortcode_atts(array(
        'icon'       => 'wand',
        'icon_color' => '',
        'shape'      => 'hex',
        'shape_color'=> '',
        'title'      => '',
        'title_color'=> '',
        'text_color' => '',
        'animate'    => 'no',
    ), $atts));

    $hasStyle = '' != $icon_color || '' != $shape_color || '' != $title_color || '' != $text_color;


    $content    = do_shortcode($content);
    $hasTitle   = '' != $title;
    $hasContent = '' != trim($content);

    $class = array('iconbox');

    if('hex' == $shape)
        $class[] = 'iconbox-hex';
    else
        $class[] = 'iconbox-circle';

    if(!$hasTitle && !$hasContent)
        $class[] = 'no-content';

    if('yes' == $animate)
    {
        $class[] = 'animate';
    }

    //Generate ID for this shortcode
    if(array_key_exists('px_sc_iconbox_shape', $GLOBALS))
        $GLOBALS['px_sc_iconbox_shape']++;
    else
        $GLOBALS['px_sc_iconbox_shape'] = 1;

    $id = $GLOBALS['px_sc_iconbox_shape'];
    $id = "iconbox-shape$id";

    ob_start();
    if($hasStyle)
    {
    ?>
    <style type="text/css">
        <?php if(strlen($icon_color))
        {
            echo "#$id > .icon .glyph"; ?>
            {
                color: <?php echo $icon_color; ?>;
            }
        <?php
        }
        if(strlen($shape_color))
        {
            echo "#$id > .icon"; ?>
            {
                background-color: <?php echo $shape_color; ?>;
            }
        <?php
        }
        if(strlen($title_color && $hasTitle))
        {
            echo "#$id > .title"; ?>
            {
                color: <?php echo $title_color; ?>;
            }
        <?php
        }
        if(strlen($text_color))
        {
            echo "#$id > .content"; ?>
            {
                color: <?php echo $text_color; ?>;
            }
        <?php
        }?>
    </style>
    <?php
    }//if($hasStyle)
    ?>
    <div id="<?php echo $id; ?>" class="<?php echo implode(' ', $class); ?>">
        <div class="icon">
            <?php if('hex'==$shape){ ?>
            <img class="shape" src="<?php echo path_combine(THEME_IMAGES_URI, 'hexagon.png'); ?>" alt="<?php echo esc_attr($title); ?>" />
            <?php } ?>
            <span class="glyph icon-<?php echo $icon; ?>"></span>
        </div>
        <?php if($hasTitle){ ?>
            <h3 class="title"><?php echo $title; ?></h3>
            <hr class="hr-center hr-small hr-margin-small" />
        <?php } ?>
        <div class="content"><?php echo do_shortcode($content); ?></div>
    </div>

    <?php
    return ob_get_clean();
}

add_shortcode( 'iconbox_shape', 'px_sc_iconbox_shape' );

function px_sc_iconbox($atts, $content=null)
{
    extract(shortcode_atts(array(
        'title'         => '',
        'title_color'   => '',
        'icon'          => 'wand',
        'icon_color'    => '',
        'icon_position' => 'left',
        'url'           => '',
        'url_text'      => __('Learn More', TEXTDOMAIN),
        'target'        => '',
        'text_color'    => '',
        'animate'       => 'no'
    ), $atts));

    $hasStyle  = '' != $icon_color || '' != $title_color || '' != $text_color;
    $hasTitle  = '' != $title;

    //Generate ID for this shortcode
    if(array_key_exists('px_sc_iconbox', $GLOBALS))
        $GLOBALS['px_sc_iconbox']++;
    else
        $GLOBALS['px_sc_iconbox'] = 1;

    $id = $GLOBALS['px_sc_iconbox'];
    $id = "iconbox-$id";

    $class  = array("iconbox");

    switch($icon_position)
    {
        case 'top':
            $class[] = 'iconbox-top';
            break;
        case 'left':
        default:
            $class[] = 'iconbox-left';
    }

    if('yes' == $animate)
    {
        $class[] = 'animate';
    }

    ob_start();
    if($hasStyle)
    {
        ?>
        <style type="text/css">
            <?php if('' != $icon_color)
            {
                echo "#$id > .icon .glyph"; ?>
                {
                    color: <?php echo $icon_color; ?>;
                }
            <?php
            }
            if('' != $title_color && $hasTitle)
            {
                echo "#$id > .content-wrap > .title"; ?>
                {
                    color: <?php echo $title_color; ?>;
                }
            <?php
            }
            if('' != $text_color)
            {
                echo "#$id > .content-wrap > .content"; ?>
                {
                    color: <?php echo $text_color; ?>;
                }
            <?php
            }?>
        </style>
    <?php
    }//if($hasStyle)
    ?>
    <div id="<?php echo $id; ?>" class="<?php echo implode(' ', $class); ?>">
        <div class="icon">
            <span class="glyph icon-<?php echo $icon; ?>"></span>
        </div>
        <div class="content-wrap">
            <?php if($hasTitle){ ?>
                <h3 class="title"><?php echo $title; ?></h3>
            <?php } ?>
            <div class="content"><?php echo $content; ?></div>
            <?php if(strlen($url)){ ?>
                <div class="more-link">
                    <a href="<?php $url; ?>" target="<?php echo $target; ?>"><?php echo $url_text; ?><span class="icon-play"></span></a>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php
    return ob_get_clean();
}

add_shortcode( 'iconbox', 'px_sc_iconbox' );

/*-----------------------------------------------------------------------------------*/
/*	Parallax
/*-----------------------------------------------------------------------------------*/

function px_sc_parallax($atts, $content=null)
{
    extract(shortcode_atts(array(
        'image' => '',
        'x_position'  => '50%',
        'speed' => '0.1',
        'height' => '200',

        'title' => '',
        'subtitle' => '',
        'title_animation' => 'from-top',
        'title_animation_time' => '1',//In seconds
    ), $atts));

    $title_animation_time = floatval($title_animation_time);
    if(0 >= $title_animation_time) $title_animation_time = 1;

    if('auto'!=$height)
        $height .= 'px';

    $style                = "background-image: url($image); background-size: cover; height:{$height}";
    $renderTitle          = strlen($title);
    $renderSubtitle       = strlen($subtitle);
    $renderTitleSubtitle  = $renderTitle && $renderSubtitle;
    $renderContent        = !($renderTitle || $renderSubtitle);
    $titleAnimationAttr   = $renderContent ? '' : sprintf('data-titleanimation="%s" data-titleanimationtime="%s"', $title_animation, $title_animation_time);

    ob_start();
    ?>
    <div class="parallax" style="<?php echo $style; ?>" data-xpos="<?php echo $x_position; ?>" data-speed="<?php echo $speed; ?>" <?php echo $titleAnimationAttr; ?>>
        <?php
        if($renderContent)
        {
            echo do_shortcode($content);
        }
        else
        {
            if($renderTitle)
            {?>
                <h4 class="title"><?php echo $title; ?></h4>
            <?php
            }
            if($renderTitleSubtitle)
            {?>
               <hr />
            <?php
            }
            if($renderSubtitle)
            {?>
                <span class="subtitle"><?php echo $subtitle; ?></span>
            <?php
            }
        }
        ?>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'parallax', 'px_sc_parallax' );

/*-----------------------------------------------------------------------------------*/
/*	Tabs
/*-----------------------------------------------------------------------------------*/

function px_sc_tab_group($atts, $content=null)
{
    $GLOBALS['px_sc_tab'] = array();
    do_shortcode($content);
    $tabs = $GLOBALS['px_sc_tab'];

    ob_start();
    ?>
    <div class="tabs">
    <?php if(count($tabs)){ ?>
        <ul class="head clearfix">
            <?php foreach($tabs as $tab){ ?>
            <li><?php echo $tab[0]; ?></li>
            <?php } ?>
        </ul>
        <div class="content">
            <?php foreach($tabs as $tab){ ?>
                <div class="tab-content"><?php echo $tab[1]; ?></div>
            <?php } ?>
        </div>
    <?php } ?>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'tab_group', 'px_sc_tab_group' );

function px_sc_tab($atts, $content=null)
{
    $tabCnt = count($GLOBALS['px_sc_tab']) + 1;

    extract(shortcode_atts(array(
        'title' => "Tab $tabCnt",
    ), $atts));

    $GLOBALS['px_sc_tab'][] = array($title, do_shortcode($content));
}

add_shortcode( 'tab', 'px_sc_tab' );

/*-----------------------------------------------------------------------------------*/
/*	Sidebar
/*-----------------------------------------------------------------------------------*/

function px_sc_sidebar($atts, $content=null)
{
    extract(shortcode_atts(array(
        'name' => 'Main Sidebar',
    ), $atts));

    ob_start();
    ?>
    <div class="sidebar widget-area"><?php dynamic_sidebar($name); ?></div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'sidebar', 'px_sc_sidebar' );

/*-----------------------------------------------------------------------------------*/
/*	Button
/*-----------------------------------------------------------------------------------*/

function px_sc_button($atts, $content = null)
{
    extract(shortcode_atts(array(
        'title'            => '',
        'text'             => __('View Page', TEXTDOMAIN),
        'text_color'       => '',
        'button_color'     => '',
        'url'              => '#',
        'target'           => '',
        'size'             => '',
        'style'            => '',
    ), $atts));

    if('' != $target)
        $target = "target=\"$target\"";

    $class = "button";

    if('style2' == $style)
        $class .= ' button2';

    switch($size)
    {
        case 'small':
            $class .=' button-small';
            break;
        case 'large':
            $class .=' button-large';
            break;
    }

    $elStyle = "";

    if('' != $button_color)
        $elStyle .= "background-color:$button_color;";

    if('' != $text_color)
        $elStyle .= "color:$text_color;";

    ob_start();
    ?>
    <a class="<?php echo $class; ?>" style="<?php echo $elStyle; ?>" href="<?php echo esc_attr($url); ?>" title="<?php echo esc_attr($title); ?>" <?php echo $target; ?>><?php echo $text; ?></a>
    <?php
    return ob_get_clean();
}

add_shortcode('button', 'px_sc_button');

/*-----------------------------------------------------------------------------------*/
/*	GMap
/*-----------------------------------------------------------------------------------*/

function px_sc_gmap($atts, $content = null)
{
    extract(shortcode_atts(array(
        'zoom'    => '8',
        'controls'=> 'show',
        'height'  => '300',
        'address' => '',
        'lat'     => '',
        'lng'     => '',
    ), $atts));

    $height = intval($height);
    $height = max($height, 50);
    $height .= 'px';

    $zoom = intval($zoom);
    $zoom = max(min($zoom, 19), 1);

    $lat = trim($lat);
    $lng = trim($lng);

    if('' != $lat)
        $lat = floatval($lat);
    if('' != $lng)
        $lng = floatval($lng);

    $controls = $controls == 'show' ? 'true' : 'false';

    //Get markers
    $GLOBALS['px_sc_gmap_marker'] = array();
    do_shortcode($content);
    $markers = $GLOBALS['px_sc_gmap_marker'];

    ob_start();
    ?>
    <div class="gmap" style="height:<?php echo $height; ?>" data-zoom="<?php echo $zoom; ?>" data-controls="<?php echo $controls; ?>" data-address="<?php echo esc_attr($address); ?>" data-lat="<?php echo $lat; ?>" data-lng="<?php echo $lng; ?>">
        <?php echo implode("\n", $markers); ?>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('gmap', 'px_sc_gmap');

function px_sc_gmap_marker($atts, $content = null)
{
    extract(shortcode_atts(array(
        'address' => '',
        'lat'     => '',
        'lng'     => '',
        'icon'    => ''
    ), $atts));

    $lat = trim($lat);
    $lng = trim($lng);

    if('' != $lat)
        $lat = floatval($lat);
    if('' != $lng)
        $lng = floatval($lng);

    ob_start();
    ?>
    <div class="gmap-marker" data-address="<?php echo esc_attr($address); ?>" data-lat="<?php echo $lat; ?>" data-lng="<?php echo $lng; ?>" data-icon="<?php echo esc_attr($icon); ?>"></div>
    <?php

    $GLOBALS['px_sc_gmap_marker'][] = ob_get_clean();
}

add_shortcode('gmap_marker', 'px_sc_gmap_marker');
