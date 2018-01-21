<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_Item 
{
    public function item() 
    {
        return NULL;
    }
    public function getItemsByFilter($context, $filter)
    {
        $prefix = $context['PREFIX'];
        $action = $context['ACTION'];
        $objs = array();
        $this->prop_to_Data($objs);
        if ($this->data) {
            $objs['SDATA'] = array();
            $objs['SDATA'][$this->id] = $this->data;
        }    
        $objs['actionlist']= DataManager::getActionsbyItem($context['CLASSNAME'],$prefix,$action);
        $objs['navlist'] = $this->get_navlist($context);
        return $objs;
    }
    public function getItemsByName($name) 
    {
        return NULL;
    }
}

