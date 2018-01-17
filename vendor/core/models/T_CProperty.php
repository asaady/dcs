<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_CProperty 
{
    public function txtsql_getproperties() 
    {
        $sql = "SELECT mp.id, mp.name, mp.synonym, mp.type, mp.length, mp.prec, 
                       mp.mdid, mp.rank, mp.ranktoset, mp.ranktostring, mp.valmdid, 
                       valmd.name AS name_valmdid,valmd.synonym AS valmdsynonym, 
                       mi.name as valmdtypename, valmd.mditem as valmditem, 1 as field 
                       FROM \"CProperties\" AS mp
                        LEFT JOIN \"MDTable\" as valmd
                          INNER JOIN \"CTable\" as mi
                          ON valmd.mditem=mi.id
                        ON mp.valmdid = valmd.id
                      WHERE mp.mdid = :mdid
                      ORDER BY rank";
        return $sql;
    }
    public function loadProperties()
    {
        $sql = $this->txtsql_getproperties();
        if ($sql === '')
        {
            return 0;
        }    
        $params = array('mdid'=> ($this->head) ? $this->head->getid() : $this->id);
        $res = DataManager::dm_query($sql,$params);
        $this->properties = array();
        $this->properties['name'] = array(
            'id'=>'name', 
            'name'=>'name',
            'synonym'=>'NAME',
            'rank'=>1,
            'ranktostring'=>0,
            'ranktoset'=>2,
            'valmdid'=>DCS_EMPTY_ENTITY,
            'name_valmdid'=>'',
            'valmdtypename'=>'',
            'length'=>100, 
            'prec'=>0,
            'type'=>'str',
            'class'=>'active',
            'field'=>0);
        $this->properties['synonym'] = array(
            'id'=>'synonym',
            'name'=>'synonym',
            'synonym'=>'SYNONYM',
            'rank'=>3,
            'ranktostring'=>1,
            'ranktoset'=>3,
            'valmdid'=>DCS_EMPTY_ENTITY,
            'name_valmdid'=>'',
            'valmdtypename'=>'',
            'length'=>100, 
            'prec'=>0,
            'type'=>'str',
            'class'=>'active',
            'field'=>0);
        $cnt = 2;
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $this->properties[$row['id']] = $row;
            $cnt++;
        }    
        return $cnt;
    }        
    public function getplist($prefix = '') 
    {        
        $plist = array('id' => array('id'=>'id','name'=>'id','synonym'=>'ID','rank'=>0,'ranktoset'=>1,'type'=>'str','class'=>'hidden','field'=>0),
                    'name' => array('id'=>'name', 'name'=>'name','synonym'=>'NAME','rank'=>1,'ranktoset'=>2,'type'=>'str','class'=>'active','field'=>1),
                    'synonym' => array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','rank'=>3,'ranktoset'=>3,'type'=>'str','class'=>'active','field'=>1),
                    'type' => array('id'=>'type','name'=>'type','synonym'=>'TYPE','rank'=>4,'ranktoset'=>4,'type'=>'str','class'=>'active','field'=>1),
                    'length' => array('id'=>'length','name'=>'length','synonym'=>'LENGTH','rank'=>5,'ranktoset'=>0,'type'=>'str','class'=>'active','field'=>1),
                    'prec' => array('id'=>'prec','name'=>'prec','synonym'=>'PREC','rank'=>6,'ranktoset'=>0,'type'=>'str','class'=>'active','field'=>1),
                    'rank' => array('id'=>'rank','name'=>'rank','synonym'=>'RANK','rank'=>7,'ranktoset'=>0,'type'=>'str','class'=>'active','field'=>1),
                    'ranktoset' =>array('id'=>'ranktoset','name'=>'RANKTOSET','synonym'=>'RANKTOSET','rank'=>9,'ranktoset'=>0,'type'=>'str','class'=>'active','field'=>1),
                    'ranktostring'=>array('id'=>'ranktostring','name'=>'ranktostring','synonym'=>'RANKTOSTRING','rank'=>9,'ranktoset'=>0,'type'=>'int','class'=>'active','field'=>1),
                    'valmdid' => array('id'=>'valmdid','name'=>'VALMDID','synonym'=>'VALMDID','rank'=>13,'ranktoset'=>0,'type'=>'mdid','class'=>'active','field'=>1),
                    'name_valmdid' => array('id'=>'name_valmdid','name'=>'NAME_VALMDID','synonym'=>'NAME_VALMDID','rank'=>20,'ranktoset'=>20,'type'=>'str','class'=>'hidden','field'=>0),
                    'valmdtypename' => array('id'=>'valmdtypename','name'=>'VALMDTYPENAME','synonym'=>'VALMDTYPENAME','rank'=>21,'ranktoset'=>21,'type'=>'str','class'=>'readonly','field'=>0),
                    'field' => array('id'=>'field','name'=>'field','synonym'=>'FIELD','rank'=>25,'ranktoset'=>0,'type'=>'int','class'=>'hidden','field'=>0)
                   );
        if ($prefix == 'CONFIG') {
            $plist['id']['class'] = 'readonly';
        }
        return $plist;
    }
}            


