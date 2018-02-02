<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Exception;

use Dcs\Vendor\Core\Models\Entity;

trait T_Sheet {
    function get_data(&$context) 
    {
        if  ($this->getmdtypename() == 'Items') {
            if ((array_key_exists('docid',$context['DATA']) !== FALSE)&&
                ($context['DATA']['docid']['id'] !== '')&&
                (array_key_exists('propid',$context['DATA']) !== FALSE)&&
                ($context['DATA']['propid']['id'] !== '')) {
                $prop = new EProperty($context['DATA']['propid']['id']);
                
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
    function get_head()
    {
        if(!$this->head) {
            return $this->head();
        }
        return $this->head;
    }
    public function get_navid() 
    {
        return $this->id;
    }
    function create($context) 
    {
        $entity = $this->item();
        $entity->set_data($context);
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
            $strval = sprintf("%s",$this);
        }    
        if (($this->mdtypename == 'Items')&&
            ($context['PREFIX'] !== 'CONFIG')) {
            if (isset($context['DATA']['docid'])) {
                $docid = $context['DATA']['docid']['id'];
                $doc = new Entity($docid);
                if (isset($context['DATA']['propid'])) {
                    $propid = $context['DATA']['propid']['id'];
                    $doc->add_navlist($navlist);
                    $navlist[] = array('id'=>$docid,'name'=>sprintf("%s",$doc));
                    $prop = $doc->getProperty($propid);
                    $navlist[] = array('id'=>"$docid?propid=".$propid,'name' => $prop['synonym']);
                    $strkey .= "?docid=$docid&propid=$propid";
                }
            }
        } elseif (($context['CLASSNAME'] == 'Entity')&&
                  ($context['PREFIX'] !== 'CONFIG')) {
            if (isset($context['DATA']['propid'])) {
                $propid = $context['DATA']['propid']['id'];
                if ($propid !== '') {
                    $this->add_navlist($navlist);
                    $navlist[] = array('id'=>$this->id,'name' => sprintf("%s",$this));
                    $strkey .= "?propid=".$propid;
                    $strval = $this->properties[$propid]['synonym'];
                }    
            }
        }
        if (!count($navlist)) {    
            $this->add_navlist($navlist);
        }    
        $navlist[] = array('id' => $strkey,'name' => sprintf("%s",$strval));
        return $navlist;
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
                                    $arr_e[]=$row["id_$rowname"];
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
    function getattrbyname($name)
    {
        $val='';
        $key = array_search($name, array_column($this->properties,'name','id'));
        if ($key !== FALSE) {
            $val = $this->getattrid($key);
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
    public static function getDetails($id) 
    {
        $objs = array('id'=>'','mdid'=>'','mditem'=>'');
        $sql = self::txtsql_forDetails();
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
            if (strpos($classname,'Property') === FALSE) {
                $pset = $this->getProperties(TRUE,'toset');
            } else {
                $plist = $this->getProperties(FALSE);
            }
        } else {
            if (strpos($classname,'Set') === FALSE) {
                $plist = $this->getProperties(FALSE);
                $cnt = 0;
                $psets = array_filter($this->properties,function($item){
                    return $item['valmdtypename'] === 'Sets';
                });
                if (count($psets)) {
                    $propid = '';
                    if (isset($context['DATA']['propid'])) {
                        $propid = $context['DATA']['propid']['id'];
                    } elseif (isset($context['DATA']['setid'])) {
                        $propid = $context['DATA']['setid']['id'];
                    }   
                    $setid = '';
                    foreach ($psets as $prop) {
                        $setid = $prop['id'];
                        if (!$this->getattrid($setid)) {
                            $set = new Mdentity($prop['valmdid']);
                        } else {
                            $set = new Entity($this->getattrid($setid));
                        }    
                        $sets[$setid] = $set->getProperties(true,'toset');
                        if ($propid == $prop['id']) {
                            $context['SETID'] = $propid;
                        }
                    }  
                    if (count($psets) == 1) { 
                         $context['SETID'] = $setid;
                    }  
                }
            } else {
                $pset = $this->getProperties(TRUE,'toset');
            }
        }
        $objs['PLIST'] = $plist;
        $objs['PSET'] = $pset;
        $objs['SETS'] = $sets;
    } 
    public function getItemData($context) 
    {
    	$objs = array();
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
    public static function txtsql_access() 
    {
        return '';
    }
    public static function get_right($id) 
    {
        $userid = $_SESSION['user_id'];
        $sql = self::txtsql_access();
        if ($sql == '') {
            return (User::isAdmin()) ? 'write':'read';
        }
        $params = array();
        $params['userid'] = $userid;
        $params['id'] = $id;
        $res = DataManager::dm_query($sql,$params);
        $arr_rd = $res->fetchAll(PDO::FETCH_ASSOC);
        $ar_wr = array_filter($arr_rd,function($item) { 
            return ((strtolower($item['name']) == 'write')&&
                    ($item['val'] === TRUE));});
        if ($ar_wr) {
            return "edit";
        }
        $ar_rd = array_filter($arr_rd,function($item) { 
            return ((strtolower($item['name']) == 'read')&&
                    ($item['val'] === TRUE));});
        if ($ar_rd) {
            return "read";
        }
        return "deny";
    }
}
