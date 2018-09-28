<?php

namespace Dcs\Vendor\Core\Views;

abstract class Template {
    protected $name;
    public function __construct($name = '')
    {
        if ($name == '') {
            $this->name = "Default"; 
        } else {
            $this->name = "$name"; 
        }
    }    
    public function getname()
    {
        return $this->name; 
    }    
    public function setname($name)
    {
        $this->name = $name; 
    }    
}