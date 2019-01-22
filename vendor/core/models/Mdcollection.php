<?php
namespace Dcs\Vendor\Core\Models;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

use PDO;
class Mdcollection extends Sheet implements I_Sheet, I_Set, I_Property
{
    use T_Sheet;
    use T_Mdentity;
    use T_Collection;
    
    public function getplist() 
    {
        return array(
            '0'=>array('id'=>'id','name'=>'id','synonym'=>'ID',
                        'rank'=>0,'ranktoset'=>1,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'readonly','field'=>1),
            '1'=>array('id'=>'name','name'=>'name','synonym'=>'NAME',
                        'rank'=>1,'ranktoset'=>2,'ranktostring'=>1,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
            '2'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM',
                        'rank'=>2,'ranktoset'=>3,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
             );
    }
    public function dbtablename()
    {
        return 'MDTable';
    }
    public function txtsql_forDetails() 
    {
        return "SELECT mdt.id, mdt.name, mdt.synonym, mdt.mditem, "
                    . "mdt.id as mdid, mdt.name as mdname, "
                    . "mdt.synonym as mdsynonym, mdi.name as mdtypename, "
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
    public function item($id) 
    {
        return new CProperty($id,$this);
    }
    public function getprop_classname()
    {
        return NULL;
    }
    public function item_classname()
    {
        return 'CProperty';
    }        
    function update($data) 
    {
        return array();
    }
    function create_property($data) 
    {
    }
    function before_save($data='') 
    {
        return array();
    }
    public function create_object($name,$synonym='')
    {
        return NULL;
    }        
    public function getCProperties()
    {
        $sql = $this->txtsql_properties('mdid');
        if ($sql === '')
        {
            return 0;
        }    
        $params = array('mdid'=> $this->mdid);
        $res = DataManager::dm_query($sql,$params);
        $properties = array();
        $properties['name'] = array(
            'id'=>'name', 
            'name'=>'name',
            'synonym'=>'NAME',
            'rank'=>1,
            'ranktostring'=>0,
            'ranktoset'=>2,
            'id_valmdid'=>DCS_EMPTY_ENTITY,
            'name_valmdid'=>'',
            'name_valmditem'=>'',
            'length'=>100, 
            'prec'=>0,
            'id_type'=>'str',
            'name_type'=>'str',
            'class'=>'active',
            'field'=>0);
        $properties['synonym'] = array(
            'id'=>'synonym',
            'name'=>'synonym',
            'synonym'=>'SYNONYM',
            'rank'=>3,
            'ranktostring'=>1,
            'ranktoset'=>3,
            'id_valmdid'=>DCS_EMPTY_ENTITY,
            'name_valmdid'=>'',
            'name_valmditem'=>'',
            'length'=>100, 
            'prec'=>0,
            'id_type'=>'str',
            'name_type'=>'str',
            'class'=>'active',
            'field'=>0);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $properties[$row['id']] = $row;
        }    
        return $properties;
    }    
    public function get_items() 
    {
        $sql = $this->txtsql_properties('mdid');
        if ($sql === '')
        {
            return NULL;
        }    
        $params = array('mdid'=> $this->mdid);
        return DataManager::dm_query($sql,$params);
    }    
//    public function getItems($filter=array()) 
//    {
//        $objs = array();
//        foreach ($this->get_items() as $row) {
//            $objs[$row['id']] = array(
//            'id' => array('id'=>'','name'=>$row['id']),
//            'name' => array('id'=>'','name'=>$row['name']),
//            'synonym' => array('id'=>'','name'=>$row['synonym']),
//            'type' => array('id'=>$row['type'],'name'=>$row['name_type']),
//            'ranktoset' => array('id'=>'','name'=>$row['ranktoset']),
//            'valmdid' => array('id'=>$row['valmdid'],'name'=>$row['name_valmdid']),
//            'valmdtypename' => array('id'=>$row['valmdtypename'],'name'=>$row['valmdtypename']),
//            'class' => 'active');
//        }
//        $this->version = time();
//        return $objs;
//    }
    public function loadProperties() 
    {
        $properties = array(
            'id'=>array('id'=>'id','name'=>'id','synonym'=>'ID',
                        'rank'=>0,'ranktoset'=>1,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'readonly','field'=>1),
            'name'=>array('id'=>'name','name'=>'name','synonym'=>'NAME',
                        'rank'=>1,'ranktoset'=>2,'ranktostring'=>1,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
            'synonym'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM',
                        'rank'=>3,'ranktoset'=>3,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
//            'name_type'=>array('id'=>'name_type','name'=>'name_type','synonym'=>'TYPE',
//                        'rank'=>6,'ranktoset'=>5,'ranktostring'=>0,
//                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
            'type'=>array('id'=>'type','name'=>'type','synonym'=>'Тип',
                        'rank'=>7,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'readonly','field'=>1),
            'rank'=>array('id'=>'rank','name'=>'rank','synonym'=>'RANK',
                        'rank'=>8,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'int','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
            'ranktoset'=>array('id'=>'ranktoset','name'=>'ranktoset','synonym'=>'RANKTOSET',
                        'rank'=>9,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'int','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
            'ranktostring'=>array('id'=>'ranktostring','name'=>'ranktostring','synonym'=>'RANKTOSTRING',
                        'rank'=>10,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'int','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
            'valmdid' => array('id'=>'valmdid','name'=>'VALMDID','synonym'=>'VALMDID',
                        'rank'=>19,'ranktoset'=>8,'ranktostring'=>0,
                        'name_type'=>'mdid','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'readonly','field'=>1),
//            'name_valmdid' => array('id'=>'name_valmdid','name'=>'NAME_VALMDID','synonym'=>'NAME_VALMDID',
//                        'rank'=>20,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>0),
//            'valmdid' => array('id'=>'valmdid','name'=>'VALMDID','synonym'=>'VALMDID',
//                        'rank'=>21,'ranktoset'=>9,'ranktostring'=>0,
//                        'name_type'=>'cid','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
//            'field' => array('id'=>'field','name'=>'field','synonym'=>'FIELD',
//                        'rank'=>25,'ranktoset'=>0,'ranktostring'=>0,
//                        'name_type'=>'int','name_valmdid'=>'','valmdid'=>'',
//                        'name_valmditem'=>'','class'=>'hidden','field'=>0),
             );
        $this->properties = $properties;
        return $properties;
    }        
}
