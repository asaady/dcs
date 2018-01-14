<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_Property {
    public function loadProperties($strwhere='')
    {
        $sql = $this->txtsql_getproperties($strwhere);
        if ($sql === '')
        {
            return 0;
        }    
        $params = array('mdid'=>$this->id);
        $res = DataManager::dm_query($sql,$params);
        $cnt = 0;
        $this->properties = array();
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $this->properties[$row['id']] = $row;
            $cnt++;
        }    
        return $cnt;
    }        
    public function getProperties($byid=false) 
    {
        $objs = array();
        $plist = $this->getplist();
        $key = -1;    
        foreach($this->properties as $prop) 
        {
            $rid = $prop['id'];
            if ($byid)
            {    
                $key = $rid;
            }
            else
            {
                $key++;
            }    
            $objs[$key] = array();
            foreach ($plist as $pkey => $prow)
            {    
                $objs[$key][$pkey] = $prop[$pkey];
            }
            $objs[$key]['class'] = 'active';
            if ($key == 'id') {
                $objs[$key]['class'] = 'hidden';
            }
        }
        return $objs;
    }
}
