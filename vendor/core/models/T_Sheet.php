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
        $sets = $this->getsets();
        if(count($sets)) {
            $objs['SETS'] = $sets;
        }
        $objs['PSET'] = $this->getItemsProp();
        return $objs;
    }
    //array sets properties for output
    public function getsets() 
    {
        if (!count($this->plist)) {
            $this->getplist();
        }
        $psets = array_filter($this->plist,function($item){
                            return $item['name_valmditem'] === 'Sets';
                        });
        if (!count($psets)) {
            return array();
        }
        $propid = '';
        $context = DcsContext::getcontext();
        $propid = $context->data_getattr('dcs_propid')['id'];
        $sets = array();
        foreach ($psets as $prop) {
            $mdset = new Mdentity($prop['id_valmdid']);
            $props = $mdset->get_arritems();
            $items = array_filter($props,function($item){
                            return $item['name_valmditem'] === 'Items';
                        });
            $sets[$prop['id']] = array();
            if (!count($items)) {
                continue;
            }
            foreach ($items as $item) {
                $mditem = new Mdentity($item['id_valmdid']);
                foreach (array_filter($mditem->get_arritems(),
                            function($item) {
                                return $item['ranktoset'] > 0;
                            }) as $itemprop) {
                    $sets[$prop['id']][$itemprop['id']] = $itemprop;
                }
            }
            if ($propid == $prop['id']) {
                $context->setattr('SETID',$this->get_valid($propid));
                $context->setattr('PROPID',$propid);
            } elseif (count($psets) == 1) { 
                $context->setattr('SETID',$this->get_valid($prop['id']));
                $context->setattr('PROPID',$prop['id']);
                break;
            }  
        }  
        return $sets;
    }
    public function setnamesynonym()
    {
    }        
    public function get_navid() 
    {
        return $this->id;
    }
    public function create() 
    {
        $context = DcsContext::getcontext();
        $headid = $this->getid();
        $classname = $this->item_classname();
        $propid = $context->getattr('PROPID');
        $setid = $context->getattr('SETID');
        if ($propid) {
            if (!$setid) {
                
            }    
            $headid = $setid;
            $classname = 'Item';
        }    
        $id = DataManager::getnewid($headid,$classname);
        $redirect = '/'.$context->getattr('PREFIX').'/'.$id;
        return array('status'=>'OK', 'id'=>$id, 'redirect'=>$redirect);
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
        $context = DcsContext::getcontext();
        DataManager::dm_beginTransaction();
        try {
            if ($this->isnew) {
                $this->create_object($this->params_to_create($data));
            }
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
        } catch (DcsException $ex) {
            DataManager::dm_rollback();
            throw $ex;
        }
        DataManager::dm_commit();
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
    	$objs = array();
        $objs['PLIST'] = $this->getplist();
        $objs['LDATA'] = array();
        $objs['LDATA'][$this->id] = $this->load_data();
        $classname = $context->getattr('CLASSNAME');
        $setid = '';
        $propid = $context->getattr('PROPID');
        if (($propid)&&($classname == 'Entity')) {
            $setid = $this->get_valid($propid);
            $classname = 'Sets';
            if ($setid) {
                $set = new Sets($setid,$this);
                $objs['SETS'] = array($propid => $set->getItemsProp());
                $objs['SDATA'] = array($propid => $set->getItems(DcsContext::getfilters()));
            } else {
                $prop = new EProperty($propid);
                $prop->load_data();
                $valmdid = $prop->getattrid('valmdid');
                $entset = new EntitySet($valmdid);
                $objs['SETS'] = array($propid => $entset->getItemsProp());
                $objs['SDATA'] = array($propid => array());
            }  
            $objs['SETID'] = $setid;
            $objs['PSET'] = array();
        } else {
            $objs['PSET'] = $this->getItemsProp();
            $objs['SDATA'] = $this->getItems(DcsContext::getfilters());
            $sets = $this->getsets();
            if (count($sets)) {
                $objs['SETS'] = $sets;
            }
            if ($classname == 'CollectionItem') {
                if ($context->getattr('CLASSTYPE') == 'Utils') {
                    $classname = 'Component';
                }
            }
        }
        $prefix = $context->getattr('PREFIX');
        $action = $context->getattr('ACTION');
        $objs['actionlist'] = DataManager::getActionsbyItem($classname,$prefix,$action);
        $objs['navlist'] = $this->get_navlist();
	return $objs;
    }
    public function getListItemsByFilter($filter=array()) 
    {
    	$objs = array();
        $objs['PSET'] = $this->getItemsProp();
        $objs['SDATA'] = $this->getListItems($filter);
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
                    return $item['name_valmditem'] === 'Sets';
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
        if ($context->data_getattr('dcs_param_type')['name'] == 'cid') {
            $modelname = "\\Dcs\\Vendor\\Core\\Models\\CollectionItem";
            $dep_prop = 'name';
        } elseif ($context->data_getattr('dcs_param_type')['name'] == 'id') {
            $modelname = "\\Dcs\\Vendor\\Core\\Models\\Entity";
            $dep_prop = 'propid';
        } else {
            return array('msg'=>'OK');
        }
        $ent = new $modelname($context->data_getattr('dcs_param_id')['id']);
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
            if ($prop['field'] == 0) {
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
                if ($pvalid == $nvalid) {
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
        $id = $this->id;
        $params = array();
        $params['id']=$id;
        $sql = "DELETE FROM \"".$this->dbtablename()."\" WHERE id = :id";
        DataManager::dm_query($sql,$params);
        return array('status'=>'OK', 'id'=>$this->id);
    }    
    public function params_to_create($data='')
    {
        $name = $this->name;
        $synonym = $this->synonym;
        if ($data) {
            $name = $data['name']['name'];
            $synonym = $data['synonym']['name'];
        }    
        return array(
                'name' => $name, 
                'synonym'=>$synonym,
                'mdid'=> $this->mdid,
                'id'=> $this->id
                );
    }        
    public function create_object($params)
    {
        $str_par = '';
        $str_pvar = '';
        foreach ($params as $par=>$val) {
            $str_par .= ", $par";
            $str_pvar .= ", :$par";
        }
        $str_par = substr($str_par,1);
        $str_pvar = substr($str_pvar,1);
        $sql ="INSERT INTO \"".$this->dbtablename()."\" ($str_par) "
                    . "VALUES ($str_pvar) RETURNING \"id\"";
        $res = DataManager::dm_query($sql,$params); 
        $rowid = $res->fetch(PDO::FETCH_ASSOC);
        $this->after_create();
        return $rowid['id'];
    }        
    public function after_create()
    {
        return NULL;
    }

    public function getItemsByName($name) 
    {
        return NULL;
    }
    public function getItemsProp() 
    {
        return $this->getProperties(TRUE,'toset');
    }
    public function get_arritems()
    {
        return $this->get_items()->fetchAll(PDO::FETCH_ASSOC);
    }        
    public function getItems($filter=array()) 
    {
        $context = DcsContext::getcontext();
        $propid = $context->getattr('PROPID');
        if ($propid) {
            $setid = $this->get_valid($propid);
            if ((!$setid)||($setid === DCS_EMPTY_ENTITY)) {
                return array($propid => array());
            }    
            $context->setattr('SETID',$setid);
            $set = new Sets($setid,$this);
            return array($propid => $set->getItems($filter));
        }
        $objs = array();
        $this->loadProperties();
        $arr_e = array();
        $res = $this->get_items();
        if (!$res) {
            return $objs;
        }
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $c_arr = array();
            $c_arr['class'] = 'active';
            foreach ($this->properties as $prop) {
                $propid = $prop['id'];
                if ($propid == 'id') {
                    continue;   
                }
                if (($propid == 'name')||($propid == 'synonym')) {
                    $c_arr[$propid] = array(
                                    'id'=>'',
                                    'name' => $row[$propid]);
                    continue;   
                }
                if ($prop['ranktoset'] == 0) {
                    continue;   
                }
                $rowname = Filter::rowname($propid);
                $id_rowname = 'id_'.$rowname;
                $name_rowname = 'name_'.$rowname;
                $propid_rowname = 'propid_'.$rowname;
                if (array_key_exists($propid_rowname, $row)) {
                    if (($prop['name_type'] == 'id')||
                        ($prop['name_type'] == 'cid')||    
                        ($prop['name_type'] == 'mdid')) {    
                        $c_arr[$row[$propid_rowname]] = array(
                                            'id'=>$row[$id_rowname],
                                            'name' => $row[$name_rowname]);
                        if ($prop['name_type'] == 'id') {
                            if (($row[$id_rowname])&&
                                ($row[$id_rowname]!=DCS_EMPTY_ENTITY)) {
                                    if (!in_array($row[$id_rowname],$arr_e)){
                                        $arr_e[]=$row[$id_rowname];
                                    }
                            }
                        }    
                    } elseif ($prop['name_type'] == 'date') {
                        $c_arr[$row[$propid_rowname]] = array(
                                        'id'=>'',
                                        'name' => substr($row[$name_rowname],0,10));
                    } else {
                        $c_arr[$row[$propid_rowname]] = array(
                                            'id'=>'',
                                            'name' => $row[$name_rowname]);
                    }
                }
                if (strtolower($prop['name']) == 'activity')
                {
                    if ($row[$name_rowname]===false)
                    {    
                        $c_arr['class'] ='erased';               
                    }    
                }    
            }
            $objs[$row['id']] = $c_arr;
        }
        if (count($arr_e))
        {
            DataManager::fill_entsetname($objs,$arr_e);
        }
        return $objs;
    }
    public function getListItems($filter=array()) 
    {
        $objs = array();
        $this->loadProperties();
        $arr_e = array();
        $res = $this->get_items();
        if (!$res) {
            return $objs;
        }
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $c_arr = array();
            $c_arr['class'] = 'active';
            foreach ($this->properties as $prop) {
                $propid = $prop['id'];
                if ($propid == 'id') {
                    continue;   
                }
                if (($propid == 'name')||($propid == 'synonym')) {
                    $c_arr[$propid] = array(
                                    'id'=>'',
                                    'name' => $row[$propid]);
                    continue;   
                }
                if ($prop['ranktoset'] == 0) {
                    continue;   
                }
                $rowname = Filter::rowname($propid);
                $id_rowname = 'id_'.$rowname;
                $name_rowname = 'name_'.$rowname;
                $propid_rowname = 'propid_'.$rowname;
                if (array_key_exists($propid_rowname, $row)) {
                    if (($prop['name_type'] == 'id')||
                        ($prop['name_type'] == 'cid')||    
                        ($prop['name_type'] == 'mdid')) {    
                        $c_arr[$row[$propid_rowname]] = array(
                                            'id'=>$row[$id_rowname],
                                            'name' => $row[$name_rowname]);
                        if ($prop['name_type'] == 'id') {
                            if (($row[$id_rowname])&&
                                ($row[$id_rowname]!=DCS_EMPTY_ENTITY)) {
                                    if (!in_array($row[$id_rowname],$arr_e)){
                                        $arr_e[]=$row[$id_rowname];
                                    }
                            }
                        }    
                    } elseif ($prop['name_type'] == 'date') {
                        $c_arr[$row[$propid_rowname]] = array(
                                        'id'=>'',
                                        'name' => substr($row[$name_rowname],0,10));
                    } else {
                        $c_arr[$row[$propid_rowname]] = array(
                                            'id'=>'',
                                            'name' => $row[$name_rowname]);
                    }
                }
                if (strtolower($prop['name']) == 'activity')
                {
                    if ($row[$name_rowname]===false)
                    {    
                        $c_arr['class'] ='erased';               
                    }    
                }    
            }
            $objs[$row['id']] = $c_arr;
        }
        if (count($arr_e))
        {
            DataManager::fill_entsetname($objs,$arr_e);
        }
        return $objs;
    }
    public function loadProperties()
    {
        if (count($this->properties)) {
            return $this->properties;
        }
        $properties = array();
        $sql = $this->txtsql_properties("mdid");
        if (!$sql) {
            return $properties;
        }
        $params = array('mdid'=> $this->mdid);
        $res = DataManager::dm_query($sql,$params);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $properties[$row['id']] = $row;
        }    
        $this->properties = $properties;
        return $properties;
    }        
