<?php
namespace Dcs\Vendor\Core\Models;
use Exception;
//use dcs\vendor\core\Model;
//use dcs\vendor\core\iModel as iModel;

class CPropertySet extends PropertySet implements iPropertySet
{
    use Properties;
    public function __construct($mdid) 
    {
	if ($mdid=='') 
        {
            throw new Exception("class.CPropertySet constructor: mdid is empty");
	}
        //конструктор базового класса
         parent::__construct($mdid);
         $this->tablename = "CProperties";
    }
    public function txtsql_getproperties($strwhere) 
    {
        $sql = "SELECT mp.id, mp.name, mp.synonym, mp.type, mp.length, mp.prec, 
                       mp.mdid, mp.rank, mp.ranktoset, mp.valmdid, 
                       valmd.name AS valmdname,valmd.synonym AS valmdsynonym, 
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
    public static function getplist() 
    {        
        return array('id' => array('id'=>'id','name'=>'id','synonym'=>'ID','rank'=>0,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'readonly','field'=>1),
                    'name' => array('id'=>'name', 'name'=>'name','synonym'=>'NAME','rank'=>1,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                    'synonym' => array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','rank'=>3,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                    'type' => array('id'=>'type','name'=>'type','synonym'=>'TYPE','rank'=>4,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                    'length' => array('id'=>'length','name'=>'length','synonym'=>'LENGTH','rank'=>5,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                    'prec' => array('id'=>'prec','name'=>'prec','synonym'=>'PREC','rank'=>6,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                    'rank' => array('id'=>'rank','name'=>'rank','synonym'=>'RANK','rank'=>7,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                    'ranktoset' =>array('id'=>'ranktoset','name'=>'RANKTOSET','synonym'=>'RANKTOSET','rank'=>9,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                    'valmdtype' => array('id'=>'valmdtype','name'=>'VALMDTYPE','synonym'=>'VALMDTYPE','rank'=>11,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'readonly','field'=>0),
                    'valmdid' => array('id'=>'valmdid','name'=>'VALMDID','synonym'=>'VALMDID','rank'=>13,'type'=>'mdid','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                    'name_valmdid' => array('id'=>'name_valmdid','name'=>'NAME_VALMDID','synonym'=>'NAME_VALMDID','rank'=>20,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'hidden','field'=>0),
                    'valmdtypename' => array('id'=>'valmdtypename','name'=>'VALMDTYPENAME','synonym'=>'VALMDTYPENAME','rank'=>15,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'readonly','field'=>0)
                   );
    }    
}

