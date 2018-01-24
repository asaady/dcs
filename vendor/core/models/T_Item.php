<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_Item 
{
    public function item() 
    {
        return NULL;
    }
    public function getItems($context) 
    {
        return array();
    }
    public function getItemsByName($name) 
    {
        return NULL;
    }
}

