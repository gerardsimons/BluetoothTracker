<?php

require_once(THEME_LIB . '/forms/fieldtemplate.php');
require_once(THEME_LIB . '/forms/theme-options-provider.php');

class AdminForm extends FieldTemplate
{
    protected $template = array();

    function __construct()
    {
        $this->template = admin_get_form_settings();
        parent::__construct(new ThemeOptionsProvider(), THEME_LIB . '/forms/templates');
    }

    public function GetHeader()
    {
        return $this->GetTemplate('admin-header');
    }

    public function GetBody()
    {
        return $this->GetTemplate('admin-sidebar') .
               $this->GetTemplate('admin-panels');
    }

    public function GetImage($filename, $alt='', $class='')
    {
        return $this->GetTemplate('image', array('filename'=>$filename, 'alt'=>$alt, 'class'=>$class));
    }
}