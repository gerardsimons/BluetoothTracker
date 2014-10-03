<?php

/* Returns post/page link and their parents */
function get_post_parent_trail($post_id)
{
    $parents = array();

    while ( $post_id ) {

        /* Get the post by ID. */
        $page = get_post( $post_id );

        if(null==$page)
            break;

        /* Add the formatted post link to the array of parents. */
        $parents[]  = '<a href="' . get_permalink( $post_id ) . '" title="' . esc_attr( get_the_title( $post_id ) ) . '">' . get_the_title( $post_id ) . '</a>';

        /* Set the parent post's parent to the post ID. */
        $post_id = $page->post_parent;
    }

    /* reverse the array to put them in the proper order for the trail */
    $parents = array_reverse( $parents );

    return $parents;
}


function px_modify_breadcrumb_single($postType, $trail, $delimiter)
{
    if('portfolio' != $postType)
        return $trail;

    $front_page_id = get_option( 'page_on_front' );

    //Check if parent page is specified
    if(isset($_GET['pnt']))
    {
        $parent  = intval($_GET['pnt']);
        //Don't show front page if its the parent
        if($parent != $front_page_id)
        {
            $parents = get_post_parent_trail($parent);
            $trail[] = implode($delimiter, $parents);
        }
    }
    //No parent is given
    else
    {
        //Find a page that contains portfolio shortcode
        $pages = SearchPagesByContent('[portfolio');
        $page  = null;

        //Get first page that is not the front page
        if(null!=$pages)
        {
            foreach($pages as $item)
            {
                if($item->ID != $front_page_id)
                {
                    $page = $item;
                    break;
                }
            }
        }

        if($page)
        {
            $parents = get_post_parent_trail($page->ID);
            //build the trail again (show home must be enabled)
            $newTrail   = $parents;

            if(count($trail))
                array_unshift($newTrail, $trail[0]);

            $trail = $newTrail;
        }
    }

    return $trail;
}

add_filter('px_breadcrumb_single_trail_filter', 'px_modify_breadcrumb_single', 10, 3);

function px_breadcrumb_single_trail_handler($postType)
{
    if('portfolio'==$postType)
        return true;

    return false;
}

add_filter('px_breadcrumb_single_trail_handler', 'px_breadcrumb_single_trail_handler');