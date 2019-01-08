<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Exception;

class CollectionSet extends Sheet implements I_Sheet, I_Set, I_Property
{
    use T_Sheet;
    use T_Set;
    use T_Collection;
    
    public function txtsql_forDetails() 
    {
        return "SELECT mdt.id, mdt.name, mdt.synonym, "
                    . "NULL as mdid, '' as mdname, '' as mdsynonym, "
                    . "mdi.name as mdtypename, "
                    . "mdt.mditem, mdi.synonym as mdtypedescription "
                    . "FROM \"MDTable\" AS mdt "
                        . "INNER JOIN \"CTable\" AS mdi "
                        . "ON mdt.mditem=mdi.id "
                    . "WHERE mdt.id= :id";
    }        
    public function head() 
    {
        return new MdentitySet($this->mditem);
    }
    public function item($id) 
    {
        return new CollectionItem($id,$this);
    }
    public function getprop_classname()
    {
        return 'CProperty';
    }
    public function item_classname()
    {
        return 'CollectionItem';
    }        
    public function load_data($context,$data='')
    {
        return NULL;
    }    
    public function create_object($name,$synonym='')
    {
        return NULL;
    }        
    public function createtemptable_all($entities)
    {
        $str_entities = "('".implode("','", $entities)."')";
	$artemptable = array();
        $sql = DataManager::get_select_collections($str_entities);
        $artemptable[] = DataManager::createtemptable($sql,'tt_et');   
        
        return $artemptable;
    }
//      $filter: array 
//      id = property id (CProperties)
//      val = filter value
//      val_min = min filter value (optional)    
//      val_max = max filter value (optional)    
    public function findCollByProp($filter) 
    {
        
        $ftype='';
        $dbtable = '';
        $propid = '';
        $strwhere = '';
        $col_filter = array();
        if (count($filter)>0) {
            $propid = $filter['dcs_param_id']['id'];
            if ($propid != '') {
                $arprop = $this->properties[$propid];
                $ftype = $arprop['name_type'];
                if ($ftype == 'text') {
                    return array();
                }
                $dbtable = "CPropValue_$ftype";
                $col_filter[$propid] = new Filter($this->properties[$propid],$filter['dcs_param_val']['id']);
            }
        }    
        $params = array();
        if ($col_filter) {
            if (count($col_filter) > 0) {
                foreach ($col_filter as $prop => $flt) {
                    $ptype = $this->properties[$prop]['name_type'];
                    $sw = DataManager::getstrwhere($flt,$ptype,'pv.value',$params,'pv','pid');
                    if ($sw !== '') {
                        $strwhere .= "AND $sw"; 
                    }    
                }
                $strwhere = substr($strwhere, strlen("AND"));
            }
        }    
        
        if ($strwhere != '') {
            $sql = "SELECT DISTINCT pv.id as cid FROM \"$dbtable\" as pv WHERE $strwhere"; 
        } else {
            $sql = "SELECT et.id as cid FROM \"CTable\" as et WHERE et.mdid=:mdid LIMIT ".DCS_COUNT_REC_BY_PAGE; 
            $params = array('mdid'=>$this->id);
        }    
        $res = DataManager::dm_query($sql,$params);
        $objs = array();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            if (!in_array($row['cid'],$objs))
            {
                $objs[] = $row['cid'];
            }
        }
        return $objs;
    }    
    public function getItems($context) 
    {
        $prefix = $context['PREFIX'];
        $action = $context['ACTION'];
        $limit = $context['LIMIT'];
        $page = $context['PAGE'];
        $filter = $context['DATA'];
        $mdid = $this->id;
        if (!isset($this->properties)) {
            $this->properties = $this->loadProperties();
        }
        $plist = $this->properties;
    	$objs = array();
        $filter_id = '';
        $filter_val =  '';
        if ($this->name == 'user_settings') {
            if (!User::isAdmin())
            {
                //это уид реквизита user в таблице user_settings
                $filter['dcs_param_id'] = array('id' => '94f6b075-1536-4d16-a548-bc8128791127','name'=>'');
                $filter['dcs_param_val'] = array('id' => $_SESSION['user_id'],'name' => User::getUserName($_SESSION['user_id']));
            }    
        } else {
            if (count($filter) > 0) {
                $filter_id = $filter['dcs_param_id']['id'];
                $filter_val = $filter['dcs_param_val']['id'];
            }
        }   
        $entities = $this->findCollByProp($filter);
        if (!count($entities))
        {
            return $objs;
        }
	$offset=(int)($page-1)*$limit;
	$artemptable = $this->createtemptable_all($entities);
        $str0_req='SELECT et.id, et.name, et.synonym';
        $str_req='';
        $str_p = '';
        $filtername='';
        $filtertype='';
        $params = array();
        foreach($plist as $row) 
        {
            if (!$row['field']) {
                continue;
            }
            $rid = $row['id'];
            $rowname = $this->rowname($row);
            $rowtype = $row['name_type'];
            
            if ($rowtype=='cid')
            {
                $str0_req .= ", '$rid' as pid_$rowname, pv_$rowname.value as id_$rowname, ct_$rowname.synonym as name_$rowname";
                $str_req .=" LEFT JOIN \"CPropValue_cid\" as pv_$rowname INNER JOIN \"CTable\" as ct_$rowname ON pv_$rowname.value = ct_$rowname.id ON et.id = pv_$rowname.id AND pv_$rowname.pid=:$rowname";
            }   
            elseif ($rowtype=='id')
            {
                $str0_req .= ", '$rid' as pid_$rowname, pv_$rowname.value as id_$rowname, ct_$rowname.name as name_$rowname";
                $str_req .=" LEFT JOIN \"CPropValue_id\" as pv_$rowname INNER JOIN \"ETable\" as ct_$rowname ON pv_$rowname.value = ct_$rowname.id ON et.id = pv_$rowname.id AND pv_$rowname.pid=:$rowname";
            }   
            elseif ($rowtype=='mdid')
            {
                $str0_req .= ", '$rid' as pid_$rowname, pv_$rowname.value as id_$rowname, ct_$rowname.synonym as name_$rowname";
                $str_req .=" LEFT JOIN \"CPropValue_mdid\" as pv_$rowname INNER JOIN \"MDTable\" as ct_$rowname ON pv_$rowname.value = ct_$rowname.id ON et.id = pv_$rowname.id AND pv_$rowname.pid=:$rowname";
            }   
            else
            {
                $str0_req .= ", '$rid' as pid_$rowname, '' as id_$rowname, pv_$rowname.value as name_$rowname";
                $str_req .=" LEFT JOIN \"CPropValue_$rowtype\" as pv_$rowname ON et.id = pv_$rowname.id AND pv_$rowname.pid=:$rowname";
            }    
            if ($filter_id != '')
            {
                if ($rid == $filter_id)
                {
                    $filtername = "pv_$rowname.value";
                    $filtertype = $rowtype;
                }    
            }
            $params[$rowname]=$rid;
            
        }
        $strwhere='';
        if ($filtername!='')
        {
            $col_filter = new Filter($this->properties[$filter['dcs_param_id']['id']],$filter['dcs_param_val']['id']);
            $strwhere = DataManager::getstrwhere($col_filter,$filtertype,$filtername,$params);
        }
        $str0_req .=" FROM tt_et as et";
        $sql = $str0_req.$str_req;
        $dop = DataManager::get_col_access_text('et');
        if ($strwhere != '') {
            $sql .= " WHERE $strwhere";
            if ($dop!='') {
                $sql .= " AND ".$dop;
                $params['userid'] = $_SESSION['user_id'];
            }    
        } else {
            if ($dop != '') {
                $sql .= " WHERE ".$dop;
                $params['userid'] = $_SESSION['user_id'];
            }    
        }    

	$res = DataManager::dm_query($sql,$params);
        while($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $objs[$row['id']]=array();
            $objs[$row['id']]['id']=array('name'=>$row['id'],'id'=>'');
            $objs[$row['id']]['name']=array('name'=>$row['name'],'id'=>'');
            $objs[$row['id']]['synonym']=array('name'=>$row['synonym'],'id'=>'');
            foreach($plist as $row_plist) 
            {
                if (!$row_plist['field']) {
                    continue;
                }
                $rid = $row_plist['id'];    
                $field_val = $this->rowname($row_plist);
                $field_id = "pid_$field_val";
                $objs[$row['id']][$row[$field_id]] = array('id'=>$row['id_'.$field_val],'name'=>$row['name_'.$field_val]);
                if ($row_plist['name_type'] == 'date')
                {
                    $objs[$row['id']][$row[$field_id]] = array('id'=>'','name'=>substr($row['name_'.$field_val],0,10));
                }    
            }
        }
	DataManager::droptemptable($artemptable);
	return $objs;
    }
    public function getItemsByName($name)
    {
        $mdid = $this->id;
        $sql = "select ct.id, ct.name, ct.synonym FROM \"CTable\" as ct
		WHERE ct.mdid=:mdid AND (ct.name ILIKE :name OR ct.synonym ILIKE :name) LIMIT 5";  
	$res = DataManager::dm_query($sql,array('mdid'=>$mdid, 'name'=>"%$name%"));
	$rows = $res->fetchAll(PDO::FETCH_ASSOC);
        if (!count($rows))
        {
            $sql = "select ct.id, ct.name, ct.synonym FROM \"CTable\" as ct inner join \"CTable\" as md on ct.mdid=md.mdid
                    WHERE md.id=:mdid AND (ct.name ILIKE :name OR ct.synonym ILIKE :name) LIMIT 5";  
            $res = DataManager::dm_query($sql,array('mdid'=>$mdid, 'name'=>"%$name%"));
            $rows = $res->fetchAll(PDO::FETCH_ASSOC);
        }  
        return $rows;
    }
    public function getplist($context) 
    {
        return array();
    }        
    public function loadProperties()
    {
        return $this->getCProperties();
    }        
}

