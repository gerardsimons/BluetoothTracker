<?php

class Custom_Nav_Walker extends Walker_Nav_Menu
{
	private $navIdPrefix = '';

	public function __construct($idPrefix='menu-item-')
	{
		$this->navIdPrefix = $idPrefix;
	}

	function start_el(&$output, $object, $depth = 0, $args = array(), $current_object_id = 0)
	{
        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        $classes = empty( $object->classes ) ? array() : (array) $object->classes;

        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $object ) );
        $class_names = ' class="'. esc_attr( $class_names ) . '"';

        $output .= $indent . '<li id="'. $this->navIdPrefix . $object->ID . '"' . $class_names .'>';

        $attributes  = ! empty( $object->attr_title ) ? ' title="'  . esc_attr( $object->attr_title ) .'"' : '';
        $attributes .= ! empty( $object->target )     ? ' target="' . esc_attr( $object->target     ) .'"' : '';
        $attributes .= ! empty( $object->xfn )        ? ' rel="'    . esc_attr( $object->xfn        ) .'"' : '';
        $attributes .= ! empty( $object->url )        ? ' href="'   . esc_attr( $object->url        ) .'"' : '';
        $description = ! empty( $object->description ) ? '<span>'.esc_attr( $object->description ).'</span>' : '';


        if($depth != 0)
        {
            $description = $prepend = '';
        }

        //If navigation location is empty $args will be an array
        if(is_array($args))
        {
            //Quick fix on getting a url for link element
            $attributes .= ! empty( $object->guid )  ? ' href="' . esc_attr( $object->guid ) .'"' : '';

            $item_output  = $args['before'];
            $item_output .= '<a'. $attributes .'>';
            $item_output .= $args['link_before'] . apply_filters( 'the_title', $object->post_title, $object->ID );
            $item_output .= $description . $args['link_after'];
            $item_output .= '</a>';
            $item_output .= $args['after'];
        }
        elseif (is_object($args))
        {
            $item_output  = $args->before;
            $item_output .= '<a'. $attributes .'>';
            $item_output .= $args->link_before . apply_filters( 'the_title', $object->title, $object->ID );
            $item_output .= $description.$args->link_after;
            $item_output .= '</a>';
            $item_output .= $args->after;
        }


		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $object, $depth, $args );
	}
}