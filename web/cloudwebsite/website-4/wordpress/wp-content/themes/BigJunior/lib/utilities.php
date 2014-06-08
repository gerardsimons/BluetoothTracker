<?php

if (!function_exists('file_get_html')) require_once(THEME_LIB . '/includes/simple_html_dom.php');

function get_pagination($query = null, $range = 3) {
    global $paged, $wp_query;

    $q = $query == null ? $wp_query : $query;
    $output = '';

    // How much pages do we have?
    if ( !isset($max_page) ) {
        $max_page = $q->max_num_pages;
    }

    // We need the pagination only if there is more than 1 page
    if ( $max_page < 2 )
        return $output;

    $output .= '<div class="post-pagination">';

    if ( !$paged ) $paged = 1;

    // To the previous page
    if($paged > 1)
        $output .= '<a class="prev-page-link" href="' . get_pagenum_link($paged-1) . '">'. __('Prev', TEXTDOMAIN) .'</a>';

    if ( $max_page > $range + 1 ) {
        if ( $paged >= $range )
            $output .= '<a href="' . get_pagenum_link(1) . '">1</a>';
        if ( $paged >= ($range + 1) )
            $output .= '<span class="page-numbers">&hellip;</span>';
    }

    // We need the sliding effect only if there are more pages than is the sliding range
    if ( $max_page > $range ) {
        // When closer to the beginning
        if ( $paged < $range ) {
            for ( $i = 1; $i <= ($range + 1); $i++ ) {
                $output .= ( $i != $paged ) ? '<a href="' . get_pagenum_link($i) .'">'.$i.'</a>' : '<span class="this-page">'.$i.'</span>';
            }
            // When closer to the end
        } elseif ( $paged >= ($max_page - ceil(($range/2))) ) {
            for ( $i = $max_page - $range; $i <= $max_page; $i++ ) {
                $output .= ( $i != $paged ) ? '<a href="' . get_pagenum_link($i) .'">'.$i.'</a>' : '<span class="this-page">'.$i.'</span>';
            }
            // Somewhere in the middle
        } elseif ( $paged >= $range && $paged < ($max_page - ceil(($range/2))) ) {
            for ( $i = ($paged - ceil($range/2)); $i <= ($paged + ceil(($range/2))); $i++ ) {
                $output .= ($i != $paged) ? '<a href="' . get_pagenum_link($i) .'">'.$i.'</a>' : '<span class="this-page">'.$i.'</span>';
            }
        }
        // Less pages than the range, no sliding effect needed
    } else {
        for ( $i = 1; $i <= $max_page; $i++ ) {
            $output .= ($i != $paged) ? '<a href="' . get_pagenum_link($i) .'">'.$i.'</a>' : '<span class="this-page">'.$i.'</span>';
        }
    }

    if ( $max_page > $range + 1 ){
        // On the last page, don't put the Last page link
        if ( $paged <= $max_page - ($range - 1) )
            $output .= '<span class="page-numbers">&hellip;</span><a href="' . get_pagenum_link($max_page) . '">' . $max_page . '</a>';
    }

    // Next page
    if($paged < $max_page)
        $output .= '<a class="next-page-link" href="' . get_pagenum_link($paged+1) . '">'. __('Next', TEXTDOMAIN) .'</a>';

    $output .= '</div><!-- post-pagination -->';

    echo $output;
}

// retrieves the attachment ID from the file URL
function get_image_id($image_url) {
	global $wpdb;

	$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM " .$wpdb->prefix. "posts WHERE guid='%s';", $image_url));
	
	if(count($attachment))
		return $attachment[0];
	else
		return -1;
}

