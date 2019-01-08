<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_Property {
//    public function getProperties($byid = FALSE, $filter = '') 
//    {
//        
//        $objs = array();
//        if (is_callable($filter)) {
//            $f = $filter;
//        } else {
//            if (strtolower($filter) == 'toset') {
//                $f = function($item) {
//                    return $item['ranktoset'] > 0;
//                };
//            } elseif (strtolower($filter) == 'tostring') {
//                $f = function($item) {
//                    return $item['ranktostring'] > 0;
//                };
//            } elseif (strtolower($filter) == 'sets') {
//                $f = function($item) {
//                    return $item['valmdtypename'] === 'Sets';
//                };
//            } else {
//                $f = NULL;
//            }
//        }
//        $plist = $this->getplist();
//        $key = -1;   
//        foreach($this->properties as $prop) 
//        {
//            $rid = $prop['id'];
//            if (($rid !== 'id')&&($f !== NULL)&&(!$f($prop))) {
//                continue;
//            }
//            if ($byid) {    
//                $key = $rid;
//            } else {
//                $key++;
//            }    
//            $objs[$key] = array();
//            foreach ($plist as $pkey => $prow) {    
//                $objs[$key][$pkey] = $prop[$pkey];
//            }
//            $objs[$key]['class'] = 'active';
//            if ($key === 'id') {
//                $objs[$key]['class'] = 'hidden';
//            } elseif ($prop['name'] === 'activity') {
//                $objs[$key]['class'] = 'hidden';
//            }
//        }
//        return $objs;
//    }
    public function getProperty($propid) 
    {
        $sql = $this->txtsql_property("propid");
	$res = DataManager::dm_query($sql,array('propid'=>$propid));
        return $res->fetch(PDO::FETCH_ASSOC);
    }    
}
