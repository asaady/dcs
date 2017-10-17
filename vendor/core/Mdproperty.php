<?php
namespace tzVendor;
use PDO;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/tz_const.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/tz_common.php");

class Mdproperty extends Model {
    protected $mdentity;
    protected $propstemplate;
    protected $type;
    protected $typeid;
    protected $length;
    protected $prec;
    protected $propid;
    protected $name_propid;
    protected $rank;
    protected $ranktostring;
    protected $ranktoset;
    protected $isedate;
    protected $isenumber;
    protected $valmdid;
    protected $name_valmdid;
    protected $valmdtypename;
    
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
        }
        $this->mdentity = new Mdentity($mdid);
        $this->version=time();
    }
    function getmdentity()
    {
        return $this->mdentity;
    }
    function getpropstemplate()
    {
        return $this->propstemplate;
    }
    function get_data($mode='') 
    {
        return array('id'=>$this->id,      
                    'version'=>$this->version,
                    'PLIST'=>array( 
                        array('id'=>'id','name'=>'id','synonym'=>'ID','rank'=>0,'type'=>'str','valmdid'=>TZ_EMPTY_ENTITY,'valmdtypename'=>TZ_TYPE_EMPTY,'class'=>'hidden'),
                        array('id'=>'name','name'=>'name','synonym'=>'NAME','rank'=>1,'type'=>'str','valmdid'=>TZ_EMPTY_ENTITY,'valmdtypename'=>TZ_TYPE_EMPTY,'class'=>'active'),
                        array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','rank'=>3,'type'=>'str','valmdid'=>TZ_EMPTY_ENTITY,'valmdtypename'=>TZ_TYPE_EMPTY,'class'=>'active'),
                        array('id'=>'propid','name'=>'propid','synonym'=>'PROPID','rank'=>2,'type'=>'cid','valmdid'=>$this->propid,'valmdtypename'=>TZ_TYPE_EMPTY,'class'=>'active'),
                        array('id'=>'length','name'=>'length','synonym'=>'LENGTH','rank'=>5,'type'=>'str','valmdid'=>TZ_EMPTY_ENTITY,'valmdtypename'=>TZ_TYPE_EMPTY,'class'=>'active'),
                        array('id'=>'prec','name'=>'prec','synonym'=>'PREC','rank'=>6,'type'=>'str','valmdid'=>TZ_EMPTY_ENTITY,'valmdtypename'=>TZ_TYPE_EMPTY,'class'=>'active'),
                        array('id'=>'rank','name'=>'rank','synonym'=>'RANK','rank'=>7,'type'=>'str','valmdid'=>TZ_EMPTY_ENTITY,'valmdtypename'=>TZ_TYPE_EMPTY,'class'=>'active'),
                        array('id'=>'ranktoset','name'=>'ranktoset','synonym'=>'RANKTOSET','rank'=>8,'type'=>'str','valmdid'=>TZ_EMPTY_ENTITY,'valmdtypename'=>TZ_TYPE_EMPTY,'class'=>'active'),
                        array('id'=>'ranktostring','name'=>'ranktostring','synonym'=>'RANKTOSTRING','rank'=>9,'type'=>'str','valmdid'=>TZ_EMPTY_ENTITY,'valmdtypename'=>TZ_TYPE_EMPTY,'class'=>'active'),
                        array('id'=>'isedate','name'=>'isedate','synonym'=>'ISEDATE','rank'=>13,'type'=>'bool','valmdid'=>TZ_EMPTY_ENTITY,'valmdtypename'=>TZ_TYPE_EMPTY,'class'=>'active'),
                        array('id'=>'isenumber','name'=>'isenumber','synonym'=>'ISENUMBER','rank'=>14,'type'=>'bool','valmdid'=>TZ_EMPTY_ENTITY,'valmdtypename'=>TZ_TYPE_EMPTY,'class'=>'active')
                    ),
                    'navlist'=>array(
                    $this->mdentity->getmditem()=>$this->mdentity->getmditemsynonym(),
                    $this->mdentity->getid()=>$this->mdentity->getsynonym(),
                    $this->id=>$this->synonym
                    )
              );

    }
    function getplist() 
    {
        return array(
            'id'=>array('id'=>'id','name'=>'id','synonym'=>'ID','rank'=>0,'type'=>'str','valmdtype'=>TZ_TYPE_EMPTY,'class'=>'hidden'),
            'name'=>array('id'=>'name','name'=>'name','synonym'=>'NAME','rank'=>1,'type'=>'str','valmdtype'=>TZ_TYPE_EMPTY,'class'=>'active'),
            'propid'=>array('id'=>'propid','name'=>'propid','synonym'=>'PROPID','rank'=>2,'type'=>'cid','valmdtype'=>TZ_TYPE_EMPTY,'class'=>'active'),
            'synonym'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','rank'=>3,'type'=>'str','valmdtype'=>TZ_TYPE_EMPTY,'class'=>'active'),
            'length'=>array('id'=>'length','name'=>'length','synonym'=>'LENGTH','rank'=>5,'type'=>'int','valmdtype'=>TZ_TYPE_EMPTY,'class'=>'active'),
            'prec'=>array('id'=>'prec','name'=>'prec','synonym'=>'PREC','rank'=>6,'type'=>'int','valmdtype'=>TZ_TYPE_EMPTY,'class'=>'active'),
            'rank'=>array('id'=>'rank','name'=>'rank','synonym'=>'RANK','rank'=>7,'type'=>'int','valmdtype'=>TZ_TYPE_EMPTY,'class'=>'active'),
            'ranktoset'=>array('id'=>'ranktoset','name'=>'ranktoset','synonym'=>'RANKTOSET','rank'=>8,'type'=>'int','valmdtype'=>TZ_TYPE_EMPTY,'class'=>'active'),
            'ranktostring'=>array('id'=>'ranktostring','name'=>'ranktostring','synonym'=>'RANKTOSTRING','rank'=>9,'type'=>'int','valmdtype'=>TZ_TYPE_EMPTY,'class'=>'active'),
            'isedate'=>array('id'=>'isedate','name'=>'isedate','synonym'=>'ISEDATE','rank'=>13,'type'=>'bool','valmdtype'=>TZ_TYPE_EMPTY,'class'=>'active'),
            'isenumber'=>array('id'=>'isenumber','name'=>'isenumber','synonym'=>'ISENUMBER','rank'=>14,'type'=>'bool','valmdtype'=>TZ_TYPE_EMPTY,'class'=>'active')
                   );
    }
            
    function load_data($mode,$edit_mode) 
    {
        $objs = array();
        $objs['PLIST']=$this->getplist();
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
    public static function getPropertyByName($propname,$mdid) 
    {
        $sql = DataManager::get_select_properties(" WHERE md.id = :mdid AND (name ILIKE :filter OR synonym ILIKE :filter)");
        $params = array('filter'=>"%$propname%",'mdid'=>$mdid);
        $sql .= " LIMIT 5";
        $sth = DataManager::dm_query($sql,$params);
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function IsExistTheProp($mdid,$propid) 
    {
	$sql = "SELECT 	mp.name, mp.id, mp.synonym, mp.rank FROM \"MDProperties\" as mp
		WHERE mp.mdid=:mdid and mp.propid=:propid";	
	$res = DataManager::dm_query($sql,array('mdid'=>$mdid,'propid'=>$propid));
	return $res->fetch(PDO::FETCH_ASSOC);
    }
    function create($data) 
    {
        $plist = self::getplist();
        $fname='mdid';
        $fval=':mdid';
        $params = array();
        $params['mdid'] = $this->mdentity->getid();
        foreach ($plist as $prop)
        {
            $key = $prop['id'];
            if ($key=='id')
            {
                continue;
            }    
            if (array_key_exists($key,$data))
            {
                $val = $data[$key]['name'];
                if (($prop['type']=='id')||($prop['type']=='cid')||($prop['type']=='mdid'))
                {
                    $val = $data[$key]['id'];
                }    
                if ($val == '')
                {
                    continue;
                }    
                $fname .=", $key";
                $fval .=", :$key";
                $params[$key]=$val;
            }        
        }    
	$sql = "INSERT INTO \"MDProperties\" ($fname) VALUES ($fval) RETURNING \"id\"";
	$res = DataManager::dm_query($sql,$params);
        return $res->fetch(PDO::FETCH_ASSOC);
    }
    public static function createMDProperty($data) 
    {
        $fname='';
        $fval='';
        $params = array();
        foreach ($data as $key=>$val)
        {
            $fname .=", $key";
            $fval .=", :$key";
            $params[$key]=$val;
        }    
        $fname = substr($fname, 1);
        $fval = substr($fval, 1);
	$sql = "INSERT INTO \"MDProperties\" ($fname) VALUES ($fval) RETURNING \"id\"";
	$res = DataManager::dm_query($sql,$params);
        return $res->fetch(PDO::FETCH_ASSOC);
    }
    public static function CreateMustBeProperty($type, $mdid)
    {
        $arMB = self::getMustBePropsUse($type);
        if (count($arMB)) 
        {
            foreach($arMB as $mdprop) {
                if(self::IsExistTheProp($mdid,$mdprop['propid']))
                {        
                    continue;
                }
                $arMDProperty = array(
                              'ID'=>'',
                              'NAME'=>$mdprop['name'],
                              'SYNONYM'=>$mdprop['synonym'],
                              'TYPE'=>$mdprop['type'],
                              'MDID'=>$mdid,
                              'PROPID'=>$mdprop['propid'],
                              'RANK'=>1,
                              'LENGTH'=>$mdprop['length'],
                              'PREC'=>$mdprop['prec'],
                              'RANKTOSTRING'=>0,
                              'ISEDATE'=>'false',
                              'ISENUMBER'=>'false',
                              'VALMDID'=>$mdprop['valmdid'],
                              'VALMDNAME'=>$mdprop['valmdname'],
                              'VALMDSYNONYM'=>$mdprop['valmdsynonym']
                              );
              if ($mdprop['name']=='Name'){
                $arMDProperty['RANKTOSTRING'] = 1;
              }  
              if ($mdprop['name']=='Date'){
                $arMDProperty['ISEDATE'] = 'true';
              }  
              if ($mdprop['name']=='Number'){
                $arMDProperty['ISENUMBER'] = 'true';
              }  
              if ($mdprop['name']=='Activity'){
                $arMDProperty['RANK'] = 0;
              }  
              if ($mdprop['type']=='id'){
                if ($arMDProperty['VALMDID']=='')
                  $arMDProperty['VALMDID']=TZ_EMPTY_ENTITY;
              }
              $res = self::createMDProperty($arMDProperty);
          }
        }
    }
}

