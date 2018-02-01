<?php
namespace Dcs\Vendor\Core\Models;

use Dcs\Vendor\Core\Models\DcsException;
//use dcs\vendor\core\PropertySet;
//use dcs\vendor\core\iPropertySet as iPropertySet;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

class RpropertySet extends PropertySet implements iPropertySet 
{
    use Properties;
    public function __construct($mdid='') 
    {
	if ($mdid=='') 
        {
            throw new DcsException("class.RPropertySet constructor: mdid is empty");
	}
            //конструктор базового класса
         parent::__construct($mdid);
         $this->tablename = "RegProperties";
    }
    public function txtsql_getproperties($strwhere) 
    {
        $sql = "SELECT mp.id, mp.propid, pr.name as name_prpid, mp.name, 
                       mp.synonym, pst.value as typeid, pt.name as type, 
                       mp.length, mp.prec, mp.mdid, mp.rank, mp.ranktoset, 
                       mp.isres, pmd.value as valmdid, valmd.name AS valmdname, 
                       valmd.synonym AS valmdsynonym, valmd.mditem as valmditem, 
                       mi.name as valmdtypename FROM \"RegProperties\" AS mp
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
		$strwhere
		ORDER BY rank";
        return $sql;
    }
    public static function getplist() 
    {        
        return array('id' => array('id'=>'id','name'=>'id','synonym'=>'ID','rank'=>0,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'readonly','field'=>1),
                    'name' => array('id'=>'name', 'name'=>'name','synonym'=>'NAME','rank'=>1,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                    'synonym' => array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','rank'=>3,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                    'propid'=>array('id'=>'propid','name'=>'propid','synonym'=>'PROPID','rank'=>2,'type'=>'cid','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                    'name_propid'=>array('id'=>'name_propid','name'=>'name_propid','synonym'=>'NAME_PROPID','rank'=>2,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'hidden','field'=>0),
                    'length' => array('id'=>'length','name'=>'length','synonym'=>'LENGTH','rank'=>5,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                    'prec' => array('id'=>'prec','name'=>'prec','synonym'=>'PREC','rank'=>6,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                    'rank' => array('id'=>'rank','name'=>'rank','synonym'=>'RANK','rank'=>7,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                    'ranktoset' =>array('id'=>'ranktoset','name'=>'RANKTOSET','synonym'=>'RANKTOSET','rank'=>9,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                    'isres' => array('id'=>'isres','name'=>'isres','synonym'=>'IS RESOURCE','rank'=>10,'type'=>'bool','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'active','field'=>1),
                    'valmdtype' => array('id'=>'valmdtype','name'=>'VALMDTYPE','synonym'=>'VALMDTYPE','rank'=>11,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'readonly','field'=>0),
                    'valmdid' => array('id'=>'valmdid','name'=>'VALMDID','synonym'=>'VALMDID','rank'=>13,'type'=>'mdid','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'readonly','field'=>0),
                    'name_valmdid' => array('id'=>'name_valmdid','name'=>'NAME_VALMDID','synonym'=>'NAME_VALMDID','rank'=>20,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'hidden','field'=>0),
                    'valmdtypename' => array('id'=>'valmdtypename','name'=>'VALMDTYPENAME','synonym'=>'VALMDTYPENAME','rank'=>15,'type'=>'str','valmdtype'=>DCS_TYPE_EMPTY,'class'=>'readonly','field'=>0)
                   );
    }    
}    
