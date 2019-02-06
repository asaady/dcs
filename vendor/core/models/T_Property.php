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
    public function update_properties($data,$n=0)   
    {
        $objs = array();
        $objs['status']='OK';
        $objs['objs']=array();
        $objs['id']=$this->id;
        $this->load_data();
        $sql = '';
        $params = array();
        foreach($this->plist as $row)
        {    
            if (!$row['field']) {
                continue;
            }
            $key = $row['name'];
            $id = $row['id'];
            if ($key=='id')
            {
                continue;
            }    
            if (!array_key_exists($id, $data))
            {
                continue;
            }    
            $dataname = $data[$id]['name'];
            $valname = $this->data[$id]['name'];
            $dataid = $data[$id]['id'];
            $valid = $this->data[$id]['id'];
            if (($row['name_type']=='id')||($row['name_type']=='cid')||($row['name_type']=='mdid')) 
            {
                if ($dataid!='')
                {
                    if ($dataid===$valid)
                    {
                        continue;
                    }    
                    $val = $dataid;
                }
                else 
                {
                    if ($valid!='')
                    {
                        $val = DCS_EMPTY_ENTITY;
                    }
                    else
                    {
                        continue;
                    }    
                }
            }    
            else
            {
                if (isset($dataname))
                {
                    if ($dataname===$valname)
                    {
                        continue;
                    }    
                    if (($dataname=='')&&($valname==''))
                    {
                        continue;
                    }    
                    $val = $dataname;
                }
                else
                {
                    continue;
                }    
            }    
            $sql .= ", $key=:$key";
            $params[$key] = $val;
        }    
        $sql = "UPDATE \"".$this->dbtablename()."\" SET ".substr($sql, 1)." WHERE id=:id";
        $params['id'] = $this->id;
        $res = DataManager::dm_query($sql,$params);
        return $objs;
    }        
    public function update_dependent_properties($data)
    {        
        return array();
    }        
    public function get_select_properties($strwhere)
    {
        return NULL;    
    }        
    public function txtsql_property($parname)
    {
        return NULL;    
    }        
    public function txtsql_properties($parname)
    {
        return NULL;
    }        
}
