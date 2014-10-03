<?php
require THEME_LIB . '/forms/template.php';


class FieldTemplate extends Template {
    /* @var IValueProvider $valueProvider */
    private   $valueProvider = null;

    function __construct(IValueProvider $valueProvider, $templatesDir = '')
    {
        $this->valueProvider = $valueProvider;
        parent::__construct($templatesDir);
    }

    function GetValue($key)
    {
        return $this->valueProvider->GetValue($key);
    }

    public function GetField($key, array $settings, array $vars=null)
    {
        $params = array('key' => $key, 'settings' => $settings);

        if($vars != null)
            $params = array_merge($vars, $params);

        return $this->GetTemplate($settings['type'] . '-field', $params);
    }
}