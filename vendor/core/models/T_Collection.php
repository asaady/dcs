<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use Dcs\Vendor\Core\Models\DataManager;

trait T_Collection 
{
    public function loadProperties()
    {
        $sql = $this->txtsql_getproperties();
        if ($sql === '')
        {
            return 0;
        }    
        $params = array('mdid'=> $this->mdid);
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
}