function get_related_posts_by_taxonomy($postId, $taxonomy, $maxPosts = 9)
{
	$terms   = wp_get_object_terms($postId, $taxonomy);

	if (!count($terms))
		return new WP_Query();
		
	$termsSlug = array();
	
	foreach($terms as $term)
		$termsSlug[] = $term->slug;

	$args=array(
	  'post__not_in' => array($postId),
	  'post_type' => get_post_type($postId),
	  'showposts'=>$maxPosts,
	  'tax_query' => array(
		array(
				'taxonomy' => $taxonomy,
				'field' => 'slug',
				'terms' => $termsSlug
			)
		)
	);
	
	return new WP_Query($args);
}

//Return theme option
function opt($option){
	$opt = get_option(OPTIONS_KEY);

	return stripslashes($opt[$option]);
}

//Echo theme option
function eopt($option){
	echo opt($option);
}

function PrintTerms($terms, $separatorString)
{
	$termIndex = 1;
	if($terms) 
	foreach ($terms as $term) 
	{ 
		echo $term->name; 
		
		if(count($terms) > $termIndex) 
			echo $separatorString; 

		$termIndex++;
	}
}

function format_attr($name, $val)
{
	return $name . '="'. $val . '" ';
}

function format_std_attrs(array $params)
{
	$attrs =  '';
	$keys = array_keys($params);
	
	foreach ($keys as $key)
	{
		if($key != 'id' && $key != 'class' && 
		   $key != 'style' && $key != 'src' && 
		   $key != 'href' && $key != 'alt' && 
		   $key != 'type')
		   continue;

		$attrs .= format_attr($key, $params[$key]);
	}
	
	return $attrs;
}

//Returns a html image tag string
function img(array $params){

	if(!isset($params['file']))
		throw new Exception('file parameter is missing.');
	
	$params['src'] = THEME_IMAGES_URI . '/' . $params['file'];
	
	$tag = '<img ' . format_std_attrs($params) . '/>';
	
	echo $tag;
}

//Returns a html script tag string
function js(array $params){
	echo get_js($params);
}

function get_js(array $params){
	
	if(!isset($params['file']))
		throw new Exception('file parameter is missing.');
	
	$params['type'] = 'text/javascript';
	$params['src'] = THEME_JS_URI . '/' . $params['file'];
	
	return '<script ' . format_std_attrs($params) . '></script>';
}

/*
 * Gets array value with specified key, if the key doesn't exist
 * default value is returned
 */
function array_value($key, $arr, $default='')
{
    return array_key_exists($key, $arr) ? $arr[$key] : $default;
}


/*
 * Deletes attachment by given url
 */
function delete_attachment( $url ) {
    global $wpdb;

    // We need to get the image's meta ID.
    $query = "SELECT ID FROM wp_posts where guid = '" . esc_url($url) . "' AND post_type = 'attachment'";
    $results = $wpdb->get_results($query);

    // And delete it
    foreach ( $results as $row ) {
        wp_delete_attachment( $row->ID );
    }
}

function get_post_terms_names($taxonomy)
{
    $terms = get_the_terms( get_the_ID(), $taxonomy );

    if(!is_array($terms))
        return $terms;

    $termNames = array();

    foreach ($terms as $term)
        $termNames[] = $term->name;

    return $termNames;
}

/*
 * Concatenate post category names
 */
function implode_post_terms($taxonomy, $separator = ', ')
{
    $terms = get_post_terms_names($taxonomy);

    if(!is_array($terms))
        return null;

    return implode($separator, $terms);
}

/*
 * Converts array of slugs to corresponding array of IDs
 */
function slugs_to_ids($slugs=array(), $taxonomy)
{
    $tempArr = array();
    foreach($slugs as $slug)
    {
        if(!strlen(trim($slug))) continue;
        $term = get_term_by('slug', $slug, $taxonomy);
        if(!$term) continue;
        $tempArr[] = $term->term_id;
    }

    return $tempArr;
}

function get_meta($key, $single = true)
{
    $pid = null;

    if(in_the_loop() || is_single() || (is_page() && !is_home()))
    {
        $pid = get_the_ID();
    }
    //Special case for blog page
    elseif(is_home() && !is_front_page())
    {
        $pid = get_option('page_for_posts');
    }

    if(null == $pid)
        return '';

    return get_post_meta($pid, $key, $single);
}

