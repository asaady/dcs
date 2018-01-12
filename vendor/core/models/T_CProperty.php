<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait TcProperty {
    public function txtsql_getproperties($strwhere='') 
    {
        if ($strwhere == '') {
            $strwhere = ' WHERE mp.mdid = :mdid ';
        }
        $sql = "SELECT mp.id, mp.name, mp.synonym, mp.type, mp.length, mp.prec, 
                       mp.mdid, mp.rank, mp.ranktoset, mp.valmdid, 
                       valmd.name AS name_valmdid,valmd.synonym AS valmdsynonym, 
                       mi.name as valmdtypename, valmd.mditem as valmditem 
                       FROM \"CProperties\" AS mp
                        LEFT JOIN \"MDTable\" as valmd
                          INNER JOIN \"CTable\" as mi
                          ON valmd.mditem=mi.id
                        ON mp.valmdid = valmd.id
                      $strwhere
                      ORDER BY rank";
        return $sql;
    }
    public static function get_plist($mode = '') 
    {        
        $plist = array('id' => array('id'=>'id','name'=>'id','synonym'=>'ID','rank'=>0,'type'=>'str','class'=>'readonly','field'=>1),
                    'name' => array('id'=>'name', 'name'=>'name','synonym'=>'NAME','rank'=>1,'type'=>'str','class'=>'active','field'=>1),
                    'synonym' => array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','rank'=>3,'type'=>'str','class'=>'active','field'=>1),
                    'type' => array('id'=>'type','name'=>'type','synonym'=>'TYPE','rank'=>4,'type'=>'str','class'=>'active','field'=>1),
                    'length' => array('id'=>'length','name'=>'length','synonym'=>'LENGTH','rank'=>5,'type'=>'str','class'=>'active','field'=>1),
                    'prec' => array('id'=>'prec','name'=>'prec','synonym'=>'PREC','rank'=>6,'type'=>'str','class'=>'active','field'=>1),
                    'rank' => array('id'=>'rank','name'=>'rank','synonym'=>'RANK','rank'=>7,'type'=>'str','class'=>'active','field'=>1),
                    'ranktoset' =>array('id'=>'ranktoset','name'=>'RANKTOSET','synonym'=>'RANKTOSET','rank'=>9,'type'=>'str','class'=>'active','field'=>1),
                    'ranktostring'=>array('id'=>'ranktostring','name'=>'ranktostring','synonym'=>'RANKTOSTRING','rank'=>9,'type'=>'int','class'=>'active','field'=>1),
                    'valmdid' => array('id'=>'valmdid','name'=>'VALMDID','synonym'=>'VALMDID','rank'=>13,'type'=>'mdid','class'=>'active','field'=>1),
                    'name_valmdid' => array('id'=>'name_valmdid','name'=>'NAME_VALMDID','synonym'=>'NAME_VALMDID','rank'=>20,'type'=>'str','class'=>'hidden','field'=>0),
                    'valmdtypename' => array('id'=>'valmdtypename','name'=>'VALMDTYPENAME','synonym'=>'VALMDTYPENAME','rank'=>15,'type'=>'str','class'=>'readonly','field'=>0)
                   );
        if ($mode == 'CONFIG') {
            $plist['id']['class'] = 'readonly';
        }
        return $plist;
    }
}            


