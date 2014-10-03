<?php

require_once('ivalueprovider.php');

class PostOptionsProvider implements IValueProvider {

    public function GetValue($key)
    {
        global $post;
        return get_post_meta( $post->ID, $key, true );
    }
}