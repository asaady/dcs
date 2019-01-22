<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

class Cproperty extends Sheet implements I_Sheet 
{
    use T_Sheet;
    use T_Property;
    
    public function dbtablename()
    {
        return 'CProperties';
    }
    public function getplist()
    {
        $plist = array(
            '0'=>array('id'=>'id','name'=>'id','synonym'=>'ID',
                        'rank'=>0,'ranktoset'=>1,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'readonly','field'=>1),
            '1'=>array('id'=>'name','name'=>'name','synonym'=>'NAME',
                        'rank'=>1,'ranktoset'=>2,'ranktostring'=>1,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
            '2'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'Синомним',
                        'rank'=>2,'ranktoset'=>3,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
            '3'=>array('id'=>'type','name'=>'type','synonym'=>'TYPE',
                        'rank'=>4,'ranktoset'=>5,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
            '4'=>array('id'=>'rank','name'=>'rank','synonym'=>'RANK',
                        'rank'=>7,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'int','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
            '5'=>array('id'=>'ranktoset','name'=>'ranktoset','synonym'=>'RANKTOSET',
                        'rank'=>8,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'int','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
            '6'=>array('id'=>'ranktostring','name'=>'ranktostring','synonym'=>'RANKTOSTRING',
                        'rank'=>10,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'int','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
            '7' => array('id'=>'length','name'=>'length','synonym'=>'LENGTH',
                        'rank'=>11,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'int','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
            '8' => array('id'=>'prec','name'=>'prec','synonym'=>'PREC',
                        'rank'=>11,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'int','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
            '9' => array('id'=>'valmdid','name'=>'valmdid','synonym'=>'VALMDID',
                        'rank'=>19,'ranktoset'=>8,'ranktostring'=>0,
                        'name_type'=>'mdid','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
//            '10' => array('id'=>'valmdtypename','name'=>'valmdtypename','synonym'=>'VALMDTYPENAME',
//                        'rank'=>20,'ranktoset'=>9,'ranktostring'=>0,
//                        'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
             );
        $this->plist = $plist;
        return $plist;
    }        
    public function txtsql_forDetails() 
    {
        return "SELECT mp.id, mp.mdid, mp.name, mp.synonym, "
                . "mc.name as mdname, mc.synonym as mdsynonym, mc.mditem, "
                . "tp.name as mdtypename, tp.synonym as mdtypedescription "
                . "FROM \"CProperties\" as mp "
                    . "INNER JOIN \"MDTable\" as mc "
                        . "INNER JOIN \"CTable\" as tp "
                        . "ON mc.mditem = tp.id "
                    . "ON mp.mdid = mc.id "
                . "WHERE mp.id=:id";
    }        
    public function head() 
    {
        return new Mdentity($this->mdid);
    }
    public function item($id='')
    {
        return NULL;
    }        
    public function getprop_classname()
    {
        return NULL;
    }
    public function item_classname()
    {
        return NULL;
    }        
    public function txtsql_property($parname)
    {
        return NULL;
    }        
    public function txtsql_properties($parname)
    {
        return NULL;
    }        
    public function get_tt_sql_data()
    {        
        $artemptable = array();
        $sql = "SELECT mp.id, mp.name, mp.synonym,"
                . " mp.type as type, mp.type as name_type, mp.length, mp.prec,"
                . " mp.mdid, mp.rank, mp.ranktoset, mp.ranktostring,"
                . " mp.valmdid as id_valmdid, valmd.name AS name_valmdid, valmd.synonym AS valmdsynonym,"
                . " mi.name as name_valmditem, valmd.mditem as id_valmditem,"
                . " 1 as field, 'active' as class"
                . " FROM \"CProperties\" AS mp"
                . " LEFT JOIN \"MDTable\" as valmd"
                . " INNER JOIN \"CTable\" as mi"
                . " ON valmd.mditem=mi.id"
                . " ON mp.valmdid = valmd.id"
                . " WHERE mp.id = :id"
                . " ORDER BY rank";
        $artemptable[] = DataManager::createtemptable($sql,'tt_out',array('id'=>$this->id));   
        return $artemptable;
    }    
    public function get_items() 
    {
        return NULL;
    }        
//    public function getNameFromData($data='')
//    {
//        if (!$data) {
//            return array('name' => $this->name, 'synonym' => $this->synonym);
//        } else {
//            return array('name' => $data['name']['name'],
//                         'synonym' => $data['synonym']['name']);
//        }    
//    }        
}

