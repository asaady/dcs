<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_MdSet {
//    public function getItemsByFilter($context, $filter) 
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
    public function getItems($context) 
    {
        $sql = $this->txtsql_getproperties();
        if ($sql === '')
        {
            return NULL;
        }    
        $params = array('mdid'=> $this->mdid);
        $res = DataManager::dm_query($sql,$params);
        $plist = $this->getProperties(TRUE,'toset');
        $objs = array();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $objs[$row['id']]= array();
            foreach($plist as $rid => $row_plist) {
                $r_name = $row[$rid];
                $objs[$row['id']][$rid ] = array('id'=>$rid,'name'=>$r_name,'class'=>$row_plist['class']);            
            }
        }
        return $objs;
    }
}
