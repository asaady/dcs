<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Exception;

use Dcs\Vendor\Core\Models\Entity;

trait T_Head {
    function get_data($context) 
    {
        if  ($this->getmdtypename() == 'Items') {
            if ((array_key_exists('docid',$context['DATA']) !== FALSE)&&
                ($context['DATA']['docid']['id'] !== '')&&
                (array_key_exists('propid',$context['DATA']) !== FALSE)&&
                ($context['DATA']['propid']['id'] !== '')) {
                $prop = new EProperty($context['DATA']['propid']['id']);
                //die(var_dump($prop));
                $this->set_head($prop);
                $doc = new Entity($context['DATA']['docid']['id']);
                $prop->set_head($doc);
            }    
        }
        $objs = array(
          'id'=>$this->id,
          'name'=>$this->name,
          'synonym'=>$this->synonym,
          'version'=>$this->version
          );
        $this->prop_to_Data($context, $objs);
        return $objs;
    }
    public function get_navid() 
    {
        return $this->id;
    }
    function create($data) 
    {
        $entity = $this->item();
        $entity->set_data($data);
        return $entity->save_new();
    }
    public function search_by_name($name)
    {
        return $this->getItemsByName($name);
    }        
    public function add_navlist(&$navlist) 
    {
        if ($this->head) {
            $phead = $this->head;
            $phead->add_navlist($navlist); 
            $navlist[$this->head->getid()] = sprintf("%s",$this->head);
        }
    }
    public function get_property($propid)
    {
        if (array_key_exists($propid, $this->properties) === FALSE) {
            return NULL;
        }
        return $this->properties[$propid];
    }        

