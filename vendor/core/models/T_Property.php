<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_Property {
    // byid - bool - true : return indexed array by id
    // filter - function returning bool 
    //          or string 'toset' / 'tostring'
    //
    public function getProperties($byid = FALSE, $filter = '') 
    {
        
        $objs = array();
        if (is_callable($filter)) {
            $f = $filter;
        } else {
            if (strtolower($filter) == 'toset') {
                $f = function($item) {
                    return $item['ranktoset'] > 0;
                };
            } elseif (strtolower($filter) == 'tostring') {
                $f = function($item) {
                    return $item['ranktostring'] > 0;
                };
            } else {
                $f = NULL;
            }
        }
        $plist = $this->getplist();
        //die(var_dump($plist));
        //die(var_dump($this->properties));
        $key = -1;    
        foreach($this->properties as $prop) 
        {
            $rid = $prop['id'];
            if (($rid !== 'id')&&($f !== NULL)&&(!$f($prop))) {
                continue;
            }
            if ($byid) {    
                $key = $rid;
            } else {
                $key++;
            }    
            $objs[$key] = array();
            foreach ($plist as $pkey => $prow) {    
                $objs[$key][$pkey] = $prop[$pkey];
            }
            $objs[$key]['class'] = 'active';
            if ($key === 'id') {
                $objs[$key]['class'] = 'hidden';
            }
        }
        //die(var_dump($objs)."  byid = ".$byid);
        return $objs;
    }
}
