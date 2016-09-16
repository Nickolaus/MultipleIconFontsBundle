<?php

namespace nickolaus\MultipleIconFontsBundle\Component;

class IgnoredClasses {

    private static $ignoredClasses = array(
        "lg", "2x","3x", "4x", "5x", "fw", "ul",
        "li", "border",
        "pull-left", "pull-right",
        "spin", "pulse", "rotate-90", "rotate-180", "rotate-270", "flip-horizontal", "flip-vertical",
        "stack", "stack-1x", "stack-2x",
        "inverse"
    );

    public static function all(){
        return static::$ignoredClasses;
    }

}