    // byid - bool - true : return indexed array by id
    // filter - function returning bool 
    //          or string 'toset' / 'tostring'
    //
    public function load_data()
    {
        $artemptable = $this->get_tt_sql_data();
        $sql = "select * from tt_out";
        $sth = DataManager::dm_query($sql);        
        $this->data = array();
        $arr_e = array();
        while($row = $sth->fetch(PDO::FETCH_ASSOC)) 
        {
            $this->data['id'] = array('id'=>'','name'=>$row['id']);
            foreach($this->properties as $prow)
            {
                $rowname = $this->rowname($prow['id']);
                if (array_key_exists('id_'.$rowname, $row)) {
                    $this->data[$prow['id']] = array('id'=>$row["id_$rowname"],'name'=>$row["name_$rowname"]);
                    if ($prow['type'] === 'id') {
                        if (($row["id_$rowname"])&&($row["id_$rowname"] != DCS_EMPTY_ENTITY)) {
                            if (!in_array($row["id_$rowname"],$arr_e)){
                                $arr_e[]=$row["id_$rowname"];
                            }
                        }    
                    }
                } elseif (array_key_exists($rowname, $row)) {
                    $this->data[$prow['id']] = array('id'=>'','name'=>$row[$rowname]);
                } else {
                    $this->data[$prow['id']] = array('id'=>'','name'=>'');
                }
            }    
        }
        if (count($arr_e)) {
            $this->fill_entname($this->data,$arr_e);
        }
        $this->version = time();
        DataManager::droptemptable($artemptable);
    }
    public function fill_entname(&$data,$arr_e) {
        $arr_entities = $this->getAllEntitiesToStr($arr_e);
        foreach($arr_entities as $rid=>$prow)
        {
            foreach($data as $id=>$row) 
            {
                if ($row['id'] == $rid) {
                    $data[$id]['name'] = $prow['name'];
                }        
            }
        }    
    }
    public function fill_entsetname(&$data,$arr_e) {
        $arr_entities = $this->getAllEntitiesToStr($arr_e);
        foreach($arr_entities as $rid=>$prow) {
            foreach($data as $id=>$row) {
                foreach($row as $pid=>$pdata) {
                    if (!is_array($pdata)) {
                        continue;
                    }
                    if ($pdata['id'] == $rid) {
                        $data[$id][$pid]['name'] = $prow['name'];
                    }        
                }    
            }
        }
    }
    public function getattr($propid) 
    {
        $val='';
	if(array_key_exists($propid, $this->data))
        {
	  $val=$this->data[$propid]['name'];
	}  
	return $val;
    }
    function getattrid($propid)
    {
        $val='';
	if(array_key_exists($propid, $this->data))
        {
	  $val=$this->data[$propid]['id'];
	}  
	return $val;
    }
    public function setattr($propid,$valname,$valid='') 
    {
        $val='';
	if(array_key_exists($propid, $this->data))
        {
	  $this->data[$propid]['name'] = $valname;
          $this->data[$propid]['id'] = $valid;
	}  
        return $this;
    }
    public function getDetails($id) 
    {
        $objs = array('id'=>'','mdid'=>'','mditem'=>'');
        $classname = get_called_class();
        if (strpos( $classname,'MdentitySet') !== FALSE) {
            $sql = "SELECT ct.id, ct.name, ct.synonym, 
                    NULL as mdid, '' as mdname, '' as mdsynonym,
                    NULL as mditem, '' as mdtypename, '' as mdtypedescription
                    FROM \"CTable\" as ct 
                    LEFT JOIN \"MDTable\" as md
                    ON ct.mdid=md.id AND md.name='MDitems' WHERE ct.id=:id";
       } elseif (strpos($classname,'Mdentity') !== FALSE) {
            $sql = "SELECT mdt.id, mdt.name, mdt.synonym, mdt.mditem, "
                    . "NULL as mdid, mdi.name as mdtypename, "
                    . "mdi.synonym as mdtypedescription "
                    . "FROM \"MDTable\" AS mdt "
                        . "INNER JOIN \"CTable\" AS mdi "
                        . "ON mdt.mditem=mdi.id "
                    . "WHERE mdt.id= :id";
        } elseif (strpos($classname,'Set') !== FALSE) {
            $sql = "SELECT mdt.id, mdt.name, mdt.synonym, "
                    . "NULL as mdid, mdi.name as mdtypename, "
                    . "mdt.mditem, mdi.synonym as mdtypedescription "
                    . "FROM \"MDTable\" AS mdt "
                        . "INNER JOIN \"CTable\" AS mdi "
                        . "ON mdt.mditem=mdi.id "
                    . "WHERE mdt.id= :id";
        } elseif (strpos($classname,'Entity') !== FALSE) {
            $sql = "select et.id, '' as name, '' as synonym, 
                    et.mdid , md.name as mdname, md.synonym as mdsynonym, 
                    md.mditem, tp.name as mdtypename, tp.synonym as mdtypedescription 
                    FROM \"ETable\" as et
                        INNER JOIN \"MDTable\" as md
                            INNER JOIN \"CTable\" as tp
                            ON md.mditem = tp.id
                        ON et.mdid = md.id 
                    WHERE et.id = :id";  
        } elseif (strpos($classname,'CollectionItem') !== FALSE) {
            $sql = "SELECT ct.id, ct.mdid, ct.name, ct.synonym, "
                    . "mc.name as mdname, mc.synonym as mdsynonym, mc.mditem, "
                    . "tp.name as mdtypename, tp.synonym as mdtypedescription "
                    . "FROM \"CTable\" as ct "
                        . "INNER JOIN \"MDTable\" as mc "
                            . "INNER JOIN \"CTable\" as tp "
                            . "ON mc.mditem = tp.id "
                        . "ON ct.mdid = mc.id "
                    . "WHERE ct.id=:id";
        } elseif (strpos($classname,'CollectionItem') !== FALSE) {
            $sql = "SELECT ct.id, ct.mdid, ct.name, ct.synonym, "
                    . "mc.name as mdname, mc.synonym as mdsynonym, mc.mditem, "
                    . "tp.name as mdtypename, tp.synonym as mdtypedescription "
                    . "FROM \"CTable\" as ct "
                        . "INNER JOIN \"MDTable\" as mc "
                            . "INNER JOIN \"CTable\" as tp "
                            . "ON mc.mditem = tp.id "
                        . "ON ct.mdid = mc.id "
                    . "WHERE ct.id=:id";
        } elseif (strpos($classname,'EProperty') !== FALSE) {
            $sql = "SELECT mp.id, mp.mdid, mp.name, mp.synonym, "
                    . "mc.name as mdname, mc.synonym as mdsynonym, mc.mditem, "
                    . "tp.name as mdtypename, tp.synonym as mdtypedescription "
                    . "FROM \"MDProperties\" as mp "
                        . "INNER JOIN \"MDTable\" as mc "
                            . "INNER JOIN \"CTable\" as tp "
                            . "ON mc.mditem = tp.id "
                        . "ON mp.mdid = mc.id "
                    . "WHERE mp.id=:id";
        } elseif (strpos($classname,'CProperty') !== FALSE) {
            $sql = "SELECT mp.id, mp.mdid, mp.name, mp.synonym, "
                    . "mc.name as mdname, mc.synonym as mdsynonym, mc.mditem, "
                    . "tp.name as mdtypename, tp.synonym as mdtypedescription "
                    . "FROM \"CProperties\" as mp "
                        . "INNER JOIN \"MDTable\" as mc "
                            . "INNER JOIN \"CTable\" as tp "
                            . "ON mc.mditem = tp.id "
                        . "ON mp.mdid = mc.id "
                    . "WHERE mp.id=:id";
        } else {
            return $objs;
        }
        $sth = DataManager::dm_query($sql,array('id'=>$id));   
        $res = $sth->fetch(PDO::FETCH_ASSOC);
	if($res) {
            $objs = $res;
	}
        return $objs;
    }
    public function get_classname() 
    {
        $s_class = explode('\\',get_called_class());
        return end($s_class);
    }
    public function get_head_class() 
    {
        $s_class = explode('\\', get_called_class());
        $classname = array_pop($s_class);
        switch ($classname) {
            case 'MdentitySet': $head = NULL; break;
            case 'Mdentity': $head = 'MdentitySet'; break;
            case 'Mdcollection': $head = 'MdentitySet'; break;
            case 'Mdregister': $head = 'MdentitySet'; break;
            case 'EProperty': $head = 'Mdentity'; break;
            case 'CProperty': $head = 'Mdcollection'; break;
            case 'RProperty': $head = 'Mdregister'; break;
            case 'Entity':  $head = 'EntitySet'; break;
            case 'EntitySet': $head = 'MdentitySet'; break;
            case 'CollectionSet': $head = 'MdentitySet'; break;
            case 'CollectionItem': $head = 'CollectionSet'; break;
            case 'RegisterSet': $head = 'MdentitySet'; break;
            default: $head = NULL;  break;
        }
        $s_class[] = $head;
        return implode('\\', $s_class);
    }
    public function create_head($id) 
    {
        $classname = $this->get_head_class();
        if (class_exists($classname)) {
            return new $classname($id);
        }
        return NULL;
    }
    public function update($data)     
    {
        $res = $this->update_properties($data);
        if ($res['status']=='OK')
        {
            $res1 = $this->update_dependent_properties($res['objs']);
            if (is_array($res1['objs'])) {
                $res['objs'] += $res1['objs'];
            }
        }    
        return $res;
    }
    public function prop_to_Data($context,&$objs)
    {        
        $plist = array();
        $sets = array();
        $pset = array();
        $classname = $this->get_classname();
        $prefix = $context['PREFIX'];
        if ($prefix == 'CONFIG') {
            if (strpos($classname,'Property') === FALSE) {
                $pset = $this->getProperties(TRUE,'toset');
            } else {
                $plist = $this->getProperties(FALSE);
            }
        } else {
            if (strpos($classname,'Set') === FALSE) {
                $plist = $this->getProperties(FALSE);
                foreach ($this->properties as $prop) {
                    if ($prop['valmdtypename'] !== 'Sets') {
                        continue;
                    }
                    if (!$this->getattrid($prop['id'])) {
                        $set = new Mdentity($prop['valmdid']);
                    } else {
                        $set = new Entity($this->getattrid($prop['id']));
                    }    
                    $sets[$prop['id']] = $set->getProperties(true,'toset');
                }  
            } else {
                $pset = $this->getProperties(TRUE,'toset');
            }
        }
        $objs['PLIST'] = $plist;
        $objs['PSET'] = $pset;
        $objs['SETS'] = $sets;
    } 
    public function get_navlist($context)
    {
        $navlist = array();
        $this->add_navlist($navlist);
        if ($this->id) {
            if (($context['PREFIX'] !== 'CONFIG')&&
                ($context['CLASSNAME'] === 'Entity')&&
                ($context['CURID'] !== '')) {
                    //die(var_dump($this->id));
                    $prop = $this->head->get_property($context['CURID']);
                    $navlist["$context[ITEMID]/$context[CURID]"] = $prop['synonym'];
            } else {
                $navlist[$this->id] = sprintf("%s",$this);
            }
        } else {
            $navlist['new'] = 'Новый';
        }    
        return $navlist;
    }        
}
