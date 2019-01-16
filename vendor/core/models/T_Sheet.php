<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Exception;

use Dcs\Vendor\Core\Models\Entity;
use Dcs\Vendor\Core\Models\Filter;

trait T_Sheet {
    function get_data() 
    {
        $this->check_right();
        $objs = array(
          'id'=>$this->id,
          'name'=>$this->name,
          'synonym'=>$this->synonym,
          'version'=>$this->version
          );
        $objs['PLIST'] = $this->getplist();
        $objs['SETS'] = $this->getsets();
        $objs['PSET'] = $this->getItemsProp();
       return $objs;
    }
    public function getsets()
    {
        return array();
    }        
    public function getcontext()
    {
        return array(
        'ITEMID' => '',
        'ACTION' => 'VIEW',
        'PREFIX' => 'ENTERPRISE'
        );
    }            
    public function setnamesynonym()
    {
    }        
    public function get_navid() 
    {
        return $this->id;
    }
    function getnewid($headid,$classname)
    {
	$res = DataManager::dm_beginTransaction();
        $sql = "INSERT INTO \"NewObjects\" (headid,classname) VALUES (:headid,:classname) RETURNING \"id\"";
        $res=DataManager::dm_query($sql,array('headid'=>$headid,'classname'=>$classname));
        if(!$res) {
            $res = DataManager::dm_rollback();
            throw new DcsException("Class ".get_called_class().
                    " getnewid: new row insert unable",DCS_ERROR_SQL);
        }    
        $row = $res->fetch(PDO::FETCH_ASSOC);
	$res = DataManager::dm_commit();
        return $row['id'];
    }
    function create() 
    {
        $headid = $this->getid();
        $classname = $this->item_classname();
        $context = DcsContext::getcontext();
        if ($context->getattr('SETID')) {
            $headid = $context->getattr('DATA')['dcs_param_id']['id'];
            $classname = 'Item';
        }
        $id = $this->getnewid($headid,$classname);
        $redirect = '/'.$context->getattr('PREFIX').'/'.$id;
        return array('status'=>'OK', 'id'=>$id, 'redirect'=>$redirect);
    }
    public function save_new($data)
    {
    }        

