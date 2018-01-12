<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

class Mdproperty extends Property implements IProperty {
    use TeProperty;
    use TProperties;
    
    protected $propstemplate;
    protected $typeid;
    protected $propid;
    protected $name_propid;
    protected $isedate;
    protected $isenumber;
    protected $isdepend;
    
    public function __construct($id) 
    {
        if ($id=='')
        {
            throw new Exception("class.MDProperty constructor: id is empty");
        }
        
        $arData = self::getProperty($id);
        if ($arData)
        {
            //передан id реального свойства метаданного
            $mdid = $arData['mdid'];
            $this->propstemplate = new PropsTemplate($arData['propid']);
            $this->id = $id;
            $this->name = $arData['name'];    
            $this->synonym = $arData['synonym'];  
            $this->propid = $arData['propid'];
            $this->name_propid = $arData['name_propid'];
            $this->type = $arData['type'];    
            $this->typeid = $arData['typeid'];    
            $this->length = $arData['length'];    
            $this->prec = $arData['prec'];
            $this->rank = $arData['rank'];        
            $this->ranktostring = $arData['ranktostring'];
            $this->ranktoset = $arData['ranktoset'];
            $this->isedate = $arData['isedate'];
            $this->isenumber = $arData['isenumber'];
            $this->isdepend = $arData['isdepend'];
            $this->valmdid = $arData['valmdid'];
            $this->name_valmdid = $arData['valmdname'];
            $this->valmdtypename = $arData['valmdtypename'];
        } 
        else 
        {
            //считаем что передан id реального метаданного и создаем пустое свойство
            $mdid = $id;
            $this->propstemplate = new PropsTemplate('');
            $this->id = '';
            $this->name = '';    
            $this->synonym = '';    
            $this->type = 'str';    
            $this->length = 10;    
            $this->prec = 0;    
            $this->rank = 999;    
            $this->ranktostring = 0;        
            $this->ranktoset = 0;    
            $this->isedate = false;        
            $this->isenumber = false;        
            $this->isdepend = false;
        }
        $this->head = new Mdentity($mdid);
        $this->version=time();
    }
    function getvalmdid()
    {
        return $this->valmdid;
    }
    function isdepend()
    {
        return $this->isdepend;
    }
    function getpropstemplate()
    {
        return $this->propstemplate;
    }
    function load_data($mode,$edit_mode) 
    {
        $objs = array();
        $objs['PLIST']=$this->get_plist($mode);
        $objs['actionlist']= DataManager::getActionsbyItem('Entity', $mode,$edit_mode);
        $sql = DataManager::get_select_properties(" where mp.id = :id ");
	$res = DataManager::dm_query($sql,array('id'=>$this->id));
        $objs['SDATA'] = array();
        $objs['SDATA'][$this->id] = array();
        $row = $res->fetch(PDO::FETCH_ASSOC);
        foreach ($objs['PLIST'] as $prow)
        {
            if ($prow['name']=='propid')
            {
                $objs['SDATA'][$this->id][$prow['id']] = array('id'=>$row['propid'],'name'=>$row['name_propid']);
            }   
            elseif ($prow['name']=='valmdid')
            {
                $objs['SDATA'][$this->id][$prow['id']] = array('id'=>$row['valmdid'],'name'=>$row['valmdname']);
            }    
            elseif ($prow['name']=='type')
            {
                $objs['SDATA'][$this->id][$prow['id']] = array('id'=>$row['typeid'],'name'=>$row['type']);
            }    
            else 
            {
                $objs['SDATA'][$this->id][$prow['id']] = array('id'=>'','name'=>$row[$prow['name']]);
            }
        }    
        return $objs;
    }
    function update($data) {
        $sql = '';
        $objs = $this->before_save($data);
        $params = array();
        foreach($objs as $row)
        {    
            $val = $row['nval'];
            if ($row['name']=='propid')
            {
                $val = $row['nvalid'];
            }   
            $sql .= ", $row[name]=:$row[name]";
            $params[$row['name']] = $val;
        }
        $objs['status']='NONE';
        if ($sql!=''){
            $objs['status']='OK';
            $sql = substr($sql,1);
            $id = $this->id;
            $sql = "UPDATE \"MDProperties\" SET$sql WHERE id=:id";
            $params['id']=$id;
            $res = DataManager::dm_query($sql,$params);
            if(!$res) {
                return array('status'=>'ERROR', 'msg'=>$sql);
            }
        }
        return array('status'=>'OK', 'id'=>$this->id);
    }
    function before_save($data) {
        $plist = $this->getplist();
        $sql = '';
        $objs = array();
        foreach ($plist as $prow)
        {    
            $key = $prow['id'];
            if ($key=='id') 
            {
                continue;
            }    
            if ($key=='mdid') 
            {
                continue;
            }    
            if ($prow['name']=='propid')
            {
                if ($this->propid==$data[$key]['id']) continue;
                $objs[]=array('name'=>$key, 'pval'=>$this->name_propid, 'nval'=>$data[$key]['name'], 'nvalid'=>$data[$key]['id']);
            }   
            else 
            {
                if ($prow['type']=='bool')
                {
                    if ($data[$key]['name']=='t')
                    {
                        $data[$key]['name']='true';
                    }
                    $data[$key]['name'] = filter_var($data[$key]['name'], FILTER_VALIDATE_BOOLEAN);
                }
                if ($this->$key == $data[$key]['name']) continue;
                $objs[]=array('name'=>$key, 'pval'=>$this->$key, 'nval'=>$data[$key]['name'], 'nvalid'=>'');
            }
        }    
	return $objs;
    }
        