/* Get video url from known sources such as youtube and vimeo */

function extract_video_info($string)
{
    //check for youtube video url
    if(preg_match('/https?:\/\/(?:www\.)?youtube\.com\/watch\?v=[^&\n\s"<>]+/i', $string, $matches ))
    {
        $url = parse_url($matches[0]);
        parse_str($url['query'], $queryParams);

        return array('type'=>'youtube', 'url'=> $matches[0], 'id' => $queryParams['v']);
    }
    //Vimeo
    else if(preg_match('/https?:\/\/(?:www\.)?vimeo\.com\/\d+/i', $string, $matches))
    {
        $url = parse_url($matches[0]);

        return array('type'=>'vimeo', 'url'=> $matches[0], 'id' => ltrim($url['path'], '/'));
    }

    return null;
}

function extract_audio_info($string)
{
    //check for soundcloud url
    if(preg_match('/https?:\/\/(?:www\.)?soundcloud\.com\/[^&\n\s"<>]+\/[^&\n\s"<>]+\/?/i', $string, $matches ))
    {
        return array('type'=>'soundcloud', 'url'=> $matches[0]);
    }

    return null;
}

function get_video_meta(array &$video)
{
    if($video['type']  != 'youtube' && $video['type'] != 'vimeo')
        return null;

    $ret = get_url_content($video['url']/*, '127.0.0.1:8080'*/);

    if(is_array($ret))
        return 'Server Error: ' . $ret['error'] . " \nError No: " . $ret['errorno'];

    if(trim($ret) == '')
        return 'Error: got empty response from youtube';

    $html = str_get_html($ret);
    $vW   = $html->find('meta[property="og:video:width"]');
    $vH   = $html->find('meta[property="og:video:height"]');

    if(count($vW) && count($vH))
    {
        $video['width']  = $vW[0]->content;
        $video['height'] = $vH[0]->content;
    }

    return null;
}

function soundcloud_get_embed($url)
{
    $json = get_url_content("http://soundcloud.com/oembed?format=json&url=$url"/*, '127.0.0.1:8580'*/);

    if(is_array($json))
        return 'Server Error: ' . $json['error'] . " \nError No: " . $json['errorno'];

    if(trim($json) == '')
        return 'Error: got empty response from soundcloud';

    //Convert the response string to PHP object
    $data = json_decode($json);

    if(NULL == $data)
        return "Cant decode the soundcloud response \nData: $json" ;

    //TODO: add additional error checks

    return $data->html;
}

/* downloads data from given url */

function get_url_content($url, $proxy='')
{
    $ch = curl_init();

    // set URL and other appropriate options
    $options = array( CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true );

    if($proxy != '')
        $options[CURLOPT_PROXY] = $proxy;

    // set URL and other appropriate options
    curl_setopt_array($ch, $options);

    $ret = curl_exec($ch);

    if(curl_errno($ch))
        $ret = array('error' => curl_error($ch), 'errorno' => curl_errno($ch));

    curl_close($ch);
    return $ret;
}


//Thanks to:
//http://bavotasan.com/tutorials/limiting-the-number-of-words-in-your-excerpt-or-content-in-wordpress/
function excerpt($limit) {
    $excerpt = explode(' ', get_the_excerpt(), $limit);
    if (count($excerpt)>=$limit) {
        array_pop($excerpt);
        $excerpt = implode(" ",$excerpt).'...';
    } else {
        $excerpt = implode(" ",$excerpt);
    }
    $excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
    return $excerpt;
}

/*Layer slider*/

