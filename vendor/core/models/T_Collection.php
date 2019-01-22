<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use Dcs\Vendor\Core\Models\DataManager;

trait T_Collection 
{
//    public function getplist() 
//    {
//        return array(
//            'id'=>array('id'=>'id','name'=>'id','synonym'=>'ID','type'=>'str'),
//            'name'=>array('id'=>'name','name'=>'name','synonym'=>'NAME','type'=>'str'),
//            'synonym'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','type'=>'str'),
//            'rank'=>array('id'=>'rank','name'=>'rank','synonym'=>'RANK','type'=>'int'),
//            'type'=>array('id'=>'type','name'=>'type','synonym'=>'TYPE','type'=>'str'),
//            'name_type'=>array('id'=>'name_type','name'=>'name_type','synonym'=>'NAME_TYPE','type'=>'str'),
//            'class'=>array('id'=>'class','name'=>'class','synonym'=>'CLASS','type'=>'str'),
//            'valmdid'=>array('id'=>'valmdid','name'=>'valmdid','synonym'=>'VALMDID','type'=>'str'),
//            'valmdtypename'=>array('id'=>'valmdtypename','name'=>'valmdtypename','synonym'=>'VALMDTYPENAME','type'=>'str'),
//            'field'=>array('id'=>'field','name'=>'field','synonym'=>'FIELD','type'=>'int')
//            );        
//    }
    public function get_select_properties($strwhere)
    {
        $sql = "SELECT mp.id, mp.name, mp.synonym,"
                . " mp.type as type, mp.type as name_type, mp.length, mp.prec,"
                . " mp.mdid, mp.rank, mp.ranktoset, mp.ranktostring, mp.valmdid,"
                . " valmd.name AS name_valmdid,valmd.synonym AS valmdsynonym,"
                . " mi.name as name_valmditem, valmd.mditem as valmditem,"
                . " 1 as field, 'active' as class"
                . " FROM \"CProperties\" AS mp"
                . " LEFT JOIN \"MDTable\" as valmd"
                . " INNER JOIN \"CTable\" as mi"
                . " ON valmd.mditem=mi.id"
                . " ON mp.valmdid = valmd.id"
                . " $strwhere "
                . "ORDER BY rank";
        return $sql;
    }        
    public function txtsql_property($parname)
    {
        return $this->get_select_properties(" WHERE mp.id = :$parname ");    
    }        
    public function txtsql_properties($parname)
    {
        return $this->get_select_properties(" WHERE mp.mdid = :$parname ");    
    }        
}