//    public function load_data($data='')
//    {
//        $this->data['id'] = array('id'=>'','name'=>$this->id);
//        $this->data['name'] = array('id'=>'','name'=>$this->name);
//        $this->data['synonym'] = array('id'=>'','name'=>$this->synonym);
//        if (!count($this->plist)) {
//            $this->getplist();
//        }
//        if (!$data) {
//            $sql = $this->get_tt_sql_data();
//            $sth = DataManager::dm_query($sql,array('id'=>$this->id));        
//            while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
//                foreach($this->plist as $prow) {
//                    $this->data[$prow['id']] = array('id'=>'','name'=>'');
//                    if (array_key_exists("name_".$prow['id'], $row)) {
//                        $this->data[$prow['id']]['name'] = $row["name_".$prow['id']];
//                    }
//                    if (array_key_exists("id_".$prow['id'], $row)) {
//                        $this->data[$prow['id']]['id'] = $row["id_".$prow['id']];
//                    }
//                    if (array_key_exists($prow['id'], $row)) {
//                        $this->data[$prow['id']]['name'] = $row[$prow['id']];
//                    }
//                }    
//            }
//        } else {
//            $this->data['id'] = array('id'=>'','name'=>$data['id']);
//            foreach($this->plist as $prow) {
//                $this->data[$prow['id']] = array('id'=>'','name'=>'');
//                if (array_key_exists("name_".$prow['id'], $data)) {
//                    $this->data[$prow['id']]['name'] = $data["name_".$prow['id']];
//                }
//                if (array_key_exists("id_".$prow['id'], $data)) {
//                    $this->data[$prow['id']]['id'] = $data["id_".$prow['id']];
//                }
//                if (array_key_exists($prow['id'], $data)) {
//                    $this->data[$prow['id']]['name'] = $data[$prow['id']];
//                }
//            }    
//        }  
//        $this->version = time();
//        $this->head = $this->get_head();
//        $this->check_right();
//        return $this->data;
//    }            
    public function load_data($data='')
    {
        $this->data['id'] = array('id'=>'','name'=>$this->id);
        $this->data['name'] = array('id'=>'','name'=>$this->name);
        $this->data['synonym'] = array('id'=>'','name'=>$this->synonym);
        if (!count($this->plist)) {
            $this->getplist();
        }
        if (!$data) {
            foreach($this->plist as $prow) {
                $this->data[$prow['id']] = array('id'=>'','name'=>'');
            }    
            $artemptable = $this->get_tt_sql_data();
            $sql = "select * from tt_out";
            $sth = DataManager::dm_query($sql);    
            $arr_e = array();
            while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                $this->data['id'] = array('id'=>$row['id'],'name'=>$row['id']);
                foreach($this->plist as $prow) {
                    $this->data[$prow['id']] = array('id'=>'','name'=>'');
                    $rowname = Filter::rowname($prow['id']);
                    if (array_key_exists('id_'.$rowname, $row)) {
                        $this->data[$prow['id']]['id'] = $row["id_$rowname"];
                        if ($prow['name_type'] === 'id') {
                            if ($prow['name_valmditem'] !== 'Sets') {    
                                if (($row["id_$rowname"])&&
                                    ($row["id_$rowname"] != DCS_EMPTY_ENTITY)) {
                                    if (!in_array($row["id_$rowname"],$arr_e)){
                                        $arr_e[] = $row["id_$rowname"];
                                    }
                                }    
                            }    
                        }
                    }
                    if (array_key_exists('name_'.$rowname, $row)) {
                        $this->data[$prow['id']]['name'] = $row["name_$rowname"];
                    }
                    if (array_key_exists($rowname, $row)) {
                        $this->data[$prow['id']]['name'] = $row[$rowname];
                    }
                }    
            }
            if (count($arr_e)) {
                DataManager::fill_entname($this->data,$arr_e);
            }
            DataManager::droptemptable($artemptable);
        } else {
            $this->data['id'] = array('id'=>'','name'=>$data['id']);
            foreach($this->plist as $prow) {
                $this->data[$prow['id']] = array('id'=>'','name'=>'');
                if (array_key_exists('name_'.$prow['id'], $data)) {
                    $this->data[$prow['id']]['name'] = $data['name_'.$prow['id']];
                }    
                if (array_key_exists('id_'.$prow['id'], $data)) {
                    $this->data[$prow['id']]['id'] = $data['id_'.$prow['id']];
                }    
                if (array_key_exists($prow['id'], $data)) {
                    $this->data[$prow['id']]['name'] = $data[$prow['id']];
                }
            }    
        }
        $this->version = time();
//        $this->head = $this->get_head();
        $this->setnamesynonym();
        $this->check_right();
        return $this->data;
    }            
}    