<?php

namespace nickolaus\MultipleIconFontsBundle\Component;

class IconPrefix
{
    const GLYPHICON = 'glyphicon';
    const FONTAWESOME = 'fa';
    const IONICONS = 'ion';
    const MATERIAL = 'mdi';

    public static function all(){
        $reflClass = new \ReflectionClass(__CLASS__);
        return $reflClass->getConstants();
    }
}