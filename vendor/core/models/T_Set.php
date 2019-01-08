<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_Set {
    public function getItemsByName($name)
    {
        return NULL;
    }
    public function getNameFromData($context,$data='')
    {
        if (!$data) {
            return array('name' => $this->name, 'synonym' => $this->synonym);
        } else {
            return array('name' => $data['name']['name'],
                         'synonym' => $data['synonym']['name']);
        }    
    }        
    public function getItemsProp($context) 
    {
        return $this->getProperties(TRUE,'toset');
    }

}
