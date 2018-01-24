<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_Mdproperty {
    public function loadProperties() 
    {
        return $this->getplist();
    }
    public function get_tt_sql_data() 
    {
        $artemptable = array();
        $sql = $this->txtsql_getproperty();
        $artemptable[] = DataManager::createtemptable($sql,'tt_out',array('id'=>$this->id));   
        return $artemptable;
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
}
