<?php

require_once('ivalueprovider.php');

class ThemeOptionsProvider implements IValueProvider {
    public function GetValue($key)
    {
        return opt($key);
    }
}