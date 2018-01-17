<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_Properties {
    //load properties array from database
    protected $tablename;
    
    public function isExistThePropByTemplate($propid) 
    {
        if (count($this->properties) == 0)
        {
            $this->loadProperties();
        } 
        $key = array_search($propid, array_column($this->properties, 'propid','id'));
        if ($key) {
            return isset($this->properties[$key]);
        }
        return FALSE;
    }
    public function loadProperties($strwhere)
    {
        $sql = $this->txtsql_getproperties($strwhere);
        if ($sql === '')
        {
            return 0;
        }    
        $params = array('mdid'=>$this->id);
        $res = DataManager::dm_query($sql,$params);
        $cnt = 0;
        $this->properties = array();
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $this->properties[$row['id']] = $row;
            $cnt++;
        }    
        return $cnt;
    }        
    public function createMustBeProperty()
    {
        $arMB = $this->getMustBePropsUse();
        if (count($arMB)) 
        {
            foreach($arMB as $mdprop) 
            {
                if($this->isExistThePropByTemplate($mdprop['propid']))
                {        
                    continue;
                }
                $res = $this->create($mdprop);
            }
        }
    }
    //create property from data
    public function create($data) 
    {
        $flds = '';
        $params = array();
        $plist = $this->getplist();
        $sql = '';
        foreach ($plist as $pid => $prow)
        {    
            if ($pid == 'id') {
                continue;
            }
            if ($pid == 'mdid') {
                continue;
            }
            if (!$prow['field']) {
                continue;
            }
            if (array_key_exists($pid, $data))
            {
                $val = $data[$pid]['name'];
                if ($val !== '')
                {
                    if (($prow['type'] == 'id')||($prow['type'] == 'cid')||($prow['type'] == 'mdid'))
                    {
                       $val = $data[$pid]['id'];
                    }    
                }    
                if ($val !== '')
                {    
                    $params[$pid] = $val;
                    $flds .= ', '.$pid;
                    $vals .= ', :'.$pid;
                }    
            }
        }
        $objs = array();
        $objs['id']='';
        $objs['status']='NONE';
        if ($flds !== '')
        {
            $flds = substr($flds, 1);
            $vals = substr($vals, 1);
            $sql = "INSERT INTO \"$this->tablename\" (".$flds.",mdid) VALUES (".$vals.", :mdid) RETURNING \"id\"";
            $params['mdid'] = $this->id;
            
            $objs['status']='ERROR';
            $objs['msg']=$sql;
            $res = DataManager::dm_query($sql,$params);
            if($res) 
            {
                $row = $res->fetch(PDO::FETCH_ASSOC);
                if($row) 
                {
                    $objs['status']='OK';
                    $objs['id']=$row['id'];
                }    
            }
        }
        return $objs;
    }
    function before_save($id, $data) {
        $objs = array();
        $plist = $this->getplist();
        if (!$this->isExistTheProp($id))
        {
            return $objs;
        }
        $cprop = $this->properties[$id];
        $sql = '';
        foreach ($plist as $key => $prow)
        {    
            if ($key=='id') 
            {
                continue;
            }    
            if ($key=='mdid') 
            {
                continue;
            }    
            if (!$prow['field']) {
                continue;
            }
            $pval = $cprop[$key];
            $nval = $data[$key]['name'];
            $nvalid = '';
            if (($prow['type'] == 'id')||($prow['type'] == 'cid')||($prow['type'] == 'mdid')) {
                $nval = $data[$key]['name'];
                $nvalid = $data[$key]['id'];
                if ($cprop[$key] == $data[$key]['id']) continue;
            } else {
                if ($prow['type'] == 'bool') {
                    if ((bool)$cprop[$key] === (bool)$data[$key]['name']) continue;
                } else {
                    if ($cprop[$key] == $data[$key]['name']) continue;
                }
            }
            if ($prow['name'] == 'propid')
            {
                $pval = $cprop['name_propid'];
            }   
            $objs[]=array('name'=>$key, 'pval'=>$pval, 'nval'=>$nval, 'nvalid'=>$nvalid);
        }    
	return $objs;
    }
    public function update($id, $data) 
    {
        $sql = '';
        $objs = $this->before_save($data);
        $params = array();
        foreach($objs as $row)
        {    
            $val = $row['nval'];
            if ($row['name'] == 'propid')
            {
                $val = $row['nvalid'];
            }   
            $sql .= ", $row[name] = :$row[name]";
            $params[$row['name']] = $val;
        }
        $objs['status'] = 'NONE';
        if ($sql != '') {
            $objs['status']='OK';
            $sql = substr($sql,1);
            $sql = "UPDATE \"$this->tablename\" SET$sql WHERE id=:id";
            $params['id']=$id;
            $res = DataManager::dm_query($sql,$params);
            if(!$res) {
                return array('status'=>'ERROR', 'msg'=>$sql);
            }
        }
        return array('status'=>'OK', 'id'=>$this->id);
    }
    function property_getdata($id) 
    {
        $objs = array();
        if (!$this->isExistTheProp($id))
        {
            return $objs;
        }
        $cprop = $this->properties[$id];
        $objs = array('id'=>$id,      
                    'version' => $this->version,
                    'PLIST' => array_values($this->getplist()),
                    'navlist'=>array(
                    $this->mdentity->getmditem() => $this->mdentity->getmditemsynonym(),
                    $this->id => $this->mdentity->getsynonym(),
                    $id => $cprop['synonym']
                    )
              );
        return $objs;
    }
    function property_loaddata($id, $mode='', $action='') 
    {
        $objs = array();
        $objs['PLIST']=$this->getplist();
        $objs['actionlist']= DataManager::getActionsbyItem('Entity', $mode,$action);
        $objs['SDATA'] = array();
        if (!$this->isExistTheProp($id))
        {
            return $objs;
        }
        $objs['SDATA'][$id] = array();
        $row = $this->properties[$id];
        foreach ($objs['PLIST'] as $prow)
        {
            if ($prow['class'] == 'hidden') {
                continue;
            }
            $valname = $row[$prow['name']];
            $valid = '';
            if (($prow['type'] == 'id')||($prow['type'] == 'cid')||($prow['type'] == 'mdid'))
            {
                $valid = $valname;
                $valname = $row['name_'.$prow['name']];
            }    
            $objs['SDATA'][$id][$prow['id']] = array('id'=>$valid,'name'=>$valname);
        }    
        return $objs;
    }
}
