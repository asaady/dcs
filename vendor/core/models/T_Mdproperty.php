<?php
namespace Dcs\Vendor\Core\Models;

trait T_Mdproperty {
    public function loadProperties() {
        return $this->getplist();
    }
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
            } elseif (strtolower($filter) == 'sets') {
                $f = function($item) {
                    return $item['valmdtypename'] === 'Sets';
                };
            } else {
                $f = NULL;
            }
        }
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
            $objs[$key] = $prop;
        }
        return $objs;
    }
    public function getItemsByFilter($context, $filter)
    {
        $prefix = $context['PREFIX'];
        $action = $context['ACTION'];
        $objs = array();
        $objs['actionlist']= DataManager::getActionsbyItem($context['CLASSNAME'],$prefix,$action);
        $objs['navlist'] = $this->get_navlist($context);
        $objs['PSET'] = $this->getProperties(TRUE,'toset');
        $objs['LDATA'] = array();
        foreach ($this->Properties() as $row) {
            $objs['LDATA'][$row['id']] = array();
            foreach ($objs['PSET'] as $pkey=>$prow) {
                $objs['LDATA'][$row['id']][$pkey]=array('name'=>$row[$prow['id']],'id'=>'');
            }    
        }
        return $objs;
    }
    public function getItemsByName($name) 
    {
        return NULL;
    }
}
