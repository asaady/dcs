<?php
namespace tzVendor;
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");
use PDO;

class Mdentity extends Model 
{
    protected $mdentityset;
    protected $comptype; //component type 
    protected $version;
    function __construct($id) 
    {
        $arData = self::getMD($id);
        
        if ($arData) 
        {
            $this->id = $id;
            $this->name = $arData['name'];    
            $this->synonym = $arData['synonym'];    
            $this->comptype = $arData['id_comptype'];    
            $mditem = $arData['mditem'];        
        }
        else 
        {    
            $this->id = '';
            $this->name = '';    
            $this->synonym = '';    
            $this->comptype = TZ_EMPTY_ENTITY;    
            $mditem = $id;        
	}
        $this->version=time();
        $this->mdentityset = new MdentitySet($mditem);
        
/*
 */
         
 }
    
    function get_data($mode='') 
    {
        if ($this->id)
        {
            $navlist = array($this->mdentityset->getid()=>$this->mdentityset->getsynonym(),$this->id=>$this->synonym);
        }
        else
        {
            $navlist = array($this->mdentityset->getid()=>$this->mdentityset->getsynonym(),'new'=>'Новый');
        }    
        
        $plist= self::getMDprop();
        return array('id'=>$this->id,      
                'name'=>$this->name,
                'synonym'=>$this->synonym,
                'version'=>$this->version,
                'PSET' => $plist,   
                'navlist'=>$navlist
              );

    }
    function getarProps($mode='') 
    {
        if ($this->id)
        {    
            return MdpropertySet::getMDProperties($this->id,$mode," WHERE mp.mdid = :mdid ",true);
        }
        else
        {
            return MdpropertySet::getMustBePropsUse($this->mdentityset->getid());
        }    
    }
    public static function getMDprop()
    {
        return array(
             'id'=>array('name'=>'id','synonym'=>'ID','class'=>'active'),
             'name'=>array('name'=>'name','synonym'=>'NAME','class'=>'active'),
             'synonym'=>array('name'=>'synonym','synonym'=>'SYNONYM','class'=>'active'),
            );
    }
    function getPropData($mode='',$edit_mode='') 
    {
        $objs = array();
        $objs['id'] = $this->id;
        $objs['name'] = $this->name;
        $objs['synonym'] = $this->synonym;
        $objs['version'] = $this->version;
        $objs['LDATA']=array();
        $objs['PSET'] = self::getMDprop();
        $objs['actionlist'] = DataManager::getActionsbyItem('Mdentity',$mode,$edit_mode);
        if ($this->id=='')
        {
            $objs['navlist'] = array(   $this->mdentityset->getid()=>$this->mdentityset->getsynonym(),
                                    $this->id=>'Новый');
        
            return $objs;
        }    
        $objs['navlist'] = array(   $this->mdentityset->getid()=>$this->mdentityset->getsynonym(),
                                    $this->id=>$this->synonym);
        
        $sql = DataManager::get_select_properties(" WHERE mp.mdid = :mdid ");	
	$res = DataManager::dm_query($sql,array('mdid'=>$this->id));
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            $objs['LDATA'][$row['id']] = array();
            foreach ($objs['PSET'] as $pkey=>$prow)
            {
                $objs['LDATA'][$row['id']][$pkey]=array('name'=>$row[$prow['name']],'id'=>'');
            }    
        }
        if (!count($objs['LDATA']))
        {
            $sql = DataManager::get_select_cproperties(" WHERE mp.mdid = :mdid ");	
            $res = DataManager::dm_query($sql,array('mdid'=>$this->id));
            $objs['LDATA'] = array();
            $objs['LDATA']['id'] = array();
            $objs['LDATA']['id']['id']=array('name'=>'id','id'=>'id');
            $objs['LDATA']['id']['name']=array('name'=>'id','id'=>'id');
            $objs['LDATA']['id']['synonym']=array('name'=>'ID','id'=>'id');
            $objs['LDATA']['name'] = array();
            $objs['LDATA']['name']['id']=array('name'=>'name','id'=>'name');
            $objs['LDATA']['name']['name']=array('name'=>'name','id'=>'name');
            $objs['LDATA']['name']['synonym']=array('name'=>'NAME','id'=>'name');
            $objs['LDATA']['synonym'] = array();
            $objs['LDATA']['synonym']['id']=array('name'=>'synonym','id'=>'synonym');
            $objs['LDATA']['synonym']['name']=array('name'=>'synonym','id'=>'synonym');
            $objs['LDATA']['synonym']['synonym']=array('name'=>'SYNONYM','id'=>'synonym');
            while($row = $res->fetch(PDO::FETCH_ASSOC)) 
            {
                $objs['LDATA'][$row['id']] = array();
                foreach ($objs['PSET'] as $pkey=>$prow)
                {
                    $objs['LDATA'][$row['id']][$pkey]=array('name'=>$row[$prow['name']],'id'=>'');
                }    
            }
        }
        return $objs;
    }
    function gettype() 
    {
      return $this->mdentityset->getname();
    }
    function getmditem() 
    {
      return $this->mdentityset->getid();
    }
    function getmditemsynonym() 
    {
      return $this->mdentityset->getsynonym();
    }
    function getmdtypename() 
    {
      return $this->mdentityset->getname();
    }
    function getmdtypedescription() 
    {
      return $this->mdentityset->getsynonym();
    }
    function getisc() 
    {
      return $this->mdentityset->getisc();
    }
    function setid($val) 
    {
        if ($this->id=='')
        {    
            $this->id=$val;
        }  
        else
        {    
            throw new Exception('You may not alter the value of the ID field!');
        }    
    }
    function setname($name) 
    {
	$this->name=$name;
    }
    function setsynonym($val) 
    {
	$this->synonym=$val;
    }
    function settype($val) 
    {
	$this->type=$val;
    }
    function setmditem($val) 
    {
	$this->setmditem=$val;
    }
    function setmdtypename($val) 
    {
	$this->mdtypename=$val;
    }
    function setmdtypedescription($val) 
    {
	$this->mdtypedescription=$val;
    }
    public static function getMD($mdid) 
    {
	$sql = "SELECT mdt.id, mdt.name, mdt.synonym, mdt.mditem, mdi.name as mdtypename, mdi.synonym as mdtypedescription, mdt.comptype as id_comptype, mdc.name as name_comptype FROM \"MDTable\" AS mdt "
                . "INNER JOIN \"CTable\" AS mdi "
                . "ON mdt.mditem=mdi.id "
                . "INNER JOIN \"CTable\" AS mdc "
                . "ON mdt.comptype=mdc.id "
                . "WHERE mdt.id= :mdid";
        $sth = DataManager::dm_query($sql,array('mdid'=>$mdid));        
	return $sth->fetch(PDO::FETCH_ASSOC);
    }
    
    function update($data) 
    {
        $sql = '';
        $objs = array();
        $params = array();
        if (array_key_exists('name', $data))
        {
            if ($this->name!=$data['name']['name']) 
            {
                $sql .= ", name=:name";
                $params['name']=$data['name']['name'];
            }    
        }    
        if (array_key_exists('synonym', $data))
        {
            if ($this->synonym!=$data['synonym']['name']) 
            {
                $sql .= ", synonym=:synonym";
                $params['synonym']=$data['synonym']['name'];
            }    
        }    
        if (array_key_exists('comptype', $data))
        {
            if ($this->comptype!=$data['comptype']['name']) 
            {
                $sql .= ", synonym=:synonym";
                $params['synonym']=$data['synonym']['name'];
            }    
        }    
        $objs['status']='NONE';
        if ($sql!='')
        {
            $objs['status']='OK';
            $sql = substr($sql,1);
            $sql = "UPDATE \"MDTable\" SET$sql WHERE id=:id";
            $params['id']=$this->id;
            $res = DataManager::dm_query($sql,$params);
            if(!$res) 
            {
                $objs['status']='ERROR';
                $objs['msg']=$sql;
            }
        }
        $objs['id']=$this->id;
	return $objs;
    }
    /*
     * Создание свойства метаданного
     */
    function create_property($data) 
    {
        if (($this->mdentityset->getname()=='Cols')||($this->mdentityset->getname()=='Comps'))
        {
            $props= array(
                'id'=>array('name'=>'id','type'=>'str'),
                'name'=>array('name'=>'name','type'=>'str'),
                'synonym'=>array('name'=>'synonym','type'=>'str'),
                'type'=>array('name'=>'type','type'=>'str'),
                'length'=>array('name'=>'length','type'=>'int'),
                'prec'=>array('name'=>'prec','type'=>'int'),
                'rank'=>array('name'=>'rank','type'=>'int'),
                'ranktoset'=>array('name'=>'ranktoset','type'=>'int'),
                'valmdid'=>array('name'=>'valmdid','type'=>'mdid')
            );
            $dbtable = 'CProperties';
        }    
        else 
        {
            $props= array(
                'id'=>array('name'=>'id','type'=>'str'),
                'name'=>array('name'=>'name','type'=>'str'),
                'synonym'=>array('name'=>'synonym','type'=>'str'),
                'type'=>array('name'=>'type','type'=>'cid'),
                'length'=>array('name'=>'length','type'=>'int'),
                'prec'=>array('name'=>'prec','type'=>'int'),
                'rank'=>array('name'=>'rank','type'=>'int'),
                'ranktostring'=>array('name'=>'ranktostring','type'=>'int'),
                'ranktoset'=>array('name'=>'ranktoset','type'=>'int'),
                'isedate'=>array('name'=>'isedate','type'=>'bool'),
                'valmdid'=>array('name'=>'valmdid','type'=>'mdid'),
                'propid'=>array('name'=>'propid','type'=>'mdid')
            );
            $dbtable = 'MDProperties';
        }
        $sql='';
        $objs = array();
        $fname ='';
        $fval = '';
        $params=array();
        foreach ($props as $key=>$prop)
        {    
            if ($key=='id') 
            {
                continue;
            }    
            if (array_key_exists($key, $data))
            {
                if ($prop['type']=='mdid')
                {    
                    $par=$data[$prop['name']]['id'];
                } 
                elseif ($prop['type']=='cid')
                {    
                    $par=$data[$prop['name']]['id'];
                } 
                else
                {
                    $par=$data[$prop['name']]['name'];
                }
                if ($par=='')
                {
                    continue;
                }    
                $params[$prop['name']]= $par;    
                $fname .=", $prop[name]";
                $fval .=", :$prop[name]";
            }    
        }
        $fname = substr($fname,1);
        $fval = substr($fval,1);
        $objs['status']='NONE';
        if ($fname!='')
        {
            $objs['status']='OK';
            $params['id']=$this->id;
            $sql = "INSERT INTO \"$dbtable\" ($fname, mdid) VALUES ($fval,:id) RETURNING \"id\";";
            $res = DataManager::dm_query($sql,$params);
            if(!$res) 
            {
                $objs['status']='ERROR';
                $objs['msg']=$sql;
            }
            else 
            {
                $row = $res->fetch(PDO::FETCH_ASSOC);
                $objs['id']= $row['id'];
            }
        }
	return $objs;
    }
    function create($data) 
    {
        $entity = new Entity($this->id);
        $entity->set_data($data);
        return $entity->createNew();
    }
    function before_save($data) 
    {
        if (array_key_exists('name', $data))
        {
            if ($this->name!=$data['name']['name']) 
            {
                $objs[]=array('name'=>'Name', 'pval'=>$this->name, 'nval'=>$data['name']['name']);
            }    
        }    
        if (array_key_exists('synonym', $data))
        {
            if ($this->synonym!=$data['synonym']['name']) 
            {
                $sql .= ", synonym=:synonym";
                $objs[]=array('name'=>'Synonym', 'pval'=>$this->synonym, 'nval'=>$data['synonym']['name']);
            }    
        }    
	return $objs;
    }
    public static function getMDbyName($filter,$mditem='') 
    {
        $params = array('filter'=>"%$filter%");
        $sql = "SELECT id, name, synonym, mditem FROM \"MDTable\" WHERE name ILIKE :filter or synonym ILIKE :filter";
	if ($mditem!='') 
        {
	  $sql .= " AND mditem=:mditem";
          $params['mditem'] = $mditem;
	}
        $sql .= " LIMIT 5";
        $sth = DataManager::dm_query($sql,$params);
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }
}
