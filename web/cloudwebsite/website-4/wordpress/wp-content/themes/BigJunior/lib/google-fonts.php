<?php

class GoogleFonts {

    protected $jsonObject = null;

    public function __construct($jsonFile)
    {
        $content = file_get_contents($jsonFile);

        if(FALSE === $content)
        {
            //Prevent access errors
            $jsonObject = new stdClass();
            $jsonObject->items = array();
            return;
        }

        $this->jsonObject = json_decode($content);
    }

    public function GetJson()
    {
        return $this->jsonObject;
    }

    public function GetFontNames()
    {
        $names = array();
        foreach($this->jsonObject->items as $font)
        {
            //$key = preg_replace('/\s/', '+', $font->family);
            $key = $font->family;
            $names[$key] = $font->family;
        }

        return $names;
    }

    public function GetFontByName($name)
    {
        foreach($this->jsonObject->items as $font)
        {
            if($font->family == $name)
                return $font;
        }

        return null;
    }
}