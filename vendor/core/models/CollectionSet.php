<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Exception;
use Dcs\Vendor\Core\Models\Filter;

class CollectionSet extends Sheet implements I_Sheet, I_Set, I_Property
{
    use T_Sheet;
    use T_Collection;
    
    public function dbtablename()
    {
        return 'MDTable';
    }
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
    public function load_data($data='')
    {
        return NULL;
    }    
    public function create_object($name,$synonym='')
    {
        if (!$this->mdid) {
            throw new DcsException("Class ".get_called_class().
                " create_object: mdid is empty",DCS_ERROR_WRONG_PARAMETER);
        }
        if (!$this->id) {
            throw new DcsException("Class ".get_called_class().
                " create_object: id is empty",DCS_ERROR_WRONG_PARAMETER);
        }
        if (!$name) {
            throw new DcsException("Class ".get_called_class().
                " create_object: name is empty",DCS_ERROR_WRONG_PARAMETER);
        }
        if (!$synonym) {
            $synonym = $name;
        }
        $sql = "INSERT INTO \"MDTable\" (id, mditem, name, synonym) "
                    . "VALUES (:id, :mditem, :name, :synonym) RETURNING \"id\"";
        $params = array();
        $params['id']= $this->id;
        $params['mditem']= $this->mditem;
        $params['name']= $name;
        $params['synonym']= $synonym;
        $res = DataManager::dm_query($sql,$params); 
        $rowid = $res->fetch(PDO::FETCH_ASSOC);
        return $rowid['id'];
    }
//      $filter: array 
//      id = property id (CProperties)
//      val = filter value
//      val_min = min filter value (optional)    
//      val_max = max filter value (optional)    
    public function findCollByProp($filters) 
    {
        
        $ftype='';
        $dbtable = '';
        $propid = '';
        $strwhere = '';
        $col_filter = array();
        foreach($filters as $filter) {
            $propid = $filter->getprop();
            $ftype = $filter->gettype();
            if ($ftype == 'text') {
                return array();
            }
            $dbtable = "CPropValue_$ftype";
            $col_filter[$propid] = $filter;
            break;
        }    
        $params = array();
        if (count($col_filter) > 0) {
            $strwhere = Filter::getstrwhere($col_filter,'pv_','.value',$params);
        }
        
        if ($strwhere != '') {
            $rowname = Filter::rowname($propid);
            $sql = "SELECT DISTINCT pv.id as cid FROM \"$dbtable\" as pv_$rowname WHERE $strwhere"; 
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
//    public function getItems($filter=array()) 
//    {
//        $context = DcsContext::getcontext();
//        $prefix = $context->getattr('PREFIX');
//        $action = $context->getattr('ACTION');
//        $limit = $context->getattr('LIMIT');
//        $page = $context->getattr('PAGE');
//        $mdid = $this->id;
//        if (!isset($this->properties)) {
//            $this->properties = $this->loadProperties();
//        }
//        $plist = $this->properties;
//    	$objs = array();
//        if ($this->name == 'user_settings') {
//            if (!User::isAdmin())
//            {
//                //это уид реквизита user в таблице user_settings
//                $context->data_setattr('dcs_param_id',
//                        array('id' => '94f6b075-1536-4d16-a548-bc8128791127','name'=>''));
//                $context->data_setattr('dcs_param_val',
//                        array('id' => $_SESSION['user_id'],'name' => User::getUserName($_SESSION['user_id'])));
//            }    
//        }   
//        $entities = $this->findCollByProp($filter);
//        if (!count($entities))
//        {
//            return $objs;
//        }
//	$offset=(int)($page-1)*$limit;
//	$artemptable = $this->createtemptable_all($entities);
//        $str0_req='SELECT et.id, et.name, et.synonym';
//        $str_req='';
//        $str_p = '';
//        $filtername='';
//        $filtertype='';
//        $params = array();
//        foreach($plist as $row) 
//        {
//            if (!$row['field']) {
//                continue;
//            }
//            $rid = $row['id'];
//            $rowname = Filter::rowname($rid);
//            $rowtype = $row['name_type'];
//            
//            if ($rowtype=='cid')
//            {
//                $str0_req .= ", '$rid' as pid_$rowname, pv_$rowname.value as id_$rowname, ct_$rowname.synonym as name_$rowname";
//                $str_req .=" LEFT JOIN \"CPropValue_cid\" as pv_$rowname INNER JOIN \"CTable\" as ct_$rowname ON pv_$rowname.value = ct_$rowname.id ON et.id = pv_$rowname.id AND pv_$rowname.pid=:$rowname";
//            }   
//            elseif ($rowtype=='id')
//            {
//                $str0_req .= ", '$rid' as pid_$rowname, pv_$rowname.value as id_$rowname, ct_$rowname.name as name_$rowname";
//                $str_req .=" LEFT JOIN \"CPropValue_id\" as pv_$rowname INNER JOIN \"ETable\" as ct_$rowname ON pv_$rowname.value = ct_$rowname.id ON et.id = pv_$rowname.id AND pv_$rowname.pid=:$rowname";
//            }   
//            elseif ($rowtype=='mdid')
//            {
//                $str0_req .= ", '$rid' as pid_$rowname, pv_$rowname.value as id_$rowname, ct_$rowname.synonym as name_$rowname";
//                $str_req .=" LEFT JOIN \"CPropValue_mdid\" as pv_$rowname INNER JOIN \"MDTable\" as ct_$rowname ON pv_$rowname.value = ct_$rowname.id ON et.id = pv_$rowname.id AND pv_$rowname.pid=:$rowname";
//            }   
//            else
//            {
//                $str0_req .= ", '$rid' as pid_$rowname, '' as id_$rowname, pv_$rowname.value as name_$rowname";
//                $str_req .=" LEFT JOIN \"CPropValue_$rowtype\" as pv_$rowname ON et.id = pv_$rowname.id AND pv_$rowname.pid=:$rowname";
//            }    
//            $params[$rowname]=$rid;
//        }
//        $strwhere='';
//        if (count($filter))
//        {
//            $strwhere = Filter::getstrwhere($filter,'pv_','.value',$params);
//        }
//        $str0_req .=" FROM tt_et as et";
//        $sql = $str0_req.$str_req;
//        $dop = DataManager::get_col_access_text('et');
//        if ($strwhere != '') {
//            $sql .= " WHERE $strwhere";
//            if ($dop!='') {
//                $sql .= " AND ".$dop;
//                $params['userid'] = $_SESSION['user_id'];
//            }    
//        } else {
//            if ($dop != '') {
//                $sql .= " WHERE ".$dop;
//                $params['userid'] = $_SESSION['user_id'];
//            }    
//        }    
//
//	$res = DataManager::dm_query($sql,$params);
//        while($row = $res->fetch(PDO::FETCH_ASSOC)) {
//            $objs[$row['id']]=array();
//            $objs[$row['id']]['id']=array('name'=>$row['id'],'id'=>'');
//            $objs[$row['id']]['name']=array('name'=>$row['name'],'id'=>'');
//            $objs[$row['id']]['synonym']=array('name'=>$row['synonym'],'id'=>'');
//            foreach($plist as $row_plist) 
//            {
//                if (!$row_plist['field']) {
//                    continue;
//                }
//                $rid = $row_plist['id'];    
//                $field_val = $this->rowname($row_plist);
//                $field_id = "pid_$field_val";
//                $objs[$row['id']][$row[$field_id]] = array('id'=>$row['id_'.$field_val],'name'=>$row['name_'.$field_val]);
//                if ($row_plist['name_type'] == 'date')
//                {
//                    $objs[$row['id']][$row[$field_id]] = array('id'=>'','name'=>substr($row['name_'.$field_val],0,10));
//                }    
//            }
//        }
//	DataManager::droptemptable($artemptable);
//	return $objs;
//    }
    public function get_items($filter=array()) 
    {
        $context = DcsContext::getcontext();
        $prefix = $context->getattr('PREFIX');
        $action = $context->getattr('ACTION');
        $limit = $context->getattr('LIMIT');
        $page = $context->getattr('PAGE');
        $mdid = $this->id;
        if (!isset($this->properties)) {
            $this->loadProperties();
        }
    	$objs = array();
        if ($this->name == 'user_settings') {
            if (!User::isAdmin())
            {
                //это уид реквизита user в таблице user_settings
                $context->data_setattr('dcs_param_id',
                        array('id' => '94f6b075-1536-4d16-a548-bc8128791127','name'=>''));
                $context->data_setattr('dcs_param_val',
                        array('id' => $_SESSION['user_id'],'name' => User::getUserName($_SESSION['user_id'])));
            }    
        }   
        $entities = $this->findCollByProp($filter);
        if (!count($entities))
        {
            return NULL;
        }
	$offset=(int)($page-1)*$limit;
	$artemptable = DataManager::create_col_alltemptable($entities);
        $str0_req='SELECT et.id, et.name, et.synonym';
        $str_req='';
        $str_p = '';
        $filtername='';
        $filtertype='';
        $params = array();
        foreach($this->properties as $row) 
        {
            if (!$row['field']) {
                continue;
            }
            $rid = $row['id'];
            if (($rid == 'name')||($rid == 'synonym')) {
                continue;
            }
            $rowname = Filter::rowname($rid);
            $rowtype = $row['name_type'];
            
            if ($rowtype=='cid')
            {
                $str0_req .= ", '$rid' as propid_$rowname, pv_$rowname.value as id_$rowname, ct_$rowname.synonym as name_$rowname";
                $str_req .=" LEFT JOIN \"CPropValue_cid\" as pv_$rowname INNER JOIN \"CTable\" as ct_$rowname ON pv_$rowname.value = ct_$rowname.id ON et.id = pv_$rowname.id AND pv_$rowname.pid=:$rowname";
            }   
            elseif ($rowtype=='id')
            {
                $str0_req .= ", '$rid' as propid_$rowname, pv_$rowname.value as id_$rowname, ct_$rowname.name as name_$rowname";
                $str_req .=" LEFT JOIN \"CPropValue_id\" as pv_$rowname INNER JOIN \"ETable\" as ct_$rowname ON pv_$rowname.value = ct_$rowname.id ON et.id = pv_$rowname.id AND pv_$rowname.pid=:$rowname";
            }   
            elseif ($rowtype=='mdid')
            {
                $str0_req .= ", '$rid' as propid_$rowname, pv_$rowname.value as id_$rowname, ct_$rowname.synonym as name_$rowname";
                $str_req .=" LEFT JOIN \"CPropValue_mdid\" as pv_$rowname INNER JOIN \"MDTable\" as ct_$rowname ON pv_$rowname.value = ct_$rowname.id ON et.id = pv_$rowname.id AND pv_$rowname.pid=:$rowname";
            }   
            else
            {
                $str0_req .= ", '$rid' as propid_$rowname, '' as id_$rowname, pv_$rowname.value as name_$rowname";
                $str_req .=" LEFT JOIN \"CPropValue_$rowtype\" as pv_$rowname ON et.id = pv_$rowname.id AND pv_$rowname.pid=:$rowname";
            }    
            $params[$rowname]=$rid;
        }
        $strwhere='';
        if (count($filter))
        {
            $strwhere = Filter::getstrwhere($filter,'pv_','.value',$params);
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
	DataManager::droptemptable($artemptable);
	return $res;
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
    public function getplist() 
    {
        return array();
    }        
    public function loadProperties()
    {
        $sql = DataManager::get_select_cproperties("WHERE mp.mdid = :mdid");
        $res = DataManager::dm_query($sql,array('mdid'=>$this->id));
        $this->properties = array();
        $this->properties['name'] = array('id'=>'name','name'=>'name','synonym'=>'NAME',
                        'rank'=>1,'ranktoset'=>2,'ranktostring'=>1,
                        'id_type'=>'str', 'name_type'=>'str',
                        'id_valmdid'=>'', 'name_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1);
        $this->properties['synonym'] = array('id'=>'synonym','name'=>'synonym','synonym'=>'Синоним',
                        'rank'=>2,'ranktoset'=>3,'ranktostring'=>0,
                        'id_type'=>'str', 'name_type'=>'str',
                        'id_valmdid'=>'', 'name_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $this->properties[$row['id']] = $row;
        }
        return $this->properties;
    }        
}

