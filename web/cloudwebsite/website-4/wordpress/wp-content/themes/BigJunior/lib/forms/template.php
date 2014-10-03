<?php

//Base class for generating html code from
//given template file
class Template
{
    protected $templatesDir  = 'templates';

    function __construct($templatesDir = '')
    {
        if($templatesDir != '')
            $this->templatesDir = $templatesDir;
    }

    function SetWorkingDirectory($dir)
    {
        $this->templatesDir = $dir;
    }

    function GetTemplate($file, $vars = array())
    {
        ob_start();
        require(path_combine($this->templatesDir, $file) . '.php');
        return ob_get_clean();
    }

}