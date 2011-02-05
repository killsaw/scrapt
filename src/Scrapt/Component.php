<?php

abstract class Component
{
    public static function getComponent($name)
    {
        if (class_exists($name)) {
            
        }
        $class_path = sprintf("%s/Component/%s.php", 
            __DIR__, $name);
        
        if (!file_exists($path)) {
            
        }
    }
}