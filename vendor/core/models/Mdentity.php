<?php
namespace Dcs\Vendor\Core\Models;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

use PDO;

class Mdentity extends Sheet implements I_Sheet, I_Set, I_Property
{
    use T_Sheet;
    use T_MdSet;
    use T_Mdproperty;
    use T_EProperty;
    public static function txtsql_forDetails() 
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
    public function head() 
    {
        return new MdentitySet($this->mditem);
    }
    public function item() 
    {
        return new Mdproperty($this->id);
    }
    public function item_classname()
    {
        return 'Mdproperty';
    }        
    public function load_data($context)
    {
        return NULL;
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
    public function create_object($id,$mdid,$name,$synonym='')
    {
        return NULL;
    }        
    public function getNameFromData($data)
    {
        return $this->synonym;
    }        
}
