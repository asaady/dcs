<?php

namespace Dcs\Vendor\Core\Models;

trait T_Mdentity {
//    public function load_data($data='')
//    {
//        if (!$data) {
//            return array(   'id' => array('id'=>$this->id,'name'=>$this->id),
//                        'name' => array('id'=>'','name'=>$this->name),
//                        'synonym' => array('id'=>'','name'=>$this->synonym),
//                        'mditem' => array('id'=>$this->mditem,'name'=>$this->mditem)
//                );
//        }
//        return array('id'=>array('id'=>$data['id'], 'name'=>$data['id']),
//                      'name'=>array('id'=>$data['name'],'name'=>$data['name']),
//                      'synonym'=>array('id'=>$data['synonym'], 'name'=>$data['synonym']),
//                      'mditem'=>array('id'=>$data['mditem'], 'name'=>$data['mditem'])
//            );
//    }            
    //ret: array temp table names 
    public function get_tt_sql_data()
    {
        $artemptable = array();
        $sql = "SELECT mdt.id, mdt.name, mdt.synonym FROM \"MDTable\" AS mdt "
                    . "WHERE mdt.id= :id";
        $artemptable[] = DataManager::createtemptable($sql,'tt_out',array('id'=>$this->id));   
        return $artemptable;
    }    
    public function txtsql_property($parname)
    {
        return DataManager::get_select_properties(" WHERE mp.id = :$parname ");    
    }        
    public function txtsql_properties($parname)
    {
        return DataManager::get_select_properties(" WHERE mp.mdid = :$parname ");    
    }        
}