    function gettype() 
    {
      return $this->propstemplate->gettype();
    }
    function get_history_data($entityid,$mode='')
    {
        $propid = $this->id;
        $type = $this->type;
        $clsid='hidden';
        if ($mode=='CONFIG')
        {
            $clsid='active';
        }    
        $plist = array(
              'id'=>array('name'=>'id','synonym'=>'ID','class'=>$clsid),
              'username'=>array('name'=>'username','synonym'=>'Пользователь','class'=>'active'),
              'dateupdate'=>array('name'=>'dateupdate','synonym'=>'Дата изменения','class'=>'active'),
              'value'=>array('name'=>'value','synonym'=>'Значение','class'=>'active')
            );
        if ($type=='id')
        {
            $sql = "SELECT it.id, it.userid, ct_user.synonym as username,it.dateupdate, pv.value as value, pv.value as id_value FROM \"IDTable\" as it INNER JOIN \"PropValue_id\" as pv INNER JOIN \"ETable\" as et ON pv.value = et.id ON it.id=pv.id 
                    LEFT JOIN \"CTable\" as ct_user ON it.userid=ct_user.id  WHERE it.propid=:propid AND it.entityid = :entityid ORDER BY it.dateupdate DESC";
        }  
        elseif ($type=='cid')
        {
            $sql = "SELECT it.id, it.userid, ct_user.synonym as username,it.dateupdate, et.synonym as value, pv.value as id_value FROM \"IDTable\" as it INNER JOIN \"PropValue_cid\" as pv INNER JOIN \"CTable\" as et ON pv.value = et.id ON it.id=pv.id 
                    LEFT JOIN \"CTable\" as ct_user ON it.userid=ct_user.id WHERE it.propid=:propid AND it.entityid = :entityid ORDER BY it.dateupdate DESC";
        }
        else
        {
            $sql = "SELECT it.id, it.userid, ct_user.synonym as username,it.dateupdate, pv.value as value, '' as id_value FROM \"IDTable\" as it INNER JOIN \"PropValue_$type\" as pv ON it.id=pv.id 
                    LEFT JOIN \"CTable\" as ct_user ON it.userid=ct_user.id WHERE it.propid=:propid AND it.entityid = :entityid ORDER BY it.dateupdate DESC";
        }    
        $res = DataManager::dm_query($sql,array('propid'=>$propid,'entityid'=>$entityid));
        if(!$res) {
            return array('status'=>'ERROR', 'msg'=>$sql);
        }
        $ardata = array();
        $arr_e=array();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            $ardata[$row['id']]=array();
            foreach($plist as $prop) 
            {
                $ardata[$row['id']][$prop['name']]=array();
                $ardata[$row['id']][$prop['name']]['id'] = '';
                $val = $row[$prop['name']];
                if ($prop['name']=='dateupdate')
                {
                    $dt = new \DateTime($row[$prop['name']]);
                    $val = $dt->format("Y-m-d h:i:s");
                }
                elseif ($prop['name']=='value') 
                {
                    if ($type=='id')
                    {
                        $ardata[$row['id']][$prop['name']]['id'] = $val;
                        if (!in_array($row['value'], $arr_e))
                        {        
                            $arr_e[]= $row['value'];
                        }    
                    }    
                }
                $ardata[$row['id']][$prop['name']]['name'] = $val;
            }
        }
        if (count($arr_e))
        {
            $arr_entities = EntitySet::getAllEntitiesToStr($arr_e);
            foreach($ardata as $id=>$row) 
            {
                if (array_key_exists($row['value']['id'], $arr_entities))
                {
                    $ardata[$id]['value']['name'] =$arr_entities[$row['value']['id']]['name'];
                }
            }
        }
        
        return array('LDATA'=>$ardata,'PSET'=>$plist, 'name'=>$this->name,'synonym'=>$this->synonym);
    } 
    function get_history($entityid,$mode='')
    {
        $propid = $this->id;
        $type = $this->type;
        $clsid='hidden';
        if ($mode=='CONFIG')
        {
            $clsid='active';
        }    
        $plist = array(
              'id'=>array('name'=>'id','synonym'=>'ID','class'=>$clsid),
              'username'=>array('name'=>'username','synonym'=>'USER NAME','class'=>'active'),
              'dateupdate'=>array('name'=>'dateupdate','synonym'=>'DATE UPDATE','class'=>'active'),
              'value'=>array('name'=>'value','synonym'=>'VALUE','class'=>'active')
            );
        $ent = new Entity($entityid);
        return array(
          'id'=>$this->id,
          'name'=>$this->name,
          'synonym'=>$this->synonym,
          'version'=>$this->version,
          'PSET' => $plist,   
          'navlist' => array(
              $this->mdentity->getid()=>$this->mdentity->getsynonym(),
              $ent->getid()=>$ent->getname(),
              $this->id=>$this->synonym
            )
          );
    } 
    public static function getProperty($propid) 
    {
        $sql = DataManager::get_select_properties(" WHERE mp.id = :propid ");
	$res = DataManager::dm_query($sql,array('propid'=>$propid));
        return $res->fetch(PDO::FETCH_ASSOC);
    }
}

