<?php
namespace Dcs\Vendor\Core;

//use dcs\vendor\core\Model;
//use dcs\vendor\core\iModel as iModel;

interface iPropertySet extends iModel
{
    public function txtsql_getproperties();
    public function getplist();
}

trait Properties {
    //load properties array from database
    protected $properties;
    protected $tablename;
    
    public function isExistThePropByTemplate($propid) 
    {
        if (count($this->properties) == 0)
        {
            $this->loadProperties();
        } 
        $key = array_search($propid, array_column($this->properties, 'propid','id'));
        return isset($this->properties[$key]);
    }
    public function isExistTheProp($id) 
    {
        if (count($this->properties) == 0)
        {
            $this->loadProperties();
        } 
        return isset($this->properties[$id]);
    }
    public function getProperty($id) 
    {
        $res = NULL;
        if ($this->isExistTheProp($id))
        {
            $res = $this->properties[$id];
        }
        return $res;
    }
    public function getPropList($byid=false)
    {    
        $objs = array();
        $plist = $this->getplist();
        $key = -1;    
        foreach($this->properties as $prop) 
        {
            $rid = $prop['id'];
            if ($byid)
            {    
                $key = $rid;
            }
            else
            {
                $key++;
            }    
            $objs[$key] = array();
            foreach ($plist as $pkey => $prow)
            {    
                $objs[$key][$pkey] = $prop[$pkey];
            }
        }
        return $objs;
    }    
    public function loadProperties()
    {
        $sql = $this->txtsql_getproperties();
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
    public function getProperties($strwhere, $byid=false) 
    {
        if (count($this->properties) == 0)
        {
            $this->loadProperties();
        }
        return $this->getPropList($byid);
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
abstract class PropertySet extends Model implements iPropertySet {
use Properties;    

    protected $mdentity;
    
    public function __construct($id='') 
    {
	if ($id == '') 
        {
            throw new Exception("class.PropertySet constructor: id is empty");
	}
        $this->id = $id; 
        $this->mdentity = new Mdentity($id);
        $this->name = $this->mdentity->getname()."_propertyset"; 
        $this->synonym = $this->mdentity->getsynonym()." (Список реквизитов)"; 
        $this->properties = array();
        $this->strwhere = "";
        $this->version = time();
    }
    public function getmdentity()
    {
        return $this->mdentity;
    }
    public function get_data() 
    {
        $plist = array(
          'id'=>array('name'=>'id','synonym'=>'ID','class'=>'active'),
          'name'=>array('name'=>'name','synonym'=>'NAME','class'=>'active'),
          'synonym'=>array('name'=>'synonym','synonym'=>'SYNONYM','class'=>'active')
        );
        return array(
          'id'=>$this->id,
          'name'=>$this->name,
          'synonym'=>$this->synonym,
          'version'=>$this->version,
          'PLIST' => $plist,   
          'navlist' => array(
              $this->id=>$this->synonym
            )
          );
    }
    public function txtsql_getproperties($strwhere)
    {
        return '';
    }        
    public function getMustBePropsUse()
    {
      	$sql = "SELECT ct_pt.name, ct_pt.synonym, pv_mditem.value as mditem, pv_pt.value as propid, pv_rank.value as rank, 
                    COALESCE(pv_edate.value, false) as isedate, COALESCE(pv_enum.value, false) as isenumber, ct_tp.name as type, COALESCE(pv_len.value,0) as length, COALESCE(pv_prc.value,0) as prec FROM \"CTable\" as pu 
	inner join \"MDTable\" as md
	ON pu.mdid = md.id
	and md.name='PropsUse'
	inner join \"CPropValue_cid\" as pv_mditem
		inner join \"CProperties\" as cp_mditem
		ON pv_mditem.pid=cp_mditem.id
		AND cp_mditem.name='mditem'
	ON pu.id=pv_mditem.id
        and pv_mditem.value = :mdtype
	inner join \"CPropValue_cid\" as pv_pt
		inner join \"CProperties\" as cp_pt
		ON pv_pt.pid=cp_pt.id
		AND cp_pt.name='propid'
                inner join \"CTable\" as ct_pt
                on pv_pt.value=ct_pt.id
		inner join \"CPropValue_cid\" as pv_tp
                    inner join \"CProperties\" as cp_tp
                    ON pv_tp.pid=cp_tp.id
                    AND cp_tp.name='type'
                    inner join \"CTable\" as ct_tp
                    on pv_tp.value = ct_tp.id
		on pv_pt.value = pv_tp.id
		left join \"CPropValue_int\" as pv_len
                    inner join \"CProperties\" as cp_len
                    ON pv_len.pid=cp_len.id
                    AND cp_len.name='length'
		on pv_pt.value = pv_len.id
		left join \"CPropValue_int\" as pv_prc
                    inner join \"CProperties\" as cp_prc
                    ON pv_prc.pid=cp_prc.id
                    AND cp_prc.name='prec'
		on pv_pt.value = pv_prc.id
        ON pu.id=pv_pt.id
	inner join \"CPropValue_int\" as pv_rank
		inner join \"CProperties\" as cp_rank
		ON pv_rank.pid=cp_rank.id
		AND cp_rank.name='rank'
        ON pu.id=pv_rank.id
	left join \"CPropValue_bool\" as pv_edate
		inner join \"CProperties\" as cp_edate
		ON pv_edate.pid=cp_edate.id
		AND cp_edate.name='isedate'
        ON pu.id=pv_edate.id
	left join \"CPropValue_bool\" as pv_enum
		inner join \"CProperties\" as cp_enum
		ON pv_enum.pid=cp_enum.id
		AND cp_enum.name='isenumber'
        ON pu.id=pv_enum.id";
	$res = DataManager::dm_query($sql,array('mdtype'=>$this->mdentity->getmditem()));
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }
}

