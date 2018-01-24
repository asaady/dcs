<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_Set {
//    public function getItemsByFilter($context) 
//    {
//        $prefix = $context['PREFIX'];
//        $action = $context['ACTION'];
//    	$objs = array();
//        $objs['actionlist'] = DataManager::getActionsbyItem('EntitySet',$prefix,$action);
//        $objs['navlist'] = $this->get_navlist($context);
//        $objs['PSET'] = $this->getProperties(true,'toset');
//        $objs['LDATA'] = $this->getItems($context);
//	return $objs;
//    }
    public function getItemsByName($name)
    {
        return NULL;
    }
}
