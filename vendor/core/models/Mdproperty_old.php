<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

class Mdproperty_old extends Sheet implements I_Sheet, I_Property 
{
    use T_Sheet;
    use T_Item;
    use T_EProperty;
    
    public function head() 
    {
        return new Mdentity($this->mdid);
    }
    public function item() 
    {
        return NULL;
    }
    public function loadProperties()
    {
        $this->properties = array(
            'id'=>array('id'=>'id','name'=>'id','synonym'=>'ID','rank'=>0,'ranktoset'=>1,'ranktostring'=>0,'type'=>'str','valmdtypename'=>'','class'=>'hidden','field'=>1),
            'name'=>array('id'=>'name','name'=>'name','synonym'=>'NAME','rank'=>1,'ranktoset'=>2,'ranktostring'=>1,'type'=>'str','valmdtypename'=>'','class'=>'active','field'=>1),
            'synonym'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','rank'=>3,'ranktoset'=>3,'ranktostring'=>0,'type'=>'str','valmdtypename'=>'','class'=>'active','field'=>1),
            'propid'=>array('id'=>'propid','name'=>'propid','synonym'=>'PROPID','rank'=>2,'ranktoset'=>0,'ranktostring'=>0,'type'=>'cid','valmdtypename'=>'','class'=>'active','field'=>1),
            'name_propid'=>array('id'=>'name_propid','name'=>'name_propid','synonym'=>'NAME_PROPID','rank'=>2,'ranktoset'=>0,'ranktostring'=>0,'type'=>'str','valmdtypename'=>'','class'=>'hidden','field'=>0),
            'type'=>array('id'=>'type','name'=>'type','synonym'=>'TYPE','rank'=>2,'ranktoset'=>5,'ranktostring'=>0,'type'=>'str','valmdtypename'=>'','class'=>'readonly','field'=>0),
            'length'=>array('id'=>'length','name'=>'length','synonym'=>'LENGTH','rank'=>5,'ranktoset'=>0,'ranktostring'=>0,'type'=>'int','valmdtypename'=>'','class'=>'active','field'=>1),
            'prec'=>array('id'=>'prec','name'=>'prec','synonym'=>'PREC','rank'=>6,'ranktoset'=>0,'ranktostring'=>0,'type'=>'int','valmdtypename'=>'','class'=>'active','field'=>1),
            'rank'=>array('id'=>'rank','name'=>'rank','synonym'=>'RANK','rank'=>7,'ranktoset'=>0,'ranktostring'=>0,'type'=>'int','valmdtypename'=>'','class'=>'active','field'=>1),
            'ranktoset'=>array('id'=>'ranktoset','name'=>'ranktoset','synonym'=>'RANKTOSET','rank'=>8,'ranktoset'=>0,'ranktostring'=>0,'type'=>'int','valmdtypename'=>'','class'=>'active','field'=>1),
            'ranktostring'=>array('id'=>'ranktostring','name'=>'ranktostring','synonym'=>'RANKTOSTRING','rank'=>9,'ranktoset'=>0,'ranktostring'=>0,'type'=>'int','valmdtypename'=>'','class'=>'active','field'=>1),
            'isedate'=>array('id'=>'isedate','name'=>'isedate','synonym'=>'ISEDATE','rank'=>13,'ranktoset'=>0,'ranktostring'=>0,'type'=>'bool','valmdtypename'=>'','class'=>'active','field'=>1),
            'isenumber'=>array('id'=>'isenumber','name'=>'isenumber','synonym'=>'ISENUMBER','rank'=>14,'ranktoset'=>0,'ranktostring'=>0,'type'=>'bool','valmdtypename'=>'','class'=>'active','field'=>1),
            'isdepend'=>array('id'=>'isdepend','name'=>'isdepend','synonym'=>'IS DEPENDENT','rank'=>15,'ranktoset'=>0,'ranktostring'=>0,'type'=>'bool','valmdtypename'=>'','class'=>'active','field'=>1),
            'valmdid' => array('id'=>'valmdid','name'=>'VALMDID','synonym'=>'VALMDID','rank'=>19,'ranktoset'=>0,'ranktostring'=>0,'type'=>'mdid','valmdtypename'=>'','class'=>'readonly','field'=>0),
            'name_valmdid' => array('id'=>'name_valmdid','name'=>'NAME_VALMDID','synonym'=>'NAME_VALMDID','rank'=>20,'ranktoset'=>8,'ranktostring'=>0,'type'=>'str','valmdtypename'=>'','class'=>'hidden','field'=>0),
            'valmdtypename' => array('id'=>'valmdtypename','name'=>'VALMDTYPENAME','synonym'=>'VALMDTYPENAME','rank'=>21,'ranktoset'=>9,'ranktostring'=>0,'type'=>'str','valmdtypename'=>'','class'=>'readonly','field'=>0),
            'field' => array('id'=>'field','name'=>'field','synonym'=>'FIELD','rank'=>25,'ranktoset'=>0,'ranktostring'=>0,'type'=>'int','valmdtypename'=>'','class'=>'hidden','field'=>0),
             );
        return count($this->properties);
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
    public function getProperty($propid) 
    {
        $sql = DataManager::get_select_properties(" WHERE mp.id = :propid ");
	$res = DataManager::dm_query($sql,array('propid'=>$propid));
        return $res->fetch(PDO::FETCH_ASSOC);
    }
}

