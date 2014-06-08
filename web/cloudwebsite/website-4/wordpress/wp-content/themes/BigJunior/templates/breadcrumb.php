<?php
/* Modified version of
 * http://dimox.net/wordpress-breadcrumbs-without-a-plugin/
 * Code
 */


/* === OPTIONS === */
$text['home']     = __('Home', TEXTDOMAIN); // text for the 'Home' link
$text['category'] = __('Archive by Category "%s"', TEXTDOMAIN); // text for a category page
$text['search']   = __('Search Results for "%s" Query', TEXTDOMAIN); // text for a search results page
$text['tag']      = __('Posts Tagged "%s"', TEXTDOMAIN); // text for a tag page
$text['author']   = __('Articles Posted by %s', TEXTDOMAIN); // text for an author page
$text['404']      = __('Error 404', TEXTDOMAIN); // text for the 404 page

$show_current   = 1; // 1 - show current post/page/category title in breadcrumbs, 0 - don't show
$show_on_home   = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
$show_home_link = 1; // 1 - show the 'Home' link, 0 - don't show
$show_title     = 1; // 1 - show the title for the links, 0 - don't show
$delimiter      = '<span class="separator">/</span>'; // delimiter between crumbs
$before         = '<span class="current">'; // tag before the current crumb
$after          = '</span>'; // tag after the current crumb
$delimiterEsc   = preg_quote($delimiter);
/* === END OF OPTIONS === */

global $post;
$home_link    = home_url();
$link_before  = '<span typeof="v:Breadcrumb">';
$link_after   = '</span>';
$link_attr    = ' rel="v:url" property="v:title"';
$link         = $link_before . '<a' . $link_attr . ' href="%1$s">%2$s</a>' . $link_after;
$parent_id    = $parent_id_2 = $post->post_parent;
$frontpage_id = get_option('page_on_front');
$trail        = array();

if (is_home() || is_front_page()) {

    if ($show_on_home == 1)
        $trail[] = '<a href="' . $home_link . '">' . $text['home'] . '</a>';

}
else
{


    if ($show_home_link == 1)
    {
        $trail[] = sprintf($link, $home_link, $text['home']);
    }

    if ( is_category() )
    {
        $this_cat = get_category(get_query_var('cat'), false);
        if ($this_cat->parent != 0) {
            $cats = get_category_parents($this_cat->parent, TRUE, $delimiter);
            $cats = preg_replace("#^(.+)$delimiterEsc$#", "$1", $cats);
            $cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
            $cats = str_replace('</a>', '</a>' . $link_after, $cats);
            if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
            $trail[] = $cats;
        }
        if ($show_current == 1) $trail[] = $before . sprintf($text['category'], single_cat_title('', false)) . $after;

    }
    elseif ( is_search() )
    {

        $trail[] = $before . sprintf($text['search'], get_search_query()) . $after;

    }
    elseif ( is_day() )
    {

        $trail[] = sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y'));
        $trail[] = sprintf($link, get_month_link(get_the_time('Y'),get_the_time('m')), get_the_time('F'));
        $trail[] = $before . get_the_time('d') . $after;

    }
    elseif ( is_month() )
    {
        $trail[] = sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y'));
        $trail[] = $before . get_the_time('F') . $after;
    }
    elseif ( is_year() )
    {
        $trail[] = $before . get_the_time('Y') . $after;

    }
    elseif ( is_single() && !is_attachment() )
    {
        $postType = get_post_type();

        //If a custom post type
        if ( 'post' != $postType ) {

            if(apply_filters("px_breadcrumb_single_trail_handler", $postType))
            {
                $trail = apply_filters("px_breadcrumb_single_trail_filter", $postType, $trail, $delimiter);
            }
            else
            {
                $post_type = get_post_type_object($postType);
                $slug = $post_type->rewrite;
                $trail[] = sprintf($link, $home_link . '/' . $slug['slug'] . '/', $post_type->labels->singular_name);
            }

            if ($show_current == 1) $trail[] = $before . get_the_title() . $after;
        }
        else
        {
            $cat = get_the_category(); $cat = $cat[0];
            $cats = get_category_parents($cat, TRUE, $delimiter);
            $cats = preg_replace("#^(.+)$delimiterEsc$#", "$1", $cats);
            $cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
            $cats = str_replace('</a>', '</a>' . $link_after, $cats);
            if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
            $trail[] = $cats;
            if ($show_current == 1) $trail[] = $before . get_the_title() . $after;
        }

    }
    elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() )
    {
        $post_type = get_post_type_object(get_post_type());
        $trail[]   = $before . $post_type->labels->singular_name . $after;

    }
    elseif ( is_attachment() )
    {
        $parent = get_post($parent_id);
        $cat    = get_the_category($parent->ID);
        if(count($cat))
        {
            $cat     = $cat[0];
            $cats    = get_category_parents($cat, TRUE, $delimiter);
            $cats    = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
            $cats    = str_replace('</a>', '</a>' . $link_after, $cats);
            if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
            $trail[] = $cats;
        }
        //printf($link, get_permalink($parent), $parent->post_title);
        if ($show_current == 1) $trail[] = $before . get_the_title() . $after;
    }
    elseif ( is_page() && !$parent_id )
    {
        if ($show_current == 1) $trail[] = $before . get_the_title() . $after;

    }
    elseif ( is_page() && $parent_id )
    {
        if ($parent_id != $frontpage_id) {
            $breadcrumbs = array();
            while ($parent_id) {
                $page = get_post($parent_id);
                if ($parent_id != $frontpage_id) {
                    $breadcrumbs[] = sprintf($link, get_permalink($page->ID), get_the_title($page->ID));
                }
                $parent_id = $page->post_parent;
            }
            $breadcrumbs = array_reverse($breadcrumbs);
            $trail[]     = implode($delimiter, $breadcrumbs);
        }

        if ($show_current == 1)  $trail[]     = $before . get_the_title() . $after;


    }
    elseif ( is_tag() )
    {
        $trail[] = $before . sprintf($text['tag'], single_tag_title('', false)) . $after;

    }
    elseif ( is_author() )
    {
        global $author;
        $userdata = get_userdata($author);
        $trail[]  = $before . sprintf($text['author'], $userdata->display_name) . $after;

    }
    elseif ( is_404() )
    {
        $trail[]  = $before . $text['404'] . $after;
    }

    if ( get_query_var('paged') )
    {
        $tmp = '<span class="page">';

        if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) $tmp .= ' (';
        $tmp .= __('Page', TEXTDOMAIN) . ' ' . get_query_var('paged');
        if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) $tmp .= ')';

        $tmp .= '</span>';

        if(count($trail))
            $trail[count($trail)-1]  .= $tmp;
    }

}

$trail = apply_filters('px_breadcrumb_trail_array', $trail);

echo '<div class="breadcrumbs">' . implode($delimiter, $trail) . '</div><!-- .breadcrumbs -->';