    public function render()
    {
        return array('status'=>'OK', 'id'=>$this->id);
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
            $strval = $phead->getNameFromData()['synonym'];
            $navlist[] = array('id'=>$this->head->getid(),'name'=>sprintf("%s",$strval));
        }
    }
    public function get_navlist()
    {
        $navlist = array();
        $strval = 'Новый';
        $strkey = $this->id;
        if (!$this->isnew) {
            $strval = $this->getNameFromData()['synonym'];
        }    
        $this->add_navlist($navlist);
        $navlist[] = array('id' => $strkey,'name' => sprintf("%s",$strval));
        return $navlist;
    }        
    public function get_property($propid)
    {
        if (array_key_exists($propid, $this->properties) === false) {
            return NULL;
        }
        return $this->properties[$propid];
    }        

    // byid - bool - true : return indexed array by id
    // filter - function returning bool 
    //          or string 'toset' / 'tostring'
    //
    public function fill_entname(&$data,$arr_e) {
        $arr_entities = $this->getAllEntitiesToStr($arr_e);
        foreach($arr_entities as $rid=>$prow) {
            foreach($data as $id=>$row) {
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
        $val = '';
	if(array_key_exists($propid, $this->data)) {
	  $val = $this->data[$propid]['name'];
	}  
	return $val;
    }
    function getattrid($propid)
    {
        $val = '';
	if(array_key_exists($propid, $this->data)) {
            $val = $this->data[$propid]['id'];
	}  
	return $val;
    }
    function getattrbyname($name)
    {
        $val = '';
        $key = array_search($name, array_column($this->properties,'name','id'));
        if ($key !== false) {
            $val = $this->getattrid($key);
        }
	return $val;
    }
    public function setattr($propid,$valname,$valid='') 
    {
        $val='';
	if(array_key_exists($propid, $this->data)) {
	  $this->data[$propid]['name'] = $valname;
          $this->data[$propid]['id'] = $valid;
	}  
        return $this;
    }
    public function getArrayNew($newobj)
    {
        return array('id' => $newobj['id'], 
                    'name' => '_new_',
                    'synonym' => 'Новый',
                    'mdid' => $newobj['headid'],
                    'mdname' => $newobj['classname'],
                    'mdsynonym' => '',
                    'mditem' => '',
                    'mdtypename' => '',
                    'mdtypedescription' => '');
    }        
    public function getDetails($id) 
    {
        $sql = $this->txtsql_forDetails();
        $sth = DataManager::dm_query($sql,array('id'=>$id));   
        $res = $sth->fetch(PDO::FETCH_ASSOC);
        if (!$res) {
            $newobj = DataManager::getNewObjectById($id);
            if (!$newobj) {
                throw new DcsException("Class ".get_called_class().
                   " getDetails: id:".$id.' is wrong',DCS_ERROR_WRONG_PARAMETER);
            } else {
                $res = $this->getArrayNew($newobj);
            }
        }
        return $res;
    }
    public function get_classname() 
    {
        $s_class = explode('\\',get_called_class());
        return end($s_class);
    }
    public function update($data)     
    {
        if ($this->isnew) {
            $arr_name = $this->getNameFromData($data);
            if (!$this->create_object($arr_name['name'],$arr_name['synonym'])) {
                throw new DcsException("Class ".get_called_class().
                    " save_new: unable to save new object",DCS_ERROR_SQL);
            }
        }
        $context = DcsContext::getcontext();
        $res = $this->update_properties($data);
        if ($context->getattr('SETID')) {
            if ($res['status'] == 'OK')
            {
                $res1 = $this->update_dependent_properties($res['objs']);
                if (is_array($res1['objs'])) {
                    $res['objs'] += $res1['objs'];
                }
            }    
        }
        return $res;
    }
    public function get_head() {
        if (!$this->head) {
            return $this->head();
        }
        return $this->head;
    }
//    public function prop_to_Data(&$context,&$objs)
//    {        
//        $plist = array();
//        $sets = array();
//        $pset = array();
//        $ldata = array();
//        $propid = '';
//        $classname = $this->get_classname();
//        $prefix = $context['PREFIX'];
//        if ($prefix == 'CONFIG') {
//            if (strpos($classname,'Property') > 0) {
//                $plist = $this->getProperties(false);
//            } else {
//                $pset = $this->getProperties(true,'toset');
//            }
//        } else {
//             if (strpos($classname,'Set') > 0) {
//                $pset = $this->getProperties(true,'toset');
//            } else {
//                $plist = $this->getProperties(false);
//            }
//        }
//      
//        $objs['PLIST'] = $plist;
//        $objs['PSET'] = $pset;
//        $objs['SETS'] = $sets;
//    } 
    public function getItemsByFilter() 
    {
        $context = DcsContext::getcontext();
        $prefix = $context->getattr('PREFIX');
        $action = $context->getattr('ACTION');
    	$objs = array();
        $objs['PLIST'] = $this->getplist();
        $objs['LDATA'] = array();
        $objs['LDATA'][$this->id] = $this->load_data();
        $objs['PSET'] = $this->getItemsProp();
        $objs['SDATA'] = $this->getItems(DcsContext::getfilters());
        $objs['SETS'] = $this->getsets();
        $classname = $context->getattr('CLASSNAME');
        if ($context->data_getattr('dcs_setid')['name'] !== '') {
            $classname = 'Sets';
        }
        $objs['actionlist'] = DataManager::getActionsbyItem($classname,$prefix,$action);
        $objs['navlist'] = $this->get_navlist();
	return $objs;
    }
    public function getListItemsByFilter($filter=array()) 
    {
    	$objs = array();
        $objs['PSET'] = $this->head->getItemsProp();
        $objs['SDATA'] = $this->head->getItems($filter);
	return $objs;
    }
    public function getaccessright_id()
    {
        return $this->id;
    }        
    public function get_right() 
    {
        $userid = $_SESSION['user_id'];
        $sql = self::txtsql_access();
        if ($sql == '') {
            return 'read';
        }
        $params = array();
        $params['userid'] = $userid;
        $params['id'] = $this->getaccessright_id();
        $res = DataManager::dm_query($sql,$params);
        $arr_rd = $res->fetchAll(PDO::FETCH_ASSOC);
        $ar_wr = array_filter($arr_rd,function($item) { 
            return ((strtolower($item['name']) == 'write')&&
                    ($item['val'] === true));});
        if ($ar_wr) {
            return "write";
        }
        $ar_rd = array_filter($arr_rd,function($item) { 
            $res = ((strtolower($item['name']) == 'read')&&
                    ($item['val'] === true));});
        if ($ar_rd) {
            return "read";
        }
        return 'deny';
    }        
    public function setcontext_action($param,$prefix) 
    {
        if ($param === 'write') {
            return 'EDIT';
        }
        return 'VIEW';
    }
    public function check_right() 
    {
        $res = "deny";
        $isadmin = User::isAdmin();
        if ($isadmin) {
            $res = "write";
        } else {
            $res = $this->get_right();
        }
        if ($res == 'deny') {
            throw new DcsException('Access denied',DCS_DENY_ACCESS);
        }
        $context = DcsContext::getcontext();
        $context->setattr('ACTION',$this->setcontext_action($res, $context->getattr('PREFIX')));
    }
    public function property($pid)
    {
        $prop_classname = "\\Dcs\\Vendor\\Core\\Models\\".$this->getprop_classname();
        return new $prop_classname($pid,$this->head);
    }        
//    public function getplist() 
//    {
//        return array(
//            'id'=>array('id'=>'id','name'=>'id','synonym'=>'ID','type'=>'str'),
//            'name'=>array('id'=>'name','name'=>'name','synonym'=>'NAME','type'=>'str'),
//            'synonym'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','type'=>'str'),
//            'propid'=>array('id'=>'propid','name'=>'propid','synonym'=>'PROPID','type'=>'str'),
//            'rank'=>array('id'=>'rank','name'=>'rank','synonym'=>'RANK','type'=>'int'),
//            'ranktostring'=>array('id'=>'ranktostring','name'=>'ranktostring','synonym'=>'RANKTOSTRING','type'=>'int'),
//            'ranktoset'=>array('id'=>'ranktoset','name'=>'ranktoset','synonym'=>'RANKTOSET','type'=>'int'),
//            'type'=>array('id'=>'type','name'=>'type','synonym'=>'TYPE','type'=>'str'),
//            'name_type'=>array('id'=>'name_type','name'=>'name_type','synonym'=>'NAME_TYPE','type'=>'str'),
//            'class'=>array('id'=>'class','name'=>'class','synonym'=>'CLASS','type'=>'str'),
//            'valmdid'=>array('id'=>'valmdid','name'=>'valmdid','synonym'=>'VALMDID','type'=>'str'),
//            'valmdtypename'=>array('id'=>'valmdtypename','name'=>'valmdtypename','synonym'=>'VALMDTYPENAME','type'=>'str'),
//            'field'=>array('id'=>'field','name'=>'field','synonym'=>'FIELD','type'=>'int')
//            );        
//    }
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
        foreach($this->loadProperties() as $prop) 
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
            foreach ($this->getitemplist() as $prow) {    
                if (array_key_exists($prow,$prop)) {
                    $objs[$key][$prow] = $prop[$prow]; 
                } else {
                    continue;
                }    
            }
            $objs[$key]['class'] = 'active';
            if ($key === 'id') {
                $objs[$key]['class'] = 'hidden';
            } elseif ($prop['name'] === 'activity') {
                $objs[$key]['class'] = 'hidden';
            }
        }
        return $objs;
    }
    public function after_choice()
    {
        $context = DcsContext::getcontext();
        $data = $context->getattr('DATA');
        if ($data['dcs_param_type']['name'] == 'cid') {
            $modelname = "\\Dcs\\Vendor\\Core\\Models\\CollectionItem";
            $dep_prop = 'name';
        } elseif ($data['dcs_param_type']['name'] == 'id') {
            $modelname = "\\Dcs\\Vendor\\Core\\Models\\Entity";
            $dep_prop = 'propid';
        } else {
            return array('msg'=>'OK');
        }
        try {
            $ent = new $modelname($data['dcs_param_id']['id']);
        } catch (Exception $ex) {
            throw new DcsException("Class ".get_called_class().
                " constructor: id is not valid",DCS_ERROR_WRONG_PARAMETER);
        }
        $plist = $this->getplist();
        $c_plist = $ent->getplist();
        $c_ldata = $ent->load_data();
        $objs = array();
        foreach ($c_plist as $cprop) {
            $valprop = $cprop[$dep_prop];
            $arr_plist = array_filter($plist, 
                            function($item) use ($valprop,$dep_prop) {
                                return $item[$dep_prop] == $valprop;
                            });
            foreach ($arr_plist as $prop) {
                if (array_key_exists('isdepend', $prop)) {
                    if ($prop['isdepend'] !== true) {
                        continue;
                    }
                    $objs[$prop['id']] = array(
                                        'id' => $c_ldata[$cprop['id']]['id'],
                                        'name' => $c_ldata[$cprop['id']]['name']
                    );
                }
            }        
        }
        return $objs;
    }        
    public function before_save($data='') 
    {
        if (!$data) {
            $context = DcsContext::getcontext();
            $data = $context->getattr('DATA');
        }    
        $sql = '';
        $objs = array();
        if (!count($this->plist)) {
            $this->getplist();
        }
        if (!count($this->data)) {
            $this->load_data();
        }
        foreach ($this->plist as $prop)
        {    
            $propid = $prop['id'];
            if ($propid == 'id') {
                continue;
            }    
            if (!array_key_exists($propid, $data)) {        
                continue;
            }
            $nval = $data[$propid]['name'];
            $nvalid = $data[$propid]['id'];
            $pvalid = '';
            $pval = '';
            if (array_key_exists($propid, $this->data)) {        
                $pval = $this->data[$propid]['name'];
                $pvalid = $this->data[$propid]['id'];
            }
            if ($prop['name_type'] == 'id') {
                if ($pvalid == $nvalid) {
                    continue;
                }    
                if (($pvalid == DCS_EMPTY_ENTITY)&&($nvalid=='')) {
                    continue;
                }
            } elseif ($prop['name_type'] == 'date') {
                if (substr($pval,0,19) == substr($nval,0,19)) 
                {
                    continue;
                }    
            } 
            elseif ($prop['name_type']=='bool') 
            {
                if ((bool)$pval==(bool)$nval) 
                {
                    continue;
                }   
            } 
            else 
            {
                if ($pval==$nval) 
                {
                    continue;
                }    
            }
            $objs[]=array('id'=>$propid, 'name'=>$prop['name'],'pvalid'=>$pvalid, 'pval'=>$pval, 'nvalid'=>$nvalid, 'nval'=>$nval);
        }       
	return $objs;
    }
    function before_delete() 
    {
        $nval="удалить";
        return array($this->id=>array(
            'id'=>$this->id,
            'name'=>"Элемент ".$this->get_mdsynonym(),
            'pval'=>$this->name,
            'nval'=>$nval
                ));
    }    
    function delete() 
    {
	DataManager::dm_beginTransaction();
        $id = $this->id;
        $params = array();
        $params['id']=$id;
        $sql = "DELETE FROM \"".$this->dbtablename()."\" WHERE id = :id";
        try {
            DataManager::dm_query($sql,$params);
        } catch (DcsException $exc) {
            DataManager::dm_rollback();
            return array('status'=>'ERROR','msg'=>$exc->getTraceAsString());
        }
        DataManager::dm_commit();
        return array('status'=>'OK', 'id'=>$this->id);
    }    
}    