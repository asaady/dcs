<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Exception;

use Dcs\Vendor\Core\Models\Entity;

trait T_Sheet {
    function get_data(&$context) 
    {
        $this->check_right($context);
        $objs = array(
          'id'=>$this->id,
          'name'=>$this->name,
          'synonym'=>$this->synonym,
          'version'=>$this->version
          );
        $this->prop_to_Data($context, $objs);
        return $objs;
    }
    public function setnamesynonym()
    {
    }        
    public function get_navid() 
    {
        return $this->id;
    }
    function getnewid()
    {
	$res = DataManager::dm_query("BEGIN");
        $headid = $this->getid();
        $sql = "INSERT INTO \"NewObjects\" (headid,classname) VALUES (:headid,:classname) RETURNING \"id\"";
        $res=DataManager::dm_query($sql,array('headid'=>$headid,'classname'=>$this->item_classname()));
        if(!$res) {
            $res = DataManager::dm_query("ROLLBACK");
            throw new DcsException("Class ".get_called_class().
                    " getnewid: new row insert unable",DCS_ERROR_SQL);
        }    
        $row = $res->fetch(PDO::FETCH_ASSOC);
	$res = DataManager::dm_query("COMMIT");
        return $row['id'];
    }
    function create($context) 
    {
        $id = $this->getnewid();
        $redirect = '/'.$context['PREFIX'].'/'.$id;
        return array('status'=>'OK', 'id'=>$id, 'redirect'=>$redirect);
    }
    public function save_new($context,$data)
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
            $navlist[] = array('id'=>$this->head->getid(),'name'=>sprintf("%s",$this->head));
        }
    }
    public function get_navlist($context)
    {
        $navlist = array();
        $strkey = 'new';
        $strval = 'Новый';
        if ($this->id) {
            $strkey = $this->id;
            $strval = $this->getNameFromData($this->data);
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
    public function load_data($context)
    {
        $artemptable = $this->get_tt_sql_data();
        $sql = "select * from tt_out";
        $sth = DataManager::dm_query($sql);        
        $arr_e = array();
        while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $this->data['id'] = array('id'=>'','name'=>$row['id']);
            foreach($this->properties as $prow) {
                $rowname = $this->rowname($prow);
                if (array_key_exists('id_'.$rowname, $row)) {
                    $this->data[$prow['id']] = array(
                        'id'=>$row["id_$rowname"],
                        'name'=>$row["name_$rowname"]);
                    if ($prow['name_type'] === 'id') {
                        if ($prow['valmdtypename'] !== 'Sets') {    
                            if (($row["id_$rowname"])&&
                                ($row["id_$rowname"] != DCS_EMPTY_ENTITY)) {
                                if (!in_array($row["id_$rowname"],$arr_e)){
                                    $arr_e[] = $row["id_$rowname"];
                                }
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
        $this->head = $this->get_head();
        $this->setnamesynonym();
        $this->check_right($context);
    }            
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
    public function getDetails($id) 
    {
        $sql = self::txtsql_forDetails();
        $sth = DataManager::dm_query($sql,array('id'=>$id));   
        $res = $sth->fetch(PDO::FETCH_ASSOC);
        if (!$res) {
            $newobj = DataManager::getNewObjectById($id);
            if (!$newobj) {
                throw new DcsException("Class ".get_called_class().
                   " getDetails: id:".$id.' is wrong',DCS_ERROR_WRONG_PARAMETER);
            } else {
                $res = array('id' => $id, 
                             'name' => '_new_',
                             'synonym' => 'Новый',
                             'mdid' => $newobj['headid'],
                             'mdname' => $newobj['classname'],
                             'mdsynonym' => '',
                             'mditem' => '',
                             'mdtypename' => '',
                             'mdtypedescription' => '');
            }
        }
        return $res;
    }
    public function get_classname() 
    {
        $s_class = explode('\\',get_called_class());
        return end($s_class);
    }
    public function update($context,$data)     
    {
        $name = $this->getNameFromData($data);
        if (!$this->create_object($this->id,$this->mdid,$name)) {
            throw new DcsException("Class ".get_called_class().
                " save_new: unable to save new object",DCS_ERROR_SQL);
        }
        $res = $this->update_properties($context,$data);
        if ($res['status'] == 'OK')
        {
            $res1 = $this->update_dependent_properties($context,$res['objs']);
            if (is_array($res1['objs'])) {
                $res['objs'] += $res1['objs'];
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
    public function prop_to_Data(&$context,&$objs)
    {        
        $plist = array();
        $sets = array();
        $pset = array();
        $ldata = array();
        $propid = '';
        $classname = $this->get_classname();
        $prefix = $context['PREFIX'];
        if ($prefix == 'CONFIG') {
            if (strpos($classname,'Property') > 0) {
                $plist = $this->getProperties(false);
            } else {
                $pset = $this->getProperties(true,'toset');
            }
        } else {
            if (strpos($classname,'Set') > 0) {
                $pset = $this->getProperties(true,'toset');
            } else {
                $plist = $this->getProperties(false);
            }
        }
      
        $objs['PLIST'] = $plist;
        $objs['PSET'] = $pset;
        $objs['SETS'] = $sets;
    } 
    public function getItemData($context) 
    {
    	$objs = array();
        $this->load_data($context);
        $this->prop_to_Data($context, $objs);
        if ($this->data) {
            $objs['LDATA'] = array();
            $objs['LDATA'][$this->id] = $this->data;
        }    
        $objs['SDATA'] = $this->getItems($context);
	return $objs;
    }
    public function getItemsByFilter($context) 
    {
        $prefix = $context['PREFIX'];
        $action = $context['ACTION'];
    	$objs = $this->getItemData($context);
        $objs['actionlist'] = DataManager::getActionsbyItem($context['CLASSNAME'],$prefix,$action);
        $objs['navlist'] = $this->get_navlist($context);
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
    public function check_right(&$context) 
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
        $context['ACTION'] = $this->setcontext_action($res, $context['PREFIX']);
    }
}
