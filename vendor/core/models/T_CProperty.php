<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_CProperty 
{
    public function loadProperties()
    {
        $properties = array(
            'id'=>array('id'=>'id','name'=>'id','synonym'=>'ID','rank'=>0,'ranktoset'=>1,'ranktostring'=>0,'type'=>'str','valmdtypename'=>'','class'=>'hidden','field'=>1),
            'name'=>array('id'=>'name','name'=>'name','synonym'=>'NAME','rank'=>1,'ranktoset'=>2,'ranktostring'=>1,'type'=>'str','valmdtypename'=>'','class'=>'active','field'=>1),
            'synonym'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','rank'=>3,'ranktoset'=>3,'ranktostring'=>0,'type'=>'str','valmdtypename'=>'','class'=>'active','field'=>1),
            'type'=>array('id'=>'type','name'=>'type','synonym'=>'TYPE','rank'=>2,'ranktoset'=>5,'ranktostring'=>0,'type'=>'str','valmdtypename'=>'','class'=>'readonly','field'=>0),
            'length'=>array('id'=>'length','name'=>'length','synonym'=>'LENGTH','rank'=>5,'ranktoset'=>0,'ranktostring'=>0,'type'=>'int','valmdtypename'=>'','class'=>'active','field'=>1),
            'prec'=>array('id'=>'prec','name'=>'prec','synonym'=>'PREC','rank'=>6,'ranktoset'=>0,'ranktostring'=>0,'type'=>'int','valmdtypename'=>'','class'=>'active','field'=>1),
            'rank'=>array('id'=>'rank','name'=>'rank','synonym'=>'RANK','rank'=>7,'ranktoset'=>0,'ranktostring'=>0,'type'=>'int','valmdtypename'=>'','class'=>'active','field'=>1),
            'ranktoset'=>array('id'=>'ranktoset','name'=>'ranktoset','synonym'=>'RANKTOSET','rank'=>8,'ranktoset'=>0,'ranktostring'=>0,'type'=>'int','valmdtypename'=>'','class'=>'active','field'=>1),
            'ranktostring'=>array('id'=>'ranktostring','name'=>'ranktostring','synonym'=>'RANKTOSTRING','rank'=>9,'ranktoset'=>0,'ranktostring'=>0,'type'=>'int','valmdtypename'=>'','class'=>'active','field'=>1),
            'valmdid' => array('id'=>'valmdid','name'=>'VALMDID','synonym'=>'VALMDID','rank'=>19,'ranktoset'=>0,'ranktostring'=>0,'type'=>'mdid','valmdtypename'=>'','class'=>'readonly','field'=>0),
            'name_valmdid' => array('id'=>'name_valmdid','name'=>'NAME_VALMDID','synonym'=>'NAME_VALMDID','rank'=>20,'ranktoset'=>8,'ranktostring'=>0,'type'=>'str','valmdtypename'=>'','class'=>'hidden','field'=>0),
            'valmdtypename' => array('id'=>'valmdtypename','name'=>'VALMDTYPENAME','synonym'=>'VALMDTYPENAME','rank'=>21,'ranktoset'=>9,'ranktostring'=>0,'type'=>'str','valmdtypename'=>'','class'=>'readonly','field'=>0),
            'field' => array('id'=>'field','name'=>'field','synonym'=>'FIELD','rank'=>25,'ranktoset'=>0,'ranktostring'=>0,'type'=>'int','valmdtypename'=>'','class'=>'hidden','field'=>0),
             );
        return $properties;
    }
    public function get_tt_sql_data() 
    {
        $artemptable = array();
        $sql = DataManager::get_select_cproperties(" where mp.id = :id ");
        $artemptable[] = DataManager::createtemptable($sql,'tt_out',array('id'=>$this->mdid));   
        return $artemptable;
    }
    public function txtsql_getproperties()
    {
        return "";
    }
}            


