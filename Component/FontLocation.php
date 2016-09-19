<?php

namespace nickolaus\MultipleIconFontsBundle\Component;

class FontLocation
{
    const GLYPHICON = 'twbs/bootstrap';
    const FONTAWESOME = 'components/font-awesome';
    const IONICONS = 'driftyco/ionicons';
    const MATERIAL = 'mervick/material-design-icons';

    public static function all() {
        $reflClass = new \ReflectionClass(__CLASS__);
        return $reflClass->getConstants();
    }
}