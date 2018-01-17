<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use Dcs\Vendor\Core\Models\DataManager;

trait T_Collection {
    public function getDetails($itemid) 
    {
        $sql = "SELECT ct.id, ct.mdid, ct.name, ct.synonym, "
                . "mc.name as mdname, mc.synonym as mdsynonym, mc.mditem, "
                . "tp.name as mdtypename, tp.synonym as mdtypedescription "
                . "FROM \"CTable\" as ct "
                    . "INNER JOIN \"MDTable\" as mc "
                        . "INNER JOIN \"CTable\" as tp "
                        . "ON mc.mditem = tp.id "
                    . "ON ct.mdid = mc.id "
                . "WHERE ct.id=:itemid";
        $sth = DataManager::dm_query($sql,array('itemid'=>$itemid));   
        $objs = $sth->fetch(PDO::FETCH_ASSOC);
	if(!$objs) {
            $objs = array('id'=>'','mdid'=>'','mditem'=>'');
	}
        return $objs;
    }
    public function get_tt_sql_data()
    {
        $sql = "SELECT ct.id, ct.name as name_name, '' as id_name, ct.synonym as name_synonym, '' as id_synonym, ct.mdid";
        $join = " FROM \"CTable\" AS ct";
        $params = array();
        foreach ($this->properties as $row)
        {
            if ($row['field'] == 0) {
                continue;
            }
            $rowname = str_replace("  ","",$row['name']);
            $rowname = str_replace(" ","",$rowname);
            $rowtype = $row['type'];
            if ($rowtype=='cid')
            {
                $join .= " LEFT JOIN \"CPropValue_$rowtype\" as pv_$rowname INNER JOIN \"CTable\" as ct_$rowname ON pv_$rowname.value = ct_$rowname.id ON ct.id=pv_$rowname.id AND pv_$rowname.pid = :pv_$rowname";
                $sql .= ", pv_$rowname.value as id_$rowname, ct_$rowname.synonym as name_$rowname";
            }    
            elseif ($rowtype=='mdid')
            {
                $join .= " LEFT JOIN \"CPropValue_$rowtype\" as pv_$rowname INNER JOIN \"MDTable\" as ct_$rowname ON pv_$rowname.value = ct_$rowname.id ON ct.id=pv_$rowname.id AND pv_$rowname.pid = :pv_$rowname";
                $sql .= ", pv_$rowname.value as id_$rowname, ct_$rowname.synonym as name_$rowname";
            }    
            else 
            {
                $join .= " LEFT JOIN \"CPropValue_$rowtype\" as pv_$rowname ON ct.id=pv_$rowname.id AND pv_$rowname.pid = :pv_$rowname";
                $sql .= ", pv_$rowname.value as name_$rowname, '' as id_$rowname";
                
            }
            $params["pv_$rowname"]=$row['id'];
        }        
        $sql = $sql.$join." WHERE ct.id = :id";
        $params['id'] = $this->id;
        $artemptable = array();
        $artemptable[] = DataManager::createtemptable($sql,'tt_out',$params);   
        return $artemptable;
   }        
}