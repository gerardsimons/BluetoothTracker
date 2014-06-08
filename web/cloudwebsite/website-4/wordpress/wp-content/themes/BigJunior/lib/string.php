<?php

function ends_with($string, $test) {
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, -$testlen) === 0;
}

function replace_last_occurrence($haystack, $needle, $replacement) {
    $last = strrpos($haystack, $needle);

    if( $last===FALSE )
        return $haystack;

    return substr_replace($haystack, $replacement, $last, strlen($needle));
}

function remove_last_occurrence($haystack, $needle)
{
    return replace_last_occurrence($haystack, $needle, '');
}

//Simple path combining function
function path_combine($path1, $path2)
{
    $dirSep = '/';//It should be DIRECTORY_SEPARATOR constant but doesn't work with URIs in WordPress
    $e1   = $path1{strlen($path1) - 1};
    $b2   = $path2{0};

    //Convert
    if($e1 === '\\')
        $e1 = $dirSep;

    if($b2 === '\\')
        $b2 = $dirSep;


    //Both paths has no separator chars
    if($e1 !== $dirSep && $b2 !== $dirSep)
    {
        $value = $path1 . $dirSep . $path2;
    }
    //One path has directory separator and the other doesn't
    elseif(($e1 === $dirSep && $b2 !== $dirSep) ||
           ($e1 !== $dirSep && $b2 === $dirSep)
    )
    {
        $value = $path1 . $path2;
    }
    //Else both path has directory separator
    else
    {
        $value = $path1 . substr($path2, 1);
    }

    $args  = func_get_args();

    if(count($args) < 3)
        return $value;

    $newArgs = array_merge(array($value), array_slice($args, 2));

    return call_user_func_array('path_combine', $newArgs);
}