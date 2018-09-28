<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_CProperty 
{
//    public function get_idname() 
//    {
//        $objs = array();
//        $data = array(
//            'id'=>array('id'=>'id','name'=>'id','synonym'=>'ID','rank'=>0,'ranktoset'=>1,'ranktostring'=>0,'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'hidden','field'=>1),
//            'name'=>array('id'=>'name','name'=>'name','synonym'=>'NAME','rank'=>1,'ranktoset'=>2,'ranktostring'=>1,'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
//            'synonym'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','rank'=>3,'ranktoset'=>3,'ranktostring'=>0,'name_type'=>'str','name_valmdid'=>'','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1)
//            );
//        $plist = $this->getProperties(TRUE,'toset');
//        foreach ($data as $key=>$val) {
//            $objs[$key]= array();
//            foreach($plist as $rid => $row_plist) {
//                $objs[$key][$rid ] = array('id'=>$rid,'name'=>$val[$rid],'class'=>$row_plist['class']);            
//            }
//            
//        }
//        return $objs;
//    }
    public function txtsql_getproperties() 
    {
        $sql = str_replace('mp.id = :id','mp.mdid = :mdid',$this->txtsql_getproperty())." ORDER BY rank";                
        return $sql;
    }
    public function txtsql_getproperty() 
    {
        $sql = "SELECT mp.id, mp.name, mp.synonym, mp.type as name_type, mp.length, mp.prec,"
                . " mp.mdid, mp.rank, mp.ranktoset, mp.ranktostring, mp.valmdid,"
                . " valmd.name AS name_valmdid,valmd.synonym AS valmdsynonym,"
                . " mi.name as valmdtypename, valmd.mditem as valmditem, 1 as field"
                . " FROM \"CProperties\" AS mp"
                . " LEFT JOIN \"MDTable\" as valmd"
                . " INNER JOIN \"CTable\" as mi"
                . " ON valmd.mditem=mi.id"
                . " ON mp.valmdid = valmd.id"
                . " WHERE mp.id = :id";
        return $sql;
    }
    public function getplist()
    {
        $properties = array(
            'id'=>array('id'=>'id','name'=>'id','synonym'=>'ID','rank'=>0,'ranktoset'=>1,'ranktostring'=>0,'name_type'=>'str','valmdid'=>'','valmdtypename'=>'','class'=>'hidden','field'=>1),
            'name'=>array('id'=>'name','name'=>'name','synonym'=>'NAME','rank'=>1,'ranktoset'=>2,'ranktostring'=>1,'name_type'=>'str','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            'synonym'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','rank'=>3,'ranktoset'=>3,'ranktostring'=>0,'name_type'=>'str','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            'name_type'=>array('id'=>'name_type','name'=>'name_type','synonym'=>'TYPE','rank'=>2,'ranktoset'=>5,'ranktostring'=>0,'name_type'=>'str','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
            'length'=>array('id'=>'length','name'=>'length','synonym'=>'LENGTH','rank'=>5,'ranktoset'=>0,'ranktostring'=>0,'name_type'=>'int','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            'prec'=>array('id'=>'prec','name'=>'prec','synonym'=>'PREC','rank'=>6,'ranktoset'=>0,'ranktostring'=>0,'name_type'=>'int','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            'rank'=>array('id'=>'rank','name'=>'rank','synonym'=>'RANK','rank'=>7,'ranktoset'=>0,'ranktostring'=>0,'name_type'=>'int','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            'ranktoset'=>array('id'=>'ranktoset','name'=>'ranktoset','synonym'=>'RANKTOSET','rank'=>8,'ranktoset'=>0,'ranktostring'=>0,'name_type'=>'int','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            'ranktostring'=>array('id'=>'ranktostring','name'=>'ranktostring','synonym'=>'RANKTOSTRING','rank'=>9,'ranktoset'=>0,'ranktostring'=>0,'name_type'=>'int','valmdid'=>'','valmdtypename'=>'','class'=>'active','field'=>1),
            'valmdid' => array('id'=>'valmdid','name'=>'VALMDID','synonym'=>'VALMDID','rank'=>19,'ranktoset'=>0,'ranktostring'=>0,'name_type'=>'mdid','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
            'name_valmdid' => array('id'=>'name_valmdid','name'=>'NAME_VALMDID','synonym'=>'NAME_VALMDID','rank'=>20,'ranktoset'=>8,'ranktostring'=>0,'name_type'=>'str','valmdid'=>'','valmdtypename'=>'','class'=>'hidden','field'=>0),
            'valmdtypename' => array('id'=>'valmdtypename','name'=>'VALMDTYPENAME','synonym'=>'VALMDTYPENAME','rank'=>21,'ranktoset'=>9,'ranktostring'=>0,'name_type'=>'str','valmdid'=>'','valmdtypename'=>'','class'=>'readonly','field'=>0),
            'field' => array('id'=>'field','name'=>'field','synonym'=>'FIELD','rank'=>25,'ranktoset'=>0,'ranktostring'=>0,'name_type'=>'int','valmdid'=>'','valmdtypename'=>'','class'=>'hidden','field'=>0),
             );
        return $properties;
    }
}            