function GetLayerSliderSlides()
{
    // Get WPDB Object
    global $wpdb;

    // Table name
    $table_name = $wpdb->prefix . "layerslider";

    // Get sliders
    $sliders = $wpdb->get_results( "SELECT * FROM $table_name
                                        WHERE flag_hidden = '0' AND flag_deleted = '0'
                                        ORDER BY date_c ASC LIMIT 100" );

    $items = array('no-slider'=>'');

    // Iterate over the sliders
    foreach($sliders as $key => $item) {
        $items[$item->id] = $item->name;
    }

    return $items;
}

/* CF7 */

function GetContactForm7Forms()
{
    // Get WPDB Object
    global $wpdb;

    // Table name
    $table_name = $wpdb->prefix . "posts";

    // Get forms
    $forms = $wpdb->get_results( "SELECT * FROM $table_name
                                  WHERE post_type='wpcf7_contact_form'
                                  LIMIT 100" );

    $items = array('no-form'=>'');

    // Iterate over the sliders
    foreach($forms as $key => $item) {
        $items[$item->ID] = $item->post_title;
    }


    return $items;
}

/* Search Pages by content */

function SearchPagesByContent($cnt)
{
    // Get WPDB Object
    global $wpdb;

    $sql = $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_type='page' AND post_status='publish' AND post_content LIKE %s",
        '%' . like_escape($cnt) . '%' );

    // Get forms
    $pages = $wpdb->get_results( $sql );

    return $pages;
}

/* Page title bar */

function px_title_bar($titleText='')
{
    $show = true;
    //When a static page is selected as posts page
    if(!is_front_page() && is_home())
    {
        //Get the page id
        $pid       = get_option('page_for_posts');

        //Page title bar
        if(get_post_meta($pid, 'title-bar', true))
        {
            $tmp   = get_post_meta($pid, 'title-text', true);
            $title = strlen($tmp) ? $tmp : get_the_title($pid);
        }
        else
        {
            $show = false;
        }
    }
    elseif(is_search())
    {
        $title = __("Search", TEXTDOMAIN);
        global $wp_query;
        if(!empty($wp_query->found_posts) && $wp_query->found_posts > 0)
        {
            $title = $wp_query->found_posts . __(" Results found for", TEXTDOMAIN) . " '". get_search_query() . "'";
        }
    }
    elseif( is_category() ){
        $title = sprintf(__('All posts in %s', TEXTDOMAIN), single_cat_title('',false));
    }
    elseif( is_tag() ){
        $title = sprintf(__('All posts tagged %s', TEXTDOMAIN), single_tag_title('',false));
    }
    elseif( is_day() ){
        $title = sprintf(__('Archive for %s', TEXTDOMAIN), get_the_time('F jS, Y'));
    }
    elseif( is_month() ){
        $title = sprintf(__('Archive for %s', TEXTDOMAIN), get_the_time('F, Y'));
    }
    elseif ( is_year() ){
        $title = sprintf(__('Archive for %s', TEXTDOMAIN), get_the_time('Y') );
    }
    elseif ( is_author() ){
        /* Get author data */
        if(get_query_var('author_name'))
            $curauth = get_user_by('login', get_query_var('author_name'));
        else
            $curauth = get_userdata(get_query_var('author'));

        $title = sprintf(__('Posts by %s', TEXTDOMAIN), $curauth->display_name );
    }
    elseif(is_page())
    {
        if(get_meta('title-bar'))
        {
            if(strlen(get_meta('title-text')))
                $title = get_meta('title-text');
        }
        else
            $show = false;
    }

    if(!isset($title) && strlen($titleText))
        $title = $titleText;

    if($show)
        include(locate_template('templates/title.php'));
}

function px_get_custom_sidebars()
{
    $sidebarStr = opt('custom_sidebars');

    if(strlen($sidebarStr) < 1)
        return array();

    $arr      = explode(',', $sidebarStr);
    $sidebars = array();

    foreach($arr as $item)
    {
        $sidebars["custom-" . hash("crc32b", $item)] = str_replace('%666', ',', $item);
    }

    return $sidebars;
}
