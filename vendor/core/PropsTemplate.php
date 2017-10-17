<?php

namespace tzVendor;
use PDO;
use tzVendor\DataManager;
use tzVendor\Mdentity;


class PropsTemplate extends Model {
    protected $type;
    protected $length;
    protected $prec;
    protected $valmdentity;
    
    public function __construct($id) 
    {
        
        if ($id=='')
        {
            $this->id = '';
            $this->name = '';    
            $this->synonym = '';    
            $this->type = 'str';    
            $this->length = 10;    
            $this->prec = 0;    
            $this->valmdentity = '';        
        }
        else 
        {
            $arData = self::getPropsbyID($id);
            $this->id = $id;
            $this->name = $arData['name'];    
            $this->synonym = $arData['synonym'];    
            $this->type = $arData['type'];    
            $this->length = $arData['length'];    
            $this->prec = $arData['prec'];
            if ($arData['valmdid']!='') 
            {
                $this->valmdentity = new Mdentity($arData['valmdid']);
            }    
        }
        $this->version = time();
        
    }
    function get_data($mode='') 
    {
        $Cols = Mditem::getMDitemByName('Cols');
        $arSet = CollectionSet::getMDCollectionByName('PropsTemplate');
        $valmdid = '';
        $valmdname = '';
        $valmdtype = '';
        if ($this->valmdentity!='')
        {
            $valmdid = $this->valmdentity->getid();
            $valmdtype = $this->valmdentity->gettype();
            $valmdname = $this->valmdentity->getname();
        }
        return array('id'=>$this->id,      
                    'name'=>$this->name,
                    'synonym'=>$this->synonym,
                    'version'=>$this->version,
                    'type'=>$this->type,
                    'length'=>$this->length,
                    'prec'=>$this->prec,
                    'valmdid'=>$valmdid,
                    'valmdname'=>$valmdname,
                    'valmdtype'=>$valmdtype,
                    'navlist'=>array(
                    $Cols['id']=>$Cols['synonym'],
                    $arSet['id']=>$arSet['synonym'],
                    $this->id=>$this->synonym
                    ),
                  'actionlist'=>array(
                      array('id'=>'d6563e46-759d-4c01-b7b7-0ecf40140fbb','name'=>'save','synonym'=>'Записать', 'icon'=>'save')
                    )
                    );
     }
    function update($data) 
    {
        $vars = get_object_vars($this);
        $sql = '';
        $objs = array();
        while (list($key, $value) = each($vars)) :
            if ($key=='id') 
            {
                continue;
            }    
            if (array_key_exists($key, $data))
            {
                if ($value==$data[$key]) 
                {
                    continue;
                }    
                $sql .= ", $key='$data[$key]'";
            }    
        endwhile;
        $objs['status']='NONE';
        if ($sql!=='')
        {
            $objs['status']='OK';
            $sql = substr($sql,1);
            $id = $this->id;
            $sql = "UPDATE \"PropsTable\" SET$sql WHERE id=:id";
            $res = DataManager::dm_query($sql,array('id'=>$id));
            if(!$res) 
            {
                $objs['status']='ERROR';
                $objs['msg']=$sql;
            }
        }
	return $objs;
    }
    function before_save($data) 
    {
        $vars = get_object_vars($this);
        $sql = '';
        $objs = array();
        while (list($key, $value) = each($vars)) :
            if ($key=='id') 
            {
                continue;
            }    
            if (array_key_exists($key, $data))
            {
                if ($value==$data[$key]) 
                {
                    continue;
                }    
                $objs[]=array('name'=>$key, 'pval'=>$value, 'nval'=>$data[$key]);
            }    
        endwhile;
	return $objs;
    }
    function gettype() 
    {
      return $this->type;
    }
    function getlength() 
    {
      return $this->length;
    }
    function getprec() 
    {
      return $this->prec;
    }
    function getvalmdentity() 
    {
      return $this->valmdentity;
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
    function settype($val) 
    {
	$this->type=$val;
    }
    public static function getPropsbyID($propid) 
    {
	$sql = "SELECT mp.id, mp.name, mp.synonym, pv_type.value as typeid, md_type.name as type, pv_len.value as length, pv_prec.value as prec, pv.value as valmdid, valmd.name AS valmdname FROM \"CTable\" AS mp 
		  LEFT JOIN \"CProperties\" as cp
                    INNER JOIN \"CPropValue_mdid\" as pv
                        INNER JOIN \"MDTable\" as valmd
                        ON pv.value = valmd.id
                    ON cp.id=pv.pid
                  ON mp.mdid=cp.mdid
                  and pv.id = mp.id
		  LEFT JOIN \"CProperties\" as cp_type
                    INNER JOIN \"CPropValue_cid\" as pv_type
                        INNER JOIN \"CTable\" as md_type
                        ON pv_type.value = md_type.id
                    ON cp_type.id=pv_type.pid
                  ON mp.mdid=cp_type.mdid
                  and pv_type.id = mp.id
		  LEFT JOIN \"CProperties\" as cp_len
                    INNER JOIN \"CPropValue_int\" as pv_len
                    ON cp_len.id=pv_len.pid
                  ON mp.mdid=cp_len.mdid
                  and pv_len.id = mp.id
		  LEFT JOIN \"CProperties\" as cp_prec
                    INNER JOIN \"CPropValue_int\" as pv_prec
                    ON cp_prec.id=pv_prec.pid
                  ON mp.mdid=cp_prec.mdid
                  and pv_prec.id = mp.id
		WHERE mp.id = :propid";	
	$res = DataManager::dm_query($sql,array('propid'=>$propid));
        return $res->fetch(PDO::FETCH_ASSOC);
    }
}

