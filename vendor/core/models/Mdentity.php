<?php
namespace Dcs\Vendor\Core\Models;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

use PDO;

class Mdentity extends Sheet implements I_Sheet
{
    use T_Sheet;
    use T_Mdentity;
    
    public function txtsql_forDetails() 
    {
        return "SELECT mdt.id, mdt.name, mdt.synonym, mdt.mditem, "
                    . "NULL as mdid, '' as mdname, '' as mdsynonym, "
                    . "mdi.name as mdtypename, "
                    . "mdi.synonym as mdtypedescription "
                    . "FROM \"MDTable\" AS mdt "
                        . "INNER JOIN \"CTable\" AS mdi "
                        . "ON mdt.mditem=mdi.id "
                    . "WHERE mdt.id= :id";
    }
    public function getArrayNew($newobj)
    {
        return array('id' => $newobj['id'], 
                    'name' => '_new_',
                    'synonym' => 'Новый',
                    'mdid' => $newobj['id'],
                    'mdname' => $newobj['classname'],
                    'mdsynonym' => '',
                    'mditem' => $newobj['headid'],
                    'mdtypename' => '',
                    'mdtypedescription' => '');
    }        
    public function head() 
    {
        return new MdentitySet($this->mditem);
    }
    public function item($id='') 
    {
        if ($this->mdtypename == 'Cols') {
            return new Cproperty($id,$this);
        }
        if ($this->mdtypename == 'Regs') {
            return new Rproperty($id,$this);
        };
        return new EProperty($id,$this);
    }
    public function txtsql_property($parname)
    {
        return NULL;
    }        
    public function txtsql_properties($parname)
    {
        return NULL;
    }        
    public function get_select_properties($strwhere)
    {
        return NULL;
    }        
    public function getprop_classname()
    {
        return NULL;
    }
    public function item_classname()
    {
        return 'EProperty';
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
//    function update($data) 
//    {
//        $sql = '';
//        $objs = array();
//        $params = array();
//        if (array_key_exists('name', $data))
//        {
//            if ($this->name!=$data['name']['name']) 
//            {
//                $sql .= ", name=:name";
//                $params['name']=$data['name']['name'];
//            }    
//        }    
//        if (array_key_exists('synonym', $data))
//        {
//            if ($this->synonym!=$data['synonym']['name']) 
//            {
//                $sql .= ", synonym=:synonym";
//                $params['synonym']=$data['synonym']['name'];
//            }    
//        }    
//        $objs['status']='NONE';
//        if ($sql!='')
//        {
//            $objs['status']='OK';
//            $sql = substr($sql,1);
//            $sql = "UPDATE \"MDTable\" SET$sql WHERE id=:id";
//            $params['id']=$this->id;
//            $res = DataManager::dm_query($sql,$params);
//            if(!$res) 
//            {
//                $objs['status']='ERROR';
//                $objs['msg']=$sql;
//            }
//        }
//        $objs['id']=$this->id;
//	return $objs;
//    }
    /*
     * Создание свойства метаданного
     */
    function create_property($data) 
    {
        if (($this->mdentityset->getname()=='Cols')||($this->mdentityset->getname()=='Comps')||($this->mdentityset->getname()=='Regs'))
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
    function before_save($data='') 
    {
        if (!$data) {
            $context = DcsContext::getcontext();
            $data = $context->getattr('DATA');
        }    
        if (array_key_exists('name', $data))
        {
            if ($this->name != $data['name']['name']) 
            {
                $objs[]=array('id'=>'name', 'name'=>'Name',
                    'pval'=>$this->name, 'nval'=>$data['name']['name']);
            }    
        }    
        if (array_key_exists('synonym', $data))
        {
            if ($this->synonym != $data['synonym']['name']) 
            {
                $objs[]=array('id'=>'name', 'name'=>'Synonym',
                    'pval'=>$this->synonym, 'nval'=>$data['synonym']['name']);
            }    
        }    
	return $objs;
    }
    public function create_object($name,$synonym='') 
    {
        if (!$this->mdid) {
            throw new DcsException("Class ".get_called_class().
                " create_object: mdid is empty",DCS_ERROR_WRONG_PARAMETER);
        }
        if (!$this->id) {
            throw new DcsException("Class ".get_called_class().
                " create_object: id is empty",DCS_ERROR_WRONG_PARAMETER);
        }
        if (!$name) {
            throw new DcsException("Class ".get_called_class().
                " create_object: name is empty",DCS_ERROR_WRONG_PARAMETER);
        }
        if (!$synonym) {
            $synonym = $name;
        }
        $sql = "INSERT INTO \"MDTable\" (id, mditem, name, synonym) "
                    . "VALUES (:id, :mditem, :name, :synonym) RETURNING \"id\"";
        $params = array();
        $params['id']= $this->id;
        $params['mditem']= $this->mditem;
        $params['name']= $name;
        $params['synonym']= $synonym;
      	DataManager::dm_beginTransaction();
        $res = DataManager::dm_query($sql,$params); 
        if ($res) {
            $rowid = $res->fetch(PDO::FETCH_ASSOC);
            DataManager::dm_commit();
            return $rowid['id'];
        }
        DataManager::dm_rollback();
        throw new DcsException("Class ".get_called_class().
            " create_object: unable to create new record",DCS_ERROR_WRONG_PARAMETER);
    }
    public function getNameFromData($data='')
    {
        if (!$data) {
            return array('name' => $this->name, 'synonym' => $this->synonym);
        } else {
            return array('name' => $data['name']['name'],
                         'synonym' => $data['synonym']['name']);
        }    
    }        
    public function loadProperties() 
    {
        return array(
            'id'=>array('id'=>'id','name'=>'id','synonym'=>'ID',
                        'rank'=>0,'ranktoset'=>1,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>1),
            'name'=>array('id'=>'name','name'=>'name','synonym'=>'NAME',
                        'rank'=>1,'ranktoset'=>2,'ranktostring'=>1,
                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            'synonym'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM',
                        'rank'=>3,'ranktoset'=>3,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            'propid'=>array('id'=>'propid','name'=>'propid','synonym'=>'PROPID',
                        'rank'=>4,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
            'name_propid'=>array('id'=>'name_propid','name'=>'name_propid','synonym'=>'NAME_PROPID',
                        'rank'=>5,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
            'name_type'=>array('id'=>'name_type','name'=>'name_type','synonym'=>'TYPE',
                        'rank'=>6,'ranktoset'=>5,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
            'type'=>array('id'=>'type','name'=>'type','synonym'=>'TYPEID',
                        'rank'=>7,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
            'rank'=>array('id'=>'rank','name'=>'rank','synonym'=>'RANK',
                        'rank'=>8,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            'ranktoset'=>array('id'=>'ranktoset','name'=>'ranktoset','synonym'=>'RANKTOSET',
                        'rank'=>9,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            'ranktostring'=>array('id'=>'ranktostring','name'=>'ranktostring','synonym'=>'RANKTOSTRING',
                        'rank'=>10,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            'valmdid' => array('id'=>'valmdid','name'=>'VALMDID','synonym'=>'VALMDID',
                        'rank'=>19,'ranktoset'=>8,'ranktostring'=>0,
                        'name_type'=>'mdid','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
            'name_valmdid' => array('id'=>'name_valmdid','name'=>'NAME_VALMDID','synonym'=>'NAME_VALMDID',
                        'rank'=>20,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>0),
            'valmdtypename' => array('id'=>'valmdtypename','name'=>'VALMDTYPENAME','synonym'=>'VALMDTYPENAME',
                        'rank'=>21,'ranktoset'=>9,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
            'field' => array('id'=>'field','name'=>'field','synonym'=>'FIELD',
                        'rank'=>25,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'hidden','field'=>0),
             );
    }        
    public function get_items() 
    {
        $sql = "SELECT mp.id, mp.propid, pr.name as name_propid, mp.name, mp.synonym, 
            pst.value as type, pt.name as name_type, mp.length, mp.prec, mp.mdid, 
            mp.rank, mp.ranktostring, mp.ranktoset, mp.isedate, mp.isenumber, 
            mp.isdepend, pmd.value as valmdid, valmd.name AS name_valmdid, 
            valmd.synonym AS valmdsynonym, valmd.mditem as valmditem, 
            mi.name as valmdtypename, 1 as field,'active' as class FROM \"MDProperties\" AS mp
		  LEFT JOIN \"CTable\" as pr
		    LEFT JOIN \"CPropValue_mdid\" as pmd
        		INNER JOIN \"MDTable\" as valmd
                            INNER JOIN \"CTable\" as mi
                            ON valmd.mditem = mi.id
                        ON pmd.value = valmd.id
		    ON pr.id = pmd.id
		    LEFT JOIN \"CPropValue_cid\" as pst
                        INNER JOIN \"CProperties\" as cprs
                        ON pst.pid = cprs.id
                        AND cprs.name='type'
                        INNER JOIN \"CTable\" as pt
                        ON pst.value = pt.id
		    ON pr.id = pst.id
		  ON mp.propid = pr.id
		WHERE mp.mdid = :mdid
		ORDER BY rank";
        $sth = DataManager::dm_query($sql,array('mdid'=>$this->id));        
        $objs = array();
        while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $objs[$row['id']] = $row;
        }
        return $objs;
    }
    public function update_dependent_properties($data)
    {
        return array('objs'=>null);
    }        
    public function update_properties($data,$n=0)     
    {
        $sql = '';
        $objs = array();
        $params = array();
        if (array_key_exists('name', $data))
        {
            if ($this->name != $data['name']['name']) 
            {
                $sql .= ", name=:name";
                $params['name'] = $data['name']['name'];
            }    
        }    
        if (array_key_exists('synonym', $data))
        {
            if ($this->synonym != $data['synonym']['name']) 
            {
                $sql .= ", synonym=:synonym";
                $params['synonym'] = $data['synonym']['name'];
            }    
        }    
        $status = 'NONE';
        if ($sql != '')
        {
            $status='OK';
            $sql = substr($sql,1);
            $sql = "UPDATE \"MDTable\" SET$sql WHERE id=:id";
            $params['id'] = $this->id;
            DataManager::dm_beginTransaction();
            $res = DataManager::dm_query($sql,$params);
            if(!$res) 
            {
                $status='ERROR';
                DataManager::dm_rollback();            
            } else {
                DataManager::dm_commit();            
            }
        }
        return array('status'=>$status, 'id'=>$this->id, 'objs'=>array());
    }        
}
