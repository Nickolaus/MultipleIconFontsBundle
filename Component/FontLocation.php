<?php

namespace nickolaus\MultipleIconFontsBundle\Component;

class FontLocation
{
    const GLYPHICON = 'twbs' . DIRECTORY_SEPARATOR . 'bootstrap';
    const FONTAWESOME = 'components' . DIRECTORY_SEPARATOR . 'font-awesome';
    const IONICONS = 'driftyco' . DIRECTORY_SEPARATOR . 'ionicons';
    const MATERIAL = 'mervick' . DIRECTORY_SEPARATOR . 'material-design-icons';

    public static function all(){
        $reflClass = new \ReflectionClass(__CLASS__);
        return $reflClass->getConstants();
    }
}